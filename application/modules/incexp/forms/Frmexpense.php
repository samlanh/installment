<?php 
Class Incexp_Form_Frmexpense extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddExpense($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 


		$_bankId = new Zend_Dojo_Form_Element_FilteringSelect('bank_id');
		$_bankId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'false',
		));
		$rsBank = $dbGBStock->getAllBank();
		$optBank=array(''=>$this->tr->translate("SELECT_BANK"));
		if(!empty($rsBank))foreach($rsBank AS $row){
			$optBank[$row['id']]=$row['name'];
		}
		$_bankId->setMultiOptions($optBank);

		
		$title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				));
		
		$for_date = new Zend_Dojo_Form_Element_FilteringSelect('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$options= array(1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"10",11=>"11",12=>"12");
		$for_date->setMultiOptions($options);
		
		$payment_type = new Zend_Dojo_Form_Element_FilteringSelect('payment_type');
		$payment_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'enablePayment();'
		));
// 		$options= array(
// 				1=>$this->tr->translate("CASH"),
// 				2=>$this->tr->translate("CHEQUE"),
// 				3=>$this->tr->translate("PAYWITH_BANK"));
// 		$payment_type->setMultiOptions($options);
		
		$options = $dbgb->getVewOptoinTypeByType(2,1,3,1);
		$payment_type->setMultiOptions($options);
		
		$cdate=date("Y-m-d");
		if (!empty($data['date'])){
			$cdate=date("Y-m-d",strtotime($data['date']));
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
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{".$constraintsDate."datePattern:'dd/MM/yyyy'}",
				'readOnly' =>$paymentDateEnable,
		));
		$_Date->setValue(date('Y-m-d'));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'getInvoiceNo("");getallCustomer();'
		));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
		
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('Stutas');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			
		));
		$options= array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_stutas->setMultiOptions($options);
		
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"height:70px !important",
				
		));
		
		$_cheque = new Zend_Dojo_Form_Element_TextBox('cheque');
		$_cheque ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ACCOUNT_AND_CHEQUE_NO"),
				'style'=>'color:red;font-weight: 600;',
		));
		
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				//'onkeyup'=>'convertToDollar();',
		));
		
		$convert_to_dollar=new Zend_Dojo_Form_Element_NumberTextBox('convert_to_dollar');
		$convert_to_dollar->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				//'required'=>true
		));
		
		$invoice=new Zend_Dojo_Form_Element_TextBox('invoice');
		$invoice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'true',
				'style'=>'color:red',
		));
		
		$id = new Zend_Form_Element_Hidden("id");
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('category_id');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true',
		));
// 		$opt= $db->getVewOptoinTypeByType(12,1,null,1);
		$cateIncome = $db->getAllCategoryIncomeExpens(12);
		$opt=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($cateIncome))foreach($cateIncome AS $row){
			$opt[$row['id']]=$row['name'];
		}
		$_currency_type->setMultiOptions($opt);
	
		$category_id_expense = new Zend_Dojo_Form_Element_FilteringSelect('category_id_expense');
		$category_id_expense->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
// 		$opt1= $db->getVewOptoinTypeByType(13,1,null,1);
		$cateEx = $db->getAllCategoryIncomeExpens(13);
		$opt1=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($cateEx))foreach($cateEx AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$category_id_expense->setMultiOptions($opt1);
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('is_beginning');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside'));
		$_status_opt = array(
				0=>$this->tr->translate("INCOME_TYPE"),
				2=>$this->tr->translate("MONTHLY_FEE"),
				1=>$this->tr->translate("BEGENING_TYPE")
				);
		$_status->setMultiOptions($_status_opt);
		
		
		$_supplier_id = new Zend_Dojo_Form_Element_FilteringSelect('supplier_id');
		$_supplier_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onChange'=>'Addsupplier();',
		));
		
		//$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllSupplier();
		$options=array(''=>$this->tr->translate("SELECT_SUPPLIER"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_supplier_id->setMultiOptions($options);
		
		
		$cheque_issuer = new Zend_Dojo_Form_Element_FilteringSelect('cheque_issuer');
		$cheque_issuer->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'popupIssuer();'
		));
		
		$dbe = new Incexp_Model_DbTable_DbExpense();
		$rscheque = $dbe->getAllChequeIssue();
		$opt1=array(''=>$this->tr->translate("SELECT_CHEQUE_ISSUE"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rscheque))foreach($rscheque AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$cheque_issuer->setMultiOptions($opt1);
		
		$_other_invoice = new Zend_Dojo_Form_Element_TextBox('other_invoice');
		$_other_invoice ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT"));
    	$Option = $db->getAllItems();
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_items_id = new Zend_Dojo_Form_Element_FilteringSelect("items_id");
    	$_items_id->setMultiOptions($_arr_opt);
    	$_items_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'onChange'=>'getRefreshProduct();',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$start_Date = new Zend_Dojo_Form_Element_DateTextBox('from_date');
    	$start_Date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'required'=>true,
    			'class'=>'fullside',
    			'onchange'=>'CalculateAmount();',
    			'constraints'=>"{".$constraintsDate."datePattern:'dd/MM/yyyy'}",
    			'readOnly' =>$paymentDateEnable,
    	));
    	$start_Date->setValue(date('Y-m-d'));
    	
    	$end_Date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$end_Date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'required'=>true,
    			'class'=>'fullside',
    			'constraints'=>"{".$constraintsDate."datePattern:'dd/MM/yyyy'}",
    			'readOnly' =>$paymentDateEnable,
    	));
    	
    	$qty=new Zend_Dojo_Form_Element_NumberTextBox('qty');
    	$qty->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>'fullside',
    			'onkeyup'=>'CalculateAmount();',
    			'data-dojo-props'=>"constraints:{min:0,max:60}"
    	));
    	
    	$price=new Zend_Dojo_Form_Element_NumberTextBox('unit_price');
    	$price->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>'fullside',
    			'onkeyup'=>'CalculateAmount();'
    	));
    	
    	
    	$amount=new Zend_Dojo_Form_Element_NumberTextBox('amount');
    	$amount->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>'fullside',
    	));
		
		$expenseType = new Zend_Dojo_Form_Element_FilteringSelect('expenseType');
		$expenseType ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			
		));
		$options= array(
					1=>$this->tr->translate("DIRECT_EXPENSE"),
					2=>$this->tr->translate("EXPENSE_INVOICE")
				);
		$expenseType->setMultiOptions($options);
		
		if($data!=null){
			
			$start_Date->setValue($data['category_id']);
			
			$_currency_type->setValue($data['category_id']);
			$category_id_expense->setValue($data['category_id']);
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttribs(array("readonly"=>true));
			$title->setValue($data['title']);
			$total_amount->setValue($data['total_amount']);
			$_Description->setValue($data['description']);
			$_Date->setValue($data['date']);
			$_stutas->setValue($data['status']);
			$invoice->setValue($data['invoice']);
			$id->setValue($data['id']);
			$_cheque->setValue($data['cheque']);
			$payment_type->setValue($data['payment_id']);
			
			$data['qty'] = empty($data['qty']) ? 0 : $data['qty'];
			$data['unit_price'] = empty($data['unit_price']) ? 0 : $data['unit_price'];
			$data['amount'] = empty($data['amount']) ? 0 : $data['amount'];
			$data['from_date'] = empty($data['from_date']) ? 0 : $data['from_date'];
			$data['next_date'] = empty($data['next_date']) ? 0 : $data['next_date'];
			
			$qty->setValue($data['qty']);
			$price->setValue($data['unit_price']);
			$amount->setValue($data['amount']);
			$start_Date->setValue($data['from_date']);
			$end_Date->setValue($data['next_date']);
			

			$_bankId->setValue($data['bank_id']);
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getControllerName()=='income'){
				$_status->setValue($data['is_beginning']);
			}else{
				$data['other_invoice'] = empty($data['other_invoice']) ? "" : $data['other_invoice'];
				$_other_invoice->setValue($data['other_invoice']);
			}
			if (!empty($data['supplier_id'])){
			$_supplier_id->setValue($data['supplier_id']);
			}
			
			if (!empty($data['cheque_issuer'])){
				$cheque_issuer->setValue($data['cheque_issuer']);
			}
			
			$data['expenseType'] = empty($data['expenseType']) ? "1" : $data['expenseType'];
			$expenseType->setValue($data['expenseType']);
			
		}
		$this->addElements(array($amount,$qty,$price,$start_Date,$end_Date,$_status,$payment_type,$_cheque,$invoice,$_currency_type,$title,$_Date ,$_stutas,$_Description,
				$category_id_expense,
				$total_amount,$convert_to_dollar,$_branch_id,$for_date,$id,$_supplier_id,$cheque_issuer,$_other_invoice,
				$_items_id
				,$_bankId
				,$expenseType
				
				));
		return $this;
		
	}
	
	public function FrmAddIncomeother($data=null){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		
		$for_date = new Zend_Dojo_Form_Element_FilteringSelect('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$options= array(1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"10",11=>"11",12=>"12");
		$for_date->setMultiOptions($options);
	
		$payment_type = new Zend_Dojo_Form_Element_FilteringSelect('payment_type');
		$payment_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
// 		$options= array(1=>$this->tr->translate("CASH"),2=>$this->tr->translate("CHEQUE"));
// 		$payment_type->setMultiOptions($options);
		
		$options = $dbgb->getVewOptoinTypeByType(2,1,3,1);
		$payment_type->setMultiOptions($options);
	
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_Date->setValue(date('Y-m-d'));
	
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'getInvoiceNo("");getallCustomer();'
		));
	
		$db = new Application_Model_DbTable_DbGlobal();
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
	
		$_stutas = new Zend_Dojo_Form_Element_FilteringSelect('Stutas');
		$_stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
					
		));
		$options= array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_stutas->setMultiOptions($options);
	
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"height:70px !important",
	
		));
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_method->setMultiOptions($opt);
	
		$_cheque = new Zend_Dojo_Form_Element_TextBox('cheque');
		$_cheque ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'readOnly'=>'readOnly',
				//'onkeyup'=>'convertToDollar();',
		));
	
		$convert_to_dollar=new Zend_Dojo_Form_Element_NumberTextBox('convert_to_dollar');
		$convert_to_dollar->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				//'required'=>true
		));
	
		$invoice=new Zend_Dojo_Form_Element_TextBox('invoice');
		$invoice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'true',
				'style'=>'color:red',
		));
	
		$id = new Zend_Form_Element_Hidden("id");
		$id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				));
				
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('category_id');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'true',
		));
		// 		$opt= $db->getVewOptoinTypeByType(12,1,null,1);
		$cateIncome = $db->getAllCategoryIncomeExpens(12);
		$opt=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($cateIncome))foreach($cateIncome AS $row){
			$opt[$row['id']]=$row['name'];
		}
		$_currency_type->setMultiOptions($opt);
	
		$category_id_expense = new Zend_Dojo_Form_Element_FilteringSelect('category_id_expense');
		$category_id_expense->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		// 		$opt1= $db->getVewOptoinTypeByType(13,1,null,1);
		$cateEx = $db->getAllCategoryIncomeExpens(13);
		$opt1=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($cateEx))foreach($cateEx AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$category_id_expense->setMultiOptions($opt1);
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('is_beginning');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside'));
		$_status_opt = array(
				0=>$this->tr->translate("INCOME_TYPE"),
				1=>$this->tr->translate("BEGENING_TYPE"));
		$_status->setMultiOptions($_status_opt);
	
	
		$_supplier_id = new Zend_Dojo_Form_Element_FilteringSelect('supplier_id');
		$_supplier_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onChange'=>'Addsupplier();',
		));
		
	
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllSupplier();
		$options=array(''=>$this->tr->translate("SELECT_SUPPLIER"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_supplier_id->setMultiOptions($options);
	
		if($data!=null){
			$_currency_type->setValue($data['category_id']);
			$category_id_expense->setValue($data['category_id']);
			$_branch_id->setValue($data['branch_id']);
			$total_amount->setValue($data['total_amount']);
			$_Description->setValue($data['description']);
			$_Date->setValue($data['date']);
			$_stutas->setValue($data['status']);
			$invoice->setValue($data['invoice']);
			$payment_method->setValue($data['payment_method']);
			$id->setValue($data['id']);
			$_cheque->setValue($data['cheque']);
			$payment_type->setValue($data['payment_id']);
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getControllerName()=='income'){
				$_status->setValue($data['is_beginning']);
			}
			if(!empty($data['supplier_id'])){
				$_supplier_id->setValue($data['supplier_id']);
			}
		}
		$this->addElements(array($_status,$payment_method,$payment_type,$_cheque,$invoice,$_currency_type,$_Date ,$_stutas,$_Description,
				$category_id_expense,
				$total_amount,$convert_to_dollar,$_branch_id,$for_date,$id,$_supplier_id));
		return $this;
	
	}	
}