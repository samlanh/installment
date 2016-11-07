<?php 
Class Callecterall_Form_Frmchengcallecterall extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		
	}
	public function Frmchengcallecterall(){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$branch = new Zend_Dojo_Form_Element_FilteringSelect('branch');
		$branch->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$rows = $db->getAllBranchName();
		$options='';
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$branch->setMultiOptions($options);
		$db = new Application_Model_DbTable_DbGlobal();
		$owner = new Zend_Dojo_Form_Element_FilteringSelect('owner');
		$owner->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>"popupCheckStaff();"
		));
		$options = $db->getGroupCodeById(2,0,1);
		$owner->setMultiOptions($options);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$owner_code = new Zend_Dojo_Form_Element_FilteringSelect('owner_code');
		$group_opt = $db ->getGroupCodeById(1,0,1);//code,individual,option
		$owner_code->setMultiOptions($group_opt);
		$owner_code->setValue($request->getParam('client_code'));
		
		$from = new Zend_Dojo_Form_Element_FilteringSelect('from');
		$from->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
				));
		$from->setValue($request->getParam('from'));
		$opt= $db->getCollteralType(1);
		$from->setMultiOptions($opt);
		
		$to = new Zend_Dojo_Form_Element_FilteringSelect('to');
		$to->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt= $db->getCollteralType(1);
		$to->setMultiOptions($opt);
		
		$owners = new Zend_Dojo_Form_Element_TextBox('owners');
		$owners->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$_personal=new Zend_Dojo_Form_Element_FilteringSelect('personal');
		$_personal->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$opt=array(1=>'ផ្ទាល់ខ្លួន',2=>'អ្នកធានាជំនួស');
		$_personal->setMultiOptions($opt);
		$_personal->setValue(1);
		
		
		$numbernote = new Zend_Dojo_Form_Element_NumberTextBox('numbernote');
		$numbernote->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
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
		
		
		
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$options= array(1=>"ប្រើប្រាស់",2=>"មិនប្រើប្រាស់");
		$status->setMultiOptions($options);
		
		$detial = new Zend_Dojo_Form_Element_TextBox('detial');
		$detial ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
	
		$this->addElements(array($branch,$owner,$from,$to,$date,$note,$status,$owners,$owner_code,$detial
				,$_personal,$numbernote));
		return $this;
		
	}	
}