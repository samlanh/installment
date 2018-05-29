<?php

class Loan_Model_DbTable_Dbtransferowner extends Zend_Db_Table_Abstract
{

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    	 
    }
   function getAllTranferOwner($search){
	  
	   	$sql="SELECT w.id,
	   		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=w.branch_id LIMIT 1) AS from_branch,
			c.name_kh,
			(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=w.house_id LIMIT 1) from_property,
			w.sold_price,
			w.paid_before,
			w.balance,
			(SELECT cc.name_kh FROM `ln_client` AS cc WHERE cc.client_id=w.to_customer LIMIT 1) AS to_branch,
			w.note,w.change_date,w.status
			FROM 
			`ln_change_owner` AS w,
	   		`ln_client` c 
	   		WHERE c.client_id=w.from_customer ";
	   	
	   	$from_date =(empty($search['start_date']))? '1': " w.change_date >= '".$search['start_date']." 00:00:00'";
	   	$to_date = (empty($search['end_date']))? '1': " w.change_date <= '".$search['end_date']." 23:59:59'";
	   	$where = " AND ".$from_date." AND ".$to_date;
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   	}
	   	if($search['status']>-1){
	   		$where.= " AND w.status = ".$search['status'];
	   	}
	   	if(($search['branch_id'])>0){
	   		$where.= " AND w.branch_id=".$search['branch_id'];
	   	}
	   	if(($search['client_name'])>0){
	   		$where.= " AND ( w.from_customer= ".$search['client_name']." OR w.to_customer = ".$search['client_name']." )";
	   	}
	   	
	   	$order = " ORDER BY id DESC ";
	   	$db = $this->getAdapter();
	   	return $db->fetchAll($sql.$where.$order);
   }
   
   public function addTransferOwner($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'sale_id'=>$data['loan_number'],
    				'from_customer'=>$data['member'],
    				'house_id'=>$data['land_code'],
    				'to_customer'=>$data['to_customer'],
    				'agreement_date'=>$data['agreement_date'],
    				'note'=>$data['note'],
    				'change_date'=>$data['change_date'],
    				'sold_price'=>$data['total_sold'],
    				'paid_before'=>$data['paid_before'],
    				'balance'=>$data['balance_before'],
    				'user_id'=>$this->getUserId()
    				);
    			$this->_name="ln_change_owner";
	    		$transferid = $this->insert($arr);
	    		
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