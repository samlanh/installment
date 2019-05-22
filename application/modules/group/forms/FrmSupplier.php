<?php 
Class Group_Form_FrmSupplier extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $text;
	protected $tarea=null;
	protected $t_num=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}
	public function FrmAddCO($_data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SEARCH")
				));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status_search->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status_search->setMultiOptions($_status_opt);
		$_status_search->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'values'=>'Search',
				'label'=>$this->tr->translate("SEARCH")
		));
		$_btn_search->setLabel("Search");
		
		$db = new Application_Model_DbTable_DbGlobal();
		$_supplier_code = new Zend_Dojo_Form_Element_TextBox('supplier_code');
		$_supplier_code->setAttribs(array('dojoType'=>$this->tvalidate,'required'=>'true','class'=>'fullside'));
		$_supplier_code->setValue($db->getSupplierCodeByBranch(1));
		
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'Onchange'=>'getStaffCode();'
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['project_name'];
		}
		$_branch_id->setMultiOptions($options);
		
		$_name_kh = new Zend_Dojo_Form_Element_TextBox('name');
		$_name_kh->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
				));
		
		
		$_tel = new Zend_Dojo_Form_Element_TextBox('tel');
		$_tel->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));
		
		
		
		$_email = new Zend_Dojo_Form_Element_ValidationTextBox('email');
		$_email->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
// 				'dojoProps'=>"regExp: '/^[a-zA-Z]+[a-zA-Z0-9]*@[a-zA-Z]+[a-zA-Z0-9][a-zA-Z]{2,4}([a-zA-Z]{2,4})?$/'",
				'class'=>'fullside',
				));
		
// 		$pattern="/^[a-zA-Z]+[a-zA-Z0-9]*@[a-zA-Z]+[a-zA-Z0-9][a-zA-Z]{2,4}([a-zA-Z]{2,4})?$/";
// 		if(preg_match($pattern,$_email));

		$_address = new Zend_Dojo_Form_Element_TextBox('address');
		$_address->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		
		
		$_note =  new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		
		$_id = new Zend_Form_Element_Hidden('id');
		
		if(!empty($_data)){
			$_branch_id->setValue($_data['branch_id']);
			$_supplier_code->setValue($_data['supplier_code']);
			$_name_kh->setValue($_data['name']);
			$_tel->setValue($_data['phone']);
			$_email->setValue($_data['email']);
			$_address->setValue($_data['address']);
			$_status->setValue($_data['status']);
			$_id->setValue($_data['id']);
			$_note->setValue($_data['note']);//echo $_data['note']; exit();
			
			
		}
		$this->addElements(array($_btn_search,$_status_search,$_title,$_id,$_supplier_code,$_name_kh,$_branch_id,
				$_tel,$_email,$_address,$_status,$_note,
				));
		
		return $this;
	}
}