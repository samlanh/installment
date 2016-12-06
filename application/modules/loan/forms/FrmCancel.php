<?php 
Class Loan_Form_FrmCancel extends Zend_Dojo_Form {
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
		
		$rows=$db->getPropertyTypeForsearch();
		$opt_co = array(''=>$this->tr->translate("SELECT_PROPERTY_TYPE"));
		$opt_co = $rows;
		$property = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$property->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'class'=>'fullside',
		));
		$property->setMultiOptions($opt_co);
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$start_date_search = new Zend_Dojo_Form_Element_DateTextBox('from_date_search');
		$start_date_search->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				//'required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		
		$_date = date("Y-m-d");
		$start_date_search->setValue($_date);
		if(!empty($request->getParam("to_date_search")))$start_date_search->setValue($request->getParam("from_date_search"));
		
		
		$to_date_search = new Zend_Dojo_Form_Element_DateTextBox('to_date_search');
		$to_date_search->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','class'=>'fullside',
		));
		$to_date_search->setValue($_date);
		if(!empty($request->getParam("to_date_search")))$to_date_search->setValue($request->getParam("to_date_search"));
		
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getSaleNo(), getSaleClie();'
		));
		$rows_branch = $db->getAllBranchName();
		if(!empty($rows_branch))foreach($rows_branch AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		
		
		$branch_id_search = new Zend_Dojo_Form_Element_FilteringSelect('branch_id_search');
		$branch_id_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
		));
		$options_branch_search=array("-1"=>"Select Project");
		$rows_branch_saerch = $db->getAllBranchName();
		if(!empty($rows_branch))foreach($rows_branch_saerch AS $row){
			$options_branch_search[$row['br_id']]=$row['project_name'];
		}
		$branch_id_search->setMultiOptions($options_branch_search);
		$branch_id_search->setValue($request->getParam("branch_id_search"));
		
		$_cancel_code = new Zend_Dojo_Form_Element_TextBox('cancel_code');
		$_cancel_code->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
		));
		
		$installment_paid = new Zend_Dojo_Form_Element_TextBox('installment_paid');
		$installment_paid->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
		));
		
		$_sale_no = new Zend_Dojo_Form_Element_FilteringSelect('sale_no');
		$_sale_no->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'checkScheduleOption();'
		));
		$opt = array("","SELECT_SALE_NO");
		$_sale_no->setMultiOptions($opt);
		
		$_customer_code = new Zend_Dojo_Form_Element_FilteringSelect('customer');
		$_customer_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$group_opt = $db->getGroupCodeById(1,0,1);//code,individual,option
		$_customer_code->setMultiOptions($group_opt);
		
		$_property = new Zend_Dojo_Form_Element_FilteringSelect('property');
		$_property->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$property_opt = array("","PROPERTY");
		$_property->setMultiOptions($property_opt);
		
		$buy_date = new Zend_Dojo_Form_Element_DateTextBox('buy_date');
		$buy_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'readonly'=>true,
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$s_date = date('Y-m-d');
		$buy_date->setValue($s_date);
		
		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$end_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'readonly'=>true,
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$s_date = date('Y-m-d');
		$end_date->setValue($s_date);
		
		$sold_date = new Zend_Dojo_Form_Element_DateTextBox('sold_date');
		$sold_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'readonly'=>true,
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
		$_price_sold = new Zend_Dojo_Form_Element_NumberTextBox('price_sold');
		$_price_sold->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
		));	
		$paid_amount = new Zend_Dojo_Form_Element_NumberTextBox('paid_amount');
		$paid_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>true,
		));
		
		$_balance = new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
				
		));
		$_commision = new Zend_Dojo_Form_Element_NumberTextBox('commision');
		$_commision->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
		));
		$_coid = new Zend_Dojo_Form_Element_FilteringSelect('co_id');
		$_coid->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();'
		));
		$options = $db ->getAllCOName(1);
		$_coid->setMultiOptions($options);
		
		$_coids = new Zend_Dojo_Form_Element_TextBox('gender');
		$_coids->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$commission = new Zend_Dojo_Form_Element_NumberTextBox('commission');
		$commission->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$commission->setValue(3);
		
		$_loan_type = new Zend_Dojo_Form_Element_FilteringSelect('land_code');
		$_loan_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getlandinfo();'
		));
		$opt = $db->getAllLandInfo();
		$_loan_type->setMultiOptions($opt);
		
		$_time_collect = new Zend_Dojo_Form_Element_NumberTextBox('amount_collect');
		$_time_collect->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'getFirstPayment();'
		));
 		$_time_collect->setValue(1);
 		
 		$_time_collect_pri = new Zend_Dojo_Form_Element_NumberTextBox('amount_collect_pricipal');
 		$_time_collect_pri->setAttribs(array(
 				'dojoType'=>'dijit.form.NumberTextBox',
 				'class'=>'fullside',
 				'readonly'=>true,
 				'required'=>true
 		));
 		$_time_collect_pri->setValue(0);
 		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('land_price');
		$_amount->setAttribs(array(
						'dojoType'=>'dijit.form.NumberTextBox',
						'class'=>'fullside',
						'required' =>'true',
						'readOnly'=>true,
				       
		));
		
		$_period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$_period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required' =>'true',
				'readonly'=>true,
				'class'=>'fullside',
				//'onkeyup'=>'calCulatePeriod();'
		));
		//$_period->setValue(24);
		
		$_discount = new Zend_Dojo_Form_Element_NumberTextBox('discount');
		$_discount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>'true',
				'readonly'=>true,
				'class'=>'fullside',
		));
		$_discount->setValue(0);
		
		$_other_fee = new Zend_Dojo_Form_Element_NumberTextBox('other_fee');
		$_other_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>'true',
				'readonly'=>true,
				'class'=>'fullside',
		));
		$_other_fee->setValue(0);
		
		$_collect_term = new Zend_Dojo_Form_Element_FilteringSelect('collect_termtype');
 		$_collect_term->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'changeGraicePeroid();'	
		));
		$term_opt = $db->getVewOptoinTypeByType(14,1,3,1);
		$_collect_term->setMultiOptions($term_opt);
	
		$schedule_opt = new Zend_Dojo_Form_Element_FilteringSelect('schedule_opt');
		$schedule_opt->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'readonly'=>true,
		));
		$opt = $db->getVewOptoinTypeByType(25,1,null,1);
		$schedule_opt->setMultiOptions($opt);
		
		$_pay_every = new Zend_Dojo_Form_Element_FilteringSelect('pay_every');
		$_pay_every->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'changeCollectType();'
		));
		$_pay_every->setValue(3);
		$_pay_every->setMultiOptions($term_opt);
		$_every_payamount = new Zend_Dojo_Form_Element_FilteringSelect('every_payamount');
		$_every_payamount->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$options= array(2=>"After",1=>"Before",3=>"Normal");
		$_every_payamount->setMultiOptions($options);
		
		$_time= new Zend_Dojo_Form_Element_TextBox('time');
		$_time->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$set_time='10:00-11:00 AM';
		$_time->setValue($set_time);
		
		$_paybefore = new Zend_Dojo_Form_Element_NumberTextBox('pay_before');
		$_paybefore->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'	
		));
		$_paybefore->setValue(0);
		
		$_pay_late = new Zend_Dojo_Form_Element_NumberTextBox('pay_late');
		$_pay_late->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_pay_late->setValue(0);
		$arr=$db->getSystemSetting('interest_late');
		$_pay_late->setValue($arr['value']);
		

		$_repayment_method = new Zend_Dojo_Form_Element_FilteringSelect('repayment_method');
		$_repayment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'chechPaymentMethod()'
		));
		$options = $db->getAllPaymentMethod(null,1);
		$_repayment_method->setMultiOptions($options);
		
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
		
		$penalize = new Zend_Dojo_Form_Element_TextBox("penalize");
		$penalize->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$_service_charge = new Zend_Dojo_Form_Element_TextBox("service_charge");
		$_service_charge->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$_instalment_date = new Zend_Form_Element_Hidden("instalment_date");
		$_release_date = new Zend_Form_Element_Hidden("old_release_date");
		$_interest_rate = new Zend_Form_Element_Hidden("old_rate");
		$_old_payterm = new Zend_Form_Element_Hidden("old_payterm");
		$_id = new Zend_Form_Element_Hidden('id');
		
		$_property_id = new Zend_Form_Element_Hidden("property_id");
		$_old_sale_id = new Zend_Form_Element_Hidden("old_sale_id");
		$_old_property_id = new Zend_Form_Element_Hidden("old_property_id");
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			$_cancel_code->setValue($data['cancel_code']);
			$_old_sale_id->setValue($data['sale_id']);
			$_old_property_id->setValue($data['property_id']);
			$_status->setValue($data['status']);

			$installment_paid->setValue($data['installment_paid']);
			$paid_amount->setValue($data['paid_amount']);
		}
		$this->addElements(array($installment_paid,$branch_id,$_cancel_code,$_sale_no,$_property,$end_date,$buy_date,$_price_sold,
				$paid_amount,$_balance,$_discount,$_other_fee,$schedule_opt,$_property_id,$_title,$start_date_search,$to_date_search,
				$branch_id_search,$sold_date,$_commision,$_old_sale_id,$_old_property_id,$property,
				$_old_payterm,$_interest_rate,$_release_date,$_instalment_date,$_interest,$penalize,$_service_charge,
				$_coids,$_loan_type,
				$_time_collect,$_paybefore,
				$_pay_late,$_coid,$commission,$_amount,
				$_every_payamount,$_time,$_time_collect_pri,$_status,$_period,
				$_repayment_method,$_pay_every,$_loan_code,$_collect_term,
				$_customer_code,$_id));
		return $this;
		
	}	
}