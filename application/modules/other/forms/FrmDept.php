<?php 
Class global_Form_FrmDept extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmDeptment($data=null){
		
		$_en_name = new Zend_Dojo_Form_Element_TextBox('en_name');
		$_en_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'
				));
		//$_dept->setLabel('សូមបញ្ចូលឈ្មោះមហាវិទ្យាល័យ');
		$_kh_name = new Zend_Dojo_Form_Element_TextBox('kh_name');
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
		
		$this->addElements(array($_en_name,$_kh_name, $_status));
		return $this;
		
	}
	
	
}