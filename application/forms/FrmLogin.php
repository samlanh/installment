<?php

class Application_Form_FrmLogin extends Zend_Dojo_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
    	
    	   
		$user_name_login=new Zend_Dojo_Form_Element_ValidationTextBox('txt_user_name');		
		$user_name_login->setLabel('ឈ្មោះ​អ្នក​ប្រើ​ប្រាស់')			
						->setRequired(true);
									
									
		
		$password_login=new Zend_Dojo_Form_Element_PasswordTextBox('txt_password');
		$password_login->setLabel('ពាក្យ​​សំងាត់')
						->setRequired(true);
		
		$submit_login=new Zend_Dojo_Form_Element_SubmitButton('submit_login');				
		$submit_login->setLabel('ចាប់​ផ្តើម');

		$clear_login = new Zend_Dojo_Form_Element_Button('clear_login');
		$clear_login->setLabel("សារ​ដើម");		
		
												
		$this->addElements(array($user_name_login,$password_login,$submit_login,$clear_login));
    }


}

