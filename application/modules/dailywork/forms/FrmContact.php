<?php 
class Dailywork_Form_FrmContact extends Zend_Form
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
		$db = new Dailywork_Model_DbTable_DbContact();
		$row = $db->getCategoryname();
		$_name= new Zend_Dojo_Form_Element_ValidationTextBox('name');
		$_name->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!','class'=>'fullside',
				'dojoType'=>"dijit.form.ValidationTextBox"
				
		));
		$_phone= new Zend_Dojo_Form_Element_ValidationTextBox('phone');
		$_phone->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!',
				'dojoType'=>"dijit.form.ValidationTextBox",
				'class'=>'fullside'
		));
		
		$startdate= new Zend_Dojo_Form_Element_TextBox('start_date');
		$startdate->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!',
				'dojoType'=>"dijit.form.DateTextBox",
				'class'=>'fullside'
		
		));
		$startdate->setValue(date("Y-m-d"));
		
		$_note= new Zend_Dojo_Form_Element_Textarea('note');
		$_note->setAttribs(array('required'=>'true','missingMessage'=>'Invalid Module!',
				'dojoType'=>"dijit.form.Textarea",
		 		'class'=>'fullside',
		       "style"=>"font-family:'Kh Battambang';font-size:14px;height:100px;",
				
				
		));
		
		$opt_u = array(''=>"select Category");
		if(!empty($row)){
			foreach ($row as $rs){
				$opt_u[$rs["id"]] = $rs["name"];
			}
		}
		
		//$_arr = array(1=>$this->tr->translate("WEBSITE"),2=>$this->tr->translate("SYSTEM"));
		$_category = new Zend_Dojo_Form_Element_FilteringSelect("category");
		$_category->setMultiOptions($opt_u);
		$_category->setAttribs(array(
				'required'=>'true','dojoType'=>"dijit.form.FilteringSelect",
				'class'=>'fullside'));
		if(!empty($data)){
			//print_r($data); exit();
			$_name->setValue($data['contactname']);
			$_phone->setValue($data['phone']);
			$_note->setValue($data['note']);
			$_category->setValue($data['category']);
			
		}
		$this->addElements(array($startdate,$_name,$_phone,$_note,$_category));
		return $this;
		
	}
	
}