<?php

class Application_Model_DbTable_DbGlobalStock extends Zend_Db_Table_Abstract
{
	
	public function getAllCategoryProduct($parent = 0, $spacing = '', $cate_tree_array = '',$option=null){

		$db=$this->getAdapter();
			if (!is_array($cate_tree_array))
			$cate_tree_array = array();
	
		$sql="
			SELECT 
				c.id AS id,
				c.categoryName AS `name`
			";
		$sql.=" FROM `st_category` AS c  ";
		$sql.=" WHERE c.status=1 AND c.parentId = $parent ";
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllCategoryProduct($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		if($option!=null){
			if(!empty($cate_tree_array)){
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$optionList= array(
						0=>$tr->translate("SELECT_CATEGORY"),
						-1=>$tr->translate("ADD_NEW")
						);
				foreach ($cate_tree_array as $rs){
					$optionList[$rs['id']]=$rs['name'];
				}
				return $optionList;
			}
		}
		return $cate_tree_array;
		
	}
	function getAllProduct($_data=null){
		//$dbgb = new Application_Model_DbTable_DbGlobal();
		//$userId = $dbgb->getUserId();
		//$currentLang = $dbgb->currentlang();
		
		$db=$this->getAdapter();
		$sql="
			SELECT 
				p.proId AS id,
				CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`
			";
		$sql.=" FROM `st_product` AS p  ";
		$sql.=" WHERE p.status=1 ";
			
		if(!empty($_data['branch_id'])){
			$sql.="";
		}
		if(!empty($_data['categoryId'])){
			$sql.=" AND p.categoryId= ".$_data['categoryId'];
		}
		if(!empty($_data['requestId'])){
			$sql.=" AND p.proId IN (SELECT rqd.proId FROM `st_request_po_detail` AS rqd  WHERE rqd.requestId=".$_data['requestId']." AND rqd.approvedStatus=1 AND rqd.isCompletedPO=0 GROUP BY rqd.proId )";
			if(!empty($_data['purchaseId'])){//Case Purchase Edit
				$sql.=" OR p.proId IN (SELECT pod.proId FROM `st_purchasing_detail` AS pod  WHERE pod.purchaseId=".$_data['purchaseId']." GROUP BY pod.proId ) ";
			}
		}
		if(!empty($_data['notExistingProjectid'])){
			$sql.=" AND p.proId NOT IN (SELECT l.proId FROM `st_product_location` AS l  WHERE l.projectId=".$_data['notExistingProjectid']." )";
		}
		$row = $db->fetchAll($sql);
		return $row;
		
	}
	
	function getProductInfoByLocation($_data=null){
		
		$db=$this->getAdapter();
		
		$projectId=0;
		if(!empty($_data['branch_id'])){
			$projectId = $_data['branch_id'];
		}
		$sql="
			SELECT 
				p.proId AS id,
				CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`,
				p.proCode,
				p.proName,
				(SELECT pl.qty FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= $projectId LIMIT 1) AS currentQty,
				p.measureLabel AS measureTitle
			";
		
		if(!empty($_data['requestId'])){
			$sql.="
				,
				(SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=".$_data['requestId']." LIMIT 1 ) AS isCompletedPO,
				(SELECT rqd.dateReqStockIn FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=".$_data['requestId']." LIMIT 1 ) AS dateReqStockIn,
				(SELECT rqd.note FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=".$_data['requestId']." LIMIT 1 ) AS requestItemsNote,
				(SELECT rqd.qtyApproved FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=".$_data['requestId']." LIMIT 1 ) AS qtyApproved,
				
			";
			if(!empty($_data['purchaseId'])){//Case Purchase Edit
				$sql.=" (COALESCE((SELECT pod.qty FROM `st_purchasing_detail` as pod WHERE p.proId = pod.proId LIMIT 1),0)+COALESCE((SELECT rqd.qtyApprovedAfter FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=".$_data['requestId']." LIMIT 1 ),0)) AS qtyApprovedAfter ";
			}else{
				$sql.=" (SELECT rqd.qtyApprovedAfter FROM `st_request_po_detail` AS rqd WHERE rqd.proId =p.proId AND rqd.requestId=".$_data['requestId']." LIMIT 1 ) AS qtyApprovedAfter ";
			}
		}
		
		$sql.=" FROM 
					`st_product` AS p ";
			
		if(!empty($projectId)){
			$sql.=" ,st_product_location AS l";
			$sql.=" WHERE p.status=1
						AND p.proId=l.proId ";
				$sql.=" AND l.projectId=".$projectId;
		}else{
			$sql.=" WHERE p.status=1 ";
		}
		if(!empty($_data['categoryId'])){
			$sql.=" AND p.categoryId= ".$_data['categoryId'];
		}
		if(!empty($_data['productId'])){
			$sql.=" AND p.proId= ".$_data['productId'];
		}
		$sql.=" ORDER BY p.proId DESC LIMIT	1 ";
		
		return $db->fetchRow($sql);
		
	}
	
	function generateRequestNo($_data=null){
		
		$this->_name='st_request_po';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";
		
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);
		
		$db = $this->getAdapter();
		$sql=" SELECT rq.id  FROM $this->_name AS rq WHERE rq.projectId = $branch_id  ORDER BY rq.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		
		$dateRequest = empty($_data['dateRequest'])?date("Y-m-d"):$_data['dateRequest'];
		
		$pre=$pre.date("dmy",strtotime($dateRequest));
		$pre=$pre."R";
		$numberLenght= strlen((int)$new_acc_no);
		for($i = $numberLenght;$i<4;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function generatePurchaseNo($_data=null){
		
		$this->_name='st_purchasing';
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$pre = "";
		
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$pre = $dbgb->getPrefixCode($branch_id);
		
		$db = $this->getAdapter();
		$sql=" SELECT rq.id  FROM $this->_name AS rq WHERE rq.projectId = $branch_id  ORDER BY rq.id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		
		$dateRequest = empty($_data['dateRequest'])?date("y-m-d"):$_data['dateRequest'];
		
		$pre=$pre.date("dmy",strtotime($dateRequest));
		$pre=$pre."P";
		$numberLenght= strlen((int)$new_acc_no);
		for($i = $numberLenght;$i<4;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function dataExisting($tbName,$where){
			$db = $this->getAdapter();
			$sql=" SELECT * FROM $tbName WHERE $where LIMIT 1";
			return $db->fetchRow($sql);
	}
	
	function requestingProccess($data=array()){
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$stepNum=1;
		if(!empty($data['stepNum'])){
			$stepNum=$data['stepNum'];
		}
		$typeStep=1;//keyValue
		$arrKey = array(
			1=>1,
			2=>2,
			3=>3,
			4=>4,
			5=>5,
		);
		if(!empty($data['typeStep'])){
			$typeStep=$data['typeStep'];
			if($typeStep==2){//keyTitle
				$arrKey = array(
					1=>$tr->translate("STEP_REQUESTING"),
					2=>$tr->translate("STEP_CHECKING_REQUEST"),
					3=>$tr->translate("STEP_PURCHASE_CHECKING_REQUEST"),
					4=>$tr->translate("STEP_APPROVED_REQUEST"),
					5=>$tr->translate("STEP_PURCHASING"),
					6=>$tr->translate("STEP_RECEIVING"),
				);
			}else if($typeStep==3){//for Sql Query
				
				$string=", CASE
					WHEN  $stepNum = 1 THEN '".$tr->translate("STEP_REQUESTING")."'
					WHEN  $stepNum = 2 THEN '".$tr->translate("STEP_CHECKING_REQUEST")."'
					WHEN  $stepNum = 3 THEN '".$tr->translate("STEP_PURCHASE_CHECKING_REQUEST")."'
					WHEN  $stepNum = 4 THEN '".$tr->translate("STEP_APPROVED_REQUEST")."'
					WHEN  $stepNum = 5 THEN '".$tr->translate("STEP_PURCHASING")."'
					WHEN  $stepNum = 6 THEN '".$tr->translate("STEP_RECEIVING")."'
					END AS processingStatusTitle ";
				return $string;
			}
		}
		$value = empty($arrKey[$stepNum])?0:$arrKey[$stepNum];
		return $value;
	}
	
	function getAllApprovedRequest($_data=null){
		//$dbgb = new Application_Model_DbTable_DbGlobal();
		//$userId = $dbgb->getUserId();
		//$currentLang = $dbgb->currentlang();
		
		$db=$this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				rq.id,
				CONCAT(COALESCE(rq.requestNo,'')) AS name			
		";
		$sql.=" FROM `st_request_po` AS rq WHERE rq.status=1 AND rq.approveStatus=1 AND rq.processingStatus IN (4,5) ";	
		
		//checking Items In Request For Available TO Purchasing
		$sql.=" AND (SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 )=0 ";
		
		if(!empty($_data['branch_id'])){
			$sql.=" AND rq.projectId=".$_data['branch_id'];
		}
		if(!empty($_data['requestId'])){//For Get In Purchase Edit
			$sql.=" OR rq.id=".$_data['requestId'];
		}
		$row = $db->fetchAll($sql);
		return $row;
		
	}
	
	function getAllSupplier($_data=null){
		$db=$this->getAdapter();
		$sql="
			SELECT 
				sp.id AS id,
				sp.supplierName AS `name`
			";
		$sql.=" FROM `st_supplier` AS sp  ";
		$sql.=" WHERE sp.status=1 ";
			
		if(!empty($_data['branch_id'])){
			$sql.="";
		}
		
		$row = $db->fetchAll($sql);
		return $row;
		
	}
	function initilizeProductType(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optProduct = array(
			-1=>$tr->translate("SELECT_PRODUCT_TYPE"),
			'0'=>$tr->translate("PRODUCT"),
			'1'=>$tr->translate('SERVICE')
		);
		return $optProduct;
	}
	function initilizeStockType(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$optStockType = array(
			-1=>$tr->translate("SELECT_STOCK_TYPE"),
			1=>$tr->translate("COUNTSTOCK"),
			0=>$tr->translate('NONSTOCK')
		);
		return $optStockType;
	}
	function addProductHistoryQty($projectId,$proId,$tranType,$Qty){
		$this->_name='st_product_story';
		$arr = array(
				'projectId'=>$projectId,
				'proId'=>$proId,
				'tranType'=>$tranType,//1=+init,2 +receive,3 -usage,4 -sale,5 -transfer out ,5 +receiv tran,7 +- adjust
				'qty'=>$Qty,
				'userId'=>$this->getUserId(),
				'transDate'=>date("Y-m-d"),
		);
		$this->insert($arr);
	}
	function getProductLocationbyProId($_data=null){
		$db=$this->getAdapter();
		
		$projectId=0;
		if(!empty($_data['branch_id'])){
			$projectId = $_data['branch_id'];
		}
		$sql="
			SELECT 
				p.proId AS id,
				CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`,
				p.proCode,
				p.proName,
				l.qty AS currentQty,
				(SELECT project_name from `ln_project` where br_id=l.projectId LIMIT 1) as projectName,
				p.measureLabel AS measureTitle
			";
		
		$sql.=" FROM 
					`st_product` AS p,
					 st_product_location AS l
				WHERE p.status=1
						AND p.proId=l.proId
		 ";
			
		if(!empty($projectId)){
				$sql.=" AND l.projectId=".$projectId;
		}
		
		if(!empty($_data['productId'])){
			$sql.=" AND p.proId= ".$_data['productId'];
		}
		
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	public function getAllBudgetType($parent = 0, $spacing = '', $cate_tree_array = '',$option=null){
	
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
	
		$sql="
			SELECT
				bt.id AS id,
				bt.budgetTitle AS `name` ";
		$sql.=" FROM `st_budget_type` AS bt  ";
		$sql.=" WHERE bt.status=1 AND bt.parentId = $parent ";
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllBudgetType($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		if($option!=null){
			if(!empty($cate_tree_array)){
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$optionList= array(
						0=>$tr->translate("SELECT_BUDGET_TYPE"),
						-1=>$tr->translate("ADD_NEW")
				);
				foreach ($cate_tree_array as $rs){
					$optionList[$rs['id']]=$rs['name'];
				}
				return $optionList;
			}
		}
		return $cate_tree_array;
	
	}
	public function getAllBudgetItem($parent = 0, $spacing = '', $cate_tree_array = '',$option=null,$data=null){
	
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
	
		$sql="SELECT
					bi.id AS id,
					bi.budgetTitle AS `name` ";
		$sql.=" FROM `st_budget_item` AS bi  ";
		$sql.=" WHERE bi.status=1 AND bi.parentId = $parent ";
		if(!empty($data['budgetType'])){
			$sql.=" AND bi.budgetTypeId=".$data['budgetType'];
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllBudgetItem($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		if($option!=null){
			if(!empty($cate_tree_array)){
				$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				$optionList= array(
						0=>$tr->translate("SELECT_BUDGET_ITEM"),
						-1=>$tr->translate("ADD_NEW")
				);
				foreach ($cate_tree_array as $rs){
					$optionList[$rs['id']]=$rs['name'];
				}
				return $optionList;
			}
		}
		return $cate_tree_array;
	
	}
	
}
?>