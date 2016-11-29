<?php 
Class Loan_Form_FrmSearchGroupPayment extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function AdvanceSearch ($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Loan_Model_DbTable_DbGroupPayment();
		
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
		$options= array(''=>'ប្រភេទបង់ប្រាក់',1=>'បង់ធម្មតា',4=>'បង់ផ្តាច់');
		$payment_type->setMultiOptions($options);
		$payment_type->setValue($request->getParam("paymnet_type"));
		
		
		
		
		$advnceSearch = new Zend_Dojo_Form_Element_TextBox("advance_search");
		$advnceSearch->setAttribs(array('class'=>'fullside'
				,'dojoType'=>'dijit.form.TextBox'
				,'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")));
		
		$client_name = new Zend_Dojo_Form_Element_FilteringSelect("client_name");
		$opt_client = array(''=>'ជ្រើសរើស ឈ្មោះអតិថិជន');
		$rows = $db->getIndividuleClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$client_name->setMultiOptions($opt_client);
		$client_name->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.FilteringSelect'));
		
		$g_client_name = new Zend_Dojo_Form_Element_FilteringSelect("g_client_name");
		$opt_client = array(''=>'ជ្រើសរើស ឈ្មោះអតិថិជន');
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$g_client_name->setMultiOptions($opt_client);
		$g_client_name->setAttribs(array('class'=>'fullside','dojoType'=>'dijit.form.FilteringSelect'));
		
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
			$_date = date('Y-m-d');
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
		$opt_status = array(''=>'ជ្រើសរើស ស្ថានការ','1'=>'ដំណើការ','2'=>'មិនដំណើការ');
		$status->setMultiOptions($opt_status);
		
		$submit = new Zend_Dojo_Form_Element_SubmitButton("btn_submit");
		$submit->setAttribs(array('dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'label'=>'Search'));
		$advnceSearch->setValue($request->getParam("advance_search"));
		$client_name->setValue($request->getParam("client_name"));
		$status->setValue($request->getParam("status"));
		
		
		if($data!=null){
			$advnceSearch->setValue($request->getParam("advance_search"));
			$client_name->setValue($request->getParam("client_name"));
			$start_date->setValue($request->getParam("start_date"));
			$end_date->setValue($request->getParam("end_date"));
			$status->setValue($request->getParam("status"));
			
		}
		$this->addElements(array($branch_id,$g_client_name,$payment_type,$_coid,$submit,$advnceSearch,$client_name,$start_date,$end_date,$status));
		return $this;
		
	}	
}