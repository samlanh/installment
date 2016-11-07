<?php 
Class Accounting_Form_FrmChartaccount extends Zend_Dojo_Form {
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
	public function FrmChartaccount($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$account_No = new Zend_Dojo_Form_Element_TextBox('account_No');
		$account_No->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'class'=>'fullside',
				'required'=>true
				));
		$db = new Application_Model_DbTable_DbGlobal();
		$account_Type = new Zend_Dojo_Form_Element_FilteringSelect('account_Type');
		$account_Type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true,
				'onchange'=>'getAllParentsAccount();'
		));
		$opt= $db->getVewOptoinTypeByType(8,1);
		unset($opt[-1]);
		//unset($opt['']);
		$account_Type->setMultiOptions($opt);
		//$account_Type->setValue(1);	
		
		$account_Name = new Zend_Dojo_Form_Element_TextBox('account_Name');
		$account_Name->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'class'=>'fullside',
				'required'=>true
		));
		$account_Nameen = new Zend_Dojo_Form_Element_TextBox('account_Nameen');
		$account_Nameen->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'class'=>'fullside',
				'required'=>true
		));
	
		
		$db = new Application_Model_DbTable_DbGlobal();
		$None_operation = new Zend_Dojo_Form_Element_FilteringSelect('none');
		$None_operation->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt= $db->getVewOptoinTypeByType(10,1);
		unset($opt[-1]);unset($opt['']);
		$None_operation->setMultiOptions($opt);
		$None_operation->setValue(1);
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'label'=>'Search'
		
		));
		
// 		$parent = new Zend_Dojo_Form_Element_FilteringSelect('parent');
// 		$parent->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required'=>true
				
// 		));
// 		$parent->setValue($request->getParam('parent'));
		
		$db = new Accounting_Model_DbTable_DbChartaccount();
// 		$option = $db->getAllchartaccount(3,1);
// 		$parent->setMultiOptions($option);
		
		$parents = new Zend_Dojo_Form_Element_CheckBox('parents');
		$parents->setAttribs(array(
				'dojoType'=>'dijit.form.CheckBox',
				'onchange'=>'getGroupCode();'
		));
		
		$Category=new Zend_Dojo_Form_Element_FilteringSelect("category");
		$Category->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$option = $db->getAllchartaccount(2,1);
		$Category->setMultiOptions($option);
		
		
		
	    $Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
		));
		$Date->setValue(date('Y-m-d'));
		
		$Status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$Status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$Status->setMultiOptions($opt);
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		
		$Balance = new Zend_Dojo_Form_Element_NumberTextBox('Balance');
		$Balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$Balance->setValue(0);
		$_id = new Zend_Form_Element_Hidden('id');
		
		if($data!=null){
			$account_No->setValue($data['account_code']);
			$account_Type->setValue($data['account_type']);
			$account_Name->setValue($data['account_name_kh']);
			$account_Nameen->setValue($data['account_name_en']);
			$None_operation->setValue($data['option_acc']);
// 			$parent->setValue($data['parent_id']);
			$parents->setValue($data['option_type']);
			$Category->setValue($data['category_id']);
			$Date->setValue($data['date']);
			$Status->setValue($data['status']);
			$Balance->setValue($data['balance']);
			$_id->setValue($data['id']);
				
		}

		$this->addElements(array($_btn_search,$_title,$_id,$account_No,$None_operation,$parents,$account_Type,$account_Name,$account_Nameen,$Category,$Date,$Status,$Balance));
		return $this;
		
	}	
}