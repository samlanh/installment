<?php 
Class Other_Form_FrmStaff extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $text;
	protected $date;
	protected $tarea=null;
	protected $tnumber;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->date = 'dijit.form.DateTextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
		$this->tnumber='dijit.form.NumberTextBox';
	}
	public function FrmAddStaff($_data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_db = new Application_Model_DbTable_DbGlobal();
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$options = $_db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
		$_branch_id->setValue($request->getParam("branch_id"));
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('search_status');
		$_status_search->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("search_status"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'class'=>'fullside',
		));
		
		$_co = new Zend_Dojo_Form_Element_FilteringSelect('co_khname');
		$_co->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',
		));
		$rowss = $_db->getAllCOName();
		$opt_co=array(''=>$this->tr->translate("SELECT_SALE_AGENT"));
		if(!empty($rowss))foreach($rowss AS $row){
			$opt_co[$row['id']]=$row['name'];
		}
		
		$_co->setMultiOptions($opt_co);
		$_co->setValue($request->getParam('co_khname'));
		
		
		$_date = $request->getParam("start_date");
		$_salary = new Zend_Dojo_Form_Element_TextBox('salary');
		$_salary->setAttribs(array('dojoType'=>$this->tnumber,'required'=>'true','class'=>'fullside',));
		$_staff_id = new Zend_Dojo_Form_Element_TextBox('staff_id');
		$_staff_id->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_startdate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_startdate->setAttribs(array('dojoType'=>$this->date,
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate('START_DATE'),
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");

		if(empty($_date)){
			//$_date = date('Y-m-01');
			$_date='';
		}
		$_startdate->setValue($_date);
		
		
		$_enddate = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_enddate->setAttribs(array('dojoType'=>$this->date,'required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_enddate->setValue($_date);
		
		$_amount_day = new Zend_Dojo_Form_Element_NumberTextBox('amount_day');
		$_amount_day->setAttribs(array('dojoType'=>'dijit.form.NumberTextBox','required'=>'true',
				'class'=>'fullside',
				'onkeyup'=>'CalculateDate();',
				));
		
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_id = new Zend_Form_Element_Hidden('id');
		
		$_sex = new Zend_Dojo_Form_Element_FilteringSelect('co_sex');
		$_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = array(-1=>$this->tr->translate('SELECT_GENDER'),1=>"Male",2=>"Female");
		$_sex->setMultiOptions($opt);
		$_sex->setValue($request->getParam("co_sex"));
		
		$land_id = new Zend_Dojo_Form_Element_FilteringSelect('land_id');
		$land_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$options = $db ->getAllLandInfo(null,null,1);//show name,show group,show option
		$land_id->setMultiOptions($options);
		$land_id->setValue($request->getParam("land_id"));
		
		if(!empty($_data)){
			$_co->setValue($_data['co_name']);
			$_startdate->setValue($_data['start_date']);
			$_amount_day->setValue($_data['amount_day']);
			$_enddate->setValue($_data['end_date']);
			$_status->setValue($_data['status']);
			$_id->setValue($_data['id']);
			$_note->setValue($_data['note']);
		}
		$this->addElements(array($land_id,$_sex,$_branch_id,$_salary,$_staff_id,$_btn_search,$_status_search,$_title,$_id,$_co,$_note,$_startdate,$_enddate,$_amount_day,$_status));
		return $this;
	}
	
}