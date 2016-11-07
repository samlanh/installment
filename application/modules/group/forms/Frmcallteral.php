<?php 
Class Group_Form_Frmcallteral extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddCallteral($data=null){
		
		$customer_name = new Zend_Dojo_Form_Element_TextBox('CO_name');
		$customer_name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'
				));
		
		$first_name = new Zend_Dojo_Form_Element_ValidationTextBox('first_name');
		$first_name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'required'=>true,
				'class'=>'fullside'
		));
		
		$_customer_code = new Zend_Dojo_Form_Element_TextBox('customer_code');
		$_customer_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
			
		));
		
		$is_new = new Zend_Dojo_Form_Element_RadioButton('is_new');
		$is_new->setAttribs(array(
				'dojoType'=>'dijit.form.RadioButton',
				'class'=>'fullside',
				
		));
		
		$description = new Zend_Dojo_Form_Element_Textarea('note');
		$description->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>'width:98%;',
		));
		
		$total_amount = new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
					
		));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$_branch_id->setMultiOptions(array(1=>'Null'));
	
		
		
		$_member = new Zend_Dojo_Form_Element_FilteringSelect('group_id');
		$_member->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$_dob = new Zend_Dojo_Form_Element_DateTextBox('date');
		$_dob->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
		));
		
		$group_id=new Zend_Dojo_Form_Element_FilteringSelect('group_id');
		$group_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
				));
		
		
		$id = new Zend_Form_Element_Hidden("id");
		
		if($data!=null){
				
			$_branch_id->setValue($data['branch_id']);
			$account_id->setValue($data['account_id']);
			$total_amount->setValue($data['total_amount']);
			$for_date->setValue($data['fordate']);
			$_Description->setValue($data['disc']);
			$_Date->setValue($data['date']);
			$_stutas->setValue($data['status']);
			$id->setValue($data['id']);
				
		
				
		}
		
		$this->addElements(array($customer_name,$first_name,$customer_name,$is_new,$_branch_id,$_dob,
				$total_amount,$description,$_customer_code,$group_id,$id));
		return $this;
		
	}	
}