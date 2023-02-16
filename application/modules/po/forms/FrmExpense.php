<?php 
Class Po_Form_FrmExpense extends Zend_Dojo_Form {
// 	public function init()
// 	{
// 	}///
	public function FrmExpense($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$textbox = 'dijit.form.TextBox';
		$numbertext='dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$dbGB = new Application_Model_DbTable_DbGlobal(); 
    	$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 
	
    	
		$userInfo = $dbGB->getUserInfo();
		$userLevel=0;
		$userLevel = empty($userInfo['level'])?0:$userInfo['level'];
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'onChageFunctionByBranch();'
			
		));
		$rows = $dbGB->getAllBranchName();
		$options_branch=array('-1'=>$tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		if (count($rows)==1){
			$branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($rows)) foreach($rows AS $row){
				$branch_id->setValue($row['br_id']);
			}
		}
		
		$paymentNo = new Zend_Dojo_Form_Element_TextBox('paymentNo');
    	$paymentNo->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("INVOICE_NO"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));

		$external_invoice = new Zend_Dojo_Form_Element_TextBox('externalInvoice');
    	$external_invoice->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("EXTERNAL_INVOICE"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));

		$budgetItem = new Zend_Dojo_Form_Element_FilteringSelect('budgetItem');
		$budgetItem->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
 			'onchange'=>'addNewBudgetItem();'
		));
		
		$budgetItem->setMultiOptions($dbGBStock->getAllBudgetItem(0,'', '',1,null));
		
		$expense_title = new Zend_Dojo_Form_Element_TextBox('expenseTitle');
    	$expense_title->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("EXPENSE_TITLE"),
				
    	));
		$receiver = new Zend_Dojo_Form_Element_TextBox('receiver');
    	$receiver->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("RECEIVER"),
				
    	));

		
		$paymentDate = new Zend_Dojo_Form_Element_DateTextBox('paymentDate');
 		$paymentDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		if($userLevel!=1){ // NOt Admin
			$paymentDate->setAttribs(array(
				'readOnly'=>'readOnly',
			));
		}
		$paymentDate->setValue(date("Y-m-d"));
		
		$paymentMethod = new Zend_Dojo_Form_Element_FilteringSelect('paymentMethod');
		$paymentMethod->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'onchange'=>'enablePayment();'
		));
		$opt = $dbGB->getVewOptoinTypeByType(2,1,3,1);
		$paymentMethod->setMultiOptions($opt);
		
		$bankId = new Zend_Dojo_Form_Element_FilteringSelect('bankId');
		$bankId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'false',
		));
		$rsBank = $dbGBStock->getAllBank();
		$optBank=array(''=>$tr->translate("SELECT_BANK"));
		if(!empty($rsBank))foreach($rsBank AS $row){
			$optBank[$row['id']]=$row['name'];
		}
		$bankId->setMultiOptions($optBank);
		
		$accNameAndChequeNo = new Zend_Dojo_Form_Element_TextBox('accNameAndChequeNo');
    	$accNameAndChequeNo->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'required'=>'false',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("ACCOUNT_AND_CHEQUE_NO"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:50px !important; max-width:99%;'));
		
		$totalAmount = new Zend_Dojo_Form_Element_TextBox('totalAmount');
    	$totalAmount->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("TOTAL"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$totalAmount->setValue(0);
		
		$_arr = array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("VOID"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
				
		$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    	));
		
		
		$advanceFilter = new Zend_Dojo_Form_Element_TextBox('advanceFilter');
		$advanceFilter->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside height-text',
				'placeholder'=>$tr->translate("SEARCH"),
				'missingMessage'=>$tr->translate("Forget Enter Receipt No")
		));
		
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'placeholder'=>$tr->translate('START_DATE'),
				'dojoType'=>"dijit.form.DateTextBox",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
			$start_date->setValue($_date);
		}
		
		 
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
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
		
		if(!empty($data)){
			$branch_id->setValue($data['projectId']);
			$paymentNo->setValue($data['paymentNo']);
			$external_invoice->setValue($data['externalInvoice']);
			$expense_title->setValue($data['expenseTitle']);
			$receiver->setValue($data['receiver']);
			$paymentMethod->setValue($data['paymentMethod']);
			$bankId->setValue($data['bankId']);
			$accNameAndChequeNo->setValue($data['accNameAndChequeNo']);
			$totalAmount->setValue($data['totalAmount']);
			$paymentDate->setValue($data['paymentDate']);
			$budgetItem->setValue($data['budgetId']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
		///	$start_date->setValue("");
		}
		
		$this->addElements(array(
				$branch_id,
				$paymentNo,
				$paymentDate,
				$paymentMethod,
				$bankId,
				$accNameAndChequeNo,
				$note,
				$totalAmount,
				$_status,
				$id,

				$advanceFilter,
				$start_date,
				$end_date,
				$expense_title ,
				$external_invoice ,
				$receiver,
				$budgetItem
		));
		return $this;
	}
}