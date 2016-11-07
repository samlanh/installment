<?php 
Class Callecterall_Form_Frmsalecallecterall extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		
	}
	public function Frmsalecallecterall(){
		
		$branch = new Zend_Dojo_Form_Element_FilteringSelect('branch');
		$branch->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		
		$callecterall_code = new Zend_Dojo_Form_Element_FilteringSelect('callecterall_code');
		$callecterall_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$client_id = new Zend_Dojo_Form_Element_FilteringSelect('client_id');
		$client_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
				));
		
		$callecterall_type = new Zend_Dojo_Form_Element_FilteringSelect('callecterall_type');
		$callecterall_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		
		$sale_amount = new Zend_Dojo_Form_Element_FilteringSelect('sale_amount');
		$sale_amount->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		
		$date = new Zend_Dojo_Form_Element_TextBox('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$note= new Zend_Dojo_Form_Element_FilteringSelect('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		
	
		$this->addElements(array($branch,$note,$sale_amount,$callecterall_type,$date,$client_id,$callecterall_code));
		return $this;
		
	}	
}