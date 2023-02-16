<?php

class Product_Model_DbTable_DbPreCountProduct extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_precount_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllProductClosing($search){
    	$sql=" SELECT l.id,
				(SELECT project_name FROM `ln_project` WHERE br_id=l.projectId LIMIT 1) AS projectName,
				l.inputDate, 
				(SELECT first_name FROM rms_users WHERE id=l.userId LIMIT 1 ) AS user_name, 
				l.note,
				(SELECT name_en FROM ln_view WHERE TYPE=3 AND key_code = l.status LIMIT 1) AS status
				FROM 
					`st_precount_product` AS l
					WHERE 
						 1 ";

		 $from_date =(empty($search['start_date']))? '1': " l.inputDate >= '".$search['start_date']." 00:00:00'";
		 $to_date = (empty($search['end_date']))? '1': " l.inputDate <= '".$search['end_date']." 23:59:59'";				 
		 $where_date = " AND ".$from_date." AND ".$to_date;
    	
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " p.note LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['branch_id']>0){
    		$where.= " AND l.projectId = ".$search['projectId'];
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
    		
    		$arr = array(
    				'projectId'=>$data['branch_id'],
    				'inputDate'=>$data['date'],
    				'createDate'=>date('Y-m-d h:i'),
    				'note'=>$data['note'],
    				'userId'=>$this->getUserId()
    		);
    		$tranId = $this->insert($arr);
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		
    		if(!empty($data['identity'])){
    			
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					
					$arr = array(
							'branch_id'=>$data['branch_id'],
							'productId'=>$data['proId'.$i],
					);
					$rsProduct = $dbs->getProductInfoByLocation($arr);
					
					if(!empty($rsProduct)){
						$arr = array(
								'countId'=>$tranId,
								'proId'=>$data['proId'.$i],
								'currentQty'=>$rsProduct['currentQty'],//$data['currentQty'.$i],
								'countQty'=>$data['count_qty'.$i],
								'closingDate'=>$data['closing_date'.$i],
								'note'=>$data['note'.$i],
						);
						$this->_name='st_precount_product_detail';
						$this->insert($arr);
					}
				}
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
			$id = $data['id'];
    		$arr = array(
					'projectId'=>$data['branch_id'],
    				'inputDate'=>$data['date'],
    				'note'=>$data['note'],
					'status'=>$data['status'],
    				'userId'=>$this->getUserId()
			);
			$this->_name='st_precount_product';
			$where="id=".$id;
			$this->update($arr, $where);

			if($data['status']==1){

				$identitys = explode(',',$data['identity']);
				$detailId="";
				if (!empty($identitys)){
					foreach ($identitys as $i){
						if (empty($detailId)){
							if (!empty($data['detailId'.$i])){
								$detailId = $data['detailId'.$i];
							}
						}else{
							if (!empty($data['detailId'.$i])){
								$detailId= $detailId.",".$data['detailId'.$i];
							}
						}
					}
				}
				$this->_name='st_precount_product_detail';
				$whereDl = 'countId = '.$id;
				if (!empty($detailId)){
					$whereDl.=" AND id NOT IN ($detailId)";
				}
				$this->delete($whereDl);
				
				$dbs = new Application_Model_DbTable_DbGlobalStock();

				if(!empty($data['identity'])){
					$ids = explode(',', $data['identity']);
					foreach ($ids as $i){

						$arr = array(
							'branch_id'=>$data['branch_id'],
							'productId'=>$data['proId'.$i],
						);
						$rsProduct = $dbs->getProductInfoByLocation($arr);
							
						if (!empty($data['detailId'.$i])){
							$arr = array(

								'countId'=>$id,
								'proId'=>$data['proId'.$i],
								'currentQty'=>$rsProduct['currentQty'],//$data['currentQty'.$i],
								'countQty'=>$data['count_qty'.$i],
								'closingDate'=>$data['closing_date'.$i],
								'note'=>$data['note'.$i],
							);
							$this->_name='st_precount_product_detail';
							$where =" id =".$data['detailId'.$i];
							$this->update($arr, $where);
						}else{

							$arr = array(

								'countId'=>$id,
								'proId'=>$data['proId'.$i],
								'currentQty'=>$rsProduct['currentQty'],//$data['currentQty'.$i],
								'countQty'=>$data['count_qty'.$i],
								'closingDate'=>$data['closing_date'.$i],
								'note'=>$data['note'.$i],
								
							);
							$this->_name='st_precount_product_detail';	
							$this->insert($arr);
						}
					}
				}

				
			}


			
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
    	$sql="SELECT * FROM `st_precount_product` 
		 WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }

	function GetRowDetail($countId){
		
    	$db = $this->getAdapter();
    	$sql="SELECT *,
			(SELECT st_product.proCode FROM st_product WHERE st_product.proId=pd.proId LIMIT 1) AS proCode,
			(SELECT st_product.proName FROM st_product WHERE st_product.proId=pd.proId LIMIT 1) AS productName,
			(SELECT st_product.measureLabel FROM st_product WHERE st_product.proId=pd.proId LIMIT 1) AS MeasureLabel
			 FROM `st_precount_product_detail` AS pd
			 WHERE pd.countId =".$countId;
    	return $db->fetchAll($sql);
    }

}