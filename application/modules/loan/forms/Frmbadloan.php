<?php 
Class Loan_Form_Frmbadloan extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmBadLoan($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch');
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required' =>'true'
    	));
    	$rows = $db->getAllBranchName();
    	$options=array(''=>"---Select Branch Name---");
    	if(!empty($rows))
    		foreach($rows AS $row){
    		$options[$row['br_id']]=$row['branch_namekh'];
    	}
    	$_branch_id->setMultiOptions($options);
    	$_branch_id->setValue($request->getParam('branch'));
		
		$db = new Loan_Model_DbTable_DbBadloan();
		$client_code = new Zend_Dojo_Form_Element_FilteringSelect('client_code');
		$client_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getClientInfo();'
		));
		$opt= $db->getClientByTypes(1);
		$opt[0]='---Select Client Code---';
		$client_code->setMultiOptions($opt);
		$client_code->setValue($request->getParam('client_code'));
		
		$client_codeadd = new Zend_Dojo_Form_Element_FilteringSelect('client_codeadd');
		$client_codeadd->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getClientInfo();'
		));
		$opt= $db->getClientByTypesADD(1);
		$client_codeadd->setMultiOptions($opt);
		
		$client_name = new Zend_Dojo_Form_Element_FilteringSelect('client_name');
		$client_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			    'onchange'=>"getClientInfo(1);"
				));
		$options = $db->getClientByTypes(2);
		$options[0]='---Select Client Name---';
		$client_name->setMultiOptions($options);
		$client_name->setValue($request->getParam('client_name'));
		
		$client_nameadd = new Zend_Dojo_Form_Element_FilteringSelect('client_nameadd');
		$client_nameadd->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>"getClientInfo(1);"
		));
		$options = $db->getClientByTypesADD(2);
		$client_nameadd->setMultiOptions($options);
		
		$number_code = new Zend_Dojo_Form_Element_FilteringSelect('number_code');
		$number_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>"getClientInfo(2);"
		));
		
		$options = $db->getClientByTypes(3);
		$number_code->setMultiOptions($options);
		
		$_date= new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
		));
		$_date->setValue(date('Y-m-d'));
		
		
		$date_loss= new Zend_Dojo_Form_Element_DateTextBox('date_loss');
		$date_loss->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
		));
		$date_loss->setValue(date('Y-m-d'));
		
		$db = new Loan_Model_DbTable_DbBadloan();
		$total_amount = new Zend_Dojo_Form_Element_NumberTextBox('Total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
							
		));
		
		$interest_amount = new Zend_Dojo_Form_Element_NumberTextBox('Interest_amount');
		$interest_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
				
		));
	
		$_note = new Zend_Dojo_Form_Element_Textarea('Note');
		$_note ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>'width:98%',
				'required' =>'true'
		));
		
		$_term = new Zend_Dojo_Form_Element_FilteringSelect('Term');
		$_term->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$type_opt = array(
				''=>$this->tr->translate("---Select Long Term---"),
				1=>$this->tr->translate("Standard ,<= 10 Days"),
				2=>$this->tr->translate("Special Mention ,11-90 Days"),
				3=>$this->tr->translate("Substandard ,91-180 Days"),
				4=>$this->tr->translate("Doubtful ,181-360 Days"),
				5=>$this->tr->translate("Loan Loss ,>360 days"));
		 
		$_term->setMultiOptions($type_opt);
		$_term->setValue($request->getParam('Term'));
		
		$star_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$star_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox'));
		$date = $request->getParam("start_date");
		
		if(empty($date)){
			$date = date('Y-m-01');
		}
		$star_date->setValue($date);
		
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'required'=>'true',
				'class'=>'fullside',
		));
		$date = $request->getParam("end_date");
		
		if(empty($date)){
			$date = date("Y-m-d");
		}
		$_enddate->setValue($date);
		
		
		$cash_type = new Zend_Dojo_Form_Element_FilteringSelect('cash_type');
		$cash_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt = array(''=>"Select Currency Type",2=>"Dollar",1=>'Khmer',3=>"Bath");
		if($request->getActionName()!='index' AND $request->getActionName()!='rpt-loan-npl' ){
			unset($opt['']);
		}
		$cash_type->setMultiOptions($opt);
		$cash_type->setValue($request->getParam('cash_type'));
		
		
		$status = new Zend_Dojo_Form_Element_Textarea('status');
		$status ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>'width:98%'
		));
		 
		
		$_arr = array(''=>$this->tr->translate("ALL"),1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		if($request->getActionName()!='index'){
			unset($_arr['']);
		}
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
		$_status->setValue($request->getParam('status'));
		
		$id = new Zend_Form_Element_Hidden("id");
		$id_cient = new Zend_Form_Element_Hidden("idclient");
// 		print_r($data);exit();
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch'
		));
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$loan_level= new Zend_Dojo_Form_Element_TextBox("load_level");
		$loan_level->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside','readOnly'=>'readOnly'));
		
		$release_date = new Zend_Dojo_Form_Element_TextBox("release_date");
		$release_date->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside','readOnly'=>'readOnly'));
		
		$term_opt = $db->getVewOptoinTypeByType(14,1,3);
// 		print_r($term_opt);
		$_payterm = new Zend_Dojo_Form_Element_FilteringSelect('payment_term');
		$_payterm->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'readOnly'=>'readOnly'
		));
		$_payterm->setMultiOptions($term_opt);
		
		$total_amount_loan = new Zend_Dojo_Form_Element_TextBox("total_amount_loan");
		$total_amount_loan->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside','readOnly'=>'readOnly'));
		
		$_interest_rate = new Zend_Dojo_Form_Element_TextBox("interest_rate");
		$_interest_rate->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readOnly'=>'readOnly'
		));
		
		$loan_period = new Zend_Dojo_Form_Element_TextBox("loan_period");
		$loan_period->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'readOnly'=>'readOnly'));
		
		$options = $db->getAllPaymentMethod(null,1);
		$payment_method= new Zend_Dojo_Form_Element_FilteringSelect("payment_method");
		$payment_method->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside','readOnly'=>'readOnly'));
		$payment_method->setMultiOptions($options);
		
		$loannumber = new Zend_Dojo_Form_Element_TextBox("loannumber");
		$loannumber->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readOnly'=>'readOnly'
		));
		
		
		$_releasedate= new Zend_Dojo_Form_Element_DateTextBox('release_date');
		$_releasedate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
		));
		$_releasedate->setValue(date('Y-m-d'));
		
		$_enddate= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
		));
		$_enddate->setValue(date('Y-m-d'));
		
		
		if($data!=null){
				
			$_branch_id->setValue($data['branch']);
			$client_code->setValue($data['client_code']);
			$client_name->setValue($data['client_name']);
			$number_code->setValue($data['number_code']);
			$_date->setValue($data['date']);
			$date_loss->setValue($data['loss_date']);
			$total_amount->setValue($data['total_amount']);
			$interest_amount->setValue($data['intrest_amount']);
			$_term->setValue($data['tem']);
			$_note->setValue($data['note']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
			$id_cient->setValue($data['client_code']);
		}
		
		$this->addElements(array($_releasedate,$_enddate,$_payterm,$loannumber,$payment_method,$loan_period,$_interest_rate,$total_amount_loan,$loan_level,$_enddate,$star_date,$id_cient,$client_nameadd,$client_codeadd,$_btn_search,$_title,$_status,$cash_type,$id,$_branch_id,$client_code,$client_name,$number_code,$date_loss,$total_amount,$interest_amount,$_date,$_term,$_note));
		return $this;
		
	}	
}