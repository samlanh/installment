<?php 
Class Invest_Form_FrmWithdraw extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddWithdraw($data=null){
	
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		$dbwithdraw = new Invest_Model_DbTable_DbWithdraw();
		$receiptNO = $dbwithdraw->getReceiptNO();
		$_receipt_no= new Zend_Dojo_Form_Element_TextBox('receipt_no');
		$_receipt_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red;',
				'readOnly'=>'true'
		)
		);
		$_receipt_no->setValue($receiptNO);
		
		$_investment_id = new Zend_Dojo_Form_Element_FilteringSelect('investment_id');
		$_investment_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'onChange'=>'getAllLaonPayment(),getInvesthaswithdraw(), getLaonPayment();',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(0=>$this->tr->translate("CHOOSE_INVESTMENT_INVESTOR"));
		$rows = $dbwithdraw->getAllInvestment(0);
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_investment_id->setMultiOptions($opt_doc);
		
		$_investor_id = new Zend_Form_Element_Hidden("investor_id");
		$_investor_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		)
		);
		
		$_datenext = date("Y-m-d",strtotime("+1 day"));
		$_paid_date= new Zend_Dojo_Form_Element_DateTextBox('paid_date');
		$_paid_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{max:'$_datenext',datePattern:'dd/MM/yyyy'}",
		)
		);
		$_paid_date->setValue(date("Y-m-d"));
		
		$option_pay = new Zend_Dojo_Form_Element_FilteringSelect('option_pay');
		$option_pay->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'OnChange'=>'payOption();'
		));
		$option_status = array(1=>$this->tr->translate("NORMAL_WITHDRAW"),2=>$this->tr->translate("PRINCIPAL_WITHDRAW"),3=>$this->tr->translate("PAYOFF_WITHDRAW"));
		$option_pay->setMultiOptions($option_status);
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_method->setMultiOptions($opt);
		
		$_cheque = new Zend_Dojo_Form_Element_TextBox('cheque');
		$_cheque->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_payment_date= new Zend_Dojo_Form_Element_DateTextBox('payment_date');
		$_payment_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		)
		);
		
		$_principle_paid = new Zend_Dojo_Form_Element_NumberTextBox('principle_paid');
		$_principle_paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'required'=>true,
		));
		
		
		$_interest_paid = new Zend_Dojo_Form_Element_NumberTextBox('interest_paid');
		$_interest_paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'onKeyup'=>'doTotalByServ();',
				'style'=>'color:red;',
				'required'=>true,
		));
		
		$_total_payment = new Zend_Dojo_Form_Element_NumberTextBox('total_payment');
		$_total_payment->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'style'=>'color:red;',
				'required'=>true,
				'readOnly'=>'readOnly'
		));

		$_recieve_amount = new Zend_Dojo_Form_Element_NumberTextBox('recieve_amount');
		$_recieve_amount->setAttribs(array(
				'dojoType'	=>	'dijit.form.NumberTextBox',
				'class'		=>	'fullside',
				'onKeyup'	=>	'totalReturn();',
				'style'		=>	'color:red;',
				'required'	=>	true,
		
		));
		
		$remain= new Zend_Dojo_Form_Element_NumberTextBox("remain");
		$remain->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside','readOnly'=>'readOnly'));
		
		$times = new Zend_Dojo_Form_Element_TextBox("times");
		$times->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly' =>'readOnly',
		));
		
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; min-height:50px;'
		));
		
		$_id = new Zend_Form_Element_Hidden("id");
		$_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				)
		);
		
		$_investor_search = new Zend_Dojo_Form_Element_FilteringSelect('investor_search');
		$_investor_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(0=>$this->tr->translate("CHOOSE_INVESTOR"));
		$rows = $db->getAllInvestor();
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_investor_search->setMultiOptions($opt_doc);
		$_investor_search->setValue($request->getParam("investor_search"));
		
		if($data!=null){
			
			$_receipt_no->setValue($data['id']);
			$_investment_id->setValue($data['id']);
			$_investor_id->setValue($data['id']);
			$_paid_date->setValue($data['id']);
			$option_pay->setValue($data['id']);
			$payment_method->setValue($data['id']);
			$_cheque->setValue($data['id']);
			$_payment_date->setValue($data['id']);
			$_principle_paid->setValue($data['id']);
			$_interest_paid->setValue($data['id']);
			$_total_payment->setValue($data['id']);
			$_recieve_amount->setValue($data['id']);
			$remain->setValue($data['id']);
			$_note->setValue($data['id']);
			$_id->setValue($data['id']);
			
		}
		$this->addElements(array(
				$_receipt_no,
				$_investment_id,
				$_investor_id,
				$_paid_date,
				$option_pay,
				$payment_method,
				$_cheque,
				$_payment_date,
				$_principle_paid,
				$_interest_paid,
				$_total_payment,
				$_recieve_amount,
				$remain,
				$times,
				$_note,
				$_id,
				
				$_investor_search
		));
		return $this;
	}
	
	
	public function FrmAddWithdrawBroker($data=null){
	
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
	
		$dbwithdraw = new Invest_Model_DbTable_DbWithdrawBroker();
		$receiptNO = $dbwithdraw->getReceiptNO();
		$_receipt_no= new Zend_Dojo_Form_Element_TextBox('receipt_no');
		$_receipt_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red;',
				'readOnly'=>'true'
		)
		);
		$_receipt_no->setValue($receiptNO);
	
		$_investment_id = new Zend_Dojo_Form_Element_FilteringSelect('investment_id');
		$_investment_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'onChange'=>'getAllLaonPayment(),getInvesthaswithdraw(), getLaonPayment();',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(0=>$this->tr->translate("CHOOSE_INVESTMENT_BROKER"));
		$rows = $dbwithdraw->getAllBroker(0);
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_investment_id->setMultiOptions($opt_doc);
	
		$_broker_id = new Zend_Form_Element_Hidden("broker_id");
		$_broker_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		)
		);
	
		$_datenext = date("Y-m-d",strtotime("+1 day"));
		$_paid_date= new Zend_Dojo_Form_Element_DateTextBox('paid_date');
		$_paid_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{max:'$_datenext',datePattern:'dd/MM/yyyy'}",
		)
		);
		$_paid_date->setValue(date("Y-m-d"));
	
		$option_pay = new Zend_Dojo_Form_Element_FilteringSelect('option_pay');
		$option_pay->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'OnChange'=>'payOption();'
		));
		$option_status = array(1=>$this->tr->translate("NORMAL_WITHDRAW"),3=>$this->tr->translate("PAYOFF_BROKER_WITHDRAW"));
		$option_pay->setMultiOptions($option_status);
	
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_method->setMultiOptions($opt);
	
		$_cheque = new Zend_Dojo_Form_Element_TextBox('cheque');
		$_cheque->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		$_payment_date= new Zend_Dojo_Form_Element_DateTextBox('payment_date');
		$_payment_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		)
		);
	
		$_principle_paid = new Zend_Dojo_Form_Element_NumberTextBox('principle_paid');
		$_principle_paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'required'=>true,
		));
	
	
		$_interest_paid = new Zend_Dojo_Form_Element_NumberTextBox('interest_paid');
		$_interest_paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'onKeyup'=>'doTotalByServ();',
				'style'=>'color:red;',
				'required'=>true,
		));
	
		$_total_payment = new Zend_Dojo_Form_Element_NumberTextBox('total_payment');
		$_total_payment->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'style'=>'color:red;',
				'required'=>true,
				'readOnly'=>'readOnly'
		));
	
		$_recieve_amount = new Zend_Dojo_Form_Element_NumberTextBox('recieve_amount');
		$_recieve_amount->setAttribs(array(
				'dojoType'	=>	'dijit.form.NumberTextBox',
				'class'		=>	'fullside',
				'onKeyup'	=>	'totalReturn();',
				'style'		=>	'color:red;',
				'required'	=>	true,
	
		));
	
		$remain= new Zend_Dojo_Form_Element_NumberTextBox("remain");
		$remain->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside','readOnly'=>'readOnly'));
	
		$times = new Zend_Dojo_Form_Element_TextBox("times");
		$times->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly' =>'readOnly',
		));
	
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; min-height:50px;'
		));
	
		$_id = new Zend_Form_Element_Hidden("id");
		$_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		)
		);
	
		$_broker_search = new Zend_Dojo_Form_Element_FilteringSelect('broker_search');
		$_broker_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(0=>$this->tr->translate("CHOOSE_BROKER"));
		$rows = $db->getAllBroker();
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_broker_search->setMultiOptions($opt_doc);
		$_broker_search->setValue($request->getParam("broker_search"));
	
		if($data!=null){
				
			$_receipt_no->setValue($data['id']);
			$_investment_id->setValue($data['id']);
			$_broker_id->setValue($data['id']);
			$_paid_date->setValue($data['id']);
			$option_pay->setValue($data['id']);
			$payment_method->setValue($data['id']);
			$_cheque->setValue($data['id']);
			$_payment_date->setValue($data['id']);
			$_principle_paid->setValue($data['id']);
			$_interest_paid->setValue($data['id']);
			$_total_payment->setValue($data['id']);
			$_recieve_amount->setValue($data['id']);
			$remain->setValue($data['id']);
			$_note->setValue($data['id']);
			$_id->setValue($data['id']);
				
		}
		$this->addElements(array(
				$_receipt_no,
				$_investment_id,
				$_broker_id,
				$_paid_date,
				$option_pay,
				$payment_method,
				$_cheque,
				$_payment_date,
				$_principle_paid,
				$_interest_paid,
				$_total_payment,
				$_recieve_amount,
				$remain,
				$times,
				$_note,
				$_id,
	
				$_broker_search
		));
		return $this;
	}
	
}