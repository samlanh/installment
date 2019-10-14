<?php 
Class Issue_Form_FrmPlongStep extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmPlongStep($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$_dbStepOpt = new Loan_Model_DbTable_DbStepOption();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'filterClient();setBranchID();'
		));
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);	

		$now=date("Y-m-d");
		$_date = new Zend_Dojo_Form_Element_DateTextBox('date');
		$_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				//'constraints'=>"{min:'$now',datePattern:'dd/MM/yyyy'}",
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",//min:'$now',
		));
		$_date->setValue($now);

		$_process_status = new Zend_Dojo_Form_Element_FilteringSelect('process_status');
		$_process_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$allStep = $_dbStepOpt->getAllStepOptions();
		$options_pro= array(0=>$this->tr->translate("PLEASE_SELECT"));
		if (!empty($allStep)) foreach ($allStep as $ss){
			$options_pro[$ss['id']]=$ss['name'];
		}
// 		$options_pro= array(
// 				0=>$this->tr->translate("PLEASE_SELECT"),
// 				1=>$this->tr->translate("1.ការិយាល័យ - សាលាស្រុក"),
// 				2=>$this->tr->translate("2.ការិយាល័យ - ពន្ធដា"),
// 				3=>$this->tr->translate("3.ការិយាល័យ - សាលាស្រុក"),
// 				4=>$this->tr->translate("4.ការិយាល័យ - អតិថិជន"),
// 				1=>$this->tr->translate("1.HQ-P"),
// 				2=>$this->tr->translate("2.P-HQ"),
// 				3=>$this->tr->translate("3.HQ-T"),
// 				4=>$this->tr->translate("4.T-HQ"),
// 				5=>$this->tr->translate("5.HQ-P"),
// 				6=>$this->tr->translate("6.P-HQ"),
// 				7=>$this->tr->translate("7.HQ-C"),
// 				);
		$_process_status->setMultiOptions($options_pro);
		$_process_status->setValue($request->getParam("process_status"));
		
		$give_by = new Zend_Dojo_Form_Element_TextBox("give_by");
		$give_by->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$receive_by = new Zend_Dojo_Form_Element_TextBox("receive_by");
		$receive_by->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
		));
		
		$note = new Zend_Dojo_Form_Element_TextBox("note");
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%;min-height:103px; font-size:12px; font-family:Kh Battambang'
		));
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($options);
		
		$_customer_id = new Zend_Form_Element_Hidden("customer_id");
		$_customer_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_property_id = new Zend_Form_Element_Hidden("property_id");
		$_property_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_detail_id = new Zend_Form_Element_Hidden('detail_id');
		$_detail_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttribs(array(
					'readOnly'=>'true',
			));
			$_date->setValue($data['date']);
			$_process_status->setValue($data['process_status']);
			$give_by->setValue($data['give_by']);
			$receive_by->setValue($data['receive_by']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			$_customer_id->setValue($data['customer_id']);
			$_property_id->setValue($data['property_id']);
			$_id->setValue($data['id']);
		}
		$this->addElements(
				array($_branch_id,$_date,$_process_status,$give_by,$receive_by,$note,$_status,$_customer_id,$_property_id,$_id,$_detail_id)
				);
		return $this;
		
	}	
	
}