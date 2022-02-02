<?php

class Loan_Model_DbTable_DbAgreement extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale_cancel';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	
	public function getSaleAgreement($search=null){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$edit_sale = $tr->translate("EDITSALEONLY");
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	
    	$str = 'name_en';
    	if($lang==1){
    		$str = 'name_kh';
    	}
    	
    	$from_date =(empty($search['start_date']))? '1': " a.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " a.createDate <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql="SELECT `a`.`id` AS `id`,
	    	(SELECT `ln_project`.`project_name` FROM `ln_project`
			   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
			   LIMIT 1) AS `branch_name`,
		    `c`.`name_kh` AS `name_kh`,
		    `c`.`phone` AS `phone`,
		    (SELECT `p`.`land_address`  FROM ln_properties p WHERE p.id = s.house_id LIMIT 1) AS `land_address`,     
		    (SELECT `p`.`street` FROM ln_properties p WHERE p.id = s.house_id LIMIT 1) AS `street`,
		    (SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
		    a.title,
        	`a`.`createDate`        AS `createDate`,
        (SELECT  first_name FROM rms_users WHERE id=a.userId limit 1 ) AS user_name
		FROM (`ln_sale` `s`
		    JOIN `ln_client` `c`
		   JOIN `ln_sale_conditionagreement` `a`)
		WHERE (
			    (`c`.`client_id` = `s`.`client_id`)
       		    AND (`a`.`saleId` = `s`.`id`)
    		)";
    	
    	$db = $this->getAdapter();
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " a.title LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " (SELECT `p`.`land_address`  FROM ln_properties p WHERE p.id = s.house_id LIMIT 1) LIKE '%{$s_search}%'";
      	 	$s_where[] = " (SELECT `p`.`street`  FROM ln_properties p WHERE p.id = s.house_id LIMIT 1) LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_en LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.phone LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if(!empty($search['land_id']) AND $search['land_id']>-1){
    		$where.= " AND s.house_id = ".$search['land_id'];
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if(($search['schedule_opt'])>0){
    		$where.= " AND s.payment_id = ".$search['schedule_opt'];
    	}
    	$order = " ORDER BY a.id DESC";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("`a`.`branch_id`");
    	return $db->fetchAll($sql.$where.$order);
	}
	public function addSaleAgreement($data){
		try{
				$db= $this->getAdapter();
				$arr = array(
					'branch_id'		=> $data['branch_id'],
					'saleId'		=> $data['sale_client'],
					'title'			=> $data['title'],
					'description'	=> $data['condition'],
					'userId'		=> $this->getUserId(),
					'createDate'	=> date('Y-m-d h:i:s'),
					'modifyDate'	=> date('Y-m-d h:i:s'),
					);
				$this->_name="ln_sale_conditionagreement";
				$this->insert($arr);
			 
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function updateSaleAgreement($data){
		try{
			$db= $this->getAdapter();
			$arr = array(
					'branch_id'		=> $data['branch_id'],
					'saleId'		=> $data['sale_client'],
					'title'			=> $data['title'],
					'description'	=> $data['condition'],
					'userId'		=> $this->getUserId(),
					'createDate'	=> date('Y-m-d h:i:s'),
					'modifyDate'	=> date('Y-m-d h:i:s'),
			);
			$this->_name="ln_sale_conditionagreement";
			$where=' id='.$data['contractId'];
			$this->update($arr, $where);
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getSaleAgreementById($contractId){
		$db= $this->getAdapter();
		$sql="SELECT * FROM ln_sale_conditionagreement WHERE id=".$contractId;
		
		$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("`a`.`branch_id`");
    	return $db->fetchRow($sql);
	}
}