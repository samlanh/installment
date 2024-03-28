<?php 
Class Group_Form_FrmTransferCRM extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmTransferCRM($data=null){		
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$dbTr = new Group_Model_DbTable_DbTranferCRM();
		
		
		$_crmId = new Zend_Dojo_Form_Element_FilteringSelect('crmId');
		$rows = $dbTr->getAllCrmOfUser();
		$options = array('' => $this->tr->translate("SELECT_CUSTOMER"));
		if (!empty($rows))
			foreach ($rows as $row)
				$options[$row['id']] = $row['name'];
		$_crmId->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'class' => 'fullside',
				'autoComplete' => 'false',
			)
		);
		$_crmId->setMultiOptions($options);
		
		$_note = new Zend_Dojo_Form_Element_Textarea('note');
		$_note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:98%;min-height:60px; font-size:inherit; font-family:Kh Battambang'
		));
		
		$_toUserId = new Zend_Dojo_Form_Element_FilteringSelect('toUserId');
		$rows = $dbTr->getUserListByCurrentUserTypeAndParent();
		$options = array('' => $this->tr->translate("SELECT_USER"));
		if (!empty($rows))
			foreach ($rows as $row)
				$options[$row['id']] = $row['name'];
		$_toUserId->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'class' => 'fullside',
				'autoComplete' => 'false',
			)
		);
		$_toUserId->setMultiOptions($options);
		
		$id = new Zend_Form_Element_Hidden("id");
		$id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
				));
		
		if($data!=null){
			
			$id->setValue($data['id']);
			$_toUserId->setValue($data['toUserId']);
		}
		$this->addElements(
			array(
				$id
				,$_toUserId
				,$_crmId
				,$_note
				
			)
		);
		return $this;
		
	}	
}