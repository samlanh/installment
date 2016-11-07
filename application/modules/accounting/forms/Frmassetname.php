<?php 
Class Accounting_Form_Frmassetname extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmFixedAsset($data=null){
		
		$asset_name_khmer = new Zend_Dojo_Form_Element_ValidationTextBox('asset_name_khmer');
		$asset_name_khmer->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
				));
		$asset_name_english = new Zend_Dojo_Form_Element_ValidationTextBox('asset_name_english');
		$asset_name_english->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		
		$display_type = new Zend_Dojo_Form_Element_FilteringSelect('display_type');
		$display_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt=array(1=>'As khmer',2=>'As english');
		$display_type->setMultiOptions($opt);
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('Date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
				));
		$Date->setValue(date("Y-m-d"));
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt =array(1=>"Active",0=>"Deactive");
		$status->setMultiOptions($opt);
		
		$_id = new Zend_Form_Element_Hidden('id');
		
		$this->addElements(array($asset_name_khmer,$asset_name_english,$display_type,$Date,$status));
		return $this;
		
	}	
}