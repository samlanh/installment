<?php

class Product_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_product';
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
   
    function addNewProduct($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		
    		$arr = array(
    				'proName'=>$data['productName'],
    				'proCode'=>$data['productCode'],
    				'barCode'=>$data['barCode'],
    				'isService'=>$data['isService'],
    				'categoryId'=>$data['categoryId'],
    				'costing'=>$data['costing'],
    				'isConvertMeasure'=>$data['isConvert'],
    				'measureId'=>$data['measureId'],
    				'measureLabel'=>$data['labelMeasure'],
    				'measureValue'=>$data['qtyMeasure'],
//     				'image'=>$data[''],
    				'userId'=>$this->getUserId(),
    				'createDate'=>date("Y-m-d"),
    				'isCountStock'=>$data['isCountStock'],
    				'note'=>$data['note'],
    				'status'=>1,
    				'budgetId'=>$data['budgetItem'],    				
    				);
    		
    		$part= PUBLIC_PATH.'/images/';
    		$photo_name = $_FILES['photo']['name'];
    		if (!empty($photo_name)){
    			$tem =explode(".", $photo_name);
    			$image_name = "product_".date("Y").date("m").date("d").time().".".end($tem);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				move_uploaded_file($tmp, $part.$image_name);
    				$photo = $image_name;
    				$arr['photo_name']=$photo;
    			}
    		}
    		//$this->_name='';
    		$id = $this->insert($arr);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
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