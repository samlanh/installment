<?php 
Class Incexp_Form_FrmInvExpense extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddInvExpense($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		
		$title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				));
		
		
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
		
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				//'onkeyup'=>'convertToDollar();',
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
			
			$id->setValue($data['id']);
			$data['other_invoice'] = empty($data['other_invoice']) ? "" : $data['other_invoice'];
			$_other_invoice->setValue($data['other_invoice']);
			
			if (!empty($data['supplier_id'])){
			$_supplier_id->setValue($data['supplier_id']);
			}
			
			if (!empty($data['cheque_issuer'])){
				$cheque_issuer->setValue($data['cheque_issuer']);
			}
			
		}
		$this->addElements(array($start_Date,$end_Date,$_status,$_currency_type,$title,$_Date ,$_stutas,$_Description,
				$category_id_expense,
				$total_amount,$_branch_id,$id,$_supplier_id,$_other_invoice,
				$_items_id
				
				));
		return $this;
		
	}
}