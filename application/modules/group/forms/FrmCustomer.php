<?php 
Class Group_Form_FrmCustomer extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddCustomer($data=null){		
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_branchId = new Zend_Dojo_Form_Element_FilteringSelect('branchId');
		$_branchId->setAttribs(array(
			'dojoType'=>'dijit.form.FilteringSelect',
			'required' =>'true',
			'class'=>'fullside',
			'autoComplete'=>'false',
			'queryExpr'=>'*${0}*',
			'onchange'=>'filterByBranch();'
		));
		$options = $db->getAllBranchName(null,1);
		$_branchId->setMultiOptions($options);
		
		$_name = new Zend_Dojo_Form_Element_TextBox('name');
		$_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
			    'required'=>true,
		));
		
		$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$know_by = new Zend_Dojo_Form_Element_FilteringSelect('know_by');
		$know_by->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onChange' => 'popUpKnowBy()'
		
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$opt_know = $db->getAllKnowBy(1,1);
		$know_by->setMultiOptions($opt_know);

		$_date = new Zend_Dojo_Form_Element_DateTextBox('date');
		$_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_date->setValue(date("Y-m-d"));

		$_from_price = new Zend_Dojo_Form_Element_NumberTextBox('from_price');
		$_from_price->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));


		$_to_price = new Zend_Dojo_Form_Element_NumberTextBox('to_price');
		$_to_price->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_requirement = new Zend_Dojo_Form_Element_TextBox('requirement');
		$_requirement->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_type = new Zend_Dojo_Form_Element_TextBox('type');
		$_type->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_description = new Zend_Dojo_Form_Element_Textarea('description');
		$_description->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:98%;min-height:60px; font-size:inherit; font-family:Kh Battambang'
		));
		
		$dbcusre = new Group_Model_DbTable_DbCustomer();
		$statusreq = new Zend_Dojo_Form_Element_FilteringSelect('statusreq');
		$statusreq->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'showstatusreq();'
		
		));
		$rows = $dbcusre->getAllstatusreqForOpt();
		$options = array(''=>$this->tr->translate("CHOOSE_STATUS_REQ"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['name']]=$row['name'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
		}
		$statusreq->setMultiOptions($options);
		
		if($data!=null){
			
			$_name->setValue($data['name']);
			$_phone->setValue($data['phone']);
			$_date->setValue($data['date']);
			$_from_price->setValue($data['from_price']);
			$_to_price->setValue($data['to_price']);
			$_requirement->setValue($data['requirement']);
			$_type->setValue($data['type']);
			$_description->setValue($data['description']);
			$statusreq->setValue($data['statusreq']);
			$know_by->setValue($data['know_by']);
			$_branchId->setValue($data['branchId']);
		}
		$this->addElements(
			array(
				$know_by
				,$_name
				,$_phone
				,$_date
				,$_from_price
				,$_to_price
				,$_requirement
				,$_type
				,$_description
				,$statusreq
				,$_branchId
			)
		);
		return $this;
		
	}	
}