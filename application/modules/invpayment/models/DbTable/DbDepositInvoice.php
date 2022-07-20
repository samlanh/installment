<?php

class Invpayment_Model_DbTable_DbDepositInvoice extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_purchasing';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllDepositInvoice($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'keyIndex'=>$search['ivType'],
			'typeKeyIndex'=>1,
		);
		$ivType = $dbGBstock->invoiceTypeKey($arrStep);
		
		$sql="
			SELECT 
				inv.id,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = inv.projectId LIMIT 1) AS branch_name,
				inv.invoiceNo,
				inv.invoiceDate,
				inv.supplierInvoiceNo,
				inv.receiveIvDate,
				po.purchaseNo,
				spp.supplierName,
				inv.totalAmountExternal
			";
    	$sql.=$dbGb->caseStatusShowImage("inv.status");
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=inv.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_invoice` AS inv 
					JOIN `st_purchasing` AS po ON po.id = inv.purId 
					LEFT JOIN `st_supplier` AS spp ON spp.id = inv.supplierId 
				WHERE 
					 inv.ivType=".$ivType."
		";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " inv.receiveIvDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " inv.receiveIvDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " inv.invoiceNo LIKE '%{$s_search}%'";
    		$s_where[] = " inv.supplierInvoiceNo LIKE '%{$s_search}%'";
    		$s_where[] = " po.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		$s_where[] = " po.purpose LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalInternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.vatInternal LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalAmount LIKE '%{$s_search}%'";
    		$s_where[] = " inv.vatExternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.otherFeeExternal LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalExternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.totalAmountExternal LIKE '%{$s_search}%'";
			
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND inv.status = ".$search['status'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND inv.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND inv.supplierId = ".$search['supplierId'];
    	}
    	$order=' ORDER BY inv.id DESC  ';
    	$where.=$dbGb->getAccessPermission("inv.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function addDepositInvoice($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$data['receiveIvDate']=$data['receiveIvDate'];
			$invoiceNo =$dbGBstock->generateInvoiceNo($data);

			$arrStep = array(
				'keyIndex'=>$data['ivType'],
				'typeKeyIndex'=>1,
			);
			$ivType = $dbGBstock->invoiceTypeKey($arrStep);
			
    		$arr = array(
    				'projectId'			=>$data['branch_id'],
    				'ivType'			=>$ivType,
    				'invoiceNo'			=>$invoiceNo,
    				'supplierId'		=>$data['supplierId'],
					
    				'invoiceDate'				=>$data['invoiceDate'],
    				'supplierInvoiceNo'			=>$data['supplierInvoiceNo'],
    				'receiveIvDate'				=>$data['receiveIvDate'],
    				'purId'					=>$data['purId'],
    				'note'					=>$data['note'],
					
					'totalInternal'	=>$data['totalInternal'],
    				'totalAmountExternal'	=>$data['totalAmountExternal'],
    				'totalAmountExternalAfter'	=>$data['totalAmountExternal'],
    				
					'status'			=>1,
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$this->getUserId(),
    				
    				);
    		$this->_name='st_invoice';
    		$id = $this->insert($arr);
			
					
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					
					$arr = array(
							'invId'		=>$id,
							'type'				=>$data['type'.$i],
							'proId'				=>$data['proId'.$i],
								
							'qtyPo'				=>$data['qty'.$i],
							'unitPrice'			=>$data['unitPrice'.$i],
							'discountAmount'	=>$data['discountAmount'.$i],
							'total'				=>$data['total'.$i],
						);
					$this->_name='st_invoice_detail';	
					$this->insert($arr);
				}
    		}
			
			if(!empty($data['identityService'])){
				$ids = explode(',', $data['identityService']);
				foreach ($ids as $i){
					$arr = array(
							'invId'		=>$id,
							'type'				=>$data['isService'.$i],
							'proId'				=>$data['serviceId'.$i],
								
							'qtyPo'				=>1,
							'unitPrice'			=>$data['totalService'.$i],
							'discountAmount'	=>0,
							'total'				=>$data['totalService'.$i],
						);
					$this->_name='st_invoice_detail';	
					$this->insert($arr);
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
			$this->_name='st_invoice';
			$sql=" SELECT po.* FROM $this->_name AS po WHERE po.id=".$recordId;
			$sql.=$dbGb->getAccessPermission("po.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	function getInvoiceDetailById($data){
		$recordId = empty($data['id'])?0:$data['id'];
		$isService = empty($data['isService'])?0:$data['isService'];
		$db = $this->getAdapter();
		$sql=" 	SELECT 
					invd.*,p.proCode,
					p.proName,
					p.isService AS serviceOrProType,
					(SELECT pl.qty FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= inv.projectId LIMIT 1) AS currentQty,
					p.measureLabel AS measureTitle
					";
			
		$sql.="		FROM 
					`st_invoice_detail` as invd
					JOIN `st_invoice` AS inv ON inv.id = invd.invId
					LEFT JOIN `st_product` AS p  ON p.proId = invd.proId 
			";
		$sql.=" WHERE invd.invId = $recordId";
		if(empty($data['getAllRecord'])){
		$sql.=" AND invd.type = $isService ";
		}
		
		return $db->fetchAll($sql);
	}
   
}