<?php

class Product_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllProductData($search){
    	
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang_id=$session_lang->lang_id;
    	$strLable ='name_kh' ;
    	if($lang_id==2){
    		$strLable ='name_en' ;
    	}
    	
    	$sql=" SELECT 
					
					p.proId,
					p.proName,
					p.proCode,
					p.barCode,
					(SELECT c.categoryName from `st_category` as c WHERE c.id=p.categoryId LIMIT 1) categoryName,
					(SELECT m.name FROM `st_measure` as m WHERE m.id=p.measureId LIMIT 1) MeasureName,
					(SELECT $strLable FROM `st_view` WHERE type=2 AND key_code=p.isService LIMIT 1) isService,
					(SELECT $strLable FROM `st_view` WHERE type=1 AND key_code=p.isCountStock LIMIT 1) isCountStock,
					(SELECT i.budgetTitle FROM `st_budget_item` AS i WHERE i.id=p.budgetId LIMIT 1) budgetTitle,
					(SELECT first_name FROM rms_users as u WHERE u.id = p.userId LIMIT 1) AS user ,
					p.createDate,
					(SELECT name_en FROM ln_view WHERE type=3 and key_code = p.status LIMIT 1) AS status
					
				FROM $this->_name AS p  ";
    	
    	
    	$from_date =(empty($search['start_date']))? '1': " createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " createDate <= '".$search['end_date']." 23:59:59'";
    	$where=" WHERE proName!='' ";
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " P.proName LIKE '%{$s_search}%'";
    		$s_where[] = " P.proCode LIKE '%{$s_search}%'";
    		$s_where[] = " P.barCode LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['status']>-1){
    		$where.= " AND p.status = ".$search['status'];
    	}
    	
    	if($search['isService']>-1){
    		$where.= " AND p.isService = ".$search['isService'];
    	}
    	if($search['isCountStock']>-1){
    		$where.= " AND p.isCountStock = ".$search['isCountStock'];
    	}
    	if($search['categoryId']>0){
    		$where.= " AND p.categoryId = ".$search['categoryId'];
    	}
    	if($search['budgetItem']>0){
    		$where.= " AND p.budgetId = ".$search['budgetItem'];
    	}
    	if($search['measureId']>0){
    		$where.= " AND p.measureId = ".$search['measureId'];
    	}
    	
    	$order=' ORDER BY proId DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$where_date.$order);
    }
   
    function addNewProduct($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$productCode = $this->generateProductCode();
    		
    		$arr = array(
    				'proName'=>$data['productName'],
    				'proCode'=>$productCode,
    				'barCode'=>$data['barCode'],
    				'isService'=>$data['isService'],
    				'categoryId'=>$data['categoryId'],
    				'isConvertMeasure'=>$data['isConvert'],
    				'measureId'=>$data['measureId'],
    				'measureLabel'=>$data['labelMeasure'],
    				'measureValue'=>$data['qtyMeasure'],
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
    				$arr['image']=$photo;
    			}
    		}
    		$id = $this->insert($arr);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/product/index/add");
    	}
    }
    function updateProductData($data){
    	 
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
    			'isConvertMeasure'=>$data['isConvert'],
    			'measureId'=>$data['measureId'],
    			'measureLabel'=>$data['labelMeasure'],
    			'measureValue'=>$data['qtyMeasure'],
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
    				$arr['image']=$photo;
    			}
    		}
    		if(!empty($photo_name) AND file_exists($part.$data['oldPhoto'])){//delelete old file
    			unlink($part.$data['oldPhoto']);
    		}
    		
    		$where = 'proId = '.$data['id'];
			$this->update($arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("UPDATE_FAIL", "/product/index");
    	}
    }
    function generateProductCode(){
    	$db = $this->getAdapter();
    	$sql=" SELECT count(proId) FROM $this->_name";
    	 $acc_no = $db->fetchOne($sql);
    	 $proNo= (int)$acc_no+1;
    	 $lenght = strlen((int)$proNo);
    	 $pre='';
    	 for($i = $lenght;$i<4;$i++){
    	 	$pre.='0';
    	 }
    	 return $pre.$proNo;
    }
    function getProductbyId($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE proId=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getProductDetailbyId($proId){
    	$db = $this->getAdapter();
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang_id=$session_lang->lang_id;
    	$strLable ='name_kh' ;
    	if($lang_id==2){
    		$strLable ='name_en' ;
    	}
    	
    	$sql=" SELECT
		    	p.proId,
		    	p.proName,
		    	p.proCode,
		    	p.barCode,
		    	p.measureValue,
		    	p.measureLabel,
		    	p.note,
		    	p.createDate,
		    	p.modifyDate,
		    	(SELECT c.categoryName from `st_category` as c WHERE c.id=p.categoryId LIMIT 1) categoryName,
		    	(SELECT m.name FROM `st_measure` as m WHERE m.id=p.measureId LIMIT 1) MeasureName,
		    	(SELECT $strLable FROM `st_view` WHERE type=2 AND key_code=p.isService LIMIT 1) isService,
		    	(SELECT $strLable FROM `st_view` WHERE type=1 AND key_code=p.isCountStock LIMIT 1) isCountStock,
		    	(SELECT i.budgetTitle FROM `st_budget_item` AS i WHERE i.id=p.budgetId LIMIT 1) budgetTitle,
		    	(SELECT first_name FROM rms_users as u WHERE u.id = p.userId LIMIT 1) AS user ,
		    	p.createDate,
		    	(SELECT name_en FROM ln_view WHERE type=3 and key_code = p.status LIMIT 1) AS status
		    		
		    	FROM $this->_name AS p  ";
    	return $db->fetchRow($sql);
    }
   
}