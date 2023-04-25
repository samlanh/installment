<?php
class Invpayment_Form_FrmPayment extends Zend_Dojo_Form
{
	// 	public function init()
// 	{
// 	}///
	public function FrmPayment($data = null)
	{

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$filter = 'dijit.form.FilteringSelect';
		$tvalidate = 'dijit.form.ValidationTextBox';
		$textbox = 'dijit.form.TextBox';
		$numbertext = 'dijit.form.NumberTextBox';
		$tarea = 'dijit.form.Textarea';

		$request = Zend_Controller_Front::getInstance()->getRequest();

		$dbGB = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock();

		$userInfo = $dbGB->getUserInfo();
		$userLevel = 0;
		$userLevel = empty($userInfo['level']) ? 0 : $userInfo['level'];

		$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'class' => 'fullside',
				'placeholder' => $tr->translate("SELECT_BRANCH"),
				'required' => 'false',
				'autoComplete' => 'false',
				'queryExpr' => '*${0}*',
				'onchange' => 'onChageFunctionByBranch();'
			)
		);
		$rows = $dbGB->getAllBranchName();
		$options_branch = array('-1' => $tr->translate("SELECT_BRANCH"));
		if (!empty($rows))
			foreach ($rows as $row) {
				$options_branch[$row['br_id']] = $row['project_name'];
			}
		$branch_id->setMultiOptions($options_branch);
		$branch_id->setValue($request->getParam("branch_id"));

		if (count($rows) == 1) {
			$branch_id->setAttribs(array('readonly' => 'readonly'));
			if (!empty($rows))
				foreach ($rows as $row) {
					$branch_id->setValue($row['br_id']);
				}
		}

		$paymentNo = new Zend_Dojo_Form_Element_TextBox('paymentNo');
		$paymentNo->setAttribs(
			array(
				'dojoType' => 'dijit.form.ValidationTextBox',
				'required' => 'true',
				'class' => 'fullside ',
				'readOnly' => 'readOnly ',
				'placeholder' => $tr->translate("INVOICE_NO"),
				'style' => 'color:red;font-weight: 600;',
				'missingMessage' => $tr->translate("Forget Enter Data")
			)
		);

		$supplierId = new Zend_Dojo_Form_Element_FilteringSelect('supplierId');
		$supplierId->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'placeholder' => $tr->translate("SELECT_SUPPLIER"),
				'class' => 'fullside',
			)
		);
		$rsSpp = $dbGBStock->getAllSupplier();
		$optSpp = array('' => $tr->translate("SELECT_SUPPLIER"));
		if (!empty($rsSpp))
			foreach ($rsSpp as $row) {
				$optSpp[$row['id']] = $row['name'];
			}
		$supplierId->setMultiOptions($optSpp);

		$paymentDate = new Zend_Dojo_Form_Element_DateTextBox('paymentDate');
		$paymentDate->setAttribs(
			array(
				'dojoType' => 'dijit.form.DateTextBox',
				'placeholder' => $tr->translate("PAYMENT_DATE"),
				'class' => 'fullside',
				'constraints' => "{datePattern:'dd/MM/yyyy'}"
			)
		);
		if ($userLevel != 1) { // NOt Admin
			$paymentDate->setAttribs(
				array(
					'readOnly' => 'readOnly',
				)
			);
		}
		$paymentDate->setValue(date("Y-m-d"));

		$paymentMethod = new Zend_Dojo_Form_Element_FilteringSelect('paymentMethod');
		$paymentMethod->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'placeholder' => $tr->translate("PAYMENT_METHOD"),
				'class' => 'fullside',
				'onchange' => 'enablePayment();'
			)
		);
		$opt = $dbGB->getVewOptoinTypeByType(2, 1, 3, 1);
		$paymentMethod->setMultiOptions($opt);

		$bankId = new Zend_Dojo_Form_Element_FilteringSelect('bankId');
		$bankId->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'placeholder' => $tr->translate("SELECT_BANK"),
				'class' => 'fullside',
				'required' => 'false',
			)
		);
		$rsBank = $dbGBStock->getAllBank();
		$optBank = array('' => $tr->translate("SELECT_BANK"));
		if (!empty($rsBank))
			foreach ($rsBank as $row) {
				$optBank[$row['id']] = $row['name'];
			}
		$bankId->setMultiOptions($optBank);

		$accNameAndChequeNo = new Zend_Dojo_Form_Element_TextBox('accNameAndChequeNo');
		$accNameAndChequeNo->setAttribs(
			array(
				'dojoType' => 'dijit.form.TextBox',
				'required' => 'false',
				'class' => 'fullside ',
				'readOnly' => 'readOnly ',
				'placeholder' => $tr->translate("ACCOUNT_AND_CHEQUE_NO"),
				'style' => 'color:red;font-weight: 600;',
				'missingMessage' => $tr->translate("Forget Enter Data")
			)
		);

		$note = new Zend_Form_Element_Textarea('note');
		$note->setAttribs(
			array(
				'dojoType' => 'dijit.form.Textarea',
				'class' => 'fullside',
				'style' => 'font-family: inherit;  min-height:100px !important; max-width:99%;'
			)
		);

		$totalAmount = new Zend_Dojo_Form_Element_TextBox('totalAmount');
		$totalAmount->setAttribs(
			array(
				'dojoType' => 'dijit.form.NumberTextBox',
				'required' => 'true',
				'class' => 'fullside ',
				'placeholder' => $tr->translate("TOTAL"),
				'onKeyup' => 'checkAmout()',
				'style' => 'color:red;font-weight: 600;',
				'missingMessage' => $tr->translate("Forget Enter Data")
			)
		);
		$totalAmount->setValue(0);

		$totalPaid = new Zend_Dojo_Form_Element_TextBox('totalPaid');
		$totalPaid->setAttribs(
			array(
				'dojoType' => 'dijit.form.NumberTextBox',
				'required' => 'true',
				'class' => 'fullside ',
				'readOnly' => 'readOnly ',
				'placeholder' => $tr->translate("TOTAL_PAID"),
				'style' => 'color:red;font-weight: 600;',
				'missingMessage' => $tr->translate("Forget Enter Data")
			)
		);
		$totalPaid->setValue(0);

		$totalDue = new Zend_Dojo_Form_Element_TextBox('totalDue');
		$totalDue->setAttribs(
			array(
				'dojoType' => 'dijit.form.NumberTextBox',
				'required' => 'true',
				'class' => 'fullside ',
				'readOnly' => 'readOnly ',
				'placeholder' => $tr->translate("TOTAL_DUE"),
				'style' => 'color:red;font-weight: 600;',
				'missingMessage' => $tr->translate("Forget Enter Data")
			)
		);
		$totalDue->setValue(0);



		$_arr = array(1 => $tr->translate("ACTIVE"), 0 => $tr->translate("VOID"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'placeholder' => $tr->translate("STATUS"),
				'required' => 'true',
				'missingMessage' => 'Invalid Module!',
				'class' => 'fullside height-text',
			)
		);

		$id = new Zend_Form_Element_Hidden('id');
		$id->setAttribs(
			array(
				'dojoType' => 'dijit.form.TextBox',
				'class' => 'fullside ',
			)
		);

		$balance = new Zend_Dojo_Form_Element_NumberTextBox('balance');
		$balance->setAttribs(
			array(
				'dojoType' => 'dijit.form.NumberTextBox',
				'class' => ' fullside height-text',
				'readonly' => 'readonly',
				'placeholder' => $tr->translate("BALANCE"),
				'missingMessage' => $tr->translate("Forget Enter Balance")
			)
		);
		$balance->setValue(0);

		$gTotalBalance = new Zend_Dojo_Form_Element_NumberTextBox('gTotalBalance');
		$gTotalBalance->setAttribs(
			array(
				'dojoType' => 'dijit.form.NumberTextBox',
				'class' => ' fullside height-text',
				'readonly' => 'readonly',
				'placeholder' => $tr->translate("BALANCE"),
				'missingMessage' => $tr->translate("Forget Enter Balance")
			)
		);
		$gTotalBalance->setValue(0);

		$advanceFilter = new Zend_Dojo_Form_Element_TextBox('advanceFilter');
		$advanceFilter->setAttribs(
			array(
				'dojoType' => 'dijit.form.TextBox',
				'class' => 'fullside height-text',
				'placeholder' => $tr->translate("SEARCH"),
				'missingMessage' => $tr->translate("Forget Enter Receipt No")
			)
		);

		$start_date = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$start_date->setAttribs(
			array(
				'dojoType' => "dijit.form.DateTextBox",
				'placeholder' => $tr->translate("START_DATE"),
				'value' => 'now',
				'constraints' => "{datePattern:'dd/MM/yyyy'}",
				'class' => 'fullside',
			)
		);
		$_date = $request->getParam("start_date");
		if (empty($_date)) {
			$_date = date("Y-m-d");
		}
		$start_date->setValue($_date);

		$end_date = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$date = date("Y-m-d");
		$end_date->setAttribs(
			array(
				'dojoType' => "dijit.form.DateTextBox",
				'placeholder' => $tr->translate("END_DATE"),
				'class' => 'fullside',
				'constraints' => "{datePattern:'dd/MM/yyyy'}",
				'required' => false
			)
		);
		$_date = $request->getParam("end_date");
		if (empty($_date)) {
			$_date = date("Y-m-d");
		}
		$end_date->setValue($_date);

		if (!empty($data)) {
			$branch_id->setValue($data['projectId']);
			$paymentNo->setValue($data['paymentNo']);
			$supplierId->setValue($data['supplierId']);
			$paymentDate->setValue($data['paymentDate']);
			$paymentMethod->setValue($data['paymentMethod']);
			$bankId->setValue($data['bankId']);
			$accNameAndChequeNo->setValue($data['accNameAndChequeNo']);
			$note->setValue($data['note']);
			$totalAmount->setValue($data['totalAmount']);
			$_status->setValue($data['status']);
			$id->setValue($data['id']);
			$start_date->setValue("");
		}

		$this->addElements(
			array(
				$branch_id,
				$paymentNo,
				$supplierId,
				$paymentDate,
				$paymentMethod,
				$bankId,
				$accNameAndChequeNo,
				$note,
				$totalAmount,
				$_status,
				$id,

				$totalPaid,
				$totalDue,
				$balance,
				$gTotalBalance,
				$advanceFilter,
				$start_date,
				$end_date
			)
		);
		return $this;
	}
}