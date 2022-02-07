<?php

class Application_Model_DbTable_DbSiteFront extends Zend_Db_Table_Abstract
{
	public function setName($name){
		$this->_name=$name;
	}
   
   
	public function getCountPropertyByType($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="
		SELECT COUNT(p.id) AS total,
			p.property_type,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitle,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleKH,
			(SELECT pt.type_namekh FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS propertyTypeTitleEn,
			(SELECT pt.image_feature FROM `ln_properties_type` AS pt WHERE pt.id = p.property_type LIMIT 1) AS imagePropertyType 
		FROM `ln_properties` AS p 
		WHERE p.status=1
		";
		if(!empty($search['branch_id'])){
			$sql.=" AND p.branch_id= ".$search['branch_id'];
		}
		if(!empty($search['propertyStatus'])){
			if($search['propertyStatus']==1){ //available property
				$sql.=" AND p.is_lock=0";
			}else if($search['propertyStatus']==2){
				$sql.=" AND p.is_lock=1"; //sold property
			}
		}
		
		if(!empty($search['property_type'])){
			$sql.=" AND p.property_type=".$search['property_type'];
			 return $db->fetchRow($sql);
		}else{
		
			$sql.=" GROUP BY p.property_type ";
			
			$order=" ";
			return $db->fetchAll($sql.$order);
		
		}
	}
	
	
	public function getCountSaleByPropertyType($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="
			SELECT 
				COUNT(s.id) AS totalSale,p.property_type 
			FROM `ln_sale` AS s,
				`ln_properties` AS p
			WHERE s.house_id = p.id 
				AND s.status=1
		";
		if(!empty($search['branch_id'])){
			$sql.=" AND s.branch_id= ".$search['branch_id'];
		}
		if(!empty($search['payment_id'])){
			$sql.=" AND s.payment_id=".$search['payment_id'];
		}
		
		if(!empty($search['property_type'])){
			$sql.=" AND p.property_type=".$search['property_type'];
		}
		
		$today = date("Y-m-d");
		if(!empty($search['isToday'])){
			$sql.=" AND s.buy_date=".$today;
		}
		
		$sql.=" GROUP BY p.property_type ";
		return $db->fetchRow($sql);
	}
	
	public function getCountSaleCancelByPropertyType($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="
			SELECT 
				COUNT(sc.id) AS total,
				sc.*
			FROM 
				`ln_sale_cancel` AS sc,
				`ln_properties` AS p

			WHERE 
				 sc.property_id = p.id 
				AND sc.status=1 
		";
		if(!empty($search['branch_id'])){
			$sql.=" AND sc.branch_id= ".$search['branch_id'];
		}

		if(!empty($search['property_type'])){
			$sql.=" AND p.property_type=".$search['property_type'];
		}
		$today = date("Y-m-d");
		if(!empty($search['isToday'])){
			$sql.=" AND sc.create_date=".$today;
		}
		
		$sql.=" GROUP BY p.property_type ";
		return $db->fetchRow($sql);
	}

}
?>