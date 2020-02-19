<?php
class Rent_Model_DbTable_DbRefund extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_rent_refund';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllChequeIssue(){
    	$db = $this->getAdapter();
    	$sql = " SELECT DISTINCT cheque_issuer as name,cheque_issuer as id FROM `ln_rent_refund` WHERE cheque_issuer!='' ORDER BY cheque_issuer ASC ";
    	return $db->fetchAll($sql);
    }
    function getRefundNo($branch_id){
    	$db = $this->getAdapter();
//     	$prefix = $this->getPrefixCodeByBranch($branch_id);
    
    	$sql = " SELECT count(id) FROM ln_rent_refund WHERE branch_id = $branch_id";
    	$amount = $db->fetchOne($sql);
    
    	$pre = 'incr:';
    	$result = $amount + 1;
    	$length = strlen((int)$result);
    	for($i = $length;$i < 3 ; $i++){
    		$pre.='0';
    	}
    	return $pre.$result;
    }
    function addRefund($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$invoice = $this->getRefundNo($data['branch_id']);
    		$_arr= array(
    				'refund_no'		=> $invoice,
    				'branch_id'		=> $data['branch_id'],
    				'rent_id'		=> $data['rent_id'],
    				'customer_id'	=> $data['customer_id'],
    				
    				'refund_date'		=> $data['refund_date'],
    				'payment_method'	=> $data['payment_method'],
    				'cheque'		=> $data['cheque'],
    				'cheque_issuer'	=> $data['cheque_issuer'],
    				'total_amount'	=> $data['total_amount'],
    				'note'			=> $data['note'],
    				'status'		=> 1,
    				'user_id'		=> $this->getUserId(),
    				'create_date'   => date('Y-m-d H:i:s'),
    				'modify_date'   => date('Y-m-d H:i:s'),
    		);
    		$this->_name = "ln_rent_refund";
    		$this->insert($_arr);
    		
    		$arr = array(
    				'is_cancel'	=> 2,// update rent property is already refund
    		);
    		$this->_name = "ln_rent_property";
    		$where=" id = ".$data['rent_id'];
    		$this->update($arr, $where);
    		
    		$_db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    function updateRefund($data){
    	$_db= $this->getAdapter();
    	$_db->beginTransaction();
    	try{
    		$id = $data['id'];
    		//update old rent
    		$row = $this->getRefundById($id);
    		if (!empty($row)){
    			$arr = array(
    					'is_cancel'	=> 0,
    			);
    			$this->_name = "ln_rent_property";
    			$where=" id = ".$row['rent_id'];
    			$this->update($arr, $where);
    		}
    		
    		$_arr= array(
    				'branch_id'		=> $data['branch_id'],
    				'rent_id'		=> $data['rent_id'],
    				'customer_id'	=> $data['customer_id'],
    
    				'refund_date'		=> $data['refund_date'],
    				'payment_method'	=> $data['payment_method'],
    				'cheque'		=> $data['cheque'],
    				'cheque_issuer'	=> $data['cheque_issuer'],
    				'total_amount'	=> $data['total_amount'],
    				'note'			=> $data['note'],
    				'status'		=> $data['status'],
    				'user_id'		=> $this->getUserId(),
    				'modify_date'   => date('Y-m-d H:i:s'),
    		);
    		$this->_name = "ln_rent_refund";
    		$where=" id = ".$id;
    		$this->update($_arr, $where);
    
    		if ($data['status']==1){
	    		$arr = array(
	    				'is_cancel'	=> 2,// update rent property is already refund
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
    
    function getAllRefund($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$from_date =(empty($search['start_date']))? '1': " re.refund_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " re.refund_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$sql=" SELECT 
					re.id,
					(SELECT `p`.`project_name` FROM `ln_project` AS p WHERE `p`.`br_id` = `re`.`branch_id` LIMIT 1) AS `branch_name`,
					CONCAT((SELECT `c`.`name_kh` FROM ln_client AS c WHERE c.client_id = `re`.`customer_id` LIMIT 1),' ',`p`.`land_address`,'-',`p`.`street`) AS rent_no,
					re.refund_date,
					(SELECT name_kh FROM `ln_view` WHERE TYPE=26 AND key_code=re.payment_method LIMIT 1) AS payment_type,
					cheque,
					re.total_amount,
					(SELECT  first_name FROM rms_users WHERE id=re.user_id LIMIT 1 ) AS user_name
    	";
    	$sql.= $dbp->caseStatusShowImage("re.status");
    	$sql.=" FROM 
					`ln_rent_refund` AS re,
					`ln_rent_property` AS rp,
					ln_properties AS p
				WHERE 
					rp.id = re.rent_id
					AND rp.house_id = p.id   ";
    
    	if (!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " (SELECT `c`.`name_kh` FROM ln_client AS c WHERE c.client_id = `rp`.`client_id` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
    		$s_where[] = " re.total_amount LIKE '%{$s_search}%'";
    		$s_where[] = " re.cheque LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	if($search['branch_id']>0){
    		$where.= " AND `re`.`branch_id` = ".$search['branch_id'];
    	}
    	if(!empty($search['customer_id'])){
    		$where.= " AND `re`.`customer_id` = ".$search['customer_id'];
    	}
    	if($search['payment_method']>0){
    		$where.= " AND re.payment_method = ".$search['payment_method'];
    	}
    	if (!empty($search['cheque_issuer'])){
    		$where.= " AND re.cheque_issuer = '".$search['cheque_issuer']."'";
    	}
    	if($search['status']>-1){
    		$where.= " AND `re`.`status` = ".$search['status'];
    	}
    	$where.=$dbp->getAccessPermission("re.branch_id");
    	$order=" ORDER BY re.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getRefundById($id){
    	$db = $this->getAdapter();
    	$sql = " SELECT * FROM `ln_rent_refund` WHERE id=$id ";
    	return $db->fetchRow($sql);
    }
}