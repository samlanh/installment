<?php

class Tellerandexchange_Form_Frmxchange extends Zend_Dojo_Form
{

    public function xchange()
    {
        /* Form Elements & Other Definitions Here ... */
    	
    	$Onetomany=new Zend_Dojo_Form_Element_FilteringSelect('onetomany');
    	$Onetomany->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect'));
    	$name=array(1=>'ពីមួយទៅច្រើន',2=>'ពីច្រើនទៅមួយ',3=>'ពីមួយទៅមួយ');
    	$Onetomany->setMultiOptions($name);
    	
    	$moneyinaccount=new Zend_Dojo_Form_Element_TextBox('moneyinaccount');
    	$moneyinaccount->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox'));
    	$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
    	$Date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox'));
    	$Date->setValue(date('Y-m-d'));
    	$Cusomer=new Zend_Dojo_Form_Element_FilteringSelect('customer');
    	$Cusomer->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect'));
    	$name=array(1=>'General Customer');
    	$Cusomer->setMultiOptions($name);
    	
    	$number_code=new Zend_Dojo_Form_Element_TextBox('number_code');
    	$number_code->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'readOnly'=>true,'style'=>'color:red'));
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$iv_numbber = $db->getNewInvoiceExchange();
    	$number_code->setValue($iv_numbber);
    	
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
				'required' =>'true'
		));
		
		$rows = $db->getAllBranchName();
		$options='';
			if(!empty($rows))foreach($rows AS $row){
				$options[$row['br_id']]=$row['branch_namekh'];
			}
		$_branch_id->setMultiOptions($options);
		
		$sell_dollar=new Zend_Dojo_Form_Element_NumberTextBox('sell_dollar');
		$sell_dollar->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox'));
		$sell_dollar->setValue(0);
		
		$sell_riel=new Zend_Dojo_Form_Element_NumberTextBox('sell_riel');
		$sell_riel->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox'));
		$sell_riel->setValue(0);
		
		$sell_bath=new Zend_Dojo_Form_Element_NumberTextBox('sell_bath');
		$sell_bath->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox'));
		$sell_bath->setValue(0);
		
    	$Payusa=new Zend_Dojo_Form_Element_NumberTextBox('payusa');
    	$Payusa->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox'));
    	$Payusa->setValue(0);
    	
    	$Payr=new Zend_Dojo_Form_Element_NumberTextBox('payr');
    	$Payr->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox'));
    	$Payr->setValue(0);
    	
    	$Payb=new Zend_Dojo_Form_Element_NumberTextBox('payb');
    	$Payb->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox'));
    	$Payb->setValue(0);
    	
    	$Getusa=new Zend_Dojo_Form_Element_NumberTextBox('getusa');
    	$Getusa->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'onkeyup'=>'returnMoney(2);'));
    	$Getusa->setValue(0);
    	
    	$Getr=new Zend_Dojo_Form_Element_NumberTextBox('getr');
    	$Getr->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'onkeyup'=>'returnMoney(1);'));
    	$Getr->setValue(0);
    	
    	$Getb=new Zend_Dojo_Form_Element_NumberTextBox('getb');
    	$Getb->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'onkeyup'=>'returnMoney(3);'));
    	$Getb->setValue(0);
    	
    	$Returnb=new Zend_Dojo_Form_Element_NumberTextBox('returnb');
    	$Returnb->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox'));
    	$Returnb->setValue(0);
    	
    	$Returnusa=new Zend_Dojo_Form_Element_NumberTextBox('returnusa');
    	$Returnusa->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox'));
    	$Returnusa->setValue(0);
    	
    	$Returnr=new Zend_Dojo_Form_Element_NumberTextBox('returnr');
    	$Returnr->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox'));
    	$Returnr->setValue(0);
		$this->addElements(array($_branch_id,$Cusomer,$Date,$Onetomany,$Payusa,$Payr,$Payb,
				$Getusa,$Getr,$Getb,$Returnusa,$Returnb,$Returnr,$number_code
				,$sell_dollar,$sell_riel,$sell_bath));
		return $this;
    }


}

