<?php
class Report_Model_DbTable_DbStockMg extends Zend_Db_Table_Abstract
{
	function getRequestPOById($id=null){
		$db = $this->getAdapter();
		$sql=" 
			SELECT rq.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS user_name

			FROM st_request_po AS rq WHERE 1 ";
		$sql.=" AND rq.status = 1 ";
		if (!empty($id)){
			$sql.=" AND rq.id = $id LIMIT 1";
		}
		return $db->fetchRow($sql);
	}
	function getRequestPODetailById($rsData=null){
		$db = $this->getAdapter();
		
		$id=empty($rsData['id'])?0:$rsData['id'];
		$sql=" 	SELECT 
					rqd.*,p.proCode,
					p.proName,
					0 AS currentQty,
					'Kg' AS measureTitle 
				FROM `st_request_po_detail` as rqd, `st_product` AS p WHERE p.proId = rqd.proId ";
		if (!empty($id)){
			$sql.=" AND rqd.requestId = $id";
		}
		if (!empty($rsData['approvedrequest'])){
			$sql.=" AND rqd.adjustStatus = 1 ";
		}
		return $db->fetchAll($sql);
	}
}
