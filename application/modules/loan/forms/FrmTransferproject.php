<?php 
Class Loan_Form_FrmTransferproject extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmTransferProject($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();setBranchID();'
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
		       'onkeyup'=>'calculateDiscount();',
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
		if($request->getControllerName()=='repaymentschedule'){
			unset($opt[1]);
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
				'onchange'=>'popupCheckCO();'
		));
		$options = $db->getAllCOName(1);
		$staff_id->setMultiOptions($options);
		
		$commission = new Zend_Dojo_Form_Element_NumberTextBox('commission');
		$commission->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$commission->setValue(0);
 		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('land_price');
		$_amount->setAttribs(array(
						'dojoType'=>'dijit.form.NumberTextBox',
						'class'=>'fullside',
						'required' =>'true',
						'readOnly'=>true,
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
		
		$_rate =  new Zend_Dojo_Form_Element_NumberTextBox("interest_rate");
		$_rate->setAttribs(array(
				'data-dojo-Type'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'required':true,
				'name':'interest_rate',
				'value':12,
				'class':'fullside',
				'onkeyup':'checkScheduleOption();',
				'invalidMessage':'អាចបញ្ជូលពី 1 ដល់'"));
				
		$_period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$_period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onkeyup'=>'checkScheduleOption();'
		));
		$_period->setValue(24);
		
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
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>'true',
				'class'=>'fullside',
				'onKeyup'=>'calculateDiscount();'
		));
		$discount->setValue(0);
		
		$term_opt = $db->getVewOptoinTypeByType(14,1,3,1);
		
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
// 			$_loan_code->setValue($data['sale_number']);
// 			$receipt->setValue($data['receipt_no']);
			$_branch_id->setValue($data['from_branchid']);
// 			$discount->setValue($data['discount_amount']);
			
// 			$_date_buy->setValue($data['buy_date']);
// 			$schedule_opt->setValue($data['payment_id']);
// 			$paid->setValue($data["paid_amount"]);
// 			$balance->setValue($data['balance']);
			
// 			$_period->setValue($data['total_duration']);
// 			$_first_payment->setValue($data['first_payment']);
// 			$_rate->setValue($data['interest_rate']);//
// 			$_releasedate->setValue($data['startcal_date']);
// 			$other_fee->setValue($data['other_fee']);
// 			//$_dateline->setValue(date("d/m/Y",strtotime($data['end_line'])));
// 			$_id->setValue($data['id']);
// 			$_status->setValue($data['status']);
// 			//$_loan_type->setValue($data['land_id']);
// 			$note->setValue($data['note']);
// 			$commission->setValue($data['comission']);
// 			$staff_id->setValue($data['staff_id']);
			
			
		}
		$this->addElements(array($cheque,$paid_before,$balance_before,$receipt,$fixedpayment,$note,$other_fee,$_branch_id,$_date_buy,
				$_interest,$_service_charge,$schedule_opt,$_to_total_sold,$_total_sold,$_house_price,$balance,$paid,//$_loan_type,
				$staff_id,$commission,$_amount,$_rate,$_releasedate,$_status,$discount,$_period,$_instalment_date,$_to_branch_id,
				$sold_price,$_old_payterm,$_interest_rate,$_release_date,$_first_payment,$_loan_code,$_dateline,$_id));
		return $this;
		
	}	
}