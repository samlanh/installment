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
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getAllPropertyBranch();'
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['project_name'];
		}
		$_branch_id->setMultiOptions($options);
		
		$propertyType = new Zend_Dojo_Form_Element_FilteringSelect('propertyType');
		$propertyType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$rsSpp = $dbGBStock->getAllSupplier();
		$optSpp=array(''=>$this->tr->translate("SELECT_PROPERTY_TYPE"));
		if(!empty($rsSpp))foreach($rsSpp AS $row){
			$optSpp[$row['id']]=$row['name'];
		}
		$propertyType->setMultiOptions($optSpp);
		
		$requestNo = new Zend_Dojo_Form_Element_TextBox('requestNo');
		$requestNo->setAttribs(array(
			'dojoType'=>$this->tvalidate,
			'required'=>'true',
			'class'=>'fullside',
			));
		
		$requestNoFromProject = new Zend_Dojo_Form_Element_TextBox('requestNoProject');
		$requestNoFromProject->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'required'=>'true',
				'class'=>'fullside',
		));
		
		$workType = new Zend_Dojo_Form_Element_FilteringSelect('workType');
		$workType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = $dbGBStock->getViewById(4,1);//array(1=>"DELIVERY_NOTE",2=>"INVOICE");
		unset($opt['-1']);
		$workType->setMultiOptions($opt);
		
		$withdrawDate = new Zend_Dojo_Form_Element_TextBox('withdrawDate');
		$withdrawDate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'readOnly'=>true,
				'class'=>'fullside'));
		$withdrawDate->setValue(date("Y-m-d"));
		
		
		$typeofWork = new Zend_Dojo_Form_Element_TextBox('typeofWork');
		$typeofWork->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));

		
		$staffWithdraw = new Zend_Dojo_Form_Element_TextBox('staffWithdraw');
		$staffWithdraw->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$staffMg = new Zend_Dojo_Form_Element_TextBox('staffMg');
		$staffMg->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$ConstructionWorker = new Zend_Dojo_Form_Element_TextBox('ConstructionWorker');
		$ConstructionWorker->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		
		
		
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
		
		$photogoods =  new Zend_Form_Element_File('photo');
		$fileDn =  new Zend_Form_Element_File('fileDn');
		
		$id =  new Zend_Form_Element_Hidden('id');
		
		if(!empty($_data)){
			$_branch_id->setValue($_data['projectId']);
			$staffMg->setValue($_data['plateNo']);
			$staffWithdraw->setValue($_data['driverName']);
			$typeofWork->setValue($_data['staffCounter']);
			$requestNo->setValue($_data['dnNumber']);
			$workType->setValue($_data['dnType']);
			$_status->setValue($_data['status']);
			$id->setValue($_data['id']);
			$_note->setValue($_data['note']);
			$withdrawDate->setValue($_data['receiveDate']);
		}
		$this->addElements(array($ConstructionWorker,$requestNoFromProject,$propertyType,$fileDn,$photogoods,
				$staffMg,$staffWithdraw,$typeofWork,$withdrawDate,$_branch_id,
				$workType,$requestNo,$_status,$id,$_note,
			));
		
		return $this;
	}
}