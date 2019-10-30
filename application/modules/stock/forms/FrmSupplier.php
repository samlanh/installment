<?php 
Class Stock_Form_FrmSupplier extends Zend_Dojo_Form {
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
	public function FrmSupplier($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$supplier_name = new Zend_Dojo_Form_Element_TextBox('supplier_name');
		$supplier_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>'true',
				'class'=>'fullside height-text',
				'missingMessage'=>$this->tr->translate("Forget Enter Supplier")
		));
		
		
		$_sex=  new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_sexopt = array(
				1=>$this->tr->translate("MALE"),
				2=>$this->tr->translate("FEMALE"));
		$_sex->setMultiOptions($_sexopt);
		
		
		$tel = new Zend_Dojo_Form_Element_NumberTextBox('tel');
		$tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>'true',
				));
		
		$email = new Zend_Dojo_Form_Element_NumberTextBox('email');
		$email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfive()'
		));
		
		$website = new Zend_Dojo_Form_Element_NumberTextBox('website');
		$website->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfive()'
		));
		
		$_fax = new Zend_Dojo_Form_Element_TextBox('fax ');
		$_fax->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calone()'
				));
		
		$note = new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				));
		
		$address = new Zend_Dojo_Form_Element_TextBox('address');
		$address->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		
		$_id = new Zend_Form_Element_Hidden('id');
		
		$_adv_search = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_adv_search->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH")
		));
		$_adv_search->setValue($request->getParam("adv_search"));
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status_search->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("status_search"));
		
		
		//date
		$start_date= new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'value'=>'now',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',));
		$_date = $request->getParam("start_date");
		$start_date->setValue($_date);
		
		$end_date= new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(array(
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required'=>false));
		$_date = $request->getParam("end_date");
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'value'=>' Search ',
		
		));
		
		if(!empty($data)){
			$supplier_name->setValue($data['sup_name']);
			$_sex->setValue($data['sex']);
			$tel->setValue($data['tel']);
			$email->setValue($data['email']);
// 			$website->setValue($data['parent']);
// 			$_fax->setValue($data['parent']);
			$note->setValue($data['note']);
			$address->setValue($data['address']);
			$_status->setValue($data['status']);
			$_id->setValue($data['id']);
		}
		
		$this->addElements(array(
				$supplier_name,
				$_sex,
				$tel,
				$email,
				$website,
				$_fax,
				$note,
				$address,
				$_status,
				$_id,
				$_adv_search,
				$_status_search,
				$start_date,
				$end_date
				));
		
		return $this;
		
	}
	
}