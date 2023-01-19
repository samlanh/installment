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
		$sql.=",spp.createDate ";
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=spp.userId LIMIT 1 ) AS byUser";
		$sql.=$dbGb->caseStatusShowImage("spp.status");
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
		$existing = $this->ifBankExisting($data['bank_name']);
		
		if(empty($existing)){
			$_arr = array(
				'bank_name'			=>$data['bank_name'],
    			'note'				=>$data['note'],	
    			'createDate'		=>date("Y-m-d H:i:s"),
				'modifyDate'		=>date("Y-m-d H:i:s"),
    			'userId'			=>$this->getUserId(),
			);
			$this->insert($_arr);
		}else{
			Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/invpayment/bank/add",2);
		}
	} 
	function ifBankExisting($bank_name){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE bank_name='".$bank_name."' LIMIT 1";
    	return $db->fetchRow($sql);
    }

	

	function updateBank($data){
	
		$existing = $this->ifBankExistingUpdate($data['bank_name'],$data['id']);

	    if(empty($existing)){
			$_arr = array(
				'bank_name'			=>$data['bank_name'],
				'note'				=>$data['note'],	
				'modifyDate'		=>date("Y-m-d H:i:s"),
				'userId'			=>$this->getUserId(),
				'status'				=>$data['status'],
			);
			$where = " id = ".$data['id'];
			return $this->update($_arr, $where);

		}else{
			Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/invpayment/bank/edit",2);
		}
	
    }

	function ifBankExistingUpdate($bank_name,$id){
		
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE bank_name='".$bank_name."'";
		$sql.=" AND id !=".$id;
    	return $db->fetchRow($sql);
    }
	/*
   
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
*/
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