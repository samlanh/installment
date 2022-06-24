<?php

class Stockmg_Form_FrmRequest extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmRequestPO($data=null){
    	
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
		$options_branch=array('-1'=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
    	$requestNo = new Zend_Dojo_Form_Element_TextBox('requestNo');
    	$requestNo->setAttribs(array(
    			'dojoType'=>'dijit.form.ValidationTextBox',
    			'required'=>'true',
    			'class'=>'fullside ',
    			'readOnly'=>'readOnly ',
    			'placeholder'=>$this->tr->translate("REQUEST_NO"),
				'style'=>'color:red;font-weight: 600;',
    			'missingMessage'=>$this->tr->translate("Forget Enter Data")
    	));
    	
		$requestNoLetter = new Zend_Dojo_Form_Element_TextBox('requestNoLetter');
    	$requestNoLetter->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    			'placeholder'=>$this->tr->translate("REQUEST_NO_FROM"),
    			'missingMessage'=>$this->tr->translate("Forget Enter Data")
    	));
		
		$purpose=  new Zend_Form_Element_Textarea('purpose');
    	$purpose->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
				
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
    	
    	
		$date = new Zend_Dojo_Form_Element_DateTextBox('date');
 		$date->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		$date->setValue(date("Y-m-d"));
		
    	$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
    	$_status->setMultiOptions($_arr);
    	$_status->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
			

		$checkingDate = new Zend_Dojo_Form_Element_DateTextBox('checkingDate');
 		$checkingDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}"
 		));
		$checkingDate->setValue(date("Y-m-d"));
		
		$_arrSta = array(0=>$this->tr->translate("PENDING"),1=>$this->tr->translate("APPROVED"),2=>$this->tr->translate("REJECTED"));
    	$checkingStatus = new Zend_Dojo_Form_Element_FilteringSelect("checkingStatus");
    	$checkingStatus->setMultiOptions($_arrSta);
    	$checkingStatus->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>'true',
    			'missingMessage'=>'Invalid Module!',
    			'class'=>'fullside height-text',));
				
		$checkingNote=  new Zend_Form_Element_Textarea('checkingNote');
    	$checkingNote->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
				
    	
    	$id = new Zend_Form_Element_Hidden('id');
    	$id->setAttribs(array(
    			'dojoType'=>'dijit.form.TextBox',
    			'class'=>'fullside ',
    	));
    	
		$categoryId = new Zend_Dojo_Form_Element_FilteringSelect('categoryId');
		$categoryId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getAllProduct();'
		));
		$rsCate = $dbGBStock->getAllCategoryProduct();
		$optCatePro=array(''=>$this->tr->translate("SELECT_CATEGORY"));
		if(!empty($rsCate))foreach($rsCate AS $row){
			$optCatePro[$row['id']]=$row['name'];
		}
		$categoryId->setMultiOptions($optCatePro);
		
    	if(!empty($data)){
			
			$branch_id->setValue($data["projectId"]);
			$branch_id->setAttribs(array(
				'readOnly'=>'readOnly',
			));
			$requestNo->setValue($data["requestNo"]);
			$requestNoLetter->setValue($data["requestNoLetter"]);
			$purpose->setValue($data["purpose"]);
			$note->setValue($data["note"]);
			$date->setValue($data["date"]);
			$date->setAttribs(array(
				'readOnly'=>'readOnly',
			));
			$_status->setValue($data["status"]);
			$id->setValue($data["id"]);
			if(!empty($data["checkingRequest"])){
				$requestNoLetter->setAttribs(array(
				'readOnly'=>'readOnly',
				));
				$purpose->setAttribs(array(
				'readOnly'=>'readOnly',
				));
				$note->setAttribs(array(
				'readOnly'=>'readOnly',
				));
			}
			if(!empty($data["checkingDate"])){
			$checkingDate->setValue($data["checkingDate"]);
			}
			$checkingStatus->setValue($data["checkingStatus"]);
			$checkingNote->setValue($data["checkingNote"]);
			
    		
    	}
    	
    	$this->addElements(array(
    			$branch_id,
    			$requestNo,
    			$requestNoLetter,
    			$purpose,
				$note,
				$date,
    			$_status,
    			$id,
				
				$categoryId,
				$checkingDate,
				$checkingStatus,
				$checkingNote,
    			
    			));
    	return $this;
    }
}

