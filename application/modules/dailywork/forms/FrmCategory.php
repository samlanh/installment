<?php 
class Dailywork_Form_FrmCategory extends Zend_Form
{
	protected $tr;
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	/////////////	Form Product		/////////////////
	public function add($data=null){
		$_title= new Zend_Dojo_Form_Element_TextBox('title');
		$_title->setAttribs(array('required'=>'true',
				'dojoType'=>"dijit.form.TextBox",
				'class'=>'fullside'
				
		));
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DEACTIVE"));
		$_category = new Zend_Dojo_Form_Element_FilteringSelect("category");
		$_category->setMultiOptions($_arr);
		$_category->setAttribs(array(
				'required'=>'true',
				'dojoType'=>"dijit.form.FilteringSelect",
				'class'=>'fullside'));
		if(!empty($data)){
			$_title->setValue($data['name']);
			$_category->setValue($data['status']);
		}
		$this->addElements(array($_title,$_category));
		return $this;
		
	}
	
}