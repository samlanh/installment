<?php 
Class Po_Form_FrmPurchase extends Zend_Dojo_Form {

	public function FrmPurchase($data=null){
		
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
		
		$purchaseNo = new Zend_Dojo_Form_Element_TextBox('purchaseNo');
    	$purchaseNo->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("PO_NO"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		$date = new Zend_Dojo_Form_Element_DateTextBox('date');
 		$date->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		$date->setValue(date("Y-m-d"));
		
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
		
		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
    	
		$total = new Zend_Dojo_Form_Element_TextBox('total');
    	$total->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$tr->translate("TOTAL"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		$total->setValue(0);
		
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
	
		$requestDate = new Zend_Form_Element_Hidden('requestDate');
    	$requestDate->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    	));
		
		$categoryId = new Zend_Dojo_Form_Element_FilteringSelect('categoryId');
		$categoryId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getAllProduct();'
		));
		$rsCate = $dbGBStock->getAllCategoryProduct();
		$optCatePro=array(''=>$tr->translate("SELECT_CATEGORY"));
		if(!empty($rsCate))foreach($rsCate AS $row){
			$optCatePro[$row['id']]=$row['name'];
		}
		$categoryId->setMultiOptions($optCatePro);
		
		if(!empty($data)){
			$branch_id->setValue($data['projectId']);
			$purchaseNo->setValue($data['purchaseNo']);
			$supplierId->setValue($data['supplierId']);
			$date->setValue($data['date']);
			$note->setValue($data['note']);
			$total->setValue($data['total']);
			$_status->setValue($data['status']);
		}
		
		$this->addElements(array(
				$branch_id,
				$purchaseNo,
				$supplierId,
				$date,
				$note,
				$total,
				$_status,
				$id,
				
				$categoryId,
				$requestDate,
		));
		return $this;
	}
}