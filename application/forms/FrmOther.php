<?php

class Application_Form_FrmOther extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddDept($data){
    	$en_dept = new Zend_Dojo_Form_Element_TextBox('en_name');
    	$en_dept->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>'​ភ្លេច​បំពេញ​ ឈ្មោះ!'
    	));
    	
    	$kh_dept = new Zend_Dojo_Form_Element_TextBox('kh_name');
    	$kh_dept->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>'​ភ្លេច​បំពេញ​ ឈ្មោះ!'
    	));
    	
    	$_shortcut = new Zend_Dojo_Form_Element_TextBox('shortcut');
    	$_shortcut->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>'ភ្លេច​បំពេញ​ពាក្យកាត់!'
    			
    	));
    	
    	$mul_shortcut = new Zend_Dojo_Form_Element_TextBox('mul_shortcut');
    	$mul_shortcut->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>'ភ្លេច​បំពេញ​ពាក្យកាត់!'
    			 
    	));
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$mul_status = new Zend_Dojo_Form_Element_FilteringSelect("mul_status");
    	$mul_status->setMultiOptions($_arr);
    	$mul_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	
    	$_save = new Zend_Dojo_Form_Element_Button('save');
    	$_save->setAttribs(array(
    			'dojoType'=>'dijit.form.Button',
    			'onclick'=>"dijit.byId('frm_add_tran').submit();",
    			'class'=>'dijitEditorIcon',
    	));
    	$_save->setLabel($this->tr->translate("ADD_NEW"));
    	
    	$dept_id = new Zend_Form_Element_Hidden('dept_id');
    	
    	if(!empty($data)){
    		$dept_id->setValue($data['dept_id']);
    		$en_dept->setValue($data["en_name"]);
    		$kh_dept->setValue($data["kh_name"]);
    		$_shortcut->setValue($data["shortcut"]);
    		$_status->setValue($data["is_active"]);
    		
    	}
    	
    	$this->addElements(array($dept_id,$kh_dept,$en_dept,$_shortcut,$_status,$_save,$mul_status,$mul_shortcut));
    	return $this;
    }
    
    public function FrmAddMajor($data=null){
    
    	$_major_enname = new Zend_Dojo_Form_Element_TextBox('major_enname');
    	$_major_enname->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'full height-text',
    	));
    	$_major_khname = new Zend_Dojo_Form_Element_TextBox('major_khname');
    	$_major_khname->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'full height-text',
    	));
    	$_shortcut = new Zend_Dojo_Form_Element_TextBox('shortcut');
    	$_shortcut->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'full height-text',
    			 
    	));
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'full height-text',));
    	
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$rows = $_db->getGlobalDb('SELECT DISTINCT en_name,dept_id FROM rms_dept WHERE is_active=1 AND en_name !="" ');
    	$opt = "";
    	$opt = array(''=>'','-1'=>$this->tr->translate("ADD_NEW"));
    	if(!empty($rows))foreach($rows AS $row) $opt[$row['dept_id']]=$row['en_name']; 
    	//print_r($opt);exit();
    	$_dept = new Zend_Dojo_Form_Element_FilteringSelect("dept");
    	$_dept->setMultiOptions($opt);
    	$_dept->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'full height-text','onChange'=>'ShowPopupDept()'));
    	
    	$_majorid = new Zend_Form_Element_Hidden('major_id');
    	if(!empty($data)){
    		
    		$_majorid->setValue($data['major_id']);
    		$_major_enname->setValue($data["major_enname"]);
    		$_major_khname->setValue($data["major_khname"]);
    		$_dept->setValue($data["dept_id"]);
    		$_shortcut->setValue($data['shortcut']);
    		$_status->setValue($data["is_active"]);
    	}
    
    	$this->addElements(array($_majorid,$_major_enname,$_major_khname, $_dept, $_shortcut, $_status));
    	
    	return $this;
    
    }


}

