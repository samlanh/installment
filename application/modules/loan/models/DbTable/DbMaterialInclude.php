<?php
class Loan_Model_DbTable_DbMaterialInclude extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_material_include';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function addMeterialInclude($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$invoice = $this->getNoMaterialInclude($data['branch_id']);
			
			$_arr = array(
				'invoice'		=>$invoice,
				'branch_id'		=>$data['branch_id'],
				'sale_id'		=>$data['sale_client'],
				'house_id'		=>$data['house_id'],
				'client_id'		=>$data['customer'],
				'for_date'		=>$data['Date'],
				'description'	=>$data['Description'],
				'user_id'		=>$this->getUserId(),
				'create_date'	=>date('Y-m-d H:i:s'),
				'modify_date'	=>date('Y-m-d H:i:s'),
				
				
				);
			$materailinc_id = $this->insert($_arr);
			
			$ids = explode(',', $data['identity']);
			$this->_name='ln_material_include_detail';
			foreach ($ids as $j){
				$arr = array(
						'materailinc_id'	=>$materailinc_id,
						'items_id'	=>$data['items_id'.$j],
						'description'		=>$data['description_'.$j],
						'is_gived'		=>empty($data['is_gived_'.$j])?0:$data['is_gived_'.$j],
				);
				$this->insert($arr);
			}
			$_db->commit();
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();exit();
			}
	}
	function updateMeterialInclude($data,$id){
		$_db = $this->getAdapter();
		$_db->beginTransaction();
			try{
				$arr = array(
					'branch_id'		=>$data['branch_id'],
					'sale_id'		=>$data['sale_client'],
					'house_id'		=>$data['house_id'],
					'client_id'		=>$data['customer'],
					'for_date'		=>$data['Date'],
					'description'	=>$data['Description'],
					'status'		=>$data['status'],
					'user_id'		=>$this->getUserId(),
					'modify_date'	=>date('Y-m-d H:i:s'),
					);
					$where=" id = ".$id;
					$this->update($arr, $where);
					
					
					$detailidlist = '';
					if(!empty($data['identity'])){
						$ids = explode(',', $data['identity']);
						foreach ($ids as $i){
							if (empty($detailidlist)){
								if (!empty($data['detailid'.$i])){
									$detailidlist= $data['detailid'.$i];
								}
							}else{
								if (!empty($data['detailid'.$i])){
									$detailidlist = $detailidlist.",".$data['detailid'.$i];
								}
							}
						}
					}
					$this->_name='ln_material_include_detail';
					$whereDelete = " materailinc_id = ".$id;
					if (!empty($detailidlist)){ // check if has old payment detail  detail id
						$whereDelete.=" AND id NOT IN (".$detailidlist.")";
					}
					$this->delete($whereDelete);
					
					$ids = explode(',', $data['identity']);
					foreach ($ids as $j){ 
						if (!empty($data['detailid'.$j])){
							$arr = array(
									'materailinc_id'	=>$id,
									'items_id'			=>$data['items_id'.$j],
									'description'		=>$data['description_'.$j],
									'is_gived'		=>empty($data['is_gived_'.$j])?0:$data['is_gived_'.$j],
									);
							$whereUpdate=" id=".$data['detailid'.$j];
							$this->update($arr, $whereUpdate);
						}else{
							$arr = array(
									'materailinc_id'	=>$id,
									'items_id'	=>$data['items_id'.$j],
									'description'		=>$data['description_'.$j],
									'is_gived'		=>empty($data['is_gived_'.$j])?0:$data['is_gived_'.$j],
									);
							$this->insert($arr);
						}
					}
			
				$_db->commit();
			}catch(Exception $e){
				$_db->rollBack();
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
	}
	function getMaterialIncludebyid($id){
		$db = $this->getAdapter();
		
		$sql=" SELECT m.*,
				(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =m.branch_id LIMIT 1) AS branch_name,
				(SELECT logo FROM `ln_project` WHERE ln_project.br_id =m.branch_id LIMIT 1) AS photo,
		 		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =m.client_id LIMIT 1) AS client_name,
				(SELECT phone FROM `ln_client` WHERE ln_client.client_id =m.client_id LIMIT 1) AS clientPhone,
		 		(SELECT land_address FROM `ln_properties` WHERE id=m.house_id LIMIT 1) AS land_address,
		 		(SELECT street FROM `ln_properties` WHERE id=m.house_id LIMIT 1) AS street,
		 		(SELECT type_nameen FROM `ln_properties_type` WHERE id=(SELECT property_type FROM `ln_properties` WHERE id=m.house_id LIMIT 1)) AS property_type
		FROM ln_material_include AS m where m.id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("m.branch_id");
		return $db->fetchRow($sql);
	}
	function getMaterialIncludeDetailbyid($id){
		$db = $this->getAdapter();
		$tr= Application_Form_FrmLanguages::getCurrentlanguage();
		$givedLabel = $tr->translate("GIVED_TO_CUSTOMER");
		$notYerGiveLabel = $tr->translate("NOT_YET_GIVE");
		$sql="SELECT *,
		CASE    
					WHEN  is_gived = 0 THEN '".$notYerGiveLabel."'
					WHEN  is_gived = 1 THEN '".$givedLabel."'
				END AS isGiveLabel,
			(SELECT title FROM `ln_items_material` WHERE ln_items_material.id =items_id LIMIT 1) AS itmesTitle
		FROM ln_material_include_detail WHERE materailinc_id=".$id;
		return $db->fetchAll($sql);
	}
	
	function getAllIncludeMaterial($search=null){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " for_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =ln_material_include.branch_id LIMIT 1) AS branch_name,
		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ln_material_include.client_id LIMIT 1) AS client_name,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=ln_material_include.house_id LIMIT 1) AS house_no,
		description,for_date,
		(SELECT  first_name FROM rms_users WHERE id=ln_material_include.user_id LIMIT 1 ) AS user_name  ";
		
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM ln_material_include ";
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " description LIKE '%{$s_search}%'";
			$s_where[] = " house_id LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =client_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT phone FROM `ln_client` WHERE ln_client.client_id =client_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT land_address FROM `ln_properties` WHERE id=house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT street FROM `ln_properties` WHERE id=house_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if(!empty($search['land_id']) AND $search['land_id']>-1){
			$where.= " AND house_id = ".$search['land_id'];
		}
		if($search['client_name']>0){
			$where.= " AND client_id = ".$search['client_name'];
		}
		if($search['branch_id']>-0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		$where.=$dbp->getAccessPermission("branch_id");
		
		$order=" ORDER by id desc ";
		//echo $sql.$where.$order;exit();
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getNoMaterialInclude($branch_id){
		$db = $this->getAdapter();
	
		$dbtable = new Application_Model_DbTable_DbGlobal();
		$prefix ="";
	
		$sql = " SELECT count(id) FROM ln_material_include WHERE branch_id = $branch_id";
		$amount = $db->fetchOne($sql);
		$pre = '';
		$result = $amount + 1;
		$length = strlen((int)$result);
		for($i = $length;$i < 3 ; $i++){
			$pre.='0';
		}
		return $prefix.$pre.$result;
	}
}







