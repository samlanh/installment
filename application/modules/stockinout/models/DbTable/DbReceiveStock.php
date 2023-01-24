<?php

class Stockinout_Model_DbTable_DbReceiveStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_receive_stock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllReceiveStock($search){
    	$sql="SELECT r.id,
				(SELECT project_name FROM `ln_project` WHERE br_id=r.projectId LIMIT 1) AS projectName,
				(SELECT name_kh FROM `st_view` WHERE type=4 AND key_code=r.dnType LIMIT 1) dnType,
				r.dnNumber,
				(SELECT name_kh FROM `st_view` WHERE type=5 AND key_code=r.verified LIMIT 1) isIssueInvoice,
				r.plateNo,
				r.driverName,
				r.staffCounter,
				r.receiveDate,
				(SELECT s.supplierName FROM st_supplier s WHERE s.id=r.supplierId LIMIT 1) AS supplierName,
				(SELECT purchaseNo FROM `st_purchasing` as p WHERE p.id=r.poId LIMIT 1) AS purchaseNo,
				(SELECT requestNo FROM `st_request_po` AS s WHERE s.id=r.requestId LIMIT 1) AS requestNo,
				(SELECT first_name FROM rms_users WHERE id=r.userId LIMIT 1 ) AS user_name,
				(SELECT name_en FROM ln_view WHERE type=3 and key_code = r.status LIMIT 1) AS status
				
			FROM `st_receive_stock` r WHERE 1";
    	
    	
    	$from_date =(empty($search['start_date']))? '1': " r.receiveDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " r.receiveDate <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes((trim($search['adv_search'])));
    		$s_where[] = " r.dnNumber LIKE '%{$s_search}%'";
    		$s_where[] = " r.driverName LIKE '%{$s_search}%'";
    		$s_where[] = " r.plateNo LIKE '%{$s_search}%'";
    		$s_where[] = " r.staffCounter LIKE '%{$s_search}%'";
    		
    		$s_where[] = " (SELECT p.id FROM `st_purchasing` AS p WHERE p.id=r.poId AND purchaseNo LIKE '%{$s_search}%')";
    		$s_where[] = " (SELECT s.id FROM `st_request_po` AS s WHERE s.id=r.requestId AND requestNo LIKE '%{$s_search}%')";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND r.status = ".$search['status'];
    	}
    	if($search['verifyStatus']>-1){
    		$where.= " AND r.isIssueInvoice = ".$search['verifyStatus'];
    	}
    	if($search['branch_id']>0){
    		$where.= " AND r.projectId = ".$search['branch_id'];
    	}
    	if($search['supplierId']>0){
    		$where.= " AND r.supplierId = ".$search['supplierId'];
    	}
    	if(isset($search['transactionType'])){
    		$where.= " AND r.transactionType = ".$search['transactionType'];
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('r.projectId');
    	
    	$order=' ORDER BY r.id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$where_date.$order);
    }
   
    function addReceiveStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$param = array(
    				'fetchRow'=>1,
    				'isClosed'=>-1,
    				'purchaseId'=>$data['purId']
    				);
    		$rowData = $dbs->getProductPOInfo($param);
    		
    		$arr = array(
    				'projectId'=>$data['branch_id'],
    				'dnType'=>$data['documentType'],
    				'poId'=>$data['purId'],
    				'requestId'=>$rowData['requestId'],
    				'supplierId'=>$rowData['supplierId'],
    				'receiveDate'=>$data['dnDate'],
    				'dnNumber'=>$data['dnTitle'],
    				'staffCounter'=>$data['counter'],
    				'driverName'=>$data['driver'],
    				'plateNo'=>$data['truckNumber'],
    				'note'=>$data['note'],
    				'userId'=>$this->getUserId(),
    				'createDate'=>date('Y-m-d'),
    			);
    		
    		$part= PUBLIC_PATH.'/images/dndocument/';
    		
    		$photo_name = $_FILES['photo']['name'];
    		if (!empty($photo_name)){
    			$tem =explode(".", $photo_name);
    			$image_name = "photoDn_".date("Y").date("m").date("d").time().".".end($tem);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				move_uploaded_file($tmp, $part.$image_name);
    				$photo = $image_name;
    				$arr['photoDn']=$photo;
    			}
    		}
    		
    		$photo_name = $_FILES['fileDn']['name'];
    		if (!empty($photo_name)){
    			$tem =explode(".", $photo_name);
    			$image_name = "fileDn".date("Y").date("m").date("d").time().".".end($tem);
    			$tmp = $_FILES['fileDn']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				move_uploaded_file($tmp, $part.$image_name);
    				$photo = $image_name;
    				$arr['fileDn']=$photo;
    			}
    		}
    		$receivedId = $this->insert($arr);
    		
    		$dbb = new Budget_Model_DbTable_DbInitilizeBudget();
    		
    		$param = array(
    			'branch_id'=>$data['branch_id'],
    			'type'=>1,
    			'transactionId'=>$receivedId,
    		);
    		
    		$budgetExpenseId = $dbb->addBudgetExpense($param);
    		
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$arr = array(
    					'receiveId'=>$receivedId,
    					'proId'=>$data['productId'.$i],
    					'qtyReceive'=>$data['qtyReceive'.$i],
    					'qtyAfterReceive'=>$data['qtyAfter'.$i]-$data['qtyReceive'.$i],
    					'price'=>$data['price'.$i],
    					'totalDiscount'=>$data['discountAmount'.$i]/$data['qtyPO'.$i]*$data['qtyReceive'.$i],
    					'subTotal'=>$data['qtyReceive'.$i]*$data['price'.$i],
    					'isClosed'=>$data['receiveStatus'.$i],
    					'note'=>$data['note'.$i],
    				);
    				
    				$this->_name='st_receive_stock_detail';
    				$id = $this->insert($arr);
    				
    				$paramPro = array(
    						'fetchRow'=>1,
    						'isClosed'=>-1,
    						'purchaseId'=>$data['purId'],
    						'proId'=>$data['productId'.$i]
    				);
    				
    				$poProduct = $dbs->getProductPOInfo($paramPro);
    				
    				if(!empty($poProduct)){//update po product detail and po
    					
    					$currentAfter = $poProduct['qtyAfter'];
    					$Receiveqty = $data['qtyReceive'.$i];
    					$arr =array(
    						'qtyAfter'=>$currentAfter-$Receiveqty
    					);
    					if($currentAfter-$Receiveqty<=0 OR $data['receiveStatus'.$i]==1){
    						$arr['isClosed']=1;
    					}
    					
    					$where= "purchaseId = ".$poProduct['id']." AND proId=".$poProduct['proId'];
    					
    					$this->_name='st_purchasing_detail';
    					$this->update($arr, $where);
    					
    					$paramPro = array(
    						'fetchRow'=>1,
    						'isClosed'=>-1,
    						'orderisClosedASC'=>1,
    						'purchaseId'=>$data['purId'],
    					);
    					$dbs->updatePoStatusisClose($paramPro);//update PO Status
    				}
    				
    				$param = array(
    					'branch_id'	=> $data['branch_id'],
    					'productId'	=> $data['productId'.$i],
    					'EntyQty'	=> $data['qtyReceive'.$i],
    					'EntyPrice'	=> $data['price'.$i]
    				);
    				
    				$dbs->updateStockbyBranchAndProductId($param);//Update Stock qty and new costing
    				
    				$dbs->addProductHistoryQty($data['branch_id'],$data['productId'.$i],2,$data['qtyReceive'.$i],$id);//movement'
    				
    				$param = array(
    					'budgetExpenseId'=>$budgetExpenseId,
    					'subtransactionId'=>$id,
    					'productId'=>$data['productId'.$i],
    					'price'=>$data['price'.$i],
    					'qty'=>$data['qtyReceive'.$i],
    					'totalDiscount'=>$data['discountAmount'.$i]/$data['qtyPO'.$i]*$data['qtyReceive'.$i]
    				);
    				$dbb->addBudgetExpenseDetail($param);
    			}
    		}
    		
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/stockinout/index/add",2);
    	}
    }
    function updateDataReceive($data){
    
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$param = array(
    			'fetchRow'=>1,
    			'isClosed'=>-1,
    			'purchaseId'=>$data['purId']
    		);
    		$rowData = $dbs->getProductPOInfo($param);
    		
    		$arr = array(
    				'projectId'=>$data['branch_id'],
    				'dnType'=>$data['documentType'],
    				'poId'=>$data['purId'],
    				'requestId'=>$rowData['requestId'],
    				'supplierId'=>$rowData['supplierId'],
    				'receiveDate'=>$data['dnDate'],
    				'dnNumber'=>$data['dnTitle'],
    				'staffCounter'=>$data['counter'],
    				'driverName'=>$data['driver'],
    				'plateNo'=>$data['truckNumber'],
    				'note'=>$data['note'],
    				'userId'=>$this->getUserId(),
    				'createDate'=>date('Y-m-d'),
    			);
    		
    		$part= PUBLIC_PATH.'/images/dndocument/';
    		
    		$photo_name = $_FILES['photo']['name'];
    		if (!empty($photo_name)){
    			//unset old file here
    			$tem =explode(".", $photo_name);
    			$image_name = "photoDn_".date("Y").date("m").date("d").time().".".end($tem);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				move_uploaded_file($tmp, $part.$image_name);
    				$photo = $image_name;
    				$arr['photoDn']=$photo;
    			}
    		}
    		
    		$photo_name = $_FILES['fileDn']['name'];
    		if (!empty($photo_name)){
    			//unset old file here
    			$tem =explode(".", $photo_name);
    			$image_name = "fileDn".date("Y").date("m").date("d").time().".".end($tem);
    			$tmp = $_FILES['fileDn']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				move_uploaded_file($tmp, $part.$image_name);
    				$photo = $image_name;
    				$arr['fileDn']=$photo;
    			}
    		}
    		$receivedId = $data['id'];
    		$where="id=".$receivedId;
    		
    		$this->update($arr,$where);
    		
    		$this->reverseReceivedTransaction($receivedId,$data['purId'],$data['branch_id']);//reverse po and po detail'
    		
    		$dbb = new Budget_Model_DbTable_DbInitilizeBudget();
    		
    		$dbb->reverBudgetExpense($receivedId);//delete old budget plan
    		
    		if($data['status']==0){
    			$db->commit();
    			return true;
    		}
    		
    		$param = array(
    			'branch_id'=>$data['branch_id'],
    			'type'=>1,
    			'transactionId'=>$receivedId,
    		);
    		
    		$budgetExpenseId = $dbb->addBudgetExpense($param);
    		
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$arr = array(
    					'receiveId'=>$receivedId,
    					'proId'=>$data['productId'.$i],
    					'qtyReceive'=>$data['qtyReceive'.$i],
    					'qtyAfterReceive'=>$data['qtyAfter'.$i]-$data['qtyReceive'.$i],
    					'price'=>$data['price'.$i],
    					'totalDiscount'=>$data['discountAmount'.$i]/$data['qtyPO'.$i]*$data['qtyReceive'.$i],
    					'subTotal'=>$data['qtyReceive'.$i]*$data['price'.$i],
    					'isClosed'=>$data['receiveStatus'.$i],
    					'note'=>$data['note'.$i],
    				);
    				
    				$this->_name='st_receive_stock_detail';
    				$id = $this->insert($arr);
    				
    				$paramPro = array(
    						'fetchRow'=>1,
    						'isClosed'=>-1,
    						'purchaseId'=>$data['purId'],
    						'proId'=>$data['productId'.$i]
    				);
    				
    				$poProduct = $dbs->getProductPOInfo($paramPro);
    				
    				if(!empty($poProduct)){//update po product detail and po
    					
    					$currentAfter = $poProduct['qtyAfter'];
    					$Receiveqty = $data['qtyReceive'.$i];
    					$arr =array(
    						'qtyAfter'=>$currentAfter-$Receiveqty
    					);
    					if($currentAfter-$Receiveqty<=0 OR $data['receiveStatus'.$i]==1){
    						$arr['isClosed']=1;
    					}
    					
    					$where= "purchaseId = ".$poProduct['id']." AND proId=".$poProduct['proId'];
    					
    					$this->_name='st_purchasing_detail';
    					$this->update($arr, $where);
    					
    					$paramPro = array(
    						'fetchRow'=>1,
    						'isClosed'=>-1,
    						'orderisClosedASC'=>1,
    						'purchaseId'=>$data['purId'],
    					);
    					$dbs->updatePoStatusisClose($paramPro);//update PO Status
    				}
    				
    				$param = array(
    					'branch_id'	=> $data['branch_id'],
    					'productId'	=> $data['productId'.$i],
    					'EntyQty'	=> $data['qtyReceive'.$i],
    					'EntyPrice'	=> $data['price'.$i]
    				);
    				
    				$dbs->updateStockbyBranchAndProductId($param);//Update Stock qty and new costing
    				
    				$dbs->addProductHistoryQty($data['branch_id'],$data['productId'.$i],2,$data['qtyReceive'.$i],$id);//movement'
    				
    				$param = array(
    					'budgetExpenseId'=>$budgetExpenseId,
    					'subtransactionId'=>$id,
    					'productId'=>$data['productId'.$i],
    					'price'=>$data['price'.$i],
    					'qty'=>$data['qtyReceive'.$i],
    					'totalDiscount'=>$data['discountAmount'.$i]/$data['qtyPO'.$i]*$data['qtyReceive'.$i]
    				);
    				$dbb->addBudgetExpenseDetail($param);
    			}
    		}
    		
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/stockinout/index/add",2);
    	}
    }
    function reverseReceivedTransaction($dnId,$poId,$branchId){
    	$dbs = new Application_Model_DbTable_DbGlobalStock();
    	$results = $this->getDNDetailById($dnId);
    	if(!empty($results)){foreach ($results as $result){
	    		$paramPro = array(
    				'fetchRow'=>1,
    				'isClosed'=>-1,
    				'purchaseId'=>$poId,
    				'proId'=>$result['proId']
	    		);
	    		
	    		$poProduct = $dbs->getProductPOInfo($paramPro);
	    		if(!empty($poProduct)){//update po product detail and po
	    				
	    			$currentAfter = $poProduct['qtyAfter'];
	    			$Receiveqty = $result['qtyReceive'];
	    			
	    			$arr =array(
    					'qtyAfter'=>$currentAfter+$Receiveqty,
    					'isClosed'=>0
	    			);
	    				
	    			$where= "purchaseId = ".$poId." AND proId=".$poProduct['proId'];
	    			$this->_name='st_purchasing_detail';
	    			$this->update($arr, $where);
	    			
	    			$param = array(
	    					'EntyQty'=> -$result['qtyReceive'],
	    					'branch_id'=> $branchId,
	    					'productId'=> $result['proId'],
	    			);
	    			$dbs->updateProductLocation($param);
	    			
	    		}
	    		
	    		$dbs->DeleteProductHistoryQty($result['id']);
	    	}
	    	
	    	$where= "receiveId = ".$dnId;
	    	$this->_name='st_receive_stock_detail';
	    	$this->delete($where);
	    	
	    	
	    	$where="id=".$poId;
	    	$this->_name="st_purchasing";
		    	$arr =array(
		    			'processingStatus'=>0
		    	);
	    	$this->update($arr, $where);
    	}
    				
    }
    function verifyDN($data){
    	$arr = array(
    			'verified'=>1,
    			'verifiedBy'=>$this->getUserId(),
    			'verifiedDate'=>date('Y-m-d')
    			);
    	$where= "id=".$data['purchaseId'];
    	$this->update($arr, $where);
    }
   
    function getAllProductByPO($data){
    	$db = $this->getAdapter();
    	
    	$db = new Application_Model_DbTable_DbGlobalStock();
    	$rs = $db->getProductPOInfo($data);
    
    	$string='';
    	$no = $data['keyindex'];
    	$identity='';
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	if(!empty($rs)){
    	foreach ($rs as $key => $row){
	    	if (empty($identity)){
	    		$identity=$no;
	    	}else{
	    		$identity=$identity.",".$no;
	    	}
	    
	    		$classRowBg = "odd";
	    	if(($key%2)==0){
	    			$classRowBg = "regurlar";
	    	}
	    	
	    	$qtyReceived=0;
	    	$row['qtyReceive']=$row['qtyAfter'];
	    	$note='';
	    	if(!empty($data['dnId'])){
	    		$param = array(
	    			'proId'=>$row['proId'],
	    			'dnId'=>$data['dnId']
	    			);
	    		$result = $this->getQtyReceivedByProId($param);
	    		$qtyReceived = $result['qtyReceive'];
	    		$note = $result['note'];
	    		$row['qtyReceive'] = $qtyReceived;
	    		$row['qtyAfter'] = $row['qtyAfter']+$qtyReceived;
	    		
	    		
	    	}
	    	
	    	$Message="rangeMessage:'".$tr->translate('CAN_NOT_RECEIVE_OVER')."'";
	    		$string.='
		    		<tr id="row'.$no.'" class="rowData '.$classRowBg.'" >
			    		<td align="center" style="padding: 0 10px;"><input OnChange="CheckAllTotal('.$no.')" style="vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_'.$no.'" value="'.$no.'"  name="selector[]"/></td>
			    		<td class="textCenter">'.($key+1).'</td>
			    		<td class="textCenter">'.$row['proName'].'('.$row['measureLabel'].')</td>
		    			<td><input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyPO'.$no.'"  value="'.$row['qty'].'" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="productId'.$no.'" id="productId'.$no.'" value="'.$row['proId'].'" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="price'.$no.'" id="price'.$no.'" value="'.$row['unitPrice'].'" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="discountAmount'.$no.'" id="discountAmount'.$no.'" value="'.$row['discountAmount'].'" style="text-align: center;" >
		    			</td>
		    			<td><input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyAfter'.$no.'" id="qtyAfter'.$no.'" value="'.$row['qtyAfter'].'" style="text-align: center;" ></td>
		    			<td><input type="text" class="fullside" data-dojo-props="constraints:{min:0,max:'.$row['qtyAfter'].'},'.$Message.'" dojoType="dijit.form.NumberTextBox" required="required" name="qtyReceive'.$no.'" id="qtyReceive'.$no.'" value="'.$row['qtyReceive'].'" style="text-align: center;" ></td>
		    			<td><select dojoType="dijit.form.FilteringSelect" class="fullside" name="receiveStatus'.$no.'" id="receiveStatus'.$no.'" >
		    					<option value="0">ទទួលមិនគ្រប់</option>
		    					<option value="1">ទទួលគ្រប់</option>
		    				</select>
		    			</td>
		    			<td><input type="text" class="fullside" dojoType="dijit.form.TextBox" name="note'.$no.'" id="note'.$no.'" value="'.$note.'" /></td>
		    			</tr>
	    			';
	    		$no++;
	    	}
	    	}else{
	    		$no++;
	    	}
	    	$data['fetchRow']=1;
	    	$data['isClosed']=-1;
	    	$rowData = $db->getProductPOInfo($data);
	    	
	    	$strPOInfo = '
	    		<div class="form-group" style="padding: 4px !important;">
	             	<span class="note_score">&nbsp;&nbsp; <i class="fa fa-file-text-o" aria-hidden="true"></i>&nbsp;&nbsp;
	                   	'.$tr->translate("PURCHASING_INFO").'</span>
                   		 <ul>
                   		 	<li><span class="lbl-tt">'.$tr->translate("PO_DATE").'</span>: <span class="red">'.$rowData['createDate'].'</span></li>
                   		 	<li><span class="lbl-tt">'.$tr->translate("PO_NO").'</span>: <span class="red">'.$rowData['purchaseNo'].'</span></li>
                   		 	<li><span class="lbl-tt">'.$tr->translate("SUPPLIER_NAME").'</span>: <span class="red">'.$rowData['supplierName'].'</span></li>
                   			<li><span class="lbl-tt">'.$tr->translate("REQUEST_NO").'</span>: <span class="red">'.$rowData['requestNo'].'</span></li>
                   		</ul>
             	</div>';
    	
    	$array = array('stringrow'=>$string,'POInfoDataBlog'=>$strPOInfo,'keyindex'=>$no,'identity'=>$identity);
    	return $array;
    }
    function getQtyReceivedByProId($data){
    	$db = $this->getAdapter();
    	$sql=" SELECT rd.note,rd.qtyReceive 
    					FROM `st_receive_stock` r,
    						 `st_receive_stock_detail` rd
					WHERE r.id=rd.receiveId ";
    	if(!empty($data['proId'])){
    		$sql.=" AND proId=".$data['proId'];
    	}
    	if(!empty($data['dnId'])){
    		$sql.=" AND r.id=".$data['dnId'];
    	}
    	return $db->fetchRow($sql);
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId;
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getDNById($data=array()){
    	$db = $this->getAdapter();
    	$sql=" SELECT r.id,r.projectId,
				(SELECT project_name FROM `ln_project` WHERE br_id=r.projectId LIMIT 1) AS projectName,
				(SELECT name_kh FROM `st_view` WHERE type=4 AND key_code=r.dnType LIMIT 1) dnType,
				r.dnNumber,
				r.plateNo,
				r.driverName,
				r.staffCounter,
				r.note,
				r.verified,
				r.poId AS purId,
				DATE_FORMAT(r.receiveDate,'%d-%m-%Y') receiveDate,
				(SELECT s.supplierName FROM st_supplier s WHERE s.id=r.supplierId LIMIT 1) AS supplierName,
				(SELECT purchaseNo FROM `st_purchasing` as p WHERE p.id=r.poId LIMIT 1) AS purchaseNo,
				(SELECT DATE_FORMAT(createDate,'%d-%m-%Y') FROM `st_purchasing` as p WHERE p.id=r.poId LIMIT 1) AS purchaseDate,
				(SELECT requestNo FROM `st_request_po` AS s WHERE s.id=r.requestId LIMIT 1) AS requestNo,
				(SELECT DATE_FORMAT(createDate,'%d-%m-%Y') FROM `st_request_po` AS s WHERE s.id=r.requestId LIMIT 1) requestDate,
				(SELECT first_name FROM rms_users WHERE id=r.userId LIMIT 1 ) AS user_name,
				(SELECT  u.signature_pic FROM rms_users AS u WHERE u.id=r.userId LIMIT 1 ) AS userSignature,
				(SELECT first_name FROM rms_users WHERE id=r.verifiedBy LIMIT 1 ) AS verifiedBy,
				(SELECT u.signature_pic FROM rms_users AS u WHERE u.id=r.verifiedBy LIMIT 1 ) AS verifiedSignature
				
			FROM `st_receive_stock` r WHERE 1 ";
    	if(!empty($data['dnId']))
    	{
    		$sql.=" AND r.id=".$data['dnId'];
    	}
    	if(!empty($data['transactionType']))
    	{
    		$sql.=" AND r.transactionType=".$data['transactionType'];
    	}
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('r.projectId');
    	return $db->fetchRow($sql);
    }
    function getDNDetailById($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT 
    				sd.id,
    				sd.proId,
					(SELECT p.proName FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proName,
					(SELECT p.proCode FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proCode,
					(SELECT p.measureLabel FROM st_product p WHERE p.proId=sd.proId LIMIT 1) measureLabel,
					sd.qtyReceive,
					sd.qtyAfterReceive,
					sd.isClosed,
					sd.note
				FROM `st_receive_stock_detail` AS sd
				WHERE sd.receiveId=".$recordId;
    	return $db->fetchAll($sql);
    }
   
}