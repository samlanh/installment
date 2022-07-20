<?php

class Po_Model_DbTable_DbDirectPO extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_purchasing';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllDirectedPO($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
				'keyIndex'=>$search['purchaseType'],
				'typeKeyIndex'=>1,
			);
		$purchaseType = $dbGBstock->purchasingTypeKey($arrStep);
		
		$sql="
			SELECT 
				po.id,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = po.projectId LIMIT 1) AS branch_name,
				po.purchaseNo,
				spp.supplierName,
				po.date,
				po.purpose,
				po.total
		";
    	$sql.=$dbGb->caseStatusShowImage("po.status");
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=po.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_purchasing` AS po 
					JOIN `st_supplier` AS spp ON spp.id = po.supplierId 
				WHERE 
					 po.purchaseType=".$purchaseType."
		";
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " po.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " po.date <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " po.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		$s_where[] = " po.purpose LIKE '%{$s_search}%'";
    		$s_where[] = " po.total LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND po.status = ".$search['status'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND po.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND po.supplierId = ".$search['supplierId'];
    	}
    	$order=' ORDER BY po.id DESC  ';
    	$where.=$dbGb->getAccessPermission("po.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function addDirectedPO($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$data['dateRequest']=$data['date'];
			$purchaseNo =$dbGBstock->generatePurchaseNo($data);

			$arrStep = array(
				'keyIndex'=>$data['purchaseType'],
				'typeKeyIndex'=>1,
			);
			$purchaseType = $dbGBstock->purchasingTypeKey($arrStep);
			
    		$arr = array(
    				'projectId'			=>$data['branch_id'],
    				'purchaseType'		=>$purchaseType,
    				'purchaseNo'		=>$purchaseNo,
    				'supplierId'		=>$data['supplierId'],
    				'date'				=>$data['date'],
    				'purpose'			=>$data['purpose'],
    				'note'				=>$data['note'],
    				'total'				=>$data['total'],
    				
					'status'			=>1,
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$this->getUserId(),
    				
    				);
    		$this->_name='st_purchasing';
    		$id = $this->insert($arr);
			
					
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					
					$arr = array(
							'purchaseId'		=>$id,
							'proId'				=>$data['proId'.$i],
								
							'qty'				=>$data['qty'.$i],
							'qtyAfter'			=>$data['qty'.$i],
							'unitPrice'			=>$data['unitPrice'.$i],
							'discountAmount'	=>$data['discountAmount'.$i],
							'subTotal'			=>$data['subTotal'.$i],
							'requestInDate'		=>$data['requestInDate'.$i],
							'note'				=>$data['note'.$i],
						);
					$this->_name='st_purchasing_detail';	
					$this->insert($arr);
				}
    		}
			
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }

    function editDirectedPO($data){
    	 
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$id = $data['id'];
			$arr = array(
				'projectId'			=>$data['branch_id'],
				'supplierId'		=>$data['supplierId'],
				'date'				=>$data['date'],
				'purpose'			=>$data['purpose'],
				'note'				=>$data['note'],
				'total'				=>$data['total'],
				
				'status'			=>$data['status'],
				'modifyDate'		=>date("Y-m-d H:i:s"),
				'userId'			=>$this->getUserId(),
				
				);
			
    		$this->_name='st_purchasing';
    		$where = 'id = '.$id;
			$this->update($arr, $where);
			
			if($data['status']==1){
				
				$identitys = explode(',',$data['identity']);
				$detailId="";
				if (!empty($identitys)){
					foreach ($identitys as $i){
						if (empty($detailId)){
							if (!empty($data['detailId'.$i])){
								$detailId = $data['detailId'.$i];
							}
						}else{
							if (!empty($data['detailId'.$i])){
								$detailId= $detailId.",".$data['detailId'.$i];
							}
						}
					}
				}
				$this->_name='st_purchasing_detail';
				$whereDl = 'purchaseId = '.$id;
				if (!empty($detailId)){
					$whereDl.=" AND id NOT IN ($detailId) ";
				}
				$this->delete($whereDl);
				
				if(!empty($data['identity'])){
					$ids = explode(',', $data['identity']);
					foreach ($ids as $i){
							
						if (!empty($data['detailId'.$i])){
							$arr = array(
								'purchaseId'		=>$id,
								'proId'				=>$data['proId'.$i],
									
								'qty'				=>$data['qty'.$i],
								'qtyAfter'			=>$data['qty'.$i],
								'unitPrice'			=>$data['unitPrice'.$i],
								'discountAmount'	=>$data['discountAmount'.$i],
								'subTotal'			=>$data['subTotal'.$i],
								'requestInDate'		=>$data['requestInDate'.$i],
								'note'				=>$data['note'.$i],
							);
							$this->_name='st_purchasing_detail';
							$where =" id =".$data['detailId'.$i];
							$this->update($arr, $where);
						}else{

							$arr = array(
								'purchaseId'		=>$id,
								'proId'				=>$data['proId'.$i],
									
								'qty'				=>$data['qty'.$i],
								'qtyAfter'			=>$data['qty'.$i],
								'unitPrice'			=>$data['unitPrice'.$i],
								'discountAmount'	=>$data['discountAmount'.$i],
								'subTotal'			=>$data['subTotal'.$i],
								'requestInDate'		=>$data['requestInDate'.$i],
								'note'				=>$data['note'.$i],
							);
							$this->_name='st_purchasing_detail';	
							$this->insert($arr);
						}
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
			$this->_name='st_purchasing';
			$sql=" SELECT po.*,
						(SELECT inv.purId FROM st_invoice AS inv WHERE inv.purId=po.id AND inv.status=1 AND inv.ivType=2 ORDER BY inv.id DESC LIMIT 1) AS inDepositInvoice
			FROM $this->_name
				
			AS po WHERE po.id=".$recordId;
			$sql.=$dbGb->getAccessPermission("po.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	function getPODetailById($recordId){
		$db = $this->getAdapter();
		$sql=" 	SELECT 
					pod.*,p.proCode,
					p.proName,
					p.isService AS serviceOrProType,
					(SELECT pl.qty FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= po.projectId LIMIT 1) AS currentQty,
					p.measureLabel AS measureTitle
					";
			
		$sql.="		FROM 
					`st_purchasing_detail` as pod
					JOIN `st_purchasing` AS po ON po.id = pod.purchaseId
					LEFT JOIN `st_product` AS p  ON p.proId = pod.proId 
			";
		$sql.=" WHERE pod.purchaseId = $recordId";
		return $db->fetchAll($sql);
	}
	
	function getPODetailHtml($data){
		$recordId = $data['purchaseId'];
		$recordInfo = $this->getDataRow($recordId);
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$string='';
    	$no = $data['keyindex'];
    	$identity='';
		if(!empty($data['currentInvoiceId'])){
			$dbDpInv = new Invpayment_Model_DbTable_DbDepositInvoice();
			$arrFilter = array(
						'id'=>$data['currentInvoiceId'],
					);
			$rs = $dbDpInv->getInvoiceDetailById($arrFilter);
			if(!empty($rs)){
				foreach ($rs as $key => $row){
					if (empty($identity)){
						$identity=$no;
					}else{$identity=$identity.",".$no;
					}
					$string.='
					<tr id="row'.$no.'" class="rowData" style="background: #fff; border: solid 1px #bac;">
						<td align="center" >'.($key+1).'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="detailId'.$no.'" name="detailId'.$no.'" value="'.$row['id'].'" type="text"  ></td>
						<td class="productName" >'.$row['proCode'].' - '.$row['proName'].'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="proId'.$no.'" name="proId'.$no.'" value="'.$row['proId'].'" type="text"  ><input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="type'.$no.'" name="type'.$no.'" value="'.$row['serviceOrProType'].'" type="text"  ></td>
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="qty'.$no.'" name="qty'.$no.'" placeholder="'.$tr->translate("QTY").'" value="'.$row['qtyPo'].'" type="text"  ></td>	
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="unitPrice'.$no.'" name="unitPrice'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['unitPrice'].'" type="text"  ></td>	
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="discountAmount'.$no.'" name="discountAmount'.$no.'" placeholder="'.$tr->translate("DISCOUNT")." ".$tr->translate("CURRENCY_SIGN").'" value="'.$row['discountAmount'].'" type="text"  ></td>
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="total'.$no.'" name="total'.$no.'" placeholder="'.$tr->translate("TOTAL").'" value="'.$row['total'].'" type="text"  ></td>	
					</tr>
					';$no++;
				}
			}else{
				$no++;
			}
		}else{
			$rs = $this->getPODetailById($recordId);
			if(!empty($rs)){
				foreach ($rs as $key => $row){
					if (empty($identity)){
						$identity=$no;
					}else{$identity=$identity.",".$no;
					}
					$string.='
					<tr id="row'.$no.'" class="rowData" style="background: #fff; border: solid 1px #bac;">
						<td align="center" >'.($key+1).'</td>
						<td class="productName" >'.$row['proCode'].' - '.$row['proName'].'<input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="proId'.$no.'" name="proId'.$no.'" value="'.$row['proId'].'" type="text"  ><input type="hidden" dojoType="dijit.form.TextBox" class="fullside" id="type'.$no.'" name="type'.$no.'" value="'.$row['serviceOrProType'].'" type="text"  ></td>
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="qty'.$no.'" name="qty'.$no.'" placeholder="'.$tr->translate("QTY").'" value="'.$row['qty'].'" type="text"  ></td>	
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="unitPrice'.$no.'" name="unitPrice'.$no.'" placeholder="'.$tr->translate("UNIT_PRICE").'" value="'.$row['unitPrice'].'" type="text"  ></td>	
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="discountAmount'.$no.'" name="discountAmount'.$no.'" placeholder="'.$tr->translate("DISCOUNT")." ".$tr->translate("CURRENCY_SIGN").'" value="'.$row['discountAmount'].'" type="text"  ></td>
						<td><input readOnly dojoType="dijit.form.NumberTextBox" class="fullside"  id="total'.$no.'" name="total'.$no.'" placeholder="'.$tr->translate("TOTAL").'" value="'.$row['subTotal'].'" type="text"  ></td>	
					</tr>
					';$no++;
				}
			}else{
				$no++;
			}
		}
		
		
		$array = array(
			'recordInfo'=>$recordInfo,
			'stringrow'=>$string,
			'keyindex'=>$no,
			'identity'=>$identity
			);
    	return $array;
	}
   
}