<?php 
Class Group_Form_FrmDepartment extends Zend_Dojo_Form {
	protected $tr=null;
	protected $tvalidate=null ;//text validate
	protected $filter=null;
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
	public function FrmAddDepartment($_data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_DEPARTMENT")
				));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status_search->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch'
		));
		
		$department_kh = new Zend_Dojo_Form_Element_TextBox('department_kh');
		$department_kh->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
				));
		
		$department_en = new Zend_Dojo_Form_Element_TextBox('department_en');
		$department_en->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
				));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		
		$_status_pop=  new Zend_Dojo_Form_Element_FilteringSelect('status_pop');
		$_status_pop->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_status_pop->setMultiOptions($_status_opt);
		
		$_display=  new Zend_Dojo_Form_Element_FilteringSelect('display');
		$_display->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',));
		
		$_display_pop=  new Zend_Dojo_Form_Element_FilteringSelect('display_pop');
		$_display_pop->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',));
		
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_ENGLISH"));
		$_display->setMultiOptions($_display_opt);
		
		$_display_pop->setMultiOptions($_display_opt);
		
		$_id = new Zend_Form_Element_Hidden('id');
		
// 			$position_en->setValue($_data['position_en']);
		
		if(!empty($_data)){
			$department_kh->setValue($_data['department_kh']);
			$department_en->setValue($_data['department_en']);
			$_status->setValue($_data['status']);
			$_display->setValue($_data['displayby']);
			$_id->setValue($_data['id']);
		}
		$this->addElements(array($_btn_search,$_status_search,$_title,$_id,$department_en,$department_kh,$_status,$_display,$_display_pop,$_status_pop));
		
		return $this;
	}
}