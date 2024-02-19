<?php 
Class Issue_Form_FrmGivehouse extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmGivehouse($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
	
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required' =>'true',
			'onchange'=>'getSaleClie();',
			'autoComplete'=>'false',
			'queryExpr'=> '*${0}*',
			
		));
		$rows_branch = $db->getAllBranchName();
		if(!empty($rows_branch))foreach($rows_branch AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
	
		$issue_date = new Zend_Dojo_Form_Element_DateTextBox('issue_date');
		$issue_date->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$s_date = date('Y-m-d');
		$issue_date->setValue($s_date);
	
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required' =>'true'
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($options);
		
		$payment_id = new Zend_Dojo_Form_Element_FilteringSelect('payment_id');
		$payment_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required' =>'true'
		));
		$options= array(1=>$this->tr->translate("IS_PAYOFF"),2=>$this->tr->translate("PAY_INSTALLMENT"));
		$payment_id->setMultiOptions($options);
		
					                    	
		$electric_start = new Zend_Dojo_Form_Element_NumberTextBox('electric_start');
		$electric_start->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'required' =>'true',
			'class'=>'fullside',
		));
		
		$water_start = new Zend_Dojo_Form_Element_NumberTextBox('water_start');
		$water_start->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'required' =>'true',
			'class'=>'fullside',
		));
		
		$note = new Zend_Dojo_Form_Element_NumberTextBox('note');
		$note->setAttribs(array(
			'dojoType'=>'dijit.form.Textarea',
			'class'=>'fullside',
			'style'=>'min-height: 60px;'
		));
		
		$contact_contruction = new Zend_Dojo_Form_Element_NumberTextBox('contact_contruction');
		$contact_contruction->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'required' =>'true',
				'class'=>'fullside',
				'style'=>'min-height: 60px;'
		));
	
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			$issue_date->setValue($data['issue_date']);
			$water_start->setValue($data['water_start']);
			$electric_start->setValue($data['electric_start']);
			$_status->setValue($data['status']);
			$note->setValue($data['note']);
			$contact_contruction->setValue($data['contact_construction']);
			$payment_id->setValue($data['payment_id']);
		}
		$this->addElements(array($payment_id,$contact_contruction,$note,$electric_start,$water_start,$branch_id,$issue_date,$_status));
		return $this;
	}
}