<?php

class Application_Form_FrmAdvanceSearchStock extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function AdvanceSearch($data=null,$type=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$dbGB = new Application_Model_DbTable_DbGlobal(); 
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->text,
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
				));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		
		
		

		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'label'=>$this->tr->translate("SEARCH")
				
				));
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'false',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$rows = $dbGB->getAllBranchName();
		$options_branch=array('-1'=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));
		
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'placeholder'=>$this->tr->translate('START_DATE'),
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			//$_date = date("Y-m-d");
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$to_date->setValue($_date);
		
		$checkingStatus=  new Zend_Dojo_Form_Element_FilteringSelect('checkingStatus');
		$checkingStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = array(
				0=>$this->tr->translate("CHECKING_STATUS"),
				1=>$this->tr->translate("APPROVED"),
				2=>$this->tr->translate("REJECTED"),
				);
		$checkingStatus->setMultiOptions($_opts);
		$checkingStatus->setValue($request->getParam("checkingStatus"));
		
		$pCheckingStatus=  new Zend_Dojo_Form_Element_FilteringSelect('pCheckingStatus');
		$pCheckingStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = array(
				0=>$this->tr->translate("PCHECKING_STATUS"),
				1=>$this->tr->translate("APPROVED"),
				2=>$this->tr->translate("REJECTED"),
				);
		$pCheckingStatus->setMultiOptions($_opts);
		$pCheckingStatus->setValue($request->getParam("pCheckingStatus"));
		
		$approveStatus=  new Zend_Dojo_Form_Element_FilteringSelect('approveStatus');
		$approveStatus->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside'));
		$_opts = array(
				0=>$this->tr->translate("APPROVED_STATUS"),
				1=>$this->tr->translate("APPROVED"),
				2=>$this->tr->translate("REJECTED"),
				);
		$approveStatus->setMultiOptions($_opts);
		$approveStatus->setValue($request->getParam("approveStatus"));
		
		
		$this->addElements(
			array(
				$_title,
				$_status,
				$_btn_search,
				$branch_id,
				$from_date,
				$to_date,
				
				$checkingStatus,			
				$pCheckingStatus,			
				$approveStatus,			
			)
		);
		return $this;
	}
	
}