<?php 
Class Rent_Form_FrmChangeOwner extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddChangeOwner($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$_dbRent = new Rent_Model_DbTable_DbLanddeposit();
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$rows = $db ->getAllBranchByUser();
		$options=array(''=>$this->tr->translate("SELECT_PROJECT"));;
		if(!empty($rows)){foreach($rows AS $row) $options[$row['id']]=$row['name'];}
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getAllRentNumber();'
		));
		$branch_id->setMultiOptions($options);
		
		$_from_customer = new Zend_Dojo_Form_Element_FilteringSelect("from_customer");
		$opt_client = array(''=>$this->tr->translate("CHOOSE_CUSTOEMR"));
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$_from_customer->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',
				'required'=>'false'));
		$_from_customer->setMultiOptions($opt_client);
		
		$_property_id = new Zend_Form_Element_Hidden("property_id");
		$_property_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_to_customer = new Zend_Dojo_Form_Element_FilteringSelect("to_customer");
		$opt_client = array(''=>$this->tr->translate("CHOOSE_CUSTOEMR"));
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$_to_customer->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',
				'required'=>'false'));
		$_to_customer->setMultiOptions($opt_client);
		
		$c_date = date('Y-m-d');
		$next_payment = $c_date;
		if(date('H')>=16){
			$next_payment = date("Y-m-d", strtotime("$c_date +1 day"));
		}
		$change_date = new Zend_Dojo_Form_Element_DateTextBox('change_date');
		$change_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>true,
				'constraints'=>'{datePattern:"dd-MM-yyyy"}'
		));
		$change_date->setValue($c_date);
		
		$_agreement_date = new Zend_Dojo_Form_Element_DateTextBox('agreement_date');
		$_agreement_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>true,
				'constraints'=>'{datePattern:"dd-MM-yyyy"}'
		));
		$_agreement_date->setValue($c_date);
		
		
		$_reason = new Zend_Dojo_Form_Element_TextBox('reason');
		$_reason->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height:50px;'
				//'required' =>'true'
		));
		
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height:50px;'
				//'required' =>'true'
		));
		
		$change_no = new Zend_Form_Element_Hidden("change_no");
		$change_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		
		$id = new Zend_Form_Element_Hidden("id");
		$id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect'
				,'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		if($data!=""){
			$id->setValue($data["id"]);
			$branch_id->setValue($data["branch_id"]);
			$_from_customer->setValue($data["from_customer"]);
			$_property_id->setValue($data["property_id"]);
			$_to_customer->setValue($data["to_customer"]);
			$change_date->setValue($data["change_date"]);
			$_agreement_date->setValue($data["agreement_date"]);
			$_reason->setValue($data["reason"]);
			$_note->setValue($data["note"]);
			$change_no->setValue($data["change_no"]);
			$_status->setValue($data["status"]);
		}
		$this->addElements(array(
				$branch_id,
				$_from_customer,
				$_property_id,
				$_to_customer,
				$change_date,
				$_agreement_date,
				$_reason,
				$_note,
				$change_no,
				$id,
				$_status,
				));
		return $this;
		
	}
	
	public function FrmSearchChangeOwner($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$_dbRent = new Rent_Model_DbTable_DbLanddeposit();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$rows = $db ->getAllBranchByUser();
		$options=array(''=>$this->tr->translate("SELECT_PROJECT"));
		if(!empty($rows)){
			foreach($rows AS $row) $options[$row['id']]=$row['name'];
		}
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$branch_id->setMultiOptions($options);
		$branch_id->setValue($request->getParam("branch_id"));
		
		$from_customer = new Zend_Dojo_Form_Element_FilteringSelect("from_customer");
		$opt_client = array(''=>$this->tr->translate("SELECT_FROM_CUSTOMER"));
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$from_customer->setMultiOptions($opt_client);
		$from_customer->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',
				'required'=>'false'));
		$from_customer->setValue($request->getParam("from_customer"));
	
		$to_customer = new Zend_Dojo_Form_Element_FilteringSelect("to_customer");
		$opt_client = array(''=>$this->tr->translate("SELECT_TO_CUSTOMER"));
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$to_customer->setMultiOptions($opt_client);
		$to_customer->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',
				'required'=>'false'));
		$to_customer->setValue($request->getParam("to_customer"));
	
		$_start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_start_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'placeHolder'=>'Start Date',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
		}
		$_start_date->setValue($_date);
		
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		
		
		$_end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_end_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		
		$_end_date->setValue($_date);
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect'
				,'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		if($data!=""){
				
		}
		$this->addElements(array(
				$_adv_search,
				$branch_id,
				$from_customer,
				$to_customer,
// 				$cheque_issuer,
				$_start_date,
				$_end_date,
				$_status
		));
		return $this;
	
	}

}