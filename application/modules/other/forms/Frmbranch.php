<?php 
Class Other_Form_Frmbranch extends Zend_Dojo_Form {
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
	public function Frmbranch($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH_BRANCH_INFO")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL"),
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'value'=>' Search ',
		
		));
		
		$br_id = new Zend_Dojo_Form_Element_TextBox('br_id');
		$br_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>'color:red',
				'onkeyup'=>'Calcuhundred()'
				));
		$br_code=Other_Model_DbTable_DbBranch::getBranchCode();
		$br_id->setValue($br_code);
		
		$branch_namekh = new Zend_Dojo_Form_Element_ValidationTextBox('branch_namekh');
		$branch_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Calfifty()'
				));

		$branch_nameen = new Zend_Dojo_Form_Element_ValidationTextBox('branch_nameen');
		$branch_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Caltweenty()'
				));
		$db=new Report_Model_DbTable_DbParamater();
		$rows=$db->getAllBranch();
		$opt_branch = array(''=>$this->tr->translate("SELECT_BRANCH_NAME"));
		if(!empty($rows))foreach($rows AS $row) $opt_branch[$row['br_id']]=$row['branch_nameen'];
		$select_branch_nameen = new Zend_Dojo_Form_Element_FilteringSelect('select_branch_nameen');
		$select_branch_nameen->setAttribs(array(
				'dojoType'=>$this->filter,
				'class'=>'fullside',
				'required'=>true,
				'onkeyup'=>'Caltweenty()'
		));
		$select_branch_nameen->setMultiOptions($opt_branch);
		$select_branch_nameen->setValue($request->getParam('select_branch_nameen'));
		
		$branch_code = new Zend_Dojo_Form_Element_NumberTextBox('branch_code');
		$branch_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>'color:red',
				'onkeyup'=>'Calcuhundred()'
				));
		$db_code=Other_Model_DbTable_DbBranch::getBranchCode();
		$branch_code->setValue($db_code);
		
		$branch_tel = new Zend_Dojo_Form_Element_NumberTextBox('branch_tel');
		$branch_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calfive()'
				));
		
		$_fax = new Zend_Dojo_Form_Element_TextBox('fax ');
		$_fax->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'onkeyup'=>'Calone()'
				));
		
		///*** result of calculator ///***
		$branch_note = new Zend_Dojo_Form_Element_TextBox('branch_note');
		$branch_note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
// 				'readonly'=>true
				));
		
		$prefix_code = new Zend_Dojo_Form_Element_ValidationTextBox('prefix_code');
		$prefix_code->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		
		$branch_status = new Zend_Dojo_Form_Element_FilteringSelect('branch_status');
		$branch_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
// 				'readonly'=>true
				));
		$options = array(1=>"ប្រើប្រាស់", 2=>"មិនប្រើប្រាស់");
		$branch_status->setMultiOptions($options);
		
		$branch_display = new Zend_Dojo_Form_Element_FilteringSelect('branch_display');
		$branch_display->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				));
		$_display_opt = array(
				1=>$this->tr->translate("NAME_KHMER"),
				2=>$this->tr->translate("NAME_EN"));
		$branch_display->setMultiOptions($_display_opt);
		
		$br_address = new Zend_Dojo_Form_Element_TextBox('br_address');
		$br_address->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
	
		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($data)){
			$br_id->setValue($data['br_id']);
			$prefix_code->setValue($data['prefix']);
			$branch_namekh->setValue($data['branch_namekh']);
			$branch_nameen->setValue($data['branch_nameen']);
			$select_branch_nameen->setValue($data['branch_nameen']);
			$br_address->setValue($data['br_address']);
			$branch_tel->setValue($data['branch_tel']);
			$branch_code->setValue($data['branch_code']);
			$_fax->setValue($data['fax']);
			$branch_note->setValue($data['other']);
			$branch_status->setValue($data['status']);
			$branch_display->setValue($data['displayby']);
		}
		
		$this->addElements(array($select_branch_nameen,$prefix_code,$_btn_search,$_title,$_status,$br_id,$branch_namekh,$branch_nameen,$br_address,$branch_code,$branch_tel,$_fax ,$branch_note,
				$branch_status,$branch_display));
		
		return $this;
		
	}
	
}