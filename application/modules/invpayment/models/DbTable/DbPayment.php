<?php

class Invpayment_Model_DbTable_DbPayment extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllPayment($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		
		$sql="
			SELECT 
				pt.id,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = pt.projectId LIMIT 1) AS branch_name,
				pt.paymentNo,
				spp.supplierName,
				pt.paymentDate,
				(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pt.`paymentMethod` LIMIT 1) AS paymentMethod,
				(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pt.`bankId` LIMIT 1) AS bankName,
				pt.accNameAndChequeNo,
				pt.totalAmount
			";
    	$sql.=$dbGb->caseStatusShowImage("pt.status");
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=pt.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_payment` AS pt
					LEFT JOIN `st_supplier` AS spp ON spp.id = pt.supplierId 
				WHERE 1 
		";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " pt.paymentDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " pt.paymentDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " pt.paymentNo LIKE '%{$s_search}%'";
    		$s_where[] = " pt.accNameAndChequeNo LIKE '%{$s_search}%'";
    		$s_where[] = " pt.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.totalAmount LIKE '%{$s_search}%'";
			
    		$s_where[] = " (SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pt.`bankId` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pt.`paymentMethod` LIMIT 1) LIKE '%{$s_search}%'";
			
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['statusAcc']>-1){
    		$where.= " AND pt.status = ".$search['statusAcc'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND pt.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND pt.supplierId = ".$search['supplierId'];
    	}
		if(!empty($search['paymentMethod'])){
    		$where.= " AND pt.paymentMethod = ".$search['paymentMethod'];
    	}
		if(!empty($search['bankId'])){
    		$where.= " AND pt.bankId = ".$search['bankId'];
    	}
    	$order=' ORDER BY pt.id DESC  ';
    	$where.=$dbGb->getAccessPermission("pt.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
	
    function issuePaymentInvoice($_data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_data['paymentDate']=$_data['paymentDate'];
			$paymentNo =$dbGBstock->generatePaymentNo($_data);
			
			$_arr=array(
    				'projectId'	  			=> $_data['branch_id'],
    				'paymentNo'	  			=> $paymentNo,
    				'supplierId'	    	=> $_data['supplierId'],
    				'paymentDate'			=> $_data['paymentDate'],
					
    				'paymentMethod'	  		=> $_data['paymentMethod'],
    				'bankId'      			=> $_data['bankId'],
    				'accNameAndChequeNo'    => $_data['accNameAndChequeNo'],
    				'note'      			=> $_data['note'],
					
					'balance'      			=> $_data['balance'],
					'totalDue'      		=> $_data['totalDue'],
					'totalAmount'      		=> $_data['totalAmount'],
    				'createDate'			=> date("Y-m-d H:i:s"),
    				'modifyDate'	  		=> date("Y-m-d H:i:s"),
    				'status'				=> 1,
    				'userId'  				=>$this->getUserId(),
    		);	
			$this->_name ='st_payment';
    		$paymentId =  $this->insert($_arr);			
    		$ids = explode(',', $_data['identity']);
    		$dueafter=0;
			if(!empty($_data['identity'])){
				foreach ($ids as $i){
					$is_payment =0;
					$arrFilter = array(
							'invoiceId'=>$_data['invoiceId'.$i],
							'projectId'=>$_data['branch_id'],
					);
					$rsInvoice = $this->getInvoiceInfo($arrFilter);
					$paid = (float)$_data['paymentAmount'.$i];
					if (!empty($rsInvoice)){
						$dueafter = $rsInvoice['totalAmountExternalAfter']-$paid;
						if ($dueafter>0){
							$is_payment=0;
						}else{
							$is_payment=1;
						}
						
						// update Invoice Balance
						$array=array(
								'isPaid'=>$is_payment,
								'totalAmountExternalAfter'=>$dueafter,
						);
						$where="id=".$_data['invoiceId'.$i]." AND projectId =".$_data['branch_id'];
						$this->_name="st_invoice";
						$this->update($array, $where);
					}
					
					$arrs = array(
							'paymentId'=>$paymentId,
							'invoiceId'=>$_data['invoiceId'.$i],
							'dueAmount'=>$_data['dueAmount'.$i],
							'paymentAmount'=>$_data['paymentAmount'.$i],
							'remain'=>$_data['remain'.$i],
					);
					$this->_name ='st_payment_detail';
					$this->insert($arrs);
				}
			}
			
			$db->commit();
			return $paymentId;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
	
	function editPaymentInvoice($_data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_arr=array(
    				'projectId'	  			=> $_data['branch_id'],
    				
    				'supplierId'	    	=> $_data['supplierId'],
    				'paymentDate'			=> $_data['paymentDate'],
					
    				'paymentMethod'	  		=> $_data['paymentMethod'],
    				'bankId'      			=> $_data['bankId'],
    				'accNameAndChequeNo'    => $_data['accNameAndChequeNo'],
    				'note'      			=> $_data['note'],
					
					'balance'      			=> $_data['balance'],
					'totalDue'      		=> $_data['totalDue'],
					'totalAmount'      		=> $_data['totalAmount'],
    				'modifyDate'	  		=> date("Y-m-d H:i:s"),
    				'status'				=> $_data['status'],
    				'userId'  				=>$this->getUserId(),
    		);	
			$this->_name ='st_payment';
			
			$paymentId = $_data['id'];
    		$where = " id = ".$paymentId;
    		$this->update($_arr, $where);
			
			//Revert To Old Data
			$row = $this->getPaymentDetail($paymentId);
    		if (!empty($row)) foreach ($row as $payDetail){
				$rowPaymentDetails = $this->getPaymentDetailByPaymentIdAndInvoiceId($paymentId, $payDetail['invoiceId']);
    			
    			if (!empty($rowPaymentDetails)){
					$arrFilter = array(
							'invoiceId'=>$payDetail['invoiceId'],
							'projectId'=>$_data['branch_id'],
					);
					$rsInvoice = $this->getInvoiceInfo($arrFilter);
    					
    				$dueAmount=$rowPaymentDetails['paymentAmount'];
    				$paymentDetailbyInvoiceId = $this->getSumPaymentDetailByInvoiceId($payDetail['invoiceId'], $payDetail['id']);// get other paymentAmount on this Invoice on other PaymentNumber
    				$dueAfters = $rsInvoice['totalAmountExternalAfter']+$dueAmount;
//     				
    				if (!empty($paymentDetailbyInvoiceId['tolalPayAmount'])){
    					$dueAmount = ($rowPaymentDetails['totalAmountExternal']-$paymentDetailbyInvoiceId['tolalPayAmount']);
    					$dueAfters =$dueAmount;
    				}
    				
    				if ($dueAfters>0){
    					$is_payment=0;
    				}else{
    					$is_payment=1;
    				}
					
					// update Invoice Balance
					$array=array(
							'isPaid'=>$is_payment,
							'totalAmountExternalAfter'=>$dueAfters,
					);
					$where="id=".$payDetail['invoiceId']." AND projectId =".$_data['branch_id'];
					$this->_name="st_invoice";
					$this->update($array, $where);
				}
			}
			
			if($_data['status']==1){ //For Only Active Payment
				
				$ids = explode(',', $_data['identity']);
				$detailidlist = '';
				if(!empty($_data['identity'])){
					foreach ($ids as $i){
						if (empty($detailidlist)){
							if (!empty($_data['detailid'.$i])){
								$detailidlist= $_data['detailid'.$i];
							}
						}else{
							if (!empty($_data['detailid'.$i])){
								$detailidlist = $detailidlist.",".$_data['detailid'.$i];
							}
						}
					}
				}
				// Delete Old PaymentDetail that don't have For This Edit
				$this->_name="st_payment_detail";
				$where2=" paymentId = ".$paymentId;
				if (!empty($detailidlist)){ // check if has old payment detail  detailId
					$where2.=" AND id NOT IN (".$detailidlist.")";
				}
				$this->delete($where2);
				
				$dueafter=0;
				if(!empty($_data['identity'])){
					foreach ($ids as $i){
						$is_payment =0;
						$arrFilter = array(
								'invoiceId'=>$_data['invoiceId'.$i],
								'projectId'=>$_data['branch_id'],
						);
						$rsInvoice = $this->getInvoiceInfo($arrFilter);
						$paid = (float)$_data['paymentAmount'.$i];
						if (!empty($rsInvoice)){
							$dueafter = $rsInvoice['totalAmountExternalAfter']-$paid;
							if ($dueafter>0){
								$is_payment=0;
							}else{
								$is_payment=1;
							}
							
							// update Invoice Balance
							$array=array(
									'isPaid'=>$is_payment,
									'totalAmountExternalAfter'=>$dueafter,
							);
							$where="id=".$_data['invoiceId'.$i]." AND projectId =".$_data['branch_id'];
							$this->_name="st_invoice";
							$this->update($array, $where);
						}
						if (!empty($_data['detailid'.$i])){
							$arrs = array(
									'paymentId'=>$paymentId,
									'invoiceId'=>$_data['invoiceId'.$i],
									'dueAmount'=>$_data['dueAmount'.$i],
									'paymentAmount'=>$_data['paymentAmount'.$i],
									'remain'=>$_data['remain'.$i],
							);
							$this->_name ='st_payment_detail';
							$where=" id= ".$_data['detailid'.$i];
							$this->update($arrs, $where);
						}else{
							$arrs = array(
									'paymentId'=>$paymentId,
									'invoiceId'=>$_data['invoiceId'.$i],
									'dueAmount'=>$_data['dueAmount'.$i],
									'paymentAmount'=>$_data['paymentAmount'.$i],
									'remain'=>$_data['remain'.$i],
							);
							$this->_name ='st_payment_detail';
							$this->insert($arrs);
						}
					}
				}
			
			}
			
			
			
			$db->commit();
			return $paymentId;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
	
	function getInvoiceInfo($data=array()){
    	$db = $this->getAdapter();
		$recordId = empty($data['invoiceId'])?0:$data['invoiceId'];
		$projectId = empty($data['projectId'])?0:$data['projectId'];
		$dbGb = new Application_Model_DbTable_DbGlobal();		
			$this->_name='st_invoice';
			$sql=" SELECT po.* FROM $this->_name AS po WHERE po.id=".$recordId;
			$sql.=" AND po.projectId=$projectId ";
			$sql.=$dbGb->getAccessPermission("po.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	
	function getDataRowPayment($recordId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();		
			$this->_name='st_payment';
			$sql=" SELECT pt.*,DATE_FORMAT(pt.paymentDate,'%d-%m-%Y') AS paymentDateDMY, ";
			$sql.="
				(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pt.`paymentMethod` LIMIT 1) AS paymentMethodTitle,
				(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pt.`bankId` LIMIT 1) AS bankName,
				(SELECT GROUP_CONCAT((SELECT inv.invoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) AS invoiceNoList,
				(SELECT GROUP_CONCAT((SELECT inv.supplierInvoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) AS supplierInvoiceNoList ";
			$sql.=" FROM $this->_name AS pt WHERE pt.id= ".$recordId;
			$sql.=$dbGb->getAccessPermission("pt.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	
	
	function getPaymentDetail($paymentId){
		$db = $this->getAdapter();
    	$sql="SELECT pd.* 
				FROM `st_payment_detail` AS pd WHERE pd.paymentId =$paymentId ";
		return $db->fetchAll($sql);
	}
	 function getSumPaymentDetailByInvoiceId($invoiceId,$paymentDetailId){
    	$db = $this->getAdapter();
    	$sql="SELECT SUM(pd.`paymentAmount`) AS tolalPayAmount FROM `st_payment_detail` AS pd WHERE pd.`invoiceId`=$invoiceId AND pd.`id` != $paymentDetailId AND (SELECT p.`status`=1 FROM `st_payment` AS p WHERE p.`id` = pd.`paymentId` LIMIT 1) =1 ";
    	return $db->fetchRow($sql);
    }
	function getPaymentDetailByPaymentIdAndInvoiceId($paymentId,$invoiceId){
    	$db = $this->getAdapter();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'keyIndex'=>"inv.ivType",
			'typeKeyIndex'=>3,
		);
		
    	$sql="SELECT pd.*,
				inv.receiveIvDate,
				inv.invoiceNo,
				inv.supplierInvoiceNo,
		
				inv.totalAmountExternal,
				inv.totalAmountExternalAfter
    	 ";
		$sql.=$dbGBstock->invoiceTypeKey($arrStep);
		$sql.="
			FROM 
			`st_payment_detail` AS pd 
			LEFT JOIN `st_invoice` AS inv ON inv.id = pd.invoiceId 
		WHERE pd.paymentId =$paymentId AND pd.invoiceId =$invoiceId LIMIT 1
		";
    	return $db->fetchRow($sql);
    }
   
}