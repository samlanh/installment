<?php

class Project_Model_DbTable_DbProperyType extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_properties_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function addPropery($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
			
			$part= PUBLIC_PATH.'/images/photo/property/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			
	    	$arr = array(
	    			'type_nameen'=>$data['type_nameen'],
	    			'type_namekh'=>$data['type_nameen'],
	    			'serviceFee'=>$data['serviceFee'],
	    			'serviceFeeYear'=>$data['serviceFeeYear'],
	    			'user_id'=>$this->getUserId(),
	    			'date'=>date("Y-m-d"),
	    			'note'=>$data['note'],
	    		);
				
			$name = $_FILES['logo']['name'];
			$photo='';	
			if (!empty($name)){
				$tem =explode(".", $name);
				$new_image_name = "property-type-img".date("Y").date("m").date("d").time().".".end($tem);
				$tmp = $_FILES['logo']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$new_image_name)){
					$photo = $new_image_name;
					$arr['image_feature']= $photo;
				}
			}
			
	    	$this->_name='ln_properties_type';
	    	if(!empty($data['id'])){
				$status = empty($data['status'])?0:1;
	    		$where = 'id = '.$data['id'];
	    		$arr['status']=$status;
	    		return  $this->update($arr, $where);
	    	}else{
	    		$arr['status']=1;
	    		return  $this->insert($arr);
	    	}
	    	$db->commit();
    	}catch(exception $e){
    		$db->rollBack();
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	function geteAllPropertyType($search=null){
		$db = $this->getAdapter();
		$sql='SELECT t.`id`,t.`type_nameen`,t.`note`,
			(SELECT CONCAT(u.first_name," ",u.last_name) FROM `rms_users` AS u WHERE u.id = t.`user_id`) AS user_name ';
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("t.`status`");
		$sql.=" FROM `ln_properties_type` AS t WHERE 1 ";
		$where="";
		if($search['status_search']>-1){
			$where.=" AND t.status=".$search['status_search'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]="t.`type_nameen` LIKE'%{$s_search}%'";
			$s_where[]="t.`note` LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY t.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getPropertyTypeById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_properties_type` AS t WHERE t.`id`=".$id;
		return $db->fetchRow($sql);
	}
	function ajaxPropertytype($data){ // used on ProperiestypeController
		$this->_name='ln_properties_type';
		$db = $this->getAdapter();
		$arr = array(
				'type_nameen'=>$data['type_nameen'],
				'type_namekh'=>$data['type_nameen'],
				'status'=>1
		);
		return $this->insert($arr);
	}
}