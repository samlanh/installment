<?php 
Class Group_Form_FrmClientBlackList extends Zend_Dojo_Form {
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
	public function FrmClientBlackList($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
		
		));
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$branch = new Zend_Dojo_Form_Element_FilteringSelect('branch');
		$branch->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'Onchange'=>'filterClient();'
		));
		$rows = $db->getAllBranchName();
		$options=array(''=>'---Select Branch---');
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$branch->setMultiOptions($options);
		$branch->setValue($request->getParam('branch'));
		
		
		
// 		$client_code = new Zend_Dojo_Form_Element_FilteringSelect('client_code');
// 		$client_code->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true',
// 				'onchange'=>'getmemberIdGroup();'
// 		));
// 		$group_opt = $db ->getGroupCodeById(1,0,1);//code,individual,option
// 		$client_code->setMultiOptions($group_opt);
		
// 		$client_name = new Zend_Dojo_Form_Element_FilteringSelect('client_name');
// 		$client_name->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true',
// 				'onchange'=>'checkMember()'
// 		));
// 		$options = $db->getGroupCodeById(2,0,1);
// 		$client_name->setMultiOptions($options);
		
		$problem=new Zend_Dojo_Form_Element_ValidationTextBox('problem');
		$problem->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
				
				));
		$date = new Zend_Dojo_Form_Element_DateTextBox('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$date->setValue(date('Y-m-d'));
		$status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$status->setMultiOptions($opt);
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
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
		
// 		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
// 		$_branch_id->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true'
// 		));
// 		$rows = $db->getAllBranchName();
// 		$options=array(''=>"------Select Branch Name------");
// 		if(!empty($rows))
// 			foreach($rows AS $row){
// 			$options[$row['br_id']]=$row['branch_namekh'];
// 		}
// 		$_branch_id->setMultiOptions($options);
// 		$_branch_id->setValue($request->getParam('branch_id'));
	
		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$branch->setValue($data['branch_id']);
// 			$client_code->setValue($data['client_id']);
// 			$client_name->setValue($data['client_id']);
			$problem->setValue($data['reasonblack_list']);
			$date->setValue($data['date_blacklist']);
			$status->setValue($data['status_blacklist']);
			$_id->setValue($data['client_id']);
		}
		$this->addElements(array($from_date,$to_date,$_id,$_title,$_status,$_btn_search,$branch,$problem,$date,$status));
				
		return $this;
		
	}	
}