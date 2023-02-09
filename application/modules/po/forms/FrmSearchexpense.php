<?php 
Class Po_Form_FrmSearchexpense extends Zend_Dojo_Form {
	
	public function AdvanceSearch($data=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$textbox = 'dijit.form.TextBox';
		$numbertext='dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';
		
		$dbGB = new Application_Model_DbTable_DbGlobal(); 
    	$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
		$_status_opt = array(
				-1=>$tr->translate("SELECT_STATUS"),
				1=>$tr->translate("ACTIVE"),
				0=>$tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$tr->translate("SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		
		$_bydate=  new Zend_Dojo_Form_Element_FilteringSelect('by_date');
		$_bydate->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside'));
		$_date_opt = array(
				0=>$tr->translate("PLEASE_SELECT_DATE"),
				1=>$tr->translate("START_DATE"),
				2=>$tr->translate("END_DATE"));
				
		$_bydate->setMultiOptions($_date_opt);
		$_bydate->setValue($request->getParam("by_date"));
		
;
		
	
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_releasedate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'onchange'=>'CalculateDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$tr->translate("START_DATE"),
				'class'=>'fullside'));
		$_date = $request->getParam("start_date");
		
		if(!empty($_date)){
			$_releasedate->setValue($_date);
		}

		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_dateline->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'required'=>'true','class'=>'fullside',
				'placeholder'=>$tr->translate("END_DATE"),
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_dateline->setValue($_date);
		
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
		
		
		$this->addElements(array($_title,$branch_id,$_bydate,$_releasedate
				,$_dateline,$_status));
		return $this;
		
	}	
	
}