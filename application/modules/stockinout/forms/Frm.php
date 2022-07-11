<?php 
Class Po_Form_Frm extends Zend_Dojo_Form {
// 	public function init()
// 	{
// 	}
	public function Frmbranch($data=null){
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$textbox = 'dijit.form.TextBox';
		$numbertext='dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'filterClient();'
		));
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
		
		/*
		$ValidationTextBox = new Zend_Dojo_Form_Element_ValidationTextBox('ValidationTextBox');
		$ValidationTextBox->setAttribs(array(
				'dojoType'=>$tvalidate,
				'class'=>'fullside',
				'required'=>true,
		));
		
		$TextBox = new Zend_Dojo_Form_Element_TextBox('Textbox');
		$TextBox->setAttribs(array(
				'dojoType'=>$textbox,
				'class'=>'fullside',
		));
		
		$DateText = new Zend_Dojo_Form_Element_DateTextBox('DateText');
		$DateText->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		$NumberText = new Zend_Dojo_Form_Element_NumberTextBox('NumberBox');
		$NumberText->setAttribs(array(
				'dojoType'=>$numbertext,
				'class'=>'fullside',
				'required' =>'true',
		));
		
		$note = new Zend_Dojo_Form_Element_Textarea("note");
		$note->setAttribs(array(
				'dojoType'=>$tarea,
				'class'=>'fullside',
				'style'=>'width:100%;min-height:103px; font-size:14px; font-family:Kh Battambang'
		));
		*/
		
		
		if(!empty($data)){
			//$_branch_id->setValue($data['branch_id']);
		}
		
		$this->addElements(array(
				$_branch_id
		));
		return $this;
	}
}