<?php
class Setting_Model_DbTable_DbInterestSetting extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_interestsetting';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllSetting($search){
    	$db= $this->getAdapter();
    	$sql="SELECT ms.id,
			ms.title,
			ms.status,
			(SELECT first_name FROM `rms_users` WHERE id=ms.user_id LIMIT 1) as user_name 
			 FROM `rms_interestsetting` AS ms
    	WHERE 1 ";
    	$where = "";
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " title LIKE '%{$s_search}%'";
    		$s_where[]= " note LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addSetting($data){
    	$db= $this->getAdapter();
    	try{
    		$arr = array(
    				'title'				=>$data['title'],
    				'note'			=>$data['note'],
    				'status'		=>1,
    				'create_date'	=>date("Y-m-d H:i:s"),
    				'modify_date'	=>date("Y-m-d H:i:s"),
    				'user_id'		=>$this->getUserId(),
    		);
    		$this->_name='rms_interestsetting';
    		$id = $this->insert($arr);
    		
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'settin_id'	=>$id,
							'max_month'			=>$data['max_month'.$i],
							'percent_value'		=>$data['percent_value'.$i],
							'note'	=>$data['note'.$i],
						);
					$this->_name='rms_interestsetting_detail';	
					$this->insert($arr);
				}
    		}
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	
	public function editSettingID($data){
		$db= $this->getAdapter();
		try{
			$arr = array(
    				'title'			=>$data['title'],
    				'note'			=>$data['note'],
    				'status'		=>1,
    				'modify_date'	=>date("Y-m-d H:i:s"),
    				'user_id'		=>$this->getUserId(),
    		);
    		$this->_name='rms_interestsetting';
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			$id = $data['id'];
			
			$identitys = explode(',',$data['identity']);
			$detailId="";
			if (!empty($identitys)){
				foreach ($identitys as $i){
					if (empty($detailId)){
						if (!empty($data['detailid'.$i])){
							$detailId = $data['detailid'.$i];
						}
					}else{
						if (!empty($data['detailid'.$i])){
							$detailId= $detailId.",".$data['detailid'.$i];
						}
					}
				}
			}
			$this->_name='rms_interestsetting_detail';
			$where = 'settin_id = '.$id;
			if (!empty($detailId)){
				$where.=" AND id NOT IN ($detailId) ";
			}
			$this->delete($where);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					if (!empty($data['detailid'.$i])){
						$arr = array(
							'settin_id'	=>$id,
							'max_month'			=>$data['max_month'.$i],
							'percent_value'		=>$data['percent_value'.$i],
							'note'	=>$data['note'.$i],
						);
						$this->_name='rms_interestsetting_detail';
						$where =" id =".$data['detailid'.$i];
						$this->update($arr, $where);
					}else{
						$arr = array(
							'settin_id'	=>$id,
							'max_month'			=>$data['max_month'.$i],
							'percent_value'		=>$data['percent_value'.$i],
							'note'	=>$data['note'.$i],
						);
						$this->_name='rms_interestsetting_detail';
						$this->insert($arr);
					}
				}
			}
    	}catch(Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	function getSettingById($id=null){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM rms_interestsetting WHERE 1 ";
		if (!empty($id)){
			$sql.=" AND id = $id LIMIT 1";
		}
		return $db->fetchRow($sql);
	}
	function getSettingDetailById($id=null){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `rms_interestsetting_detail` WHERE 1 ";
		if (!empty($id)){
			$sql.=" AND settin_id = $id";
		}
		return $db->fetchAll($sql);
	}
	
	
	public function getAllSettingOpt($_ispot=null){
		$db= $this->getAdapter();
		$sql="
		SELECT ms.id,
			ms.title,
			ms.title AS name
			FROM `rms_interestsetting` AS ms
		WHERE ms.status =1 ";
		$sql.=" ORDER BY ms.title ASC";
		$row =  $db->fetchAll($sql);
		if (!empty($_ispot)) {
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		  	$options=array(0=> $tr->translate("PLEASE_SELECT"));
		  	if(!empty($row)) foreach($row as $read) $options[$read['id']]=$read['name'];
		  	return $options;
		}
		return $row;
	}
}