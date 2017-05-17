<?php 
class Dailywork_Form_FrmDailywork extends Zend_Form
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
		$db=new Application_Model_DbTable_DbGlobal();
		
		$db = new Dailywork_Model_DbTable_DbWorkcomplete();
		$row = $db->getUser();
		$db=new Dailywork_Model_DbTable_DbDailywork();
		$row_projct=$db->getProjectName();
		$_work= new Zend_Dojo_Form_Element_ValidationTextBox('work');
		$_work->setAttribs(array('missingMessage'=>'Invalid Module!',
				'class'=>'fullside',
				'required'=>1,
				'dojoType'=>"dijit.form.ValidationTextBox",
		));
		
		$_description= new Zend_Dojo_Form_Element_Textarea('description');
		$_description->setAttribs(array('dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
		         "style"=>'min-height:60px; font-size:14px; font-family:Kh Battambang;'
		));
		
		$opt_u = array(''=>"select user");
		if(!empty($row)){
			foreach ($row as $rs){
				$opt_u[$rs["id"]] = $rs["first_name"];
			}
		}
		$_user = new Zend_Dojo_Form_Element_FilteringSelect("user");
		$_user->setMultiOptions($opt_u);
		$_user->setAttribs(array(
				'class'=>'fullside',
				'dojoType'=>"dijit.form.FilteringSelect",));
		
		$opt_u=array(''=>"select Project");
		if (!empty($row_projct)){
			foreach ($row_projct as $rs){
				$opt_u[$rs["id"]]=$rs["projectname"];
			}
		}
		
		$_projectname=new Zend_Dojo_Form_Element_FilteringSelect("projectname");
		$_projectname->setMultiOptions($opt_u);
		$_projectname->setAttribs(array(
				'dojoType'=>"dijit.form.FilteringSelect",
				'class'=>'fullside'));
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'required'=>'true',
				'class'=>'fullside',
				'dojoType'=>"dijit.form.FilteringSelect",));
		
		$start_date=new Zend_Dojo_Form_Element_DateTextBox("start_date");
		$start_date->setAttribs(array(
				'class'=>'fullside',
				'dojoType'=>"dijit.form.DateTextBox",));
		$start_date->setValue(date("Y-m-d"));
		
		if(!empty($data)){
			$_work->setValue($data['work']);
			$_user->setValue($data['user']);
			$_description->setValue($data['description']);
			$_status->setValue($data['status']);
			$_projectname->setValue($data['projectname']);
			$start_date->setValue(($data['date']));
// 			$customerid->setValue($data['customer_id']);
		}
		$this->addElements(array($start_date,$_projectname,$_work,$_status,$_user,$_description));
		return $this;
		
	}
	
}