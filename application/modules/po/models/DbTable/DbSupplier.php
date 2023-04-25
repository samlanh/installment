<?php

class Po_Model_DbTable_DbSupplier extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_supplier';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllSupplier($search){
    	$db = $this->getAdapter();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		
		
		$sql="
			SELECT 
				spp.id,
				spp.supplierName,
				spp.supplierTel,
				spp.contactName,
				spp.contactNumber,
				spp.receiverName,
				spp.bankNumber,
				spp.email
				,CASE
					WHEN  spp.supplierType = 1 THEN '".$tr->translate("LOCAL")."'
					WHEN  spp.supplierType= 2 THEN '".$tr->translate("OVER_SEA")."'
					END AS supplierTypeTitle 
				
		";
    	$sql.=$dbGb->caseStatusShowImage("spp.status");
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=spp.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_supplier` AS spp 
				WHERE  1
		";
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " spp.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " spp.createDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes((trim($search['adv_search'])));
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierTel LIKE '%{$s_search}%'";
    		$s_where[] = " spp.contactName LIKE '%{$s_search}%'";
    		$s_where[] = " spp.contactNumber LIKE '%{$s_search}%'";
    		$s_where[] = " spp.receiverName LIKE '%{$s_search}%'";
    		$s_where[] = " spp.bankNumber LIKE '%{$s_search}%'";
    		$s_where[] = " spp.email LIKE '%{$s_search}%'";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1 AND $search['status']!=''){
    		$where.= " AND spp.status = ".$search['status'];
    	}
		if(!empty($search['supplierType'])){
			$where.= " AND spp.supplierType = ".$search['supplierType'];
		}
    	$order=' ORDER BY spp.id DESC  ';
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function addSupplier($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'supplierType'			=>$data['supplierType'],
    				'supplierName'			=>$data['supplierName'],
    				'address'				=>$data['address'],
    				'supplierTel'			=>$data['supplierTel'],
    				'contactName'			=>$data['contactName'],
    				'contactNumber'			=>$data['contactNumber'],
    				'receiverName'			=>$data['receiverName'],
    				'bankNumber'			=>$data['bankNumber'],
    				'email'					=>$data['email'],
    				'note'					=>$data['note'],
    				
    				'modifyDate'			=>date("Y-m-d H:i:s"),
    				'userId'				=>$this->getUserId(),
    				
    				);
			$this->_name='st_supplier';

			$existing = $this->ifSupplierExisting($data);

			if(!empty($data['id'])){

				if(empty($existing)){
					$id = $data['id'];
					$arr['status']=$data['status'];
					$where = 'id = '.$id;
					$this->update($arr, $where);

				}else{
					Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/po/supplier/edit",2);
				}

			}else{
				if(empty($existing)){
					$arr['status']=1;
					$arr['createDate']=date("Y-m-d H:i:s");
					$id = $this->insert($arr);
				}else{
					Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/po/supplier/add",2);
				}

				
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
			$this->_name='st_supplier';
			$sql=" SELECT po.*
					FROM $this->_name AS po 
					WHERE po.id=".$recordId;
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }

	function ifSupplierExisting($data){
		
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE supplierName='".addslashes((trim($data['supplierName'])))."'";
		if(!empty($data['id'])){
			$sql.=" AND id !=".$data['id'];
		}	
    	return $db->fetchRow($sql);
    }
   
}