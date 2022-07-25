<?php

class Invpayment_Model_DbTable_DbIssueCheque extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_receive_cheque';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllIssueChequePayment($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		
		$sql="
			SELECT 
				reCh.id,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = reCh.projectId LIMIT 1) AS branch_name,
				reCh.receiveDate,
				reCh.receiverName,
				pt.paymentNo,
				spp.supplierName
				
			";
    	$sql.=$dbGb->caseStatusShowImage("reCh.status");
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=reCh.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_receive_cheque` AS reCh
					LEFT JOIN `st_payment` AS pt ON pt.id = reCh.paymentId 
					LEFT JOIN `st_supplier` AS spp ON spp.id = pt.supplierId 
				WHERE 1 
		";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " reCh.receiveDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " reCh.receiveDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " reCh.receiverName LIKE '%{$s_search}%'";
    		$s_where[] = " pt.paymentNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND reCh.status = ".$search['status'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND reCh.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND pt.supplierId = ".$search['supplierId'];
    	}
    	$order=' ORDER BY reCh.id DESC  ';
    	$where.=$dbGb->getAccessPermission("reCh.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
	
    function issueChequePaymentInvoice($_data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			
			$_arr=array(
    				'projectId'	  			=> $_data['branch_id'],
    				'paymentId'	    		=> $_data['paymentId'],
    				'receiveDate'			=> $_data['receiveDate'],
					
    				'receiverName'	  		=> $_data['receiverName'],
    				'note'      			=> $_data['note'],
    				
    				'createDate'			=> date("Y-m-d H:i:s"),
    				'modifyDate'	  		=> date("Y-m-d H:i:s"),
    				'status'				=> 1,
    				'userId'  				=>$this->getUserId(),
    		);	
			$this->_name ='st_receive_cheque';
    		$issueId =  $this->insert($_arr);			
    		
			
			$db->commit();
			return $issueId;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
	
	function editIssueChequePaymentInvoice($_data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_arr=array(
    				'projectId'	  			=> $_data['branch_id'],
    				'receiveDate'			=> $_data['receiveDate'],
    				'receiverName'	  		=> $_data['receiverName'],
    				'note'      			=> $_data['note'],
    				'modifyDate'	  		=> date("Y-m-d H:i:s"),
    				'status'				=> $_data['status'],
    				'userId'  				=>$this->getUserId(),
    		);	
			$this->_name ='st_receive_cheque';
			
			$paymentId = $_data['id'];
    		$where = " id = ".$paymentId;
    		$this->update($_arr, $where);
			
			
			
			$db->commit();
			return $paymentId;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
	
	
	
	function getDataRowIsseueCheque($recordId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();		
			$this->_name='st_receive_cheque';
			$sql=" SELECT pt.*";
			$sql.=" FROM $this->_name AS pt WHERE pt.id= ".$recordId;
			$sql.=$dbGb->getAccessPermission("pt.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	
	
	function receiveChequePaymentInvoice($_data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			
			$_arr=array(
    				'withdrawDate'	  			=> $_data['withdrawDate'],
    				'noteWithdraw'				=> $_data['noteWithdraw'],
    				'statusWithdraw'	  		=> $_data['statusWithdraw'],
    				'drawUserId'  				=>$this->getUserId(),
    		);	
			$this->_name ='st_receive_cheque';
			
			$issueId = $_data['issueId'];
    		$where = " id = ".$issueId;
    		$this->update($_arr, $where);
				
    		
			$db->commit();
			return $issueId;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
	
   
}