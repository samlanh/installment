<?php 
Class Invest_Form_FrmInvestor extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddInvestor($data=null){
		
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		
		$_name = new Zend_Dojo_Form_Element_TextBox('name');
		$_name->setAttribs(array(
						'dojoType'=>'dijit.form.ValidationTextBox',
						'class'=>'fullside',
						'required' =>'true'
		));
		
		$_sex = new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_sex->setMultiOptions($opt_status);
		
		$_document_type = new Zend_Dojo_Form_Element_FilteringSelect('document_type');
		$_document_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(-1=>$this->tr->translate("CHOOSE_DOCUMENT_TYPE"));
		$rows = $db->getclientdtype();
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_document_type->setMultiOptions($opt_doc);
		
		$_document_no= new Zend_Dojo_Form_Element_TextBox('document_no');
		$_document_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_date = date("Y-m-d",strtotime("+1 day"));
		$_doc_issue_date= new Zend_Dojo_Form_Element_DateTextBox('doc_issue_date');
		$_doc_issue_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{max:'$_date',datePattern:'dd/MM/yyyy'}",
				)
			);
		
		$_nation = new Zend_Dojo_Form_Element_TextBox('nation');
		$_nation->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_nation->setValue("ខ្មែរ");
		
		$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_email = new Zend_Dojo_Form_Element_TextBox('email');
		$_email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_current_address=  new Zend_Form_Element_Textarea('current_address');
		$_current_address->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; font-family: inherit;  min-height:100px !important;'));
		
		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; font-family: inherit;  min-height:100px !important;'));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		
		$_id = new Zend_Form_Element_Hidden("id");
		
		if($data!=null){
			$_name->setValue($data['name']);
			$_sex->setValue($data['sex']);
			$_document_type->setValue($data['document_type']);
			$_document_no->setValue($data['document_no']);
			$_doc_issue_date->setValue($data['doc_issue_date']);
			$_nation->setValue($data['nation']);
			$_phone->setValue($data['phone']);
			$_email->setValue($data['email']);
			$_current_address->setValue($data['current_address']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			$_id->setValue($data['id']);
		}
		$this->addElements(array(
				$_name,
				$_sex,
				$_document_type,
				$_document_no,
				$_doc_issue_date,
				$_nation,
				$_phone,
				$_email,
				$_current_address,
				$note,
				$_status,
				$_id
				
				));
		return $this;
		
	}	
	
	
	public function FrmAddBroker($data=null){
	
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
	
	
		$_name = new Zend_Dojo_Form_Element_TextBox('name');
		$_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
	
		$_sex = new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_sex->setMultiOptions($opt_status);
	
		$_date = date("Y-m-d",strtotime("+1 day"));
		$_dob= new Zend_Dojo_Form_Element_DateTextBox('dob');
		$_dob->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{max:'$_date',datePattern:'dd/MM/yyyy'}",
		)
		);
		
		$_document_type = new Zend_Dojo_Form_Element_FilteringSelect('document_type');
		$_document_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(-1=>$this->tr->translate("CHOOSE_DOCUMENT_TYPE"));
		$rows = $db->getclientdtype();
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_document_type->setMultiOptions($opt_doc);
	
		$_document_no= new Zend_Dojo_Form_Element_TextBox('document_no');
		$_document_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		$_date = date("Y-m-d",strtotime("+1 day"));
		$_doc_issue_date= new Zend_Dojo_Form_Element_DateTextBox('doc_issue_date');
		$_doc_issue_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{max:'$_date',datePattern:'dd/MM/yyyy'}",
		)
		);
	
		$_nation = new Zend_Dojo_Form_Element_TextBox('nation');
		$_nation->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_nation->setValue("ខ្មែរ");
	
		$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_email = new Zend_Dojo_Form_Element_TextBox('email');
		$_email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		$_current_address=  new Zend_Form_Element_Textarea('current_address');
		$_current_address->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; font-family: inherit;  min-height:100px !important;'));
	
		$_percent_income = new Zend_Dojo_Form_Element_NumberTextBox('percent_income');
		$_percent_income->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		
		$note=  new Zend_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; font-family: inherit;  min-height:100px !important;'));
	
	
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
	
	
		$_id = new Zend_Form_Element_Hidden("id");
	
		if($data!=null){
			$_name->setValue($data['name']);
			$_sex->setValue($data['sex']);
			$_dob->setValue($data['dob']);
			$_document_type->setValue($data['document_type']);
			$_document_no->setValue($data['document_no']);
			$_doc_issue_date->setValue($data['doc_issue_date']);
			$_nation->setValue($data['nation']);
			$_phone->setValue($data['phone']);
			$_email->setValue($data['email']);
			$_current_address->setValue($data['current_address']);
			$note->setValue($data['note']);
			$_percent_income->setValue($data['percent_income']);
			$_status->setValue($data['status']);
			$_id->setValue($data['id']);
		}
		$this->addElements(array(
				$_name,
				$_sex,
				$_dob,
				$_document_type,
				$_document_no,
				$_doc_issue_date,
				$_nation,
				$_phone,
				$_email,
				$_current_address,
				$note,
				$_percent_income,
				$_status,
				$_id
	
		));
		return $this;
	
	}
}