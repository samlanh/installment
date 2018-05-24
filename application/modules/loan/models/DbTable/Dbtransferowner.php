<?php

class Loan_Model_DbTable_Dbtransferowner extends Zend_Db_Table_Abstract
{

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    	 
    }
//    function getAllChangeHouse($search){
//    	$from_date =(empty($search['start_date']))? '1': " s.change_date >= '".$search['start_date']." 00:00:00'";
//    	$to_date = (empty($search['end_date']))? '1': " s.change_date <= '".$search['end_date']." 23:59:59'";
//    	$where = " AND ".$from_date." AND ".$to_date;
//    	$sql="SELECT cp.id,
//    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.branch_id LIMIT 1) AS from_branch,
// 		c.name_kh,
// 		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_property LIMIT 1) from_property,
// 		from_paid,
// 		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branch LIMIT 1) AS to_branch,
// 		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_property LIMIT 1) to_propertype,
// 		cp.trafer_date,cp.status
// 		FROM `ln_transfercash` AS cp,
//    		`ln_client` c 
//    		WHERE c.client_id=cp.from_clientid ";
   	
//    	$from_date =(empty($search['start_date']))? '1': " cp.trafer_date >= '".$search['start_date']." 00:00:00'";
//    	$to_date = (empty($search['end_date']))? '1': " cp.trafer_date <= '".$search['end_date']." 23:59:59'";
//    	$where = " AND ".$from_date." AND ".$to_date;
//    	if(!empty($search['adv_search'])){
//    		$s_where = array();
//    	}
//    	if($search['status']>-1){
//    		$where.= " AND cp.status = ".$search['status'];
//    	}
//    	if(($search['client_name'])>0){
//    		$where.= " AND cp.from_clientid=".$search['client_name'];
//    	}
//    	if(($search['branch_id'])>0){
//    		$where.= " AND ( cp.branch_id = ".$search['branch_id']." OR cp.branch_id = ".$search['branch_id']." )";
//    	}
   	
//    	$order = " ORDER BY id DESC ";
//    	$db = $this->getAdapter();
//    	return $db->fetchAll($sql.$where.$order);
//    }
   
   public function addTransferOwner($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
//     		$arr = array(
//     				'branch_id'=>$data['branch_id'],
//     				'from_sale'=>$data['loan_number'],
//     				'from_property'=>$data['land_code'],
//     				'from_clientid'=>$data['member'],
//     				'from_saleprice'=>$data['total_sold'],
//     				'from_paid'=>$data['paid_before'],
//     				'from_balance'=>$data['balance_before'],
    				
//     				'to_branch'=>$data['to_branch_id'],
//     				'to_sale'=>$data['toloan_number'],
//     				'to_property'=>$rows['house_id'],
//     				'to_saleprice'=>$data['land_price'],
//     				'to_paid'=>$data['house_price'],
//     				'to_balance'=>$data['to_total_sold'],
    				
//     				'note'=>$data['transfer_note'],
//     				'trafer_date'=>date('Y-m-d'),
//     				'user_id'=>$this->getUserId()
//     				);
// 	    		$this->_name="ln_transfercash";
// 	    		$transferid = $this->insert($arr);
	    		
	    		$arra = array(
	    				'client_id' => $data['to_customer'],
	    			);
	    		$this->_name="ln_client_receipt_money";
	    		$where="sale_id=".$data['loan_number'];
	    		$this->update($arra, $where);
	    		
	    		$arra = array(
	    				'client_id' => $data['to_customer'],
	    				'agreement_date'=>$data['agreement_date']
	    		);
	    		$this->_name="ln_sale";
	    		$where="id=".$data['loan_number'];
	    		$this->update($arra, $where);
    		
    			$db->commit();
    			return 1;
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
    			echo $err;exit();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }
//     function getTransferProject($id){
//     	$sql=" select * from ln_change_house where id= $id limit 1";
//     	$db = $this->getAdapter();
//     	return $db->fetchRow($sql);
//     }
}