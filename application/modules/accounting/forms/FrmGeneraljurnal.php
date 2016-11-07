<?php 
Class Accounting_Form_FrmGeneraljurnal extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmGeneraljurnal($data=null){
		
		$Brance = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$Brance->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true,
				'onchange'=>'getJurnalcode();'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranchName();
		$options='';
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		
		$rows = $db->getAllBranchName();
		$options=array(''=>'---Select Branch---');
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$Brance->setMultiOptions($options);
      	
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('currency_type');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = $db->getVewOptoinTypeByType(15,1,3,1);
		$_currency_type->setMultiOptions($opt);
		$_currency_type->setValue(2);
		
		$parent = new Zend_Dojo_Form_Element_FilteringSelect('parent');
		$parent->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>true,
				'onchange'=>'getAllAccountNameByParents();'
		
		));
		
		$db = new Accounting_Model_DbTable_DbChartaccount();
		$option = $db->getAllchartaccount(3,1);
		$parent->setMultiOptions($option);
		
		$Add_Date = new Zend_Dojo_Form_Element_DateTextBox('add_date');
		$Add_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$Add_Date->setValue(date('Y-m-d'));
		
		
		$Account_Number=new Zend_Dojo_Form_Element_TextBox('journal_code');
		$Account_Number->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'readOnly',
				'required'=>'true'
		));
		
		$invoice=new Zend_Dojo_Form_Element_TextBox('invoice');
		$invoice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$Note = new Zend_Dojo_Form_Element_TextBox('note');
		$Note->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside'
		));
		
		$Debit = new Zend_Dojo_Form_Element_NumberTextBox('debit');
		$Debit->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>1,
				'readonly'=>'readonly'
		));
// 		$Debit->setValue(0);
		
		$Credit = new Zend_Dojo_Form_Element_NumberTextBox('credit');
		$Credit->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>1,
				'readonly'=>'readonly'
		));
		
		$id = new Zend_Form_Element_Hidden('id');
		
		if($data!=null){
			$id->setValue($data['id']);
			$Brance->setValue($data['branch_id']);
			$Account_Number->setValue($data['journal_code']);
			$invoice->setValue($data['receipt_number']);
			$_currency_type->setValue($data['currency_id']);
			$Note->setValue($data['note']);
			$Add_Date->setValue($data['date']);
			$Debit->setValue($data['debit']);
			$Credit->setValue($data['credit']);
		}
// 		$Credit->setValue(0);
		
		$this->addElements(array($id,$invoice,$_currency_type,$parent,$Add_Date,$Account_Number,$Note,$Debit,$Credit,$Brance));
		return $this;
		
	}	
}