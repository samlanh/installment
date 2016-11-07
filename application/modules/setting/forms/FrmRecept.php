<?php 
Class Registrar_Form_FrmRecept extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->check='dijit.form.CheckBox';
	}
	public function FrmRecept($data=null){
	
		$_no = new Zend_Dojo_Form_Element_TextBox('no');
		$_no->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside',));
		
		$_no_date = new Zend_Dojo_Form_Element_TextBox('no_date');
		//$date = new Zend_Date();
		//echo $date->get('MM-dd-YYYY');
		//$date = new Zend_Date();
 
// Output of the desired date
		$_no_date->setAttribs(array('dojoType'=>$this->t_date,'class'=>'fullside',));
		//$_no_date->setValue($date->get('MM-dd-YYYY'));
		
		$_kh_name = new Zend_Dojo_Form_Element_TextBox('kh_name');
		$_kh_name->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_en_name = new Zend_Dojo_Form_Element_TextBox('en_name');
		$_en_name->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_id_number = new Zend_Dojo_Form_Element_TextBox('id_number');
		$_id_number->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_sex=  new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_sex_opt = array(
				1=>$this->tr->translate("Male"),
				2=>$this->tr->translate("Female"));
		$_sex->setMultiOptions($_sex_opt);
		
		$_dob= new Zend_Dojo_Form_Element_TextBox('dob');
		$_dob->setAttribs(array('dojoType'=>$this->t_date,'class'=>'fullside',));
		
		$_course = new Zend_Dojo_Form_Element_TextBox('course');
		$_course->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$_level = new Zend_Dojo_Form_Element_TextBox('level');
		$_level->setAttribs(array('dojoType'=>$this->t_num,'required'=>'true','class'=>'fullside',));
		
		$_tel = new Zend_Dojo_Form_Element_TextBox('tel');
		$_tel->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside',));
		
		$_start_date= new Zend_Dojo_Form_Element_TextBox('start_date');
		$_start_date->setAttribs(array('dojoType'=>$this->t_date,'class'=>'fullside',));
		
		$_end_date= new Zend_Dojo_Form_Element_TextBox('end_date');
		$_end_date->setAttribs(array('dojoType'=>$this->t_date,'class'=>'fullside',));
		
		$_session = new Zend_Dojo_Form_Element_FilteringSelect('session');
		$_session->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_session_opt = array(
				1=>$this->tr->translate("Morning"),
				2=>$this->tr->translate("Afternoon"),
				3=>$this->tr->translate("Evening"),
				4=>$this->tr->translate("weekend"));
		$_session->setMultiOptions($_session_opt);
		
		$_course_of_study = new Zend_Dojo_Form_Element_TextBox('course_of_study_materials');
		$_course_of_study->setAttribs(array('dojoType'=>$this->t_num,'required'=>'true','class'=>'fullside',));
		
		$_payment_for = new Zend_Dojo_Form_Element_TextBox('payment_for');
		$_payment_for->setAttribs(array('dojoType'=>$this->t_num,'required'=>'true','class'=>'fullside',));
		
		$_tuition_fee = new Zend_Dojo_Form_Element_TextBox('tuition_fee');
		$_tuition_fee->setAttribs(array('dojoType'=>$this->t_num,'required'=>'true','class'=>'fullside',));
		
		$_discount = new Zend_Dojo_Form_Element_TextBox('discount');
		$_discount->setAttribs(array('dojoType'=>$this->t_num,'class'=>'fullside',));
		
		$_amount_paid = new Zend_Dojo_Form_Element_TextBox('amount_paid');
		$_amount_paid->setAttribs(array('dojoType'=>$this->t_num,'required'=>'true','class'=>'fullside',));
		
		$_in_words = new Zend_Dojo_Form_Element_TextBox('in_words');
		$_in_words->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside',));
		
		$this->addElements(array($_no,$_no_date,$_kh_name,$_en_name,$_id_number,$_sex,$_dob
				,$_course,$_level,$_tel,$_start_date,$_end_date,$_session,$_course_of_study,
				$_payment_for,$_tuition_fee,$_discount,$_amount_paid,$_in_words));
		
		return $this;
		
	}
	
}