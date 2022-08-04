<?php 
Class Invpayment_Form_FrmInvoice extends Zend_Dojo_Form {
// 	public function init()
// 	{
// 	}
	public function FrmInvoices($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$textbox = 'dijit.form.TextBox';
		$numbertext='dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$dbGB = new Application_Model_DbTable_DbGlobal(); 
    	$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 
    	
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
		
		$invoiceNo = new Zend_Dojo_Form_Element_TextBox('invoiceNo');
    	$invoiceNo->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("INVOICE_NO"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		$supplierInvoiceNo = new Zend_Dojo_Form_Element_TextBox('supplierInvoiceNo');
    	$supplierInvoiceNo->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("INVOICE_NO"),
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		$invoiceDate = new Zend_Dojo_Form_Element_DateTextBox('invoiceDate');
 		$invoiceDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		$invoiceDate->setValue(date("Y-m-d"));
		
		$receiveIvDate = new Zend_Dojo_Form_Element_DateTextBox('receiveIvDate');
 		$receiveIvDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		$receiveIvDate->setValue(date("Y-m-d"));
		
		
		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
				
		$totalInternal = new Zend_Dojo_Form_Element_TextBox('totalInternal');
    	$totalInternal->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("TOTAL"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$totalInternal->setValue(0);
		
		$vatInternal = new Zend_Dojo_Form_Element_TextBox('vatInternal');
    	$vatInternal->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("VAT"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$vatInternal->setValue(0);
		
		$totalAmount = new Zend_Dojo_Form_Element_TextBox('totalAmount');
    	$totalAmount->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("TOTAL"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$totalAmount->setValue(0);
		
		$totalExternal = new Zend_Dojo_Form_Element_TextBox('totalExternal');
    	$totalExternal->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("TOTAL"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$totalExternal->setValue(0);
		
		$vatExternal = new Zend_Dojo_Form_Element_TextBox('vatExternal');
    	$vatExternal->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("VAT"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$vatExternal->setValue(0);
		
		$otherFeeExternal = new Zend_Dojo_Form_Element_TextBox('otherFeeExternal');
    	$otherFeeExternal->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("TOTAL"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$otherFeeExternal->setValue(0);
		
		$totalAmountExternal = new Zend_Dojo_Form_Element_TextBox('totalAmountExternal');
    	$totalAmountExternal->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("TOTAL_AMOUNT"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$totalAmountExternal->setValue(0);
		
		$supplierId = new Zend_Form_Element_Hidden('supplierId');
    	$supplierId->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    	));
		
		$_arr = array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
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
		
		$dnId = new Zend_Form_Element_Hidden('dnId');
    	$dnId->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    	));
		
		$purchaseType = new Zend_Dojo_Form_Element_FilteringSelect('purchaseType');
		$purchaseType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$arrProc = array('typeKeyIndex'=>4);
		$rowPurchasingType = $dbGBStock->purchasingTypeKey($arrProc);
		$optionsPurchasingType=array();
		if(!empty($rowPurchasingType))foreach($rowPurchasingType AS $row){
			$optionsPurchasingType[$row['id']]=$row['name'];
		}
		$purchaseType->setMultiOptions($optionsPurchasingType);
		
		if(!empty($data)){
			$branch_id->setValue($data['projectId']);
			$invoiceNo->setValue($data['invoiceNo']);
			$supplierInvoiceNo->setValue($data['supplierInvoiceNo']);
			$invoiceDate->setValue($data['invoiceDate']);
			$receiveIvDate->setValue($data['receiveIvDate']);
			$note->setValue($data['note']);
			$totalInternal->setValue($data['totalInternal']);
			$vatInternal->setValue($data['vatInternal']);
			$totalAmount->setValue($data['totalAmount']);
			
			$totalExternal->setValue($data['totalExternal']);
			$vatExternal->setValue($data['vatExternal']);
			$otherFeeExternal->setValue($data['otherFeeExternal']);
			$totalAmountExternal->setValue($data['totalAmountExternal']);
			
			$supplierId->setValue($data['supplierId']);
			$id->setValue($data['id']);
			$_status->setValue($data['status']);
			$dnId->setValue($data['dnId']);
			$purchaseType->setValue($data['purchaseType']);
		}
		
		$this->addElements(array(
				$branch_id,
				$invoiceNo,
				$supplierInvoiceNo,
				$invoiceDate,
				$receiveIvDate,
				$note,
				$totalInternal,
				$vatInternal,
				$totalAmount,
				$totalExternal,
				$vatExternal,
				$otherFeeExternal,
				$totalAmountExternal,
				
				$supplierId,
				$id,
				$_status,
				$dnId,
				$purchaseType,
		));
		return $this;
	}
}