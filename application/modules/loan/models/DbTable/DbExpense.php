<?php
class Loan_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	function getAllExpenseCategoryLoan($parent = 0, $spacing = '', $cate_tree_array = ''){
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$db = $this->getAdapter();
		$sql = " select key_code as id,name_kh as name from ln_view where type=13 AND name_kh!='' AND `parent_id` = $parent ";
		$query =  $db->fetchAll($sql);
		
		$rowCount = count($query);
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllExpenseCategoryLoan($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
}