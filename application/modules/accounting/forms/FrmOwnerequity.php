<?php 
Class Accounting_Form_FrmOwnerequity extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmOwnerequity($data=null){
		
		$Date=new Zend_Dojo_Form_Element_TextBox('dates');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		$For_date=new Zend_Dojo_Form_Element_DateTextBox('for_dates');
		$For_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
				));
		$For_date->setValue(date('Y-m-d'));
		
		$Branc=new Zend_Dojo_Form_Element_TextBox('brancs');
		$Branc->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>'true'
		));
		
		
		
		
		
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$rows = $db->getAllBranchName();
// 		$options='';
// 		if(!empty($rows))foreach($rows AS $row){
// 			$options[$row['br_id']]=$row['branch_namekh'];
// 		}
// 		$_branch_id->setMultiOptions($options);
		 
// 		$_id = new Zend_Form_Element_Hidden('id');
		
		$this->addElements(array($Date,$For_date,$Branc));
		return $this;
		
	}	
}