<?php 
Class Loan_Form_FrmMaterialInclude extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	
	public function FrmAddMaterialInclude($data=null){
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		
		$for_date = new Zend_Dojo_Form_Element_FilteringSelect('for_date');
		$for_date->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		$options= array(1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"10",11=>"11",12=>"12");
		$for_date->setMultiOptions($options);
	
	
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_Date->setValue(date('Y-m-d'));
	
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'getSaleClie();getallCustomer();'
		));
	
		$db = new Application_Model_DbTable_DbGlobal();
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
	
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
					
		));
		$options= array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($options);
	
		$_Description = new Zend_Dojo_Form_Element_Textarea('Description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"height:70px !important",
	
		));
		
		
		$invoice=new Zend_Dojo_Form_Element_TextBox('invoice');
		$invoice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'true',
				'style'=>'color:red',
		));
	
		$id = new Zend_Form_Element_Hidden("id");
		
	
	
		$_items_id = new Zend_Dojo_Form_Element_FilteringSelect('items_id');
		$_items_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onChange'=>'setValueToRow();',
		));
		
	
		$db = new Loan_Model_DbTable_DbItems();
		$rows = $db->getAllItemsMaterial();
		$options=array(''=>$this->tr->translate("SELECT_ITEMS"),-1=>$this->tr->translate("ADD_NEW"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_items_id->setMultiOptions($options);
	
		if($data!=null){
			
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttribs(array(
				'readOnly' =>'true',
			));
			$_Description->setValue($data['description']);
			$_Date->setValue($data['for_date']);
			$_status->setValue($data['status']);
			$invoice->setValue($data['invoice']);
			$id->setValue($data['id']);
			
			
		}
		$this->addElements(array($_status,$invoice,$_Date,$_Description,
				$_branch_id,$for_date,$id,$_items_id));
		return $this;
	
	}
	public function FrmAddMaterialIncludeSearch($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Loan_Model_DbTable_DbItems();
		
		$_items_id = new Zend_Dojo_Form_Element_FilteringSelect('items_id');
		$_items_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onChange'=>'setValueToRow();',
		));
		$rows = $db->getAllItemsMaterial();
		$options=array(''=>$this->tr->translate("SELECT_ITEMS"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_items_id->setMultiOptions($options);
		$_items_id->setValue($request->getParam('items_id'));
		
		$_is_gived = new Zend_Dojo_Form_Element_FilteringSelect('is_gived');
		$_is_gived->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onChange'=>'setValueToRow();',
		));
		$rows = $db->getAllItemsMaterial();
		$options=array('-1'=>$this->tr->translate("SELECT_STATUS"),'0'=>$this->tr->translate("NOT_YET_GIVE"),'1'=>$this->tr->translate("GIVED_TO_CUSTOMER"));
		
		$_is_gived->setMultiOptions($options);
		$_is_gived->setValue($request->getParam('is_gived'));
		
		$this->addElements(array($_items_id,
					$_is_gived
		));
		return $this;
	}		
}