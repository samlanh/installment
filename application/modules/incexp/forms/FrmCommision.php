<?php 
Class Incexp_Form_FrmCommision extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddFrmCancel($data=null){
		
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
		
		$_property_id = new Zend_Form_Element_Hidden("property_id");
		$_old_sale_id = new Zend_Form_Element_Hidden("id");
		
		$_price_sold = new Zend_Dojo_Form_Element_NumberTextBox('price_sold');
		$_price_sold->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
		));	
		$schedule_opt = new Zend_Dojo_Form_Element_FilteringSelect('schedule_opt');
		$schedule_opt->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'readonly'=>true,
		));
		$opt = $db->getVewOptoinTypeByType(25,1,null,1);
		$schedule_opt->setMultiOptions($opt);
		
		$installment_paid = new Zend_Dojo_Form_Element_TextBox('installment_paid');
		$installment_paid->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
		));
		
		$paid_amount = new Zend_Dojo_Form_Element_NumberTextBox('paid_amount');
		$paid_amount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>true,
		));
		
		
		$_balance = new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$_balance->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
		));
		$_commision = new Zend_Dojo_Form_Element_NumberTextBox('commision');
		$_commision->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readonly'=>true,
		));
		
		$full_commission = new Zend_Dojo_Form_Element_NumberTextBox('full_commission');
		$full_commission->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'readOnly'=>true
		));
		$full_commission->setValue(0);
		
		$return_back = new Zend_Dojo_Form_Element_NumberTextBox('return_back');// amount money return when cancel
		$return_back->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'onkeyup'=>'checkreturnAmount();'
		));
		$return_back->setValue(0);
		
		$date = new Zend_Dojo_Form_Element_TextBox("date");
		$date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'required' =>'true'));
		if (DISABLE_PAYMENT_DATE==1){
			$date->setAttribs(array(
					'readonly'=>true,
			));
		}
		$date->setValue(date("Y-m-d"));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
				'class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DEACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$_discount = new Zend_Dojo_Form_Element_NumberTextBox('discount');
		$_discount->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required'=>'true',
				'readonly'=>true,
				'class'=>'fullside',
		));
		$_discount->setValue(0);
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		
		$branch_id_search = new Zend_Dojo_Form_Element_FilteringSelect('branch_id_search');
		$branch_id_search->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
		));
		$options_branch_search=array("-1"=>$this->tr->translate("SELECT_PROJECT"));
		$rows_branch_saerch = $db->getAllBranchName();
		if(!empty($rows_branch))foreach($rows_branch_saerch AS $row){
			$options_branch_search[$row['br_id']]=$row['project_name'];
		}
		$branch_id_search->setMultiOptions($options_branch_search);
		$branch_id_search->setValue($request->getParam("branch_id_search"));
		
		$staff_id = new Zend_Dojo_Form_Element_FilteringSelect('staff_id');
		$staff_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>'false',
				'queryExpr'=>'*${0}*',
		));
		$staff_opt = $db->getAllCOName(1);
		$staff_id->setMultiOptions($staff_opt);
		$staff_id->setValue($request->getParam("staff_id"));
		
		$start_date_search = new Zend_Dojo_Form_Element_DateTextBox('from_date_search');
		$start_date_search->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'placeholder'=>$this->tr->translate('START_DATE'),
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				));
		
		$_date = date("Y-m-d");
		$to_date_search = $request->getParam("to_date_search");
		if(!empty($to_date_search)){
			$start_date_search->setValue($request->getParam("from_date_search"));
		}
		
		$to_date_search = new Zend_Dojo_Form_Element_DateTextBox('to_date_search');
		$to_date_search->setAttribs(array(
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'dojoType'=>'dijit.form.DateTextBox','class'=>'fullside',
		));
		$to_date_search->setValue($_date);
		$todatesearch = $request->getParam("to_date_search");
		if(!empty($todatesearch))$to_date_search->setValue($request->getParam("to_date_search"));
		
		
		$payment_type = new Zend_Dojo_Form_Element_FilteringSelect('payment_type');
		$payment_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'enablePayment();'
		));
		$options= array(
				1=>$this->tr->translate("CASH"),
				2=>$this->tr->translate("CHEQUE"),
				3=>$this->tr->translate("PAYWITH_BANK"));
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
		
		if($data!=null){
			$branch_id->setValue($data['branch_id']);
			$branch_id->setAttrib('readonly',true);
			$_old_sale_id->setValue($data['id']);
			$return_back->setValue($data['total_amount']);
			$_status->setValue($data['status']);
			
			$payment_type->setValue($data['payment_id']);
			$_cheque->setValue($data['cheque']);
			$cheque_issuer->setValue($data['cheque_issuer']);
			$date->setValue($data['for_date']);
		}
		$this->addElements(array(
		$branch_id,$_property_id,$_old_sale_id,$_price_sold,$schedule_opt,$installment_paid,$paid_amount,$_balance,$_commision,$full_commission,$return_back,$date,$_status,
		$_discount,
		$_title,$branch_id_search,$staff_id,$start_date_search,$to_date_search,
				$payment_type,
				$_cheque,
				$cheque_issuer
				
				));
		return $this;
		
	}	
}