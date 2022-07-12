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
				$optionList= array();
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
		$row = $db->fetchAll($sql);
		return $row;
		
	}
	function getProductInfoByLocation($_data=null){
		
		$db=$this->getAdapter();
		$sql="
			SELECT 
				p.proId AS id,
				CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`,
				p.proCode,
				p.proName,
				0 AS currentQty,
				'Kg' AS measureTitle
			";
		$sql.=" FROM `st_product` AS p  ";
		$sql.=" WHERE p.status=1 ";
			
		if(!empty($_data['branch_id'])){
			$sql.="";
		}
		if(!empty($_data['categoryId'])){
			$sql.=" AND p.categoryId= ".$_data['categoryId'];
		}
		if(!empty($_data['productId'])){
			$sql.=" AND p.proId= ".$_data['productId'];
		}
		$sql.=" ORDER BY p.proId DESC LIMIT	1 ";
		$row = $db->fetchRow($sql);
		return $row;
		
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
		
		$pre=$pre.date("Ymd",strtotime($dateRequest));
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
		$stepNum=0;
		if(!empty($data['stepNum'])){
			$stepNum=$data['stepNum'];
		}
		$typeStep=1;//keyValue
		$arrKey = array(
			0=>0,
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
					0=>$tr->translate("STEP_REQUESTING"),
					1=>$tr->translate("STEP_CHECKING_REQUEST"),
					2=>$tr->translate("STEP_PURCHASE_CHECKING_REQUEST"),
					3=>$tr->translate("STEP_APPROVED_REQUEST"),
					4=>$tr->translate("STEP_PURCHASING"),
					5=>$tr->translate("STEP_RECEIVING"),
				);
			}else if($typeStep==3){//for Sql Query
				
				$string=", CASE
					WHEN  $stepNum = 0 THEN '".$tr->translate("STEP_REQUESTING")."'
					WHEN  $stepNum = 1 THEN '".$tr->translate("STEP_CHECKING_REQUEST")."'
					WHEN  $stepNum = 2 THEN '".$tr->translate("STEP_PURCHASE_CHECKING_REQUEST")."'
					WHEN  $stepNum = 3 THEN '".$tr->translate("STEP_PURCHASE_CHECKING_REQUEST")."'
					WHEN  $stepNum = 4 THEN '".$tr->translate("STEP_APPROVED_REQUEST")."'
					WHEN  $stepNum = 5 THEN '".$tr->translate("STEP_RECEIVING")."'
					END AS processingStatusTitle ";
				return $string;
			}
		}
		$value = empty($arrKey[$stepNum])?0:$arrKey[$stepNum];
		return $value;
	}
	
}
?>