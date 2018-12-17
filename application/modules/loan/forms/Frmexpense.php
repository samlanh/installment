<?php 
Class Loan_Form_Frmexpense extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddExpense($data=null){
		
		$title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required'=>true
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
				'class'=>'fullside'
		));
		$options= array(1=>$this->tr->translate("CASH"),2=>$this->tr->translate("CHEQUE"));
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
				0=>$this->tr->translate("DACTIVE"));
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
		));
		
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'onkeyup'=>'convertToDollar();',
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
			$title->setValue($data['title']);
			$total_amount->setValue($data['total_amount']);
			$_Description->setValue($data['description']);
			$_Date->setValue($data['date']);
			$_stutas->setValue($data['status']);
			$invoice->setValue($data['invoice']);
			$id->setValue($data['id']);
			$_cheque->setValue($data['cheque']);
			$payment_type->setValue($data['payment_id']);
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getControllerName()=='income'){
				$_status->setValue($data['is_beginning']);
			}
			if (!empty($data['supplier_id'])){
			$_supplier_id->setValue($data['supplier_id']);
			}
		}
		$this->addElements(array($_status,$payment_type,$_cheque,$invoice,$_currency_type,$title,$_Date ,$_stutas,$_Description,
				$category_id_expense,
				$total_amount,$convert_to_dollar,$_branch_id,$for_date,$id,$_supplier_id));
		return $this;
		
	}
	public function FrmAddExpenseother($data=null){
	
		$title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required'=>true
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
				'class'=>'fullside'
		));
		$options= array(1=>$this->tr->translate("CASH"),2=>$this->tr->translate("CHEQUE"));
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
				0=>$this->tr->translate("DACTIVE"));
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
		));
	
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'readOnly'=>'readOnly',
				'onkeyup'=>'convertToDollar();',
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
			$title->setValue($data['title']);
			$total_amount->setValue($data['total_amount']);
			$_Description->setValue($data['description']);
			$_Date->setValue($data['date']);
			$_stutas->setValue($data['status']);
			$invoice->setValue($data['invoice']);
			$id->setValue($data['id']);
			$_cheque->setValue($data['cheque']);
			$payment_type->setValue($data['payment_id']);
			$request=Zend_Controller_Front::getInstance()->getRequest();
			if($request->getControllerName()=='income'){
				$_status->setValue($data['is_beginning']);
			}
			if (!empty($data['supplier_id'])){
				$_supplier_id->setValue($data['supplier_id']);
			}
		}
		$this->addElements(array($_status,$payment_type,$_cheque,$invoice,$_currency_type,$title,$_Date ,$_stutas,$_Description,
				$category_id_expense,
				$total_amount,$convert_to_dollar,$_branch_id,$for_date,$id,$_supplier_id));
		return $this;
	
	}	
}