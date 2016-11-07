<?php 
Class Other_Form_FrmVeiwType extends Zend_Dojo_Form {
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
	public function FrmViewType($data=null){
		$db = new  Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_CALLECTERALLTYPE_INFO")
		));
		$_title->setValue($request->getParam("adv_search"));
		$status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$status_search->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$status_search->setMultiOptions($_status_opt);
		$status_search->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
		
		));
		
		$name_en = new Zend_Dojo_Form_Element_TextBox('title_en');
		$name_en->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$name_kh = new Zend_Dojo_Form_Element_ValidationTextBox('title_kh');
		$name_kh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$key_code = new Zend_Dojo_Form_Element_NumberTextBox('key_code');
		$key_code->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
				));
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
		
		$_type = new Zend_Dojo_Form_Element_FilteringSelect("type");
		$_type->setMultiOptions($_arr);
		$_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
		$opt= $db->getAllViewType(1);
		$_type->setMultiOptions($opt);
		
		$_display=  new Zend_Dojo_Form_Element_FilteringSelect('display_by');
		$_display->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_ENGLISH"));
		$_display->setMultiOptions($_display_opt);
		
		$date_call = new Zend_Dojo_Form_Element_DateTextBox('date');
		$date_call->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$date_call->setValue(date('Y-m-d'));
	  $_id = new Zend_Form_Element_Hidden('id');
		
		if($data!=null){
			$name_en->setValue($data['title_en']);
			$name_kh->setValue($data['title_kh']);
			$_display->setValue($data['displayby']);
			$_status->setValue($data['status']);
			$date_call->setValue($data['date']);
			$_type->setValue($data['type']);
		    $_id->setValue($data['id']);
			
		}
		$this->addElements(array($_type,$status_search,$_title,$_btn_search,$name_en,$name_kh,$key_code,$_id,$_display,$_status,$date_call));
		return $this;
		
	}	
}