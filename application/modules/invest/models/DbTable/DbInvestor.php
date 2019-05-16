<?php

class Invest_Model_DbTable_DbInvestor extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_investor';
    public function getUserId(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	return $db->getUserId();
    }
    
    function getAllInvestor($search = null){
    	try{
    		$db = $this->getAdapter();
    		$from_date =(empty($search['start_date']))? '1': "i.create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': "i.create_date <= '".$search['end_date']." 23:59:59'";
    		$where = " WHERE  ".$from_date." AND ".$to_date;
    		$sql = "
    		SELECT
				i.id,
				i.name,
				(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =11 AND i.sex=v.key_code LIMIT 1) AS sex,
				(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =23 AND i.document_type=v.key_code LIMIT 1) AS document_type,
				i.document_no,
				i.nation,
				i.phone,
				i.email ";
    			
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->caseStatusShowImage("i.status");
    		$sql.=" FROM `rms_investor` AS i ";
    			
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
    			$s_where[] = " i.name LIKE '%{$s_search}%'";
    			$s_where[] = " i.document_no LIKE '%{$s_search}%'";
    			$s_where[] = " i.phone LIKE '%{$s_search}%'";
    			$s_where[] = " i.email LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if($search['status']>-1){
    			$where.= " AND status = ".$search['status'];
    		}
    		if(!empty($search['document_type'])){
    			$where.=" AND i.document_type = ".$search['document_type'];
    		}
    		$order=" ORDER BY id DESC ";
    		return $db->fetchAll($sql.$where.$order);
    			
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    
    public function getNewInvestorCode(){
    	$this->_name='rms_investor';
    	$db = $this->getAdapter();
    	$sql=" SELECT count(id)  FROM $this->_name WHERE 1 LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	 
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$prefix="";
    	$pre= "";
    	for($i = $acc_no;$i<6;$i++){
    		$pre.='0';
    	}
    	return $prefix.$pre.$new_acc_no;
    }
	public function addInvestor($_data){
		try{
			$investorCode = $this->getNewInvestorCode();
		    $_arr=array(
				'code'	  				=> $investorCode,
	    		'name'	  				=> $_data['name'],
				'sex'	      			=> $_data['sex'],
				'document_type'  		=> $_data['document_type'],
				'document_no'	  		=> $_data['document_no'],
				'doc_issue_date'	    => $_data['doc_issue_date'],
				'nation'				=>$_data['nation'],
		    	'phone'					=>$_data['phone'],
		    	'email' 				=> $_data['email'],
		    	'current_address' 		=> $_data['current_address'],
				'note'	      			=> $_data['note'],
		    	'user_id'	  			=> $this->getUserId(),
		    	'modify_date' 			=> date("Y-m-d H:i:s"),
			); 
		    
			$part= PUBLIC_PATH.'/images/photo/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
		    $photo_name = $_FILES['photo']['name'];
		    if (!empty($photo_name)){
		    	$tem =explode(".", $photo_name);
		    	$image_name_stu = "inv-profile_".date("Y").date("m").date("d").time().".".end($tem);
		    	$tmp = $_FILES['photo']['tmp_name'];
		    	if(move_uploaded_file($tmp, $part.$image_name_stu)){
		    		move_uploaded_file($tmp, $part.$image_name_stu);
		    		$photo = $image_name_stu;
		    		$_arr['photo']=$photo;
		    	}
		    }		    
			if(!empty($_data['id'])){
				$investor_id =  $_data['id'];
				$_arr['status'] = $_data['status'];
				$where = 'id = '.$investor_id;
				$this->update($_arr, $where);			 
			}else{
				$_arr['create_date'] = date("Y-m-d H:i:s");
				$_arr['status'] = 1;
				$investor_id = $this->insert($_arr);
			}
		
			return $investor_id;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getInvestorById($id){
		$db = $this->getAdapter();
		$sql="SELECT i.* FROM rms_investor AS i WHERE i.id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
}