<?php

class Accounting_Model_DbTable_DbTuitionFee extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    function getAllTuitionFee($search=''){
    	$db=$this->getAdapter();
    	$sql = "SELECT fee_id as id,batch,degree,
    		(SELECT en_name FROM `rms_dept` WHERE dept_id=faculty_id)as faculty_name
    		,degree_type,status,
    			create_date,user_id 
    			FROM `rms_tuitionfee`
    			WHERE 1";
    	$order=" ORDER BY degree";
    	$where = '';
    	if(empty($search)){
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
    	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    function getFeebyOther($fee_id){
    	$db = $this->getAdapter();
    	$sql = "select * from rms_tuitionfee_detail where fee_id =".$fee_id." ORDER BY id";
    	return $db->fetchAll($sql);
    }
    ////////////////
    public function addTuitionFee($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if($_data['degree']!=2){
    			$_data['faculty']=0;
    			$degree_type=0;
    		}else{
    			$degree_type=1;
    		}
    		$_arr = array(
    				'degree'=>$_data['degree'],
    				'batch'=>$_data['batch'],
    				'faculty_id'=>$_data['faculty'],
    				'degree_type'=>$degree_type,
    				'status'=>1,
    				'create_date'=>$_data['create_date'],
    				'user_id'=>$this->getUserId());
    		$fee_id = $this->insert($_arr);
    		$this->_name='rms_tuitionfee_detail';
    		$ids = explode(',', $_data['identity']);
    		$id_term =explode(',', $_data['iden_term']);
    		foreach ($ids as $i){
    			foreach ($id_term as $j){
    				$_arr = array(
    						'fee_id'=>$fee_id,
    						'metion'=>$_data['metion'.$i],
    						'payment_type'=>$j,
    						'tuition_fee'=>$_data['fee'.$i.'_'.$j],
    						'remark'=>$_data['remark'.$i]
    				);
    				$this->insert($_arr);
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
    public function setServiceChargeExist($service_id,$pay_type){
    	$db = $this->getAdapter();
    	$sql = "SELECT servicefee_id,price FROM `rms_servicefee_detail` WHERE service_id=$service_id AND pay_type=$pay_type ";
    	return $db->fetchRow($sql);
    	//batch ,metion OR faculty,payment_term,(degree_type)
    }
}