<?php 
Class Callecterall_Form_Frmrefundcallecterall extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function Frmrefundcallecterall(){
		$db = new Application_Model_DbTable_DbGlobal();
		$branch = new Zend_Dojo_Form_Element_FilteringSelect('branch'); 
		$branch->setAttribs(array( 'dojoType'=>'dijit.form.FilteringSelect', 'class'=>'fullside', 'required' =>'true' )); 
		$rows = $db->getAllBranchName(); $options=''; if(!empty($rows))foreach($rows AS $row){ $options[$row['br_id']]=$row['branch_namekh']; } 
		$branch->setMultiOptions($options);
		
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
		
		$callecterall_code = new Zend_Dojo_Form_Element_TextBox('callecterall_code');
		$callecterall_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>true,
				'style'=>'color:red; font-weight: bold;'
		));
		$client_id = new Zend_Dojo_Form_Element_FilteringSelect('client_id');
		$client_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		
		$fund_amount = new Zend_Dojo_Form_Element_TextBox('fund_amount');
		$fund_amount->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$date = new Zend_Dojo_Form_Element_FilteringSelect('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$innalization = new Zend_Dojo_Form_Element_TextBox('innalization');
		$innalization->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$stastu = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$stastu->setMultiOptions($_arr);
		$stastu->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$callecterall_type = new Zend_Dojo_Form_Element_FilteringSelect('callecterall_type');
		$callecterall_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt= $db->getVewOptoinTypeBys(1);
		$callecterall_type->setMultiOptions($opt);
		
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
		
		$this->addElements(array($branch,$stastu,$note,$innalization,$date,$fund_amount,$client_id,$cash_type,
				$customer_name,$cus_code,$callecterall_type,$dayless,$much_boro,$term_fun,$charge_term,$amount_money,
				$callecterall_code));
		return $this;
		
		}
	}