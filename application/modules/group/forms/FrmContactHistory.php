<?php

class Group_Form_FrmContactHistory extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddCRMContactHistory($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$userinfo = $_dbgb->getUserInfo();
    	
    	$contact_date= new Zend_Dojo_Form_Element_DateTextBox('contact_date');
    	$contact_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d");
    	$contact_date->setValue($_date);
    	
    	$feedback=  new Zend_Form_Element_Textarea('feedback');
    	$feedback->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'required'=>'true',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_arr = array(
				0=>"បោះបង់ការទំនាក់ទំនង"
				,1=>"កំពុងដំណើរការណ៍ "
				,2=>"បន្តការទំនាក់ទំនង"
				,3=>"រង់ចាំណាត់ជួប"
				,4=>"បានណាត់ជួប"
				,5=>"បិទការលក់ "
				,6=>"ការកក់ប្រាក់"
				,7=>"ចុះកុងត្រា"
		);
    	$_proccess = new Zend_Dojo_Form_Element_FilteringSelect("proccess");
    	$_proccess->setMultiOptions($_arr);
    	$_proccess->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$next_contact= new Zend_Dojo_Form_Element_DateTextBox('next_contact');
    	$next_contact->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'class'=>'fullside',));
    	$_date = date("Y-m-d",strtotime("+15 day"));
    	$next_contact->setValue($_date);
    	
    	
    	$crm_id = new Zend_Form_Element_Hidden('id');
    	$recordbranhc="";
    	if (!empty($data['branch_id'])){
    		$recordbranhc=$data['branch_id'];
    	}
    	$_arr_opt_user = array();
    	$optionUser = $_dbgb->getAllUserGlobal($recordbranhc);
    	if(!empty($optionUser))foreach($optionUser AS $row) $_arr_opt_user[$row['id']]=$row['name'];
    	$_user_contact = new Zend_Dojo_Form_Element_FilteringSelect("user_contact");
    	$_user_contact->setMultiOptions($_arr_opt_user);
    	$_user_contact->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_user_contact->setValue($userinfo['user_id']);
    	
    	
    	if ($userinfo['level']!=1){
    		$contact_date->setAttribs(array(
    				'readonly'=>"readonly",
    		));
    		
    		$_user_contact->setAttribs(array(
    				'readonly'=>"readonly",
    		));
    	}
    	if(!empty($data)){
    		$crm_id->setValue($data["id"]);
//     		$_proccess->setValue($data["crm_status"]);
    	}
    	
    	
    	$this->addElements(array(
    			$contact_date,
    			$feedback,
    			$_proccess,
    			$next_contact,
    			$_user_contact,
    			$crm_id
    	));
    	return $this;
    }
}

