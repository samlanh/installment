<?php 
Class Invest_Form_FrmInvestment extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddInvestment($data=null){
		
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
		
		$dbinvest = new Invest_Model_DbTable_DbInvestment();
		$investNo = $dbinvest->getInvestmentNO();
		$_invest_no= new Zend_Dojo_Form_Element_TextBox('invest_no');
		$_invest_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red;',
				'readOnly'=>'true'
		)
		);
		$_invest_no->setValue($investNo);
		
		$_investor_id = new Zend_Dojo_Form_Element_FilteringSelect('investor_id');
		$_investor_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(0=>$this->tr->translate("CHOOSE_INVESTOR"));
		$rows = $db->getAllInvestor();
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_investor_id->setMultiOptions($opt_doc);
		$_investor_id->setValue($request->getParam("investor_id"));
		
		$_broker_id = new Zend_Dojo_Form_Element_FilteringSelect('broker_id');
		$_broker_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'getBrokerInfo();',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_doc=array(0=>$this->tr->translate("CHOOSE_BROKER"));
		$rows = $db->getAllBroker();
		if(!empty($rows))foreach($rows AS $row){
			$opt_doc[$row['id']]=$row['name'];
		}
		$_broker_id->setMultiOptions($opt_doc);
		$_broker_id->setValue($request->getParam("broker_id"));
		
		$_datenext = date("Y-m-d",strtotime("+1 day"));
		$_date= new Zend_Dojo_Form_Element_DateTextBox('date');
		$_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{max:'$_datenext',datePattern:'dd/MM/yyyy'}",
				)
			);
		$_date->setValue(date("Y-m-d"));
		
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('amount');
		$_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onKeyup'=>'calculateBrokerAmount(1);'
		));
		$_amount->setValue(0);
		
		$_duration = new Zend_Dojo_Form_Element_NumberTextBox('duration');
		$_duration->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$_duration->setValue(0);
		
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
		$_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		)
		);
		
		$_broker_percent = new Zend_Dojo_Form_Element_NumberTextBox('broker_percent');
		$_broker_percent->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onKeyup'=>'calculateBrokerAmount(1);'
		));
		$_broker_percent->setValue(0);
		
		$_broker_amount = new Zend_Dojo_Form_Element_NumberTextBox('broker_amount');
		$_broker_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onKeyup'=>'calculateBrokerAmount(2);'
		));
		$_broker_amount->setValue(0);
		
		
		$_broker_duration = new Zend_Dojo_Form_Element_NumberTextBox('broker_duration');
		$_broker_duration->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
		));
		$_broker_duration->setValue(0);
		
		if($data!=null){
			$_invest_no->setValue($data['invest_no']);
			$_investor_id->setValue($data['investor_id']);
			$_broker_id->setValue($data['broker_id']);
			$_date->setValue($data['date']);
			$_amount->setValue($data['amount']);
			$_duration->setValue($data['duration']);
			$note->setValue($data['note']);
			$_status->setValue($data['status']);
			$_id->setValue($data['id']);
			
			$_broker_percent->setValue($data['broker_percent']);
			$_broker_amount->setValue($data['broker_amount']);
			$_broker_duration->setValue($data['broker_duration']);
		}
		$this->addElements(array(
				$_invest_no,
				$_investor_id,
				$_broker_id,
				$_date,
				$_amount,
				$_duration,
				$note,
				$_status,
				$_id,
				$_broker_percent,
				$_broker_amount,
				$_broker_duration
				));
		return $this;
		
	}	
	
}