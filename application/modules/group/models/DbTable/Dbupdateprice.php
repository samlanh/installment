<?php

class Group_Model_DbTable_Dbupdateprice extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllRoad($search){
    	$db = $this->getAdapter();
	  	$sql = 'SELECT DISTINCT street,
				  	(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = ln_properties.branch_id LIMIT 1) AS branch_name,
				  	street AS street_name,
	  				(SELECT type_namekh FROM `ln_properties_type` WHERE id=ln_properties.property_type) AS properties_type,
				  	price
	  		 FROM `ln_properties` WHERE street!="" ';
	  	$where="";
	  	if(!empty($search['adv_search'])){
	  		$s_where = array();
	  		$s_search = addslashes(trim($search['adv_search']));
	  		$s_where[] = " street LIKE '%{$s_search}%'";
	  		$where .=' AND ('.implode(' OR ',$s_where).')';
	  	}
	  	if(!empty($search['streetlist'])){
	  		$where.= " AND street ='".$search['streetlist']."'";
	  	}
	  	if($search['branch_id']>-1){
	  		$where.= " AND branch_id = ".$search['branch_id'];
	  	}
	  	$where.=" GROUP BY branch_id,street,property_type ORDER BY branch_id DESC,street ASC";
	  	return  $db->fetchAll($sql.$where);
    }
    public function getPropertiesByStreet($street,$branch_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT *,
				(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = ln_properties.branch_id LIMIT 1) AS branch_name,
				(SELECT t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.id = property_type limit 1) AS  pro_type
    	FROM $this->_name WHERE is_buy=0 AND street = ".$db->quote($street);
    	$sql.=" ORDER BY price DESC, cast(land_address as unsigned) ";
    	return $db->fetchAll($sql);
    }
    function updatePrice($data){
    	if(!empty($data['id_selected'])){
    		$ids = explode(',', $data['id_selected']);
    		$key = 1;
    		$arr = array(
    				"land_price"=>$data['land_price'],
    				'house_price'=>$data['house_price'],
    				'price'=>$data['sold_price']
    			);
    		foreach ($ids as $i){
    			
    			$this->_name="ln_property_price";
    			$row = $this->getPropertyById($i);
    			if(empty($row)){
    				$row['land_price']=0;$row['house_price']=0;$row['price']=0;
    			}
    			$arr_price = array(
    					'property_id'=>$i,
    					"old_landprice"=>$row['land_price'],
    					'old_houseprice'=>$row['house_price'],
    					'old_price'=>$row['price'],
    						
    					"land_price"=>$data['land_price'],
    					'house_price'=>$data['house_price'],
    					'price'=>$data['sold_price'],
    					'user_id'=>$this->getUserId(),
    					'note'=>$data['note'],
    					'update_date'=>date("Y-m-d h:i:s")
    			);
    			$this->insert($arr_price);
    			
    			$this->_name="ln_properties";
    			$where=" is_buy=0 AND id=".$i;
    			$this->update($arr, $where);
    			
    		}
    	}
    }
    function getPropertyById($proper_id){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM ln_properties WHERE is_buy=0 AND id=$proper_id LIMIT 1";
    	return $db->fetchRow($sql);
    }
	
}