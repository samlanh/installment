<?php

class Budget_Model_DbTable_DbInitilizeBudget extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_budget_project';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllBudgetProject($search){
    	$sql="SELECT bp.id,
		    		 (SELECT project_name FROM `ln_project` WHERE br_id =bp.projectId LIMIT 1) AS branch_name,
		    		 (SELECT b.budgetTitle FROM st_budget_type AS b WHERE b.id=bi.budgetTypeId LIMIT 1) AS budgetType,
		    		 bi.budgetTitle AS budgetTitle,
		    		 bp.totalBudget,bp.budgetAlert,
			    	 bp.createDate,
			    	(SELECT first_name FROM rms_users AS u WHERE u.id = bp.userId LIMIT 1) AS user ,
		    		(SELECT name_en FROM ln_view WHERE TYPE=3 AND key_code = bp.status LIMIT 1) AS status
    			FROM 
    				$this->_name AS bp,
    				st_budget_item AS bi
    	 WHERE bp.budgetId=bi.id ";
    	
    	
    	$from_date =(empty($search['start_date']))? '1': " bp.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " bp.createDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where="";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	if($search['branch_id']>-1){
    		$where.= " AND bp.projectId = ".$search['branch_id'];
    	}
    	if($search['budgetItem']>0){
    		$where.= " AND bp.budgetId = ".$search['budgetItem'];
    	}
    	if($search['budgetType']>0){
    		$where.= " AND bi.budgetTypeId = ".$search['budgetType'];
    	}
    	
    	$order=' ORDER BY bp.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
    function addAmountBudgetItem($data){
    	
    $db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
						$arr = array(
								'projectId'=>$data['branch_id'],
								'budgetId'=>$data['budgetItem'.$i],
								'totalBudget'=>$data['budgetAmount'.$i],
								'budgetAlert'=>$data['qtyAmount'.$i],
								'createDate'=>date('Y-m-d'),
								'userId'=>$this->getUserId(),
						);
						$this->insert($arr);
				}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/budget/setup/add");
    	}
    }
//     function updateData($data){
    	 
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	try
//     	{
//     		$arr = array(
//     				''=>''
    
//     		);
    		
//     		//$this->_name='';
//     		$where = 'client_id = '.$data['id'];
// 			$this->update($arr, $where);
//     		$db->commit();
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     	}
//     }
//     function getDataRow($recordId){
//     	$db = $this->getAdapter();
    	
//     	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
//     	return $db->fetchRow($sql);
//     }
		function addBudgetExpense($data){
			$arr = array(
					'projectId'=>$data['branch_id'],
					'type'=>$data['type'],
					'transactionId'=>$data['transactionId'],
					'createDate'=>date('Y-m-d'),
					'userId'=>$this->getUserId(),
			);
			$this->_name='st_budget_expense';
			return $this->insert($arr);
		}
		
		function addBudgetExpenseDetail($data){
			
			$dbs = new Application_Model_DbTable_DbGlobalStock();
			$rsStock = $dbs->getProductInfoByLocation(array('productId'=>$data['productId']));
			if(!empty($rsStock)){
				$arr = array(
					'budgetExpenseId'=>$data['budgetExpenseId'],
					'subtransactionId'=>$data['subtransactionId'],
					'productId'=>$data['productId'],
					'budgetItemId'=>$rsStock['budgetId'],
					'qty'=>$data['qty'],
					'price'=>$data['price'],
					'totalDiscount'=>$data['totalDiscount'],
					'total'=>($data['price']*$data['qty'])-$data['totalDiscount'],
				);
					
				$this->_name='st_budget_expense_detail';
				$this->insert($arr);
			}
		}
		function reverBudgetExpense($transactionId){
			$db = $this->getAdapter();
			$sql="SELECT id FROM st_budget_expense WHERE transactionId=".$transactionId." LIMIT 1";
			$id = $db->fetchOne($sql);
			if(!empty($id)){
				$this->_name='st_budget_expense_detail';
				$where="budgetExpenseId=".$id;
				$this->delete($where);
					
				$this->_name='st_budget_expense';
				$where="id=".$transactionId;
				$this->delete($where);
				
			}
			
			
		}
		
		
}