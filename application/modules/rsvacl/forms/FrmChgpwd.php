<?php
class RsvAcl_Form_FrmChgpwd extends Zend_Form
{
	public function init()
	{
		//current password
		$current_password=new Zend_Form_Element_Password('current_password');
    	$current_password->setAttribs(array(
    		'id'=>'current_password',
    		'class'=>'validate[required]',
    	));	
    	$this->addElement($current_password);
		//password    	
    	$password=new Zend_Form_Element_Password('password');
    	$password->setAttribs(array(
    		'id'=>'password',
    		'class'=>'validate[required]',
    	));
    	$this->addElement($password);
		//confirm password    	
    	$confirm_password=new Zend_Form_Element_Password('confirm_password');
    	$confirm_password->setAttribs(array(
    		'id'=>'confirm_password',
    		'class'=>'validate[required, equals[password]]',
    	));
    	$this->addElement($confirm_password);
	}
}