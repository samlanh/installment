<?php

class Accounting_Model_DbTable_DbServiceCharge extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_servicefee_detail';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	public function sqltuitionfee($search=''){
    	$sql = "SELECT p.service_id as id,p.`title` AS service_name,p.status,t.title as cate_name FROM `rms_program_name` AS p,`rms_program_type` AS t
					WHERE t.id=p.ser_cate_id ";
    	$order=" ORDER BY p.title";
    	$where = '';
    	if(empty($search)){
    		return $sql.$order;
    	}
    	if(!empty($search['txtsearch'])){
    		$where.=" AND title LIKE '%".$search['txtsearch']."%'";
    	}
    	if($search['type']>-1){
    		$where.= " AND type = ".$search['type'];
    	}
    	if($search['status']>-1){
    		$where.= " AND status = '".$search['status']."'";
    	}
    	echo $sql.$where.$order;exit();
    	return $sql.$where.$order;
    }
    function getAllTuitionFee($search,$type=null){
    	$db = $this->getAdapter();
    	$sql = 'SELECT p.service_id as id,p.`title` AS service_name,
    		p.status,t.title as cate_name FROM `rms_program_name` AS p,
    		`rms_program_type` AS t
    	WHERE t.id=p.ser_cate_id ';
    	if($type!=null){
    		$sql.=" AND t.type = $type ";
    	}else{ $sql.=" AND t.type = 1";}
    	
    	$order=' ORDER BY p.title';
    	$where = '';
    	if(empty($search)){
    		 $sql.$order;
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$where.=" AND title LIKE '%".$search['txtsearch']."%'";
    	}
    	if($search['type']>-1){
    		$where.= " AND type = ".$search['type'];
    	}
    	if($search['status']>-1){
    		$where.= " AND status = '".$search['status']."'";
    	}
    	//echo $sql.$where.$order;exit();
    	//return $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
//     	$sql_rs = $this->sqltuitionfee($search)." LIMIT ".$start.", ".$limit;
//     	if ($limit == 'All') {
//     		$sql_rs = $this->sqltuitionfee($search);
//     	}
//     	$sql_count = $this->sqltuitionfee();
//     	if(!empty($search)){
//     		$sql_count = $this->sqltuitionfee($search);
//     	}
//     	$_db = new Application_Model_DbTable_DbGlobal();
//     	return($_db ->getGlobalResultList($sql_rs,$sql_count));
    }
    public function addServiceCharge($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$ids =explode(',', $_data['identity']);//main
    		$id_term =explode(',', $_data['iden_term']);//sub
    		foreach ($ids as $i){
    			foreach ($id_term as $j){
    				$rs=$this->setServiceChargeExist($_data['service_id'.$i],$j);
    				if(!empty($rs)){
    					$_arr= array(
    							'price'=>$_data['fee'.$i.'_'.$j],
    							'remark'=>$_data['remark'.$i]
    					);
    					$where = 'servicefee_id='.$rs['servicefee_id'];
    					$this->update($_arr, $where);
    				}else{
	    				$_arr= array(
	    						'service_id'=>$_data['service_id'.$i],
	    						'pay_type'=>$j,
	    						'price'=>$_data['fee'.$i.'_'.$j],
	    						'remark'=>$_data['remark'.$i]
	    				);
	    				$this->insert($_arr);
	    				$code=$db->lastInsertId();
	    				
	    				$data = array('service_code'=>"S".$code.$j);
	    				$where='servicefee_id = '.$code;
	    				$this->update($data, $where);
    				}
    			}
    		}
    	    $db->commit();
    	    return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
    public function updateServiceCharge($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$ids =explode(',', $_data['identity']);//main
    		$id_term =explode(',', $_data['iden_term']);//sub
    		foreach ($ids as $i){
    			foreach ($id_term as $j){
    				$rs=$this->setServiceChargeExist($_data['service_id'.$i],$j);
    				if(!empty($rs)){
    					$_arr= array(
    							'price'=>$_data['fee'.$i.'_'.$j],
    							'remark'=>$_data['remark'.$i]
    					);
    					$where = 'servicefee_id='.$rs['servicefee_id'];
    					$this->update($_arr, $where);
    				}else{
    					$_db = new Application_Model_DbTable_DbGlobal();
    					$rs_serfee = $_db->getServiceFeeByServiceWtPayType($_data['id'],$j);
    					if(!empty($rs_serfee)){
    						$_arr= array(
    								'service_id'=>$_data['service_id'.$i],
    								'pay_type'=>$j,
    								'price'=>$_data['fee'.$i.'_'.$j],
    								'remark'=>$_data['remark'.$i]
    						);
    						$where = 'servicefee_id='.$rs_serfee['servicefee_id'];
    						$this->update($_arr, $where);
    					}
    				}
    				
    			}
    		}
    		$db->commit();
    		return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
    function getServiceFeebyId($service_id,$type=null){
    	$db = $this->getAdapter();
    	if($type!=null){
    		$sql = "SELECT * FROM `rms_servicefee_detail` WHERE service_id=".$service_id." 
    		GROUP BY service_id,price,type_hour,level
    		ORDER BY service_id";
    		 
    	}else{
    		$sql = "SELECT * FROM `rms_servicefee_detail` WHERE service_id=".$service_id." ORDER BY service_id";
    	}
    	return $db->fetchAll($sql);
    	 
    }
    public function setServiceChargeExist($service_id,$pay_type){
    	$db = $this->getAdapter();
    	$sql = "SELECT servicefee_id,price FROM `rms_servicefee_detail` WHERE service_id=$service_id AND pay_type=$pay_type ";
    	return $db->fetchRow($sql);
    }
    public function getServiceChargeById($service_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_program_name WHERE service_id=$service_id LIMIT 1";
    	
    /*	$sql = "SELECT ser_cate_id,status,
    	sd.service_id,pay_type,price,remark,s.create_date
    	FROM `rms_program_name` AS s,`rms_servicefee_detail` AS sd
    	WHERE sd.service_id=s.service_id AND s.service_id=$service_id";*/
    	return $db->fetchAll($sql);
    
    }
}



