<?php
class Stockinout_Form_FrmTransfer extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate; //text validate
	protected $filter;
	protected $text;
	protected $tarea = null;
	protected $t_num = null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.Textarea';
	}
	public function FrmTransfer($_data = null)
	{

		$db = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock();
		$request = Zend_Controller_Front::getInstance()->getRequest();

		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'placeholder' => $this->tr->translate("SELECT_BRANCH"),
				'class' => 'fullside',
				'required' => 'true',
				'onchange' => 'getDataByBranch();'
			)
		);

		$rows = $db->getAllBranchName();
		$options = array('' => $this->tr->translate("SELECT_BRANCH"));
		if (!empty($rows))
			foreach ($rows as $row) {
				$options[$row['br_id']] = $row['project_name'];
			}
		$_branch_id->setMultiOptions($options);

		if (count($rows) == 1) {
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			if (!empty($rows))
				foreach ($rows as $row) {
					$_branch_id->setValue($row['br_id']);
				}
		}

		$toProjectId = new Zend_Dojo_Form_Element_FilteringSelect('toProjectId');
		$toProjectId->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'placeholder' => $this->tr->translate("SELECT_BRANCH"),
				'class' => 'fullside',
				'required' => 'true',
			)
		);
		$arrCondiction = array(
			'showAll' => 1
		);
		$rowsAllBranch = $db->getAllBranchName(null, null, $arrCondiction);
		$optionsAllBranch = array('' => $this->tr->translate("SELECT_BRANCH"));
		if (!empty($rowsAllBranch))
			foreach ($rowsAllBranch as $row) {
				$optionsAllBranch[$row['br_id']] = $row['project_name'];
			}
		$toProjectId->setMultiOptions($optionsAllBranch);

		$categoryId = new Zend_Dojo_Form_Element_FilteringSelect('categoryId');
		$categoryId->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'placeholder' => $this->tr->translate("SELECT_CATEGORY"),
				'class' => 'fullside',
				'onchange' => 'getAllProduct();'
			)
		);

		$rsCate = $dbGBStock->getAllCategoryProduct(0, '', '', 1);
		unset($rsCate['-1']);
		$categoryId->setMultiOptions($rsCate);

		$requestNo = new Zend_Dojo_Form_Element_TextBox('requestNo');
		$requestNo->setAttribs(
			array(
				'dojoType' => $this->tvalidate,
				'placeholder' => $this->tr->translate("TRANFER_NO"),
				'required' => 'true',
				'class' => 'fullside',
				'readonly' => true
			)
		);

		$transferDate = new Zend_Dojo_Form_Element_TextBox('transferDate');
		$transferDate->setAttribs(
			array(
				'dojoType' => 'dijit.form.DateTextBox',
				'constraints' => "{datePattern:'dd/MM/yyyy'}",
				'placeholder' => $this->tr->translate("TRANSFER_STOCK_DATE"),
				'readOnly' => true,
				'class' => 'fullside'
			)
		);
		$transferDate->setValue(date("Y-m-d"));



		$driver = new Zend_Dojo_Form_Element_TextBox('driver');
		$driver->setAttribs(
			array(
				'dojoType' => $this->tvalidate,
				'placeholder' => $this->tr->translate("DRIVER"),
				'required' => 'true',
				'class' => 'fullside',
			)
		);

		$transferer = new Zend_Dojo_Form_Element_TextBox('transferer');
		$transferer->setAttribs(
			array(
				'dojoType' => $this->tvalidate,
				'placeholder' => $this->tr->translate("DISTRIBUTOR"),
				'required' => 'true',
				'class' => 'fullside',
			)
		);

		$receiver = new Zend_Dojo_Form_Element_TextBox('receiver');
		$receiver->setAttribs(
			array(
				'dojoType' => $this->tvalidate,
				'placeholder' => $this->tr->translate("RECEIVER"),
				'required' => 'true',
				'class' => 'fullside',
			)
		);


		$useFor = new Zend_Dojo_Form_Element_TextBox('useFor');
		$useFor->setAttribs(
			array(
				'dojoType' => $this->text,
				'placeholder' => $this->tr->translate("USAGE_FOR"),
				'class' => 'fullside',
			)
		);

		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(
			array(
				'dojoType' => $this->filter,
				'placeholder' => $this->tr->translate("STATUS"),
				'class' => 'fullside',
			)
		);
		$_status_opt = array(
			1 => $this->tr->translate("ACTIVE"),
			0 => $this->tr->translate("DEACTIVE")
		);
		$_status->setMultiOptions($_status_opt);


		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(
			array(
				'dojoType' => $this->tarea,
				'class' => 'fullside',
				'style' => 'height:200px !important;'
			)
		);

		$id = new Zend_Form_Element_Hidden('id');

		if (!empty($_data)) {


			$useFor->setValue($_data['userFor']);
			$receiver->setValue($_data['receiverId']);
			$transferer->setValue($_data['transferer']);
			$driver->setValue($_data['driver']);
			$transferDate->setValue($_data['transferDate']);
			$_branch_id->setValue($_data['fromProjectId']);
			$toProjectId->setValue($_data['toProjectId']);
			$requestNo->setValue($_data['transferNo']);
			$_status->setValue($_data['status']);
			$id->setValue($_data['id']);
			$_note->setValue($_data['note']);
		}
		$this->addElements(
			array(
				$categoryId
				,
				$useFor
				,
				$receiver
				,
				$transferer
				,
				$driver
				,
				$transferDate
				,
				$_branch_id
				,
				$toProjectId
				,
				$requestNo
				,
				$_status
				,
				$id
				,
				$_note
			)
		);

		return $this;
	}


	public function FrmTransferReceive($_data = null)
	{

		$db = new Application_Model_DbTable_DbGlobal();
		$dbGBStock = new Application_Model_DbTable_DbGlobalStock();
		$request = Zend_Controller_Front::getInstance()->getRequest();

		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'class' => 'fullside',
				'required' => 'true',
				'onchange' => 'getDataByBranch();'
			)
		);

		$rows = $db->getAllBranchName();
		$options = array('' => $this->tr->translate("SELECT_BRANCH"));
		if (!empty($rows))
			foreach ($rows as $row) {
				$options[$row['br_id']] = $row['project_name'];
			}
		$_branch_id->setMultiOptions($options);

		if (count($rows) == 1) {
			$_branch_id->setAttribs(array('readonly' => 'readonly'));
			if (!empty($rows))
				foreach ($rows as $row) {
					$_branch_id->setValue($row['br_id']);
				}
		}



		$fromProjectId = new Zend_Dojo_Form_Element_FilteringSelect('fromProjectId');
		$fromProjectId->setAttribs(
			array(
				'dojoType' => 'dijit.form.FilteringSelect',
				'class' => 'fullside',
				'required' => 'true',
				'onchange' => 'getDataByFromBranch();'
			)
		);
		$arrCondiction = array(
			'showAll' => 1
		);
		$rowsAllBranch = $db->getAllBranchName(null, null, $arrCondiction);
		$optionsAllBranch = array('' => $this->tr->translate("SELECT_BRANCH"));
		if (!empty($rowsAllBranch))
			foreach ($rowsAllBranch as $row) {
				$optionsAllBranch[$row['br_id']] = $row['project_name'];
			}
		$fromProjectId->setMultiOptions($optionsAllBranch);


		$receiveNo = new Zend_Dojo_Form_Element_TextBox('receiveNo');
		$receiveNo->setAttribs(
			array(
				'dojoType' => $this->tvalidate,
				'required' => 'true',
				'class' => 'fullside',
				'readonly' => true
			)
		);

		$receiveDate = new Zend_Dojo_Form_Element_TextBox('receiveDate');
		$receiveDate->setAttribs(
			array(
				'dojoType' => 'dijit.form.DateTextBox',
				'constraints' => "{datePattern:'dd/MM/yyyy'}",
				'readOnly' => true,
				'class' => 'fullside'
			)
		);
		$receiveDate->setValue(date("Y-m-d"));

		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType' => $this->filter, 'class' => 'fullside', ));
		$_status_opt = array(
			1 => $this->tr->translate("ACTIVE"),
			0 => $this->tr->translate("DEACTIVE")
		);
		$_status->setMultiOptions($_status_opt);


		$_note = new Zend_Dojo_Form_Element_TextBox('note');
		$_note->setAttribs(
			array(
				'dojoType' => $this->tarea,
				'class' => 'fullside',
				'style' => 'height:200px !important;'
			)
		);

		$id = new Zend_Form_Element_Hidden('id');

		if (!empty($_data)) {
			$_branch_id->setValue($_data['projectId']);
			$fromProjectId->setValue($_data['fromProjectId']);
			$receiveNo->setValue($_data['receiveNo']);
			$receiveDate->setValue($_data['receiveDate']);

			$id->setValue($_data['id']);
			$_status->setValue($_data['status']);
			$_note->setValue($_data['note']);
		}
		$this->addElements(
			array(
				$_branch_id
				,
				$fromProjectId
				,
				$receiveNo
				,
				$receiveDate
				,
				$id
				,
				$_status
				,
				$_note

			)
		);

		return $this;
	}
}