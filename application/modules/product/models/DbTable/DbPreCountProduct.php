<?php

class Product_Model_DbTable_DbPreCountProduct extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_precount_product';
    /*public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }*/
    function getAllProductClosing($search){
    	if(!isset($search['btn_search'])){
    		return array();
    	}
    	$sql=" SELECT l.id,
				(SELECT project_name FROM `ln_project` WHERE br_id=l.projectId LIMIT 1) AS projectName,
				CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`,
				l.count_qty, l.closing_date, l.note
				FROM 
					`st_product` AS p ,
					`st_precount_product` AS l
					WHERE 
						 p.proId=l.proId ";

		 $from_date =(empty($search['start_date']))? '1': " l.closing_date >= '".$search['start_date']." 00:00:00'";
		 $to_date = (empty($search['end_date']))? '1': " l.closing_date <= '".$search['end_date']." 23:59:59'";				 
		 $where_date = " AND ".$from_date." AND ".$to_date;
    	
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " p.proName LIKE '%{$s_search}%'";
    		$s_where[] = " p.proCode LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['branch_id']>0){
    		$where.= " AND l.projectId = ".$search['branch_id'];
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('l.projectId');
    	
    	$order=' ORDER BY l.id DESC ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
    function addPreCountProduct($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
						$arr = array(
								'projectId'=>$data['branch_id'],
								'proId'=>$data['proId'.$i],
								'count_qty'=>$data['count_qty'.$i],
								'closing_date'=>$data['closing_date'.$i],
								'note'=>$data['note'.$i],
						);
						$this->_name='st_precount_product';
						$tranId = $this->insert($arr);
					}
					
				//	$dbs->addProductHistoryQty($data['branch_id'], $data['proId'.$i], 1, $data['qtyInit'.$i],$tranId);
			}
    		
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/product/closingstock/add",2);
    	}
    }
    function updateData($data){
    	 
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
				'count_qty'=>$data['count_qty'],
				'closing_date'=>$data['closing_date'],
				'note'=>$data['note'],
			);
			$this->_name='st_precount_product';
			$where="id=".$data['id'];
			$this->update($arr, $where);
			
			/*
			$arr = array(
					'qty'=>$data['beginingQty']
				);
			$this->_name='st_product_story';
			$where="tranType =1 AND transId=".$data['id'];
			$this->update($arr, $where);
			*/

    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql="SELECT l.*,
		(SELECT project_name FROM `ln_project` WHERE br_id=l.projectId LIMIT 1) AS projectName,
		(SELECT proName FROM `st_product` p WHERE p.proId=l.proId LIMIT 1) AS proName
		FROM `st_precount_product` l
		 WHERE l.id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }

}