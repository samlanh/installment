<?php 
Class Po_Form_FrmSupplier extends Zend_Dojo_Form {

	public function FrmSupplier($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$textbox = 'dijit.form.TextBox';
		$numbertext='dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$dbGB = new Application_Model_DbTable_DbGlobal(); 
    	$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 
    	
		$_arr = array(1=>$tr->translate("LOCAL"),2=>$tr->translate("OVER_SEA"));
    	$supplierType = new Zend_Dojo_Form_Element_FilteringSelect("supplierType");
    	$supplierType->setMultiOptions($_arr);
    	$supplierType->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',)
		);
		
		$supplierName = new Zend_Dojo_Form_Element_TextBox('supplierName');
    	$supplierName->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("SUPPLIER_NAME"),
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		$address=  new Zend_Form_Element_Textarea('address');
    	$address->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
		
		$supplierTel=  new Zend_Dojo_Form_Element_TextBox('supplierTel');
    	$supplierTel->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside',
    			'placeholder'=>$tr->translate("TEL"),
				)
		);
		
		
		$contactName=  new Zend_Dojo_Form_Element_TextBox('contactName');
    	$contactName->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside',
    			'placeholder'=>$tr->translate("CONTACT_NAME"),
				)
		);
		
		$contactNumber=  new Zend_Dojo_Form_Element_TextBox('contactNumber');
    	$contactNumber->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside',
    			'placeholder'=>$tr->translate("CONTACT_NUMBER"),
				)
		);
		
		$receiverName=  new Zend_Dojo_Form_Element_TextBox('receiverName');
    	$receiverName->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside',
    			'placeholder'=>$tr->translate("RECEIVER_NAME"),
				)
		);
		
		$bankNumber=  new Zend_Dojo_Form_Element_TextBox('bankNumber');
    	$bankNumber->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside',
    			'placeholder'=>$tr->translate("BANK_NUMBER"),
				)
		);
		
		$email=  new Zend_Dojo_Form_Element_TextBox('email');
    	$email->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside',
    			'placeholder'=>$tr->translate("EMAIL"),
				)
		);
		
		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
			
		
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
	

		if(!empty($data)){
			
			$supplierType->setValue($data['supplierType']);
			$supplierName->setValue($data['supplierName']);
			$address->setValue($data['address']);
			$supplierTel->setValue($data['supplierTel']);
			$contactName->setValue($data['contactName']);
			$contactNumber->setValue($data['contactNumber']);
			$receiverName->setValue($data['receiverName']);
			$bankNumber->setValue($data['bankNumber']);
			$email->setValue($data['email']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
		}
		
		$this->addElements(array(
				$supplierType,
				$supplierName,
				$address,
				$supplierTel,
				$contactName,
				$contactNumber,
				$receiverName,
				$bankNumber,
				$email,
				$note,
				$_status,
				$id
		));
		return $this;
	}
}