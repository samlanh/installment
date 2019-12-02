<?php
class Stock_Model_DbTable_DbForSection extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_for_section';
    
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllRequestFor($search){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
    				id,
    				title,
    				create_date,
    				(select name_en from ln_view where type=1 and key_code = rms_for_section.status) AS status,
    				(select first_name from rms_users as u where u.id = user_id) as user 
    			FROM 
    				rms_for_section 
    			WHERE 
    				title!='' 
    		";
    	$where = "";
    	if(!empty($search['title'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	$order = " ORDER BY id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function addForSection($_data){
    	$db = $this->getAdapter();
    	$_arr = array(
    		'title'=>$_data['title'],
    		'create_date'=>date("Y-m-d"),
    		'status'=>1,
    		'user_id'=>$this->getUserId(),
    	);
    	return $this->insert($_arr);
    } 
    
    public function updateForSection($_data,$id){
    	$db = $this->getAdapter();
    	$_arr = array(
    		'title'=>$_data['title'],
    		'status'=>$_data['status'],
    		'user_id'=>$this->getUserId(),
    	);
    	$where = " id = $id ";
    	return $this->update($_arr, $where);
    }
    
    public function getRequestforById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_for_section WHERE id = $id limit 1 ";
    	return $db->fetchRow($sql);
    }	
    
}



