<?php 
Class Stock_Form_FrmPurchasePayment extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
	}
	public function FrmAddPurchasePayment($data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_dbpayment = new Stock_Model_DbTable_DbPurchasePayment();
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',  
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		
		$_receipt_no = new Zend_Dojo_Form_Element_TextBox('receipt_no');
		$_receipt_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'readonly'=>'readonly',
				'style'=>'color: red; font-weight: 600;',
				'missingMessage'=>$this->tr->translate("Forget Enter Receipt No")
		));
		$itemsCode = $_dbpayment->getPuchasePaymentCode();
		$_receipt_no->setValue($itemsCode);
		
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$Option = $_dbpayment->getAllSuplier();
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$_supplier_id = new Zend_Dojo_Form_Element_FilteringSelect("supplier_id");
		$_supplier_id->setMultiOptions($_arr_opt);
		$_supplier_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'missingMessage'=>'Invalid Module!',
				'onChange'=>'getSupplierInfo()',
				'class'=>'fullside height-text',));
		
		$_balance = new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$_balance->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>' fullside height-text',
			'readonly'=>'readonly',
			'placeholder'=>$this->tr->translate("BALANCE"),
			'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		$_total_paid = new Zend_Dojo_Form_Element_NumberTextBox('total_paid');
		$_total_paid->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>' fullside height-text',
			'readonly'=>'readonly',
			'placeholder'=>$this->tr->translate("TOTAL_PAID"),
			'missingMessage'=>$this->tr->translate("Forget Enter Total Paid")
		));
		
		$_total_discount = new Zend_Dojo_Form_Element_NumberTextBox('total_discount');
		$_total_discount->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>' fullside height-text',
			'readonly'=>'readonly',
			'placeholder'=>$this->tr->translate("TOTAL_DISCOUNT"),
			'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		$_total_due = new Zend_Dojo_Form_Element_NumberTextBox('total_due');
		$_total_due->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>' fullside height-text',
			'readonly'=>'readonly',
			'placeholder'=>$this->tr->translate("TOTAL_DUE"),
			'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		
		$_date_payment= new Zend_Dojo_Form_Element_DateTextBox('date_payment');
		$_date_payment->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',));
		$_date_payment->setValue(date("Y-m-d"));
		
		$_all_balance = new Zend_Dojo_Form_Element_NumberTextBox('all_balance');
		$_all_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("BALANCE"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		$_all_balance->setValue(0);
		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('amount');
		$_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>' fullside height-text',
				'onKeyup'=>'checkAmout()',
				'placeholder'=>$this->tr->translate("AMOUNT"),
				'missingMessage'=>$this->tr->translate("Forget Enter Amount")
		));
		$_amount->setValue(0);
		
		$_arr_optview = array();
		$optionView = $_dbgb->getViewById(8);
		if(!empty($optionView))foreach($optionView AS $row) $_arr_optview[$row['key_code']]=$row['view_name'];
		$_paid_by = new Zend_Dojo_Form_Element_FilteringSelect("paid_by");
		$_paid_by->setMultiOptions($_arr_optview);
		$_paid_by->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',
			));
		
		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'font-family: inherit;  min-height:100px !important;'
		));
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("VOID"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$id = new Zend_Form_Element_Hidden('id');
		//searh
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside height-text',
			'placeholder'=>$this->tr->translate("SEARCH")."...",
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$optionBranch = $_dbgb->getAllBranch();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
		$_branch_search->setMultiOptions($_arr_opt_branch);
		$_branch_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_branch_search->setValue($request->getParam("branch_search"));
		
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
		$Option = $_dbpayment->getAllSuplier();
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$_supplier_search = new Zend_Dojo_Form_Element_FilteringSelect("supplier_search");
		$_supplier_search->setMultiOptions($_arr_opt);
		$_supplier_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_supplier_search->setValue($request->getParam("supplier_search"));
		
		$_bypuchase_no = new Zend_Dojo_Form_Element_TextBox('bypuchase_no');
		$_bypuchase_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'placeholder'=>$this->tr->translate("FILTER_BY_PURCHASE_NO"),
				'missingMessage'=>$this->tr->translate("Forget Enter Receipt No")
		));
		
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',));
		$_date = $request->getParam("start_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$start_date->setValue($_date);
		 
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required'=>false));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		
		$_arr_optview = array(""=>$this->tr->translate("ALL"));
		$optionView = $_dbgb->getViewById(8);
		if(!empty($optionView))foreach($optionView AS $row) $_arr_optview[$row['key_code']]=$row['view_name'];
		$_paid_by_search = new Zend_Dojo_Form_Element_FilteringSelect("paid_by_search");
		$_paid_by_search->setMultiOptions($_arr_optview);
		$_paid_by_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$_paid_by_search->setValue($request->getParam("paid_by_search"));
		
		$_arr = array(""=>$this->tr->translate("ALL"),1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("VOID"));
		$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
		$_status_search->setMultiOptions($_arr);
		$_status_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'missingMessage'=>'Invalid Module!',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'class'=>'fullside height-text',));
		$_status_search->setValue($request->getParam("status_search"));
		
		
		$_arr_opt_user = array(""=>$this->tr->translate("PLEASE_SELECT_USER"),);
		$userinfo = $_dbgb->getUserInfo();
		$optionUser = $_dbgb->getAllUserGlobal();
		if(!empty($optionUser))foreach($optionUser AS $row) $_arr_opt_user[$row['id']]=$row['name'];
		$_user_id = new Zend_Dojo_Form_Element_FilteringSelect("user_id");
		$_user_id->setMultiOptions($_arr_opt_user);
		$_user_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'queryExpr'=>'*${0}*',
				'autoComplete'=>'false',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		if ($userinfo['level']!=1){
			$_user_id->setAttribs(array(
					'readonly'=>true,
			));
			$_user_id->setValue($userinfo['user_id']);
		}
		$_user_id->setValue($request->getParam("user_id"));
		
		if(!empty($data)){
			$_branch_id->setValue($data["branch_id"]);
			$_receipt_no->setValue($data["receipt_no"]);
			$_supplier_id->setValue($data["supplier_id"]);
			$_balance->setValue($data["balance"]);
			$_total_paid->setValue($data["total_paid"]);
			$_amount->setValue($data["total_paid"]);
			$_total_discount->setValue($data["total_discount"]);
			$_total_due->setValue($data["total_due"]);
			if (!empty($data["date_payment"])){
				$_date_payment->setValue(date("Y-m-d",strtotime($data["date_payment"])));
			}
			$_paid_by->setValue($data["paid_by"]);
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
			$note->setValue($data["note"]);
		}
		$this->addElements(array(
				$_branch_id,
				$_receipt_no,
				$_supplier_id,
				$_all_balance,
				$_balance,
				$_total_paid,
				$_total_discount,
				$_total_due,
				$_date_payment,
				$_paid_by,
				$_status,
				$id,
				$_amount,
				$note,
				
				$_branch_search,
				$start_date,
				$end_date,
				$_bypuchase_no,
				$_adv_search,
				$_paid_by_search,
				$_supplier_search,
				$_status_search,
				$_user_id
				));
		
		return $this;
		
	}
	
	
	
	
}