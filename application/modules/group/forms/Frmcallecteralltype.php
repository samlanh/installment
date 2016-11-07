<?php 
Class Group_Form_Frmcallecteralltype extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function Frmcallecterall($data=null){
		
		$name_en = new Zend_Dojo_Form_Element_TextBox('title_en');
		$name_en->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$name_kh = new Zend_Dojo_Form_Element_TextBox('title_kh');
		$name_kh->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$key_code = new Zend_Dojo_Form_Element_NumberTextBox('key_code');
		$key_code->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
				));
		
		$_display=  new Zend_Dojo_Form_Element_FilteringSelect('display_by');
		$_display->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_ENGLISH"));
		$_display->setMultiOptions($_display_opt);
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside'));
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
			//$key_code->setValue($data['key_code']);
		    $_id->setValue($data['id']);
			
		}
		$this->addElements(array($name_en,$name_kh,$key_code,$_id,$_display,$_status,$date_call));
		return $this;
		
	}	
}