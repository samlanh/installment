<?php

class Group_Model_DbTable_DbTranferCRM extends Zend_Db_Table_Abstract
{

    protected $_name = 'in_transfer_crm';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	function getUserListByCurrentUserTypeAndParent(){
		$db= $this->getAdapter();
		$sql="
		SELECT 
			u.`id`
			,CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) AS name
		FROM `rms_users` AS u
		WHERE u.`active`=1 
			
		";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$userInfo = $dbp->getUserInfo();
		if(!empty($userInfo)){
			$level = empty($userInfo["level"]) ? 0 : $userInfo["level"];
			$userId = empty($userInfo["user_id"]) ? 0 : $userInfo["user_id"];
			if($level!=1){
				$sql.= " AND FIND_IN_SET(u.`user_type`,( COALESCE((SELECT 
							GROUP_CONCAT(ut.`user_type_id`,',',ut.parent_id)
							FROM `rms_acl_user_type` AS ut WHERE (ut.user_type_id = ".$level." OR ut.`parent_id`=".$level.")),0)))
			
			";
			}
			$sql.= " AND u.`id` != ".$userId;
		}
		$sql.= ' AND (u.`user_name` != "system" AND u.`user_name` != "accountpo" AND u.`user_name` != "sitemg2") ';
		return $db->fetchAll($sql);
	}
	
	function getAllCrmOfUser(){
		$db= $this->getAdapter();
		$sql="
		SELECT 
			crm.`id`
			,crm.name AS name
		FROM `in_customer` AS crm
		WHERE crm.`status`=1 
			
		";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$userInfo = $dbp->getUserInfo();
		if(!empty($userInfo)){
			$level = empty($userInfo["level"]) ? 0 : $userInfo["level"];
			$userId = empty($userInfo["user_id"]) ? 0 : $userInfo["user_id"];
			if($level!=1){
				$sql.= " AND crm.user_id =".$userId;
			}
		}
		return $db->fetchAll($sql);
	}
	
	public function add($_data){
		try{
			$_data['crmId'] = empty($_data['crmId']) ? 0 : $_data['crmId'];
			$dbCus = new Group_Model_DbTable_DbCustomer();
			$row = $dbCus->getById($_data['crmId']);
			if(!empty($row)){
				$_arr=array(		    
					'crmId'	      => $_data['crmId'],
					'toUserId'	  => $_data['toUserId'],
					'note'	  	  => $_data['note'],
					
					'status'	  => 1,
					'fromUserId'  => empty($row["user_id"]) ? 0 : $row["user_id"],
					'userId'	  => $this->getUserId(),
					
					'createDate'   => date("Y-m-d H:i:s"),
					'modifyDate'   => date("Y-m-d H:i:s"),
				);
				$this->_name = "in_transfer_crm";
				$id = $this->insert($_arr);
				
				$arrClient = array(
					"user_id" =>$_data['toUserId']
				);
				$whereClient = 'id = '.$_data['crmId'];
				$this->_name = "in_customer";
				$this->update($arrClient, $whereClient);
			}
		

		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}



	function getAllInfo($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': " tr.createDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " tr.createDate <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;	

		
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
		$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
		
		$sql = "
			SELECT 
				tr.id
				,crt.name
				,crt.phone
				,(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=crt.know_by LIMIT 1) as know_by
				,(SELECT  CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) FROM rms_users AS u WHERE u.id = tr.toUserId limit 1 ) AS toUser
				,tr.createDate
		
		 ";
		
		$sql.="
		
		,(SELECT  first_name FROM rms_users WHERE id = tr.userId limit 1 ) AS user_name
		,CASE    
			WHEN  tr.`status` = 1 THEN '".$imgtick."'
			WHEN  tr.`status` = 0 THEN '".$imgnone."'
		END AS status
		";
		
		
		
		$sql.=" FROM 
					in_transfer_crm tr
					JOIN in_customer AS crt ON tr.crmId = crt.id	
		
		";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " crt.name LIKE '%{$s_search}%'";
			$s_where[] = " crt.phone LIKE '%{$s_search}%'";
			$s_where[] = " crt.requirement LIKE '%{$s_search}%'";
			$s_where[] = " crt.type LIKE '%{$s_search}%'";
			$s_where[] = " crt.from_price LIKE '%{$s_search}%'";
			$s_where[] = " crt.to_price LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$userinfo = $dbgb->getUserInfo();
		if($userinfo['level']!=1){
			$where.= " AND tr.userId = ".$userinfo['user_id'];
		}
		$order=" ORDER BY tr.id DESC ";
		return $db->fetchAll($sql.$where.$order);	
	}
	
}