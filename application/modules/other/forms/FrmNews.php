<?php 
Class Other_Form_FrmNews extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddNews($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_arr=array(
				"1"=>$this->tr->translate("ACTIVE"),
				"0"=>$this->tr->translate("DEACTIVE"),
		);
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside'));
		$_status->setValue($request->getParam('status'));
		
		$public_date = new Zend_Dojo_Form_Element_DateTextBox('public_date');
		$public_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$public_date->setValue(date("Y-m-d"));
		$id = new Zend_Form_Element_Hidden("id");
		$id_cient = new Zend_Form_Element_Hidden("idclient");
// 		print_r($data);exit();
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch'
		));
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:100%;min-height:60px;'));
		$note->setValue($request->getParam('note'));
		
		
		$_status_search = new Zend_Dojo_Form_Element_FilteringSelect("status_search");
		$_arrsearch=array(
				""=>$this->tr->translate("SELECT_STATUS"),
				"1"=>$this->tr->translate("ACTIVE"),
				"0"=>$this->tr->translate("DEACTIVE"),
		);
		$_status_search->setMultiOptions($_arrsearch);
		$_status_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'class'=>'fullside',
		));
		$_status_search->setValue($request->getParam('status_search'));
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				//'required'=>'true',
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
		
		if($data!=null){
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
			$public_date->setValue($data['publish_date']);
		}
		
		$this->addElements(array($id,$public_date,$_btn_search,$_status,$note,$_adv_search,$_status_search,$from_date,$to_date));
		return $this;
	}	
	
}