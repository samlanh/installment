<?php 
Class Accounting_Form_Frmbalancsheet extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function Frmbalancsheet($data=null){
		
		
		$Branch=new Zend_Dojo_Form_Element_FilteringSelect('branch');
		$Branch->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$For=new Zend_Dojo_Form_Element_FilteringSelect('for');
		$For->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$Parent=new Zend_Dojo_Form_Element_FilteringSelect('parent');
		$Parent->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$Category=new Zend_Dojo_Form_Element_FilteringSelect('category');
		$Category->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$Account_No=new Zend_Dojo_Form_Element_FilteringSelect('account_No');
		$Account_No->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$Account_Name=new Zend_Dojo_Form_Element_FilteringSelect('account_Name');
		$Account_Name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$Amount=new Zend_Dojo_Form_Element_TextBox('amount');
		$Amount->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$Note=new Zend_Dojo_Form_Element_TextBox('note');
		$Note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>'true'
		));
		$Date->setValue(date('Y-m-d'));
		
		$Status=new Zend_Dojo_Form_Element_TextBox('status');
		$Status->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>'true'
		));
		
// 		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
// 		$_branch_id->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true'
// 		));
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$rows = $db->getAllBranchName();
// 		$options='';
// 		if(!empty($rows))foreach($rows AS $row){
// 			$options[$row['br_id']]=$row['branch_namekh'];
// 		}
// 		$_branch_id->setMultiOptions($options);
		 
// 		$_id = new Zend_Form_Element_Hidden('id');
		
		$this->addElements(array($Branch,$For,$Parent,$Category,$Account_No,$Amount,$Note,$Date,$Status,$Account_Name));
		return $this;
		
	}	
}