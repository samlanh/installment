<?php

class Po_Model_DbTable_DbConcret extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_receive_stock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllReceiveStockConcret($search){
    	$sql="SELECT r.id,
		(SELECT project_name FROM `ln_project` WHERE br_id=r.projectId LIMIT 1) AS projectName,
		(SELECT s.supplierName FROM st_supplier s WHERE s.id=r.supplierId LIMIT 1) AS supplierName,
		r.dnNumber,
		r.receiveDate,
		(SELECT p.proName FROM `st_product` p WHERE p.proId=rd.proId LIMIT 1) proName,
		rd.qtyReceive,
		rd.price,
		rd.subTotal,
		rd.note,
		(SELECT first_name FROM rms_users WHERE id=r.userId LIMIT 1 ) AS user_name,
		(SELECT name_en FROM ln_view WHERE TYPE=3 AND key_code = r.status LIMIT 1) AS STATUS
		
		FROM `st_receive_stock` AS r 
		JOIN st_receive_stock_detail AS rd  ON r.id=rd.receiveId 
		JOIN `st_purchasing` AS p ON p.id=r.poId WHERE  p.purchasetype = 3 ";
    	
    	
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
    		$s_where[] = " (SELECT p.proId FROM `st_product` AS p WHERE p.proId=rd.proId AND p.proName LIKE '%{$s_search}%')";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND r.status = ".$search['status'];
    	}
    	if($search['branch_id']>0){
    		$where.= " AND r.projectId = ".$search['branch_id'];
    	}
    	if($search['supplierId']>0){
    		$where.= " AND r.supplierId = ".$search['supplierId'];
    	}
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('r.projectId');
    	
    	$order=' ORDER BY rd.id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$where_date.$order);
    }

    function addReceiveStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		
    		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
    		$data['dateRequest']=$data['date'];
    		$purchaseNo = $dbGBstock->generatePurchaseNo($data);
    		$purchaseNo = $purchaseNo."CC";
    		
    		$arr = array(
    				'projectId'			=>$data['branch_id'],
    				'purchaseType'		=>3,
    				'purchaseNo'		=>$purchaseNo,
    				'supplierId'		=>$data['supplierId'],
    				'date'				=>$data['date'],
    				'note'				=>$data['note'],
    				
    				'processingStatus'	=>1,//completed po
    				'status'			=>1,
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$this->getUserId(),
    		);
    		$this->_name='st_purchasing';
    		$poId = $this->insert($arr);
    		
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				
    				$arr = array(
    					'purchaseId'		=> $poId,
    					'proId'				=> $data['proId'.$i],
    					'qty'				=> $data['qty'.$i],
    					'qtyAfter'			=> 0,
    					'unitPrice'			=> $data['unitPrice'.$i],
    					'discountAmount'	=> 0,
    					'subTotal'			=> $data['total'.$i],
    					'isClosed'			=> 1,//finish buy this item
    					'note'				=> $data['note'.$i],
    				);
    				$this->_name='st_purchasing_detail';
    				$this->insert($arr);
    				
    				$arr = array(
    					'projectId'=>$data['branch_id'],
    					'transactionType'=>2,
    					'dnType'=>1,
    					'supplierId'=>$data['supplierId'],
    					'receiveDate'=>$data['date'],
    					'dnNumber'=>$data['dnNO'.$i],
    					'poId'=>$poId,
    					'note'=>$data['note'],
    					'userId'=>$this->getUserId(),
    					'createDate'=>date('Y-m-d'),
    						
    					'verifiedBy'=>$this->getUserId(),
    					'verified'=>1,
    					'verifiedDate'=>date('Y-m-d'),
    				);
    				$this->_name='st_receive_stock';
    				$receivedId = $this->insert($arr);
    				
    				$dbb = new Budget_Model_DbTable_DbInitilizeBudget();
    				
    				$param = array(
    					'branch_id'=>$data['branch_id'],
    					'type'=>2,//concrete budget
    					'transactionId'=>$receivedId,
    				);
    				$budgetExpenseId = $dbb->addBudgetExpense($param);
    				
    				$arr = array(
    					'receiveId'=>$receivedId,
    					'proId'=>$data['proId'.$i],
						'workType'	=>$data['workType'.$i],
    					'qtyReceive'=>$data['qty'.$i],
    					'qtyAfterReceive'=>0,
    					'price'=>$data['unitPrice'.$i],
    					'totalDiscount'=>0,
    					'subTotal'=>$data['total'.$i],
    					'isClosed'=>1,
    					'note'=>$data['note'.$i],
    				);
    				
    				$this->_name='st_receive_stock_detail';
    				$id = $this->insert($arr);
    				
    				$dbs->addProductHistoryQty($data['branch_id'],$data['proId'.$i],8,$data['qty'.$i],$id);//movement'
    				
    				$param = array(
    					'budgetExpenseId'=>$budgetExpenseId,
    					'subtransactionId'=>$id,
    					'productId'=>$data['proId'.$i],
    					'price'=>$data['unitPrice'.$i],
    					'qty'=>$data['qty'.$i],
    					'totalDiscount'=>0
    				);
    				$dbb->addBudgetExpenseDetail($param);
    			}
    		}
    		
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/po/concret/add",2);
    	}
    }
    function updateConcreteReceive($data){
    
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		
    		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
    		$dnId = $data['id'];
    		$rsDn = $this->getDNById($dnId);
    		if(!empty($rsDn)){
	    		$arr = array(
	    				'projectId'			=> $data['branch_id'],
	    				'purchaseType'		=> 3,
	    				'supplierId'		=> $data['supplierId'],
	    				'note'				=> $data['note'],
	    				'processingStatus'	=> 1,
	    				'status'			=> $data['status'],
	    				'createDate'		=> $data['date'],
	    				'modifyDate'		=> date("Y-m-d H:i:s"),
	    				'userId'			=> $this->getUserId(),
	    		);
	    		$this->_name='st_purchasing';
	    		$poId = $rsDn['poId'];
	    		$where = " id=".$poId;
	    		$this->update($arr, $where);
	    		
	    		$ids = explode(',',$data['identity']);
	    		if(!empty($ids)){
	    			foreach($ids as $i){
	    		
	    				$arr = array(
	    						'purchaseId'		=> $poId,
	    						'proId'				=> $data['proId'.$i],
	    						'qty'				=> $data['qty'.$i],
	    						'qtyAfter'			=> 0,
	    						'unitPrice'			=> $data['unitPrice'.$i],
	    						'discountAmount'	=> 0,
	    						'subTotal'			=> $data['total'.$i],
	    						'isClosed'			=> 1,
	    						'note'				=> $data['note'.$i],
	    				);
	    				$this->_name='st_purchasing_detail';
	    				$where = " purchaseId=".$poId;
	    				$this->update($arr, $where);
	    				
	    		
	    				$arr = array(
	    						
	    						'projectId'=>$data['branch_id'],
	    						'transactionType'=>2,
	    						'dnType'=>1,
	    						'supplierId'=>$data['supplierId'],
	    						'receiveDate'=>$data['date'],
	    						'dnNumber'=>$data['dnNO'.$i],
	    						'poId'=>$poId,
	    						'note'=>$data['note'],
	    						'userId'=>$this->getUserId(),
	    						'createDate'=>date('Y-m-d'),
	    						
	    						'verifiedBy'=>$this->getUserId(),
	    						'verified'=>1,
	    						'verifiedDate'=>date('Y-m-d'),
	    				);
	    				$this->_name='st_receive_stock';
	    				
	    				$where = " id=".$dnId;
	    				$this->update($arr, $where);
	    				
	    				$dbb = new Budget_Model_DbTable_DbInitilizeBudget();
	    				$dbb->reverBudgetExpense($dnId);//delete old budget
	    				
	    				$param = array(
	    						'branch_id'=>$data['branch_id'],
	    						'type'=>2,//concrete budget
	    						'transactionId'=>$dnId,
	    				);
	    				$budgetExpenseId = $dbb->addBudgetExpense($param);

	    				$arr = array(
	    						'receiveId'=>$dnId,
	    						'proId'=>$data['proId'.$i],
								'workType'	=>$data['workType'.$i],
	    						'qtyReceive'=>$data['qty'.$i],
	    						'qtyAfterReceive'=>0,
	    						'price'=>$data['unitPrice'.$i],
	    						'totalDiscount'=>0,
	    						'subTotal'=>$data['total'.$i],
	    						'isClosed'=>1,
	    						'note'=>$data['note'.$i],
	    				);
	    				$this->_name='st_receive_stock_detail';
	    				
	    				$where = " receiveId=".$dnId;
	    				$this->update($arr, $where);
	    				
	    				
	    				if(!empty($rsDn)){
	    					$param = array(
    							'budgetExpenseId'=>$budgetExpenseId,
    							'subtransactionId'=>$rsDn['receiveId'],
    							'productId'=>$data['proId'.$i],
    							'price'=>$data['unitPrice'.$i],
    							'qty'=>$data['qty'.$i],
    							'totalDiscount'=>0,
	    					);
	    					$dbb->addBudgetExpenseDetail($param);
	    				}
	    				
	    				$sql="SELECT id FROM st_receive_stock_detail WHERE receiveId =".$dnId;
	    				$sql.=" LIMIT 1";
	    				$dnDetailId = $db->fetchOne($sql);
	    				if(!empty($dnDetailId)){
	    					$dbs->addProductHistoryQty($data['branch_id'],$data['proId'.$i],8,$data['qty'.$i],$dnDetailId,1);//movement'
	    				} 		
	    				
	    			}
	    		}
    		}
    		
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/po/concret/index",2);
    	}
    }
    
    function getDNById($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT r.id,
    			(SELECT rd.receiveId FROM st_receive_stock_detail rd WHERE r.id=rd.receiveId LIMIT 1) AS receiveId,
    			r.poId,
    			r.projectId,
				r.dnNumber,
				r.plateNo,
				r.driverName,
				r.note,
				r.receiveDate,
				r.supplierId,
				r.status,
				r.isIssueInvoice,
				(SELECT po.workType FROM st_purchasing po WHERE po.id = r.poId LIMIT 1) AS workType
			FROM `st_receive_stock` r WHERE r.id=".$recordId;
    	//$sql.=" AND (SELECT id FROM st_receive_stock_detail rd WHERE r.id = rd.receiveId AND `isclosed`=0) ";
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getDNDetailById($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT 
    				sd.id,
    				sd.proId,
					sd.workType,
    				sd.price,
    				sd.subTotal,
					(SELECT p.proName FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proName,
					(SELECT p.proCode FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proCode,
					(SELECT p.measureLabel FROM st_product p WHERE p.proId=sd.proId LIMIT 1) measureLabel,
					sd.qtyReceive,
					sd.isClosed,
					sd.note
				FROM `st_receive_stock_detail` AS sd
				WHERE sd.receiveId=".$recordId;
    	return $db->fetchAll($sql);
    }
   
}