<?php

class Invpayment_Model_DbTable_DbConcreteStatement extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_statement';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}


	function getAllStatement($search)
	{
		$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();

		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$sql = "SELECT st.id,
			(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id=st.projectId LIMIT 1) AS projectName,
			st.stmentNo, st.stmentDate, st.supplierStmentNo, st.totalExternal,

			(SELECT sp.supplierName FROM `st_supplier` AS sp  WHERE sp.id = st.supplierId LIMIT 1) AS supplierName,
			st.note, 
			(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = st.userId LIMIT 1 ) AS byUser, st.status ";

		$sql .= $dbGb->caseStatusShowImage("st.status");
		$sql .= "
			FROM `st_statement` AS st WHERE 1
			";

		$where = "";
		$from_date = (empty($search['start_date'])) ? '1' : " st.stmentDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " st.stmentDate <= '" . $search['end_date'] . " 23:59:59'";

		$where .= " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['adv_search'])) {

			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " st.stmentNo LIKE '%{$s_search}%'";
			$s_where[] = " st.supplierStmentNo LIKE '%{$s_search}%'";
			$s_where[] = " st.totalExternal LIKE '%{$s_search}%'";

			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if ($search['status'] > -1) {
			$where .= " AND st.status = " . $search['status'];
		}
		if (($search['branch_id']) > 0) {
			$where .= " AND st.projectId = " . $search['branch_id'];
		}
		if (!empty($search['supplierId'])) {
			$where .= " AND st.supplierId = " . $search['supplierId'];
		}
		$order = ' ORDER BY st.id DESC  ';
		$where .= $dbGb->getAccessPermission("st.projectId");

		return $db->fetchAll($sql . $where . $order);

	}

	function getConcreteStatement($recordId)
	{
		$db = $this->getAdapter();
		$sql = "SELECT *,
			( SELECT p.proName FROM `st_product` AS p WHERE p.proId=sd.proId LIMIT 1) AS proName,
			( SELECT p.proCode FROM `st_product` AS p WHERE p.proId=sd.proId LIMIT 1) AS proCode,
			( SELECT p.measureLabel FROM `st_product` AS p WHERE p.proId=sd.proId LIMIT 1) AS measureLabel,
			( SELECT r.dnNumber FROM `st_receive_stock` AS r WHERE r.id=sd.dnId LIMIT 1) AS dnNumber,
			( SELECT rd.strength FROM `st_receive_stock_detail` AS rd WHERE rd.receiveId=sd.dnId LIMIT 1) AS strength,
			( SELECT w.workTitle FROM  `st_work_type` AS w WHERE w.id IN (SELECT workType FROM  st_receive_stock_detail WHERE id = sd.dnId) ) AS workType
			 FROM `st_statement_detail` sd  JOIN `st_statement` AS st ON sd.stamentId= st.id
			 WHERE sd.stamentId = " . $recordId;

		return $db->fetchAll($sql);
	}

	function getStatementRow($recordId)
	{
		$db = $this->getAdapter();
		$sql = "SELECT *,
			(SELECT sp.supplierName FROM `st_supplier` AS sp  WHERE sp.id = st.supplierId LIMIT 1) AS supplierName
<<<<<<< HEAD
			 FROM `st_statement` AS st  WHERE st.id = ".$recordId;
    	return $db->fetchRow($sql);
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
	    			'dnIdList'=>$data['dnIdentity'],
	    			'fromDate'=>$data['startDate'],
	    			'toDate'=>$data['endDate'],
	    			'note'=>$data['note'],
	    			'userId'=>$this->getUserId(),
	    			'totalExternal'=>$data['totalAmountExternal'],
	    	);
	    	$stmentId  = $this->insert($arr);
	    	
	    	
	    	$arr = array(
	    			'projectId'			=>$data['branch_id'],
	    			'ivType'			=>3,
	    			'invoiceNo'			=>$data['invoiceNo'],
	    			'dnId'				=>$data['dnIdentity'],
	    			'supplierId'		=>$data['supplierId'],
	    				
	    			'invoiceDate'				=>date('Y-m-d'),
	    			'supplierInvoiceNo'			=>$data['supplierstMentNo'],
	    			'receiveIvDate'				=>date('Y-m-d'),
	    			//'purId'						=>'',
	    			'note'						=>$data['note'],
	    			'totalInternal'				=>$data['totalAmountExternal'],
	    			'totalAmount'				=>$data['totalAmountExternal'],
	    			'totalExternal'				=>$data['totalAmountExternal'],
	    			'totalAmountExternal'		=>$data['totalAmountExternal'],
	    			'totalAmountExternalAfter'	=>$data['totalAmountExternal'],
	    			'status'			=>1,
	    			'createDate'		=>date("Y-m-d H:i:s"),
	    			'modifyDate'		=>date("Y-m-d H:i:s"),
	    			'userId'			=>$this->getUserId(),
	    	);
	    	$this->_name='st_invoice';
	    	$invoiceId = $this->insert($arr);
	    	
	    	$ids = explode(',', $data['identity']);
	    	foreach ($ids as $i){
	    		$arr = array(
	    				'stamentId'=>$stmentId,
	    				'dnId'=>$data['rsId'.$i],
	    				'proId'=>$data['proId'.$i],
	    				'qtyPo'=>$data['qty'.$i],
	    				'subTotal'=>$data['subTotal'.$i],
	    			);
	    		$this->_name='st_statement_detail';
	    		$this->insert($arr);
	    		
	    		$arr = array(
		    				'invId'				=>$invoiceId,
		    				'type'				=>1,
		    				'proId'				=>$data['proId'.$i],
		    				'qtyPo'				=>$data['qty'.$i],
		    				'unitPrice'			=>$data['unitPrice'.$i],
		    				'discountPercent'	=>0,
		    				'discountAmount'	=>0,
		    				'totalDiscount'		=>0,
		    				'total'				=>$data['subTotal'.$i],
		    				'totalQtyReceive'	=>$data['qty'.$i],
		    				'unitPriceReceive'		=>$data['unitPrice'.$i],
		    				'receiveDiscountPercent'=>0,
		    				'receiveDiscountAmount'	=>0,
		    				'totalReceiveDiscount'	=>0,
		    				'totalReceive'			=>$data['subTotal'.$i],
	    		);
	    		$this->_name='st_invoice_detail';
	    		$this->insert($arr);
	    		$this->_name='st_receive_stock';
	    		$arr = array(
	    				'isissueStatement'=>1
	    				);
	    		$where = 'id='.$data['rsId'.$i];
	    		$this->update($arr, $where);
	    		
	    	}
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/invpayment/index/add");
    	}
    }
=======
			 FROM `st_statement` AS st  WHERE st.id = " . $recordId;
		return $db->fetchRow($sql);
	}
	function addConcreteStatment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {

			$arr = array(
				'statementType' => 3,
				'projectId' => $data['branch_id'],
				'supplierId' => $data['supplierId'],
				'stmentNo' => $data['invoiceNo'],
				'stmentDate' => date("Y-m-d"),
				'supplierStmentNo' => $data['supplierstMentNo'],
				//     			'purIdList'=>$data[''],
				'dnIdList' => $data['dnIdentity'],
				'fromDate' => $data['startDate'],
				'toDate' => $data['endDate'],
				'note' => $data['note'],
				'userId' => $this->getUserId(),
				'totalExternal' => $data['totalAmountExternal'],
			);
			$stmentId = $this->insert($arr);


			$arr = array(
				'projectId' => $data['branch_id'],
				'ivType' => 3,
				'invoiceNo' => $data['invoiceNo'],
				'dnId' => $data['dnIdentity'],
				'supplierId' => $data['supplierId'],

				'invoiceDate' => date('Y-m-d'),
				'supplierInvoiceNo' => $data['supplierstMentNo'],
				'receiveIvDate' => date('Y-m-d'),
				//'purId'						=>'',
				'note' => $data['note'],
				'totalInternal' => $data['totalAmountExternal'],
				'totalAmount' => $data['totalAmountExternal'],
				'totalExternal' => $data['totalAmountExternal'],
				'totalAmountExternal' => $data['totalAmountExternal'],
				'totalAmountExternalAfter' => $data['totalAmountExternal'],
				'status' => 1,
				'createDate' => date("Y-m-d H:i:s"),
				'modifyDate' => date("Y-m-d H:i:s"),
				'userId' => $this->getUserId(),
			);
			$this->_name = 'st_invoice';
			$invoiceId = $this->insert($arr);

			$ids = explode(',', $data['identity']);
			foreach ($ids as $i) {
				$arr = array(
					'stamentId' => $stmentId,
					'dnId' => $data['dnId' . $i],
					'proId' => $data['proId' . $i],
					'qtyPo' => $data['qty' . $i],
					'subTotal' => $data['subTotal' . $i],
				);
				$this->_name = 'st_statement_detail';
				$this->insert($arr);


				$arr = array(
					'invId' => $invoiceId,
					'type' => 1,
					'proId' => $data['proId' . $i],
					'qtyPo' => $data['qty' . $i],
					'unitPrice' => $data['unitPrice' . $i],
					'discountPercent' => 0,
					'discountAmount' => 0,
					'totalDiscount' => 0,
					'total' => $data['subTotal' . $i],

					'totalQtyReceive' => $data['qty' . $i],
					'unitPriceReceive' => $data['unitPrice' . $i],
					'receiveDiscountPercent' => 0,
					'receiveDiscountAmount' => 0,
					'totalReceiveDiscount' => 0,
					'totalReceive' => $data['subTotal' . $i],

				);
				$this->_name = 'st_invoice_detail';
				$this->insert($arr);

				$this->_name = 'st_receive_stock';
				$arr = array(
					'isissueStatement' => 1
				);
				$where = 'id=' . $data['dnId' . $i];
				$this->update($arr, $where);
			}
			$db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/invpayment/index/add");
		}
	}

>>>>>>> remotes/origin/master
}