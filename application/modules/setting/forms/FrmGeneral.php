<?php 
Class Setting_Form_FrmGeneral extends Zend_Dojo_Form {
	protected $tr;
	protected $tvalidate ;//text validate
	protected $filter;
	protected $t_date;
	protected $t_num;
	protected $text;
	protected $check;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->t_date = 'dijit.form.DateTextBox';
		$this->t_num = 'dijit.form.NumberTextBox';
		$this->text = 'dijit.form.TextBox';
		$this->check='dijit.form.CheckBox';
	}
	public function FrmGeneral($data=null){
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_label_animation = new Zend_Dojo_Form_Element_TextBox('label_animation');
		$_label_animation->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("label_animation")
		));
// 		$_smsWarnning = new Zend_Dojo_Form_Element_TextBox('smsWarnningKH');
// 		$_smsWarnning->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				'placeholder'=>$this->tr->translate("SMS Warnning KH")
// 		));
		
// 		$_reciept_kh = new Zend_Dojo_Form_Element_TextBox('reciept_kh');
// 		$_reciept_kh->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				'placeholder'=>$this->tr->translate("Reciept KH")
// 		));
// 		$_exchange_ratetitle = new Zend_Dojo_Form_Element_NumberTextBox('exchange_ratetitle');
// 		$_exchange_ratetitle->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				//'required'=>true,
// 		));
// 		$_exchange_reciept = new Zend_Dojo_Form_Element_NumberTextBox('exchange_reciept');
// 		$_exchange_reciept->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				//'required'=>true,
// 		));
		
		$_comment = new Zend_Dojo_Form_Element_Textarea("comment");
		$_comment->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%;min-height:60px; font-size:inherit; font-family:inherit; overflow-x: hidden;'
		));
		
		$_comment1 = new Zend_Dojo_Form_Element_Textarea("comment1");
		$_comment1->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%;min-height:60px; font-size:inherit; font-family:inherit; overflow-x: hidden;'
		));
		
// 		$_brand_client = new Zend_Dojo_Form_Element_TextBox('brand_client');
// 		$_brand_client->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				'placeholder'=>$this->tr->translate("Branch Client")
// 		));
// 		$_brand_holiday = new Zend_Dojo_Form_Element_TextBox('brand_holiday');
// 		$_brand_holiday->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				'placeholder'=>$this->tr->translate("Branch Holiday")
// 		));
		
// 		$_brand_call = new Zend_Dojo_Form_Element_TextBox('brand_call');
// 		$_brand_call->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				'placeholder'=>$this->tr->translate("Branch Call")
// 		));
		
// 		$_transferTitleKH = new Zend_Dojo_Form_Element_TextBox('rptTransferTitleKh');
// 		$_transferTitleKH->setAttribs(array(
// 				'dojoType'=>'dijit.form.TextBox',
// 				'class'=>'fullside',
// 				'placeholder'=>$this->tr->translate("Transfer Title KH")
// 		));
		
		$_branchAddClient = new Zend_Dojo_Form_Element_TextBox('footer_branch');
		$_branchAddClient->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Address Client")
		));
		$_telClient = new Zend_Dojo_Form_Element_TextBox('telClient');
		$_telClient->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Tel Client")
		));
		$_client_website = new Zend_Dojo_Form_Element_TextBox('client_website');
		$_client_website->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Client Website")
		));
		$_email_client = new Zend_Dojo_Form_Element_TextBox('email_client');
		$_email_client->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Email Client")
		));
		
		
		$_branchTel = new Zend_Dojo_Form_Element_TextBox('branchTel');
		$_branchTel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Tel")
		));
		$_power_by = new Zend_Dojo_Form_Element_TextBox('power_by');
		$_power_by->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Power By")
		));
		$_branch_email = new Zend_Dojo_Form_Element_TextBox('branch_email');
		$_branch_email->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Email")
		));
		$_branch_add = new Zend_Dojo_Form_Element_TextBox('branch_add');
		$_branch_add->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Address")
		));
		$_website = new Zend_Dojo_Form_Element_TextBox('website');
		$_website->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Website")
		));
		
		$_customer_sign = new Zend_Dojo_Form_Element_TextBox('customer_sign');
		$_customer_sign->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Customer Sign")
		));
		
		$_teller_sign = new Zend_Dojo_Form_Element_TextBox('teller_sign');
		$_teller_sign->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Teller Sign")
		));
		
		$_bank_info = new Zend_Dojo_Form_Element_TextBox('bank_info');
		$_bank_info->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Information")
		));
		
		$_show_propertyprice = new Zend_Dojo_Form_Element_TextBox('show_propertyprice');
		$_show_propertyprice->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Show Property Price")
		));
		
		$_bank_account1 = new Zend_Dojo_Form_Element_TextBox('bank_account1');
		$_bank_account1->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Account")." 1"
		));
		$_bank_account1number = new Zend_Dojo_Form_Element_TextBox('bank_account1number');
		$_bank_account1number->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Account Number")." 1"
		));
		$_bank_account2 = new Zend_Dojo_Form_Element_TextBox('bank_account2');
		$_bank_account2->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Account")." 2"
		));
		$_bank_account2number = new Zend_Dojo_Form_Element_TextBox('bank_account2number');
		$_bank_account2number->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Bank Account Number")." 2"
		));
		$_cheque_receiver = new Zend_Dojo_Form_Element_TextBox('cheque_receiver');
		$_cheque_receiver->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Cheque Receiver")
		));
		$_showhouseinfo = new Zend_Dojo_Form_Element_TextBox('showhouseinfo');
		$_showhouseinfo->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Show House Info")
		));
		if($data!=null){
			$_label_animation->setValue($data['label_animation']['keyValue']);
// 			$_smsWarnning->setValue($data['sms-warnning-kh']['keyValue']);
// 			$_reciept_kh->setValue($data['reciept_kh']['keyValue']);
// 			$_exchange_ratetitle->setValue($data['exchange_ratetitle']['keyValue']);
// 			$_exchange_reciept->setValue($data['exchange_reciept']['keyValue']);
			$_comment->setValue($data['comment']['keyValue']);
			$_comment1->setValue($data['comment1']['keyValue']);
			$_website->setValue($data['website']['keyValue']);
// 			$_brand_client->setValue($data['brand_client']['keyValue']);
// 			$_brand_holiday->setValue($data['brand_holiday']['keyValue']);
// 			$_brand_call->setValue($data['brand_call']['keyValue']);
// 			$_transferTitleKH->setValue($data['rpt-transfer-title-kh']['keyValue']);
			
			$_branchAddClient->setValue($data['footer_branch']['keyValue']);
			$_telClient->setValue($data['tel-client']['keyValue']);
			$_client_website->setValue($data['client_website']['keyValue']);
			$_email_client->setValue($data['email_client']['keyValue']);
			
			$_branch_email->setValue($data['branch_email']['keyValue']);
			$_branch_add->setValue($data['branch_add']['keyValue']);
			$_branchTel->setValue($data['branch-tel']['keyValue']);
			$_power_by->setValue($data['power_by']['keyValue']);
			$_customer_sign->setValue($data['customer_sign']['keyValue']);
			$_teller_sign->setValue($data['teller_sign']['keyValue']);
			$_bank_info->setValue($data['bank_info']['keyValue']);
			
			$_show_propertyprice->setValue($data['show_propertyprice']['keyValue']);
			$_bank_account1->setValue($data['bank_account1']['keyValue']);
			$_bank_account1number->setValue($data['bank_account1number']['keyValue']);
			$_bank_account2->setValue($data['bank_account2']['keyValue']);
			$_bank_account2number->setValue($data['bank_account2number']['keyValue']);
			$_cheque_receiver->setValue($data['cheque_receiver']['keyValue']);
			$_showhouseinfo->setValue($data['showhouseinfo']['keyValue']);
		}
		$this->addElements(array(
				$_label_animation,
// 				$_smsWarnning,
// 				$_reciept_kh,
// 				$_exchange_ratetitle,
// 				$_exchange_reciept,
				$_comment,
				$_comment1,
// 				$_brand_client,
// 				$_brand_holiday,
// 				$_brand_call,
// 				$_transferTitleKH,
				$_branchAddClient,
				$_telClient,
				$_client_website,
				$_email_client,
				$_branch_email,
				$_branch_add,
				$_branchTel,
				$_power_by,
				$_website,
				$_customer_sign,
				$_teller_sign,
				$_bank_info,
				$_show_propertyprice,
				$_bank_account1,
				$_bank_account1number,
				$_bank_account2,
				$_bank_account2number,
				$_cheque_receiver,
				$_showhouseinfo,
				));
		
		return $this;
		
	}
	
}