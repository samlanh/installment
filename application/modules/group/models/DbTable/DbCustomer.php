<?php

class Group_Model_DbTable_DbCustomer extends Zend_Db_Table_Abstract
{

    protected $_name = 'in_customer';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	function getSalebyId($sale_id){
    	$db= $this->getAdapter();
    	$sql="SELECT s.*,
				(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=$sale_id AND status=1 AND is_completed=0 LIMIT 1) as intallment
    		  FROM ln_sale AS s WHERE s.id = $sale_id LIMIT 1 "; 
    	return $db->fetchRow($sql);
    }
	
	public function add($_data){
		try{
		    $_arr=array(		    
		    	'name'	      => $_data['name'],
		    	'phone'	      => $_data['phone'],
		    	'know_by'	  => $_data['know_by'],
				'date'		  => $_data['date'],
				'from_price'  => $_data['from_price'],
				'to_price'    => $_data['to_price'],
				'requirement' => $_data['requirement'],
				'type'        => $_data['type'],
				'description' => $_data['description'],
		    	'statusreq'   => $_data['statusreq'],
				'status'	  => 1,//$_data['status'],
				'user_id'	  => $this->getUserId(),
				
				'branchId'   => $_data['branchId'],
				'saleId'     => $_data['saleId'],
		);
		$isConnectedSale = 0;
		if(!empty($_data['saleId'])){
			$rsSale = $this->getSalebyId($_data['saleId']);
			if(!empty($rsSale)){
				$_arr["clientId"] = $rsSale["house_id"];
				$_arr["propertyId"] = $rsSale["client_id"];
				$isConnectedSale = 1;
			}
		}
		
		$_arr["isConnectedSale"] = $isConnectedSale;
		$this->_name;   
		if(!empty($_data['id'])){
			$where = 'id = '.$_data['id'];
			$this->update($_arr, $where);
			return $_data['id'];
			 
		}else{
			$_arr['create_date']=date('Y-m-d');
			$customerId = $this->insert($_arr);
			
			
			$newCreateDate = new DateTime($_data['date']);
			$newCreateDate->modify('+10 day');
			$nextContact = $newCreateDate->format('Y-m-d');
			
			$proccess=1;
			if($_data['statusreq']=="បោះបង់ការទំនាក់ទំនង"){
				$proccess=0;
				$nextContact = $_data['date'];
			}
			$feedBack="ព័ត៌មានដំបូង";
			$feedBack=empty($_data['requirement']) ? $feedBack : $feedBack." ".$_data['requirement'];
			$feedBack=empty($_data['from_price']) ? $feedBack : $feedBack." តម្លៃចាប់ពី".$_data['from_price'];
			$feedBack=empty($_data['to_price']) ? $feedBack : $feedBack." ដល់".$_data['to_price'];
			if(!empty($_data['saleId'])){
				$feedBack="";
			}
			
			
			$_arr=array(
					'customer_id'	=> $customerId,
					'contact_date' 	=> $_data['date'],
					'feedback'		=> $feedBack,
					'proccess'		=> $proccess,
					'next_contact'  => $nextContact,
					'user_contact'  => $this->getUserId(),
					'create_date'   => date("Y-m-d H:i:s"),
					'modify_date'   => date("Y-m-d H:i:s"),
					'user_id'	    => $this->getUserId(),
					'saleId'		=> $_data['saleId'],
			);
			$this->_name = "ln_history_contact";
			$id = $this->insert($_arr);
			return $customerId;
		}

		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}

	public function getById($id){
		$db = $this->getAdapter();
		$sql = "SELECT 
			crt.*
			,(SELECT kn.title FROM rms_know_by as kn WHERE kn.id = crt.know_by LIMIT 1) AS know_bytitle 
			,(SELECT pro.project_name FROM ln_project AS pro WHERE pro.br_id = crt.branchId LIMIT 1 ) AS projectName
			,(SELECT s.sale_number FROM ln_sale AS s WHERE s.id = crt.saleId LIMIT 1 ) AS saleNumber
			,(SELECT crm.date_payment FROM ln_client_receipt_money AS crm WHERE crm.sale_id = crt.saleId AND crm.total_payment > 0 ORDER BY crm.id DESC LIMIT 1 ) AS lastPaidDate
			
			,(SELECT c.client_number FROM ln_client AS c WHERE c.client_id = crt.clientId LIMIT 1 ) AS clientCode
			,(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = crt.clientId LIMIT 1 ) AS clientName
			,(SELECT c.phone FROM ln_client AS c WHERE c.client_id = crt.clientId LIMIT 1 ) AS clientPhone
			
			,(SELECT p.land_address FROM ln_properties AS p WHERE p.id = crt.propertyId LIMIT 1 ) AS landAddress
			,(SELECT p.street FROM ln_properties AS p WHERE p.id = crt.propertyId LIMIT 1 ) AS landStreet
			FROM $this->_name AS crt WHERE crt.id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}


	function getAllInfo($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;	

		
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
		$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
		
		$sql = "SELECT 
		crt.id
		,crt.name
		,crt.phone
		,(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=crt.know_by LIMIT 1) as know_by
		 ,crt.`date`
		 ,crt.from_price
		 ,crt.to_price
		 ,crt.requirement
		 ,crt.type
		 ,crt.description
		
		 ";
		
		$sql.="
		, CASE
			WHEN  crt.isConnectedSale = 1 THEN 
				(SELECT CONCAT(p.land_address,' ',p.street) FROM ln_properties AS p WHERE p.id = crt.propertyId LIMIT 1 )
			ELSE ''
		END AS connectedSale ";
		
		$sql.="
		,crt.statusreq
		,(SELECT  first_name FROM rms_users WHERE id = crt.user_id limit 1 ) AS user_name
		,CASE    
			WHEN  crt.`status` = 1 THEN '".$imgtick."'
			WHEN  crt.`status` = 0 THEN '".$imgnone."'
		END AS status
		";
		
		
		
		$sql.=" FROM $this->_name AS crt ";
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " crt.name LIKE '%{$s_search}%'";
			$s_where[] = " crt.phone LIKE '%{$s_search}%'";
			$s_where[] = " crt.requirement LIKE '%{$s_search}%'";
			$s_where[] = " crt.type LIKE '%{$s_search}%'";
			$s_where[] = " crt.from_price LIKE '%{$s_search}%'";
			$s_where[] = " crt.to_price LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT p.land_address FROM ln_properties AS p WHERE p.id = crt.propertyId LIMIT 1 ) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT p.street FROM ln_properties AS p WHERE p.id = crt.propertyId LIMIT 1 ) LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND crt.status = ".$search['status'];
		}
		if(!empty($search['statusreq'])){
			$where.= " AND crt.statusreq = '".$search['statusreq']."'";
		}
		if($search['know_by']>0){
			$where.= " AND crt.know_by = ".$search['know_by'];
		}	
		$userid = $this->getUserId();
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		if (!empty($user_info['staff_id'])){
			$where.= " AND crt.user_id = ".$userid;
		}
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$userinfo = $dbgb->getUserInfo();
		if($userinfo['level']!=1){
			$where.= " AND crt.user_id = ".$userinfo['user_id'];
		}
		$order=" ORDER BY crt.id DESC ";
		return $db->fetchAll($sql.$where.$order);	
	}
	function  getAllstatusreqForOpt(){
		$db = $this->getAdapter();
		$sql = 'SELECT 
					DISTINCT statusreq as name
					,statusreq as id 
				FROM `in_customer` 
				WHERE 
					statusreq!="" 
					 ';
		$sql.=" AND statusreq !='' ";
		$s_where = array();
		$s_where[] = " statusreq != 'បន្តទំនាក់ទំនង'";
		$s_where[] = " statusreq != 'រង់ចាំការណាត់ជួប'";
		$s_where[] = " statusreq != 'បោះបង់ការទំនាក់ទំនង'";
		$s_where[] = " statusreq != 'ជាន់ភ្ញៀវ'";
		$s_where[] = " statusreq != 'ស្នើរសុំជំនួយ'";
		$sql.=' AND ('.implode(' AND ',$s_where).')';
			
		$sql.=" ORDER BY statusreq ASC ";
		$rows =  $db->fetchAll($sql);
		return $rows;
	}
	public function AllHistoryContact($crm_id){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT c.*,
		(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE c.user_contact=id LIMIT 1 ) AS user_contact_name
		";
		$sql.=", CASE
		WHEN  c.proccess = 0 THEN '".$tr->translate("DROPPED")."'
		WHEN c.proccess = 1 THEN '".$tr->translate("PROCCESSING")."'
		WHEN c.proccess = 2 THEN '".$tr->translate("WAITING_RESPONSE")."'
		WHEN c.proccess = 3 THEN '".$tr->translate("COMPLETED_CONTACT")."'
		
		END AS proccessTitle ";
		$sql.=" FROM `ln_history_contact` AS c WHERE customer_id = $crm_id ORDER BY c.id DESC";
		
		return $db->fetchAll($sql);
	}
	public function addContactHistory($_data){
		$_db= $this->getAdapter();
		try{
			
			$_data['saleId'] = empty($_data['saleId']) ? 0 : $_data['saleId'];
			$_arr=array(
					'customer_id'	  => $_data['id'],
					'contact_date' => $_data['contact_date'],
					'feedback'=> $_data['feedback'],
					'proccess'=> $_data['proccess'],
					'next_contact'=> $_data['next_contact'],
					'user_contact'=> $_data['user_contact'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId(),
					
					'saleId'=> $_data['saleId'],
			);
			$this->_name = "ln_history_contact";
			$id = $this->insert($_arr);
				
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	function checkDuplicatePhone($_data){
		$phone = empty($_data['phone']) ? "0" : $_data['phone'];
		$db = $this->getAdapter();
		$sql = "SELECT c.id FROM `in_customer` AS c WHERE 1 AND c.phone='".$phone."' ";
		
		if(!empty($_data['customerId'])){
			$sql.=" AND c.id !=".$_data['customerId'];
		}
		$sql.=" LIMIT 1 ";
		$rows =  $db->fetchRow($sql);
		if(!empty($rows)){
			return true;
		}
		return false;
	}
	
	public function addKnowBy($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$_arr=array(
					'title'	  => $_data['title'],
					'create_date' => date('Y-m-d H:i:s'),
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_know_by";
			if(!empty($_data['id'])){
				$id = $_data['id'];
				$_arr['status'] = $_data['status'];
				$where = 'id = '.$id;
				$this->update($_arr, $where);
			}else{
				$_arr['status'] = 1;
				$id =  $this->insert($_arr);
			}
			$db->commit();
			return $id;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
}