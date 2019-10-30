<?php

class Stock_Form_FrmItems extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddDegree($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	
    	$title = new Zend_Dojo_Form_Element_TextBox('title');
    	$title->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("TITLE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$title_en = new Zend_Dojo_Form_Element_TextBox('title_en');
    	$title_en->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("TITLE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Title")
    	));
    	
    	$_shortcut = new Zend_Dojo_Form_Element_TextBox('shortcut');
    	$_shortcut->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			//'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Shortcut")
    			
    	));
    	
    	$ordering = new Zend_Dojo_Form_Element_TextBox('ordering');
    	$ordering->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    	));
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    	));
    	$_date = $request->getParam("start_date");
    	
    	if(!empty($_date)){
    		$start_date->setValue($_date);
    	}
    	
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    	));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
    	$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT_DISTYPE"));
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important;'));
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
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
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(
    			array('dojoType'=>'dijit.form.TextBox',)
    			);
    	
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr = array(-1=>$this->tr->translate("ALL"),1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	if(!empty($data)){
    		$title->setValue($data["title"]);
    		$title_en->setValue($data["title_en"]);
    		$_shortcut->setValue($data["shortcut"]);
    		
    		$ordering->setValue($data["ordering"]);
    		$note->setValue($data["note"]);
    		$_status->setValue($data["status"]);
    		$id->setValue($data["id"]);
    	}
    	
    	$this->addElements(array(
    			$ordering,
    			$title,
    			$title_en,
    			$_shortcut,
    			$note,
    			$_save,
    			$start_date,
    			$end_date,
    			$_status,
    			$id,
    			$advance_search,
    			$_status_search,
    			));
    	return $this;
    }
}