<?php

class Product_Model_DbTable_DbinitilizeQtybyProject extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_product_location';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllDataRows($search){
    	$sql="";
    	
    	
    	$from_date =(empty($search['start_date']))? '1': " send_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " send_date <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		//$s_where[] = " sms.contance LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	
    	$order.=' ORDER BY id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$order);
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
								'qty'=>$data['qtyRequest'.$i],
								'qtyAlert'=>$data['qtyAlert'.$i],
						);
						$this->_name='st_product_location';
						$this->insert($arr);
						
					}
					
					$dbs->addProductHistoryQty($data['branch_id'], $data['proId'.$i], 1, $data['qtyRequest'.$i]);
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