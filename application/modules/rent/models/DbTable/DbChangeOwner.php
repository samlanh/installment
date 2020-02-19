<?php
class Rent_Model_DbTable_DbChangeOwner extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_rent_changeowner';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getChangeOnwerNo($branch_id){
    	$db = $this->getAdapter();
//     	$prefix = $this->getPrefixCodeByBranch($branch_id);
    
    	$sql = " SELECT count(id) FROM ln_rent_changeowner WHERE branch_id = $branch_id";
    	$amount = $db->fetchOne($sql);
    
    	$pre = 'incr:';
    	$result = $amount + 1;
    	$length = strlen((int)$result);
    	for($i = $length;$i < 3 ; $i++){
    		$pre.='0';
    	}
    	return $pre.$result;
    }
    function addChangeOwner($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$invoice = $this->getChangeOnwerNo($data['branch_id']);
    		$_arr= array(
    				'change_no'		=> $invoice,
    				'branch_id'		=> $data['branch_id'],
    				'rent_id'		=> $data['rent_id'],
    				'from_customer'	=> $data['from_customer'],
    				'property_id'		=> $data['property_id'],
    				
    				'to_customer'	=> $data['to_customer'],
    				'change_date'		=> $data['change_date'],
    				'agreement_date'	=> $data['agreement_date'],
    				'reason'	=> $data['reason'],
    				'note'			=> $data['note'],
    				'status'		=> 1,
    				
    				'user_id'		=> $this->getUserId(),
    				'create_date'   => date('Y-m-d H:i:s'),
    				'modify_date'   => date('Y-m-d H:i:s'),
    		);
    		$this->_name = "ln_rent_changeowner";
    		$this->insert($_arr);
    		
    		$arr = array(
    				'client_id'	=> $data['to_customer'],
//     				'change_date'		=> $data['change_date'],
    				'agreement_date'	=> $data['agreement_date'],
    		);
    		$this->_name = "ln_rent_property";
    		$where=" id = ".$data['rent_id'];
    		$this->update($arr, $where);
    		
    		$_db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function updateChangeOwner($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$id = $data['id'];
    		//update old rent
    		$row = $this->getChangeOwnerById($id);
    		if (!empty($row)){
    			$arr = array(
    					'client_id'	=> $row['from_customer'],
//     					'agreement_date'	=> $data['agreement_date'],
    			);
    			$this->_name = "ln_rent_property";
    			$where=" id = ".$row['rent_id'];
    			$this->update($arr, $where);
    		}
    		
    		$_arr= array(
    				'branch_id'		=> $data['branch_id'],
    				'rent_id'		=> $data['rent_id'],
    				'from_customer'	=> $data['from_customer'],
    				'property_id'	=> $data['property_id'],
    				
    				'to_customer'		=> $data['to_customer'],
    				'change_date'		=> $data['change_date'],
    				'agreement_date'	=> $data['agreement_date'],
    				'reason'		=> $data['reason'],
    				'note'			=> $data['note'],
    				'status'		=> $data['status'],
    				
    				'user_id'		=> $this->getUserId(),
    				'modify_date'   => date('Y-m-d H:i:s'),
    		);
    		$this->_name = "ln_rent_changeowner";
    		$where=" id = ".$id;
    		$this->update($_arr, $where);
    
    		if ($data['status']==1){
	    		$arr = array(
    				'client_id'	=> $data['to_customer'],
    				'agreement_date'	=> $data['agreement_date'],
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
    
    function getAllChangeOwner($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$from_date =(empty($search['start_date']))? '1': " rc.change_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rc.change_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$sql=" SELECT 
				rc.id,
				(SELECT `p`.`project_name` FROM `ln_project` AS p WHERE `p`.`br_id` = `rc`.`branch_id` LIMIT 1) AS `branch_name`,
				CONCAT(p.land_address,',',p.street) AS property,
				(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`from_customer` LIMIT 1) AS from_customer,
				(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`to_customer` LIMIT 1) AS to_customer,
				rc.change_date,
				(SELECT  first_name FROM rms_users WHERE id=rc.user_id LIMIT 1 ) AS user_name
    	";
    	$sql.= $dbp->caseStatusShowImage("rc.status");
    	$sql.=" FROM `ln_rent_changeowner` AS rc,
					 `ln_rent_property` AS rp,
					  ln_properties AS p
				WHERE 
					  rc.rent_id = rp.id
					AND p.id = rc.property_id   ";
    
    	if (!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`from_customer` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`to_customer` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	if(!empty($search['branch_id'])){
    		$where.= " AND `rc`.`branch_id` = ".$search['branch_id'];
    	}
    	if(!empty($search['from_customer'])){
    		$where.= " AND `rc`.`from_customer` = ".$search['from_customer'];
    	}
    	if(!empty($search['to_customer'])){
    		$where.= " AND `rc`.`to_customer` = ".$search['to_customer'];
    	}
    	if($search['status']>-1){
    		$where.= " AND `rc`.`status` = ".$search['status'];
    	}
    	$where.=$dbp->getAccessPermission("rc.branch_id");
    	$order=" ORDER BY rc.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getChangeOwnerById($id){
    	$db = $this->getAdapter();
    	$sql = " SELECT * FROM `ln_rent_changeowner` WHERE id=$id ";
    	return $db->fetchRow($sql);
    }
}