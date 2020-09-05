<?php 
Class Project_Form_FrmStreet extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmStreet($data=null){
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$level = $session_user->level;
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Application_Model_DbTable_DbGlobal();
	
		$_code = new Zend_Dojo_Form_Element_TextBox('code');
		$_code->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
		));
	
		$_title = new Zend_Dojo_Form_Element_ValidationTextBox('title');
		$_title->setAttribs(array(
			'dojoType'=>'dijit.form.ValidationTextBox',
			'class'=>'fullside',
			'required' =>'true',
			'readOnly' =>'readOnly',
		));
		
		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:96%;min-height:50px;'));
		
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		if($data!=null){
			$_id->setValue($data['id']);
			$_code->setValue($data['code']);
			$_title->setValue($data['title']);
// 			$_note->setValue($data['note']);
		}
		$this->addElements(array(
				$_id,
				$_code,
				$_title,
				$_note
				));
		return $this;
	}
}