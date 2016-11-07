<?php 
Class Accounting_Form_Frmaccountname extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAccount($data=null){
		
		$account_id = new Zend_Dojo_Form_Element_NumberTextBox('account_id');
		$account_id->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside'
				));
	
		$account_namekh = new Zend_Dojo_Form_Element_TextBox('account_namekh');
		$account_namekh->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'
		));
		
		$account_nameeg = new Zend_Dojo_Form_Element_TextBox('account_nameeg');
		$account_nameeg->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'
		));
		
		
		$description = new Zend_Dojo_Form_Element_TextBox('description');
		$description->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'
		));
		
		$dispay_by = new Zend_Dojo_Form_Element_FilteringSelect('dispay_by');
		$dispay_by->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside'
		));
		
		$opt=array(1=>'NAME_ENGLISH',2=>'NAME_KMHER');
		$dispay_by->setMultiOptions($opt);
		
		
		$optionacc=new Zend_Dojo_Form_Element_RadioButton('optionacc');
		$optionacc->setAttribs(array(
				'dojoType'=>'dijit.form.RadioButton',
				'class'=>'fullside'
		));
		$option=array(1=>'Operation Account',2=>'Non_operation');
		$optionacc->setMultiOptions($option);
		$optionacc->setValue(1);
		
		
	
		
		$db = new Application_Model_DbTable_DbGlobal();
		$account_type = new Zend_Dojo_Form_Element_FilteringSelect('account_type');
		$account_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$opt= $db->getVewOptoinTypeByType(8,1);
		$account_type->setMultiOptions($opt);
		$account_type->setValue(1);
		
		$parent_acc = new Zend_Dojo_Form_Element_FilteringSelect('parent_acc');
		$parent_acc->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$db= new Application_Model_DbTable_DbGlobal();
		$sql="SELECT id,account_name_en FROM ln_account_name WHERE option_acc=2 AND status=1";
		$rows = $db->getGlobalDb($sql);
		//print_r($rows);exit;
		$opt = '';
		if(!empty($rows)){
			foreach($rows as $row){
				$opt[$row['id']]=$row['account_name_en'];
			}
		}
		$parent_acc->setMultiOptions($opt);
		
		
		$categories = new Zend_Dojo_Form_Element_FilteringSelect('categories');
		$categories->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true
		));
		$db= new Application_Model_DbTable_DbGlobal();
		$sql="SELECT category_id,account_name_kh FROM ln_account_name WHERE option_acc=2 AND STATUS=1";
		$rows = $db->getGlobalDb($sql);
		$opt = '';
		if(!empty($rows)){
			foreach($rows as $row){
				$opt[$row['category_id']]=$row['account_name_kh'];
			}
		}
		$categories->setMultiOptions($opt);
		
		
		$date = new Zend_Dojo_Form_Element_DateTextBox('date');
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$date->setValue(date('Y-m-d'));
		
		$status = new Zend_Dojo_Form_Element_TextBox('status');
		$status->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>true
		));
		
		$id = new Zend_Form_Element_Hidden("id");
		
		if($data!=null){
			$account_id->setValue($data['account_code']);
			$account_namekh->setValue($data['account_name_kh']);
			$account_nameeg->setValue($data['account_name_en']);
			$description->setValue($data['disc']);
			$dispay_by->setValue($data['displayby']);
			$optionacc->setValue($data['option_acc']);
			$account_type->setValue($data['account_type']);
			$parent_acc->setValue($data['parent_id']);
			$categories->setValue($data['category_id']);
			$date->setValue($data['date']);
			$status->setValue($data['status']);
			$id->setValue($data['id']);
		}
		
		$this->addElements(array($account_id,$description,$dispay_by,$optionacc
				,$account_type,$parent_acc,$categories,$date,$status,$account_namekh,$account_nameeg,$id));
		return $this;
	}
}