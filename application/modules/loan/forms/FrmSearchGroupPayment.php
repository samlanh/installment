<?php 
Class Loan_Form_FrmSearchGroupPayment extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function AdvanceSearch ($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
// 		$db = new Loan_Model_DbTable_DbGroupPayment();
		
		$dbs = new Application_Model_DbTable_DbGlobal();
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$rows = $dbs ->getAllBranchByUser();
		$options=array('');		
		if(!empty($rows)){
			foreach($rows AS $row) $options[$row['id']]=$row['name'];
		}
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$branch_id->setMultiOptions($options);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$payment_type = new Zend_Dojo_Form_Element_FilteringSelect("paymnet_type");
		$payment_type->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.FilteringSelect'));
		$options= array(''=>$tr->translate("REPAYMENT_TYPE"),1=>$tr->translate("NORMAL_PAID"),3=>$tr->translate("PRINCIPAL_PAID"),4=>$tr->translate("PAYOFF_PAID"));
		$payment_type->setMultiOptions($options);
		$payment_type->setValue($request->getParam("paymnet_type"));
		
		$advnceSearch = new Zend_Dojo_Form_Element_TextBox("advance_search");
		$advnceSearch->setAttribs(array('class'=>'fullside'
				,'dojoType'=>'dijit.form.TextBox'
				,'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")));
		
		$client_name = new Zend_Dojo_Form_Element_FilteringSelect("client_name");
		$opt_client = array(''=>$this->tr->translate("CHOOSE_CUSTOEMR"));
		$rows = $dbs->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$client_name->setMultiOptions($opt_client);
		$client_name->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.FilteringSelect'));
		
// 		$g_client_name = new Zend_Dojo_Form_Element_FilteringSelect("g_client_name");
// 		$opt_client = array(''=>'ជ្រើសរើស ឈ្មោះអតិថិជន');
// 		$rows = $db->getAllClient();
// 		if(!empty($rows))foreach($rows AS $row){
// 			$opt_client[$row['id']]=$row['name'];
// 		}
// 		$g_client_name->setMultiOptions($opt_client);
// 		$g_client_name->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.FilteringSelect'));
		
		$_coid = new Zend_Dojo_Form_Element_FilteringSelect('co_id');
		$_coid->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'onchange'=>'popupCheckCO();',
				'class'=>'fullside'
		));
		$options = $dbs->getAllCOName(1);
		$_coid->setMultiOptions($options);
		$_coid->setValue($request->getParam("co_id"));
		
		
		$start_date = new Zend_Dojo_Form_Element_DateTextBox("start_date");
		$start_date->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.DateTextBox','placeholder'=>$this->tr->translate("ចាប់ពីថ្ងៃ")));
		$_date = $request->getParam("start_date");
		if(empty($_date)){
// 			$_date = date('Y-m-d');
		}
		$start_date->setValue($_date);
		
		$date = date("y-m-d");
		$end_date = new Zend_Dojo_Form_Element_DateTextBox("end_date");
		$end_date->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.DateTextBox','placeholder'=>$this->tr->translate("រហូតដល់ថ្ងៃ")));
		
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$end_date->setValue($_date);
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$status->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.FilteringSelect','placeholder'=>$this->tr->translate("ស្ថានការ")));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$status->setMultiOptions($_status_opt);
		$status->setValue($request->getParam("status"));
		
		$submit = new Zend_Dojo_Form_Element_SubmitButton("btn_submit");
		$submit->setAttribs(array('dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'label'=>'Search'));
		$advnceSearch->setValue($request->getParam("advance_search"));
		$client_name->setValue($request->getParam("client_name"));
		$status->setValue($request->getParam("status"));
		
		$land_id = new Zend_Dojo_Form_Element_FilteringSelect('land_id');
		$land_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$options = $db ->getAllLandInfo(null,null,1);//show name,show group,show option
		$land_id->setMultiOptions($options);
		$land_id->setValue($request->getParam("land_id"));
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3);
		unset($opt[-1]);
		$payment_method->setMultiOptions($opt);
		$payment_method->setValue($request->getParam("payment_method"));
		
		if($data!=null){
			$advnceSearch->setValue($request->getParam("advance_search"));
			$client_name->setValue($request->getParam("client_name"));
			$start_date->setValue($request->getParam("start_date"));
			$end_date->setValue($request->getParam("end_date"));
			$status->setValue($request->getParam("status"));
			
		}
		$this->addElements(array($payment_method,$land_id,$branch_id,$payment_type,$_coid,$submit,$advnceSearch,$client_name,$start_date,$end_date,$status));
		return $this;
		
	}	
}