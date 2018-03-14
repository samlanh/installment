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
		$_smsWarnning = new Zend_Dojo_Form_Element_TextBox('smsWarnningKH');
		$_smsWarnning->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("SMS Warnning KH")
		));
		
		$_reciept_kh = new Zend_Dojo_Form_Element_TextBox('reciept_kh');
		$_reciept_kh->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Reciept KH")
		));
		$_exchange_ratetitle = new Zend_Dojo_Form_Element_NumberTextBox('exchange_ratetitle');
		$_exchange_ratetitle->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				//'required'=>true,
		));
		$_exchange_reciept = new Zend_Dojo_Form_Element_NumberTextBox('exchange_reciept');
		$_exchange_reciept->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				//'required'=>true,
		));
		
		$_comment = new Zend_Dojo_Form_Element_Textarea("comment");
		$_comment->setAttribs(array(
				'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'width:99%;min-height:60px; font-size:inherit; font-family:inherit'
		));
		
		$_brand_client = new Zend_Dojo_Form_Element_TextBox('brand_client');
		$_brand_client->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Client")
		));
		$_brand_holiday = new Zend_Dojo_Form_Element_TextBox('brand_holiday');
		$_brand_holiday->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Holiday")
		));
		
		$_brand_call = new Zend_Dojo_Form_Element_TextBox('brand_call');
		$_brand_call->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Branch Call")
		));
		
		$_transferTitleKH = new Zend_Dojo_Form_Element_TextBox('rptTransferTitleKh');
		$_transferTitleKH->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("Transfer Title KH")
		));
		
		$_branchAddClient = new Zend_Dojo_Form_Element_TextBox('branchAddClient');
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
		if($data!=null){
			$_label_animation->setValue($data['label_animation']['keyValue']);
			$_smsWarnning->setValue($data['sms-warnning-kh']['keyValue']);
			$_reciept_kh->setValue($data['reciept_kh']['keyValue']);
			$_exchange_ratetitle->setValue($data['exchange_ratetitle']['keyValue']);
			$_exchange_reciept->setValue($data['exchange_reciept']['keyValue']);
			$_comment->setValue($data['comment']['keyValue']);
			$_brand_client->setValue($data['brand_client']['keyValue']);
			$_brand_holiday->setValue($data['brand_holiday']['keyValue']);
			$_brand_call->setValue($data['brand_call']['keyValue']);
			$_transferTitleKH->setValue($data['rpt-transfer-title-kh']['keyValue']);
			
			$_branchAddClient->setValue($data['branch-add-client']['keyValue']);
			$_telClient->setValue($data['tel-client']['keyValue']);
			$_client_website->setValue($data['client_website']['keyValue']);
			$_email_client->setValue($data['email_client']['keyValue']);
			
			$_branch_email->setValue($data['branch_email']['keyValue']);
			$_branch_add->setValue($data['branch_add']['keyValue']);
			$_branchTel->setValue($data['branch-tel']['keyValue']);
			$_power_by->setValue($data['power_by']['keyValue']);
		}
		$this->addElements(array(
				$_label_animation,
				$_smsWarnning,
				$_reciept_kh,
				$_exchange_ratetitle,
				$_exchange_reciept,
				$_comment,
				$_brand_client,
				$_brand_holiday,
				$_brand_call,
				$_transferTitleKH,
				$_branchAddClient,
				$_telClient,
				$_client_website,
				$_email_client,
				$_branch_email,
				$_branch_add,
				$_branchTel,
				$_power_by,
				));
		
		return $this;
		
	}
	
}