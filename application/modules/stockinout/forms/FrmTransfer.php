<?php 
Class Stockinout_Form_FrmTransfer extends Zend_Dojo_Form {
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
	public function FrmTransfer($_data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock();
		$request = Zend_Controller_Front::getInstance()->getRequest();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getDataByBranch();'
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['project_name'];
		}
		$_branch_id->setMultiOptions($options);
		
		if(count($rows)==1){
			$_branch_id->setAttribs(array('readonly'=>'readonly'));
			if(!empty($rows)) foreach($rows AS $row){
				$_branch_id->setValue($row['br_id']);
			}
		}
		
		$toProjectId = new Zend_Dojo_Form_Element_FilteringSelect('toProjectId');
		$toProjectId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
		));
		
		$toProjectId->setMultiOptions($options);
		
		$categoryId = new Zend_Dojo_Form_Element_FilteringSelect('categoryId');
		$categoryId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getAllProduct();'
		));
		
		$rsCate = $dbGBStock->getAllCategoryProduct(0,'','',1);
		unset($rsCate['-1']);
		$categoryId->setMultiOptions($rsCate);
		
		$requestNo = new Zend_Dojo_Form_Element_TextBox('requestNo');
		$requestNo->setAttribs(array(
			'dojoType'=>$this->tvalidate,
			'required'=>'true',
			'class'=>'fullside',
			'readonly'=>true
			));
		
		$transferDate = new Zend_Dojo_Form_Element_TextBox('transferDate');
		$transferDate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'readOnly'=>true,
				'class'=>'fullside'));
		$transferDate->setValue(date("Y-m-d"));
		
		
		
		$driver = new Zend_Dojo_Form_Element_TextBox('driver');
		$driver->setAttribs(array(
			'dojoType'=>$this->tvalidate,
			'required'=>'true',
			'class'=>'fullside',
		));
		
		$transferer = new Zend_Dojo_Form_Element_TextBox('transferer');
		$transferer->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
		));
		
		$receiver = new Zend_Dojo_Form_Element_TextBox('receiver');
		$receiver->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
		));
		
		
		$useFor = new Zend_Dojo_Form_Element_TextBox('useFor');
		$useFor->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
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
		
		$id =  new Zend_Form_Element_Hidden('id');
		
		if(!empty($_data)){
			$_branch_id->setValue($_data['projectId']);
			$requestNo->setValue($_data['requestNo']);
			$useFor->setValue($_data['reqOutNo']);
			$transferDate->setValue($_data['workerName']);
			$toProjectId->setValue($_data['houseType']);
			$transferer->setValue($_data['typeofWork']);
			$driver->setValue($_data['requestDate']);
			$id->setValue($_data['id']);
			$receiver->setValue($_data['workType']);
			$_status->setValue($_data['status']);
			$_note->setValue($_data['note']);
		}
		$this->addElements(array($categoryId,$useFor,$receiver,
				$transferer,$driver,$transferDate,$_branch_id,
				$toProjectId,$requestNo,$_status,$id,$_note,
			));
		
		return $this;
	}
}