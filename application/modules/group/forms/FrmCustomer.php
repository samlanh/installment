<?php 
Class Group_Form_FrmCustomer extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddCustomer($data=null){		
		

		$_name = new Zend_Dojo_Form_Element_TextBox('name');
		$_name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_date = new Zend_Dojo_Form_Element_DateTextBox('date');
		$_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
		));

		$_from_price = new Zend_Dojo_Form_Element_NumberTextBox('from_price');
		$_from_price->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));


		$_to_price = new Zend_Dojo_Form_Element_NumberTextBox('to_price');
		$_to_price->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_requirement = new Zend_Dojo_Form_Element_TextBox('requirement');
		$_requirement->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_type = new Zend_Dojo_Form_Element_TextBox('type');
		$_type->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_description = new Zend_Dojo_Form_Element_Textarea('description');
		$_description->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:98%;min-height:60px; font-size:inherit; font-family:Kh Battambang'
		));
		
		
	
		if($data!=null){
			
			$_name->setValue($data['name']);
			$_phone->setValue($data['phone']);
			$_date->setValue($data['date']);
			$_from_price->setValue($data['from_price']);
			$_to_price->setValue($data['to_price']);
			$_requirement->setValue($data['requirement']);
			$_type->setValue($data['type']);
			$_description->setValue($data['description']);

		}
		$this->addElements(array($_name,$_phone,$_date,$_from_price,$_to_price,$_requirement,$_type,$_description));
		return $this;
		
	}	
}