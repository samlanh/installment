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
					l.id,
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
    	
    	$order=' ORDER BY l.id DESC ';
    	
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
						$tranId = $this->insert($arr);
					}
					
					$dbs->addProductHistoryQty($data['branch_id'], $data['proId'.$i], 1, $data['qtyInit'.$i],$tranId);
				}
    		}
    		
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/product/initqty/add",2);
    	}
    }
    function updateData($data){
    	 
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
				'qty'=>$data['beginingQty'],
				'costing'=>$data['costing'],
				'qtyAlert'=>$data['qtyAlert'],
			);
			$this->_name='st_product_location';
			$where="id=".$data['id'];
			$this->update($arr, $where);
			
			
			$arr = array(
					'qty'=>$data['beginingQty']
				);
			$this->_name='st_product_story';
			$where="tranType =1 AND transId=".$data['id'];
			$this->update($arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT l.*,
    		(SELECT count(id) FROM `st_product_story` WHERE projectId=l.projectId AND proId=l.proId ) AS recordHistory,
			(SELECT project_name FROM `ln_project` WHERE br_id=l.projectId LIMIT 1) as projectName,
			(SELECT proName FROM `st_product` p WHERE p.proId=l.proId LIMIT 1) as proName
    	FROM $this->_name l WHERE l.id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getProductMovement($productId,$ProjectId){
    	    	$db = $this->getAdapter();
    	    	$session_lang=new Zend_Session_Namespace('lang');
    	    	$lang_id=$session_lang->lang_id;
    	    	$strLable ='name_kh' ;
    	    	if($lang_id==2){
    	    		$strLable ='name_en' ;
    	    	}
    	    	$sql=" SELECT id,
						(SELECT project_name FROM `ln_project` WHERE br_id=ss.projectId LIMIT 1) AS projectName,
							projectId,
							proId,
							tranType,
							(SELECT $strLable FROM `st_view` WHERE TYPE=6 AND key_code=tranType LIMIT 1) AS tranTypeValue,
							qty,
							(SELECT first_name FROM rms_users as u WHERE u.id = ss.userId LIMIT 1) AS user,
							transDate
						 FROM `st_product_story` ss
						 
						 WHERE 
						 ss.proId=$productId
						 AND ss.projectId=$ProjectId 
						    ORDER BY id DESC   ";
    	    	return $db->fetchAll($sql);
    }

	function getProductCosting($productId,$ProjectId){
		$db = $this->getAdapter();
		$session_lang=new Zend_Session_Namespace('lang');
		$lang_id=$session_lang->lang_id;
		$strLable ='name_kh' ;
		if($lang_id==2){
			$strLable ='name_en' ;
		}
		$sql=" SELECT * FROM `st_product_costing`
				 
				 WHERE 
				 productId=$productId
				 AND projectId=$ProjectId 
					ORDER BY date DESC   ";
		return $db->fetchAll($sql);
}
   
}