<?php

class Group_Model_DbTable_DbClient extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_client';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	public function addClient($_data){
		try{
			if(!empty($_data['id'])){
				$oldClient_Code = $this->getClientById($_data['id']);
				$client_code = $oldClient_Code['client_number'];
			}else{
				$db = new Application_Model_DbTable_DbGlobal();
// 				$client_code = $db->getNewClientIdByBranch($_data['branch_id']);
				$client_code = $db->getNewClientIdByBranch();
			}
			
			$record = $this->recordhistory($_data);
			$activityold = $record['activityold'];
			$after_edit_info = $record['after_edit_info'];
			$labelDescribtion = 'Add New Customer';
			
		    $_arr=array(
				'client_number'=> $client_code,//$_data['client_no'],
				'name_kh'	  	=> $_data['name_kh'],
				//'name_en'	  	=> $_data['name_en'],
				'sex'	      	=> $_data['sex'],
				'dob'			=>$_data['dob_client'],
				'pro_id'     	=> $_data['province'],
				'dis_id'      	=> $_data['district'],
				'com_id'     	=> $_data['commune'],
				'village_id'  	=> $_data['village'],
				'street'	  	=> $_data['street'],
				'house'	      	=> $_data['house'],
				'nation_id'		=>$_data['national_id'],
		    	'nationality'	=>$_data['nationality'],
		    	'client_issuedateid' => $_data['client_issuedateid'],
		    	'join_issuedateid' => $_data['join_issuedateid'],
				'phone'	      	=> $_data['phone'],
		    	'email'	      	=> $_data['email'],
				'create_date' 	=> date("Y-m-d"), 
				'remark'	  	=> $_data['desc'],
				'client_d_type' => $_data['client_d_type'],
				'user_id'	  	=> $this->getUserId(),
		    	'hname_kh'      => $_data['hname_kh'],
		    	'dob_buywith'	=> $_data['dob_buywith'],
		    	'p_nationality' => $_data['p_nationality'],
		    	'ghouse'      	=> $_data['ghouse'],
		    	'ksex'      	=> $_data['ksex'],
		    	'adistrict'     => $_data['adistrict'],
		    	'lphone'      	=> $_data['lphone'],
		    	'cprovince'     => $_data['cprovince'],
		    	'dcommune'      => $_data['dcommune'],
		    	'qvillage'      => $_data['qvillage'],
		    	'dstreet'      	=> $_data['dstreet'],
		    	'rid_no'      	=> $_data['rid_no'],
		    	'arid_no'      	=> $_data['arid_no'],
		    	'edesc'      	=> $_data['edesc'],
// 		    	'branch_id'     => $_data['branch_id'],
		    	'joint_doc_type'=> $_data['join_d_type'],
		    	'refe_nation_id'=> $_data['reference_national_id'],
		    	'join_type'     => $_data['join_type'],		    		
			); 
		    
		    $part= PUBLIC_PATH.'/images/';
		    $photo_name = $_FILES['photo']['name'];
		    if (!empty($photo_name)){
		    	$tem =explode(".", $photo_name);
		    	$image_name_stu = "profile_".date("Y").date("m").date("d").time().".".end($tem);
		    	$tmp = $_FILES['photo']['tmp_name'];
		    	if(move_uploaded_file($tmp, $part.$image_name_stu)){
		    		move_uploaded_file($tmp, $part.$image_name_stu);
		    		$photo = $image_name_stu;
		    		$_arr['photo_name']=$photo;
		    	}
		    }		    
			if(!empty($_data['id'])){
				$customer_id =  $_data['id'];
				$_arr['status'] = $_data['status'];
				$where = 'client_id = '.$customer_id;
				$this->update($_arr, $where);

				$labelDescribtion = 'Edit Customer '.$client_code;
			}else{
				$_arr['status'] = 1;
				$customer_id = $this->insert($_arr);
			}
		
			$part= PUBLIC_PATH.'/images/document/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
		 
			if(!empty($_data['id'])){//only edit (delete only)
				$this->_name = "ln_client_document";
				$where1 =" client_id=".$_data['id'];
				$this->delete($where1);
			}
			
			if (!empty($_data['identity'])){
				$identity = $_data['identity'];
				$ids = explode(',', $identity);
				$image_name="";
				$photo="";
				$this->_name='ln_client_document';
				$detailId="";
				foreach ($ids as $i){
					$name = $_FILES['attachment'.$i]['name'];
					if (!empty($name)){
						$ss = 	explode(".", $name);
						$file_new = "document_".date("Y").date("m").date("d").time().$i.".".end($ss);
						$tmp = $_FILES['attachment'.$i]['tmp_name'];
						if(move_uploaded_file($tmp, $part.$file_new)){
							$photo_new = $file_new;
							$arr_new = array(
								'client_id'=>$customer_id,
								'document_name'=>$photo_new,
							);
							$this->insert($arr_new);
						}
					}else{
						$photo = $_data['old_file'.$i];
						$arr = array(
							'client_id'=>$customer_id,
							'document_name'=>$photo,
						);
						$this->insert($arr);
					}
				}
			}
			
			
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$_datas = array('description'=>$labelDescribtion,'activityold'=>$activityold,'after_edit_info'=>$after_edit_info);
			$dbgb->addActivityUser($_datas);
			
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function recordhistory($_data){
		$arr=array();
		$stringold="";
		$string="";
		if (!empty($_data['id'])){
	
			$row=$this->getClientById($_data['id']);
			$sex = "M";
			if ($row['sex']==2){ $sex = "F";}
			$stringold="Customer Name : ".$row['name_kh']."<br />";
			$stringold.="sex : ".$sex."<br />";
			$stringold.="phone : ".$row['phone']."<br />";
			$stringold.="Email: ".$row['email']."<br />";
			$stringold.="Nationality : ".$row['nationality']."<br />";
			
			$sex = "M";
			if ($row['ksex']==2){
				$sex = "F";
			}
			$stringold.="With Name : ".$row['hname_kh']."<br />";
			$stringold.="sex : ".$sex."<br />";
			$stringold.="phone : ".$row['lphone']."<br />";
			$stringold.="Email: ".$$row['email']."<br />";
			$stringold.="Nationality : ".$row['p_nationality']."<br />";
	
			
			$sex = "M";
			if ($_data['sex']==2){
				$sex = "F";
			}
			$string="Customer Name : ".$_data['name_kh']."<br />";
			$string.="sex : ".$sex."<br />";
			$string.="phone : ".$_data['phone']."<br />";
			$string.="Email: ".$_data['email']."<br />";
			$string.="Nationality : ".$_data['nationality']."<br />";
				
			$sex = "M";
			if ($_data['ksex']==2){
				$sex = "F";
			}
			$string.="With Name : ".$_data['hname_kh']."<br />";
			$string.="sex : ".$sex."<br />";
			$string.="phone : ".$_data['lphone']."<br />";
			$string.="Email: ".$_data['email']."<br />";
			$string.="Nationality : ".$_data['p_nationality']."<br />";
			
	
	
		}else{
			$string="";
			$sex = "M";
			if ($_data['sex']==2){ $sex = "F";}
			$stringold="Customer Name : ".$_data['name_kh']."<br />";
			$stringold.="sex : ".$sex."<br />";
			$stringold.="phone : ".$_data['phone']."<br />";
			$stringold.="Email: ".$_data['email']."<br />";
			$stringold.="Nationality : ".$_data['nationality']."<br />";
			
			$sex = "M";
			if ($_data['ksex']==2){
				$sex = "F";
			}
			$stringold.="With Name : ".$_data['hname_kh']."<br />";
			$stringold.="sex : ".$sex."<br />";
			$stringold.="phone : ".$_data['lphone']."<br />";
			$stringold.="Email: ".$_data['email']."<br />";
			$stringold.="Nationality : ".$_data['p_nationality']."<br />";
			
		}
		
		$arr['activityold']=$stringold;
		$arr['after_edit_info']=$string;
		return $arr;
	}
	public function getClientById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE client_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
			$row=$db->fetchRow($sql);
			return $row;
	}
	public function getDocumentClientById($client_id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM ln_client_document WHERE client_id = ".$client_id;
		return $db->fetchAll($sql);
	}
	public function getClientDetailInfo($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.*,
				(SELECT  v.name_kh FROM `ln_view` AS v WHERE v.type=11 AND v.key_code =c.`sex` LIMIT 1) AS sex,
				(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type=23 AND v.key_code = c.`client_d_type`) AS doc_name,
				(SELECT commune_namekh FROM `ln_commune` WHERE com_id = c.com_id   LIMIT 1) AS commune_name
				,(SELECT district_namekh FROM `ln_district` AS ds WHERE dis_id = c.dis_id  LIMIT 1) AS district_name
				,(SELECT province_kh_name FROM `ln_province` WHERE province_id= c.pro_id  LIMIT 1) AS province_en_name
				,(SELECT village_namekh FROM `ln_village` WHERE vill_id = c.village_id  LIMIT 1) AS village_name , 
				(SELECT project_name FROM `ln_project` WHERE br_id =c.branch_id LIMIT 1) AS project_name ,
				(SELECT  v.name_kh FROM `ln_view` AS v WHERE v.type=11 AND v.key_code =c.`ksex` LIMIT 1) AS ksex,
				 (SELECT commune_namekh FROM `ln_commune` WHERE com_id = c.dcommune   LIMIT 1) AS p_commune_name
				,(SELECT district_namekh FROM `ln_district` AS ds WHERE dis_id = c.adistrict  LIMIT 1) AS p_district_name
				,(SELECT province_kh_name FROM `ln_province` WHERE province_id= c.cprovince  LIMIT 1) AS p_province_en_name
				,(SELECT village_namekh FROM `ln_village` WHERE vill_id = c.qvillage  LIMIT 1) AS p_village_name ,
				(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type=23 AND v.key_code = c.`joint_doc_type`) AS join_doc_name
		 FROM 
		`ln_client` AS c WHERE client_id =  ".$db->quote($id);
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
	function getAllClients($search = null){		
		try{	
			$db = $this->getAdapter();
			$from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE  ".$from_date." AND ".$to_date;		
			$sql = "
			SELECT client_id,
			client_number,
			name_kh,
			(SELECT name_en FROM `ln_view` WHERE type =11 AND sex=key_code LIMIT 1) AS sex
			,phone,house,street,
				(SELECT village_namekh FROM `ln_village` WHERE vill_id= village_id LIMIT 1) AS village_name,
			    create_date,
			    (SELECT first_name FROM rms_users WHERE id=user_id LIMIT 1 ) AS user_name ";
			
			$dbp = new Application_Model_DbTable_DbGlobal();
			$sql.=$dbp->caseStatusShowImage("status");
			$sql.=" FROM $this->_name ";
			
			if(!empty($search['adv_search'])){
				$s_where = array();
				$s_search = addslashes(trim($search['adv_search']));
				$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
				$s_where[]=" REPLACE(client_number,' ','') LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(client_number,' ','') LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(name_kh,' ','') LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(hname_kh,' ','') LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(phone,' ','') LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(lphone,' ','') LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(tel,' ','') LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(street,' ','') LIKE '%{$s_search}%'";
				
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['status']>-1){
				$where.= " AND status = ".$search['status'];
			}
// 			if($search['branch_id']>-1){
// 				$where.= " AND branch_id = ".$search['branch_id'];
// 			}
			if($search['province_id']>0){
				$where.=" AND pro_id= ".$search['province_id'];
			}
			if(!empty($search['district_id'])){
				$where.=" AND dis_id= ".$search['district_id'];
			}
			if($search['customer_id']>0){
				$where.=" AND client_id= ".$search['customer_id'];
			}
			if(!empty($search['comm_id'])){
				$where.=" AND com_id= ".$search['comm_id'];
			}
			if(!empty($search['village'])){
				$where.=" AND village_id= ".$search['village'];
			}
			$order=" ORDER BY client_id DESC ";
			return $db->fetchAll($sql.$where.$order);
			
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
	}
	public function getGroupCodeBYId($data){
		$db = $this->getAdapter();
		$sql = " SELECT *,
				(SELECT t.type_nameen FROM `ln_properties_type` as t WHERE t.id=property_type) As property_type
				FROM `ln_properties` 
			WHERE id = ".$data['land_id']." LIMIT 1" ;
			 $rs = $db->fetchRow($sql);
			 $rs['house_type']=ltrim(strstr($rs['property_type'], '('), '.');
			 
			if(empty($rs)){return ''; }else{
				return $rs;
			}
	}
	function getPrefixCode($branch_id){
		$db  = $this->getAdapter();
		$sql = " SELECT prefix FROM `ln_branch` WHERE br_id = $branch_id  LIMIT 1";
		return $db->fetchOne($sql);
	}	
	public function getClientCode(){//for get client by branch
		$db = $this->getAdapter();
			$sql = "SELECT COUNT(client_id) AS number FROM `ln_client`
			WHERE 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre ="";
		for($i = $acc_no;$i<6;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
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
	function addViewType($data){
		try{
			$db = $this->getAdapter();
			$data['type'] = 23;
			$key_code = $this->getLastKeycodeByType($data['type']);
			$arr = array(
				'name_kh'		=>$data['doc_name'],
				'status'		=>1,
				'displayby'		=>1,
				'key_code'		=>$key_code,
				'type'			=>$data['type'],
			);
			$this->_name = "ln_view";
			return $this->insert($arr);
		}catch (Exception $e){
			echo '<script>alert('."$e".');</script>';
		}
	}
	function getLastKeycodeByType($type){
		$db =$this->getAdapter();
		$sql = "SELECT key_code FROM `ln_view` WHERE type=$type ORDER BY key_code DESC LIMIT 1 ";
		$number = $db->fetchOne($sql);
		return $number+1;
	}
	
	public function CheckTitle($data){
		$db =$this->getAdapter();
		$sql = "SELECT client_id FROM `ln_client` WHERE name_kh = '".$data['name_kh']."' LIMIT 1 ";
		return $db->fetchRow($sql);
	}
}