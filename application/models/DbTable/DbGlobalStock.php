<?php

class Application_Model_DbTable_DbGlobalStock extends Zend_Db_Table_Abstract
{
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllCategoryProduct($parent = 0, $spacing = '', $cate_tree_array = '', $option = null)
	{

		$db = $this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();

		$sql = "
			SELECT 
				c.id AS id,
				c.categoryName AS `name`
			";
		$sql .= " FROM `st_category` AS c  ";
		$sql .= " WHERE c.status=1 AND c.parentId = $parent ";
		$sql .= " ORDER BY c.id DESC ";
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id = '';
		if ($rowCount > 0) {
			foreach ($query as $row) {
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllCategoryProduct($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		if ($option != null) {
			if (!empty($cate_tree_array)) {
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$optionList = array(
					0 => $tr->translate("SELECT_CATEGORY"),
					-1 => $tr->translate("ADD_NEW")
				);
				foreach ($cate_tree_array as $rs) {
					$optionList[$rs['id']] = $rs['name'];
				}
				return $optionList;
			} else {
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$optionList = array(
					0 => $tr->translate("SELECT_CATEGORY"),
					-1 => $tr->translate("ADD_NEW")
				);
				return $optionList;
			}
		}
		return $cate_tree_array;
	}
	function getAllProduct($_data = null)
	{
		//$dbgb = new Application_Model_DbTable_DbGlobal();
		//$userId = $dbgb->getUserId();
		//$currentLang = $dbgb->currentlang();

		$db = $this->getAdapter();
		$sql = "
			SELECT 
				p.proId AS id,
				CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`
			";
		$sql .= " FROM `st_product` AS p  ";
		$sql .= " WHERE p.status=1 ";

		if (!empty($_data['isService'])) {
			$sql .= " AND p.isService=1 "; //Case Service Items
		} else {
			$sql .= " AND p.isService=0 ";
		}
		if (isset($_data['isCountStock'])) {
			$sql .= " AND p.isCountStock= " . $_data['isCountStock'];
		}

		if (!empty($_data['branch_id'])) {
			$sql .= " AND p.proId IN (SELECT l.proId FROM `st_product_location` AS l  WHERE l.projectId=" . $_data['branch_id'] . " )";
		}
		if (!empty($_data['categoryId'])) {
			$sql .= " AND p.categoryId= " . $_data['categoryId'];
		}
		if (!empty($_data['requestId'])) {
			$sql .= " AND p.proId IN (SELECT rqd.proId FROM `st_request_po_detail` AS rqd  WHERE rqd.requestId=" . $_data['requestId'] . " AND rqd.approvedStatus=1 AND rqd.isCompletedPO=0 GROUP BY rqd.proId )";
			if (!empty($_data['purchaseId'])) { //Case Purchase Edit
				$sql .= " OR p.proId IN (SELECT pod.proId FROM `st_purchasing_detail` AS pod  WHERE pod.purchaseId=" . $_data['purchaseId'] . " GROUP BY pod.proId ) ";
			}
		}
		if (isset($_data['isMaterial'])) {
			$sql .= " AND (SELECT id FROM `st_category` AS ct  WHERE ct.id= p.categoryId AND ct.isMaterial=" . $_data['isMaterial'] . ")";
		}
		if (!empty($_data['notExistingProjectid'])) {
			$sql .= " AND p.proId NOT IN (SELECT l.proId FROM `st_product_location` AS l  WHERE l.projectId=" . $_data['notExistingProjectid'] . " )";
		}
		$row = $db->fetchAll($sql);
		return $row;
	}

	function getProductInfoByLocation($_data = null)
	{

		$db = $this->getAdapter();

		$projectId = 0;
		if (!empty($_data['branch_id'])) {
			$projectId = $_data['branch_id'];
		}
		$sql = "
			SELECT 
				p.proId AS id
				,CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`
				,p.proCode
				,p.proName
				,p.isService
				,p.isCountStock
				,p.budgetId
				,COALESCE((SELECT pl.qty FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= $projectId LIMIT 1),0) AS currentQty
				,COALESCE((SELECT pl.costing FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= $projectId LIMIT 1),0) AS currentPrice
				,p.measureLabel AS measureTitle
				,COALESCE((SELECT pod.unitPrice FROM `st_purchasing_detail` AS pod WHERE pod.proId=p.proId ORDER BY pod.purchaseId DESC LIMIT 1),1) AS latestUnitPrice
			";

		if (!empty($_data['requestId'])) {
			$sql .= "
				,
				(SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=" . $_data['requestId'] . " LIMIT 1 ) AS isCompletedPO,
				(SELECT rqd.dateReqStockIn FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=" . $_data['requestId'] . " LIMIT 1 ) AS dateReqStockIn,
				(SELECT rqd.note FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=" . $_data['requestId'] . " LIMIT 1 ) AS requestItemsNote,
				(SELECT rqd.qtyApproved FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=" . $_data['requestId'] . " LIMIT 1 ) AS qtyApproved,
				
			";
			if (!empty($_data['purchaseId'])) { //Case Purchase Edit
				$sql .= " (COALESCE((SELECT pod.qty FROM `st_purchasing_detail` as pod WHERE p.proId = pod.proId AND pod.purchaseId=" . $_data['purchaseId'] . " LIMIT 1),0)+COALESCE((SELECT rqd.qtyApprovedAfter FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=" . $_data['requestId'] . " LIMIT 1 ),0)) AS qtyApprovedAfter ";
			} else {
				$sql .= " (SELECT rqd.qtyApprovedAfter FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=" . $_data['requestId'] . " LIMIT 1 ) AS qtyApprovedAfter ";
			}
		}

		$sql .= " FROM 
					`st_product` AS p  ";

		if (!empty($projectId)) {
			if (!empty($_data['IsNotLocation'])) {
				$sql .= " Left JOIN st_product_location AS l ON p.proId=l.proId AND l.projectId=" . $projectId;
			} else {
				$sql .= " JOIN st_product_location AS l ON p.proId=l.proId AND l.projectId=" . $projectId;
			}

			$sql .= " WHERE p.status=1 ";
		} else {
			$sql .= " WHERE p.status=1 ";
		}
		if (!empty($_data['categoryId'])) {
			$sql .= " AND p.categoryId= " . $_data['categoryId'];
		}
		if (!empty($_data['productId'])) {
			$sql .= " AND p.proId= " . $_data['productId'];
		}
		$sql .= " ORDER BY p.proId DESC LIMIT	1 ";

		return $db->fetchRow($sql);
	}

	function generateRequestNo($_data = null)
	{

		$this->_name = 'st_request_po';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";

		$branch_id = empty($_data['branch_id']) ? 0 : $_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);

		$db = $this->getAdapter();
		$sql = " SELECT rq.id  FROM $this->_name AS rq WHERE rq.projectId = $branch_id  ORDER BY rq.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;

		$dateRequest = empty($_data['dateRequest']) ? date("Y-m-d") : $_data['dateRequest'];

		$pre = $pre . date("dmy", strtotime($dateRequest));
		$pre = $pre . "R";
		$numberLenght = strlen((int) $new_acc_no);
		for ($i = $numberLenght; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function generatePurchaseNo($_data = null)
	{

		$this->_name = 'st_purchasing';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";

		$branch_id = empty($_data['branch_id']) ? 0 : $_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);

		$db = $this->getAdapter();
		$sql = " SELECT rq.id  FROM $this->_name AS rq WHERE rq.projectId = $branch_id  ORDER BY rq.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;

		$dateRequest = empty($_data['dateRequest']) ? date("y-m-d") : $_data['dateRequest'];

		$pre = $pre . date("dmy", strtotime($dateRequest));
		$pre = $pre . "P";
		$numberLenght = strlen((int) $new_acc_no);
		for ($i = $numberLenght; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function generateInvoiceNo($_data = null)
	{

		$this->_name = 'st_invoice';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";

		$branch_id = empty($_data['branch_id']) ? 0 : $_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);

		$db = $this->getAdapter();
		$sql = " SELECT rq.id  FROM $this->_name AS rq WHERE rq.projectId = $branch_id  ORDER BY rq.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;

		$receiveIvDate = empty($_data['receiveIvDate']) ? date("y-m-d") : $_data['receiveIvDate'];

		$pre = $pre . date("dmy", strtotime($receiveIvDate));
		$pre = $pre . "INV";
		$numberLenght = strlen((int) $new_acc_no);
		for ($i = $numberLenght; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function generatePaymentNo($_data = null)
	{

		$this->_name = 'st_payment';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";

		$branch_id = empty($_data['branch_id']) ? 0 : $_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);

		$db = $this->getAdapter();
		$sql = " SELECT rq.id  FROM $this->_name AS rq WHERE rq.projectId = $branch_id  ORDER BY rq.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;

		$paymentDate = empty($_data['paymentDate']) ? date("y-m-d") : $_data['paymentDate'];

		$pre = $pre . date("dmy", strtotime($paymentDate));
		$pre = $pre . "PM";
		$numberLenght = strlen((int) $new_acc_no);
		for ($i = $numberLenght; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function generateRequestUsageNo($_data = null)
	{

		$this->_name = 'st_stockout';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";

		$branch_id = empty($_data['branch_id']) ? 0 : $_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);
		$strTranType = '';
		$strTypePrefix = 'U';
		if (!empty($_data['tranType'])) {
			if ($_data['tranType'] == 2) {
				$strTypePrefix = 'S';
			}
			$strTranType = ' AND tranType=' . $_data['tranType'];
		}

		$db = $this->getAdapter();
		$sql = " SELECT so.id  FROM $this->_name AS so WHERE so.projectId = $branch_id  $strTranType ORDER BY so.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;

		$dateRequest = empty($_data['createDate']) ? date("Y-m-d") : $_data['createDate'];

		$pre = $pre . date("dmy", strtotime($dateRequest));
		$pre = $pre . $strTypePrefix;
		$numberLenght = strlen((int) $new_acc_no);
		for ($i = $numberLenght; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function generateTransferNo($_data = null)
	{

		$this->_name = 'st_transferstock';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";

		$branch_id = empty($_data['branch_id']) ? 0 : $_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);

		$db = $this->getAdapter();
		$sql = " SELECT t.id  FROM $this->_name AS t WHERE t.fromProjectId = $branch_id  ORDER BY t.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;

		$dateRequest = empty($_data['createDate']) ? date("Y-m-d") : $_data['createDate'];

		$pre = $pre . date("dmy", strtotime($dateRequest));
		$pre = $pre . "T";
		$numberLenght = strlen((int) $new_acc_no);
		for ($i = $numberLenght; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function generateReceiveTransferNo($_data = null)
	{

		$this->_name = 'st_transfer_receive';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";

		$branch_id = empty($_data['branch_id']) ? 0 : $_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);

		$db = $this->getAdapter();
		$sql = " SELECT t.id  FROM $this->_name AS t WHERE t.projectId = $branch_id  ORDER BY t.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no = (int) $acc_no + 1;

		$dateRequest = empty($_data['createDate']) ? date("Y-m-d") : $_data['createDate'];

		$pre = $pre . date("dmy", strtotime($dateRequest));
		$pre = $pre . "R";
		$numberLenght = strlen((int) $new_acc_no);
		for ($i = $numberLenght; $i < 4; $i++) {
			$pre .= '0';
		}
		return $pre . $new_acc_no;
	}
	function dataExisting($tbName, $where)
	{
		$db = $this->getAdapter();
		$sql = " SELECT * FROM $tbName WHERE $where LIMIT 1";
		return $db->fetchRow($sql);
	}

	function requestingProccess($data = array())
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$stepNum = 1;
		if (!empty($data['stepNum'])) {
			$stepNum = $data['stepNum'];
		}
		$typeStep = 1; //keyValue
		$arrKey = array(
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 5,
		);
		if (!empty($data['typeStep'])) {
			$typeStep = $data['typeStep'];
			if ($typeStep == 2) { //keyTitle
				$arrKey = array(
					1 => $tr->translate("STEP_REQUESTING"),
					2 => $tr->translate("STEP_CHECKING_REQUEST"),
					3 => $tr->translate("STEP_PURCHASE_CHECKING_REQUEST"),
					4 => $tr->translate("STEP_APPROVED_REQUEST"),
					5 => $tr->translate("STEP_PURCHASING"),
					6 => $tr->translate("STEP_RECEIVING"),
				);
			} else if ($typeStep == 3) { //for Sql Query

				$string = ", CASE
					WHEN  $stepNum = 1 THEN '" . $tr->translate("STEP_REQUESTING") . "'
					WHEN  $stepNum = 2 THEN '" . $tr->translate("STEP_CHECKING_REQUEST") . "'
					WHEN  $stepNum = 3 THEN '" . $tr->translate("STEP_PURCHASE_CHECKING_REQUEST") . "'
					WHEN  $stepNum = 4 THEN '" . $tr->translate("STEP_APPROVED_REQUEST") . "'
					WHEN  $stepNum = 5 THEN '" . $tr->translate("STEP_PURCHASING") . "'
					WHEN  $stepNum = 6 THEN '" . $tr->translate("STEP_RECEIVING") . "'
					END AS processingStatusTitle ";
				return $string;
			} else if ($typeStep == 4) { //for Rerturn Array
				$arrKey = array(
					array('id' => 1, 'name' => $tr->translate("STEP_REQUESTING")),
					array('id' => 2, 'name' => $tr->translate("STEP_CHECKING_REQUEST")),
					array('id' => 3, 'name' => $tr->translate("STEP_PURCHASE_CHECKING_REQUEST")),
					array('id' => 4, 'name' => $tr->translate("STEP_APPROVED_REQUEST")),
					array('id' => 5, 'name' => $tr->translate("STEP_PURCHASING")),
					array('id' => 6, 'name' => $tr->translate("STEP_RECEIVING")),

				);
				return $arrKey;
			}
		}
		$value = empty($arrKey[$stepNum]) ? 0 : $arrKey[$stepNum];
		return $value;
	}

	function getAllApprovedRequest($_data = null)
	{
		//$dbgb = new Application_Model_DbTable_DbGlobal();
		//$userId = $dbgb->getUserId();
		//$currentLang = $dbgb->currentlang();

		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				rq.id,
				CONCAT(COALESCE(rq.requestNo,'')) AS name			
		";
		$sql .= " FROM `st_request_po` AS rq WHERE rq.status=1 AND rq.approveStatus=1  ";

		if (!empty($_data['requestHasPoAndPending'])) {
			$sql .= " AND COALESCE((SELECT po.processingStatus FROM `st_purchasing` AS po WHERE po.requestId =rq.id AND po.isInvoiced !=1 ORDER BY po.processingStatus ASC LIMIT 1 ),-1) >= 0  ";
			$sql .= " AND COALESCE((SELECT SUM(rqd.isCompletedPO) FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 ),0) >0  ";
		} else {
			$sql .= " AND rq.processingStatus IN (4,5) ";
			//checking Items In Request For Available TO Purchasing
			$sql .= " AND (SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 )=0 ";
		}

		if (!empty($_data['branch_id'])) {
			$sql .= " AND rq.projectId=" . $_data['branch_id'];
		}
		if (!empty($_data['requestId'])) { //For Get In Purchase Edit
			$sql .= " OR rq.id=" . $_data['requestId'];
		}
		$sql .= $dbGb->getAccessPermission("rq.projectId");
		$row = $db->fetchAll($sql);
		return $row;
	}

	function getAllSupplier($_data = null)
	{
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				sp.id AS id,
				sp.supplierName AS `name`
			";
		$sql .= " FROM `st_supplier` AS sp  ";
		$sql .= " WHERE sp.status=1 ";

		if (!empty($_data['branch_id'])) {
			$sql .= "";
		}

		$row = $db->fetchAll($sql);
		return $row;
	}

	function getAllCateIncome($_data = null)
	{
		$db = $this->getAdapter();
		$sql = "
			SELECT 
				c.id AS id,
				c.title AS `name`
			";
		$sql .= " FROM `st_cate_income` AS c  ";
		$sql .= " WHERE c.status=1 ";

		$row = $db->fetchAll($sql);
		return $row;
	}
	function initilizeProductType()
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optProduct = array(
			-1 => $tr->translate("SELECT_PRODUCT_TYPE"),
			'0' => $tr->translate("PRODUCT"),
			'1' => $tr->translate('SERVICE')
		);
		return $optProduct;
	}
	function initilizeStockType()
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optStockType = array(
			-1 => $tr->translate("SELECT_STOCK_TYPE"),
			1 => $tr->translate("COUNTSTOCK"),
			0 => $tr->translate('NONSTOCK')
		);
		return $optStockType;
	}
	function initilizeCategoryType()
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optCatType = array(
			-1 => $tr->translate("SELECT_CATEGORY_TYPE"),
			0 => $tr->translate("NORMAL"),
			1 => $tr->translate('IS_MATERIAL')
		);
		return $optCatType;
	}
	function addProductHistoryQty($projectId, $proId, $tranType, $Qty, $tranId = 0, $isUpdate = null)
	{
		$this->_name = 'st_product_story';
		$arr = array(
			'projectId' => $projectId,
			'proId' => $proId,
			'transId' => $tranId,
			'tranType' => $tranType,
			//1=+init,2 +receive,3 -usage,4 -sale,5 -transfer out ,5 +receiv tran,7 +- adjust
			'qty' => $Qty,
			'userId' => $this->getUserId(),
			'transDate' => date("Y-m-d"),
		);
		if ($isUpdate != null and $tranId != 0) {
			$where = "transId = " . $tranId . " AND tranType=" . $tranType;
			$this->update($arr, $where);
		} else {
			$this->insert($arr);
		}
	}
	function DeleteProductHistoryQty($tranId, $tranType = 0)
	{
		$this->_name = 'st_product_story';
		$where = " transId = $tranId AND tranType =" . $tranType;
		$this->delete($where);
	}
	function getProductLocationbyProId($_data = null)
	{
		$db = $this->getAdapter();

		$projectId = 0;
		if (!empty($_data['branch_id'])) {
			$projectId = $_data['branch_id'];
		}
		$sql = "
			SELECT 
				p.proId AS id,
				CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`,
				p.proCode,
				p.proName,
				l.qty AS currentQty,
				l.costing,
				(SELECT project_name from `ln_project` where br_id=l.projectId LIMIT 1) as projectName,
				p.measureLabel AS measureTitle
			";

		$sql .= " FROM 
					`st_product` AS p,
					 st_product_location AS l
				WHERE p.status=1
						AND p.proId=l.proId
		 ";

		if (!empty($projectId)) {
			$sql .= " AND l.projectId=" . $projectId;
		}

		if (!empty($_data['productId'])) {
			$sql .= " AND p.proId= " . $_data['productId'];
		}

		if (!empty($_data['isCountStock'])) {
			$sql .= " AND p.isCountStock= " . $_data['isCountStock'];
		}

		$rows = $db->fetchAll($sql);
		return $rows;
	}
	public function getAllBudgetType($parent = 0, $spacing = '', $cate_tree_array = '', $option = null, $data = array())
	{

		$db = $this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();

		$sql = "
			SELECT
				bt.id AS id,
				bt.budgetTitle AS `name` ";
		$sql .= " FROM `st_budget_type` AS bt  ";
		$sql .= " WHERE bt.status=1 AND bt.parentId = $parent ";
		if (!empty($data['notinBranchId'])) {
			$sql .= " AND bt.id NOT IN (SELECT budgetTypeId FROM `st_budget_project_item` WHERE projectId=" . $data['notinBranchId'] . " )";
		}

		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id = '';
		if ($rowCount > 0) {
			foreach ($query as $row) {
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllBudgetType($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		if ($option != null) {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$optionList = array(
				0 => $tr->translate("SELECT_BUDGET_TYPE"),
				-1 => $tr->translate("ADD_NEW")
			);
			if (!empty($cate_tree_array)) {
				foreach ($cate_tree_array as $rs) {
					$optionList[$rs['id']] = $rs['name'];
				}
			}
			return $optionList;
		}
		return $cate_tree_array;
	}
	public function getAllBudgetItem($parent = 0, $spacing = '', $cate_tree_array = '', $option = null, $data = null)
	{

		$db = $this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();

		$sql = "SELECT
					bi.id AS id,
					bi.budgetTitle AS `name` ";
		$sql .= " FROM `st_budget_item` AS bi  ";
		$sql .= " WHERE bi.status=1 AND bi.parentId = $parent ";
		if (!empty($data['budgetType'])) {
			$sql .= " AND bi.budgetTypeId=" . $data['budgetType'];
		}
		if (!empty($data['notinBranchId'])) {
			$sql .= " AND bi.id NOT IN (SELECT budgetId FROM `st_budget_project_item` WHERE projectId=" . $data['notinBranchId'] . " )";
		}
		if (!empty($data['BranchId'])) {
			$sql .= " AND bi.id IN (SELECT budgetId FROM `st_budget_project_item` WHERE projectId=" . $data['BranchId'] . ")";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id = '';
		if ($rowCount > 0) {
			foreach ($query as $row) {
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllBudgetItem($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		if ($option != null) {
			if (!empty($cate_tree_array)) {
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$optionList = array(
					0 => $tr->translate("SELECT_BUDGET_ITEM"),
					-1 => $tr->translate("ADD_NEW")
				);
				foreach ($cate_tree_array as $rs) {
					$optionList[$rs['id']] = $rs['name'];
				}
				return $optionList;
			}
		}
		return $cate_tree_array;
	}

	function purchasingTypeKey($data = array())
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$keyIndex = 1;
		if (!empty($data['keyIndex'])) {
			$keyIndex = $data['keyIndex'];
		}
		$typeKeyIndex = 1; //keyValue
		$arrKey = array(
			1 => 1,
			2 => 2,
			3 => 3,

		);
		if (!empty($data['typeKeyIndex'])) {
			$typeKeyIndex = $data['typeKeyIndex'];
			if ($typeKeyIndex == 2) { //keyTitle
				$arrKey = array(
					1 => $tr->translate("PO_BY_REQUEST"),
					2 => $tr->translate("DIRECTED_PO"),
					3 => $tr->translate("PO_PETTY_CASH"),
					4 => $tr->translate("OVERSEAS_PO"),

				);
			} else if ($typeKeyIndex == 3) { //for Sql Query

				$string = ", CASE
					WHEN  $keyIndex = 1 THEN '" . $tr->translate("PO_BY_REQUEST") . "'
					WHEN  $keyIndex = 2 THEN '" . $tr->translate("DIRECTED_PO") . "'
					WHEN  $keyIndex = 3 THEN '" . $tr->translate("PO_PETTY_CASH") . "'
					WHEN  $keyIndex = 4 THEN '" . $tr->translate("OVERSEAS_PO") . "'
				
					END AS purchaseTypeTitle ";
				return $string;
			} else if ($typeKeyIndex == 4) { //for Rerturn Array
				$arrKey = array(
					array('id' => 1, 'name' => $tr->translate("PO_BY_REQUEST")),
					array('id' => 2, 'name' => $tr->translate("DIRECTED_PO")),
					array('id' => 3, 'name' => $tr->translate("PO_PETTY_CASH")),
					array('id' => 4, 'name' => $tr->translate("OVERSEAS_PO")),


				);
				return $arrKey;
			}
		}
		$value = empty($arrKey[$keyIndex]) ? 0 : $arrKey[$keyIndex];
		return $value;
	}


	function getAllPo($_data = null)
	{

		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				po.id,
				CONCAT(COALESCE(po.purchaseNo,''),' ',COALESCE(spp.supplierName,'')) AS name			
		";
		$sql .= " FROM `st_purchasing` AS po 
					LEFT JOIN `st_supplier` AS spp ON spp.id = po.supplierId ";
		if (!empty($_data['invoiceType'])) { // only Deposit 1 time
			if ($_data['invoiceType'] == 2) {
				$sql .= " LEFT JOIN `st_invoice` AS inv ON po.id = inv.purId AND inv.ivType=2 AND inv.status=1 ";
			}
		}
		$sql .= " WHERE po.status=1 AND po.isInvoiced !=1 ";

		if (!empty($_data['branch_id'])) {
			$sql .= " AND po.projectId=" . $_data['branch_id'];
		}
		if (isset($_data['processingStatus'])) {
			$sql .= " AND po.processingStatus=" . $_data['processingStatus'];
		}
		$normalInvoice = 1;
		if (!empty($_data['invoiceType'])) {
			// only Deposit 1 time
			if ($_data['invoiceType'] == 2) {
				//Deposit Invoice
				$sql .= " AND inv.purId IS NULL ";
				$normalInvoice = 2;
			}
		}
		if (!empty($_data['requestId'])) {
			$sql .= " AND po.requestId=" . $_data['requestId'];
		}
		if (!empty($_data['purchaseType'])) { //Type Of Purchase

			$arrStep = array(
				'keyIndex' => $_data['purchaseType'],
				'typeKeyIndex' => 1,
			);
			$purchaseType = $this->purchasingTypeKey($arrStep);
			$sql .= " AND po.purchaseType=" . $purchaseType;
			if ($purchaseType == 1 || $purchaseType == 2) {
				if ($normalInvoice == 1) {
					// Query PO has received And verified Only
					$sql .= " AND COALESCE((SELECT rst.poId FROM  `st_receive_stock` AS rst WHERE rst.poId = po.id AND rst.status=1 AND rst.verified=1 LIMIT 1),0)  !=0 ";
				}
			}
			if (!empty($_data['purchaseId'])) {
				$sql .= " OR po.id=" . $_data['purchaseId'];
			}
		}
		$sql .= $dbGb->getAccessPermission("po.projectId");
		$row = $db->fetchAll($sql);
		return $row;
	}


	function invoiceTypeKey($data = array())
	{
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$keyIndex = 1;
		if (!empty($data['keyIndex'])) {
			$keyIndex = $data['keyIndex'];
		}
		$typeKeyIndex = 1; //keyValue
		$arrKey = array(
			1 => 1,
			2 => 2,
			3 => 3,

		);
		if (!empty($data['typeKeyIndex'])) {
			$typeKeyIndex = $data['typeKeyIndex'];
			if ($typeKeyIndex == 2) { //keyTitle
				$arrKey = array(
					1 => $tr->translate("INVOICE_BY_REQUEST"),
					2 => $tr->translate("INVOICE_DEPOSIT"),
					3 => $tr->translate("INVOICE_CONCATE"),
					4 => $tr->translate("INVOICE_PETTY_CASH"),

				);
			} else if ($typeKeyIndex == 3) { //for Sql Query

				$string = ", CASE
					WHEN  $keyIndex = 1 THEN '" . $tr->translate("INVOICE_BY_REQUEST") . "'
					WHEN  $keyIndex = 2 THEN '" . $tr->translate("INVOICE_DEPOSIT") . "'
					WHEN  $keyIndex = 3 THEN '" . $tr->translate("INVOICE_CONCATE") . "'
					WHEN  $keyIndex = 4 THEN '" . $tr->translate("INVOICE_PETTY_CASH") . "'
				
					END AS ivTypeTitle ";
				return $string;
			} else if ($typeKeyIndex == 4) { //for Rerturn Array
				$arrKey = array(
					array('id' => 1, 'name' => $tr->translate("INVOICE_BY_REQUEST")),
					array('id' => 2, 'name' => $tr->translate("INVOICE_DEPOSIT")),
					array('id' => 3, 'name' => $tr->translate("INVOICE_CONCATE")),
					array('id' => 4, 'name' => $tr->translate("INVOICE_PETTY_CASH")),


				);
				return $arrKey;
			}
		}
		$value = empty($arrKey[$keyIndex]) ? 0 : $arrKey[$keyIndex];
		return $value;
	}

	function getAllBank($data = array())
	{
		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				ba.id,
				CONCAT(COALESCE(ba.bank_name,'')) AS name			
		";
		$sql .= " FROM `st_bank` AS ba 				
				WHERE ba.bank_name!='' AND ba.status=1 ";
		$sql .= " ORDER BY ba.bank_name ASC ";
		$row = $db->fetchAll($sql);
		return $row;
	}
	public function getAllWorkType($parent = 0, $spacing = '', $cate_tree_array = '', $option = null)
	{

		$db = $this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();

		$sql = "SELECT
				wt.id AS id,
				wt.workTitle AS `name` ";
		$sql .= " FROM `st_work_type` AS wt  ";
		$sql .= " WHERE wt.status=1 AND wt.parentId = $parent ";
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id = '';
		if ($rowCount > 0) {
			foreach ($query as $row) {
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllWorkType($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		if ($option != null) {
			if (!empty($cate_tree_array)) {
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$request = Zend_Controller_Front::getInstance()->getRequest();
				$optionList = array(
					0 => $tr->translate("SELECT_WORK_TYPE")
				);
				if ($request->getActionName() == 'add' and $request->getControllerName() != 'worktype') {
					$optionList[-1] = $tr->translate("ADD_NEW");
				}
				foreach ($cate_tree_array as $rs) {
					$optionList[$rs['id']] = $rs['name'];
				}
				return $optionList;
			}
		}
		return $cate_tree_array;
	}

	function getWorkTypeOpt($option = null)
	{

		$db = $this->getAdapter();
		$sql = "SELECT
				wt.id AS id,
				wt.workTitle AS `name` ";
		$sql .= " FROM `st_work_type` AS wt  ";
		$sql .= " WHERE wt.status=1 ";
		$rows = $db->fetchAll($sql);
		if ($option == null) {
			return $rows;
		} else {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = " <option value=''>" . $tr->translate("SELECT_WORKTYPE") . "</option> ,
						<option value='-1'>" . $tr->translate("ADD_NEW") . "</option>";
			if (!empty($rows)) {
				foreach ($rows as $row) {
					$options .= '<option value="' . $row['id'] . '" >' . htmlspecialchars($row['name'], ENT_QUOTES) . '</option>';
				}
			}
			return $options;
		}
	}
	function getProductPOInfo($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
						p.id,
						(SELECT r.requestNo FROM `st_request_po` r WHERE r.id = p.requestId LIMIT 1) as requestNo,
						(SELECT DATE_FORMAT(r.date,'%d-%m-%Y') FROM `st_request_po` r WHERE r.id = p.requestId LIMIT 1) as requestDate,
						p.purchaseNo,
						DATE_FORMAT(p.date,'%d-%m-%Y') createDate,
						p.processingStatus,
						p.supplierId,
						p.requestId,
						(SELECT s.supplierName FROM `st_supplier` s WHERE s.id=p.supplierId LIMIT 1) as supplierName,
						pd.purchaseId,
						pd.proId,
						(SELECT proName FROM `st_product` WHERE st_product.proId=pd.proId LIMIT 1) AS proName,
						(SELECT measureLabel FROM `st_product` WHERE st_product.proId=pd.proId LIMIT 1) AS measureLabel,
						pd.qty,
						pd.qtyAfter,
						pd.unitPrice,
						pd.discountPercent,
						pd.discountAmount,
						pd.totalDiscount,
						pd.subTotal,
						pd.requestInDate,
						pd.isClosed
					FROM
						`st_purchasing` p,
						`st_purchasing_detail` pd
					WHERE
						p.id=pd.purchaseId ";
		if (!empty($data['purchaseId'])) {
			$sql .= " AND pd.purchaseId = " . $data['purchaseId'];
		}

		if (!empty($data['proId'])) {
			$sql .= " AND pd.proId = " . $data['proId'];
		}
		if ($data['isClosed'] > -1) {
			$sql .= " AND pd.isClosed = " . $data['isClosed'];
		}
		if (!empty($data['orderisClosedASC'])) {
			$sql .= " ORDER BY pd.isClosed ASC ";
		}

		if (!empty($data['fetchRow'])) {
			$rs = $db->fetchRow($sql);
		} else {
			$rs = $db->fetchAll($sql);
		}

		return $rs;
	}
	function updateStockbyBranchAndProductId($data)
	{
		$resultStock = $this->getProductInfoByLocation($data);
		if (!empty($resultStock)) {
			if ($resultStock['isCountStock'] == 1 and $resultStock['isService'] == 0) { //only for type product count stock only

				$currentStock = $resultStock['currentQty'];
				$currentPrice = $resultStock['currentPrice'];
				$newQty = $data['EntyQty'];
				$newPrice = $data['EntyPrice'];
				$totalQty = $currentStock + $newQty;
				$costing = (($currentStock * $currentPrice) + ($newQty * $newPrice)) / $totalQty;


				$arr = array(
					'projectId' => $data['branch_id'],
					'productId' => $data['productId'],
					'costing' => $currentPrice,
					'date' => date('Y-m-d')
				);

				$this->_name = 'st_product_costing';
				$this->insert($arr);

				$arr = array(
					'qty' => $totalQty,
					'costing' => $costing
				);

				$this->_name = 'st_product_location';
				$where = 'projectId=' . $data['branch_id'] . " AND proId=" . $data['productId'];
				$this->update($arr, $where);
			}
		} else { //for product not in project
			$param = array(
				'productId' => $data['projectId']
			);
			$row = $this->getProductInfoByLocation($param);
			if (!empty($row)) {
				if ($row['isCountStock'] == 1 and $row['isService'] == 0) {
					$arr = array(
						'projectId' => $data['branch_id'],
						'qty' => $data['EntyQty'],
						'proId' => $data['productId'],
						'costing' => empty($data['EntyPrice']) ? 0 : $data['EntyPrice'],
					);
					$this->_name = 'st_product_location';
					$this->insert($arr);
				}

				$arr = array(
					'projectId' => $data['branch_id'],
					'productId' => $data['productId'],
					'costing' => $data['EntyPrice'],
					'date' => date('Y-m-d')
				);

				$this->_name = 'st_product_costing';
				$this->insert($arr);
			}
		}
	}
	function updateProductLocation($data)
	{
		$resultStock = $this->getProductInfoByLocation($data);
		if (!empty($resultStock)) {
			if ($resultStock['isCountStock'] == 1) {
				$currentStock = $resultStock['currentQty'];
				$newQty = $data['EntyQty'];
				$totalQty = $currentStock + $newQty;

				$arr = array(
					'qty' => $totalQty,
				);

				$this->_name = 'st_product_location';
				$where = 'projectId=' . $data['branch_id'] . " AND proId=" . $data['productId'];
				$this->update($arr, $where);
			}
		} else { //new stock
			$arr = array(
				'projectId' => $data['branch_id'],
				'qty' => $data['EntyQty'],
				'proId' => $data['productId'],
				'costing' => empty($data['costing']) ? 0 : $data['costing'],
			);

			$this->_name = 'st_product_location';
			$this->insert($arr);
		}
	}

	function getAllPaymentRecord($_data = null)
	{
		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				pt.id,
						
		";
		if (!empty($_data['paymentMethodCheque'])) {
			$sql .= " CONCAT(COALESCE(pt.accNameAndChequeNo,''),' ',COALESCE(spp.supplierName,'')) AS name	
			";
		} else {
			$sql .= " 
				CONCAT(COALESCE(pt.paymentNo,''),' ',COALESCE(spp.supplierName,'')) AS name	
			";
		}
		$sql .= " FROM `st_payment` AS pt 
				LEFT JOIN `st_supplier` AS spp ON spp.id = pt.supplierId
		";
		$sql .= " WHERE pt.status=1  ";


		if (!empty($_data['branch_id'])) {
			$sql .= " AND pt.projectId=" . $_data['branch_id'];
		}
		if (!empty($_data['paymentMethodCheque'])) {
			//checking Payment For Available TO IssueCheque
			$sql .= " AND (SELECT COALESCE(reCh.paymentId,0) FROM `st_receive_cheque` AS reCh WHERE reCh.paymentId =pt.id AND reCh.status=1 ORDER BY reCh.id ASC LIMIT 1 ) IS NULL  ";
			$sql .= " AND pt.paymentMethod=3 ";
			if (!empty($_data['currentPaymentId'])) { // edit IssueCheque
				$sql .= " OR pt.id= " . $_data['currentPaymentId'];
			}
		}
		$sql .= $dbGb->getAccessPermission("pt.projectId");
		$row = $db->fetchAll($sql);
		return $row;
	}

	function getAllIssueChequeRecord($_data = null)
	{
		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				cheQ.id,
				CONCAT(COALESCE(pt.accNameAndChequeNo,''),' ',COALESCE(spp.supplierName,'')) AS name			
		";
		$sql .= " FROM 
					st_receive_cheque AS cheQ
				JOIN `st_payment` AS pt ON pt.id = cheQ.paymentId
				LEFT JOIN `st_supplier` AS spp ON spp.id = pt.supplierId
		";
		$sql .= " WHERE cheQ.status=1  AND cheQ.drawUserId IS NULL ";


		if (!empty($_data['branch_id'])) {
			$sql .= " AND cheQ.projectId=" . $_data['branch_id'];
		}
		if (!empty($_data['currentIssueId'])) {
			$sql .= " OR cheQ.id=" . $_data['currentIssueId'];
		}
		$row = $db->fetchAll($sql);
		return $row;
	}
	function updatePoStatusisClose($data)
	{
			//if all po detail of product close update po to close also
			/*$data= array(* purchaseId,* isClosed,* fetchRow=1)
		 */;
		$poResult = $this->getProductPOInfo($data);
		if ($poResult['isClosed'] == 1) {
			$where = "id=" . $data['purchaseId'];
			$this->_name = "st_purchasing";
			$arr = array(
				'processingStatus' => 1
			);
			$this->update($arr, $where);
		}
	}
	function getViewById($type, $is_opt = null)
	{
		$session_lang = new Zend_Session_Namespace('lang');
		$lang_id = $session_lang->lang_id;
		$str = 'name_en';
		if ($lang_id == 1) {
			$str = 'name_kh';
		}

		$db = $this->getAdapter();
		$sql = "SELECT key_code AS id,$str AS name 
				FROM st_view 
					WHERE `type`=$type AND `status`=1 ";
		$rows = $db->fetchAll($sql);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$options = array(-1 => $tr->translate("SELECT_TYPE"));
		if ($is_opt != null) {
			if (!empty($rows))
				foreach ($rows as $row) {
					$options[$row['id']] = $row['name'];
				}
		} else {
			return $rows;
		}
		return $options;
	}
	function getAllStaffbyBranch($data, $option = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT 
					id,staffName AS name
				FROM st_worker
					WHERE `status`=1 ";
		if (!empty($data['branch_id'])) {
			$sql .= " AND projectId=" . $data['branch_id'];
		}
		$rows = $db->fetchAll($sql);
		if ($option != null) {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = array(-1 => $tr->translate("CHOOSE_STAFF"));
			if (!empty($rows))
				foreach ($rows as $row) {
					$options[$row['id']] = $row['name'];
				}
			return $options;
		}
		return $rows;
	}
	function getAllContractorbyBranch($data, $option = null)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
		id,staffName AS name
		FROM st_contractor
		WHERE `status`=1 ";
		if (!empty($data['branch_id'])) {
			$sql .= " AND projectId=" . $data['branch_id'];
		}
		$rows = $db->fetchAll($sql);
		if ($option != null) {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$options = array(-1 => $tr->translate("CHOOSE_STAFF"));
			if (!empty($rows))
				foreach ($rows as $row) {
					$options[$row['id']] = $row['name'];
				}
			return $options;
		}
		return $rows;
	}


	function getAllTransferOut($_data = null)
	{

		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				trs.id,
				CONCAT(COALESCE(trs.transferNo,''),' ',COALESCE(trs.transferNo,'')) AS name			
		";
		$sql .= " FROM `st_transferstock` AS trs 
					 ";

		$sql .= " WHERE trs.status=1 AND trs.isApproved=1 ";

		//checking Items In Request For Available to receive
		$sql .= " AND (SELECT trsd.isCompleted FROM `st_transferstock_detail` AS trsd WHERE trsd.transferId =trs.id ORDER BY trsd.isCompleted ASC LIMIT 1 )=0 ";

		if (!empty($_data['fromProjectId'])) {
			$sql .= " AND trs.fromProjectId=" . $_data['fromProjectId'];
		}

		if (!empty($_data['transferId'])) {
			$sql .= " OR trs.id=" . $_data['transferId'];
		}

		$sql .= $dbGb->getAccessPermission("trs.toProjectId");
		$row = $db->fetchAll($sql);
		return $row;
	}


	// Below For Push Notification Only
	function getMobileToken($_data)
	{

		$_data['branchId'] = empty($_data['branchId']) ? 0 : $_data['branchId'];
		$_data['userAction'] = empty($_data['userAction']) ? 0 : $_data['userAction'];


		$userList = $this->getUserList($_data);
		$userList = empty($userList) ? "0" : $userList;

		$db = $this->getAdapter();
		$sql = "SELECT mb.`token`
				FROM `mobile_mobile_token` AS mb
				WHERE mb.userId != 0 ";

		$sql .= " AND mb.userAction=" . $_data['userAction'];
		$sql .= " AND mb.userId IN (" . $userList . ") ";
		return  $db->fetchCol($sql);
	}

	function getUserList($_data)
	{
		$_data['userAction'] = empty($_data['userAction']) ? 0 : $_data['userAction'];
		$recordBranchId = empty($_data['branchId']) ? 0 : $_data['branchId'];
		$db = $this->getAdapter();
		$sql = " 
			SELECT 
				u.id,u.branch_list 
			FROM `rms_users` AS u 
			WHERE u.active =1 
				AND u.userAction= " . $_data['userAction'] . "
		";
		$rowUser = $db->fetchAll($sql);

		$userList = "";
		if (!empty($rowUser)) {
			foreach ($rowUser as $rs) {
				$branchList = empty($rs['branch_list']) ? "" : $rs['branch_list'];
				$exp = explode(",", $branchList);
				if (COUNT($exp) > 1) {
					foreach ($exp as $branchId) {
						if ($branchId == $recordBranchId) {
							if ($userList == "") {
								$userList = $rs['id'];
							} else {
								$userList = $userList . "," . $rs['id'];
							}
							break;
						}
					}
				} else {
					if ($branchList == $recordBranchId) {
						if ($userList == "") {
							$userList = $rs['id'];
						} else {
							$userList = $userList . "," . $rs['id'];
						}
						break;
					}
				}
			}
		}
		return $userList;
	}

	function getRequestPOInfoId($id = null)
	{
		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql = " 
		SELECT 
			'requestingRecord' AS recordType
				
				,rq.date AS recordDate
				,rq.requestNo AS recordNo
				,rq.*
				,CASE
					WHEN  rq.checkingStatus= 0 THEN  'PENDING'
					WHEN  rq.checkingStatus = 1 THEN 'APPROVED'
					WHEN  rq.checkingStatus = 2 THEN 'REJECTED'
				END AS checkingStatusTitle,
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN   'PENDING'
					WHEN  rq.pCheckingStatus = 1 THEN  'APPROVED'
					WHEN  rq.pCheckingStatus = 2 THEN  'REJECTED'
				END AS pCheckingStatusTitle,
				CASE
					WHEN  rq.approveStatus= 0 THEN  'PENDING'
					WHEN  rq.approveStatus = 1 THEN 'APPROVED'
					WHEN  rq.approveStatus = 2 THEN 'REJECTED'
				END AS approveStatusTitle,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS projectName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS userName,
				rq.processingStatus AS currentStep,
				'' AS itemsRequest
				
		FROM st_request_po AS rq WHERE 1 ";
		if (!empty($id)) {
			$sql .= " AND id = $id ";
		}
		$sql .= " LIMIT 1 ";
		return $db->fetchRow($sql);
	}

	function pushNotificationForAndroid($_data)
	{
		$_data['branchId'] = empty($_data['branchId']) ? 0 : $_data['branchId'];
		$_data['userAction'] = empty($_data['userAction']) ? 0 : $_data['userAction'];
		$_data['deviceType'] = empty($_data['deviceType']) ? 1 : $_data['deviceType'];

		$notificationId = empty($_data['notificationId']) ? 0 : $_data['notificationId'];
		$notificationTitle = "Notification Title";
		$notificationSubTitle = "Notification Sub Title";
		$notificationDescription = "";
		$typeNotify = empty($_data['typeNotify']) ? "toCheckingRequest" : $_data['typeNotify'];

		$recordDetail = array();
		if ($typeNotify == "toCheckingRequest" || $typeNotify == "toPoVerifyRequest" || $typeNotify == "toApproveRequest" || $typeNotify == "toPoPurchase") {
			$recordInfo = $this->getRequestPOInfoId($notificationId);
			if (!empty($recordInfo)) {
				$notificationTitle = "សំណើបញ្ជាទិញពី : គម្រោង " . str_replace('គម្រោង', '', $recordInfo['projectName']);
				$notificationSubTitle = "សំណើលេខ " . $recordInfo['requestNo'] . " ស្នើរដោយ : " . $recordInfo['userName'];
				$notificationDescription = "សំណើលេខ " . $recordInfo['requestNo'] . " ស្នើរដោយ : " . $recordInfo['userName'];
				if (!empty($recordInfo['purpose'])) {
					$notificationDescription = $notificationDescription . " គោលបំណងស្នើ : " . $recordInfo['purpose'];
				}

				if ($typeNotify == "toPoVerifyRequest") {
					if (!empty($recordInfo['checkingByName'])) {
						$notificationDescription = $notificationDescription . " បានត្រួតពិនិត្យដោយ : " . $recordInfo['checkingByName'];
					}
				}
				if ($typeNotify == "toApproveRequest") {
					if (!empty($recordInfo['pCheckingByName'])) {
						$notificationDescription = $notificationDescription . " បានផ្ទៀងផ្ទាត់ដោយ : " . $recordInfo['pCheckingByName'];
					}
				}

				if ($typeNotify == "toPoPurchase") {
					$notificationTitle = "សំណើបញ្ជាទិញបានអនុម័តសម្រាប់ :  គម្រោង" . str_replace('គម្រោង', '', $recordInfo['projectName']);
				}
				$recordDetail = array($recordInfo);
			}
		}


		$dataNotify = array(
			"notificationId" 	=> $notificationId,
			"title" 			=> $notificationTitle,
			"subTitle" 			=> $notificationDescription,
			"userAction" 		=> $_data['userAction'],
			"typeNotify" 		=> $typeNotify,
			"recordDetail" 		=> $recordDetail,
		);
		$headings = array(
			"en" => $notificationTitle,
		);
		$content = array(
			"en" => $notificationSubTitle,
		);
		$bigPicture = "http://8.214.12.212/bppt.22.4.09/public/images/icon.png";

		$androidToken = $this->getMobileToken($_data);
		$fields = array(
			'app_id' => APP_ID,
			'include_player_ids' => $androidToken,
			'data' => $dataNotify,
			'headings' => $headings,
			'contents' => $content,
			"external_id" => null,

		);

		$fields = json_encode($fields);


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			'Authorization: Basic OGY3MGQ2M2EtMmQ3OS00MjZhLTk2MjYtYjYzMzExYTg5YWRm'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);

		curl_close($ch);
	}
}
