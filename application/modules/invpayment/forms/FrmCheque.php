<?php 
Class Invpayment_Form_FrmCheque extends Zend_Dojo_Form {
// 	public function init()
// 	{
// 	}
	public function FrmCheque($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$textbox = 'dijit.form.TextBox';
		$numbertext='dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$dbGB = new Application_Model_DbTable_DbGlobal(); 
    	$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 
    	
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'onChageFunctionByBranch();'
		));
		$rows = $dbGB->getAllBranchName();
		$options_branch=array('-1'=>$tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		if (count($rows)==1){
			$branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($rows)) foreach($rows AS $row){
				$branch_id->setValue($row['br_id']);
			}
		}
		
		
		$receiveDate = new Zend_Dojo_Form_Element_DateTextBox('receiveDate');
 		$receiveDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		$receiveDate->setValue(date("Y-m-d"));
		
		$receiverName = new Zend_Dojo_Form_Element_TextBox('receiverName');
    	$receiverName->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'placeholder'=>$tr->translate("RECEIVED_CHEQUE_NAME"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$tr->translate("Forget Enter Data")
    	));
		
		
		$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
				
		$withdrawDate = new Zend_Dojo_Form_Element_DateTextBox('withdrawDate');
 		$withdrawDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		$withdrawDate->setValue(date("Y-m-d"));
			
		$noteWithdraw=  new Zend_Form_Element_Textarea('noteWithdraw');
    	$noteWithdraw->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
				
		$_arr = array(1=>$tr->translate("WITHDRAWN"),0=>$tr->translate("PENDING"));
    	$statusWithdraw = new Zend_Dojo_Form_Element_FilteringSelect("statusWithdraw");
    	$statusWithdraw->setMultiOptions($_arr);
    	$statusWithdraw->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
		
		$_arr = array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
				
		$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    	));
		
		if(!empty($data)){
			$branch_id->setValue($data['projectId']);
			
			$receiveDate->setValue($data['receiveDate']);
			$receiverName->setValue($data['receiverName']);
			$note->setValue($data['note']);
			$withdrawDate->setValue($data['withdrawDate']);
			$noteWithdraw->setValue($data['noteWithdraw']);
			$statusWithdraw->setValue($data['statusWithdraw']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);

		}
		
		$this->addElements(array(
				$branch_id,
				$receiveDate,
				$receiverName,
				$note,
				$withdrawDate,
				$noteWithdraw,
				$statusWithdraw,
				$_status,
				$id
		));
		return $this;
	}
}