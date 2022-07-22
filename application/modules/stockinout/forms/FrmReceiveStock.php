<?php 
Class Stockinout_Form_FrmReceiveStock extends Zend_Dojo_Form {
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
	public function FrmReceivStock($_data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getAllPO();'
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>$this->tr->translate("SELECT_BRANCH"));
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['project_name'];
		}
		$_branch_id->setMultiOptions($options);
		
		$supplierId = new Zend_Dojo_Form_Element_FilteringSelect('supplierId');
		$supplierId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$rsSpp = $dbGBStock->getAllSupplier();
		$optSpp=array(''=>$this->tr->translate("SELECT_SUPPLIER"));
		if(!empty($rsSpp))foreach($rsSpp AS $row){
			$optSpp[$row['id']]=$row['name'];
		}
		$supplierId->setMultiOptions($optSpp);
		
		$dnTitle = new Zend_Dojo_Form_Element_TextBox('dnTitle');
		$dnTitle->setAttribs(array(
			'dojoType'=>$this->tvalidate,
			'required'=>'true',
			'class'=>'fullside',
			));
		
		$documentType = new Zend_Dojo_Form_Element_FilteringSelect('documentType');
		$documentType->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = array(1=>"DELIVERY_NOTE",2=>"INVOICE");
		$documentType->setMultiOptions($opt);
		
		$dnDate = new Zend_Dojo_Form_Element_TextBox('dnDate');
		$dnDate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'readOnly'=>true,
				'class'=>'fullside'));
		$dnDate->setValue(date("Y-m-d"));
		
		
		$counter = new Zend_Dojo_Form_Element_TextBox('counter');
		$counter->setAttribs(array('dojoType'=>$this->tvalidate,'class'=>'fullside',));

		
		$driver = new Zend_Dojo_Form_Element_TextBox('driver');
		$driver->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
		$truckNumber = new Zend_Dojo_Form_Element_TextBox('truckNumber');
		$truckNumber->setAttribs(array('dojoType'=>$this->text,'class'=>'fullside',));
		
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
		$id->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'));
		
		$photogoods =  new Zend_Form_Element_File('photo');
		$fileDn =  new Zend_Form_Element_File('fileDn');
		
		if(!empty($_data)){
			$_branch_id->setValue($_data['projectId']);
			$truckNumber->setValue($_data['staffName']);
			$driver->setValue($_data['gender']);
			$counter->setValue($_data['pob']);
			$dnTitle->setValue($_data['tel']);
			$documentType->setValue($_data['address']);
			$_status->setValue($_data['status']);
			$id->setValue($_data['id']);
			$_note->setValue($_data['note']);
			$dnDate->setValue($_data['note']);
		}
		$this->addElements(array($supplierId,$fileDn,$photogoods,$truckNumber,$driver,$counter,$dnDate,$_branch_id,
				$documentType,$dnTitle,$_status,$id,$_note,
			));
		
		return $this;
	}
}