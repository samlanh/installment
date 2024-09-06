<?php

class Issue_Model_DbTable_Dbplonglist extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_plonglist';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function addPlongList($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if(!empty($data['identity'])){
    			$ids = explode(',', $data['identity']);
    			foreach ($ids as $i){
					if(!empty($data['soft_title'.$i]) AND !empty($data['hard_title'.$i])){
						if(!empty($data['soft_title'.$i])){
							$arr = array(
								'projectId'=>$data['project_id'],
								'propertyid'=>$data['land_id'.$i],
								'plongType'=>1,
								'plongNumber'=>$data['soft_title'.$i],
								'userId'=>$this->getUserId(),
								'createDate'=>date('Y-m-d h:s'),
							);
							$this->insert($arr);
						}
					
						if(!empty($data['hard_title'.$i])){
							$arr = array(
								'projectId'=>$data['project_id'],
								'propertyid'=>$data['land_id'.$i],
								'plongType'=>2,
								'plongNumber'=>$data['hard_title'.$i],
								'userId'=>$this->getUserId(),
								'createDate'=>date('Y-m-d h:s'),
							);
							$this->insert($arr);
						}
					}
    			}
    		}
		
	    	$db->commit();
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    	}
	}
	function plongListData($search)
	{
		
			$dbp = new Application_Model_DbTable_DbGlobal();
			$from_date =(empty($search['start_date']))? '1': " pr.createDate >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " pr.createDate <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
		$sql = "SELECT 
				(`p`.`id`) AS `id`,
				(SELECT `ln_project`.`project_name` FROM `ln_project` WHERE (`ln_project`.`br_id` = `p`.`branch_id`)	LIMIT 1) AS `branchName`,
				`p`.`land_address`    AS `landAddress`,
				(SELECT pls.plongNumber FROM ln_plonglist pls WHERE plongType=1 and `p`.`id` = `pls`.`propertyId` LIMIT 1) as softTitle,
				(SELECT pls.plongNumber FROM ln_plonglist pls WHERE plongType=2 and `p`.`id` = `pls`.`propertyId` LIMIT 1) as hardTitle,
				s.id,
				(SELECT `c`.`name_kh` FROM ln_client c WHERE `c`.`client_id`=s.`client_id` LIMIT 1) AS clientName,
				(SELECT `c`.`phone` FROM ln_client c WHERE `c`.`client_id`=s.`client_id` LIMIT 1) AS phone_number,					 
				s.price_sold,
				(SELECT totalPrincipalPaid from `v_getsaleprincipalpaid` WHERE saleId=s.id LIMIT 1) AS totalPrincipalPaid, 
				(SELECT ps.title FROM `ln_plongstep_option` AS ps WHERE ps.id = pp.process_status LIMIT 1) AS processing,
				CASE    
					WHEN  (SELECT rec.house_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.house_id = p.id ORDER BY rec.id DESC LIMIT 1 ) IS NOT NULL THEN 'បានប្រគល់'
					WHEN pp.process_status=5 THEN 'មិនទាន់ប្រគល់'
					ELSE  ''
				END AS statusReceive ";
				
				$sql.="
				FROM (`ln_properties` `p`
					LEFT JOIN `ln_processing_plong` pp ON p.id=pp.property_id)
					LEFT JOIN `ln_sale` s ON s.house_id=p.id
				WHERE 
					p.status=1
					AND p.`id` IN (SELECT propertyId FROM `ln_plonglist`)
			   ";
			$where="";
			if(!empty($search['adv_search'])){
				$s_where = array();
				$s_search = addslashes(trim($search['adv_search']));
				$s_where[] = " p.`land_address` LIKE '%{$s_search}%'";
				$s_where[]= " p.id IN (SELECT pls.propertyId FROM ln_plonglist pls WHERE `p`.`id` = `pls`.`propertyId` AND pls.`plongNumber` LIKE '%{$s_search}%'"." )";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			
			if(!empty($search['client_name']) AND ($search['client_name'])>0){
				$where.= " AND `s`.`client_id`=".$search['client_name'];
			}
			if(($search['branch_id'])>0){
				$where.= " AND p.branch_id = ".$search['branch_id'];
			}
			if(($search['land_id'])>0){
				$where.= " AND p.id = ".$search['land_id'];
			}
			if(($search['process_status'])>0){
				$where.= " AND pp.process_status = ".$search['process_status'];
			}
			if(($search['plong_type'])>0){//1 soft ,2 hard title
				if($search['plong_type']==1){
					$where.= "AND p.id =(SELECT pls.propertyId FROM ln_plonglist pls WHERE plongType=1 and `p`.`id` = `pls`.`propertyId` LIMIT 1)";
				}else{
					$where .= "AND p.id =(SELECT pls.propertyId FROM ln_plonglist pls WHERE plongType=2 and `p`.`id` = `pls`.`propertyId` LIMIT 1)";
				}
			}
			if(($search['status_plong'])>0){//1 receied,2notyet
				if($search['status_plong']==1){
					$where.= " AND p.id IN (SELECT rec.house_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.house_id = p.id ORDER BY rec.id DESC)";
				}else{
					$where .= " AND p.id IN (SELECT rec.house_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.house_id = p.id ORDER BY rec.id DESC)";
				}
			}
			$where.=$dbp->getAccessPermission("`p`.`branch_id`");
			$db = $this->getAdapter();
			return $db->fetchAll($sql.$where);
	}
}