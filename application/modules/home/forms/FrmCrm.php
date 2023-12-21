<?php

class Home_Form_FrmCrm extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAddCRM($data){
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	$_dbuser = new Application_Model_DbTable_DbUsers();
    	$userid = $_dbgb->getUserId();
    	$userinfo = $_dbuser->getUserInfo($userid);
    	
//     	$dbCRM = new Home_Model_DbTable_DbCRM();
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
    	$_branch_id->setMultiOptions($_arr_opt_branch);
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
    	if (count($optionBranch)==1){
    		$_branch_id->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_id->setValue($row['id']);
    		}
    	}
    	
    	$kh_name = new Zend_Dojo_Form_Element_TextBox('kh_name');
    	$kh_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Name In Khmer")
    	));
    	
    	$_first_name = new Zend_Dojo_Form_Element_TextBox('first_name');
    	$_first_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>OTHER_LANG_REQUIRED,
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter First Name")
    			
    	));
    	
    	$_last_name = new Zend_Dojo_Form_Element_TextBox('last_name');
    	$_last_name->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>OTHER_LANG_REQUIRED,
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    			 
    	));
    	
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex = new Zend_Dojo_Form_Element_FilteringSelect("sex");
    	$_sex->setMultiOptions($_arr);
    	$_sex->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			
    			'class'=>'fullside height-text',));
    	
    	
    	$_arr = array(1=>$this->tr->translate("KHMER_KNOWLEDGE"),
    			2=>$this->tr->translate("ENGLISH_KNOWLEDGE"),
    			//3=>$this->tr->translate("UNIVERSITY"),
    			4=>$this->tr->translate("CHINESE_KNOWLEDGE"),
    			5=>$this->tr->translate("OTHER"));
    	$_ask_for = new Zend_Dojo_Form_Element_FilteringSelect("ask_for");
    	$_ask_for->setMultiOptions($_arr);
    	$_ask_for->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
    	
    	$_arr_opt_know = array(""=>$this->tr->translate("KNOW_BY"));
    	$optionKnowBy = $_dbgb->getAllKnowBy();
    	if(!empty($optionKnowBy))foreach($optionKnowBy AS $row) $_arr_opt_know[$row['id']]=$row['name'];
    	$_know_by = new Zend_Dojo_Form_Element_FilteringSelect("know_by");
    	$_know_by->setMultiOptions($_arr_opt_know);
    	$_know_by->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
    	
    	$_from_school = new Zend_Dojo_Form_Element_TextBox('old_school');
    	$_from_school->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("FROM_SCHOOL"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	
    	$reason=  new Zend_Form_Element_Textarea('reason');
    	$reason->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:70px !important;'));
    	$current_address=  new Zend_Form_Element_Textarea('current_address');
    	$current_address->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:60px !important;'));
    	
    	$_tel = new Zend_Dojo_Form_Element_TextBox('tel');
    	$_tel->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>' fullside height-text',
    			'missingMessage'=>$this->tr->translate("Forget Enter Tel")
    	
    	));
    	
    	$_tel_stu = new Zend_Dojo_Form_Element_TextBox('tel_stu');
    	$_tel_stu->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("PHONE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Tel")
    	));
    	
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit; width:99%;  min-height:100px !important;'));
    	
    	$_arr = array(1=>$this->tr->translate("PROGRESSING"),
    			2=>$this->tr->translate("WAITING_COMPLETED"),
    			3=>$this->tr->translate("COMPLETED"),
    			0=>$this->tr->translate("CANCEL"));
    	$_crm_status = new Zend_Dojo_Form_Element_FilteringSelect("crm_status");
    	$_crm_status->setMultiOptions($_arr);
    	$_crm_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	$kh_name_stu = new Zend_Dojo_Form_Element_TextBox('kh_name_stu');
    	$kh_name_stu->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("STUDENT_NAMEKHMER"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Name In Khmer")
    	));
    	 
    	$_first_name_stu = new Zend_Dojo_Form_Element_TextBox('first_name_stu');
    	$_first_name_stu->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("First Name"),
    			'missingMessage'=>$this->tr->translate("Forget Enter First Name")
    	));
    	 
    	$_last_name_stu = new Zend_Dojo_Form_Element_TextBox('last_name_stu');
    	$_last_name_stu->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("last Name"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Last Name")
    	));
    	
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex_stu = new Zend_Dojo_Form_Element_FilteringSelect("sex_stu");
    	$_sex_stu->setMultiOptions($_arr);
    	$_sex_stu->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside height-text',));
    	
    	$_age_stu = new Zend_Dojo_Form_Element_NumberTextBox('age_stu');
    	$_age_stu->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>' fullside height-text',
    			'placeholder'=>$this->tr->translate("AGE"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Age")
    	));
    	
    	$_arr_opt = array(""=>$this->tr->translate("SELECT_DEGREE"));
    	$Option = $_dbgb->getAllItems(1);
    	if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
    	$_degree = new Zend_Dojo_Form_Element_FilteringSelect("degree");
    	$_degree->setMultiOptions($_arr_opt);
    	$_degree->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'onChange'=>'getAllGrade();',
    			'class'=>'fullside height-text',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',));
    	 
    	$_arr = array(1=>$this->tr->translate("MALE"),2=>$this->tr->translate("FEMALE"));
    	$_sex_stu = new Zend_Dojo_Form_Element_FilteringSelect("sex_stu");
    	$_sex_stu->setMultiOptions($_arr);
    	$_sex_stu->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'class'=>'fullside height-text',));
    	
    	//for form Search
    	$advance_search = new Zend_Dojo_Form_Element_TextBox('advance_search');
    	$advance_search->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside height-text',
    			'placeholder'=>$this->tr->translate("SEARCH_HERE"),
    			'missingMessage'=>$this->tr->translate("SEARCH_HERE")
    	));
    	$advance_search->setValue($request->getParam("advance_search"));
    	
    	$_arr_opt_branch = array(""=>$this->tr->translate("SELECT_BRANCH"));
    	$optionBranch = $_dbgb->getAllBranch();
    	if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
    	$_branch_search = new Zend_Dojo_Form_Element_FilteringSelect("branch_search");
    	$_branch_search->setMultiOptions($_arr_opt_branch);
    	$_branch_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'autoComplete'=>'false',
				'required'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("SELECT_BRANCH"),
    			'class'=>'fullside height-text',));
    	$_branch_search->setValue($request->getParam("branch_search"));
    	if (count($optionBranch)==1){
    		$_branch_search->setAttribs(array('readonly'=>'readonly'));
    		if(!empty($optionBranch))foreach($optionBranch AS $row){
    			$_branch_search->setValue($row['id']);
    		}
    	}
    	
    	$_arr = array(""=>$this->tr->translate("ASK_FOR"),1=>$this->tr->translate("KHMER_KNOWLEDGE"),2=>$this->tr->translate("ENGLISH_KNOWLEDGE"),3=>$this->tr->translate("UNIVERSITY"),4=>$this->tr->translate("CHINESE_KNOWLEDGE"),5=>$this->tr->translate("OTHER"));
    	$_ask_for_search = new Zend_Dojo_Form_Element_FilteringSelect("ask_for_search");
    	$_ask_for_search->setMultiOptions($_arr);
    	$_ask_for_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("ASK_FOR"),
    			'class'=>'fullside height-text',));
    	
    	$_ask_for_search->setValue($request->getParam("ask_for_search"));
    	
    	$_arr_opt_know = array(""=>$this->tr->translate("KNOW_BY"));
    	$optionKnowBy = $_dbgb->getAllKnowBy();
    	if(!empty($optionKnowBy))foreach($optionKnowBy AS $row) $_arr_opt_know[$row['id']]=$row['name'];
    	$_know_by_search = new Zend_Dojo_Form_Element_FilteringSelect("know_by_search");
    	$_know_by_search->setMultiOptions($_arr_opt_know);
    	$_know_by_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("KNOW_BY"),
    			'class'=>'fullside height-text',));
    	$_know_by_search->setValue($request->getParam("know_by_search"));
    	
    	$_arr= $_dbgb->getcrmFollowupStatus();
    	$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
    	$_status_search->setMultiOptions($_arr);
    	$_status_search->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'autoComplete'=>'false',
				'required'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("STATUS"),
    			'class'=>'fullside height-text',));
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	
    	$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
    	$start_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'value'=>'now',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("START_DATE"),
    			'class'=>'fullside',));
    	$_date = $request->getParam("start_date");
    	$start_date->setValue($_date);
    		
    	$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
    	$date = date("Y-m-d");
    	$end_date->setAttribs(array(
    			'dojoType'=>"dijit.form.DateTextBox",
    			'class'=>'fullside',
    			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
    			'placeholder'=>$this->tr->translate("END_DATE"),
    			'required'=>'false'));
    	$_date = $request->getParam("end_date");
    	if(empty($_date)){
    		$_date = date("Y-m-d");
    	}
    	$end_date->setValue($_date);
    	
//     	$_arr_opt_crm = array(""=>$this->tr->translate("PLEASE_SELECT"));
//     	$optionCRM = $dbCRM->getAllCrmFilter();
//     	if(!empty($optionCRM))foreach($optionCRM AS $row) $_arr_opt_crm[$row['id']]=$row['name'];
//     	$_crm_list = new Zend_Dojo_Form_Element_FilteringSelect("crm_list");
//     	$_crm_list->setMultiOptions($_arr_opt_crm);
//     	$_crm_list->setAttribs(array(
//     			'dojoType'=>'dijit.form.FilteringSelect',
//     			'autoComplete'=>'false',
//     			'queryExpr'=>'*${0}*',
//     			'class'=>'fullside height-text',));
//     	$_crm_list->setValue($request->getParam("crm_list"));
    	
    	$_arr = $_dbgb->crmStatusprocess();
    	$_crmprocess  = new Zend_Dojo_Form_Element_FilteringSelect("crm_process");
    	$_crmprocess->setMultiOptions($_arr);
    	$_crmprocess->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
    	$_crmprocess->setValue($request->getParam('crm_process'));
    	
    	$followup = new Zend_Dojo_Form_Element_FilteringSelect("followup_status");
    	$followup->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'false',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
				'placeholder'=>$this->tr->translate("FOLLOWU_STATUS"),
    			'class'=>'fullside height-text',));
    	 
    	$_arr =array(
    			-1=>$this->tr->translate("FOLLOWU_STATUS"),
    			1=>$this->tr->translate("FOLLOW_UP"),
    			0=>$this->tr->translate("STOP_FOLLOW_UP"),
    	);
    	$followup->setMultiOptions($_arr);
    	
    	$followup->setValue($request->getParam('followup_status'));

		$_arr_opt = array(""=>$this->tr->translate("PLEASE_SELECT_SCHOOL_OPTION"));
		$Option = $_dbgb->getAllSchoolOption();
		if(!empty($Option))foreach($Option AS $row) $_arr_opt[$row['id']]=$row['name'];
		$school_option = new Zend_Dojo_Form_Element_FilteringSelect("school_option");
		$school_option->setMultiOptions($_arr_opt);
		$school_option->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'placeholder'=>$this->tr->translate("PLEASE_SELECT_SCHOOL_OPTION"),
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',));
		$school_option->setValue($request->getParam("school_option"));
    	
    	if(!empty($data)){
    		$_branch_id->setValue($data["branch_id"]);
    		$kh_name->setValue($data["kh_name"]);
    		$_first_name->setValue($data["first_name"]);
    		$_last_name->setValue($data["last_name"]);
    		$_sex->setValue($data["sex"]);
    		$_ask_for->setValue($data["ask_for"]);
    		$_know_by->setValue($data["know_by"]);
    		$_from_school->setValue($data["old_school"]);
    		$_tel->setValue($data["tel"]);
    		$reason->setValue($data["reason"]);
    		$_crm_status->setValue($data["crm_status"]);
    		$id->setValue($data["id"]);
    		$note->setValue($data["note"]);
    		$current_address->setValue($data["current_address"]);
    	}
    	
    	$this->addElements(array(
    			$followup,
    			$_crmprocess,
    			$_tel_stu,
    			$current_address,
    			$_branch_id,
    			$kh_name,
				$_first_name,
				$_last_name,
				$_sex,
    			$_ask_for,
    			$_know_by,
    			$_from_school,
    			$_tel,
				$reason,
				$_crm_status,
    			$id,
    			$note,
    			$kh_name_stu,
    			$_first_name_stu,
    			$_last_name_stu,
    			$_sex_stu,
    			$_age_stu,
    			$_degree,
    			$advance_search,
    			$_ask_for_search,
    			$_branch_search,
    			$_know_by_search,
    			$_status_search,
    			$start_date,
    			$end_date,
    			$_crm_list,
				$school_option
    			));
    	return $this;
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
    	
    	$_arr = array(0=>$this->tr->translate("DROPPED"),
    			1=>$this->tr->translate("PROCCESSING"),
    			2=>$this->tr->translate("WAITING_TEST"),
    			3=>$this->tr->translate("COMPLETED")
    			);
    	
    	$_proccess = new Zend_Dojo_Form_Element_FilteringSelect("proccess");
    	$_proccess->setMultiOptions($_arr);
    	$_proccess->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'class'=>'fullside height-text',));
    	
    	$followup = new Zend_Dojo_Form_Element_FilteringSelect("followup_status");
    	$followup->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
    			'onchange'=>'Followup();',
    			'class'=>'fullside height-text',));
    	
    	$_arr =array(
    			1=>$this->tr->translate("FOLLOW_UP"),
    			0=>$this->tr->translate("STOP_FOLLOW_UP"),
    			);
    	$followup->setMultiOptions($_arr);
    	
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
    			'autoComplete'=>'false',
    			'queryExpr'=>'*${0}*',
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
    		$_proccess->setValue($data["crm_status"]);
    		if($data["followup_statusId"]==0){
    			$next_contact->setAttrib('readOnly', true);
    		}
    		$followup->setValue($data["followup_status"]);
    	}
    	
    	$this->addElements(array(
    			$followup,
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