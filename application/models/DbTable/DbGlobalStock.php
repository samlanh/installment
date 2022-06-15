<?php

class Application_Model_DbTable_DbGlobalStock extends Zend_Db_Table_Abstract
{
	
	public function getAllCategoryProduct($parent = 0, $spacing = '', $cate_tree_array = ''){

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
		return $cate_tree_array;
		
	}
	function getAllProductByBranch($_data=null){
		//$dbgb = new Application_Model_DbTable_DbGlobal();
		//$userId = $dbgb->getUserId();
		//$currentLang = $dbgb->currentlang();
		
		$db=$this->getAdapter();
		$sql="
			SELECT 
				p.proId AS id,
				p.proName AS `name`
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
	
	
}
?>