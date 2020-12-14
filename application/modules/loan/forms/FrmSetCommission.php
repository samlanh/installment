<?php 
Class Loan_Form_FrmSetCommission extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddSetCommission($data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'filterClient();setBranchID();'
		));
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);	
		
		$full_commission = new Zend_Dojo_Form_Element_NumberTextBox('full_commission');
		$full_commission->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required'=>true
		));
		$full_commission->setValue(0);
		

		
		$_id = new Zend_Form_Element_Hidden('id');
		$_id->setAttribs(array(
			'dojoType'=>'dijit.form.TextBox',
			'class'=>'fullside',
			'required' =>'true'
		));
		
		
		$_totoalCmminssionPaid = new Zend_Dojo_Form_Element_NumberTextBox('totoalCmminssionPaid');
		$_totoalCmminssionPaid->setAttribs(array(
			'dojoType'=>'dijit.form.NumberTextBox',
			'class'=>'fullside',
			'required'=>true,
			'readonly'=>true,
		));
		
		if($data!=null){
			$_id->setValue($data['id']);
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttribs(array(
				'readonly'=>true,
			));
			$full_commission->setValue($data['full_commission']);
			$_totoalCmminssionPaid->setValue($data['totoalCmminssionPaid']);
		}
		$this->addElements(array(
				$_branch_id,
				$full_commission,
				$_id,
				$_totoalCmminssionPaid
				));
		return $this;
		
	}	
}