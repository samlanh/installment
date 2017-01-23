<?php 
Class Other_Form_FrmProperty extends Zend_Dojo_Form {
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
	public function FrmFrmProperty($_data=null){
	
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('search_status');
		$_status_search->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				'-1'=>$this->tr->translate("ALL"),
				'1'=>$this->tr->translate("ACTIVE"),
				'0'=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("search_status"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
		
		));
		$_db = new Application_Model_DbTable_DbGlobal();
		$rows=$_db->getPropertyTypeForsearch();
		$opt_co = array(''=>$this->tr->translate("SELECT_PROPERTY_TYPE"));
		$opt_co = $rows;
		$property = new Zend_Dojo_Form_Element_FilteringSelect('property_type');
		$property->setAttribs(array('dojoType'=>$this->filter,
				'class'=>'fullside',
		));
		$property->setMultiOptions($opt_co);
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				//'required'=>'true',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$options = $_db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
		$_branch_id->setValue($request->getParam("branch_id"));
		
		if(empty($_date)){
			//$_date = date("Y-m-d");
			$_date = '';
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$to_date->setValue(date("Y-m-d"));
		
		$streetlist = new Zend_Dojo_Form_Element_FilteringSelect('streetlist');
		$streetlist->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$streetopt = $db->getAllStreet();
		$streetlist->setMultiOptions($streetopt);
		$streetlist->setValue($request->getParam("streetlist"));
		
		
		$_type_of_property=  new Zend_Dojo_Form_Element_FilteringSelect('type_property_sale');
		$_type_of_property->setAttribs(array('dojoType'=>$this->filter,	'class'=>'fullside',));
		$_type_of = array(
				'-1'=>$this->tr->translate("ALL"),
				'1'=>$this->tr->translate("SOLD_OUT"),
				'0'=>$this->tr->translate("NOT_YET_SALE"));
		$_type_of_property->setMultiOptions($_type_of);
		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($_data)){
			//$_co->setValue($_data['co_name']);
		}
		$this->addElements(array($streetlist,$_branch_id,$_btn_search,$_status_search,$_title,$_id,$property,$_type_of_property,$from_date,$to_date));
		return $this;
	}
	
}