<?php

class Stockinout_Form_FrmAdjustStock extends Zend_Dojo_Form
{
	protected  $tr;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    }
    function FrmAdjust($data=null){
    	
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
		if (count($rows)==1){
			$branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($rows)) foreach($rows AS $row){
				$branch_id->setValue($row['br_id']);
			}
		}
		
		$beginingStockDate = new Zend_Dojo_Form_Element_DateTextBox('beginingStockDate');
 		$beginingStockDate->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
			'readOnly'=>'readOnly ',
 		));
		$beginingStockDate->setValue(date("Y-m-d"));
				
    	$note=  new Zend_Form_Element_Textarea('note');
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'font-family: inherit;  min-height:100px !important; max-width:99%;'));
    	
    	
		$date = new Zend_Dojo_Form_Element_DateTextBox('date');
 		$date->setAttribs(array(
 			'dojoType'=>'dijit.form.DateTextBox',
 			'class'=>'fullside',
 			'constraints'=>"{datePattern:'dd/MM/yyyy'}",
			'readOnly'=>'readOnly ',
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
		
		$counter = new Zend_Dojo_Form_Element_DateTextBox('counter');
		$counter->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'readOnly'=>'readOnly ',
		));
		$date->setValue(date("Y-m-d"));
		
    	if(!empty($data)){
			
// 			$branch_id->setValue($data["projectId"]);
// 			$branch_id->setAttribs(array(
// 				'readOnly'=>'readOnly',
// 			));
// 			$note->setValue($data["note"]);
// 			$date->setValue($data["date"]);
// 			$date->setAttribs(array(
// 				'readOnly'=>'readOnly',
// 			));
// 			$_status->setValue($data["status"]);
// 			$counter->setValue($data["id"]);
    	}
    	
    	$this->addElements(array(
    			$branch_id,
    			$categoryId,
				$note,
				$date,
    			$_status,
    			$counter,
    			$beginingStockDate
				
    			));
    	return $this;
    }
}

