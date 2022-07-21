<?php

class Invpayment_Model_DbTable_DbPayment extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_payment';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
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
   
}