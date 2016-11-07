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
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
				));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		
		$db = new Application_Model_DbTable_DbGlobal(); 
		
		$employee = new Zend_Dojo_Form_Element_FilteringSelect('employee');
		$rows = $db ->getAllCOName();
		$options=array(''=>"---ស្វែងរកតាមរយៈឈ្មោះ---");
		if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
		$employee->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();'
		));
		$employee->setMultiOptions($options);
		$employee->setValue($request->getParam('employee'));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'label'=>'Search'
				
				));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$rows = $db->getAllBranchName();
		$options_branch=array(''=>"---ស្វែងរកតាមរយៈសាខា---");
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['br_id']]=$row['branch_namekh'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$approve_by = new Zend_Dojo_Form_Element_FilteringSelect('approve_by');
		$rows = $db ->getAllCOName();
		$options_approve=array(''=>"---ស្វែងរកអ្នកយល់ព្រម---");
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
				'class'=>'fullside'
		));
		$type->setMultiOptions($opt_type);
		$type->setValue($request->getParam("type"));
		
// 		$from_date=new Zend_Dojo_Form_Element_DateTextBox('from_date');
// 		$from_date->setAttribs(array(
// 				'dojoType'=>'dijit.form.DateTextBox',
// 				'required'=>true,
// 				'class'=>'fullside'
// 		));
// 		$from_date->setValue(date('Y-m-d'));
// // 		$from_date->setValue($request->getParam("from_date"));
		 
// 		$to_date=new Zend_Dojo_Form_Element_DateTextBox('to_date');
// 		$to_date->setAttribs(array(
// 				'dojoType'=>'dijit.form.DateTextBox',
// 				'required'=>true,
// 				'class'=>'fullside'
// 		));
// 		$to_date->setValue(date('Y-m-d'));
// // 		$to_date->setValue($request->getParam("to_date"));

		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				//'required'=>'true',
				'class'=>'fullside',
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
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
		
		$opt_position=$db->getAllStaffPosition(null,1);
		$position_->setMultiOptions($opt_position);
		$position_->setValue($request->getParam("position"));
		
		$this->addElements(array($position_,$from_date,$to_date,$type,$employee,$_title,$_title,$_status,$_btn_search,$branch_id,$approve_by));
		return $this;
	}
	
}