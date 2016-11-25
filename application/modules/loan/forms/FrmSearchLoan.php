<?php 
Class Loan_Form_FrmSearchLoan extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function AdvanceSearch($data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'class'=>'fullside',
		
		));
		
// 		$_group_code = new Zend_Dojo_Form_Element_FilteringSelect('group_code');
// 		$_group_code->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// // 				'class'=>'fullside',
// 				'onchange'=>'getmemberIdGroup();'
// 		));
// 		$group_opt = $db ->getGroupCodeById(1,1,1);
// 		$_group_code->setMultiOptions($group_opt);
// 		$_group_code->setValue($request->getParam("group_code"));
		
// 		$_customer_code = new Zend_Dojo_Form_Element_FilteringSelect('customer_code');
// 		$_customer_code->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'onchange'=>'getmemberIdGroup();'
// 		));
// 		$group_opt = $db ->getGroupCodeById(1,0,1);//code,individual,option
// 		$_customer_code->setMultiOptions($group_opt);
// 		$_customer_code->setValue($request->getParam("customer_code"));
		
// 		$_member = new Zend_Dojo_Form_Element_FilteringSelect('member');
// 		$_member->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'onchange'=>'checkMember()'
// 		));
// 		$options = $db->getGroupCodeById(2,0,1);
// 		$_member->setMultiOptions($options);
// 		$_member->setValue($request->getParam("member"));
		
// 		$_groupid = new Zend_Dojo_Form_Element_FilteringSelect('group_id');
// 		$_groupid->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
//  				'onchange'=>'popupCheckClient();'
// 				));
// 		$options = $db ->getGroupCodeById(2,1,1);//show name,show group,show option
// 		$_groupid->setMultiOptions($options);
// 		$_groupid->setValue($request->getParam("group_id"));

		$schedule_opt = new Zend_Dojo_Form_Element_FilteringSelect('schedule_opt');
		$schedule_opt->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'checkScheduleOption();'
		));
		$rows = $db->getVewOptoinTypeByType(25,null,null,1);
		$options = array(-1=>"ជ្រើសរើសប្រភេទបង់ប្រាក់");
	    if(!empty($rows))foreach($rows AS $row){
  			$options[$row['key_code']]=$row['name_en'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
  		}
// 		print_r($opt);exit();
// 		៊៊$opt[-1]="ជ្រើសរើសប្រភេទបង់ប្រាក់";
// 		array_unshift($opt, array(1=>'ជ្រើសរើសប្រភេទបង់ប្រាក់') );
// 		print_r($opt);exit();
		$schedule_opt->setMultiOptions($options);
		$schedule_opt->setValue($request->getParam("schedule_opt"));
		
		
		$_coid = new Zend_Dojo_Form_Element_FilteringSelect('co_id');
		$_coid->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();'
		));
		$options = $db ->getAllCOName(1);
		$_coid->setMultiOptions($options);
		$_coid->setValue($request->getParam("co_id"));
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_releasedate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$_releasedate->setValue($_date);
		
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_dateline->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_dateline->setValue($_date);
		
		$client_name = new Zend_Dojo_Form_Element_FilteringSelect("client_name");
		$opt_client = array(''=>'ជ្រើសរើស ឈ្មោះអតិថិជន');
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$client_name->setMultiOptions($opt_client);
		$client_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$client_name->setValue($request->getParam("client_name"));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
		$_branch_id->setValue($request->getParam("branch_id"));
		
		$propertiestype = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$propertiestype->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside','onChange'=>'showPopupForm();'));
		$propertiestype_opt = $db->getPropertyTypeForsearch();
		$propertiestype->setMultiOptions($propertiestype_opt);
		$propertiestype->setValue($request->getParam("property_type"));
		
		$_category = new Zend_Dojo_Form_Element_FilteringSelect('category_id');
		$_category->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt= $db->getVewOptoinTypeByType(12,1,null,null);
		if($request->getActionName()!='add' OR $request->getActionName()!='edit'){
			unset($opt[-1]);
		}
		$_category->setMultiOptions($opt);
		
		$category_id_expense = new Zend_Dojo_Form_Element_FilteringSelect('category_id_expense');
		$category_id_expense->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt1= $db->getVewOptoinTypeByType(13,1,null,null);
		if($request->getActionName()!='add' OR $request->getActionName()!='edit'){
			unset($opt1[-1]);
		}
		$category_id_expense->setMultiOptions($opt1);
		
		if($data!=null){
// 			$_member->setValue($data['client_id']);
			$_coid->setValue($data['co_id']);
			$_releasedate->setValue($data['date_release']);
			$client_name->setValue($data['client_name']);
		}
		$this->addElements(array($propertiestype,$schedule_opt,$_branch_id,$client_name,$_title,$_coid,$_releasedate,
				$_category,$category_id_expense,
		// 				$_groupid,$_member,$_group_code,$_customer_code,
				$_dateline,$_status,$_btn_search));
		return $this;
		
	}	
	function JurnalSearch($data=null){
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('currency_type');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = $db->getVewOptoinTypeByType(15,1,3,1);
		$opt['-1']=("--Select Currency Type--");
		$_currency_type->setMultiOptions($opt);
		
		$_valuecurr=$request->getParam("currency_type");
		if(empty($_valuecurr) AND $_valuecurr!=-1){
			$_currency_type->setValue(-1);
		}else{
			$_currency_type->setValue($_valuecurr);
		}
		
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_releasedate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
		// 				'class'=>'fullside',
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$_releasedate->setValue($_date);
		
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_dateline->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true',
		// 				'class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_dateline->setValue($_date);
		
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
		));
		
		$rows = $db->getAllBranchName();
		$options=array(-1=>'---Select Branch---');
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$_branch_id->setMultiOptions($options);
		$_branch_id->setValue($request->getParam("branch_id"));
		
		
		if($data!=null){
			//print_r($data);
			$_branch_id->setValue($data['member_id']);
			$_releasedate->setValue($data['date_release']);
			$_currency_type->setValue($data['payment_method']);
		}
		$this->addElements(array($_title,$_branch_id,$_currency_type,$_releasedate,$_dateline));
		return $this;
		
	}
}