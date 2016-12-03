<?php

class Group_Model_DbTable_DbLand extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	public function addLandinfo($_data){
		try{
			if(!empty($_data['id'])){
				$oldCode = $this->getClientById($_data['id']);
				$land_code = $oldCode['land_code'];
			}else{
				$db = new Application_Model_DbTable_DbGlobal();
				$land_code = $db->getNewLandByBranch($_data['branch_id']);
			}
			
		    $_arr=array(
		    	'branch_id'	  => $_data['branch_id'],
				'land_code'	  => $land_code,
				'land_address'	  => $_data['land_address'],
		    	'street'	  => $_data['street'],
				'price'	      => $_data['price'],
		    	'land_price'	      => $_data['land_price'],
		    	'house_price'	      => $_data['house_price'],
				'land_size'			=>$_data['size'],
				'width'      => $_data['width'],
				'height'      => $_data['height'],
				'hardtitle'      => $_data['hardtitle'],
				'note'       => $_data['desc'],
		        'create_date'=>date("Y-m-d"),
				'status'	  => $_data['status'],
				'user_id'	  => $this->getUserId(),
		    		
		    	'property_type'	  => $_data['property_type'],
		    	'floor'	  => $_data['floor'],
		    	'living'	  => $_data['living'],
		    	'bedroom'	  => $_data['bedroom'],
		    	'dinnerroom'	  => $_data['dinnerroom'],
		    	'buidingyear'	  => $_data['buidingyear'],
		    	'parkingspace'	  => $_data['parkingspace'],
		    	//'photo'	  => ''//$_data['parkingspace'],	
				
		);
		if(!empty($_data['id'])){
			$where = 'id = '.$_data['id'];
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
		$sql = "SELECT * FROM $this->_name WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getClientDetailInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.client_id ,c.is_group,group_code, c.client_number ,c.name_kh ,c.name_en,c.join_with ,c.join_nation_id,c.relate_with, 
		c.join_tel, c.guarantor_with ,c.guarantor_tel ,nation_id,
		c.position_id ,(SELECT commune_name FROM `ln_commune` WHERE com_id = c.com_id   LIMIT 1) AS commune_name
		,(SELECT district_name FROM `ln_district` AS ds WHERE dis_id = c.dis_id  LIMIT 1) AS district_name
		,(SELECT province_en_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) AS province_en_name
		,(SELECT village_name FROM `ln_village` WHERE vill_id = c.village_id  LIMIT 1) AS village_name ,c.street,c.house ,
		c.id_type ,c.id_number, c.phone,c.job , c.spouse_name , c.spouse_nationid ,c.remark ,c.status , c.user_id ,
		(SELECT name_en FROM `ln_view` WHERE TYPE = 5 AND key_code = c.sit_status) AS sit_status , 
		(SELECT branch_nameen FROM `ln_branch` WHERE br_id =c.branch_id LIMIT 1) AS branch_name ,
		(SELECT name_en FROM `ln_client` WHERE client_id =c.parent_id ) AS parent , 
		(SELECT name_en FROM `ln_view` WHERE TYPE = 11 AND key_code =c.sex) AS sex ,
		(SELECT name_en FROM `ln_view` WHERE TYPE = 23 AND key_code =c.`client_d_type`) AS client_d_type ,
		(SELECT name_en FROM `ln_view` WHERE TYPE = 23 AND key_code =c.`join_d_type`) AS join_d_type ,  
		(SELECT name_en FROM `ln_view` WHERE TYPE = 23 AND key_code =c.`guarantor_d_type`) AS guarantor_d_type ,`guarantor_address`,      
		 photo_name FROM `ln_client` AS c WHERE client_id =  ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getClientCallateralBYId($client_id){
		$db = $this->getAdapter();
		$sql = " SELECT cc.id AS client_coll ,cd.* FROM `ln_client_callecteral` AS cc , `ln_client_callecteral_detail` AS cd WHERE  
		         cd.is_return=0 AND cd.client_coll_id = cc.id AND cc.client_id = ".$client_id;
		return $db->fetchAll($sql);
	}
    function getViewClientByGroupId($group_id){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE client_id=
    	(SELECT client_id FROM `ln_loan_member` WHERE group_id=".$db->quote($group_id)." LIMIT 1)";
    	$row=$db->fetchRow($sql);
    	return $row;
    }
	function getAllLandInfo($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;		
		$sql = "SELECT id,
				(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = ln_properties.branch_id LIMIT 1) AS branch_name,
				land_code,land_address,street,
				(SELECT t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.id = property_type limit 1) AS  pro_type,
				price,width,height,land_size,hardtitle,create_date,
		    (SELECT  CONCAT(first_name,' ', last_name) FROM rms_users WHERE id=user_id limit 1 ) AS user_name,
			status FROM $this->_name ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " land_code LIKE '%{$s_search}%'";
			$s_where[] = " land_address LIKE '%{$s_search}%'";
			$s_where[] = " street LIKE '%{$s_search}%'";
			$s_where[] = " price LIKE '%{$s_search}%'";
			$s_where[] = " land_size LIKE '%{$s_search}%'";
			$s_where[] = " width LIKE '%{$s_search}%'";
			$s_where[] = " height LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['branch_id']>-1){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if(($search['property_type_search'])>0){
			$where.= " AND property_type = ".$search['property_type_search'];
		}
		
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);	
	}
	public function getGroupCodeBYId($data){
		$db = $this->getAdapter();
		if($data['is_group']==1){
			$sql = "SELECT COUNT(client_id) AS number FROM `ln_client`
			      WHERE is_group =1 AND branch_id = ".$data['branch_id'] ;
			    $acc_no = $db->fetchOne($sql);
				$new_acc_no= (int)$acc_no+1;
				$acc_no= strlen((int)$acc_no+1);
				$pre ="G".$this->getPrefixCode($data['branch_id']);
				for($i = $acc_no;$i<3;$i++){
					$pre.='0';
				}
				return $pre.$new_acc_no;
		}else{
			$sql = " SELECT group_code FROM `ln_client`
			WHERE client_id = ".$data['group_id'] ;
			 $rs = $db->fetchOne($sql);
			if(empty($rs)){return ''; }else{
				return $rs;
			}
		}
		
	}
	function getPrefixCode($branch_id){
		$db  = $this->getAdapter();
		$sql = " SELECT prefix FROM `ln_branch` WHERE br_id = $branch_id  LIMIT 1";
		return $db->fetchOne($sql);
	}	
	public function getClientCode($branch_id){//for get client by branch
		$db = $this->getAdapter();
			$sql = "SELECT COUNT(client_id) AS number FROM `ln_client`
			WHERE 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre =$this->getPrefixCode($branch_id);
		for($i = $acc_no;$i<6;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
// 	public function adddoocumenttype($data){
		
// 		$db = $this->getAdapter();
// 		$document_type=array(
// 				'name_en'=>$data['clienttype_nameen'],
// 				'name_kh'=>$data['clienttype_namekh'],
// 				'displayby'=>1,
// 				'type'=>23,
// 				'status'=>1
				
// 		);
		
// 		$row= $this->insert($document_type);
// 		return $row;
// 	}
	public function addIndividaulClient($_data){
		
		$client_code = $this->getClientCode($_data['branch_id']);
			$_arr=array(
					'is_group'=>0,
					'group_code'=>'',
					'parent_id'=>0,
					'client_number'=>$client_code,
					'name_kh'	  => $_data['name_kh'],
					'name_en'	  => $_data['name_en'],
					'sex'	      => $_data['sex'],
					'sit_status'  => $_data['situ_status'],
					'dis_id'      => $_data['district'],
					'village_id'  => $_data['village'],
					'street'	  => $_data['street'],
					'house'	      => $_data['house'],
					'branch_id'  => $_data['branch_id'],
					'job'        =>$_data['job'],
					'phone'	      => $_data['phone'],
					'create_date' => date("Y-m-d"),
					'client_d_type'      => $_data['client_d_type'],
					'user_id'	  => $this->getUserId(),
					'dob'			=>$_data['dob_client'],	
					'pro_id'      => $_data['province'],
					'com_id'      => $_data['commune'],
					
			
			);
			
				$this->_name = "ln_client";
				$id =$this->insert($_arr);
				return array('id'=>$id,'client_code'=>$client_code);
	}
	public function CheckTitle($data){
		$db =$this->getAdapter();
		$sql = "SELECT  * FROM `ln_properties` AS p WHERE p.`land_address` = '".$data['land_address']."' AND p.`branch_id` = ".$data['branch_id'];
		return $db->fetchRow($sql);
	}
	public function getPropertyType(){
		$db = $this->getAdapter();
		$sql= "SELECT t.`id`,t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.`status`=1";
		return $db->fetchAll($sql);
	}
	public function getPropertyInfor($id){
		$db = $this->getAdapter();
		$sql="SELECT *,
		(SELECT project.project_name FROM `ln_project` AS project WHERE project.br_id=pro.`branch_id` LIMIT 1) AS project_name,
		(SELECT project.br_address FROM `ln_project` AS project WHERE project.br_id=pro.`branch_id` LIMIT 1) AS br_address,
		(SELECT p_type.type_nameen FROM `ln_properties_type` AS p_type WHERE p_type.id = pro.property_type) AS pro_type
		 FROM `ln_properties` AS  pro WHERE pro.`id`=".$id;
		return $db->fetchRow($sql);
	}
}

