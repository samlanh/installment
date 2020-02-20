<?php 
Class Rent_Form_FrmCancel extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddCancel($data=null){
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
		
		$customer_id = new Zend_Form_Element_Hidden("customer_id");
		$customer_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_property_id = new Zend_Form_Element_Hidden("property_id");
		$_property_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$c_date = date('Y-m-d');
		$next_payment = $c_date;
		if(date('H')>=16){
			$next_payment = date("Y-m-d", strtotime("$c_date +1 day"));
		}
		$cancel_date = new Zend_Dojo_Form_Element_DateTextBox('cancel_date');
		$cancel_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>true,
				'constraints'=>'{datePattern:"dd-MM-yyyy"}'
		));
		$cancel_date->setValue($c_date);
		
		
		
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
		
		$cancel_no = new Zend_Form_Element_Hidden("cancel_no");
		$cancel_no->setAttribs(array(
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
			$_property_id->setValue($data["property_id"]);
			$cancel_date->setValue($data["cancel_date"]);
			
			$_reason->setValue($data["reason"]);
			$_note->setValue($data["note"]);
			$cancel_no->setValue($data["cancel_no"]);
			$_status->setValue($data["status"]);
		}
		$this->addElements(array(
				$branch_id,
				$customer_id,
				$_property_id,
				$cancel_date,
				
				$_reason,
				$_note,
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
		
		$customer_id = new Zend_Dojo_Form_Element_FilteringSelect("customer_id");
		$opt_client = array(''=>$this->tr->translate("CHOOSE_CUSTOEMR"));
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$customer_id->setMultiOptions($opt_client);
		$customer_id->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',
				'required'=>'false'));
		$customer_id->setValue($request->getParam("customer_id"));
	
	
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
				$customer_id,
// 				$cheque_issuer,
				$_start_date,
				$_end_date,
				$_status
		));
		return $this;
	
	}

}