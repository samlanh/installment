<?php 
Class Loan_Form_FrmCommision extends Zend_Dojo_Form {
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
				'placeholder'=>$this->tr->translate('START_DATE'),
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		
		$_date = date("Y-m-d");
		$to_date_search = $request->getParam("to_date_search");
		if(!empty($to_date_search)){
			$start_date_search->setValue($request->getParam("from_date_search"));
		}
		
		$to_date_search = new Zend_Dojo_Form_Element_DateTextBox('to_date_search');
		$to_date_search->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','class'=>'fullside',
		));
		$to_date_search->setValue($_date);
		$todatesearch = $request->getParam("to_date_search");
		if(!empty($todatesearch))$to_date_search->setValue($request->getParam("to_date_search"));
		
		
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
		$options_branch_search=array("-1"=>$this->tr->translate("SELECT_PROJECT"));
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
		
		$full_commission = new Zend_Dojo_Form_Element_NumberTextBox('full_commission');
		$full_commission->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readOnly'=>true
		));
		$full_commission->setValue(0);
		
		$_coid = new Zend_Dojo_Form_Element_FilteringSelect('co_id');
		$_coid->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();'
		));
		$options = $db ->getAllCOName(1);
		$_coid->setMultiOptions($options);
		
		$_period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$_period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required' =>'true',
				'readonly'=>true,
				'class'=>'fullside',
		));
		
		$_discount = new Zend_Dojo_Form_Element_NumberTextBox('discount');
		$_discount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>'true',
				'readonly'=>true,
				'class'=>'fullside',
		));
		$_discount->setValue(0);
		
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
		
		$date = new Zend_Dojo_Form_Element_TextBox("date");
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required' =>'true'));
		$date->setValue(date("Y-m-d"));
		
		$_property_id = new Zend_Form_Element_Hidden("property_id");
		$_old_sale_id = new Zend_Form_Element_Hidden("id");
		
		$return_back = new Zend_Dojo_Form_Element_NumberTextBox('return_back');// amount money return when cancel
		$return_back->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onkeyup'=>'checkreturnAmount();'
		));
		$return_back->setValue(0);
		
		$client_name = new Zend_Dojo_Form_Element_FilteringSelect("client_name");
		$opt_client = array(''=>'ជ្រើសរើស ឈ្មោះអតិថិជន');
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$client_name->setMultiOptions($opt_client);
		$client_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$client_name->setValue($request->getParam("client_name"));
		
		$client_name->setValue($request->getParam('client_name'));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$staff_id = new Zend_Dojo_Form_Element_FilteringSelect('staff_id');
		$staff_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$staff_opt = $db->getAllCOName(1);
		$staff_id->setMultiOptions($staff_opt);
		$staff_id->setValue($request->getParam("staff_id"));
		
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			$_old_sale_id->setValue($data['id']);
// 			echo $data['id'];exit();
// 			$_status->setValue($data['status']);
			$return_back->setValue($data['total_amount']);
			$_status->setValue($data['status']);
		}
		$this->addElements(array($full_commission,$staff_id,$date,$client_name,$installment_paid,$branch_id,$_cancel_code,$_sale_no,$_property,$end_date,$buy_date,$_price_sold,
				$paid_amount,$_balance,$_discount,$schedule_opt,$_property_id,$_title,$start_date_search,$to_date_search,
				$branch_id_search,$sold_date,$_commision,$_old_sale_id,$property,
				$_period,$_loan_code,$_collect_term,
				$_customer_code,
				$return_back,$_status
				));
		return $this;
		
	}	
}