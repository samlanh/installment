<?php 
Class Loan_Form_FrmLoan extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddLoan($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
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
		//$_house_price->setValue(5000);
		
		$other_fee = new Zend_Dojo_Form_Element_NumberTextBox('other_fee');
		$other_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				//'readonly'=>true,
		       'onkeyup'=>'calculateDiscount();',
				'class'=>'fullside',
		));

		$_total_sold = new Zend_Dojo_Form_Element_NumberTextBox('total_sold');
		$_total_sold->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
		));
		//$_total_sold->setValue(10000);
		
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
		if($request->getControllerName()=='repaymentschedule'){
			unset($opt[1]);
		}
		$schedule_opt->setMultiOptions($opt);
		
// 		$_customer_code = new Zend_Dojo_Form_Element_FilteringSelect('customer_code');
// 		$_customer_code->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'showPopupclient;'
// 		));
// 		$group_opt = $db->getGroupCodeById(1,0,1);//code,individual,option
// 		$_customer_code->setMultiOptions($group_opt);
		
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
				'onchange'=>'popupCheckCO();'
		));
		$options = $db->getAllCOName(1);
		$staff_id->setMultiOptions($options);
		
// 		$staff_ids = new Zend_Dojo_Form_Element_TextBox('gender');
// 		$staff_ids->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 		));
		
		$commission = new Zend_Dojo_Form_Element_NumberTextBox('commission');
		$commission->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$commission->setValue(0);
		
// 		$_loan_type = new Zend_Dojo_Form_Element_FilteringSelect('land_code');
// 		$_loan_type->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onChange'=>'getlandinfo();'
// 		));
// 		$opt = $db->getAllLandInfo();
// 		$_loan_type->setMultiOptions($opt);
		
// 		$_time_collect = new Zend_Dojo_Form_Element_NumberTextBox('amount_collect');
// 		$_time_collect->setAttribs(array(
// 				'dojoType'=>'dijit.form.NumberTextBox',
// 				'class'=>'fullside',
// 				'onkeyup'=>'getFirstPayment();'
// 		));
//  		$_time_collect->setValue(1);
 		
//  		$_time_collect_pri = new Zend_Dojo_Form_Element_NumberTextBox('amount_collect_pricipal');
//  		$_time_collect_pri->setAttribs(array(
//  				'dojoType'=>'dijit.form.NumberTextBox',
//  				'class'=>'fullside',
//  				'readonly'=>true,
//  				'required'=>true
//  		));
//  		$_time_collect_pri->setValue(0);
 		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('land_price');
		$_amount->setAttribs(array(
						'dojoType'=>'dijit.form.NumberTextBox',
						'class'=>'fullside',
						'required' =>'true',
						'readOnly'=>true,
		));
// 		$_amount->setValue(5000);
		
		$sold_price = new Zend_Dojo_Form_Element_NumberTextBox('sold_price');
		$sold_price->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onkeyup'=>'Balance();',
				'style'=>'color:red;',
				'readonly'=>true
		));
		
		$_rate =  new Zend_Dojo_Form_Element_NumberTextBox("interest_rate");
		$_rate->setAttribs(array(
				'data-dojo-Type'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'required':true,
				'name':'interest_rate',
				'class':'fullside',
				'onkeyup':'checkScheduleOption();',
				'invalidMessage':'អាចបញ្ជូលពី 1 ដល់'
				"));
				
		$_period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$_period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onkeyup'=>'checkScheduleOption();CalculateDate();'
				//'onkeyup'=>'calCulatePeriod();'
		));
		$_period->setValue(60);
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('release_date');
		$_releasedate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'checkReleaseDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		$_date_buy = new Zend_Dojo_Form_Element_DateTextBox('date_buy');
		$_date_buy->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'checkReleaseDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_date_buy->setValue(date("Y-m-d"));
		
// 		$_rate->setAttribs(array(
// 				'data-dojo-Type'=>'dijit.form.NumberTextBox',
// 				'data-dojo-props'=>"
// 				'required':true,
// 				'name':'interest_rate',
// 				'value':1.6,
// 				'class':'fullside',
// 				'invalidMessage':'អាចបញ្ជូលពី 1 ដល់'
// 				"));

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
				'required' =>'true',
				'readonly'=>true,
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		$discount = new Zend_Dojo_Form_Element_NumberTextBox('discount');
		$discount->setAttribs(array(
				
				'data-dojo-Type'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"regExp:'[0-9]{1,2}','required':true,
				'name':'discount',
				'onKeyup':'calculateDiscount();',
				'class':'fullside',
				'invalidMessage':'អាចបញ្ជូលពី 1 ដល់ 99'"			
		));
		$discount->setValue(0);
		
// 		$_collect_term = new Zend_Dojo_Form_Element_FilteringSelect('collect_termtype');
//  		$_collect_term->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'changeGraicePeroid();'	
// 		));
		$term_opt = $db->getVewOptoinTypeByType(14,1,3,1);
// 		$_collect_term->setMultiOptions($term_opt);
	
// 		$_payterm = new Zend_Dojo_Form_Element_FilteringSelect('payment_term');
// 		$_payterm->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true'
// 		));
// 		$_payterm->setMultiOptions($term_opt);
// 		$_pay_every = new Zend_Dojo_Form_Element_FilteringSelect('pay_every');
// 		$_pay_every->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required' =>'true',
// 				'class'=>'fullside',
// 				'onchange'=>'changeCollectType();'
// 		));
// 		$_pay_every->setValue(3);
// 		$_pay_every->setMultiOptions($term_opt);
// 		$_every_payamount = new Zend_Dojo_Form_Element_FilteringSelect('every_payamount');
// 		$_every_payamount->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true'
// 		));
// 		$options= array(2=>"After",1=>"Before",3=>"Normal");
// 		$_every_payamount->setMultiOptions($options);
		
// 		$_time= new Zend_Dojo_Form_Element_TextBox('time');
// 		$_time->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 		));
// 		$set_time='10:00-11:00 AM';
// 		$_time->setValue($set_time);
		
// 		$_paybefore = new Zend_Dojo_Form_Element_NumberTextBox('pay_before');
// 		$_paybefore->setAttribs(array(
// 				'dojoType'=>'dijit.form.NumberTextBox',
// 				'class'=>'fullside',
// 				'required' =>'true'	
// 		));
// 		$_paybefore->setValue(0);
		
// 		$_pay_late = new Zend_Dojo_Form_Element_NumberTextBox('pay_late');
// 		$_pay_late->setAttribs(array(
// 				'dojoType'=>'dijit.form.NumberTextBox',
// 				'class'=>'fullside',
// 				'required' =>'true'
// 		));
// 		$_pay_late->setValue(0);
// 		$arr=$db->getSystemSetting('interest_late');
// 		$_pay_late->setValue($arr['value']);
		

// 		$_repayment_method = new Zend_Dojo_Form_Element_FilteringSelect('repayment_method');
// 		$_repayment_method->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'required' =>'true',
// 				'class'=>'fullside',
// 				'onchange'=>'chechPaymentMethod()'
// 		));
// 		$options = $db->getAllPaymentMethod(null,1);
// 		$_repayment_method->setMultiOptions($options);

// 		$get_laonnumber = new Zend_Dojo_Form_Element_FilteringSelect('get_laonnumber');
// 		$get_laonnumber->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'getInfoByLoanNumber();getLoanInfoByLoanNumber();'
// 		));
// 		$group_opt = $db->getLoanAllLoanNumber(1,1);
// 		$get_laonnumber->setMultiOptions($group_opt);
		
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status_using');
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$options= array(1=>"Active",0=>"Cancel");
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
				'required' =>'true'
		));
		
		$note = new Zend_Dojo_Form_Element_TextBox("note");
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
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
		
		$_instalment_date = new Zend_Form_Element_Hidden("instalment_date");
		$_release_date = new Zend_Form_Element_Hidden("old_release_date");
		$_interest_rate = new Zend_Form_Element_Hidden("old_rate");
		$_old_payterm = new Zend_Form_Element_Hidden("old_payterm");
		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$receipt->setValue($data['receipt_no']);
			$discount->setValue($data['discount_amount']);
			$_loan_code->setValue($data['sale_number']);
			$schedule_opt->setValue($data['payment_id']);
			$paid->setValue($data["paid_amount"]);
			$balance->setValue($data['balance']);
			
			$_period->setValue($data['total_duration']);
			$_first_payment->setValue($data['first_payment']);
			$_rate->setValue($data['interest_rate']);//
			$_releasedate->setValue($data['startcal_date']);
			$other_fee->setValue($data['other_fee']);
			$_dateline->setValue(date("d/m/Y",strtotime($data['end_line'])));
			$_id->setValue($data['id']);
			$_status->setValue($data['status']);
// 			$_loan_type->setValue($data['land_id']);
			$note->setValue($data['note']);
			$commission->setValue($data['comission']);
			$staff_id->setValue($data['staff_id']);
			
			
		}
		$this->addElements(array($cheque,$paid_before,$balance_before,$receipt,$fixedpayment,$note,$other_fee,$_branch_id,$_date_buy,
				$_interest,$_service_charge,$schedule_opt,$_to_total_sold,$_total_sold,$_house_price,$balance,$paid,//$_loan_type,
// 				$_client_code,$_time_collect,$_paybefore,$staff_ids,$_pay_late,$_payterm,$_every_payamount,
// 				$_time,$_time_collect_pri,$_customer_code,$_repayment_method,$_pay_every,$_collect_term,
				$staff_id,$commission,$_amount,$_rate,$_releasedate,$_status,$discount,$_period,$_instalment_date,$_to_branch_id,
				$sold_price,$_old_payterm,$_interest_rate,$_release_date,$_first_payment,$_loan_code,$_dateline,$_id));
		return $this;
		
	}	
}