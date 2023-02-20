<?php

class Invpayment_Model_DbTable_DbConcreteStatement extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_statement';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
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
	    		
	    		$this->_name='st_receive_stock_detail';
	    		$arr = array(
	    				'isClosed'=>1
	    				);
	    		$where = 'receiveId='.$data['dnId'.$i];
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