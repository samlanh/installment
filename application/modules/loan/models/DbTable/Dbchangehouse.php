<?php

class Loan_Model_DbTable_Dbchangehouse extends Zend_Db_Table_Abstract
{

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    	 
    }
   function getAllChangeHouse($search){
   	$from_date =(empty($search['start_date']))? '1': " s.change_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " s.change_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	$sql="SELECT cp.id,
   	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.from_branchid LIMIT 1) AS from_branch,
	(SELECT sale_number FROM `ln_sale` WHERE id=cp.sale_id LIMIT 1) AS sale_number,
	c.name_kh,
	(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_houseid LIMIT 1) from_property,
	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branchid LIMIT 1) AS to_branch,
	(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_houseid LIMIT 1) to_propertype,
	cp.change_date,cp.status
	FROM `ln_change_house` AS cp,`ln_client` c WHERE c.client_id=cp.client_id ";
   	
   	$from_date =(empty($search['start_date']))? '1': " cp.change_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " cp.change_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	if(!empty($search['adv_search'])){
   		$s_where = array();
//    		$s_search = addslashes(trim($search['adv_search']));
//    		$s_where[] = " cp.receipt_no LIKE '%{$s_search}%'";
//    		$s_where[] = " p.land_code LIKE '%{$s_search}%'";
//    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
//    		$s_where[] = " c.client_number LIKE '%{$s_search}%'";
//    		$s_where[] = " c.name_en LIKE '%{$s_search}%'";
//    		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
//    		$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
//    		$s_where[] = " s.comission LIKE '%{$s_search}%'";
//    		$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
//    		$where .=' AND ( '.implode(' OR ',$s_where).')';
   	}
   	if($search['status']>-1){
   		$where.= " AND cp.status = ".$search['status'];
   	}
   	if(($search['client_name'])>0){
   		$where.= " AND `cp`.`client_id`=".$search['client_name'];
   	}
   	if(($search['branch_id'])>0){
   		$where.= " AND ( cp.from_branchid = ".$search['branch_id']." OR cp.to_branchid = ".$search['branch_id']." )";
   	}
   	
   	$order = " ORDER BY id DESC ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql.$where.$order);
   }
   
   public function addChangeHouse($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{//need add schedule
    		$dbs = new Loan_Model_DbTable_DbLandpayment();
    		$id = $data['loan_number'];
    		$rows = $dbs->getTranLoanByIdWithBranch($id);
    		$arr = array(
    				'from_branchid'=>$data['branch_id'],
    				'from_houseid'=>$rows['house_id'],
    				'sale_id'=>$id,
    				'client_id'=>$data['member'],
    				'change_date'=>date('Y-m-d'),//$data['date_buy'],
    				'to_branchid'=>$data['to_branch_id'],
    				'to_houseid'=>$data['to_land_code'],
    				'note'=>'',//$data['note'],
    				'user_id'=>$this->getUserId()
    				);
	    		$this->_name="ln_change_house";
	    		$changeid = $this->insert($arr);

    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'house_id'=>$data["to_land_code"],
    		);
	    		$where = " id = ".$data['loan_number'];
	    		$this->_name="ln_sale";
	    		$id = $this->update($arr, $where);//add group loan
	    		
	    		$this->_name="ln_properties";
	    		$where=" id=".$data['land_code'];
	    		$arr = array(
	    				'is_lock'=>0
	    				);
	    		$this->update($arr, $where);//unlock old house
	    		
	    		$where=" id=".$data['to_land_code'];
	    		$arr = array(
	    				'is_lock'=>1
	    		);
	    		$this->update($arr, $where);//lock new house
    			$db->commit();
    			return 1;
    			
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }
    public function UpdateChangeHouse($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{//need add schedule
    		$dbs = new Loan_Model_DbTable_DbLandpayment();
    		$id = $data['loan_number'];
    		$rows = $dbs->getTranLoanByIdWithBranch($id);
    		$arr = array(
    				'from_branchid'=>$data['branch_id'],
    				'from_houseid'=>$rows['house_id'],
    				'sale_id'=>$id,
    				'client_id'=>$data['member'],
    				'change_date'=>date('Y-m-d'),//$data['date_buy'],
    				'to_branchid'=>$data['to_branch_id'],
    				'to_houseid'=>$data['to_land_code'],
    				'note'=>'',//$data['note'],
    				'user_id'=>$this->getUserId()
    		);
    		$this->_name="ln_change_house";
    		$where=" id =".$data['id'];
    		$changeid = $this->update($arr,$where);
    
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'house_id'=>$data["to_land_code"],
    		);
    		$where = " id = ".$data['loan_number'];
    		$this->_name="ln_sale";
    		$id = $this->update($arr, $where);//add group loan
    		 
    		$this->_name="ln_properties";
    		$where=" id=".$data['land_code'];
    		$arr = array(
    				'is_lock'=>0
    		);
    		$this->update($arr, $where);//unlock old house
    		 
    		$where=" id=".$data['to_land_code'];
    		$arr = array(
    				'is_lock'=>1
    		);
    		$this->update($arr, $where);//lock new house
    		$db->commit();
    		return 1;
    		 
    	}catch (Exception $e){
    		$db->rollBack();
    		$err =$e->getMessage();
    		echo $err;exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
    function getTransferProject($id){
    	$sql=" select * from ln_change_house where id= $id limit 1";
    	$db = $this->getAdapter();
    	return $db->fetchRow($sql);
    }

}
  


