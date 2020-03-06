<?php

class Issue_Model_DbTable_Dbgivehouse extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_issue_house';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllIssueHouse($search = null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$from_date =(empty($search['start_date']))? '1': " rs.issue_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rs.issue_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql = "SELECT rs.id,
			    	(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = rs.branch_id LIMIT 1) AS branch_name,
			    	(SELECT name_kh FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) customer_name,
			    	(SELECT tel FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) AS tel,
			    	p.land_address ,
			    	p.street  AS street,
			    	CASE    
						WHEN  `rs`.`payment_id` = 1 THEN '".$tr->translate("IS_PAYOFF")."'
						WHEN  `rs`.`payment_id` = 2 THEN '".$tr->translate("PAY_INSTALLMENT")."' 
					END AS payment_id,
			    	rs.electric_start,rs.water_start,rs.issue_date,
    				(SELECT  first_name FROM rms_users WHERE rms_users.id=rs.user_id LIMIT 1) AS user_name,
    				CASE    
					WHEN  `rs`.`status` = 0 THEN '".$tr->translate("DEACTIVE")."'
					WHEN  `rs`.`status` = 1 THEN '".$tr->translate("ACTIVE")."' 
					END AS status
	    		FROM 
	    			ln_sale AS s,
	    			`ln_properties` `p`,
	    			ln_issue_house AS rs
	    	WHERE s.id = rs.sale_id AND p.id = s.house_id ";
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
    		$s_where[] = " p.street LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND rs.status = ".$search['status'];
    	}
    	if(!empty($search['streetlist'])){
    		$where.= " AND street ='".$search['streetlist']."'";
    	}
    	if(!empty($search['land_id']) AND $search['land_id']>-1){
    		$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
    	}
    	if($search['branch_id']>0){
    		$where.= " AND rs.branch_id = ".$search['branch_id'];
    	}
    	$where.=$dbp->getAccessPermission("rs.branch_id");
    	$order=" ORDER BY s.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
	function addIssueHouse($data){
	    $arr = array(
    		'branch_id'=>$data['branch_id'],
    		'sale_id'=>$data['sale_no'],
    		'payment_id'=>$data['payment_id'],
    		'water_start'=>$data['water_start'],
    		'electric_start'=>$data['electric_start'],
    		'contact_construction'=>$data['contact_contruction'],
    		'note'=>$data['note'],
    		'issue_date'=>$data['issue_date'],
    		'user_id'=>$this->getUserId()
	    );
	    if(empty($data['id'])){
	    	$this->insert($arr);
	    }else{
	    	$arr['status']=$data['status'];
	    	$where ='id = '.$data['id'];
	    	$this->update($arr, $where);
	    }
	   
	}
	function getIssueHousebyId($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM ln_issue_house WHERE id = $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	public function getSaleNoByProjectNotYetReceiveHouse($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT *, s.`id`,
		CONCAT((SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = s.`client_id` LIMIT 1),' (',
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=s.`house_id` LIMIT 1),')' ) AS `name`
		FROM `ln_sale` AS s
		WHERE (s.`is_cancel` =0 ) AND s.`branch_id` =".$branch_id;
		$houseGive = $this->getHouseGiveReady();
		$sql.=" AND s.`id` NOT IN ($houseGive)";
		return $db->fetchAll($sql);
	}
	function getHouseGiveReady(){
		$db = $this->getAdapter();
		$sql=" SELECT GROUP_CONCAT(DISTINCT ish.sale_id) FROM `ln_issue_house` AS ish WHERE ish.status=1 ";
		$row = $db->fetchOne($sql);
		return $row;
	}
    
}