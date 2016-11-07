<?php

class Accounting_Model_DbTable_DbProgramCharge extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_servicefee_detail';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	
//     function getAllTuitionFee($search){
//     	$db = $this->getAdapter();
//     	$sql = "SELECT p.service_id as id,p.`title` AS service_name,
//     		p.status,t.title as cate_name FROM `rms_program_name` AS p,
//     		`rms_program_type` AS t
//     	WHERE t.id=p.ser_cate_id ";
//     	$order=" ORDER BY p.title";
//     	$where = '';
//     	if(empty($search)){
//     		 $sql.$order;
//     		return $db->fetchAll($sql.$order);
//     	}
//     	if(!empty($search['txtsearch'])){
//     		$where.=" AND title LIKE '%".$search['txtsearch']."%'";
//     	}
//     	if($search['type']>-1){
//     		$where.= " AND type = ".$search['type'];
//     	}
//     	if($search['status']>-1){
//     		$where.= " AND status = '".$search['status']."'";
//     	}
//     	return $db->fetchAll($sql.$where.$order);

//     }
    public function addServiceCharge($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$ids =explode(',', $_data['identity']);//main
    		$id_term =explode(',', $_data['iden_term']);//sub
    		foreach ($ids as $i){
	    				$levels = explode(',', $_data['level'.$i]);
	    		foreach ($levels as $level){
	    					foreach ($id_term as $j){
	    				$rs=$this->getProgramPriceExist($_data['service_id'.$i],$level,$j,$_data['type_hour']);
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
		    						'level'=>$level,
		    						'type_hour'=>$_data['type_hour'],
		    						'total_hour'=>$_data['total_hour'],
		    						'price'=>$_data['fee'.$i.'_'.$j],
		    						'remark'=>$_data['remark'.$i]
		    				       );
		    				$this->insert($_arr);
		    				$code=$db->lastInsertId();
		    				
		    				$data = array('service_code'=>"P".$code.$level.$_data['type_hour'].$j);
		    				$where='servicefee_id = '.$code;
		    				$this->update($data, $where);
 	    				}
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
//     public function updateServiceCharge($_data){
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	try{
//     		$ids =explode(',', $_data['identity']);//main
//     		$id_term =explode(',', $_data['iden_term']);//sub
//     		foreach ($ids as $i){
//     			foreach ($id_term as $j){
//     				$rs=$this->setServiceChargeExist($_data['service_id'.$i],$j);
//     				if(!empty($rs)){
//     					$_arr= array(
//     							'price'=>$_data['fee'.$i.'_'.$j],
//     							'remark'=>$_data['remark'.$i]
//     					);
//     					$where = 'servicefee_id='.$rs['servicefee_id'];
//     					$this->update($_arr, $where);
//     				}else{
//     					$_db = new Application_Model_DbTable_DbGlobal();
//     					$rs_serfee = $_db->getServiceFeeByServiceWtPayType($_data['id'],$j);
//     					if(!empty($rs_serfee)){
//     						$_arr= array(
//     								'service_id'=>$_data['service_id'.$i],
//     								'pay_type'=>$j,
//     								'price'=>$_data['fee'.$i.'_'.$j],
//     								'remark'=>$_data['remark'.$i]
//     						);
//     						$where = 'servicefee_id='.$rs_serfee['servicefee_id'];
//     						$this->update($_arr, $where);
//     					}
//     				}
    				
//     			}
//     		}
//     		$db->commit();
//     		return true;
//     	}catch (Exception $e){
//     		$db->rollBack();
//     		echo $e->getMessage();exit();
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		return false;
//     	}
//     }
//     function getServiceFeebyId($service_id){
//     	$db = $this->getAdapter();
//     	$sql = "SELECT * FROM `rms_servicefee_detail` WHERE service_id=".$service_id." ORDER BY service_id";
//     	return $db->fetchAll($sql);
    	 
//     }
    public function getProgramPriceExist($service_id,$level,$pay_type,$type_hour){//
    	$db = $this->getAdapter();
    	$sql = "SELECT servicefee_id,price FROM `rms_servicefee_detail` WHERE service_id=$service_id 
    	AND level = $level AND pay_type=$pay_type AND type_hour = $type_hour LIMIT 1  ";
    	return $db->fetchRow($sql);
    }
//     public function getServiceChargeById($service_id){
//     	$db = $this->getAdapter();
//     	$sql = "SELECT * FROM rms_program_name WHERE service_id=$service_id LIMIT 1";
    	
//     	return $db->fetchAll($sql);
    
//     }
}



