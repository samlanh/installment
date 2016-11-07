<?php

class Group_Model_DbTable_DbCallteral extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_client_callecteral';
    function addcallteral($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$arr = array(
	    			'branch_id'=>$data['branch_id'],
					'collecteral_code'=>$data['cod_cal'],
	    			'co_id'=>$data['co_name'],
	    			'client_id'=>$data['client_name'],
	    			'join_with'=>$data['name'],
	    			'relative'=>$data['names'],
	    			'guarantor'=>$data['owner'],
	    			'guarantor_relative'=>$data['and_name'],
					'note'=>$data['note'],
					'date'=>$data['date_estate'],
					'status'=>$data['Stutas'],
	    		);
	    	$id_call = $this->insert($arr);
	    	$ids = explode(",",$data['record_row']);
	    	$this->_name='ln_client_callecteral_detail';
	    	foreach ($ids as $i){
	    		$code = Group_Model_DbTable_DbCallteral::getCallteralCode();
	    		$array = array(
	    				'client_coll_id'=> $id_call,//$data['client_coll'],
	    				'collecteral_code'=>$code,
	    				'collecteral_type'=>$data['collect_type'.$i],
	    				'owner_type'=>$data['owner_type'.$i],
	    				'owner_name'=>$data['owner_name'.$i],
	    				'number_collecteral'=>$data['number_collteral'.$i],
	    				'issue_date'=>$data['issue_date'.$i],
	    				'status'=>$data['Stutas'],
	    				'note'=>$data['note'.$i],
	    				);
	    		$this->insert($array);
	    	}
	    	$db->commit();
    	}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
	}
	function updatecallteral($format){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$arr = array(
					'branch_id'=>$format['branch_id'],
					'collecteral_code'=>$format['cod_cal'],
					'co_id'=>$format['co_name'],
					'client_id'=>$format['client_name'],
					'join_with'=>$format['name'],
					'relative'=>$format['names'],
					'guarantor'=>$format['owner'],
					'guarantor_relative'=>$format['and_name'],
					'note'=>$format['note'],
					'date'=>$format['date_estate'],
					'status'=>$format['Stutas'],
			);
			$this->_name='ln_client_callecteral';
			$where=" id = ".$format['id'];
			$this->update($arr, $where);
			
			$this->_name='ln_client_callecteral_detail';
			$where  = 'is_return =0 AND client_coll_id = '.$format['id'];
			$this->delete($where);

			$ids = explode(",",$format['record_row']);
			
			foreach ($ids as $i){
				$code = Group_Model_DbTable_DbCallteral::getCallteralCode();
				$array = array(
						'client_coll_id'=> $format['id'],
						'collecteral_code'=>$code,
						'collecteral_type'=>$format['collect_type'.$i],
						'owner_type'=>$format['owner_type'.$i],
						'owner_name'=>$format['owner_name'.$i],
						'number_collecteral'=>$format['number_collteral'.$i],
						'issue_date'=>$format['issue_date'.$i],
						'status'=>$format['Stutas'],
						'note'=>$format['note'.$i],
				);
				$this->insert($array);
			}
			$db->commit();
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
		
	}
	function getecallteralbyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id=$id ";
		return $db->fetchRow($sql);
	}
	function getCallecteralDetailById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_client_callecteral_detail` WHERE client_coll_id =$id AND is_changed=0 AND is_return =0 ";
		return $db->fetchAll($sql);
	}
	function geteAllcallteral($search=null){
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		
		$sql=" SELECT id ,branch_name ,client_code ,client_name,collecteral_code,co_id,join_with , relative , 
		date ,note ,status FROM `v_getallcallateral` ";
		if($search['status_search']>-1){
			$where.=" AND status=".$search['status_search'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND branch_id = ".$search['branch_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			
			$s_where[]="branch_name LIKE'%{$s_search}%'";
			$s_where[]="co_id LIKE'%{$s_search}%'";
			$s_where[]="collecteral_code LIKE'%{$s_search}%'";
			$s_where[]="client_code LIKE'%{$s_search}%'";
			$s_where[]="client_name LIKE'%{$s_search}%'";
			$s_where[]="join_with LIKE'%{$s_search}%'";
			$s_where[]="relative LIKE'%{$s_search}%'";
			$s_where[]="note LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY id DESC ";
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	public static function getCallteralCode(){
		$db = new Application_Model_DbTable_DbGlobal();
		$sql = "SELECT COUNT(id) AS amount FROM `ln_client_callecteral_detail`";
		$acc_no= $db->getGlobalDbRow($sql);
		$acc_no=$acc_no['amount'];
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "";
		for($i = $acc_no;$i<6;$i++){
			$pre.='0';
		}
		return "CL".$pre.$new_acc_no;
	}
}

