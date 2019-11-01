<?php

class Project_Form_FrmImport extends Zend_Dojo_Form
{
	protected  $tr;
	protected $filter;

    public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();	
    	$this->filter = 'dijit.form.FilteringSelect';
    }
    function FrmImport($data){
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$typeItems = empty($typeItems)?1:$typeItems;
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	
   	 	$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$options_branch = $_dbgb->getAllBranchName(null,1);
		$branch_id->setMultiOptions($options_branch);
    	
    	
    	if(!empty($data)){
    		$branch_id->setValue($data["branch_id"]);
    	}
    	$this->addElements(array(
    			$branch_id,
    			));
    	return $this;
    }
}