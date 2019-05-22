<?php 
Class Loan_Form_Frmreceiveplong extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddFrmCancel($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_loan_code = new Zend_Dojo_Form_Element_TextBox('loan_code');
		$_loan_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readonly'=>true,
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$loan_number = $db->getLoanNumber();
		$_loan_code->setValue($loan_number);
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getSaleClie();'
		));
		$rows_branch = $db->getAllBranchName();
		if(!empty($rows_branch))foreach($rows_branch AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		
		$_property = new Zend_Dojo_Form_Element_FilteringSelect('property');
		$_property->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$property_opt = array("","PROPERTY");
		$_property->setMultiOptions($property_opt);
		
		$buy_date = new Zend_Dojo_Form_Element_DateTextBox('date');
		$buy_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$s_date = date('Y-m-d');
		$buy_date->setValue($s_date);
		
		$_id = new Zend_Form_Element_Hidden('id');
		$_property_id = new Zend_Form_Element_Hidden("property_id");
		$_old_property_id = new Zend_Form_Element_Hidden("customer_id");
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'));
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($options);
		
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			$_old_property_id->setValue($data['house_id']);
			$buy_date->setValue($data['date']);
			$_id->setValue($data['id']);
			$_status->setValue($data['status']);
		}
		$this->addElements(array($branch_id,$_property,$buy_date,$_status,
				$_old_property_id,$_loan_code,$_id));
		return $this;
		
	}	
}