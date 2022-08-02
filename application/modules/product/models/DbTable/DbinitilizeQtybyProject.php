<?php

class Product_Model_DbTable_DbinitilizeQtybyProject extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_product_location';
    /*public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }*/
    function getAllProductLocation($search){
    	if(!isset($search['btn_search'])){
    		return array();
    	}
    	$sql=" SELECT 
					p.proId AS id,
					(SELECT project_name from `ln_project` WHERE br_id=l.projectId LIMIT 1) as projectName,
					CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`,
					p.barCode,
					l.qty AS currentQty,
					p.measureLabel AS measureTitle,
					l.costing,
					(SELECT c.categoryName FROM `st_category` as c WHERE c.id=p.categoryId LIMIT 1) categoryName
				FROM 
					`st_product` AS p ,
					$this->_name AS l
					WHERE 
						 p.proId=l.proId ";
    	
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " p.proName LIKE '%{$s_search}%'";
    		$s_where[] = " p.proCode LIKE '%{$s_search}%'";
    		$s_where[] = " p.barCode LIKE '%{$s_search}%'";
    		$s_where[] = " p.measureLabel LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['isCountStock']>-1){
    		$where.= " AND p.isCountStock = ".$search['isCountStock'];
    	}
    	if($search['categoryId']>0){
    		$where.= " AND p.categoryId = ".$search['categoryId'];
    	}
    	if($search['branch_id']>0){
    		$where.= " AND l.projectId = ".$search['branch_id'];
    	}
    	if($search['measureId']>0){
    		$where.= " AND p.budgetId = ".$search['measureId'];
    	}
    	
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('l.projectId');
    	
    	$order=' ORDER BY p.proName ASC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function addProductInitQty($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					
					$param= array(
							'branch_id'=>$data['branch_id'],
							'productId'=>$data['proId'.$i],
							);
					
					$result = $dbs->getProductInfoByLocation($param);
					
					if(empty($result)){
					
						$arr = array(
								'projectId'=>$data['branch_id'],
								'proId'=>$data['proId'.$i],
								'qty'=>$data['qtyInit'.$i],
								'costing'=>$data['costing'.$i],
								'qtyAlert'=>$data['qtyAlert'.$i],
						);
						$this->_name='st_product_location';
						$this->insert($arr);
					}
					
					$dbs->addProductHistoryQty($data['branch_id'], $data['proId'.$i], 1, $data['qtyInit'.$i]);
				}
    		}
    		
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/product/initqty/add");
    	}
    }
    function updateData($data){
    	 
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
    				''=>''
    
    		);
    		
    		//$this->_name='';
    		$where = 'client_id = '.$data['id'];
			$this->update($arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}