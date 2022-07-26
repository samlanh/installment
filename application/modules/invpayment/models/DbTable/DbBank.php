<?php

class Invpayment_Model_DbTable_DbBank extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_bank';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllBank($search){
    	$db = $this->getAdapter();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		
		
		$sql="
			SELECT 
				spp.id,
				spp.bank_name
		";
    	$sql.=$dbGb->caseStatusShowImage("spp.status");
		$sql.=",spp.createDate ";
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=spp.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_bank` AS spp 
				WHERE  1
		";
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " spp.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " spp.createDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " spp.bank_name LIKE '%{$s_search}%'";
    		$s_where[] = " spp.note LIKE '%{$s_search}%'";
    		
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND spp.status = ".$search['status'];
    	}
		
    	$order=' ORDER BY spp.id DESC  ';
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function addBank($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
    				'bank_name'			=>$data['bank_name'],
    				'note'				=>$data['note'],
    				
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$this->getUserId(),
    				
    				);
			$this->_name='st_bank';
			if(!empty($data['id'])){
				
				$id = $data['id'];
				$arr['status']=$data['status'];
				$where = 'id = '.$id;
				$this->update($arr, $where);
			}else{
				$arr['status']=1;
				$arr['createDate']=date("Y-m-d H:i:s");
				$id = $this->insert($arr);
			}
			
			
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }

    function getDataRow($recordId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();		
			$this->_name='st_bank';
			$sql=" SELECT po.*
					FROM $this->_name AS po 
					WHERE po.id=".$recordId;
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }

	
	
   
}