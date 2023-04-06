<?php 
Class Stockinout_Form_FrmStockOut extends Zend_Dojo_Form {
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
	public function FrmWithdrawStock($_data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock();
		$request = Zend_Controller_Front::getInstance()->getRequest();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate('SELECT_BRANCH'),
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
		
		$propertyType = new Zend_Dojo_Form_Element_FilteringSelect('propertyType');
		$propertyType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getAllProperty();',
				'required'=>'false',
				'placeHolder'=>$this->tr->translate('PROPERTY_TYPE')
		));
		
		$rsProtype = $db->getPropertyType();
		unset($rsProtype['-1']);
		$propertyType->setMultiOptions($rsProtype);
		$propertyType->setValue($request->getParam('propertyType'));
		
		$categoryId = new Zend_Dojo_Form_Element_FilteringSelect('categoryId');
		$categoryId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate('SELECT_CATEGORY'),
				'required'=>'false',
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
			'readonly'=>true,
			'placeholder'=>$this->tr->translate("REQUEST_NO"),
			'style'=>'color:red;font-weight: 600;',
    		'missingMessage'=>$this->tr->translate("Forget Enter Data")
			));
		
		$requestNoFromProject = new Zend_Dojo_Form_Element_TextBox('requestNoProject');
		$requestNoFromProject->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("REQUEST_NO_FROM"),
		));
		
		$workType = new Zend_Dojo_Form_Element_FilteringSelect('workType');
		$workType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'placeholder'=>$this->tr->translate('SELECT_WORK_TYPE'),
				'class'=>'fullside',
				'required'=>'false'
		));
		$opt = $dbGBStock->getAllWorkType(0,'','',1);
		$workType->setMultiOptions($opt);
		$workType->setValue($request->getParam('workType'));
		
		
		$withdrawDate = new Zend_Dojo_Form_Element_TextBox('withdrawDate');
		$withdrawDate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'readOnly'=>true,
				'class'=>'fullside'));
		$withdrawDate->setValue(date("Y-m-d"));
		
		
		$typeofWork = new Zend_Dojo_Form_Element_TextBox('typeofWork');
		$typeofWork->setAttribs(array(
			'dojoType'=>$this->tvalidate,
			'placeholder'=>$this->tr->translate('WORK_TYPE'),
			'class'=>'fullside',
		));
		
		$ConstructionWorker = new Zend_Dojo_Form_Element_TextBox('ConstructionWorker');
		$ConstructionWorker->setAttribs(array(
			'dojoType'=>$this->text,
			'class'=>'fullside',
			'placeholder'=>$this->tr->translate('ConstructionWorker'),
		));
		
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
			$requestNoFromProject->setValue($_data['reqOutNo']);
			$ConstructionWorker->setValue($_data['workerName']);
			$propertyType->setValue($_data['houseType']);
			$typeofWork->setValue($_data['typeofWork']);
			$withdrawDate->setValue($_data['requestDate']);
			$id->setValue($_data['id']);
			$workType->setValue($_data['workType']);
			$_status->setValue($_data['status']);
			$_note->setValue($_data['note']);
		}
		$this->addElements(array($categoryId,$ConstructionWorker,$requestNoFromProject,
				$propertyType,$typeofWork,$withdrawDate,$_branch_id,
				$workType,$requestNo,$_status,$id,$_note,
			));
		
		return $this;
	}
}