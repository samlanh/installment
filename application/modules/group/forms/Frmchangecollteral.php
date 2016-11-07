<?php 
Class Group_Form_Frmchangecollteral extends Zend_Dojo_Form {
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
	
	public function FrmChangeCollteral($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_client_code = new Zend_Dojo_Form_Element_FilteringSelect('client_code');
		$_client_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'onchange'=>'getOwnerInfo();'
		));
		$group_opt = $db ->getGroupCodeById(1,0,1);//code,individual,option
		$_client_code->setMultiOptions($group_opt);
		$_client_code->setValue($request->getParam('client_code'));
		
		
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
		
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required' =>'true',
    			'onchange'=>'filterClient();'
    	));
    	$rows = $db->getAllBranchName();
    	$options=array(''=>"---Select Branch Name---");
    	if(!empty($rows))
    		foreach($rows AS $row){
    		$options[$row['br_id']]=$row['branch_namekh'];
    	}
    	$_branch_id->setMultiOptions($options);
    	$_branch_id->setValue($request->getParam('branch_id'));
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = new Zend_Dojo_Form_Element_FilteringSelect('co_name');
		$rows = $db ->getAllCOName();
		$options=array(''=>"------Select------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
		$co_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'onchange'=>'popupCheckCO();'
		));
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				));
		
		$number_code = new Zend_Dojo_Form_Element_NumberTextBox('number_code');
		$number_code->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>true
		));
		$contract_code = new Zend_Dojo_Form_Element_NumberTextBox('contract_code');
		$contract_code->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>true
		));
		
		$_code = new Zend_Dojo_Form_Element_NumberTextBox('code');
		$_code ->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>true
		));

		$clint_name = new Zend_Dojo_Form_Element_FilteringSelect('client_name');
		$clint_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'onchange'=>'checkClientCode()'
		));
		$options = $db->getGroupCodeById(2,0,1);
		$clint_name->setMultiOptions($options);
		
		$owner=new Zend_Dojo_Form_Element_ValidationTextBox('owner');
		$owner->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
		));
		
        $db = new Application_Model_DbTable_DbGlobal();
		$collteral_type=new Zend_Dojo_Form_Element_FilteringSelect('collteral_type');
		$collteral_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				));
		$opt= $db->getCollteralType(1);
		$opt=array(''=>'------Select------',1=>'ផ្ទាល់ខ្លួន',2=>'អ្នកធានាជំនួស');
		$collteral_type->setMultiOptions($opt);
		$collteral_type->setValue($request->getParam('collteral_type'));
		
		
		$note=new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
		));
		
		$_note=new Zend_Dojo_Form_Element_TextBox('_note');
		$_note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
		));
		$_note->setValue('return back to client');
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
		));
		$Date->setValue(date('Y-m-d'));
		$stutas = new Zend_Dojo_Form_Element_FilteringSelect('Stutas');
		$stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$stutas->setMultiOptions($options);
		
		$cod_cal = new Zend_Dojo_Form_Element_TextBox('cod_cal');
		$cod_cal ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readOnly'=>'readOnly',
				'style'=>'color:red'
		));
		$code = Group_Model_DbTable_DbCallteral::getCallteralCode();
		$cod_cal->setValue($code);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$from = new Zend_Dojo_Form_Element_FilteringSelect('from');
		$from->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>true
		));
		$from->setValue($request->getParam('from'));
		$opt= $db->getCollteralType(1);
		$from->setMultiOptions($opt);
		
		$to = new Zend_Dojo_Form_Element_FilteringSelect('to');
		$to->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>true
		));
		$opt= $db->getCollteralType(1);
		$to->setMultiOptions($opt);
		$to->setValue($request->getParam('to'));
		
		$receiver_name=new Zend_Dojo_Form_Element_ValidationTextBox('receiver_name');
		$receiver_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>true
				));
		
		$giver_name=new Zend_Dojo_Form_Element_ValidationTextBox('giver_name');
		$giver_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'required'=>true
		));
		
		$owner_name=new Zend_Dojo_Form_Element_TextBox('owner_name');
		$owner_name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				));
		
		$collteral_id=new Zend_Form_Element_Hidden('collteral_id');
		$collteral_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				));
		$changecollteral_id=new Zend_Form_Element_Text('changecollteral_id');
		$changecollteral_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				));
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$from_date->setValue($_date);
		
		
		$to_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$to_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true'
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$to_date->setValue($_date);
		
		$id = new Zend_Form_Element_Hidden("id");
		$receiver_name->setValue($this->getUserName());
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$giver_name->setValue($data['giver_name']);
			$receiver_name->setValue($data['receiver_name']);
			$note->setValue($data['note']);
			$Date->setValue($data['date']);
			$stutas->setValue($data['status']);
			$_note->setValue($data['return_note']);
			$id->setValue($data['id']);
			//$_client_code->setValue($data['owner_code_id']);
			//$clint_name->setValue($data['owner_id']);
			//$collteral_id->setValue($data['collteral_id']);
            //$changecollteral_id->setValue($data['id']);
			//$to->setValue($data['to_id']);
			//$collteral_type->setValue($data['collteral_type']);
			//$number_code->setValue($data['number_code']);
			// $owner_name->setValue($data['owner']);
		}
		$this->addElements(array($from_date,$to_date,$changecollteral_id,$collteral_id,$owner_name,$giver_name,$receiver_name,$_note,$from,$to,
				$_client_code,$_btn_search,$_status_search,$_title,$co_name,$Date,
				$number_code,$contract_code,$_code,$clint_name,$owner,$collteral_type,
				$note,$Date,$_branch_id,$id,$stutas,$cod_cal));
		return $this;
		
	}	
}