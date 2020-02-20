<?php
class Rent_Model_DbTable_DbCancel extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_rent_cancel';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getCancelNo($branch_id){
    	$db = $this->getAdapter();
//     	$prefix = $this->getPrefixCodeByBranch($branch_id);
    
    	$sql = " SELECT count(id) FROM ln_rent_cancel WHERE branch_id = $branch_id";
    	$amount = $db->fetchOne($sql);
    
    	$pre = 'incr:';
    	$result = $amount + 1;
    	$length = strlen((int)$result);
    	for($i = $length;$i < 3 ; $i++){
    		$pre.='0';
    	}
    	return $pre.$result;
    }
    function addCancelRental($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$invoice = $this->getCancelNo($data['branch_id']);
    		$_arr= array(
    				'cancel_no'		=> $invoice,
    				'branch_id'		=> $data['branch_id'],
    				'rent_id'		=> $data['rent_id'],
    				'customer_id'	=> $data['customer_id'],
    				'property_id'		=> $data['property_id'],
    				
    				'cancel_date'		=> $data['cancel_date'],
    				'reason'	=> $data['reason'],
    				'note'			=> $data['note'],
    				'status'		=> 1,
    				
    				'user_id'		=> $this->getUserId(),
    				'create_date'   => date('Y-m-d H:i:s'),
    				'modify_date'   => date('Y-m-d H:i:s'),
    		);
    		$this->_name = "ln_rent_cancel";
    		$this->insert($_arr);
    		
    		$arr = array(
    				'is_cancel'	=> 1,// update rent property is cancled
    		);
    		$this->_name = "ln_rent_property";
    		$where=" id = ".$data['rent_id'];
    		$this->update($arr, $where);
    		
    		$_db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function updateCancelRental($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$id = $data['id'];
    		//update old rent
    		$row = $this->getCancelRentalById($id);
    		if (!empty($row)){
    			$arr = array(
    					'is_cancel'	=> 0
    			);
    			$this->_name = "ln_rent_property";
    			$where=" id = ".$row['rent_id'];
    			$this->update($arr, $where);
    		}
    		
    		$_arr= array(
    				'branch_id'		=> $data['branch_id'],
    				'rent_id'		=> $data['rent_id'],
    				'customer_id'	=> $data['customer_id'],
    				'property_id'		=> $data['property_id'],
    				
    				'cancel_date'		=> $data['cancel_date'],
    				'reason'	=> $data['reason'],
    				'note'			=> $data['note'],
    				'status'		=> $data['status'],
    				
    				'user_id'		=> $this->getUserId(),
    				'modify_date'   => date('Y-m-d H:i:s'),
    		);
    		$this->_name = "ln_rent_cancel";
    		$where=" id = ".$id;
    		$this->update($_arr, $where);
    
    		if ($data['status']==1){
	    		$arr = array(
    				'is_cancel'	=> 1,// update rent property is cancled
	    		);
	    		$this->_name = "ln_rent_property";
	    		$where=" id = ".$data['rent_id'];
	    		$this->update($arr, $where);
    		}
    		$_db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function getAllCancelRental($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$from_date =(empty($search['start_date']))? '1': " rc.cancel_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rc.cancel_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$sql=" SELECT 
				rc.id,
				(SELECT `p`.`project_name` FROM `ln_project` AS p WHERE `p`.`br_id` = `rc`.`branch_id` LIMIT 1) AS `branch_name`,
				CONCAT((SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`customer_id` LIMIT 1),' ',p.land_address,',',p.street) AS rent_no,
				rc.cancel_date,
				(SELECT  first_name FROM rms_users WHERE id=rc.user_id LIMIT 1 ) AS user_name
    	";
    	$sql.= $dbp->caseStatusShowImage("rc.status");
    	$sql.=" FROM `ln_rent_cancel` AS rc,
					 `ln_rent_property` AS rp,
					  ln_properties AS p
				WHERE 
					  rc.rent_id = rp.id
					AND p.id = rc.property_id   ";
    
    	if (!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`customer_id` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	if(!empty($search['branch_id'])){
    		$where.= " AND `rc`.`branch_id` = ".$search['branch_id'];
    	}
    	if(!empty($search['customer_id'])){
    		$where.= " AND `rc`.`customer_id` = ".$search['customer_id'];
    	}
    	if($search['status']>-1){
    		$where.= " AND `rc`.`status` = ".$search['status'];
    	}
    	$where.=$dbp->getAccessPermission("rc.branch_id");
    	$order=" ORDER BY rc.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getCancelRentalById($id){
    	$db = $this->getAdapter();
    	$sql = " SELECT * FROM `ln_rent_cancel` WHERE id=$id ";
    	return $db->fetchRow($sql);
    }
}