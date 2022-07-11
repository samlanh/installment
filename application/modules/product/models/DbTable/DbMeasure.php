<?php
class Product_Model_DbTable_DbMeasure extends Zend_Db_Table_Abstract
{

    protected $_name = 'st_measure';
    
     function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
     function getAllMeasure($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				id,
    				name,
    				(SELECT first_name from rms_users as u where u.id = user_id LIMIT 1) as user ,
    				date,
    				(SELECT name_en from ln_view where type=3 and key_code = $this->_name.status LIMIT 1) AS status
    				
    			FROM 
    				$this->_name 
    			WHERE 
    				name!='' ";
    	$where = "";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " name LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	$order = " ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
     function addMeasure($_data){
    		$existing = $this->ifMeasureExisting($_data['title']);
    		
    		if(empty($existing)){
		    	$_arr = array(
		    		'name'=>$_data['title'],
		    		'date'=>date("Y-m-d"),
		    		'status'=>1,
		    		'user_id'=>$this->getUserId(),
		    	);
		    	$this->insert($_arr);
    		}else{
    			Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/product/measure/add");
    		}
    } 
     
    function ifMeasureExisting($title){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE name='".$title."' LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
     function updateMeasure($_data){
    	$_arr = array(
    		'name'=>$_data['title'],
    		'status'=>$_data['status'],
    		'user_id'=>$this->getUserId(),
    	);
    	$where = " id = ".$_data['id'];
    	return $this->update($_arr, $where);
    }
    
     function getMeasureById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM $this->_name WHERE id = $id limit 1 ";
    	return $db->fetchRow($sql);
    }	
}