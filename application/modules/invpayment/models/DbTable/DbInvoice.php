<?php

class Invpayment_Model_DbTable_DbInvoice extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_invoice';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllInvoice($search){
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
	
	function checkOtherDnOfPurchasing($data){
		$db = $this->getAdapter();
		$purchaseId =$data['purchaseId'];
		$dnIdList =$data['dnIdList'];
		$sql="SELECT rst.id FROM `st_receive_stock` AS rst 
				WHERE rst.poId =$purchaseId 
					
					AND rst.id NOT IN($dnIdList)";
		if(empty($data['editCheking'])){
			$sql.=" AND rst.isIssueInvoice=0 ";
		}else{
			$sql.=" AND rst.isIssueInvoice=1 ";
		}
		return $db->fetchOne($sql);
	}
	function issueInvoice($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$data['receiveIvDate']=$data['receiveIvDate'];
			$invoiceNo =$dbGBstock->generateInvoiceNo($data);

			$purchaseId =$data['purId'];
		
		
			$arrStep = array(
				'keyIndex'=>$data['ivType'],
				'typeKeyIndex'=>1,
			);
			$ivType = $dbGBstock->invoiceTypeKey($arrStep);
			
			$dnId="";
			if(!empty($data['dnIdentity'])){
				$dnIdentityIds = explode(',', $data['dnIdentity']);
				foreach ($dnIdentityIds as $i){
					if(empty($dnId)){
						$dnId = empty($data['deliveryId'.$i])?"":$data['deliveryId'.$i];
					}else{
						$dnId = empty($data['deliveryId'.$i])?$dnId:$dnId.",".$data['deliveryId'.$i];
					}
				}
			}
			$dbPO = new Po_Model_DbTable_DbPurchasing();
			$rowPO = $dbPO->getDataRow($purchaseId);
			if(!empty($rowPO)){
				$isInvoiced=2;//Some Issued DN to Invoice
				if($rowPO['processingStatus']==1){
					if(!empty($dnId)){
						$arrCheck = array(
							'purchaseId'	=>$purchaseId,
							'dnIdList'		=>$dnId,
						);
						$checkingOtherDN = $this->checkOtherDnOfPurchasing($arrCheck);
						if(empty($checkingOtherDN)){
							$isInvoiced=1;//Completed Issued DN to Invoice
						}
					}
				}
				$arrPo = array(
					'isInvoiced'		=>$isInvoiced,
				);
				$this->_name='st_purchasing';
				$wherePO =" id =".$purchaseId;
				$this->update($arrPo, $wherePO);
			}
			if(!empty($dnId)){
				$arrDN = array(
					'isIssueInvoice'		=>1,
				);
				$this->_name='st_receive_stock';
				$whereDN =" id IN (".$dnId.")";
				$this->update($arrDN, $whereDN);
			}
			
    		$arr = array(
    				'projectId'			=>$data['branch_id'],
    				'ivType'			=>$ivType,
    				'invoiceNo'			=>$invoiceNo,
    				'dnId'				=>$dnId,
    				'supplierId'		=>$data['supplierId'],
					
    				'invoiceDate'				=>$data['invoiceDate'],
    				'supplierInvoiceNo'			=>$data['supplierInvoiceNo'],
    				'receiveIvDate'				=>$data['receiveIvDate'],
    				'purId'						=>$purchaseId,
    				'note'						=>$data['note'],
					
					'totalInternal'				=>$data['totalInternal'],
    				'totalAmount'				=>$data['totalAmount'],
					
    				'totalExternal'				=>$data['totalExternal'],
    				'totalAmountExternal'		=>$data['totalAmountExternal'],
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
							
							'qtyPo'				=>$data['purchaseQty'.$i],
							'unitPrice'			=>$data['purchaseUnitPrice'.$i],
							'discountAmount'	=>$data['purchaseDiscountAmount'.$i],
							'total'				=>$data['purchaseSubTotal'.$i],
							
							'totalQtyReceive'			=>$data['totalQtyReceive'.$i],
							'unitPriceReceive'			=>$data['price'.$i],
							'totalReceiveDiscount'		=>$data['discountAmount'.$i],
							'totalReceive'				=>$data['subTotal'.$i],
							
						);
					$this->_name='st_invoice_detail';	
					$this->insert($arr);
				}
    		}
			
			if(!empty($data['identityService'])){
				$ids = explode(',', $data['identityService']);
				foreach ($ids as $i){
					$arr = array(
							'invId'				=>$id,
							'type'				=>$data['isService'.$i],
							'proId'				=>$data['serviceId'.$i],
								
							'qtyPo'				=>1,
							'discountAmount'	=>0,
							'unitPrice'			=>$data['totalServiceInternal'.$i],
							'total'				=>$data['totalServiceInternal'.$i],
							
							//received
							'totalQtyReceive'			=>1,
							'unitPriceReceive'			=>$data['totalService'.$i],
							'totalReceiveDiscount'		=>0,
							'totalReceive'				=>$data['totalService'.$i],
							
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
	
	function editIssueInvoice($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$id = $data['id'];
			$purchaseId =$data['purId'];
			
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$dbPO = new Po_Model_DbTable_DbPurchasing();
			$row = $this->getDataRow($id);
			if(!empty($row)){
				$rowPO = $dbPO->getDataRow($purchaseId);
				if(!empty($rowPO)){
					$isInvoiced=2;//Some Issued DN to Invoice
					if($rowPO['processingStatus']==1){
						if(!empty($row['dnId'])){
							$arrCheck = array(
								'purchaseId'	=>$purchaseId,
								'dnIdList'		=>$row['dnId'],
								'editCheking'	=>1,
							);
							$checkingOtherDN = $this->checkOtherDnOfPurchasing($arrCheck);
							if(empty($checkingOtherDN)){
								$isInvoiced=0;//Completed Issued DN to Invoice
							}
						}
					}
					$arrPo = array(
						'isInvoiced'		=>$isInvoiced,
					);
					$this->_name='st_purchasing';
					$wherePO =" id =".$purchaseId;
					$this->update($arrPo, $wherePO);
				}
				if(!empty($row['dnId'])){
					$arrDN = array(
						'isIssueInvoice'		=>0,
					);
					$this->_name='st_receive_stock';
					$whereDN =" id IN (".$row['dnId'].")";
					$this->update($arrDN, $whereDN);
				}
			}
			
			$arr = array(
					'note'						=>$data['note'],
					'status'			=>$data['status'],
					'modifyDate'		=>date("Y-m-d H:i:s"),
					'userId'			=>$this->getUserId(),
					);
					
			
			$this->_name='st_invoice';
			$where = 'id = '.$id;
			$this->update($arr, $where);
				
			if($data['status']==1){
				$dnId="";
				if(!empty($data['dnIdentity'])){
					$dnIdentityIds = explode(',', $data['dnIdentity']);
					foreach ($dnIdentityIds as $i){
						if(empty($dnId)){
							$dnId = empty($data['deliveryId'.$i])?"":$data['deliveryId'.$i];
						}else{
							$dnId = empty($data['deliveryId'.$i])?$dnId:$dnId.",".$data['deliveryId'.$i];
						}
					}
				}
				
				$rowPO = $dbPO->getDataRow($purchaseId);
				if(!empty($rowPO)){
					$isInvoiced=2;//Some Issued DN to Invoice
					if($rowPO['processingStatus']==1){
						if(!empty($dnId)){
							$arrCheck = array(
								'purchaseId'	=>$purchaseId,
								'dnIdList'		=>$dnId,
							);
							$checkingOtherDN = $this->checkOtherDnOfPurchasing($arrCheck);
							if(empty($checkingOtherDN)){
								$isInvoiced=1;//Completed Issued DN to Invoice
							}
						}
					}
					$arrPo = array(
						'isInvoiced'		=>$isInvoiced,
					);
					$this->_name='st_purchasing';
					$wherePO =" id =".$purchaseId;
					$this->update($arrPo, $wherePO);
				}
				if(!empty($dnId)){
					$arrDN = array(
						'isIssueInvoice'		=>1,
					);
					$this->_name='st_receive_stock';
					$whereDN =" id IN (".$dnId.")";
					$this->update($arrDN, $whereDN);
				}
				$arr = array(
						
						'dnId'				=>$dnId,
						'supplierId'		=>$data['supplierId'],
						
						'invoiceDate'				=>$data['invoiceDate'],
						'supplierInvoiceNo'			=>$data['supplierInvoiceNo'],
						'receiveIvDate'				=>$data['receiveIvDate'],
						'purId'						=>$data['purId'],
						'note'						=>$data['note'],
						
						'totalInternal'				=>$data['totalInternal'],
						'totalAmount'				=>$data['totalAmount'],
						
						'totalExternal'				=>$data['totalExternal'],
						'totalAmountExternal'		=>$data['totalAmountExternal'],
						'totalAmountExternalAfter'	=>$data['totalAmountExternal'],
						
						'status'			=>$data['status'],
						'modifyDate'		=>date("Y-m-d H:i:s"),
						'userId'			=>$this->getUserId(),
						
						);
						
				
				$this->_name='st_invoice';
				$where = 'id = '.$id;
				$this->update($arr, $where);
			
			
				$this->_name='st_invoice_detail';
				$whereDl2 = 'invId = '.$id;
				$this->delete($whereDl2);
			
				if(!empty($data['identity'])){
					$ids = explode(',', $data['identity']);
					foreach ($ids as $i){
						
						$arr = array(
								'invId'		=>$id,
								'type'				=>$data['type'.$i],
								'proId'				=>$data['proId'.$i],
								
								'qtyPo'				=>$data['purchaseQty'.$i],
								'unitPrice'			=>$data['purchaseUnitPrice'.$i],
								'discountAmount'	=>$data['purchaseDiscountAmount'.$i],
								'total'				=>$data['purchaseSubTotal'.$i],
								
								'totalQtyReceive'			=>$data['totalQtyReceive'.$i],
								'unitPriceReceive'			=>$data['price'.$i],
								'totalReceiveDiscount'		=>$data['discountAmount'.$i],
								'totalReceive'				=>$data['subTotal'.$i],
								
							);
						$this->_name='st_invoice_detail';	
						$this->insert($arr);
					}
				}
				
				if(!empty($data['identityService'])){
					$ids = explode(',', $data['identityService']);
					foreach ($ids as $i){
						$arr = array(
								'invId'				=>$id,
								'type'				=>$data['isService'.$i],
								'proId'				=>$data['serviceId'.$i],
									
								'qtyPo'				=>1,
								'discountAmount'	=>0,
								'unitPrice'			=>$data['totalServiceInternal'.$i],
								'total'				=>$data['totalServiceInternal'.$i],
								
								//received
								'totalQtyReceive'			=>1,
								'unitPriceReceive'			=>$data['totalService'.$i],
								'totalReceiveDiscount'		=>0,
								'totalReceive'				=>$data['totalService'.$i],
								
							);
						$this->_name='st_invoice_detail';	
						$this->insert($arr);
					}
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
			$sql=" SELECT inv.*,(SELECT po.purchaseType FROM st_purchasing AS po WHERE po.id = inv.purId LIMIT 1) AS purchaseType 
			
			FROM $this->_name AS inv WHERE inv.id=".$recordId;
			$sql.=$dbGb->getAccessPermission("inv.projectId");
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
	
	
	function getCurrentBalanceBySupplier($data){
    	$db = $this->getAdapter();
    	$sql = "SELECT SUM(inv.`totalAmountExternal`) AS gTotalBalance FROM `st_invoice` AS inv WHERE inv.`status`=1 AND inv.`isPaid`=0 AND inv.`supplierId`=".$data['supplierId']." AND inv.projectId =".$data['branch_id'];
    	return $db->fetchOne($sql);
    }
	function getAllInvoiceBySupplier($data){
    	$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'keyIndex'=>"inv.ivType",
			'typeKeyIndex'=>3,
		);
		
		
			
    	$supplier_id = empty($data['supplierId'])?0:$data['supplierId'];
    	$branch_id = $data['branch_id'];
    	$sql="SELECT inv.*
					,po.purchaseNo 
					,po.date AS purchaseDate 
					,rq.requestNo 
					,rq.requestNoLetter 
					,rq.purpose AS requestPurpose 
			";
    	$sql.=$dbGBstock->invoiceTypeKey($arrStep);
    	$sql.=" FROM `st_invoice` AS inv 
					LEFT JOIN st_purchasing AS po ON po.id = inv.purId 
					LEFT JOIN st_request_po AS rq ON rq.id = po.requestId 
				WHERE  inv.status=1 
						AND inv.isPaid = 0 
						AND inv.projectId =$branch_id  ";
    	$sql.=" AND inv.supplierId =$supplier_id ";
		
    	$from_date =(empty($data['start_date']))? '1': " inv.receiveIvDate >= '".date("Y-m-d",strtotime($data['start_date']))." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " inv.receiveIvDate <= '".date("Y-m-d",strtotime($data['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	if (!empty($data['advanceFilter'])){
			
			$s_where = array();
			$s_search = trim(addslashes($data['advanceFilter']));
			$s_where[] = " inv.invoiceNo LIKE '%{$s_search}%'";
			$s_where[] = " inv.supplierInvoiceNo LIKE '%{$s_search}%'";
			$s_where[] = " inv.note LIKE '%{$s_search}%'";
			$sql .=' AND ('.implode(' OR ',$s_where).')';
				
    	}
    	$rs = $db->fetchAll($sql);
    	
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
				
				$classRowBg = "odd";
				if(($key%2)==0){
				$classRowBg = "regurlar";
				}
    			$string.='
    			<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
    				<td align="center" style="  padding: 0 10px;"><input  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
	    			<td class="textCenter">'.($key+1).'</td>
	    			<td class="textCenter">
	    				<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['receiveIvDate'])).'<br /><small>'.$row['ivTypeTitle'].'</small></label>
	    				<input type="hidden" dojoType="dijit.form.TextBox" name="invoiceId'.$no.'" id="invoiceId'.$no.'" value="'.$row['id'].'" >
    				</td>
					<td class="invNoCol">
						<label id="titleInvoice'.$no.'">'.$row['invoiceNo'].'<br />'.$row['supplierInvoiceNo'].'</label>
						<span>'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].'</span>
    				</td>
					<td class="textCenter">
						<label id="origtotallabel'.$no.'">'.number_format($row['totalAmountExternal'],2).'</label>
					</td>
					<td class="textCenter">
						<label id="duelabel'.$no.'">'.number_format($row['totalAmountExternalAfter'],2).'</label>
						<input type="hidden" dojoType="dijit.form.TextBox" name="dueAmount'.$no.'" id="dueAmount'.$no.'" value="'.$row['totalAmountExternalAfter'].'" >
					</td>
					
					<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="paymentAmount'.$no.'" id="paymentAmount'.$no.'" value="0" style="text-align: center;" ></td>
					<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['totalAmountExternalAfter'].'" style="text-align: center;" ></td>
				</tr>
    			';$no++;
    		}
    	}else{
    		$no++;
    	}
    	$gTotalBalance =0;
    	$supplierBalace = $this->getCurrentBalanceBySupplier($data);
    	if (!empty($supplierBalace)){
    		$gTotalBalance = $supplierBalace;
    	}
    	$array = array('stringrow'=>$string,'keyindex'=>$no,'identity'=>$identity,'gTotalBalance'=>$gTotalBalance);
    	return $array;
    }
	
	
	function getAllInvoiceBySupplierEdit($data){
    	$db = $this->getAdapter();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbPayment = new Invpayment_Model_DbTable_DbPayment();
		$rsPmtDetail = $dbPayment->getPaymentDetail($data['paymentId']);
    	
		$invoiceListInPayment ='';
    	if (!empty($rsPmtDetail)) foreach ($rsPmtDetail as $rsDetail){
    		if (empty($invoiceListInPayment)){
    			$invoiceListInPayment=$rsDetail['invoiceId'];
    		}else{$invoiceListInPayment=$invoiceListInPayment.",".$rsDetail['invoiceId'];
    		}
    	}
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'keyIndex'=>"inv.ivType",
			'typeKeyIndex'=>3,
		);
		
			
    	$supplier_id = empty($data['supplierId'])?0:$data['supplierId'];
    	$branch_id = $data['branch_id'];
    	$sql="SELECT inv.*
				,po.purchaseNo 
				,po.date AS purchaseDate 
				,rq.requestNo 
				,rq.requestNoLetter 
				,rq.purpose AS requestPurpose
		";
    	$sql.=$dbGBstock->invoiceTypeKey($arrStep);
    	$sql.=" FROM `st_invoice` AS inv 
					LEFT JOIN st_purchasing AS po ON po.id = inv.purId 
					LEFT JOIN st_request_po AS rq ON rq.id = po.requestId 
				WHERE  inv.status=1 
						AND inv.isPaid = 0 
						AND inv.projectId =$branch_id  ";
    	$sql.=" AND inv.supplierId =$supplier_id ";
		if (!empty($invoiceListInPayment)){
    		$sql.=" OR inv.`id` IN ($invoiceListInPayment) ";
    	}
		
    	$from_date =(empty($data['start_date']))? '1': " inv.receiveIvDate >= '".date("Y-m-d",strtotime($data['start_date']))." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " inv.receiveIvDate <= '".date("Y-m-d",strtotime($data['end_date']))." 23:59:59'";
    	$sql.= " AND  ".$from_date." AND ".$to_date;
    	if (!empty($data['advanceFilter'])){
			
			$s_where = array();
			$s_search = trim(addslashes($data['advanceFilter']));
			$s_where[] = " inv.invoiceNo LIKE '%{$s_search}%'";
			$s_where[] = " inv.supplierInvoiceNo LIKE '%{$s_search}%'";
			$s_where[] = " inv.note LIKE '%{$s_search}%'";
			$sql .=' AND ('.implode(' OR ',$s_where).')';
				
    	}
    	$rs = $db->fetchAll($sql);
		
		
    	
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
		$identityEdit='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
				
				$classRowBg = "odd";
				if(($key%2)==0){
				$classRowBg = "regurlar";
				}
				
				$rowPaymentdetail = $dbPayment->getPaymentDetailByPaymentIdAndInvoiceId($data['paymentId'], $row['id']);
    			
				if (!empty($rowPaymentdetail)){
					
					$dueAmount=$rowPaymentdetail['dueAmount'];
    				$paymentDetailbyInvoiceId = $dbPayment->getSumPaymentDetailByInvoiceId($rowPaymentdetail['invoiceId'], $rowPaymentdetail['id']);// get other paymentAmount on this Invoice on other PaymentNumber
    				if (!empty($paymentDetailbyInvoiceId)){
    					$dueAmount = $rowPaymentdetail['totalAmountExternal']-$paymentDetailbyInvoiceId['tolalPayAmount'];
    				}
					
					if (empty($identityEdit)){
    					$identityEdit=$no;
    				}else{$identityEdit=$identityEdit.",".$no;
    				}
					
					$string.='
					<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
						<td align="center" style="  padding: 0 10px;"><input checked="checked" OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
						<td class="textCenter">'.($key+1).'</td>
						<td class="textCenter">
							<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($rowPaymentdetail['receiveIvDate'])).'<br /><small>'.$rowPaymentdetail['ivTypeTitle'].'</small></label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="paymentId'.$no.'" id="paymentId'.$no.'" value="'.$rowPaymentdetail['paymentId'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="invoiceId'.$no.'" id="invoiceId'.$no.'" value="'.$rowPaymentdetail['invoiceId'].'" >
							<input type="hidden" dojoType="dijit.form.TextBox" name="detailid'.$no.'" id="detailid'.$no.'" value="'.$rowPaymentdetail['id'].'" >
						</td>
						<td class="invNoCol">
							
							<label id="titleInvoice'.$no.'">
							'.$rowPaymentdetail['invoiceNo'].'<br />'.$rowPaymentdetail['supplierInvoiceNo'].'
							</label>
							<span>'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].'</span>
						</td>
					
						<td class="textCenter">
							<label id="origtotallabel'.$no.'">'.number_format($rowPaymentdetail['totalAmountExternal'],2).'</label>
						</td>
						<td class="textCenter">
							<label id="duelabel'.$no.'">'.number_format($dueAmount,2).'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="dueAmount'.$no.'" id="dueAmount'.$no.'" value="'.$dueAmount.'" >
						</td>
						
						<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="paymentAmount'.$no.'" id="paymentAmount'.$no.'" value="'.$rowPaymentdetail['paymentAmount'].'" style="text-align: center;" ></td>
						<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$rowPaymentdetail['remain'].'" style="text-align: center;" ></td>
					</tr>
					';
				}else{
					$string.='
					<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
						<td align="center" style="  padding: 0 10px;"><input  OnChange="CheckAllTotal('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
						<td class="textCenter">'.($key+1).'</td>
						<td class="textCenter">
							<label id="billingdatelabel'.$no.'">'.date("d-M-Y",strtotime($row['receiveIvDate'])).'<br /><small>'.$row['ivTypeTitle'].'</small></label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="invoiceId'.$no.'" id="invoiceId'.$no.'" value="'.$row['id'].'" >
						</td>
						<td class="invNoCol">
							
							<label id="titleInvoice'.$no.'">
								'.$row['invoiceNo'].'<br />'.$row['supplierInvoiceNo'].'	
							</label>
							<span>'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].'</span>
						</td>
					
						<td class="textCenter">
							<label id="origtotallabel'.$no.'">'.number_format($row['totalAmountExternal'],2).'</label>
						</td>
						<td class="textCenter">
							<label id="duelabel'.$no.'">'.number_format($row['totalAmountExternalAfter'],2).'</label>
							<input type="hidden" dojoType="dijit.form.TextBox" name="dueAmount'.$no.'" id="dueAmount'.$no.'" value="'.$row['totalAmountExternalAfter'].'" >
						</td>
						
						<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="paymentAmount'.$no.'" id="paymentAmount'.$no.'" value="0" style="text-align: center;" ></td>
						<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="remain'.$no.'" id="remain'.$no.'" value="'.$row['totalAmountExternalAfter'].'" style="text-align: center;" ></td>
					</tr>
					';
				}
				$no++;
    		}
    	}else{
    		$no++;
    	}
		
    	$gTotalBalance =0;
    	$supplierBalace = $this->getCurrentBalanceBySupplier($data);
    	if (!empty($supplierBalace)){
    		$gTotalBalance = $supplierBalace;
    	}
    	$array = array(
					'stringrow'=>$string,
					'keyindex'=>$no,
					'identity'=>$identity,
					'identitycheck'=>$identityEdit,
					'gTotalBalance'=>$gTotalBalance);
    	return $array;
    }
	
	function getDnIDInInvoice($data){
		$db = $this->getAdapter();
		$purchaseId = empty($data['purchaseId'])?0:$data['purchaseId'];
    	$branchId = $data['branch_id'];
		$sql=" 
		SELECT 
			GROUP_CONCAT(inv.dnId) FROM `st_invoice` AS inv 
			WHERE inv.status=1
		";
		if(!empty($data['currentInvoiceId'])){
			$sql.=" AND inv.id!=".$data['currentInvoiceId'];
		}
		$sql.=" AND inv.projectId =$branchId ";
		$sql.=" AND inv.purId =$purchaseId ";
		$rs = $db->fetchOne($sql);
		return $rs;
	}
	
	function getDnList($data){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
    	$purchaseId = empty($data['purchaseId'])?0:$data['purchaseId'];
    	$branchId = $data['branch_id'];
		
		$sql="
			SELECT rst.*
				,rq.requestNo
				,rq.requestNoLetter
				,rq.date AS requestDate
				,rq.purpose AS requestPurpose
				,po.purchaseNo AS purchaseNo
				,po.date AS purchaseDate
		";
		$sql.="
			FROM st_receive_stock AS rst 
				JOIN `st_purchasing`AS po ON po.id = rst.poId 
				LEFT JOIN `st_request_po` AS rq ON rq.id = po.requestId 
		";
		$sql.=" WHERE rst.status=1 ";
		$sql.=" AND rst.verified =1 ";
		$sql.=" AND rst.projectId =$branchId ";
    	$sql.=" AND rst.poId =$purchaseId ";
		
		$DNListFromInvoice = $this->getDnIDInInvoice($data);
		if(!empty($DNListFromInvoice)){ //For Only Available DN Of Purchase To Make Invoice
			$sql.=" AND rst.id NOT IN ($DNListFromInvoice) ";
		}
		
		$rs = $db->fetchAll($sql);
		
		
		$string='';
    	$no = $data['dnKeyIndex'];
    	$dnIdentity='';
		$dnIdentityChecked='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$gTotalInternal = 0;
		$gTotalExternal = 0;
		$string.='<ul class="listDn">';
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($dnIdentity)){
    				$dnIdentity=$no;
    			}else{$dnIdentity=$dnIdentity.",".$no;
    			}
				if(!empty($data['dnId'])){
					$currentInvoice = $data['dnId'];
					if(!empty($currentInvoice)){
						$dnIds = explode(",",$currentInvoice);
						$true=0;
						foreach ($dnIds as $iid){
							if($iid==$row['id']){
								if (empty($dnIdentityChecked)){
									$dnIdentityChecked=$no;
								}else{$dnIdentityChecked=$dnIdentityChecked.",".$no;
								
								}
								$true=1;
								$string.='
									<li>
										<span  class="dnNumber"><input checked="checked" OnChange="checkAllDn('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/> '.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
										<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
										<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
										<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
									</li>
								';
								break;
							}
						}
						if($true==0){
							$string.='
								<li>
									<span  class="dnNumber"><input  OnChange="checkAllDn('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/> '.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
									<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
									<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
									<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
								</li>
							';
						}
					}else{
						$string.='
							<li>
								<span  class="dnNumber"><input  OnChange="checkAllDn('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/> '.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
								<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
								<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
								<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
							</li>
						';
					}
				}else{
					if (empty($dnIdentityChecked)){
						$dnIdentityChecked=$no;
					}else{$dnIdentityChecked=$dnIdentityChecked.",".$no;
					
					}
					$string.='
							<li>
								<span  class="dnNumber"><input checked="checked" OnChange="checkAllDn('.$no.')" style=" vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/> '.$row['dnNumber'].' '.date("d-m-Y",strtotime($row['receiveDate'])).'</span>
								<small class="poNumber">'.$tr->translate("PO_NO").' : '.$row['purchaseNo'].' '.date("d-m-Y",strtotime($row['purchaseDate'])).'</small>
								<small class="requestNumber">'.$tr->translate("REQUEST_NO").' : '.$row['requestNo'].' '.date("d-m-Y",strtotime($row['requestDate'])).'</small>
								<input type="hidden" dojoType="dijit.form.TextBox" name="deliveryId'.$no.'" id="deliveryId'.$no.'" value="'.$row['id'].'" >
							</li>
					';
				}
    			$no++;
    		}
    	}else{
    		$no++;
    	}
		$string.='</ul>';
		
		$array = array(
					'stringrow'=>$string,
					'dnKeyIndex'=>$no,
					'dnIdentity'=>$dnIdentity,
					'dnIdentityChecked'=>$dnIdentityChecked,
				);
				
    	return $array;
		
	}
	function getRowInvoiceDetail($data){
		$db = $this->getAdapter();
		$sql="SELECT invd.* FROM st_invoice_detail AS invd ";
		$sql.="WHERE invd.invId =".$data['currentInvoiceId'];
		$sql.=" AND invd.proId =".$data['proId'];
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getDnDetailTotalByPurchase($data){
		
		$db = $this->getAdapter();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
    	$purchaseId = empty($data['purchaseId'])?0:$data['purchaseId'];
		
		$dbPo = new Po_Model_DbTable_DbPurchasing();
		$recordInfo = $dbPo->getDataRow($purchaseId);
		
    	$branchId = $data['branch_id'];
    	$sql="SELECT rstd.*
					,p.proName AS productName
					,p.isService AS serviceOrProType
					,p.measureLabel AS measureTitle
					,COALESCE(SUM(rstd.qtyReceive),0) AS totalQtyReceive,
					COALESCE(SUM(rstd.qtyAfterReceive),0) AS totalQtyAfterReceive,
					COALESCE(SUM(rstd.subTotal),0) AS totalSubTotal
					,pod.qty AS purchaseQty
					,pod.unitPrice AS purchaseUnitPrice
					,pod.discountAmount AS purchaseDiscountAmount
					,pod.discountPercent AS purchaseDiscountPercent
					,pod.subTotal AS purchaseSubTotal
		";
    	$sql.=" FROM `st_receive_stock_detail` AS rstd
					JOIN `st_receive_stock` AS rst ON rst.id = rstd.receiveId 
					LEFT JOIN `st_product` AS p ON p.proId = rstd.proId
					LEFT JOIN `st_purchasing_detail` AS pod ON rst.poId = pod.purchaseId AND pod.proId = rstd.proId 
		";
    	$sql.=" WHERE rst.status =1  ";
    	$sql.=" AND rst.verified =1 ";
    	$sql.=" AND rst.projectId =$branchId ";
    	$sql.=" AND rst.poId =$purchaseId ";
		if(!empty($data['DNIdKey'])){
			$sql.=" AND rstd.receiveId IN (".$data['DNIdKey'].") ";
		}
	
		
		$sql.=" GROUP BY rstd.proId ORDER BY rstd.isClosed DESC,rstd.proId ASC ";
    	$rs = $db->fetchAll($sql);
		
		$string='';
    	$no = $data['keyindex'];
    	$identity='';
		$identityEdit='';
    	$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$gTotalInternal = 0;
		$gTotalExternal = 0;
    	if(!empty($rs)){
    		foreach ($rs as $key => $row){
    			if (empty($identity)){
    				$identity=$no;
    			}else{$identity=$identity.",".$no;
    			}
				
				$classRowBg = "odd";
				if(($key%2)==0){
				$classRowBg = "regurlar";
				}
				
				$gTotalInternal = $gTotalInternal+$row['purchaseSubTotal'];
				
				$receivePrice = $row['price'];
				$receiveDiscountAmount = $row['totalDiscount'];
				$receiveTotalSubTotal = $row['totalSubTotal'];
				if(!empty($data['currentInvoiceId'])){
					$data['proId'] = $row['proId'];
					$rowInvoiceDetail = $this->getRowInvoiceDetail($data);
					if(!empty($rowInvoiceDetail)){
						$receivePrice = $rowInvoiceDetail['unitPriceReceive'];
						$receiveDiscountAmount = $rowInvoiceDetail['totalReceiveDiscount'];
						$receiveTotalSubTotal = $rowInvoiceDetail['totalReceive'];
					}
				}
				
				$gTotalExternal = $gTotalExternal+$receiveTotalSubTotal;
    			$string.='
    			<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
	    			<td class="textCenter poInfoCol">'.($key+1).'</td>
	    			<td class="textCenter poInfoCol">&nbsp;
	    				<label id="billingdatelabel'.$no.'">'.$row['productName'].'('.$row['measureTitle'].')</label>
	    				<input type="hidden" dojoType="dijit.form.TextBox" name="proId'.$no.'" id="proId'.$no.'" value="'.$row['proId'].'" >
						<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="type'.$no.'" name="type'.$no.'" value="'.$row['serviceOrProType'].'" type="text"  >
					</td>
					<td class="textCenter poInfoCol">
						<span>'.$row['purchaseQty'].'</span><br />
						<input type="hidden" dojoType="dijit.form.TextBox" name="purchaseQty'.$no.'" id="purchaseQty'.$no.'" value="'.$row['purchaseQty'].'" >
						<input type="hidden" dojoType="dijit.form.TextBox" name="purchaseUnitPrice'.$no.'" id="purchaseUnitPrice'.$no.'" value="'.$row['purchaseUnitPrice'].'" >
						<input type="hidden" dojoType="dijit.form.TextBox" name="purchaseDiscountAmount'.$no.'" id="purchaseDiscountAmount'.$no.'" value="'.$row['purchaseUnitPrice'].'" >
						<input type="hidden" dojoType="dijit.form.TextBox" name="purchaseSubTotal'.$no.'" id="purchaseSubTotal'.$no.'" value="'.$row['purchaseSubTotal'].'" >
    				</td>
					<td class="textCenter poInfoCol">
						<span>'.number_format($row['purchaseUnitPrice'],2).'</span><br />
    				</td>
					<td class="textCenter poInfoCol">
						<span>'.number_format($row['purchaseDiscountAmount'],2).'</span><br />
    				</td>
					<td class="textCenter poInfoCol">
						<span>'.number_format($row['purchaseSubTotal'],2).'</span><br />
    				</td>
					<td class="textCenter">
						<input readOnly type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="totalQtyReceive'.$no.'" id="totalQtyReceive'.$no.'" value="'.$row['totalQtyReceive'].'" style="text-align: center;" >
					</td>
					<td><input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="price'.$no.'" id="price'.$no.'" value="'.$receivePrice.'" style="text-align: center;" ></td>
					<td class="textCenter">
						<input type="text" class="fullside" dojoType="dijit.form.NumberTextBox" required="required" onKeyup="calculateamount('.$no.');" name="discountAmount'.$no.'" id="discountAmount'.$no.'" value="'.$receiveDiscountAmount.'" style="text-align: center;" >
					</td>
					<td><input type="text" class="fullside" readonly="readonly" dojoType="dijit.form.NumberTextBox" required="required" name="subTotal'.$no.'" id="subTotal'.$no.'" value="'.$receiveTotalSubTotal.'" style="text-align: center;" ></td>
				</tr>
    			';$no++;
    		}
    	}else{
    		$no++;
    	}
		
		$array = array(
					'recordInfo'=>$recordInfo,
					'stringrow'=>$string,
					'keyindex'=>$no,
					'identity'=>$identity,
					'identitycheck'=>$identityEdit,
					'gTotalInternal'=>$gTotalInternal,
					'gTotalExternal'=>$gTotalExternal,
					
					);
    	return $array;
	}
   
}