<?php 
Class Stockinout_Form_FrmStaffWorker extends Zend_Dojo_Form {
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
		$this->tarea = 'dijit.form.Textarea';
	}
	public function FrmWorker($_data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['project_name'];
		}
		$_branch_id->setMultiOptions($options);
		
		
		$staffName = new Zend_Dojo_Form_Element_TextBox('staffName');
		$staffName->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
				));
		
		$gender = new Zend_Dojo_Form_Element_FilteringSelect('gender');
		$gender->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = array(1=>"Male",2=>"Female");
		$gender->setMultiOptions($opt);
		
		$dob = new Zend_Dojo_Form_Element_TextBox('dob');
		$dob->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'placeholder'=>$this->tr->translate('DOB'),
				'class'=>'fullside',));
		
		
		$_tel = new Zend_Dojo_Form_Element_TextBox('tel');
		$_tel->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));

		
		$_address = new Zend_Dojo_Form_Element_TextBox('address');
		$_address->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_pob = new Zend_Dojo_Form_Element_TextBox('pob');
		$_pob->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>$this->filter,'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		
		$_note =  new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(array(
				'dojoType'=>$this->tarea,
				'class'=>'fullside',
				'style'=>'height:200px !important;'
				));
		
		$tel =  new Zend_Dojo_Form_Element_TextBox('tel');
		$tel->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		$position =  new Zend_Dojo_Form_Element_TextBox('position');
		$position->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		$id =  new Zend_Form_Element_Hidden('id');
		$id->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		if(!empty($_data)){
			$_branch_id->setValue($_data['projectId']);
			$staffName->setValue($_data['staffName']);
			$gender->setValue($_data['gender']);
			$_pob->setValue($_data['pob']);
			$_tel->setValue($_data['tel']);
			$_address->setValue($_data['address']);
			$_status->setValue($_data['status']);
			$id->setValue($_data['id']);
			$_note->setValue($_data['note']);
			$position->setValue($_data['position']);
			$tel->setValue($_data['tel']);
			
		}
		$this->addElements(array($position,$tel,$dob,$staffName,$_branch_id,
				$gender,$_tel,$_pob,$_address,$_status,$id,$_note,
			));
		
		return $this;
	}
}