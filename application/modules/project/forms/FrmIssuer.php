<?php 
Class Project_Form_FrmIssuer extends Zend_Dojo_Form {
	protected $tr=null;
	protected $tvalidate=null ;//text validate
	protected $filter=null;
	protected $text=null;
	protected $tarea=null;
	public function getUserName(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	
	public function FrmIssuer($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status_search->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'class'=>'fullside',
		));
		$label=$this->tr->translate("SEARCH");
		$_btn_search->setLabel("SEARCh");
		
		
		$nameKh=new Zend_Dojo_Form_Element_ValidationTextBox('nameKh');
		$nameKh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
				));
				
		$sex = new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$sex->setMultiOptions($opt_status);
		
		$dob=new Zend_Dojo_Form_Element_DateTextBox('dob');
		$dob->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$dob->setValue(date('Y-m-d'));
		
		$nationality = new Zend_Dojo_Form_Element_TextBox('nationality');
		$nationality->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$nationId = new Zend_Dojo_Form_Element_TextBox('nationId');
		$nationId->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		
				
		$nationIdIssueDate=new Zend_Dojo_Form_Element_DateTextBox('nationIdIssueDate');
		$nationIdIssueDate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$nationIdIssueDate->setValue(date('Y-m-d'));
				
		$tel = new Zend_Dojo_Form_Element_TextBox('tel');
		$tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$position = new Zend_Dojo_Form_Element_TextBox('position');
		$position->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$currentAddress=new Zend_Dojo_Form_Element_Textarea('currentAddress');
		$currentAddress->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height: 60px;font-size:12px;'
		));
		
		$nameKhWith=new Zend_Dojo_Form_Element_TextBox('nameKhWith');
		$nameKhWith->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				
				));
				
		$sexWith = new Zend_Dojo_Form_Element_FilteringSelect('sexWith');
		$sexWith->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$sexWith->setMultiOptions($opt_status);
		
		$dobWith=new Zend_Dojo_Form_Element_DateTextBox('dobWith');
		$dobWith->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$dobWith->setValue(date('Y-m-d'));
		
		$nationalityWith = new Zend_Dojo_Form_Element_TextBox('nationalityWith');
		$nationalityWith->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$nationIdWith = new Zend_Dojo_Form_Element_TextBox('nationIdWith');
		$nationIdWith->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		
				
		$nationIdIssueDateWith=new Zend_Dojo_Form_Element_DateTextBox('nationIdIssueDateWith');
		$nationIdIssueDateWith->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$nationIdIssueDateWith->setValue(date('Y-m-d'));
				
		$telWith = new Zend_Dojo_Form_Element_TextBox('telWith');
		$telWith->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$positionWith = new Zend_Dojo_Form_Element_TextBox('positionWith');
		$positionWith->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$currentAddressWith=new Zend_Dojo_Form_Element_Textarea('currentAddressWith');
		$currentAddressWith->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'min-height: 60px;font-size:12px;'
		));
	
		
		$status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$options= array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$status->setMultiOptions($options);
		
		
		$id = new Zend_Form_Element_Hidden("id");
		$id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		if($data!=null){
			
			$id->setValue($data['id']);
			$nameKh->setValue($data['nameKh']);
			$sex->setValue($data['sex']);
			$dob->setValue($data['dob']);
			$nationality->setValue($data['nationality']);
			$nationId->setValue($data['nationId']);
			$nationIdIssueDate->setValue($data['nationIdIssueDate']);
			$tel->setValue($data['tel']);
			$position->setValue($data['position']);
			$currentAddress->setValue($data['currentAddress']);
			
			$nameKhWith->setValue($data['nameKhWith']);
			$sexWith->setValue($data['sexWith']);
			$dobWith->setValue($data['dobWith']);
			$nationalityWith->setValue($data['nationalityWith']);
			$nationIdWith->setValue($data['nationIdWith']);
			$nationIdIssueDateWith->setValue($data['nationIdIssueDateWith']);
			$telWith->setValue($data['telWith']);
			$positionWith->setValue($data['positionWith']);
			$currentAddressWith->setValue($data['currentAddressWith']);
			
			$status->setValue($data['status']);
			
		}
		
		$this->addElements(array(
		
		$_title,
		$_status_search,
					$id,
					$nameKh,
					$sex,
					$dob,
					$nationality,
					$nationId,
					$nationIdIssueDate,
					$tel,
					$position,
					$currentAddress,
					
					$nameKhWith,
					$sexWith,
					$dobWith,
					$nationalityWith,
					$nationIdWith,
					$nationIdIssueDateWith,
					$telWith,
					$positionWith,
					$currentAddressWith,
					$status,
				));
		return $this;
		
	}	
}