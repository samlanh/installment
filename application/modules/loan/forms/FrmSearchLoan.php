<?php 
Class Loan_Form_FrmSearchLoan extends Zend_Dojo_Form{
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function AdvanceSearch($data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside'));
		
		$_status_opt = array(
			-1=>$this->tr->translate("ALL"),
			1=>$this->tr->translate("ACTIVE"),
			0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$status_plong =  new Zend_Dojo_Form_Element_FilteringSelect('status_plong');
		$status_plong->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside'));
		
		$status_opt = array(
			-1=>$this->tr->translate("SELECT_STATUS"),
			1=>$this->tr->translate("បានប្រគល់"),
			2=>$this->tr->translate("មិនទាន់ប្រគល់"));
		$status_plong->setMultiOptions($status_opt);
		$status_plong->setValue($request->getParam("status_plong"));
		
		$payment_process =  new Zend_Dojo_Form_Element_FilteringSelect('payment_process');
		$payment_process->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside'));
		
		$_status_opt = array(
			-1=>$this->tr->translate("ALL"),
			1=>$this->tr->translate("PAID"),
			0=>$this->tr->translate("UNPAID"));
		$payment_process->setMultiOptions($_status_opt);
		$payment_process->setValue($request->getParam("payment_process"));
		
		$_ordering=  new Zend_Dojo_Form_Element_FilteringSelect('ordering');
		$_ordering->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside'));
		
		$_ordering_opt = array(
				1=>$this->tr->translate("Sort By Date"),
				2=>$this->tr->translate("Sort By Invoice"));
		$_ordering->setMultiOptions($_ordering_opt);
		$_ordering->setValue($request->getParam("ordering"));
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
			'onkeyup'=>'this.submit()',
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
			'dojoType'=>'dijit.form.Button',
			'iconclass'=>'dijitIconSearch',
			'class'=>'fullside',
		));
		
		$schedule_opt = new Zend_Dojo_Form_Element_FilteringSelect('schedule_opt');
		$schedule_opt->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'checkScheduleOption();'
		));
		$rows = $db->getVewOptoinTypeByType(25,null,null,1);
		$options = array(-1=>$this->tr->translate("REPAYMENT_TYPE"));
	    if(!empty($rows))foreach($rows AS $row){
	    	$row['name_en'] = $this->tr->translate($row['name_en']);
  			$options[$row['key_code']]=$row['name_en'];
  		}
		$schedule_opt->setMultiOptions($options);
		$schedule_opt->setValue($request->getParam("schedule_opt"));
		
		$_coid = new Zend_Dojo_Form_Element_FilteringSelect('co_id');
		$_coid->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'popupCheckCO();'
		));
		
		$rowss = $db->getAllCOName();
		$optionsCo=array(''=>$this->tr->translate("SELECT_SALE_AGENT"));
		if(!empty($rowss))foreach($rowss AS $row){
			$optionsCo[$row['id']]=$row['name'];
		}
		$_coid->setMultiOptions($optionsCo);
		$_coid->setValue($request->getParam("co_id"));
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_releasedate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
			'class'=>'fullside',
			'placeHolder'=>$this->tr->translate('START_DATE'),
			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
			'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
		}
		$_releasedate->setValue($_date);
		
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_dateline->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_dateline->setValue($_date);
		
		$client_name = new Zend_Dojo_Form_Element_FilteringSelect("client_name");
		$opt_client = array(''=>$this->tr->translate("CHOOSE_CUSTOEMR"));
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$client_name->setMultiOptions($opt_client);
		$client_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside',
			'required'=>'false'));
		$client_name->setValue($request->getParam("client_name"));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'getAllPropertyBranch();',
			'class'=>'fullside',
		));
		
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
		$_branch_id->setValue($request->getParam("branch_id"));
		
		$propertiestype = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside','onChange'=>'showPopupForm();'));
		$propertiestype_opt = $db->getPropertyTypeForsearch();
		$propertiestype->setMultiOptions($propertiestype_opt);
		$propertiestype->setValue($request->getParam("property_type"));
		
		$_category = new Zend_Dojo_Form_Element_FilteringSelect('category_id');
		$_category->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside',
		));
		$cateIncome = $db->getAllCategoryIncomeExpens(12);
		$opt=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($cateIncome))foreach($cateIncome AS $row){
			$opt[$row['id']]=$row['name'];
		}
		$_category->setMultiOptions($opt);
		$_category->setValue($request->getParam("category_id"));
		
		$category_id_expense = new Zend_Dojo_Form_Element_FilteringSelect('category_id_expense');
		$category_id_expense->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$cateEx = $db->getAllCategoryIncomeExpens(13);
		$opt1=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($cateEx))foreach($cateEx AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$category_id_expense->setMultiOptions($opt1);
		$category_id_expense->setValue($request->getParam("category_id_expense"));
		
		$payment_type = new Zend_Dojo_Form_Element_FilteringSelect('payment_type');
		$payment_type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
// 		$options= array(-1=>$this->tr->translate("PAYMENT_TYPE"),
// 			1=>$this->tr->translate("CASH"),
// 			2=>$this->tr->translate("CHEQUE"),
// 			3=>$this->tr->translate("PAYWITH_BANK")
// 		);
// 		$payment_type->setMultiOptions($options);
// 		$payment_type->setValue($request->getParam("payment_type"));
		$options=array('-1'=>$this->tr->translate("SELECT_TYPE"));
		$optsPayType = $db->getVewOptoinTypeByType(2,null,3,1);
		if(!empty($optsPayType))foreach($optsPayType AS $row){
			$options[$row['key_code']]=$row['name_en'];
		}
		$payment_type->setMultiOptions($options);
		
		$buy_type = new Zend_Dojo_Form_Element_FilteringSelect('buy_type');
		$buy_type->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$options= array(-1=>$this->tr->translate("CHOOSE_SALE_TYPE"),1=>"ធ្វើកិច្ចសន្យា",2=>$this->tr->translate("DEPOSIT"));
		$buy_type->setMultiOptions($options);
		$buy_type->setValue($request->getParam("buy_type"));
		
		$sale_status = new Zend_Dojo_Form_Element_FilteringSelect('sale_status');
		$sale_status->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$options= array(-1=>$this->tr->translate("CHOOSE_SALE_STATUS"),
				1=>"ទូទាត់ដាច់",
				2=>$this->tr->translate("មិនទាន់ដាច់"),
				3=>$this->tr->translate("Active Sale"),
				4=>$this->tr->translate("Cancel Sale"),
			);
		$sale_status->setMultiOptions($options);
		$sale_status->setValue($request->getParam("sale_status"));
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'onchange'=>'enablePayment();',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3);
		unset($opt[-1]);
		$payment_method->setMultiOptions($opt);
		$payment_method->setValue($request->getParam("payment_method"));
		
		$user = new Zend_Dojo_Form_Element_FilteringSelect('user_id');
		$user->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>"false",
			'queryExpr'=>'*${0}*',
			'required'=>false
		));
		$user->setValue($request->getParam('user_id'));
		$opt_user = array(''=>$this->tr->translate("LASTNAME_FIRSTNAME"));
		$all_user=$db->getAllUserGlobal();
		if(!empty($all_user))foreach ($all_user As $row)$opt_user[$row['id']]=$row['by_user'];
		$user->setMultiOptions($opt_user);
		
		$_supplier_id = new Zend_Dojo_Form_Element_FilteringSelect('supplier_id');
		$_supplier_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'onChange'=>'Addsupplier();',
		));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllSupplier();
		$options=array(''=>$this->tr->translate("SELECT_SUPPLIER"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_supplier_id->setMultiOptions($options);
		$_supplier_id->setValue($request->getParam('supplier_id'));
		
		//new filter search
		$streetlist = new Zend_Dojo_Form_Element_FilteringSelect('streetlist');
		$streetlist->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		
		));
		$streetopt = $db->getAllStreet();
		$streetlist->setMultiOptions($streetopt);
		$streetlist->setValue($request->getParam("streetlist"));
		
		$cheque_issuer_search = new Zend_Dojo_Form_Element_FilteringSelect('cheque_issuer_search');
		$cheque_issuer_search->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$dbe = new Incexp_Model_DbTable_DbExpense();
		$rscheque = $dbe->getAllChequeIssue();
		$opt1=array(''=>$this->tr->translate("SELECT_CHEQUE_ISSUE"));
		if(!empty($rscheque))foreach($rscheque AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$cheque_issuer_search->setMultiOptions($opt1);
		$cheque_issuer_search->setValue($request->getParam("cheque_issuer_search"));
		
		$payment_id = new Zend_Dojo_Form_Element_FilteringSelect('payment_id');
		$payment_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'required' =>'true'
		));
		$options = array();
		$options= array(0=>$this->tr->translate("SELECT_TYPE"));
		$options['1']=$this->tr->translate("IS_PAYOFF");
		$options['2']=$this->tr->translate("PAY_INSTALLMENT");
		$payment_id->setMultiOptions($options);
		$payment_id->setValue($request->getParam("payment_id"));
		
		$_agency_id = new Zend_Dojo_Form_Element_FilteringSelect('agency_id');
		$_agency_id->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		
		$rowss = $db->getAllCOName();
		$staff_opt=array(''=>$this->tr->translate("SELECT_SALE_AGENT"));
		if(!empty($rowss))foreach($rowss AS $row){
			$staff_opt[$row['id']]=$row['name'];
		}
		$_agency_id->setMultiOptions($staff_opt);
		$_agency_id->setValue($request->getParam("agency_id"));
		
		$option_pay = new Zend_Dojo_Form_Element_FilteringSelect('option_pay');
		$option_pay->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'OnChange'=>'payOption();'
		));
		$option_status = array(
				-1=>$this->tr->translate("PLEASE_SELECT").$this->tr->translate("PAYMENT_TYPE"),
				1=>$this->tr->translate("NORMAL_PAID"),
				3=>$this->tr->translate("PRINCIPAL_PAID"),
				4=>$this->tr->translate("PAYOFF_PAID"));
		$option_pay->setMultiOptions($option_status);
		$option_pay->setValue($request->getParam("option_pay"));
		
		$receipt_type = new Zend_Dojo_Form_Element_FilteringSelect('receipt_type');
		$receipt_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'OnChange'=>'payOption();'
		));
		$option_status = array(
				-1=>$this->tr->translate("PLEASE_SELECT").$this->tr->translate("PAYMENT_TYPE"),
				1=>$this->tr->translate("DEPOSIT_RECEIPT"),
				2=>$this->tr->translate("MONTHLY_RECEIPT"));
		$receipt_type->setMultiOptions($option_status);
		$receipt_type->setValue($request->getParam("receipt_type"));
		
		
		$_queryOrdering=  new Zend_Dojo_Form_Element_FilteringSelect('queryOrdering');
		$_queryOrdering->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside'));
		
		$_ordering_opt = array(
			0=>$this->tr->translate("ORD_DEFAULT"),
			1=>$this->tr->translate("ORD_BY_DATE_ASC"),
			2=>$this->tr->translate("ORD_BY_DATE_DESC"),
			3=>$this->tr->translate("ORD_BY_NUMBER_ASC"),
			4=>$this->tr->translate("ORD_BY_NUMBER_DESC"),
			5=>$this->tr->translate("ORD_BY_CLIENT"),
			);
		$_queryOrdering->setMultiOptions($_ordering_opt);
		$_queryOrdering->setValue($request->getParam("queryOrdering"));
		
		$_receiptStatus=  new Zend_Dojo_Form_Element_FilteringSelect('receiptStatus');
		$_receiptStatus->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside'));
		
		$_optStatusReceipt = array(
			0=>$this->tr->translate("ALL"),
			1=>$this->tr->translate("NORMAL_STATUS"),
			2=>$this->tr->translate("VOID_STATUS"),
			);
		$_receiptStatus->setMultiOptions($_optStatusReceipt);
		$_receiptStatus->setValue($request->getParam("receiptStatus"));
		
		$credit_category = new Zend_Dojo_Form_Element_FilteringSelect('credit_category');
		$credit_category->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		$cateEx = $db->getAllCategoryIncomeExpens(30);
		$opt1=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($cateEx))foreach($cateEx AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$credit_category->setMultiOptions($opt1);
		$credit_category->setValue($request->getParam("credit_category"));
		
		$_is_closed=  new Zend_Dojo_Form_Element_FilteringSelect('is_closed');
		$_is_closed->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'class'=>'fullside'));
		
		$_optStatusClosed = array(
			0=>$this->tr->translate("ALL"),
			1=>$this->tr->translate("Closed"),
			2=>$this->tr->translate("Unclosed"),
			);
		$_is_closed->setMultiOptions($_optStatusClosed);
		$_is_closed->setValue($request->getParam("is_closed"));
		
		if($data!=null){
			$_coid->setValue($data['co_id']);
			$_releasedate->setValue($data['date_release']);
			$client_name->setValue($data['client_name']);
		}
		$this->addElements(array($receipt_type,$option_pay,$payment_id,$sale_status,$status_plong,$payment_process,$user,$payment_method,$_ordering,$buy_type,$payment_type,
			$propertiestype,$schedule_opt,$_branch_id,$client_name,$_title,$_coid,$_releasedate,
			$_category,$category_id_expense,$_dateline,$_status,$_btn_search,$_supplier_id,$streetlist,$cheque_issuer_search,
			$_agency_id,
			
			$_queryOrdering,
			$_receiptStatus,
			$credit_category,
			$_is_closed
			
		));
		return $this;
		
	}	
}