<?php

class Loan_Model_DbTable_Dbtransfercash extends Zend_Db_Table_Abstract
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
   		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.branch_id LIMIT 1) AS from_branch,
		c.name_kh,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_property LIMIT 1) from_property,
		from_paid,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branch LIMIT 1) AS to_branch,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_property LIMIT 1) to_propertype,
		cp.trafer_date,cp.status
		FROM `ln_transfercash` AS cp,
   		`ln_client` c 
   		WHERE c.client_id=cp.from_clientid ";
   	
   	$from_date =(empty($search['start_date']))? '1': " cp.trafer_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " cp.trafer_date <= '".$search['end_date']." 23:59:59'";
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
   		$where.= " AND cp.from_clientid=".$search['client_name'];
   	}
   	if(($search['branch_id'])>0){
   		$where.= " AND ( cp.branch_id = ".$search['branch_id']." OR cp.branch_id = ".$search['branch_id']." )";
   	}
   	
   	$order = " ORDER BY id DESC ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql.$where.$order);
   }
   
   public function addTransfercash($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$dbs = new Loan_Model_DbTable_DbLandpayment();
    		$id = $data['loan_number'];
    		$rows = $dbs->getTranLoanByIdWithBranch($data['toloan_number']);//toland
    		
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'from_sale'=>$data['loan_number'],
    				'from_property'=>$data['land_code'],
    				'from_clientid'=>$data['member'],
    				'from_saleprice'=>$data['total_sold'],
    				'from_paid'=>$data['paid_before'],
    				'from_balance'=>$data['balance_before'],
    				
    				'to_branch'=>$data['to_branch_id'],
    				'to_sale'=>$data['toloan_number'],
    				'to_property'=>$rows['house_id'],
    				'to_saleprice'=>$data['land_price'],
    				'to_paid'=>$data['house_price'],
    				'to_balance'=>$data['to_total_sold'],
    				
    				'note'=>$data['transfer_note'],
    				'trafer_date'=>date('Y-m-d'),
    				'user_id'=>$this->getUserId()
    				);
	    		$this->_name="ln_transfercash";
	    		$transferid = $this->insert($arr);
	    		
	    		$arra = array(
	    				'sale_id' => $data['toloan_number'],
	    				'client_id' => $rows['client_id'],
	    			);
	    		$this->_name="ln_client_receipt_money";
	    		$where="sale_id=".$data['loan_number'];
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
  


