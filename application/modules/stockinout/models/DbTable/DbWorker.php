<?php

class Stockinout_Model_DbTable_DbWorker extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_worker';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllStaffWorker($search){
    	$sql="SELECT 
		    		w.id,
		    		(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = w.projectId LIMIT 1) AS branch_name,
		    		w.staffName,
		    		(SELECT name_kh FROM `ln_view` WHERE TYPE =11 AND w.gender=key_code LIMIT 1) AS gender,
		    		w.address,
		    		w.tel,
		    		w.position,
			    	w.createDate,
			    	(SELECT first_name FROM rms_users AS u WHERE u.id = w.userId LIMIT 1) AS USER ,
		    		(SELECT name_en FROM ln_view WHERE TYPE=3 AND key_code = w.status LIMIT 1) AS STATUS
		    	FROM $this->_name AS w
		    		WHERE 1 ";
    	
    	$from_date =(empty($search['start_date']))? '1': "w.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " w.createDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " w.staffName LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND w.status = ".$search['status'];
    	}
    	if($search['branch_id']>-1){
    		$where.= " AND w.projectId = ".$search['branch_id'];
    	}
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('w.projectId');
    	
    	$order=' ORDER BY w.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
    function addWorker($data){
    	try
    	{
    		$db = new Application_Model_DbTable_DbGlobalStock();
    		$result = $db->dataExisting($this->_name,"staffName='".$data['staffName']."'");
    		
    		if(empty($result)){
	    		$arr = array(
	    				'projectId'=>$data['branch_id'],
	    				'staffName'=>$data['staffName'],
	    				'gender'=>$data['gender'],
	    				'position'=>$data['position'],
	    				'tel'=>$data['tel'],
	    				'pob'=>$data['pob'],
	    				'dob'=>$data['dob'],
	    				'address'=>$data['address'],
	    				'createDate'=>date("Y-m-d"),
	    				'status'=>1,
	    				'userId'=>$this->getUserId(),
	    			);
	    		$this->insert($arr);
    		}else{
    			Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/stockinout/staff/add");
    		}
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/staff/add",2);
    	}
    }
    function updateWorker($data){
    	try
    	{
    		$arr = array(
    				'projectId'=>$data['branch_id'],
    				'staffName'=>$data['staffName'],
    				'gender'=>$data['gender'],
    				'position'=>$data['position'],
    				'tel'=>$data['tel'],
    				'pob'=>$data['pob'],
    				'dob'=>$data['dob'],
    				'address'=>$data['address'],
    				'createDate'=>date("Y-m-d"),
    				'status'=>$data['status'],
    				'userId'=>$this->getUserId()
    			);	;
    		
    		$where = 'id = '.$data['id'];
			$this->update($arr, $where);
			
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::Sucessfull("UPDATE_FAIL", "/stockinout/staff/index",2);
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
}