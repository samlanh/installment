<?php 
Class Project_Form_FrmProject extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function Frmbranch($data=null,$copy=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_BRANCH_INFO"),
				'class'=>' fullside ',
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>' fullside ',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'value'=>' Search ',
				'class'=>' fullside ',
		
		));
		
		$br_id = new Zend_Dojo_Form_Element_TextBox('br_id');
		$br_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>'color:red',
				'onkeyup'=>'Calcuhundred()'
				));
		$br_code=Group_Model_DbTable_DbProject::getBranchCode();
		$br_id->setValue($br_code);
		
		$branch_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('branch_namekh');
		$branch_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Calfifty()'
				));
		$project_manager_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('project_manager_namekh');
		$project_manager_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
// 				'required'=>true,
// 				'onkeyup'=>'Calfifty()'
		));
		$project_manager_nameen = new Zend_Dojo_Form_Element_ValidationTextBox('project_manager_nameen');
		$project_manager_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$project_manager_nation_id = new Zend_Dojo_Form_Element_TextBox('project_manager_nation_id');
		$project_manager_nation_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
// 				'required'=>true,
		));
		
		$project_manager_nationality = new Zend_Dojo_Form_Element_ValidationTextBox('project_manager_nationality');
		$project_manager_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$project_manager_nationality->setValue("ខ្មែរ");
		
		$sc_project_manager_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('sc_project_manager_namekh');
		$sc_project_manager_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
				// 				'onkeyup'=>'Calfifty()'
		));
		$sc_project_manager_nameen = new Zend_Dojo_Form_Element_ValidationTextBox('sc_project_manager_nameen');
		$sc_project_manager_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sc_project_manager_nation_id = new Zend_Dojo_Form_Element_TextBox('sc_project_manager_nation_id');
		$sc_project_manager_nation_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$sc_project_manager_nationality = new Zend_Dojo_Form_Element_TextBox('sc_project_manager_nationality');
		$sc_project_manager_nationality->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$sc_project_manager_nationality->setValue("ខ្មែរ");
		$current_addres = new Zend_Dojo_Form_Element_Textarea('current_address');
		$current_addres->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
// 				'readOnly'=>'readOnly',
				'style'=>'width:100%;min-height:60px; font-size:13px; font-family:"Kh Battambang"'
		));
		$branch_nameen = new Zend_Dojo_Form_Element_FilteringSelect('project_type');
		$branch_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Caltweenty()'
				));
		//$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$db = new Application_Model_DbTable_DbGlobal();
		$propertiestype_opt = $db->getVewOptoinTypeByType(7,1,11);
		$branch_nameen->setMultiOptions($propertiestype_opt);
		
		/*$db=new Report_Model_DbTable_DbParamater();
		$rows=$db->getAllBranch();
		$opt_branch = array(''=>$this->tr->translate("SELECT_BRANCH_NAME"));
		if(!empty($rows))foreach($rows AS $row) $opt_branch[$row['br_id']]=$row['branch_nameen'];
		$select_branch_nameen = new Zend_Dojo_Form_Element_FilteringSelect('select_branch_nameen');
		$select_branch_nameen->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Caltweenty()'
		));
		$select_branch_nameen->setMultiOptions($opt_branch);
		$select_branch_nameen->setValue($request->getParam('select_branch_nameen'));*/
		
		$branch_code = new Zend_Dojo_Form_Element_NumberTextBox('branch_code');
		$branch_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red',
				'onkeyup'=>'Calcuhundred()'
				));
		$db_code=Group_Model_DbTable_DbProject::getBranchCode();
		$branch_code->setValue($db_code);
		
		$branch_tel = new Zend_Dojo_Form_Element_NumberTextBox('branch_tel');
		$branch_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfive()'
				));
		
		$_fax = new Zend_Dojo_Form_Element_TextBox('fax ');
		$_fax->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calone()'
				));
		
		///*** result of calculator ///***
		$branch_note = new Zend_Dojo_Form_Element_TextBox('branch_note');
		$branch_note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
// 				'readonly'=>true
				));
		
		$prefix_code = new Zend_Dojo_Form_Element_ValidationTextBox('prefix_code');
		$prefix_code->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		
		$branch_status = new Zend_Dojo_Form_Element_FilteringSelect('branch_status');
		$branch_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
// 				'readonly'=>true
				));
		$options = array(1=>$this->tr->translate("ACTIVE"), 2=>$this->tr->translate("DEACTIVE"));
		$branch_status->setMultiOptions($options);
		
		$branch_display = new Zend_Dojo_Form_Element_FilteringSelect('branch_display');
		$branch_display->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_EN"));
		$branch_display->setMultiOptions($_display_opt);
		
		$br_address = new Zend_Dojo_Form_Element_NumberTextBox('br_address');
		$br_address->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height: 60px;font-size:12px;'
		));
	
		$_p_manager_sex = new Zend_Dojo_Form_Element_FilteringSelect('p_manager_sex');
		$_p_manager_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_p_manager_sex->setMultiOptions($opt_status);
		
		$_dob_manager= new Zend_Dojo_Form_Element_DateTextBox('dob_manager');
		$_dob_manager->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		$date_iss_doc= new Zend_Dojo_Form_Element_DateTextBox('date_iss_doc');
		$date_iss_doc->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		$_csp_manager_sex = new Zend_Dojo_Form_Element_FilteringSelect('csp_manager_sex');
		$_csp_manager_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_csp_manager_sex->setMultiOptions($opt_status);
		
		$_dob_cs_manager= new Zend_Dojo_Form_Element_DateTextBox('dob_cs_manager');
		$_dob_cs_manager->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		$date_iss_doc_cs_manager= new Zend_Dojo_Form_Element_DateTextBox('date_iss_doc_cs_manager');
		$date_iss_doc_cs_manager->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','class'=>'fullside','constraints'=>"{datePattern:'dd/MM/yyyy'}",));
		
		$cs_manager_current_address = new Zend_Dojo_Form_Element_Textarea('cs_manager_current_address');
		$cs_manager_current_address->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				// 				'readOnly'=>'readOnly',
				'style'=>'width:100%;min-height:60px; font-size:13px; font-family:"Kh Battambang"'
		));
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$map = new Zend_Dojo_Form_Element_TextBox('map_url');
		$map->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$budget_amount = new Zend_Dojo_Form_Element_NumberTextBox('budget_amount');
		$budget_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$budget_amount->setValue(0);
		
		$position = new Zend_Dojo_Form_Element_TextBox('position');
		$position->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$w_position = new Zend_Dojo_Form_Element_TextBox('w_position');
		$w_position->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$contact_contruction = new Zend_Dojo_Form_Element_NumberTextBox('contact_contruction');
		$contact_contruction->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height: 60px;font-size:12px;'
		));
		$str_value='';
		$contact_contruction->setValue($str_value);
		
		
		$gm_phone = new Zend_Dojo_Form_Element_NumberTextBox('gm_phone');
		$gm_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$w_phone = new Zend_Dojo_Form_Element_NumberTextBox('w_phone');
		$w_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$office_tel = new Zend_Dojo_Form_Element_NumberTextBox('office_tel');
		$office_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$office_email = new Zend_Dojo_Form_Element_NumberTextBox('office_email');
		$office_email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		 
		$office_website = new Zend_Dojo_Form_Element_NumberTextBox('office_website');
		$office_website->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$office_address = new Zend_Dojo_Form_Element_NumberTextBox('office_address');
		$office_address->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height: 60px;font-size:12px;'
		));
		
		$_bank_info = new Zend_Dojo_Form_Element_TextBox('bank_info');
		$_bank_info->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Information")
		));
		
		
		$_bank_account1 = new Zend_Dojo_Form_Element_TextBox('bank_account1');
		$_bank_account1->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Name")." 1"
		));
		
		$_bank_account_name1 = new Zend_Dojo_Form_Element_TextBox('bank_account_name1');
		$_bank_account_name1->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Account Name")." 1"
		));
		
		$_bank_account1number = new Zend_Dojo_Form_Element_TextBox('bank_account1number');
		$_bank_account1number->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Acc Name")." 1"
		));
		$_bank_account2 = new Zend_Dojo_Form_Element_TextBox('bank_account2');
		$_bank_account2->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Name")." 2"
		));
		
		$_bank_account_name2 = new Zend_Dojo_Form_Element_TextBox('bank_account_name2');
		$_bank_account_name2->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Account Name")." 2"
		));
		
		$_bank_account2number = new Zend_Dojo_Form_Element_TextBox('bank_account2number');
		$_bank_account2number->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Acc Name")." 2"
		));
		
		$_bank_account3 = new Zend_Dojo_Form_Element_TextBox('bank_account3');
		$_bank_account3->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Name")." 3"
		));
		
		$_bank_account_name3 = new Zend_Dojo_Form_Element_TextBox('bank_account_name3');
		$_bank_account_name3->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Account Name")." 3"
		));
		
		$_bank_account3number = new Zend_Dojo_Form_Element_TextBox('bank_account3number');
		$_bank_account3number->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Acc Name")." 3"
		));
		
		
		$_cheque_receiver = new Zend_Dojo_Form_Element_TextBox('cheque_receiver');
		$_cheque_receiver->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Cheque Receiver")
		));
		
		
		if(!empty($data)){
			$br_id->setValue($data['br_id']);
			$_id->setValue($data['br_id']);
			$prefix_code->setValue($data['prefix']);
			$branch_namekh->setValue($data['project_name']);
			$branch_nameen->setValue($data['project_type']);
			
			$br_address->setValue($data['br_address']);
			$map->setValue($data['map_url']);
			$branch_tel->setValue($data['branch_tel']);
			if (empty($copy)){
				$branch_code->setValue($data['branch_code']);
			}
			$_fax->setValue($data['fax']);
			$branch_note->setValue($data['other']);
			$branch_status->setValue($data['status']);
			$branch_display->setValue($data['displayby']);
			$current_addres->setValue($data['p_current_address']);
			$project_manager_namekh->setValue($data['p_manager_namekh']);
			$project_manager_nation_id->setValue($data['p_manager_nation_id']);
			$project_manager_nationality->setValue($data['p_manager_nationality']);
			
			$sc_project_manager_nameen->setValue($data['w_manager_namekh']);
			$sc_project_manager_nationality->setValue($data['w_manager_nationality']);
			$sc_project_manager_nation_id->setValue($data['w_manager_nation_id']);
			
			
			$_dob_manager->setValue($data['p_dob']);
			$date_iss_doc->setValue(date("Y-m-d",strtotime($data['p_nationid_issue'])));
			$_p_manager_sex->setValue($data['p_sex']);
			
			$_csp_manager_sex->setValue($data['w_sex']);
			$_dob_cs_manager->setValue(date("Y-m-d",strtotime($data['w_dob'])));
			$date_iss_doc_cs_manager->setValue($data['w_nation_id_issue']);
			$cs_manager_current_address->setValue($data['w_current_address']);
			
			$budget_amount->setValue($data['budget_amount']);
			
			$position->setValue($data['position']);
			$w_position->setValue($data['w_position']);
			$contact_contruction->setValue($data['contact_contruction']);
			
			$gm_phone->setValue($data['w_manager_tel']);
			$w_phone->setValue($data['w_manager_tel1']);
			
			$office_tel->setValue($data['office_tel']);
			$office_email->setValue($data['office_email']);
			$office_website->setValue($data['office_website']);
			$office_address->setValue($data['office_address']);
			
			
			$_bank_account_name1->setValue($data['bank_account1']);
			$_bank_account1->setValue($data['bank_account_name1']);
			$_bank_account1number->setValue($data['bank_account1number']);
			
			$_bank_account2->setValue($data['bank_account2']);
			$_bank_account_name2->setValue($data['bank_account_name2']);
			$_bank_account2number->setValue($data['bank_account2number']);
			
			$_bank_account3->setValue($data['bank_account3']);
			$_bank_account_name3->setValue($data['bank_account_name3']);
			$_bank_account3number->setValue($data['bank_account3number']);
			$_cheque_receiver->setValue($data['cheque_receiver']);
		}
		
		
		$this->addElements(array(
				$_bank_info,$_bank_account_name1,$_bank_account1,$_bank_account1number,$_bank_account2,$_bank_account_name2,$_bank_account2number,
				$_bank_account3,$_bank_account_name3,$_bank_account3number,$_cheque_receiver,
				$contact_contruction,$map,$prefix_code,$_btn_search,$_title,$_status,$br_id,$branch_namekh,
		$branch_nameen,$br_address,$branch_code,$branch_tel,$_fax ,$branch_note,
				$current_addres,$project_manager_nameen,$project_manager_namekh,$project_manager_nation_id,$project_manager_nationality,
				$sc_project_manager_nameen,$sc_project_manager_namekh,$sc_project_manager_nation_id,$sc_project_manager_nationality,
				$branch_status,$branch_display,
				
				$_dob_manager,
				$date_iss_doc,
				$_p_manager_sex,
				$_csp_manager_sex,
				$_dob_cs_manager,
				$date_iss_doc_cs_manager,
				$cs_manager_current_address,
				$budget_amount,
				$position,
				$w_position,
				
				$gm_phone,
				$w_phone,
				
				$_id,
				$office_tel,
				$office_email,
				$office_website,
				$office_address
				));
		
		return $this;
		
	}
	
}