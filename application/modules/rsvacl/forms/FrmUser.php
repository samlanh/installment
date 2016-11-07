<?php 
class RsvAcl_Form_FrmUser extends Zend_Form
{
	public function init()
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();	
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	
    	//user typefilter
		$sql = 'SELECT user_type_id,user_type FROM rms_acl_user_type';
		$rs=$db->getGlobalDb($sql);
		$options=array('All');
		$usertype = $request->getParam('user_type_filter');
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		$user_type_filter=new Zend_Form_Element_Select('user_type_filter');		
    	$user_type_filter->setMultiOptions($options);
    	$user_type_filter->setAttribs(array(
    		'id'=>'user_type_filter',
    		'class'=>'validate[required]',
    		'onchange'=>'this.form.submit()',
    	));
    	$user_type_filter->setValue($usertype);
    	$this->addElement($user_type_filter);
    	
    	
    	//uer title
    	$user_title = new Zend_Form_Element_Select("title");
    	$user_title->setMultiOptions(array("Mr"=>"Mr","Ms"=>"Ms"));
    	$this->addElement($user_title);
    	
    	//user full name
    	$user_fullname = new Zend_Form_Element_Text("name");
    	$user_fullname->setAttribs(array(
    		'id'=>'name',
    		'class'=>'validate[required]',
    	));
    	$this->addElement($user_fullname);
    	
    	//user cso
    	/*$user_cso = new Zend_Form_Element_Select("cso_id");
    	$user_cso->setMultiOptions($db->getOptionCSO());
    	$user_cso->setAttribs(array(
    		'id'=>'cso_id',
    		'class'=>'validate[required]',
    	));
    	$this->addElement($user_cso);*/
    	
    	//Select CSO
//     	$rs=$db->getGlobalDb('SELECT id, name_en FROM fi_cso');
// 			$options=array(''=>'Please select');
// 		foreach($rs as $read) $options[$read['id']]=$read['name_en'];
// 		$cso_id=new Zend_Form_Element_Select('cso_id');		
//     	$cso_id->setMultiOptions($options);
//     	$cso_id->setattribs(array('class'=>'validate[required]',));
//     	$this->addElement($cso_id);
    	
    	//user name    	
    	$user_name=new Zend_Form_Element_Text('username');
    	$user_name->setAttribs(array(
    		'id'=>'username',
    		'class'=>'validate[required]',
    		
    	));
    	$this->addElement($user_name);
    	
    	//email
    	$email=new Zend_Form_Element_Text('email');
    	$email->setAttribs(array(
    		'id'=>'email',
    		'class'=>'validate[required]',
    		
    	));
    	$this->addElement($email);
    	 
    	
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
    		'class'=>'validate[required]',
    	));
    	$this->addElement($confirm_password);
    	
    	//user type
		$sql = 'SELECT user_type_id,user_type FROM rms_acl_user_type';
		$rs=$db->getGlobalDb($sql);
		$options=array(''=>'Please select');
		foreach($rs as $read) $options[$read['user_type_id']]=$read['user_type'];
		$user_type_id=new Zend_Form_Element_Select('user_type_id');		
    	$user_type_id->setMultiOptions($options);
    	$user_type_id->setAttribs(array(
    		'id'=>'user_type_id',
    		'class'=>'validate[required]',
    	));
    	$this->addElement($user_type_id);
    }
}
?>
