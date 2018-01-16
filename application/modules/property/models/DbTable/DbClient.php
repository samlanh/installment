<?php

class Property_Model_DbTable_DbClient extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_client_property';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	public function addClient($_data){
		try{
			if(!empty($_data['id'])){
				$oldClient_Code = $this->getClientById($_data['id']);
				$client_code = $oldClient_Code['client_number'];
			}else{
				$db = new Application_Model_DbTable_DbGlobal();
				$client_code = $db->getNewClientIdTypeTwo();
			}
			$photoname = str_replace(" ", "_", $client_code) . '.jpg';
			$upload = new Zend_File_Transfer();
			$upload->addFilter('Rename',
					array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'photo');
			$receive = $upload->receive();
			if($receive)
			{
				$_data['photo'] = $photoname;
			}
			else{
				$_data['photo']="";
			}
			if (empty($_data['photo'])){
				$photo = $_data['old_photo'];
			}else{
				$photo = $_data['photo'];
			}
		    $_arr=array(
				'client_number'=> $client_code,//$_data['client_no'],
				'name_kh'	  => $_data['name_kh'],
				'sex'	      => $_data['sex'],
		    	'p_age'      => $_data['p_age'],
		    	'age'      => $_data['age'],
		    	'is_relevant_type'=> $_data['is_type_of_relevant'],
				'photo_name'  =>$photo,
	    		'current_address'=>$_data['current_address'],
	    		'nation_id_issue_date'=>$_data['national_id_issue_date'],
	    		'p_nation_issue_date'=>$_data['p_national_id_issue_date'],
				'client_d_type'      => $_data['client_d_type'],
				'nation_id'=>$_data['national_id'],
		    	'nationality'=>$_data['nationality'],
				'phone'	      => $_data['phone'],
		    	'email'	      => $_data['email'],
				'create_date' => date("Y-m-d"), 
				'remark'	  => $_data['desc'],
				'status'      => $_data['status'],
				'user_id'	  => $this->getUserId(),
		    	'hname_kh'      => $_data['hname_kh'],
		    	'p_nationality'      => $_data['p_nationality'],
		    	'ksex'      => $_data['ksex'],
		    	'lphone'      => $_data['lphone'],
		    	'p_age'      => $_data['p_age'],
				'joint_doc_type'      => $_data['join_d_type'],
		    	'rid_no'      => $_data['rid_no'],
		    	'arid_no'      => $_data['arid_no'],
		    	'refe_nation_id'      => $_data['reference_national_id'],
		    	//'type'      => 2,
		);
		if(!empty($_data['id'])){
			$where = 'client_id = '.$_data['id'];
			$this->update($_arr, $where);
			return $_data['id'];
			 
		}else{
			return  $this->insert($_arr);
		}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getClientById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE client_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getClientDetailInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.client_id , c.client_number ,c.`name_kh`,c.`sex`,c.`age`,c.`nation_id`,c.`nation_id_issue_date`,
		c.`phone`,c.`dob`,c.`pob`,c.`tel`,c.`email`,c.`current_address`,
		(SELECT project_name FROM `ln_project` WHERE br_id =c.branch_id LIMIT 1) AS project_name ,
		 c.`remark`,c.`status`,
		 c.bname_kh,c.`hname_kh`,c.`lphone`,c.`ksex`,c.`p_age`,
		c.`nationality`,c.`p_nationality`,c.`p_nation_issue_date`,c.`rid_no`,c.`arid_no`,c.refe_nation_id,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type=26 AND v.key_code = c.`is_relevant_type`) AS relevent,
		 c.photo_name FROM `ln_client_property` AS c WHERE client_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllClients($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
		$sql = "
		SELECT client_id,
		client_number,CONCAT(name_kh,' , ',hname_kh) AS name_kh,
		(SELECT name_en FROM `ln_view` WHERE TYPE =11 AND sex=key_code LIMIT 1) AS sex
		,phone,current_address,
		    create_date,
		    (SELECT  CONCAT(first_name,' ', last_name) FROM rms_users WHERE id=user_id ) AS user_name,
			status, 'ប្រវិត្តិរូប',
         'កែប្រែ' FROM $this->_name WHERE status > -1 ";
		if(empty($search['show_all'])){
			$where = " AND  name_kh!=''  AND ".$from_date." AND ".$to_date;
			if(!empty($search['adv_search'])){
				$s_where = array();
				$s_search = addslashes(trim($search['adv_search']));
				$s_where[] = " client_number LIKE '%{$s_search}%'";
				$s_where[] = " name_en LIKE '%{$s_search}%'";
				$s_where[] = " name_kh LIKE '%{$s_search}%'";
				$s_where[] = " phone LIKE '%{$s_search}%'";
				$s_where[] = " house LIKE '%{$s_search}%'";
				$s_where[] = " street LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['status']>-1){
				$where.= " AND status = ".$search['status'];
			}
		}else{
			$where ='';
		}
		$order=" ORDER BY client_id DESC ";
		return $db->fetchAll($sql.$where.$order);	
	}
	public function getGroupCodeBYId($data){
		$db = $this->getAdapter();
			$sql = " SELECT *,
				(SELECT t.type_nameen FROM `ln_properties_type` as t WHERE t.id=property_type LIMIT 1) As property_type
				FROM `ln_properties` 
			WHERE id = ".$data['land_id']." LIMIT 1" ;
			 $rs = $db->fetchRow($sql);
			if(empty($rs)){return ''; }else{
				return $rs;
			}
		
	}
	function getPrefixCode($branch_id){
		$db  = $this->getAdapter();
		$sql = " SELECT prefix FROM `ln_branch` WHERE br_id = $branch_id  LIMIT 1";
		return $db->fetchOne($sql);
	}	
	public function getClientCode(){//for get client by branch
		$db = $this->getAdapter();
			$sql = "SELECT COUNT(client_id) AS number FROM `ln_client_property`
			WHERE 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre ="";
		for($i = $acc_no;$i<6;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}

	function deleteClient($id){
		$db = $this->getAdapter();
		$arr = array( 'status'=> -1);
		$where = ' client_id = '.$id;
		$this->_name = "ln_client_property";
		$this->update($arr, $where);
	}
	public function addClientByAjax($_data){
		try{
			$db = new Application_Model_DbTable_DbGlobal();
			$client_code = $db->getNewClientIdTypeTwo();
			$_arr=array(
					'client_number'=> $client_code,//$_data['client_no'],
					'name_kh'	  => $_data['name_kh'],
					'sex'	      => $_data['customer_sex'],
					'client_d_type'      => $_data['client_d_type'],
					'nation_id'=>$_data['national_id'],
					'nation_id_issue_date'=>$_data['national_id_issue_date'],
					'nationality'=>$_data['nationality'],
					'age'      => $_data['age'],
					'phone'	      => $_data['cus_phone'],
					'email'	      => $_data['email'],
					
					'hname_kh'      => $_data['hname_kh'],
					'ksex'      => $_data['ksex'],
					'p_nationality'      => $_data['p_nationality'],
					'p_age'      => $_data['p_age'],
					'lphone'      => $_data['lphone'],
					'rid_no'      => $_data['rid_no'],
					'joint_doc_type'      => $_data['join_d_type'],
					'is_relevant_type'=> $_data['is_type_of_relevant'],
					'p_nation_issue_date'=>$_data['p_national_id_issue_date'],
					
					'create_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId(),
					//'type'      => 2,
					'status'      => 1,

			);
			return  $this->insert($_arr);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
}

