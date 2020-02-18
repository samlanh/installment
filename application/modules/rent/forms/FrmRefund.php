<?php 
Class Rent_Form_FrmRefund extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddRefund($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$_dbRent = new Rent_Model_DbTable_DbLanddeposit();
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$rows = $db ->getAllBranchByUser();
		$options=array('');
		if(!empty($rows)){foreach($rows AS $row) $options[$row['id']]=$row['name'];}
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getAllRentNumber();'
		));
		$branch_id->setMultiOptions($options);
		
		$c_date = date('Y-m-d');
		$next_payment = $c_date;
		if(date('H')>=16){
			$next_payment = date("Y-m-d", strtotime("$c_date +1 day"));
		}
		$refund_date = new Zend_Dojo_Form_Element_DateTextBox('refund_date');
		$refund_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>true,
				'constraints'=>'{datePattern:"dd-MM-yyyy"}'
		));
		$refund_date->setValue($c_date);
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_method->setMultiOptions($opt);
		
		$_cheque = new Zend_Dojo_Form_Element_TextBox('cheque');
		$_cheque->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		
		$cheque_issuer = new Zend_Dojo_Form_Element_FilteringSelect('cheque_issuer');
		$cheque_issuer->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'popupIssuer();'
		));
		
		$dbe = new Rent_Model_DbTable_DbRefund();
		$rscheque = $dbe->getAllChequeIssue();
		$opt1=array(''=>$this->tr->translate("SELECT_CHEQUE_ISSUE"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rscheque))foreach($rscheque AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$cheque_issuer->setMultiOptions($opt1);
		
		$_total_amount = new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$_total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'style'=>'color:red;',
				'readOnly' =>true,
		));
		$_total_amount->setValue(0);
		
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height:50px;'
				//'required' =>'true'
		));
		
		$refund_no = new Zend_Form_Element_Hidden("refund_no");
		$refund_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$customer_id = new Zend_Form_Element_Hidden("customer_id");
		$customer_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$id = new Zend_Form_Element_Hidden("id");
		$id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		
		if($data!=""){
			$id->setValue($data["id"]);
			$refund_no->setValue($data["refund_no"]);
			$branch_id->setValue($data["branch_id"]);
			$customer_id->setValue($data["customer_id"]);
			
			$refund_date->setValue($data["refund_date"]);
			$payment_method->setValue($data["payment_method"]);
			$_cheque->setValue($data["cheque"]);
			$cheque_issuer->setValue($data["cheque_issuer"]);
			$_total_amount->setValue($data["total_amount"]);
			$_note->setValue($data["note"]);
			
		}
		$this->addElements(array(
				$id,
				$refund_no,
				$branch_id,
				$customer_id,
				$refund_date,
				$payment_method,
				$_cheque,
				$cheque_issuer,
				$_total_amount,
				$_note
				));
		return $this;
		
	}
	
	public function FrmSearchRefund($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$_dbRent = new Rent_Model_DbTable_DbLanddeposit();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$rows = $db ->getAllBranchByUser();
		$options=array('');
		if(!empty($rows)){
			foreach($rows AS $row) $options[$row['id']]=$row['name'];
		}
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$branch_id->setMultiOptions($options);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$customer_id = new Zend_Dojo_Form_Element_FilteringSelect("customer_id");
		$opt_client = array(''=>$this->tr->translate("CHOOSE_CUSTOEMR"));
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$customer_id->setMultiOptions($opt_client);
		$customer_id->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',
				'required'=>'false'));
		$customer_id->setValue($request->getParam("customer_id"));
	
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_method->setMultiOptions($opt);
		$payment_method->setValue($request->getParam("payment_method"));
	
	
		$cheque_issuer = new Zend_Dojo_Form_Element_FilteringSelect('cheque_issuer');
		$cheque_issuer->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'popupIssuer();'
		));
	
		$dbe = new Rent_Model_DbTable_DbRefund();
		$rscheque = $dbe->getAllChequeIssue();
		$opt1=array(''=>$this->tr->translate("SELECT_CHEQUE_ISSUE"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rscheque))foreach($rscheque AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$cheque_issuer->setMultiOptions($opt1);
		$cheque_issuer->setValue($request->getParam("cheque_issuer"));
	
	
		$_start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_start_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'placeHolder'=>'Start Date',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
		}
		$_start_date->setValue($_date);
		
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		
		
		$_end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_end_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
		$_end_date->setValue($_date);
	
	
		if($data!=""){
				
		}
		$this->addElements(array(
				$_adv_search,
				$branch_id,
				$customer_id,
				$payment_method,
				$cheque_issuer,
				$_start_date,
				$_end_date
		));
		return $this;
	
	}

}