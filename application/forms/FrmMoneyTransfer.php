<?php

class Application_Form_FrmMoneyTransfer extends Zend_Dojo_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	$province=new Zend_Dojo_Form_Element_ComboBox('province');		
		$province->setLabel('province')							
				 ->setRequired(true);									
		
		$agent=new Zend_Dojo_Form_Element_ComboBox('agent');		
		$agent->setLabel('agent')							
			 ->setRequired(true);
			 
						 
		$sender=new Zend_Dojo_Form_Element_ValidationTextBox('sender');
		$sender->setLabel('sender')
			   ->setRequired(true);
		
		$reciever=new Zend_Dojo_Form_Element_ValidationTextBox('reciever');
		$reciever->setLabel('reciever')
				 ->setRequired(true);
			   
		$reciever_tel=new Zend_Dojo_Form_Element_ValidationTextBox('reciever_tel');
		$reciever_tel->setLabel('reciever_tel')
				 	 ->setRequired(true);
				 	 
		$send_date = new Zend_Dojo_Form_Element_DateTextBox('send_date');
		$send_date->setLabel('send_date')
				  ->setRequired(true);

		$exp_date = new Zend_Dojo_Form_Element_DateTextBox('expire_date');
		$exp_date->setLabel('expire date')
				  ->setRequired(true);
				  
		$type_money=new Zend_Dojo_Form_Element_ComboBox('type_money');		
		$type_money->setLabel('type_money')							
				   ->setRequired(true);	  

		$amount = new Zend_Dojo_Form_Element_CurrencyTextBox('asd');
				   
		$submit_login=new Zend_Dojo_Form_Element_SubmitButton('submit_login');				
		$submit_login->setLabel('ចាប់​ផ្តើម');

		$clear_login = new Zend_Dojo_Form_Element_Button('clear_login');
		$clear_login->setLabel("សារ​ដើម");		
		
												
		$this->addElements(array($province,$agent,$sender,$reciever,$reciever_tel,$send_date,$exp_date,$type_money,$submit_login,$clear_login));
    }


}

