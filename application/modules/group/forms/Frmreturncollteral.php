<?php 
Class Group_Form_Frmreturncollteral extends Zend_Dojo_Form {
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
	
	public function FrmReturnCollteral($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_client_code = new Zend_Dojo_Form_Element_FilteringSelect('client_code');
		$_client_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getOwnerInfo();'
		));
		$group_opt = $db ->getGroupCodeById(1,0,1);//code,individual,option
		$_client_code->setMultiOptions($group_opt);
		$_client_code->setValue($request->getParam('client_code'));
		
		$clint_name = new Zend_Dojo_Form_Element_FilteringSelect('client_name');
		$clint_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'checkClientCode()'
		));
		$options = $db->getGroupCodeById(2,0,1);
		$clint_name->setMultiOptions($options);
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));

		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status_search->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch'
		));
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
				));
		
		$note=new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$note->setValue('out of loan');
	
		$Date->setValue(date('Y-m-d'));
		$stutas = new Zend_Dojo_Form_Element_FilteringSelect('stutas');
		$stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$stutas->setMultiOptions($options);
		
		$receiver_name=new Zend_Dojo_Form_Element_ValidationTextBox('receiver_name');
		$receiver_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
				));
		
		$giver_name=new Zend_Dojo_Form_Element_ValidationTextBox('giver_name');
		$giver_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$to_date->setValue($_date);
		
		$from = new Zend_Dojo_Form_Element_FilteringSelect('from');
		$from->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$from->setValue($request->getParam('from'));
		$opt= $db->getCollteralType(1);
		$from->setMultiOptions($opt);
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$rows = $db->getAllBranchName();
		$options=array(''=>"------Select Branch Name------");
		if(!empty($rows))
			foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$_branch_id->setMultiOptions($options);
		$_branch_id->setValue($request->getParam('branch_id'));
		
		$collteral_type=new Zend_Dojo_Form_Element_FilteringSelect('collteral_type');
		$collteral_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		
		$opt= $db->getCollteralType(1);
		$collteral_type->setMultiOptions($opt);
		$collteral_type->setValue($request->getParam('collteral_type'));
		
		$id = new Zend_Form_Element_Hidden("id");
		$receiver_name->setValue($this->getUserName());
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$clint_name->setValue($data['client_id']);
			$_client_code->setValue($data['client_id']);
			$giver_name->setValue($data['receiver_name']);
			$receiver_name->setValue($data['giver_name']);
			$note->setValue($data['note']);
			$Date->setValue($data['date']);
			$stutas->setValue($data['status']);
			$id->setValue($data['return_id']);
		}
		
		$this->addElements(array($collteral_type,$_branch_id,$from,$_client_code,$clint_name,$from_date,$to_date,$giver_name,$receiver_name,$_btn_search,$_status_search,
				$_title,$Date,$note,$Date,$id,$stutas));
		return $this;
		
	}	
}