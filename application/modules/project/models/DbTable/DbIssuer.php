<?php

class Project_Model_DbTable_DbIssuer extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_contract_issuer';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function addContractIssuer($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{

	    	$arr = array(
	    			'nameKh'				=>$data['nameKh'],
	    			'sex'					=>$data['sex'],
	    			'dob'					=>$data['dob'],
	    			'nationality'			=>$data['nationality'],
					'nationId'				=>$data['nationId'],
					'nationIdIssueDate'		=>$data['nationIdIssueDate'],
					'tel'					=>$data['tel'],
					'position'				=>$data['position'],
					'currentAddress'		=>$data['currentAddress'],
					
					'nameKhWith'				=>$data['nameKhWith'],
	    			'sexWith'					=>$data['sexWith'],
	    			'dobWith'					=>$data['dobWith'],
	    			'nationalityWith'			=>$data['nationalityWith'],
					'nationIdWith'				=>$data['nationIdWith'],
					'nationIdIssueDateWith'		=>$data['nationIdIssueDateWith'],
					'telWith'					=>$data['telWith'],
					'positionWith'				=>$data['positionWith'],
					'currentAddressWith'		=>$data['currentAddressWith'],
					
	    			'user_id'=>$this->getUserId(),
	    			'modify_date'=>date("Y-m-d H:i:s"),
	    			
	    		);
				
			
	    	$this->_name='ln_contract_issuer';
	    	if(!empty($data['id'])){
				$id =$data['id'];
	    		$where = 'id = '.$data['id'];
	    		$arr['status']=$data['status'];
	    		$this->update($arr, $where);
	    	}else{
				$arr['create_date']=date("Y-m-d H:i:s");
	    		$arr['status']=1;
	    		$id = $this->insert($arr);
	    	}
	    	$db->commit();
			return $id;
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    	}
	}
	
	function geteAllContractIssuer($search=null){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$currentLang = $dbp->currentlang();
    	$title="name_en";
		if ($currentLang==1){
    		$title="name_kh";
    	}
		$sql="
			SELECT 
			t.`id`,
			t.`nameKh`,
			(SELECT $title FROM `ln_view` WHERE TYPE =11 AND t.`sex`=key_code LIMIT 1) AS sex,
			t.`nationality`,
			t.`nationId`,
			
			t.`nameKhWith`,
			(SELECT $title FROM `ln_view` WHERE TYPE =11 AND t.`sexWith`=key_code LIMIT 1) AS sexWith,
			t.`nationalityWith`,
			t.`nationIdWith`,
			(SELECT CONCAT(u.first_name,' ',u.last_name) FROM `rms_users` AS u WHERE u.id = t.`user_id` LIMIT 1) AS user_name
		";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("t.`status`");
		$sql.=" FROM `ln_contract_issuer` AS t WHERE 1 ";
		$where="";
		if($search['status_search']>-1){
			$where.=" AND t.status=".$search['status_search'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]="t.`nameKh` LIKE'%{$s_search}%'";
			$s_where[]="t.`nationality` LIKE'%{$s_search}%'";
			$s_where[]="t.`tel` LIKE'%{$s_search}%'";
			
			$s_where[]="t.`nameKhWith` LIKE'%{$s_search}%'";
			$s_where[]="t.`nationalityWith` LIKE'%{$s_search}%'";
			$s_where[]="t.`telWith` LIKE'%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY t.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getContractIssuerById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_contract_issuer` AS t WHERE t.`id`=".$id;
		return $db->fetchRow($sql);
	}
	
}