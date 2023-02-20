<?php

class Application_Form_FrmAdvanceSearchStock extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function AdvanceSearch($data=null,$type=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$dbGB = new Application_Model_DbTable_DbGlobal(); 
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
				));
		$_title->setValue($request->getParam("adv_search"));

		$budgetItem = new Zend_Dojo_Form_Element_FilteringSelect('budgetItem');
		$budgetItem->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
		));
		
		$budgetItem->setMultiOptions($dbGBStock->getAllBudgetItem(0,'', '',1,null));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$statusAcc=  new Zend_Dojo_Form_Element_FilteringSelect('statusAcc');
		$statusAcc->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_statusAccOpt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("VOID"));
		$statusAcc->setMultiOptions($_statusAccOpt);
		$statusAcc->setValue($request->getParam("statusAcc"));
		
		
		$closingStatus=  new Zend_Dojo_Form_Element_FilteringSelect('closingStatus');
		$closingStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$closingStatusOpt = array(
				-1=>$this->tr->translate("ALL"),
				0=>$this->tr->translate("UNCLOSE"),
				1=>$this->tr->translate("CLOSED"),
			);
		$closingStatus->setMultiOptions($closingStatusOpt);
		$closingStatus->setValue($request->getParam("closingStatus"));
		
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'glyphicon glyphicon-search',
				'class'=>'button-class button-primary',
				'label'=>$this->tr->translate("SEARCH")
				
				));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$rows = $dbGB->getAllBranchName();
		$options_branch=array('-1'=>$this->tr->translate("SELECT_BRANCH"));
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
		
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'placeholder'=>$this->tr->translate('START_DATE'),
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			//$_date = date("Y-m-d");
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$to_date->setValue($_date);
		
		$checkingStatus=  new Zend_Dojo_Form_Element_FilteringSelect('checkingStatus');
		$checkingStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = array(
				0=>$this->tr->translate("CHECKING_STATUS"),
				1=>$this->tr->translate("APPROVED"),
				2=>$this->tr->translate("REJECTED"),
				);
		$checkingStatus->setMultiOptions($_opts);
		$checkingStatus->setValue($request->getParam("checkingStatus"));
		
		$pCheckingStatus=  new Zend_Dojo_Form_Element_FilteringSelect('pCheckingStatus');
		$pCheckingStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = array(
				0=>$this->tr->translate("PCHECKING_STATUS"),
				1=>$this->tr->translate("APPROVED"),
				2=>$this->tr->translate("REJECTED"),
				);
		$pCheckingStatus->setMultiOptions($_opts);
		$pCheckingStatus->setValue($request->getParam("pCheckingStatus"));
		
		$approveStatus=  new Zend_Dojo_Form_Element_FilteringSelect('approveStatus');
		$approveStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = array(
				0=>$this->tr->translate("APPROVED_STATUS"),
				1=>$this->tr->translate("APPROVED"),
				2=>$this->tr->translate("REJECTED"),
				);
		$approveStatus->setMultiOptions($_opts);
		$approveStatus->setValue($request->getParam("approveStatus"));
		
		$processingStatus = new Zend_Dojo_Form_Element_FilteringSelect('processingStatus');
		$processingStatus->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$arrProc = array('typeStep'=>4);
		$rowProcess = $dbGBStock->requestingProccess($arrProc);
		$optionsProcess=array(0=>$this->tr->translate("SELECT_PROCESSING"));
		if(!empty($rowProcess))foreach($rowProcess AS $row){
			$optionsProcess[$row['id']]=$row['name'];
		}
		$processingStatus->setMultiOptions($optionsProcess);
		$processingStatus->setValue($request->getParam("processingStatus"));
		
		$supplierId = new Zend_Dojo_Form_Element_FilteringSelect('supplierId');
		$supplierId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$rowSupplier = $dbGBStock->getAllSupplier();
		$optionsSupplier=array(0=>$this->tr->translate("SELECT_SUPPLIER"));
		if(!empty($rowSupplier))foreach($rowSupplier AS $row){
			$optionsSupplier[$row['id']]=$row['name'];
		}
		$supplierId->setMultiOptions($optionsSupplier);
		$supplierId->setValue($request->getParam("supplierId"));

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
		$optionsPurchasingType=array(0=>$this->tr->translate("SELECT_PURCHASE_TYPE"));
		if(!empty($rowPurchasingType))foreach($rowPurchasingType AS $row){
			$optionsPurchasingType[$row['id']]=$row['name'];
		}
		$purchaseType->setMultiOptions($optionsPurchasingType);
		$purchaseType->setValue($request->getParam("purchaseType"));
		
		$invoiceType = new Zend_Dojo_Form_Element_FilteringSelect('invoiceType');
		$invoiceType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$arrProc = array('typeKeyIndex'=>4);
		$rowInvoiceType = $dbGBStock->invoiceTypeKey($arrProc);
		$optionsInvoiceType=array(0=>$this->tr->translate("SELECT_INVOICE_TYPE"));
		if(!empty($rowInvoiceType))foreach($rowInvoiceType AS $row){
			$optionsInvoiceType[$row['id']]=$row['name'];
		}
		$invoiceType->setMultiOptions($optionsInvoiceType);
		$invoiceType->setValue($request->getParam("invoiceType"));
		
		
		$paymentMethod = new Zend_Dojo_Form_Element_FilteringSelect('paymentMethod');
		$paymentMethod->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
		));
		$rsOption = $dbGB->getVewOptoinTypeByType(2);
		$optMethod=array(''=>$this->tr->translate("PLEASE_SELECT"));
		if(!empty($rsOption))foreach($rsOption AS $row){
			$optMethod[$row['key_code']]=$row['name_en'];
		}
		$paymentMethod->setMultiOptions($optMethod);
		$paymentMethod->setValue($request->getParam("paymentMethod"));
		
		$bankId = new Zend_Dojo_Form_Element_FilteringSelect('bankId');
		$bankId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'false',
		));
		$rsBank = $dbGBStock->getAllBank();
		$optBank=array(''=>$this->tr->translate("SELECT_BANK"));
		if(!empty($rsBank))foreach($rsBank AS $row){
			$optBank[$row['id']]=$row['name'];
		}
		$bankId->setMultiOptions($optBank);
		$bankId->setValue($request->getParam("bankId"));
		
		$statusWithdraw=  new Zend_Dojo_Form_Element_FilteringSelect('statusWithdraw');
		$statusWithdraw->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = array(
				0=>$this->tr->translate("STATUS"),
				1=>$this->tr->translate("WITHDRAWN"),
				2=>$this->tr->translate("NOT_YET_WITHDRAW"),
				);
		$statusWithdraw->setMultiOptions($_opts);
		$statusWithdraw->setValue($request->getParam("statusWithdraw"));
		
		
		$_arr = array(0=>$this->tr->translate("ALL"),1=>$this->tr->translate("LOCAL"),2=>$this->tr->translate("OVER_SEA"));
    	$supplierType = new Zend_Dojo_Form_Element_FilteringSelect("supplierType");
    	$supplierType->setMultiOptions($_arr);
    	$supplierType->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',)
		);
		$supplierType->setValue($request->getParam("supplierType"));
		
		$verifyStatus =  new Zend_Dojo_Form_Element_FilteringSelect('verifyStatus');
		$verifyStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = $dbGBStock->getViewById(5,1);
		$verifyStatus->setMultiOptions($_opts);
		$verifyStatus->setValue($request->getParam("verifyStatus"));
		
		$isPaidStatus=  new Zend_Dojo_Form_Element_FilteringSelect('isPaidStatus');
		$isPaidStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_isPaidStatusOpt = array(
				0=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("NOT_YET_PAID"),
				2=>$this->tr->translate("COMPLETED_PAYMENT"),
				3=>$this->tr->translate("SOME_PAID")
				);
		$isPaidStatus->setMultiOptions($_isPaidStatusOpt);
		$isPaidStatus->setValue($request->getParam("isPaidStatus"));
		
		$reqPOStatus=  new Zend_Dojo_Form_Element_FilteringSelect('reqPOStatus');
		$reqPOStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$reqPOStatusOpt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("COMPLETED_PO"),
				0=>$this->tr->translate("UPCOMPLETED_PO"),
				);
		$reqPOStatus->setMultiOptions($reqPOStatusOpt);
		$reqPOStatus->setValue($request->getParam("reqPOStatus"));
		
		
		$requestStatusCheck=  new Zend_Dojo_Form_Element_FilteringSelect('requestStatusCheck');
		$requestStatusCheck->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_optsRequestStatusCheck = array(
				0=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("APPROVED"),
				2=>$this->tr->translate("REJECTED"),
				);
		$requestStatusCheck->setMultiOptions($_optsRequestStatusCheck);
		$requestStatusCheck->setValue($request->getParam("requestStatusCheck"));
		
		$recivedProPO=  new Zend_Dojo_Form_Element_FilteringSelect('recivedProPO');
		$recivedProPO->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_optsRecivedProPO = array(
				0=>$this->tr->translate("PRODUCT_RECEIVED_STATUS"),
				1=>$this->tr->translate("PENDING"),
				2=>$this->tr->translate("SOME_RECEIVED"),
				3=>$this->tr->translate("COMPLETED_RECEIVED"),
				);
		$recivedProPO->setMultiOptions($_optsRecivedProPO);
		$recivedProPO->setValue($request->getParam("recivedProPO"));
		
		$dateFilterOpt=  new Zend_Dojo_Form_Element_FilteringSelect('dateFilterOpt');
		$dateFilterOpt->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_optsDateFilterOpt = array(
				0=>$this->tr->translate("NORMAL"),
				1=>$this->tr->translate("BY_PRODUCT_DATE_INCOMING"),
				);
		$dateFilterOpt->setMultiOptions($_optsDateFilterOpt);
		$dateFilterOpt->setValue($request->getParam("dateFilterOpt"));
		
		$this->addElements(
			array(
				$verifyStatus,
				$_title,
				$_status,
				$statusAcc,
				$closingStatus,
				$_btn_search,
				$branch_id,
				$from_date,
				$to_date,
				
				$checkingStatus,			
				$pCheckingStatus,			
				$approveStatus,	
				
				$processingStatus,
				$supplierId,
				$invoiceType,				
				$purchaseType,				
				$paymentMethod,				
				$bankId,	
				$statusWithdraw,	
				
				$supplierType,
				$isPaidStatus,
				$reqPOStatus
				,$requestStatusCheck
				
				,$recivedProPO
				,$dateFilterOpt
				,$budgetItem	
			)
		);
		return $this;
	}
	
}