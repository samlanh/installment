<?php 
Class Loan_Form_FrmLoan extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddLoan($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$userInfo = $db->getUserInfo();
		$userLevel = empty($userInfo['level'])?0:$userInfo['level'];
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'filterClient();'
		));
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);	

		$_to_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('to_branch_id');
		$_to_branch_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'onchange'=>'getAllPropertyBranchTransfer();'
		));
		$options = $db->getAllBranchName(null,1);
		$_to_branch_id->setMultiOptions($options);
		
		$_loan_code = new Zend_Dojo_Form_Element_TextBox('sale_code');
		$_loan_code->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'readonly'=>true,
			'class'=>'fullside',
			'style'=>'color:red; font-weight: bold;'
		));
		$loan_number = $db->getLoanNumber();
		$_loan_code->setValue($loan_number);
		
		$receipt = new Zend_Dojo_Form_Element_TextBox('receipt');
		$receipt->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'readonly'=>true,
			'class'=>'fullside',
			'style'=>'color:red; font-weight: bold;'
		));
		
		$receipt_no = $db->getReceiptByBranch();
		$receipt->setValue($receipt_no);
		
		$_house_price = new Zend_Dojo_Form_Element_NumberTextBox('house_price');
		$_house_price->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'readonly'=>true,
			'class'=>'fullside',
		));
		
		$other_fee = new Zend_Dojo_Form_Element_NumberTextBox('other_fee');
		$other_fee->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
	        'onkeyup'=>'calculateTotalremain();',
		    'class'=>'fullside',
		));
		
		$other_feenote = new Zend_Dojo_Form_Element_TextBox('other_feenote');
		$other_feenote->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
		));

		$_total_sold = new Zend_Dojo_Form_Element_NumberTextBox('total_sold');
		$_total_sold->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'readonly'=>true,
		));
		
		$_to_total_sold = new Zend_Dojo_Form_Element_NumberTextBox('to_total_sold');
		$_to_total_sold->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'readonly'=>true,
		));
		
		$schedule_opt = new Zend_Dojo_Form_Element_FilteringSelect('schedule_opt');
		$schedule_opt->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'onchange'=>'checkScheduleOption();'
		));
		$opt = $db->getVewOptoinTypeByType(25,1,null,1);
		$request=Zend_Controller_Front::getInstance()->getRequest();
		if($request->getControllerName()=='newschedule'){
			unset($opt[1]);unset($opt[2]);
		}
		$schedule_opt->setMultiOptions($opt);
		
		$paid = new Zend_Dojo_Form_Element_NumberTextBox('deposit');
		$paid->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'onkeyup'=>'Balance();',
			'required'=>true,
		));		
		
		$balance = new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$balance->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'readonly'=>true,
		));
		
		$staff_id = new Zend_Dojo_Form_Element_FilteringSelect('staff_id');
		$staff_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>false,
			'queryExpr'=>'*${0}*', 
			'onchange'=>'popupCheckCO();'
		));
// 		$options = $db->getAllCOName(1);
		$rowss = $db->getAllCOName();
		$optionsCo=array(''=>$this->tr->translate("SELECT_SALE_AGENT"));
		if(!empty($rowss))foreach($rowss AS $row){
			$optionsCo[$row['id']]=$row['name'];
		}
		$staff_id->setMultiOptions($options);
		
		$receivedopt = array(1=>$this->tr->translate("BY_SCHEDULE_DATE"),0=>$this->tr->translate("RECEIVED_PROPERTY"),2=>$this->tr->translate("RECEIVED_HOUSE"));
		$paid_receivehouse = new Zend_Dojo_Form_Element_FilteringSelect('paid_receivehouse');
		$paid_receivehouse->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
		));
		$paid_receivehouse->setMultiOptions($receivedopt);
		
		$commission = new Zend_Dojo_Form_Element_NumberTextBox('commission');
		$commission->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
		));
		$commission->setValue(0);
		
		$full_commission = new Zend_Dojo_Form_Element_NumberTextBox('full_commission');
		$full_commission->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required'=>true
		));
		$full_commission->setValue(0);
		
		$amount_build = new Zend_Dojo_Form_Element_NumberTextBox('amount_build');
		$amount_build->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'onkeyup'=>'getFirstPayment();'
		));
		$amount_build->setValue(24);
		
 		$start_building = new Zend_Dojo_Form_Element_DateTextBox('start_building');
 		$start_building->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
 		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('land_price');
		$_amount->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required' =>'true',
		));
		
		$sold_price = new Zend_Dojo_Form_Element_NumberTextBox('sold_price');
		$sold_price->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required' =>'true',
			'onkeyup'=>'Balance();',
			'style'=>'color:red;',
			'readonly'=>true
		));
		
		$_rate =  new Zend_Dojo_Form_Element_FilteringSelect("interest_rate");
		$_rate->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>false,
				'name'=>'interest_rate',
				'class'=>'fullside',
				'onchange'=>'checkScheduleOption();',
			));
		$interest_opt = $db->getAllInterestrate();
		$_rate->setMultiOptions($interest_opt);
				
		$_period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$_period->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'onkeyup'=>'checkScheduleOption();CalculateDate();'
		));
		
		$last_payment = new Zend_Dojo_Form_Element_NumberTextBox('last_payment');
		$last_payment->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onkeyup'=>'calCulateSchedulePayment();',
		));
		$last_payment->setValue(0);
		
		$agreementdate = new Zend_Dojo_Form_Element_DateTextBox('agreement_date');
		$agreementdate->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$agreementdate->setValue(date("Y-m-d"));
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('release_date');
		$_releasedate->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'onchange'=>'checkReleaseDate();',
			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		
		$cdate=date("Y-m-d");
		if (!empty($data['date_input'])){
			$cdate=date("Y-m-d",strtotime($data['date_input']));
		}
		$paymentDateEnable="false";
		$constraintsDate="";
		if (DISABLE_PAYMENT_DATE==1){
			$paymentDateEnable = "true";
		}else if (DISABLE_PAYMENT_DATE==2){
			$constraintsDate="min:'$cdate',";
		}else if (DISABLE_PAYMENT_DATE==3){
			$constraintsDate="max:'$cdate',";
		}else if (DISABLE_PAYMENT_DATE==4){
			$constraintsDate="min:'$cdate',max:'$cdate',";
		}
		$paid_date = new Zend_Dojo_Form_Element_DateTextBox('paid_date');
		$paid_date->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'constraints'=>"{".$constraintsDate."datePattern:'dd/MM/yyyy'}",
			'readOnly' =>$paymentDateEnable,
		));
		$paid_date->setValue(date("Y-m-d"));
		
		
		$_date = date("Y-m-d");
		$_date_buy = new Zend_Dojo_Form_Element_DateTextBox('date_buy');
		$_date_buy->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'onchange'=>'checkReleaseDate();',
			'constraints'=>"{max:'$_date',datePattern:'dd/MM/yyyy'}",
		));
		$_date_buy->setValue(date("Y-m-d"));
		
		$s_date = date('Y-m-d');
		$_releasedate->setValue($s_date);
		
		$_first_payment = new Zend_Dojo_Form_Element_DateTextBox('first_payment');
		$_first_payment->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		    'onchange'=>'calCulateEndDate();'
		));
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('date_line');
		$_dateline->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'class'=>'fullside',
			'required' =>'false',
			'readonly'=>true,
			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
		$discount = new Zend_Dojo_Form_Element_NumberTextBox('discount');
		$discount->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'onKeyup'=>'calculateDiscount();',
			'class'=>'fullside50 fullside',
			'placeHolder'=>'ជាតម្លៃ',
			'invalidMessage'=>'អាចបញ្ជូលពី 1 ដល់ 99'		
		));
		
		$discount_percent = new Zend_Dojo_Form_Element_NumberTextBox('discount_percent');
		$discount_percent->setAttribs(array(
			'data-dojo-Type'=>'dijit.form.NumberTextBox',
			'data-dojo-props'=>"constraints:{min:0,max:100},
			'name':'discount_percent',
			'id':'discount_percent',
			'onKeyup':'calculateDiscount();',
			'class':'fullside fullside50',
			'placeHolder':'ភាគរយ%',
			'invalidMessage':'អាចបញ្ជូលពី 1 ដល់ 99'"
		));
		
		$total_discount = new Zend_Dojo_Form_Element_NumberTextBox('total_discount');
		$total_discount->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'placeHolder'=>'',
			'Readonly'=>true
		));
		$term_opt = $db->getVewOptoinTypeByType(14,1,3,1);
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status_using');
		$_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required' =>'true'
		));
		$options= array(1=>"ប្រើប្រាស់",0=>"បោះបង់");
		$_status->setMultiOptions($options);
		
		$_interest = new Zend_Dojo_Form_Element_TextBox("interest");
		$_interest->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
			'required' =>'true'
		));
		
		$fixedpayment = new Zend_Dojo_Form_Element_NumberTextBox("fixed_payment");
		$fixedpayment->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required' =>'true',
			'onkeyup'=>'calculateDuration(1)'
		));
		
		$note = new Zend_Dojo_Form_Element_Textarea("note");
		$note->setAttribs(array(
			'dojoType'=>'dijit.form.Textarea',
			'class'=>'fullside',
			'style'=>'width:100%;min-height:103px; font-size:14px; font-family:Kh Battambang'
		));
		
		$cheque = new Zend_Dojo_Form_Element_TextBox("cheque");
		$cheque->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
		));
		
		$_service_charge = new Zend_Dojo_Form_Element_TextBox("service_charge");
		$_service_charge->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
			'required' =>'true'
		));
		
		$paid_before = new Zend_Dojo_Form_Element_NumberTextBox('paid_before');
		$paid_before->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
		));
		
		$balance_before = new Zend_Dojo_Form_Element_NumberTextBox('balance_before');
		$balance_before->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
		));
		
		$typesale = new Zend_Dojo_Form_Element_FilteringSelect('typesale');
		$typesale->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'onchange'=>'resetSale();'
		));
		$options= array(1=>$this->tr->translate("NORMAL_SALE"),2=>$this->tr->translate("MULTY_SALE"));
		$typesale->setMultiOptions($options);
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_method->setMultiOptions($opt);
		
		$_instalment_date = new Zend_Form_Element_Hidden("instalment_date");
		$_release_date = new Zend_Form_Element_Hidden("old_release_date");
		$_interest_rate = new Zend_Form_Element_Hidden("old_rate");
		$_old_payterm = new Zend_Form_Element_Hidden("old_payterm");
		$_id = new Zend_Form_Element_Hidden('id');
		
		$second_depostit = new Zend_Dojo_Form_Element_NumberTextBox('second_depostit');
		$second_depostit->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required'=>true
		));
		$second_depostit->setValue(0);
		
		$delay_day = new Zend_Dojo_Form_Element_NumberTextBox('delay_day');
		$delay_day->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required'=>true
		));
		$delay_day->setValue(0);
		
		$note_agreement = new Zend_Dojo_Form_Element_Textarea("note_agreement");
		$note_agreement->setAttribs(array(
			'dojoType'=>'dijit.form.Textarea',
			'class'=>'fullside',
			'style'=>'width:100%;min-height:103px; font-size:14px; font-family:Kh Battambang'
		));
		
		$propertiestype = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'required'=>false,
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside','onChange'=>'filterClient();'
		));
		$propertiestype_opt = $db->getPropertyTypeForsearch();
		$propertiestype->setMultiOptions($propertiestype_opt);
		
		$times_commission = new Zend_Dojo_Form_Element_NumberTextBox('times_commission');
		$times_commission->setAttribs(array(
			'data-dojo-Type'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'placeHolder'=>'ចំ.ដង',
			'onkeyup'=>'revertCommission(1);'
		));
		
		$commision_amt = new Zend_Dojo_Form_Element_NumberTextBox('commission_amt');
		$commision_amt->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'placeHolder'=>'ចំ.ប្រាក់',
			'onkeyup'=>'revertCommission(2);'
		));
		
		$other_discount = new Zend_Dojo_Form_Element_NumberTextBox('other_discount');
		$other_discount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'onKeyup'=>'calculateDiscount();',
				'class'=>'fullside',
				'placeHolder'=>$this->tr->translate("OTHER_DISCOUNT"),
				'invalidMessage'=>'អាចបញ្ជូលពី 1 ដល់ 99'
		));
		$other_discount->setValue(0);
		
		$grace_period = new Zend_Dojo_Form_Element_NumberTextBox('grace_period');
		$grace_period->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
		));
		$grace_period->setValue(0);
		if($data!=null){
			$agreementdate->setValue($data['agreement_date']);
			$_branch_id->setValue($data['branch_id']);
			$receipt->setValue($data['receipt_no']);
			$discount->setValue($data['discount_amount']);
			$discount_percent->setValue($data['discount_percent']);
			$_loan_code->setValue($data['sale_number']);
			$schedule_opt->setValue($data['payment_id']);
			$paid->setValue($data["paid_amount"]);
			$balance->setValue($data['balance']);
			$paid_date->setValue($data['date_input']);
			$_period->setValue($data['total_duration']);
			$_first_payment->setValue($data['first_payment']);
			$_rate->setValue($data['interest_rate']);//
			$_releasedate->setValue($data['startcal_date']);
			$other_fee->setValue($data['other_fee']);
			$_dateline->setValue($data['end_line']);
			$_id->setValue($data['id']);
			$_status->setValue($data['status']);
			$sold_price->setValue($data['price_sold']);
			$note->setValue($data['note']);
			$commission->setValue($data['comission']);
			$rs = $db->getUserInfo();
			if($rs['level']!=1){
				//$data['full_commission'] = 0;
			}
			$full_commission->setValue($data['full_commission']);
			
			$staff_id->setValue($data['staff_id']);
			
			$start_building->setValue($data['build_start']);
			$amount_build->setValue($data['amount_build']);
			$note_agreement->setValue($data['note_agreement']);
			if (!empty($data['buy_date'])){
				$_date_buy->setValue($data['buy_date']);
			}
			if (!empty($data['second_depostit'])){
				$second_depostit->setValue($data['second_depostit']);
			}
			if (!empty($data['other_discount'])){
				$other_discount->setValue($data['other_discount']);
			}
		}
		$this->addElements(array($grace_period,$commision_amt,$times_commission,$last_payment,$paid_date,$note_agreement,$total_discount,$delay_day,$full_commission,$payment_method,$other_feenote,$start_building,$amount_build,$typesale,$paid_receivehouse,$agreementdate,$discount_percent,$cheque,$paid_before,$balance_before,$receipt,$fixedpayment,$note,$other_fee,$_branch_id,$_date_buy,
				$_interest,$_service_charge,$schedule_opt,$_to_total_sold,$_total_sold,$_house_price,$balance,$paid,
				$staff_id,$commission,$_amount,$_rate,$_releasedate,$_status,$discount,$_period,$_instalment_date,$_to_branch_id,
				$sold_price,$_old_payterm,$_interest_rate,$_release_date,$_first_payment,$_loan_code,$_dateline,$_id,
				$second_depostit,
				$propertiestype,
				$other_discount
				));
		return $this;
		
	}	
}