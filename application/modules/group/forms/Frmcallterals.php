<?php 
Class Group_Form_Frmcallterals extends Zend_Dojo_Form {
	protected $tr=null;
	protected $tvalidate=null ;//text validate
	protected $filter=null;
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
	public function FrmCallTeral($data=null){
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_COLLTERAL")
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
		
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required' =>'true',
    			'Onchange'=>"filterClient();"
    			
    	));
    	$rows = $db->getAllBranchName();
    	$options=array(''=>"------Select Branch------");
    	if(!empty($rows))
    		foreach($rows AS $row){
    		$options[$row['br_id']]=$row['branch_namekh'];
    	}
    	$_branch_id->setMultiOptions($options);
    	$_branch_id->setValue($request->getParam('branch_id'));
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = new Zend_Dojo_Form_Element_FilteringSelect('co_name');
		$rows = $db ->getAllCOName();
		$options=array(''=>"------Select Staff------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
		$co_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();'
		));
		$co_name->setMultiOptions($options);
		$co_name->setValue($request->getParam('co_name'));
		$getter_name = new Zend_Dojo_Form_Element_ValidationTextBox('getter_name');
		$getter_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$giver_name = new Zend_Dojo_Form_Element_TextBox('giver_name');
		$giver_name->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
				));
		
		$_name=new Zend_Dojo_Form_Element_ValidationTextBox('name');
		$_name->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside'
				));
		$relattive=new Zend_Dojo_Form_Element_ValidationTextBox('names');
		$relattive->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside'
		));
		$guarantor=new Zend_Dojo_Form_Element_ValidationTextBox('owner');
		$guarantor->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside'
		));
		$relative_guarantor=new Zend_Dojo_Form_Element_ValidationTextBox('and_name');
		$relative_guarantor->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside'
		));
		$relative_guarantor_=new Zend_Dojo_Form_Element_ValidationTextBox('and_names');
		$relative_guarantor_->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside'
		));
		
//         $db = new Application_Model_DbTable_DbGlobal();
// 		$collteral_type=new Zend_Dojo_Form_Element_FilteringSelect('collteral_type');
// 		$collteral_type->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside'
// 				));
// 		$opt= $db->getCollteralType(1);
// 		$collteral_type->setMultiOptions($opt);
// 		$collteral_type->setValue($request->getParam('collteral_type'));
		
		$note=new Zend_Dojo_Form_Element_TextBox('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$Date_estate=new Zend_Dojo_Form_Element_DateTextBox('date_estate');
		$Date_estate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
		));
		$Date_estate->setValue(date('Y-m-d'));
		$stutas = new Zend_Dojo_Form_Element_FilteringSelect('Stutas');
		$stutas ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
		$stutas->setMultiOptions($options);
		
		$cod_cal = new Zend_Dojo_Form_Element_TextBox('cod_cal');
		$cod_cal ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readOnly'=>'readOnly',
				'class'=>'fullside',
				'style'=>'color:red'
		));
		$code = Group_Model_DbTable_DbCallteral::getCallteralCode();
		$cod_cal->setValue($code);
		
		$from_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$from_date->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'));
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
		
		
		$id = new Zend_Form_Element_Hidden("id");
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$cod_cal->setValue($data['collecteral_code']);
			$co_name->setValue($data['co_id']);

			$_name->setValue($data['join_with']);
			$relattive->setValue($data['relative']);
			$guarantor->setValue($data['guarantor']);
			$relative_guarantor->setValue($data['guarantor_relative']);
			$note->setValue($data['note']);
// 			$Date_estate->setValue($data['date_registration']);
			$stutas->setValue($data['status']);
			$id->setValue($data['id']);
			
		}


		$this->addElements(array($from_date,$to_date,$_btn_search,$_status_search,$_title,$co_name,$getter_name,$giver_name,$Date,
				$_name,$relattive,$guarantor,$relative_guarantor,$note,	$Date_estate,$_branch_id,$id,$stutas,$cod_cal));
		return $this;
		
	}	
}