<?php 
Class Stock_Form_FrmReceived extends Zend_Dojo_Form {
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
	public function FrmReceived($data=null){
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		
		$_arr_opt_branch = array(""=>$this->tr->translate("PLEASE_SELECT_BRANCH"));
		$optionBranch = $_dbgb->getAllBranchByUser();
		if(!empty($optionBranch))foreach($optionBranch AS $row) $_arr_opt_branch[$row['id']]=$row['name'];
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect("branch_id");
		$_branch_id->setMultiOptions($_arr_opt_branch);
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'class'=>'fullside height-text',
				'onChange'=>'getReceivedNo(),getAllTransferByToBranch();'
				));
		
		
		$receive_no = new Zend_Dojo_Form_Element_TextBox('receive_no');
		$receive_no->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'style'=>'color:red',
		));
		
		$_receive_date = new Zend_Dojo_Form_Element_DateTextBox('receive_date');
		$_receive_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_receive_date->setValue(date('Y-m-d'));
		
		$note = new Zend_Dojo_Form_Element_Textarea('note');
		$note->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%; min-height:90px;'
		));
		
		$_statuss=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_statuss->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_statuss->setMultiOptions($_status_opt);
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',"class"=>"fullside",
				'placeholder'=>$this->tr->translate("SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
		$_status->setAttribs(array('dojoType'=>$this->filter,"class"=>"fullside",));
		$_status_opt = array(
				-1=>$this->tr->translate("ALL_STATUS"),
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
		$_f_branch = new Zend_Form_Element_Hidden('f_branch');
		$_f_branch->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
		));
		$_branch = new Zend_Form_Element_Hidden('branch');
		$_branch->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
		));
		
		$_id = new Zend_Form_Element_Hidden('id');
		if(!empty($data)){
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttribs(array(
					'readOnly'=>'readOnly',
			));
			$receive_no->setValue($data['receive_no']);
			$_receive_date->setValue($data['receive_date']);
			$note->setValue($data['note']);
			$_statuss->setValue($data['status']);
			$_id->setValue($data['id']);
// 			$_f_branch,
// 			$_branch
		}
		
		$this->addElements(array(
				$_id,
				$_btn_search,$_title,$_status,
				$_branch_id,
				$receive_no,
				$_receive_date,
				$note,
				$_f_branch,
				$_branch,
				$_statuss
				));
		
		return $this;
		
	}
	
}