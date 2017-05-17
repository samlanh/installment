<?php 
class Dailywork_Form_Frmworkprocess extends Zend_Form
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
		
		$customerid=new Zend_Form_Element_Select('customer_id');
		$customerid ->setAttribs(array(
				'class' => 'validate[required] fullside',
				'dojoType'=>"dijit.form.FilteringSelect",
				'Onchange'=>'getCustomerInfo()',
		));
		$options = $db->getAllCustomer(1);
		$customerid->setMultiOptions($options);
		$this->addElement($customerid);
		
		$db = new Dailywork_Model_DbTable_DbWorkcomplete();
		$row = $db->getUser();
		$db=new Dailywork_Model_DbTable_DbDailywork();
		
		$_work= new Zend_Dojo_Form_Element_FilteringSelect('work');
		$_work->setAttribs(array('missingMessage'=>'Invalid Module!',
				'class'=>'fullside validate[required] select2me',
				'onchange'=>'getWorkDetail();',
				'required'=>1,
				'dojoType'=>"dijit.form.FilteringSelect",
		));
		$result = $db->getAllDailywork();
		$opt_work=array(""=>"Please Select Your Work");
		if(!empty($result)){
			foreach ($result as $rs){
				$opt_work[$rs["id"]] = $rs["work"];
			}
		}
		$_work->setMultiOptions($opt_work);
		
		$_description= new Zend_Dojo_Form_Element_Textarea('description');
		$_description->setAttribs(array('dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'required'=>1,
		         "style"=>'height:170px;font-family:Kh Battambang',
				'dojoType'=>"dijit.form.Textarea",
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
		
		$opt_u=array(''=>"select Task","-1"=>"Add New Task");
		
		$row_projct=$db->getProjectName();
		if (!empty($row_projct)){
			foreach ($row_projct as $rs){
				$opt_u[$rs["id"]]=$rs["projectname"];
			}
		}
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$uservalue = $request->getParam('user');
		$_user->setValue($uservalue);
		
		$_projectname=new Zend_Dojo_Form_Element_FilteringSelect("projectname");
		$_projectname->setMultiOptions($opt_u);
		$_projectname->setAttribs(array(
				'class'=>'fullside validate[required] select2me',"onChange"=>"getProject()",
				'dojoType'=>"dijit.form.FilteringSelect",));
		
		$projectvalue = $request->getParam('projectname');
		$_projectname->setValue($projectvalue);
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'required'=>'true',
				'class'=>'fullside',
				'dojoType'=>"dijit.form.FilteringSelect",));
		
		$start_date=new Zend_Dojo_Form_Element_TextBox("start_date");
		$start_date->setAttribs(array(
				'class'=>'fullside fullside-inline date-picker',
				'dojoType'=>"dijit.form.DateTextBox",));
		$start_date->setValue(date("Y-m-d"));
		
		$_arr = array(1=>$this->tr->translate("WORK_COMPLETED"),2=>$this->tr->translate("INPGROGRESS"),3=>$this->tr->translate("CANCEL"));
		$_workstatus = new Zend_Dojo_Form_Element_FilteringSelect("work_status");
		$_workstatus->setMultiOptions($_arr);
		$_workstatus->setAttribs(array(
				'required'=>'true',
				'dojoType'=>"dijit.form.FilteringSelect",
				'class'=>'fullside'));
		
		
		if(!empty($data)){
			$_work->setValue($data['work_id']);
			$_user->setValue($data['user']);
			$_description->setValue($data['description']);
			$_status->setValue($data['status']);
			$_projectname->setValue($data['project_id']);
			$start_date->setValue(date($data['date']));
// 			$customerid->setValue($data['customer_id']);
			$_workstatus->setValue($data['work_status']);
		}
		$this->addElements(array($_workstatus,$_work,$start_date,$_projectname,$_work,$_status,$_user,$_description));
		return $this;
		
	}
	
}