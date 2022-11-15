<?php 
Class Incexp_Form_FrmOtherIncomePayment extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	public function FrmAddIncomeother($data=null){
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock(); 


		$_bankId = new Zend_Dojo_Form_Element_FilteringSelect('bank_id');
		$_bankId->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required'=>'false',
		));
		$rsBank = $dbGBStock->getAllBank();
		$optBank=array(''=>$this->tr->translate("SELECT_BANK"));
		if(!empty($rsBank))foreach($rsBank AS $row){
			$optBank[$row['id']]=$row['name'];
		}
		$_bankId->setMultiOptions($optBank);
	
		$title = new Zend_Dojo_Form_Element_TextBox('title');
		$title->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required'=>false,
		));
		
		$cdate=date("Y-m-d");
		if (!empty($data['for_date'])){
			$cdate=date("Y-m-d",strtotime($data['for_date']));
		}
		$paymentDateEnable="false";
		$constraintsDate="";
		if (DISABLE_PAYMENT_DATE==1){
			$paymentDateEnable = "true";
		}else if (DISABLE_PAYMENT_DATE==2){
			$constraintsDate="min:'$cdate',";
		}else if (DISABLE_PAYMENT_DATE==3){
			$constraintsDate="max:'$cdate',";
		}else if (DISABLE_PAYMENT_DATE==4){
			$constraintsDate="min:'$cdate',max:'$cdate',";
		}
		$_Date = new Zend_Dojo_Form_Element_DateTextBox('for_date');
		$_Date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required'=>true,
				'class'=>'fullside',
				'constraints'=>"{".$constraintsDate."datePattern:'dd/MM/yyyy'}",
				'readOnly' =>$paymentDateEnable,
		));
		$_Date->setValue(date('Y-m-d'));
	
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'getallCustomer();getBranchinfo();'
		));
	
		$db = new Application_Model_DbTable_DbGlobal();
		$options = $db->getAllBranchName(null,1);
		$_branch_id->setMultiOptions($options);
	
		$_stutus = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_stutus ->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
					
		));
		$options= array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_stutus->setMultiOptions($options);
	
		$_Description = new Zend_Dojo_Form_Element_Textarea('description');
		$_Description ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>"height:70px !important",
	
		));
		
		$payment_method = new Zend_Dojo_Form_Element_FilteringSelect('payment_method');
		$payment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'enablePayment();'
		));
		$opt = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_method->setMultiOptions($opt);
	
		$_cheque = new Zend_Dojo_Form_Element_TextBox('cheque');
		$_cheque ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ACCOUNT_AND_CHEQUE_NO"),
				'style'=>'color:red;font-weight: 600;',
		));
		
		$balance=new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'readonly'=>'readonly',
		));
		
		$total_amount=new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$total_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'onKeyup'=>'totalRemain();',
		));
	
		$remain=new Zend_Dojo_Form_Element_NumberTextBox('remain');
		$remain->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>'true',
				'readonly'=>'readonly',
		));
		
		$invoice=new Zend_Dojo_Form_Element_TextBox('invoice');
		$invoice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readOnly'=>'true',
				'style'=>'color:red',
		));
	
		$id = new Zend_Form_Element_Hidden("id");
		
		$_cate_type = new Zend_Dojo_Form_Element_FilteringSelect("cate_type");
		$_cate_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'onChange'=>'getCategoryByType();',
				'class'=>'fullside'));
		$opt= $db->getAllViewType(1,1);
		$_cate_type->setMultiOptions($opt);
	
		if($data!=null){
			$title->setValue($data['title_income']);
			$_branch_id->setValue($data['branch_id']);
			$_branch_id->setAttrib('readonly', true);
			$invoice->setValue($data['receipt_no']);
			$_Date->setValue($data['for_date']);
			$_cate_type->setValue($data['cate_type']);
			$payment_method->setValue($data['payment_method']);
			$_bankId->setValue($data['bank_id']);
			$_cheque->setValue($data['cheque']);
			$balance->setValue($data['balance']);
			$total_amount->setValue($data['total_paid']);
			$remain->setValue($data['remain']);
			$_stutus->setValue($data['status']);
			$_Description->setValue($data['note']);
			$id->setValue($data['id']);
			$_cate_type->setAttribs(array(
					'readOnly'=>'true',
			));
		}
		$this->addElements(array(
				$_bankId,
				$_cate_type,
				$payment_method,
				$_cheque,
				$invoice,
				$balance,
				$remain,
				$_Date,
				$_stutus,
				$_Description,
				$title,
				$total_amount,$_branch_id,$id));
		return $this;
	
	}	
}