<?php 
Class Loan_Form_FrmMultiSalePaymemt extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmMultiSalePaymemt($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 
		$userInfo = $db->getUserInfo();
		$userLevel = empty($userInfo['level'])?0:$userInfo['level'];
		
		$_branchId = new Zend_Dojo_Form_Element_FilteringSelect('branchId');
		$_branchId->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'filterClient();'
		));
		$options = $db->getAllBranchName(null,1);
		$_branchId->setMultiOptions($options);	
		
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
		$_datePayment = new Zend_Dojo_Form_Element_DateTextBox('datePayment');
		$_datePayment->setAttribs(array(
			'dojoType'=>'dijit.form.DateTextBox',
			'required' =>'true',
			'class'=>'fullside',
			'constraints'=>"{".$constraintsDate."datePattern:'dd/MM/yyyy'}",
			'readOnly' =>$paymentDateEnable,
		));
		$_datePayment->setValue(date("Y-m-d"));
		
		$_totalInterest = new Zend_Dojo_Form_Element_TextBox("totalInterest");
		$_totalInterest->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
			'required' =>'true',
			'readOnly' =>'true'
		));
		
		$_totalPrinciple = new Zend_Dojo_Form_Element_NumberTextBox('totalPrinciple');
		$_totalPrinciple->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required' =>'true',
			'readOnly' =>'true'
		));
		
		$_totalPayment = new Zend_Dojo_Form_Element_NumberTextBox('totalPayment');
		$_totalPayment->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required' =>'true',
			'readOnly' =>'true'
		));
		
		$_totalAllPaid = new Zend_Dojo_Form_Element_NumberTextBox('totalAllPaid');
		$_totalAllPaid->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required' =>'true',
			'readOnly' =>'true'
		));
		
		$_totalBalance = new Zend_Dojo_Form_Element_NumberTextBox('totalBalance');
		$_totalBalance->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required' =>'true',
			'readOnly' =>'true'
		));
		
		$_note = new Zend_Dojo_Form_Element_Textarea("note");
		$_note->setAttribs(array(
			'dojoType'=>'dijit.form.Textarea',
			'class'=>'fullside',
			'style'=>'width:100%; min-height:103px; font-size:14px; font-family:inherit;'
		));
		
		$_paymentMethod = new Zend_Dojo_Form_Element_FilteringSelect('paymentMethod');
		$_paymentMethod->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'class'=>'fullside',
			'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$_paymentMethod->setMultiOptions($opt);
		
		$_bankId = new Zend_Dojo_Form_Element_FilteringSelect('bankId');
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
		
		$_cheque = new Zend_Dojo_Form_Element_TextBox("cheque");
		$_cheque->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("ACCOUNT_AND_CHEQUE_NO"),
			'style'=>'color:red;font-weight: 600;',
			'missingMessage'=>$this->tr->translate("Forget Enter Data")
		));
		
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate("ACCOUNT_AND_CHEQUE_NO"),
			'style'=>'color:red;font-weight: 600;'
		));
		
		if($data!=null){
			$_branchId->setValue($data['branchId']);
			
			$_datePayment->setValue($data['datePayment']);
			$_totalInterest->setValue($data['totalInterest']);
			$_totalPrinciple->setValue($data['totalPrinciple']);
			$_totalPayment->setValue($data['totalPayment']);
			$_totalAllPaid->setValue($data['totalAllPaid']);
			$_totalBalance->setValue($data['totalBalance']);

			$_note->setValue($data['note']);
			
			$_paymentMethod->setValue($data['paymentMethod']);
			$_bankId->setValue($data['bankId']);
			$_cheque->setValue($data['cheque']);
			$_id->setValue($data['id']);
		}
		$this->addElements(
				array(
				$_id
				,$_branchId
				,$_datePayment
				
				,$_totalInterest
				,$_totalPrinciple
				,$_totalPayment
				,$_totalAllPaid
				,$_totalBalance

				,$_note
				,$_paymentMethod
				,$_bankId
				,$_cheque
				));
		return $this;
		
	}	
}