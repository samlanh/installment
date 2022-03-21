<?php

class Project_Model_DbTable_Dbpinmap extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllLandMap($search = null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	
//     	$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
//     	$where = " WHERE ".$from_date." AND ".$to_date;
    	$sql = "SELECT id,
    	(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = ln_properties.branch_id LIMIT 1) AS branch_name,
    	land_address,street,
    	(SELECT t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.id = property_type limit 1) AS  pro_type,
    	map_width,map_height,map_transform,map_top,map_left ";
    	
    	$where=" WHERE map_width>0 AND map_height>0 ";
    	$sql.=" FROM $this->_name ";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " land_code LIKE '%{$s_search}%'";
    		$s_where[] = " land_address LIKE '%{$s_search}%'";
    		$s_where[] = " street LIKE '%{$s_search}%'";
    		$s_where[] = " price LIKE '%{$s_search}%'";
    		$s_where[] = " land_size LIKE '%{$s_search}%'";
    		$s_where[] = " width LIKE '%{$s_search}%'";
    		$s_where[] = " height LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$search['status'];
    	}
    	if(!empty($search['streetlist'])){
    		$where.= " AND street ='".$search['streetlist']."'";
    	}
    	if($search['branch_id']>-1){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	if(($search['property_type_search'])>0){
    		$where.= " AND property_type = ".$search['property_type_search'];
    	}
    	$where.=$dbp->getAccessPermission("ln_properties.branch_id");
    	
    	$order= " ORDER BY LENGTH(land_address), land_address ASC  ";
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addMapPin($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    
    		$ids = explode(',', $data['identity']);
    		if(!empty($data['identity'])){
    			foreach ($ids as $i){
    				$where_proper="id = ".$data['land_id'.$i];
    				$this->_name="ln_properties";
    				$arr_proper = array(
    						'map_width'=>$data['width'.$i],
    						'map_height'=>$data['height'.$i],
    						'map_transform'=>$data['transform'.$i],
    						'map_left'=>$data['left'.$i],
    						'map_top'=>$data['top'.$i],
    						
    				);
    				$this->update($arr_proper, $where_proper);
    
    			}
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		$db->rollBack();
    
    		 
    	}
    }
   
}