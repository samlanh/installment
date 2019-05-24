<?php

class Loan_Model_DbTable_DbPlongStep extends Zend_Db_Table_Abstract
{
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    }
    function getAllissueplong($search){
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$from_date =(empty($search['start_date']))? '1': " pr.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " pr.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql="SELECT `pr`.`id` AS `id`,
    	
    	(SELECT `ln_project`.`project_name`   	FROM `ln_project`  	WHERE (`ln_project`.`br_id` = `pr`.`branch_id`)	LIMIT 1) AS `branch_name`,
    	`c`.`name_kh`         AS `name_kh`,
    	`p`.`land_address`    AS `land_address`,
    	`p`.`street`          AS `street`,
    	c.phone,
    	pr.date,
    	pr.note,
    	CASE
			WHEN  pr.process_status = 1 THEN '1.HQ-P'
			WHEN  pr.process_status = 2 THEN '2.P-HQ'
			WHEN  pr.process_status = 3 THEN '3.HQ-T'
			WHEN  pr.process_status = 4 THEN '4.HQ-P'
			WHEN  pr.process_status = 5 THEN '5.HQ-C'
		END AS processing
    	 ";
    	$sql.=$dbp->caseStatusShowImage("pr.status");
    	$sql.="
    	FROM (`ln_processing_plong` `pr`,
		     `ln_client` `c`
		   JOIN `ln_properties` `p`)
    	WHERE (`c`.`client_id` = `pr`.`customer_id`)
       AND (`p`.`id` = `pr`.`property_id`) 
    	";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    	}
    	if (!empty($search['process_status'])){
    		$where.= " AND pr.process_status = ".$search['process_status'];
    	}
    	if($search['status']>-1){
    		$where.= " AND c.status = ".$search['status'];
    	}
    	if(!empty($search['client_name']) AND ($search['client_name'])>0){
    		$where.= " AND `c`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND pr.branch_id = ".$search['branch_id'];
    	}
    	if(($search['land_id'])>0){
    		$where.= " AND pr.property_id = ".$search['land_id'];
    	}
    	$where.=$dbp->getAccessPermission("`pr`.`branch_id`");
    
    	$order = " ORDER BY pr.id DESC";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addPlongStep($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$ids = explode(',', $data['identity']);
    		if(!empty($data['identity'])){
    			foreach ($ids as $i){
    				$arr = array(
    						'branch_id'		=>$data['branch_id'],
    						'sale_id'		=>$data['sale_id'.$i],
    						'customer_id'	=>$data['customer_id'.$i],
    						'property_id'	=>$data['property_id'.$i],
    						'date'			=>$data['date'],
    						'process_status'	=>$data['process_status'],
    						'give_by'			=>$data['give_by'],
    						'receive_by'	=>$data['receive_by'],
    						'note'			=>$data['note'.$i],
    						'create_date'	=>date('Y-m-d H:i:s'),
    						'modify_date'	=>date('Y-m-d H:i:s'),
    						'user_id'			=>$this->getUserId(),
    						'status'=>1,
    				);
    				$this->_name="ln_processing_plong";
    				$id = $this->insert($arr);
    		
    				if ($data['process_status']>0){
    					$arr_detail=array(
    							'processplong_id'=>$id,
    							'date'			=>$data['date'],
    							'process_status'=>$data['process_status'],
    							'give_by'		=>$data['give_by'],
    							'receive_by'	=>$data['receive_by'],
    							'note'			=>$data['note'.$i],
    					);
    					$this->_name="ln_processing_plong_detail";
    					$this->insert($arr_detail);
    				}
    				if ($data['process_status']==7){
    					$this->_name="ln_issueplong";
    					$arrissuepl = array(
    							'sale_id'=>$data['sale_id'.$i],
    							'issue_date'=>$data['date'],
    							'layout_number'=>"",
    							'note'=>$data['note'.$i],
    							'is_receivedplong'=>0,
    					);
    					$this->insert($arrissuepl);
    				}
    		
    			}
    			 
    		}
//     			$arr = array(
//     				'branch_id'		=>$data['branch_id'],
//     				'sale_id'		=>$data['loan_number'],
//     				'customer_id'	=>$data['customer_id'],
//     				'property_id'	=>$data['property_id'],
//     				'date'			=>$data['date'],
//     				'process_status'	=>$data['process_status'],
//     				'give_by'	=>$data['give_by'],
//     				'receive_by'	=>$data['receive_by'],
//     				'note'	=>$data['note'],
//     				'create_date'	=>date('Y-m-d H:i:s'),
//     				'modify_date'	=>date('Y-m-d H:i:s'),
//     				'user_id'			=>$this->getUserId(),
//     				'status'=>1,
//     			);
//     			$this->_name="ln_processing_plong";
//     			$id = $this->insert($arr);
//     			if ($data['process_status']>0){
//     				$arr_detail=array(
//     					'processplong_id'=>$id,
//     					'date'			=>$data['date'],
//     					'process_status'=>$data['process_status'],
//     					'give_by'		=>$data['give_by'],
//     					'receive_by'	=>$data['receive_by'],
//     					'note'			=>$data['note'],
//     				);
//     				$this->_name="ln_processing_plong_detail";
//     				$this->insert($arr_detail);
//     			}
    			
//     			if ($data['process_status']==5){
//     					$this->_name="ln_issueplong";
//     					$arrissuepl = array(
//     						'sale_id'=>$data['loan_number'],
//     						'issue_date'=>$data['date'],
//     						'layout_number'=>"",
//     						'note'=>$data['note'],
//     						'is_receivedplong'=>0,
//     					);
//     					$this->insert($arrissuepl);
//     			}
    			$db->commit();
    			return $id;
    		}catch (Exception $e){
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    			$db->rollBack();
    		}
    }
    
    function getPlogStepById($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT *,(SELECT p.land_address FROM ln_properties AS p WHERE p.id = ln_processing_plong.property_id LIMIT 1) AS land_address FROM ln_processing_plong WHERE id = $id";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("branch_id");
    	return $db->fetchRow($sql);
    }
    function getPlogStepDetailById($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT pr.*,CASE
			WHEN  pr.process_status = 1 THEN '1.HQ-P'
			WHEN  pr.process_status = 2 THEN '2.P-HQ'
			WHEN  pr.process_status = 3 THEN '3.HQ-T'
			WHEN  pr.process_status = 4 THEN '4.HQ-P'
			WHEN  pr.process_status = 5 THEN '5.HQ-C'
		END AS processing
    	 FROM ln_processing_plong_detail as pr WHERE pr.processplong_id = $id ORDER BY pr.id DESC";
    	return $db->fetchAll($sql);
    }
    function getPlogStepDetailRowById($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT pr.*,CASE
    	WHEN  pr.process_status = 1 THEN '1.HQ-P'
    	WHEN  pr.process_status = 2 THEN '2.P-HQ'
    	WHEN  pr.process_status = 3 THEN '3.HQ-T'
    	WHEN  pr.process_status = 4 THEN '4.HQ-P'
    	WHEN  pr.process_status = 5 THEN '5.HQ-C'
    	END AS processing
    	FROM ln_processing_plong_detail as pr WHERE pr.id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    public function addPlongStepDetail($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if ($data['process_status']>0){
    			$arr_detail=array(
    					'processplong_id'			=>$data['id'],
    					'date'			=>$data['date'],
    					'process_status'	=>$data['process_status'],
    					'give_by'	=>$data['give_by'],
    					'receive_by'	=>$data['receive_by'],
    					'note'	=>$data['note'],
    					'user_id'			=>$this->getUserId(),
    			);
    			$this->_name="ln_processing_plong_detail";
    			if (!empty($data['detail_id'])){
    				$wheredetal = "id = ".$data['detail_id'];
    				$this->update($arr_detail, $wheredetal);
    			}else{
    				$this->insert($arr_detail);
    			}
    			
    			$arr = array(
    					'date'			=>$data['date'],
    					'process_status'	=>$data['process_status'],
    					'give_by'	=>$data['give_by'],
    					'receive_by'	=>$data['receive_by'],
    					'modify_date'	=>date('Y-m-d H:i:s'),
    			);
    			$this->_name="ln_processing_plong";
    			$where=" id = ".$data['id'];
    			$id = $this->update($arr, $where);
    			
    			if ($data['process_status']==7){
    				$rs = $this->getPlogStepById($data['id']);
    				if (!empty($rs)){
	    				$this->_name="ln_issueplong";
	    				$arr = array(
	    						'sale_id'=>$rs['sale_id'],
	    						'issue_date'=>$data['date'],
	    						'layout_number'=>"",
	    						'note'=>$data['note'],
	    						'is_receivedplong'=>0,
	    				);
	    				$this->insert($arr);
    				}
    				
    				$arr = array(
    					'is_issueplong'=>1,
    					'issueplong_date'=>date("Y-m-d")
    				);
    				$where="id = ".$rs['sale_id'];
    				$this->_name="ln_sale";
    				$this->update($arr, $where);
    			}
    		}
    		
    		$db->commit();
    		return $id;
    	}catch (Exception $e){
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		$db->rollBack();
    	}
    }
}