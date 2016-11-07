<?php 
Class Callecterall_Form_Frmcallecteralllist extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		
	}
	public function callecteralllist($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$branch = new Zend_Dojo_Form_Element_FilteringSelect('branch'); 
		$branch->setAttribs(array( 'dojoType'=>'dijit.form.FilteringSelect', 'class'=>'fullside', 'required' =>'true' )); 
		$rows = $db->getAllBranchName(); $options=''; if(!empty($rows))foreach($rows AS $row){ $options[$row['br_id']]=$row['branch_namekh']; } 
		$branch->setMultiOptions($options);	    
		
		$id_client = $db->getNewReceiptId();
		$receipt = new Zend_Dojo_Form_Element_TextBox('receipt');
		$receipt->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true,
				'readonly'=>true,
				'style'=>'color:red; font-weight: bold;'
		));
		
		$receipt->setValue($id_client);
	
		$id_client = $db->getCodecallId();
		$code_call = new Zend_Dojo_Form_Element_TextBox('code_call');
		$code_call->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>true,
				'style'=>'color:red; font-weight: bold;'
		));
		$code_call->setValue($id_client);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$customer_name = new Zend_Dojo_Form_Element_FilteringSelect('customer_name');
		$customer_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>"getClientInfo(1);"
		));
		$rows = $db->getClientByType();
		$options=array(''=>"------Select------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['client_id']]=$row['name_en'];
		$customer_name->setMultiOptions($options);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$cus_code = new Zend_Dojo_Form_Element_FilteringSelect('cus_code');
		$cus_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true,
				'onchange'=>"getClientInfo(2);"
				));
		$rows = $db->getClientByType();
		$options=array(''=>"------Select------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['client_id']]=$row['client_number'];
		$cus_code->setMultiOptions($options);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$callecterall_type = new Zend_Dojo_Form_Element_FilteringSelect('callecterall_type');
		$callecterall_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt= $db->getVewOptoinTypeBys(1);
		$callecterall_type->setMultiOptions($opt);
		//$callecterall_type->setValue();
		
		$nameouner= new Zend_Dojo_Form_Element_TextBox('nameouner');
		$nameouner->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$callnumber= new Zend_Dojo_Form_Element_TextBox('callnumber');
		$callnumber->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$date_call = new Zend_Dojo_Form_Element_DateTextBox('date_call');
		$date_call->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'onchange'=>'checkReleaseDate();',
				'required'=>true
		));
		$date_call->setValue(date('Y-m-d'));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$time_think = new Zend_Dojo_Form_Element_FilteringSelect('time_think');
		$time_think->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'checkTerm();',
				'required'=>true
		));
		$opt= $db->getVewOptoinTypeByType(14,1);
		$time_think->setMultiOptions($opt);
		$time_think->setValue(1);
	
		
		$time_boro = new Zend_Dojo_Form_Element_NumberTextBox('time_boro');
		$time_boro->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'calCulatePeriod()',
				'required'=>true
		));
		
		$dayless= new Zend_Dojo_Form_Element_DateTextBox('dayless');
		$dayless->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				
		));
		$dayless->setValue(date('Y-m-d'));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$cash_type = new Zend_Dojo_Form_Element_FilteringSelect('cash_type');
		$cash_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt= $db->getVewOptoinTypeByType(15,1);
		$cash_type->setMultiOptions($opt);
		$cash_type->setValue(1);
		
		$much_boro = new Zend_Dojo_Form_Element_NumberTextBox('much_boro');
		$much_boro->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$term_fun = new Zend_Dojo_Form_Element_FilteringSelect('term_fun');
		$term_fun->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_fun= $db->getVewOptoinTypeByType(14,1);
		$term_fun->setMultiOptions($opt_fun);
		$term_fun->setValue(1);
		
		$charge_term = new Zend_Dojo_Form_Element_FilteringSelect('charge_term');
		$charge_term->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true,
		));
		$opt_ch_term = array(1=>'គិតជាភាគរយ​ %',2=>'គិតជាលុយផ្ទាល់');
		$charge_term->setMultiOptions($opt_ch_term);
		
		$amount_money = new Zend_Dojo_Form_Element_TextBox('amount_money');
		$amount_money->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true,
		));
	  $_id = new Zend_Form_Element_Hidden('id');

		if($data!=null){
			$branch->setValue($data['branch']);
			$receipt->setValue($data['receipt']);
			$code_call->setValue($data['code_call']);
			$customer_name->setValue($data['customer_id']);
			$callecterall_type->setValue($data['type_call']);
			$nameouner->setValue($data['owner_call']);
			$callnumber->setValue($data['callnumber']);
			$date_call->setValue($data['date_debt']);
			$time_think->setValue($data['term']);
			$time_boro->setValue($data['amount_term']);
			$dayless->setValue($data['date_line']);
			$cash_type->setValue($data['curr_type']);
			$much_boro->setValue($data['amount_debt']);
			$note->setValue($data['note']);
			$_id->setValue($data['id']);
			$cus_code->setValue($data['customer_id']);
			$amount_money->setValue($data['amount_money']);
			$charge_term->setValue($data['charge_term']);
			$term_fun->setValue($data['term_fun']);
		}
		$this->addElements(array($amount_money,$branch,$customer_name,$cus_code,$receipt,$time_think,$time_boro,$term_fun,$charge_term,
				$date_call,$callecterall_type,$code_call,$note,$cash_type,$much_boro,$_id,$dayless,$callnumber,$nameouner));
		return $this;
		
	}	
}