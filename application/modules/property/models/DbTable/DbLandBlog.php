<?php

class Property_Model_DbTable_DbLandBlog extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_land_blog';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    
    }
    function addLandBlog($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$arr = array(
	    			'title_kh'=>$data['title_kh'],
					'status'=>$data['status'],
	    			'user_id'=>$this->getUserId(),
	    			'date'=>date("Y-m-d"),
	    			'note'=>$data['note'],
	    		);
	    	$this->_name='ln_land_blog';
	    	if(!empty($data['id'])){
	    		$where = 'id = '.$data['id'];
	    		return  $this->update($arr, $where);
	    	}else{
	    		return  $this->insert($arr);
	    	}
	    	$db->commit();
    	}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
	}
	
	function geteAllLandBlog($search=null){
		$db = $this->getAdapter();

		$sql='SELECT t.`id`,t.`title_kh`,t.`note`,
(SELECT CONCAT(u.first_name," ",u.last_name) FROM `rms_users` AS u WHERE u.id = t.`user_id`) AS user_name,
t.`status` FROM `ln_land_blog` AS t where t.`status` >-1 ';
		$where="";
		if (empty($search['show_all'])){
			if($search['status_search']>-1){
				$where.=" AND t.status=".$search['status_search'];
			}
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search=$search['adv_search'];
				
				$s_where[]="t.`title_kh` LIKE'%{$s_search}%'";
				//$s_where[]="t.`type_namekh` LIKE'%{$s_search}%'";
				$s_where[]="t.`note` LIKE'%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
		}
		$order = " ORDER BY t.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getLandBlogById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_land_blog` AS t WHERE t.`id`=".$id;
		return $db->fetchRow($sql);
	}
	function ajaxLandBlog($data){ // used on BlogController
		$this->_name='ln_land_blog';
		$db = $this->getAdapter();
		$arr = array(
				'title_kh'=>$data['title_kh'],
				'status'=>1,
				'user_id'=>$this->getUserId(),
				'date'=>date("Y-m-d"),
		);
		return $this->insert($arr);
	}
	function deleteLandBlog($id){
		$db = $this->getAdapter();
		$arr = array( 'status'=> -1);
		$where = ' id = '.$id;
		$this->_name = "ln_land_blog";
		$this->update($arr, $where);
	}
	public function getLandBlog(){
		$db = $this->getAdapter();
		$sql= "SELECT t.`id`,t.`title_kh` AS `name` FROM `ln_land_blog` AS t WHERE t.`status`=1";
		return $db->fetchAll($sql);
	}
}

