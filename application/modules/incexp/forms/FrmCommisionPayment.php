<?php 
Class Incexp_Form_FrmCommisionPayment extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddFrmCommisionPayment($data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'getSaleNo(), getSaleClie();'
		));
		$rows_branch = $db->getAllBranchName();
		if(!empty($rows_branch))foreach($rows_branch AS $row){
			$options_branch[$row['br_id']]=$row['project_name'];
		}
		$branch_id->setMultiOptions($options_branch);
		
		
		$all_balance = new Zend_Dojo_Form_Element_NumberTextBox('all_balance');
		$all_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("BALANCE"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		$all_balance->setValue(0);
		
		$balance= new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("BALANCE"),
				'missingMessage'=>$this->tr->translate("Forget Enter Balance")
		));
		$balance->setValue(0);
		
		$total_paid = new Zend_Dojo_Form_Element_NumberTextBox('total_paid');
		$total_paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'placeholder'=>$this->tr->translate("TOTAL_PAID"),
				'missingMessage'=>$this->tr->translate("Forget Enter Total Paid")
		));
		$total_paid->setValue(0);
		
		$total_due = new Zend_Dojo_Form_Element_NumberTextBox('total_due');
		$total_due->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>'readonly',
		));
		$total_due->setValue(0);
		
		$amount = new Zend_Dojo_Form_Element_NumberTextBox('amount');
		$amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onKeyup'=>'checkAmout()',
				'placeholder'=>$this->tr->translate("AMOUNT"),
				'missingMessage'=>$this->tr->translate("Forget Enter Amount")
		));
		$amount->setValue(0);
		
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
		$date = new Zend_Dojo_Form_Element_TextBox("date");
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'constraints'=>"{".$constraintsDate."datePattern:'dd/MM/yyyy'}",
				'readOnly' =>$paymentDateEnable,
			));
		$date->setValue(date("Y-m-d"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',));
		$_status_opt=array();
		if($request->getActionName()=='index'){
			$_status_opt[-1]=$this->tr->translate("SELECT_TYPE");
		}
		$_status_opt[1]=$this->tr->translate("ACTIVE");
		$_status_opt[0]=$this->tr->translate("DEACTIVE");
		
		$_status->setMultiOptions($_status_opt);
		$_status->setValue($request->getParam("status"));
		
		$staff_id = new Zend_Dojo_Form_Element_FilteringSelect('staff_id');
		$staff_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$rowss = $db->getAllCOName();
		$staff_opt=array(''=>$this->tr->translate("SELECT_SALE_AGENT"));
		if(!empty($rowss))foreach($rowss AS $row){
			$staff_opt[$row['id']]=$row['name'];
		}
		$staff_id->setMultiOptions($staff_opt);
		$staff_id->setValue($request->getParam("staff_id"));
		
		
		
		
		$payment_type = new Zend_Dojo_Form_Element_FilteringSelect('payment_type');
		$payment_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'enablePayment();'
		));
		
		$options = $db->getVewOptoinTypeByType(2,1,3,1);
		$payment_type->setMultiOptions($options);
		
		$_cheque = new Zend_Dojo_Form_Element_TextBox('cheque');
		$_cheque ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$cheque_issuer = new Zend_Dojo_Form_Element_FilteringSelect('cheque_issuer');
		$cheque_issuer->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'onchange'=>'popupIssuer();'
		));
		$dbe = new Incexp_Model_DbTable_DbComission();
		$rscheque = $dbe->getAllChequeIssue();
		$opt1=array(''=>$this->tr->translate("SELECT_CHEQUE_ISSUE"),'-1'=>$this->tr->translate("ADD_NEW"));
		if(!empty($rscheque))foreach($rscheque AS $row){
			$opt1[$row['id']]=$row['name'];
		}
		$cheque_issuer->setMultiOptions($opt1);
		
		
		$_id = new Zend_Form_Element_Hidden("id");
		$_id ->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			$branch_id->setAttrib('readonly',true);
			$_id->setValue($data['id']);
			$_status->setValue($data['status']);
			
			$payment_type->setValue($data['payment_method']);
			$_cheque->setValue($data['cheque_no']);
			$cheque_issuer->setValue($data['cheque_issuer']);
			$date->setValue($data['date_payment']);
			
			$balance->setValue($data['balance']);
			$total_due->setValue($data['total_due']);
			$total_paid->setValue($data['total_paid']);
			$amount->setValue($data['total_paid']);
		}
		$this->addElements(array(
		$branch_id,
				$_id,$date,$_status,
				$staff_id,
				$payment_type,
				$_cheque,
				$cheque_issuer,
				
				$all_balance,
				$balance,
				$total_due,
				$total_paid,
				$amount

				));
		return $this;
		
	}	
}