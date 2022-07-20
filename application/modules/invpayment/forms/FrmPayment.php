<?php 
Class Invpayment_Form_FrmPayment extends Zend_Dojo_Form {
// 	public function init()
// 	{
// 	}
	public function FrmPayment($data=null){
		
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
		
		$paymentNo = new Zend_Dojo_Form_Element_TextBox('paymentNo');
    	$paymentNo->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("INVOICE_NO"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		$supplierId = new Zend_Dojo_Form_Element_FilteringSelect('supplierId');
		$supplierId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$rsSpp = $dbGBStock->getAllSupplier();
		$optSpp=array(''=>$tr->translate("SELECT_SUPPLIER"));
		if(!empty($rsSpp))foreach($rsSpp AS $row){
			$optSpp[$row['id']]=$row['name'];
		}
		$supplierId->setMultiOptions($optSpp);
		
		$paymentDate = new Zend_Dojo_Form_Element_DateTextBox('paymentDate');
 		$paymentDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
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
		));
		$rsBank = $dbGBStock->getAllBank();
		$optBank=array(''=>$tr->translate("SELECT_BANK"));
		if(!empty($rsBank))foreach($rsBank AS $row){
			$optBank[$row['id']]=$row['name'];
		}
		$bankId->setMultiOptions($optBank);
		
		$accNameAndChequeNo = new Zend_Dojo_Form_Element_TextBox('accNameAndChequeNo');
    	$accNameAndChequeNo->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("ACCOUNT_AND_CHUQE_NO"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
				
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
		
		if(!empty($data)){
			$branch_id->setValue($data['projectId']);
			$paymentNo->setValue($data['paymentNo']);
			$supplierId->setValue($data['supplierId']);
			$paymentDate->setValue($data['paymentDate']);
			$paymentMethod->setValue($data['paymentMethod']);
			$bankId->setValue($data['bankId']);
			$accNameAndChequeNo->setValue($data['accNameAndChequeNo']);
			$note->setValue($data['note']);
			$totalAmount->setValue($data['totalAmount']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
		}
		
		$this->addElements(array(
				$branch_id,
				$paymentNo,
				$supplierId,
				$paymentDate,
				$paymentMethod,
				$bankId,
				$accNameAndChequeNo,
				$note,
				$totalAmount,
				$_status,
				$id
		));
		return $this;
	}
}