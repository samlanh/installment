<?php

class Application_Form_FrmAdvanceSearch extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function AdvanceSearch($data=null,$type=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
				));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside','required'=>false,));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		
		$db = new Application_Model_DbTable_DbGlobal(); 
		
		$employee = new Zend_Dojo_Form_Element_FilteringSelect('employee');
		$rows = $db ->getAllCOName();
		$options=array(''=>$this->tr->translate("SELECT_EMPLOYEE"));
		if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
		$employee->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$employee->setMultiOptions($options);
		$employee->setValue($request->getParam('employee'));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'class'=>'button-class button-primary',
				'iconclass'=>'glyphicon glyphicon-search',
				'label'=>$this->tr->translate("SEARCH")
				
				));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$rows = $db->getAllBranchName();
		$options_branch=array('-1'=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		if (count($rows)==1){
			$branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($rows)) foreach($rows AS $row){
				$branch_id->setValue($row['br_id']);
			}
		}
		
		$approve_by = new Zend_Dojo_Form_Element_FilteringSelect('approve_by');
		$rows = $db ->getAllCOName();
		$options_approve=array(''=>$this->tr->translate("SELECT_APPROVE_BY"));
		if(!empty($rows))foreach($rows AS $row) $options_approve[$row['co_id']]=$row['co_khname'];
		$approve_by->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();'
		));
		$approve_by->setMultiOptions($options_approve);
		$approve_by->setValue($request->getParam("approve_by"));
		
		$opt_type=$db->getVewOptoinTypeByType(7,1);
		$type=new Zend_Dojo_Form_Element_FilteringSelect('type');
		$type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>true,
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$type->setMultiOptions($opt_type);
		$type->setValue($request->getParam("type"));
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				//'required'=>'true',
				'placeholder'=>$this->tr->translate('START_DATE'),
				
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			//$_date = date("Y-m-d");
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$to_date->setValue($_date);
		
		
		$position_=new Zend_Dojo_Form_Element_FilteringSelect('position');
		$position_->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				//     			'dojoType'=>$this->filter,
				'required'=>true,
				'class'=>'fullside'
		));
		
		$opt_position=array();
		$position_->setMultiOptions($opt_position);
		$position_->setValue($request->getParam("position"));
		
		$user = new Zend_Dojo_Form_Element_FilteringSelect('user');
		$rows = $db ->getAllUser();
		$options=array(''=>$this->tr->translate("SELECT_USER"));
		if(!empty($rows))foreach($rows AS $row) $options[$row['id']]=$row['by_user'];
		$user->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
		));
		$user->setMultiOptions($options);
		$user->setValue($request->getParam('user'));
		
		$dbcusre = new Group_Model_DbTable_DbCustomer();
		$statusreq = new Zend_Dojo_Form_Element_FilteringSelect('statusreq');
		$statusreq->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'showstatusreq();'
		
		));
		$rows = $dbcusre->getAllstatusreqForOpt();
		$options = array(''=>$this->tr->translate("CHOOSE_STATUS_REQ"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['name']]=$row['name'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
		}
		$statusreq->setMultiOptions($options);
		$statusreq->setValue($request->getParam('statusreq'));
		
		$know_by = new Zend_Dojo_Form_Element_FilteringSelect('know_by');
		$know_by->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$opt_know = $db->getAllKnowBy(1);
		$know_by->setMultiOptions($opt_know);
		$know_by->setValue($request->getParam('know_by'));
		
		$_arr = array(-1=>$this->tr->translate("PLEASE_SELECT"),0=>$this->tr->translate("DROPPED"),1=>$this->tr->translate("PROCCESSING"),2=>$this->tr->translate("WAITING_RESPONSE"),3=>$this->tr->translate("COMPLETED_CONTACT"));
    	$_proccessSearch = new Zend_Dojo_Form_Element_FilteringSelect("proccessSearch");
    	$_proccessSearch->setMultiOptions($_arr);
    	$_proccessSearch->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
		$_proccessSearch->setValue($request->getParam('proccessSearch'));
		
		$_type_of_property=  new Zend_Dojo_Form_Element_FilteringSelect('type_property_sale');
		$_type_of_property->setAttribs(array('dojoType'=>$this->filter,	'class'=>'fullside',));
		$_type_of = array(
				'-1'=>$this->tr->translate("SELECT_TYPE"),
				'1'=>$this->tr->translate("SOLD_OUT"),
				'0'=>$this->tr->translate("NOT_YET_SALE"));
		$_type_of_property->setMultiOptions($_type_of);
		$_type_of_property->setValue($request->getParam("type_property_sale"));
		
		
		$this->addElements(array($_type_of_property,$know_by,$statusreq,$position_,$from_date,$to_date,$type,$employee,$_title,$_title,$_status,$_btn_search,$branch_id,
		$approve_by,$user,
		$_proccessSearch
		
		));
		return $this;
	}
	
}