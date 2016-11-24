<?php 
Class Group_Form_FrmPropertiestype extends Zend_Dojo_Form {
	protected $tr=null;
	protected $tvalidate=null ;//text validate
	protected $filter=null;
	protected $text=null;
	protected $tarea=null;
	public function getUserName(){
		$session_user=new Zend_Session_Namespace('auth');
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
	
	public function FrmPropertiesType($data=null){
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
				0=>$this->tr->translate("DACTIVE"));
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
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		
		$note=new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		$Date->setValue(date('Y-m-d'));
		$status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$status ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$status->setMultiOptions($options);
		
		$property_type_nameen=new Zend_Dojo_Form_Element_ValidationTextBox('type_nameen');
		$property_type_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
				));
		$property_type_namekh=new Zend_Dojo_Form_Element_ValidationTextBox('type_namekh');
		$property_type_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$id = new Zend_Form_Element_Hidden("id");
		if($data!=null){
			$property_type_nameen->setValue($data['type_nameen']);
			$property_type_namekh->setValue($data['type_namekh']);
			$note->setValue($data['note']);
			$Date->setValue($data['date']);
			$status->setValue($data['status']);
			//$id->setValue($data['return_id']);
		}
		
		$this->addElements(array($property_type_nameen,$property_type_namekh,$_btn_search,$_status_search,
				$Date,$note,$Date,$id,$status,$_title));
		return $this;
		
	}	
}