<?php 
Class Accounting_Form_Frmfixedasset extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmFixedAsset($data=null){
		
		$asset_name = new Zend_Dojo_Form_Element_FilteringSelect('asset_name');
		$asset_name->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
				));
		
		$asset_type = new Zend_Dojo_Form_Element_FilteringSelect('asset_type');
		$asset_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt=array(1=>'Long Term',2=>'Short Term');
		$asset_type->setMultiOptions($opt);
		
		$asset_cost=new Zend_Dojo_Form_Element_NumberTextBox('asset_cost');
		$asset_cost->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true'
				));
		
		
		$useful_life = new Zend_Dojo_Form_Element_NumberTextBox('usefull_life');
		$useful_life->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$salvage_value=new Zend_Dojo_Form_Element_ValidationTextBox('salvage_value');
		$salvage_value->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required'=>'true'
				));
		
		$db = new Application_Model_DbTable_DbGlobal();
		$payment_method=new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
				));
		$opt= $db->getVewOptoinTypeByType(16,1);
		$payment_method->setMultiOptions($opt);
		$payment_method->setValue(1);
		
		$Date=new Zend_Dojo_Form_Element_DateTextBox('date');
		$Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside'
				));
		
		$branch=new Zend_Dojo_Form_Element_FilteringSelect('branch');
		$branch->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
				));
		$_id = new Zend_Form_Element_Hidden('id');
		
		$this->addElements(array($asset_name,$asset_type,$asset_cost,$useful_life,$salvage_value,'asset_code,
				$payment_method,
				$Date,$branch));
		return $this;
		
	}	
}