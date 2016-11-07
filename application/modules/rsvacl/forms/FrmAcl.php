<?php 
class RsvAcl_Form_FrmAcl extends Zend_Form
{
	public function init()
    {
//Module    	
    	$module=new Zend_Form_Element_Text('module');
    	$module->setAttribs(array(
    		'id'=>'module',
    		'class'=>'validate[required]',
    	));
    	$this->addElement($module);
    	
//Controller    	
    	$controller=new Zend_Form_Element_Text('controller');
    	$controller->setAttribs(array(
    		'id'=>'controller',
    	    'class'=>'validate[required]',
    	));
    	$this->addElement($controller);
    	
//Action   	
    	$action=new Zend_Form_Element_Text('action');
    	$action->setAttribs(array(
    		'id'=>'action',
    	 	'class'=>'validate[required]',
    	));
    	$this->addElement($action);
    }
}
?>

