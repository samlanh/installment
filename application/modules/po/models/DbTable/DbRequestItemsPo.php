<?php

class Po_Model_DbTable_DbRequestItemsPo extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_purchasing';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getPendingPoItemsRequestApproved($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
			
		$sql="
			SELECT 
				(SELECT b.project_name FROM `ln_project` AS b WHERE b.br_id=rq.projectId LIMIT 1) AS projectName
				,rq.requestNo
				,rq.requestNoLetter
				,rq.purpose
				,rq.date AS requestDate
				,p.proName
				,rqd.*
			FROM `st_request_po_detail` AS rqd
				JOIN `st_request_po` AS rq ON rq.id = rqd.requestId 
				LEFT JOIN `st_product` AS p ON rqd.proId = p.proId 
			
		";
		$sql.=" WHERE 
				rqd.isCompletedPO  = 0 
				AND rq.approveStatus =1
				AND rqd.approvedStatus =1 
			";
		$sql.=" ";
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " rq.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rq.date <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(($search['branch_id'])>0){
    		$where.= " AND rq.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['requestId'])){
			$where.= " AND rq.projectId = ".$search['requestId'];
		}
		
    	$order=" ORDER BY rqd.requestId ASC ";
    	$where.=$dbGb->getAccessPermission("rq.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }

   function submitClosingItemsForPO($data){
      	$db = $this->getAdapter();
      	if(!empty($data['id_selected'])){
      		$ids = explode(',', $data['id_selected']);
      		$key = 1;
      		$arr = array(
      				"isCompletedPO"	=>1,
      				"closingBy"		=>$this->getUserId(),
      				"modifyDate"	=>date("Y-m-d H:i:s"),
      		);
      		foreach ($ids as $i){
      			$this->_name="st_request_po_detail";
      			$where="id= ".$data['id_'.$i];
      			$this->update($arr, $where);
      		}
      	}
      }
}