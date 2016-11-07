<?php 
Class Accounting_Form_Frmaccountcate extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function Frmaccountcate($data=null){
		
		$Categoryname_kh = new Zend_Dojo_Form_Element_TextBox('categoryname_kh');
		$Categoryname_kh->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
				));
		
		$Categoryname_Eng = new Zend_Dojo_Form_Element_TextBox('categoryname_eng');
		$Categoryname_Eng->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$Type = new Zend_Dojo_Form_Element_FilteringSelect('type');
		$Type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt= $db->getVewOptoinTypeByType(8,1);
		$Type->setMultiOptions($opt);
		$Type->setValue(1);
		
		$parent = new Zend_Dojo_Form_Element_TextBox('parent');
		$parent->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
				));
		$Date->setValue(date('Y-m-d'));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$display = new Zend_Dojo_Form_Element_FilteringSelect('display');
		$display->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		
		$opt= $db->getVewOptoinTypeByType(18,1);
		$display->setMultiOptions($opt);
		$display->setValue(1);
		
		
		$Status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$Status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt=array(1=>'Active',2=>'Deactive');
		$Status->setMultiOptions($opt);
		
		$id = new Zend_Form_Element_Hidden("id");
		
		if($data!=null){
				
			$Categoryname_kh->setValue($data['cate_namekh']);
			$Categoryname_Eng->setValue($data['cate_nameen']);
			$Type->setValue($data['parent_id']);
			$parent->setValue($data['account_type']);
			$Date->setValue($data['date']);
			$display->setValue($data['deplay']);
			$Status->setValue($data['status']);
			$id->setValue($data['id']);
		
	}	
		$this->addElements(array($Categoryname_kh,$Categoryname_Eng,$Type,
				$Date,$display,$Status,$parent,$id));
		return $this;
	}
}