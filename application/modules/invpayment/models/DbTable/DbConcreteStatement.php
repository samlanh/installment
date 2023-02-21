<?php

class Invpayment_Model_DbTable_DbConcreteStatement extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_statement';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }


	function getAllStatement($recordId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();

		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$sql="
			
				
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
    	
    	// if(!empty($search['adv_search'])){
    	// 	$s_where = array();
    	// 	$s_search = (trim($search['adv_search']));
    	// 	$s_where[] = " inv.invoiceNo LIKE '%{$s_search}%'";
    	// 	$s_where[] = " inv.supplierInvoiceNo LIKE '%{$s_search}%'";
    	// 	$s_where[] = " po.purchaseNo LIKE '%{$s_search}%'";
    	// 	$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    	// 	$s_where[] = " po.purpose LIKE '%{$s_search}%'";
			
    	// 	$s_where[] = " inv.totalInternal LIKE '%{$s_search}%'";
    	// 	$s_where[] = " inv.vatInternal LIKE '%{$s_search}%'";
			
    	// 	$s_where[] = " inv.totalAmount LIKE '%{$s_search}%'";
    	// 	$s_where[] = " inv.vatExternal LIKE '%{$s_search}%'";
    	// 	$s_where[] = " inv.otherFeeExternal LIKE '%{$s_search}%'";
			
    	// 	$s_where[] = " inv.totalExternal LIKE '%{$s_search}%'";
    	// 	$s_where[] = " inv.totalAmountExternal LIKE '%{$s_search}%'";
			
    	// 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	// }
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
    	$row =  $db->fetchAll($sql.$where.$order);
		
    }
  
    function getConcreteStatement($recordId){
    	$db =$this->getAdapter();
    	$sql="SELECT 
				st.dnNumber,
				sd.qtyReceive,
				sd.price,
				sd.subTotal,
				DATE_FORMAT(st.receiveDate,'%d-%m-%Y') AS receiveDate,
				(SELECT k.workTitle FROM `st_work_type` k WHERE k.id=sd.workType LIMIT 1) WorkType,
				(SELECT p.proName FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proName,
				(SELECT p.proCode FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proCode,
				(SELECT p.measureLabel FROM st_product p WHERE p.proId=sd.proId LIMIT 1) measureLabel,
				sd.note AS note,
				sd.strength
		FROM 
			`st_receive_stock_detail` AS 
				sd  JOIN `st_receive_stock` AS st 
				ON sd.receiveId = st.id WHERE
				st.transactionType = 2 AND sd.receiveId IN(".$recordId.")";
    	echo $sql;
    	return $db->fetchAll($sql);
    }
    function addConcreteStatment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    	
	    	$arr = array(
	    			'statementType'=>3,
	    			'projectId'=>$data['branch_id'],
	    			'supplierId'=>$data['supplierId'],
	    			'stmentNo'=>$data['invoiceNo'],
	    			'stmentDate'=>date("Y-m-d"),
	    			'supplierStmentNo'=>$data['supplierstMentNo'],
	//     			'purIdList'=>$data[''],
	    			'dnIdList'=>$data['dnIdentity'],
	    			'fromDate'=>$data['startDate'],
	    			'toDate'=>$data['endDate'],
	    			'note'=>$data['note'],
	    			'userId'=>$this->getUserId(),
	    			'totalExternal'=>$data['totalAmountExternal'],
	    		);
	    	$stmentId  = $this->insert($arr);
	    	$ids = explode(',', $data['identity']);
	    	foreach ($ids as $i){
	    		$arr = array(
	    				'stamentId'=>$stmentId,
	    				'dnId'=>$data['dnId'.$i],
	    				'proId'=>$data['branch_id'],
	    				'qtyPo'=>$data['qty'.$i],
	    				'subTotal'=>$data['subTotal'.$i],
	    			);
	    		$this->_name='st_statement_detail';
	    		$this->insert($arr);
	    		
	    		$this->_name='st_receive_stock';
	    		$arr = array(
	    				'isissueStatement'=>1
	    				);
	    		$where = 'id='.$data['dnId'.$i];
	    		$this->update($arr, $where);
	    	}
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/invpayment/index/add");
    	}
    }

}