<?php

class Project_Model_DbTable_DbLand extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllLandInfo($search = null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	
    	$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " WHERE ".$from_date." AND ".$to_date;
    	$sql = "SELECT id,
    	(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = ln_properties.branch_id LIMIT 1) AS branch_name,
    	land_address,street,
    	(SELECT t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.id = property_type limit 1) AS  pro_type,
    	price,width,height,land_size,hardtitle,
    	(SELECT name_kh FROM `ln_view` WHERE type = 28 AND key_code=is_lock LIMIT 1) sale_type,
    	create_date,
    	(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name";
    	
    	$sql.=$dbp->caseStatusShowImage("status");
    	$sql.=" FROM $this->_name ";
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
    	if(!empty($search['streetlist'])){
    		$where.= " AND street ='".$search['streetlist']."'";
    	}
    	if($search['branch_id']>-1){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	if(($search['property_type_search'])>0){
    		$where.= " AND property_type = ".$search['property_type_search'];
    	}
    	if($search['type_property_sale']>-1){
    		$where.= " AND is_lock = ".$search['type_property_sale'];
    	}
    	if(!empty($search['type_tob'])){
    		$where.= " AND `type`=".$search['type_tob'];
    	}
    	
    	$where.=$dbp->getAccessPermission("ln_properties.branch_id");
    	
//     	$order=" ORDER BY cast(land_address as unsigned) , id DESC ";
    	
//     	$order = " ORDER BY IF(land_address RLIKE '^[a-z]', 1, 2), land_address DESC , id DESC ";
    	$order= " ORDER BY LENGTH(land_address), land_address ASC  ";
    	return $db->fetchAll($sql.$where.$order);
    }
    public function addLandinfoAuto($_data){
    	try{
    			$increase = 0;
    			
		    		$dbStreet = new Project_Model_DbTable_DbStreet();
					$titleStreet = empty($_data['street'])?"":$_data['street'];
					$streetInfo = $dbStreet->getStreetByTitle($titleStreet);
					$street_code = empty($streetInfo['code'])?0:$streetInfo['code'];
					if (empty($streetInfo)){
						$streetId = $dbStreet->addStreet($_data);
						$street_code = empty($_data['streetCode'])?"":$_data['streetCode'];
					}
    			
    			for($i=$_data['land_address'];$i<=$_data['to_land_address'];$i++){
    				$db = new Application_Model_DbTable_DbGlobal();
    				$land_code = $db->getNewLandByBranch($_data['branch_id']);
    				$south = '';
    				$north='';
    				$west='';
    				$east='';
    				if($_data['south']!=-1 AND ($_data['south'] !='' AND ($_data['south_prefix']!='' OR $_data['postfix_south']!='' )))
    				{
    					$south = $_data['south_prefix'].($_data['south']+$_data['option_south']*$increase).$_data['postfix_south'];
    				}elseif($_data['south'] =='' AND $_data['postfix_south']==''){
    					$south = $_data['south_prefix'];
    				}
    				 
    				if($_data['north']!=-1 AND ($_data['north'] !='' AND ($_data['north_prefix']!='' OR $_data['postfix_north']!='' )))
    				{
    					$north = $_data['north_prefix'].($_data['north']+$_data['option_north']*$increase).$_data['postfix_north'];
    				}elseif($_data['north'] =='' AND $_data['postfix_north']==''){
    					$north = $_data['north_prefix'];
    				}
    				if($_data['west']!=-1 AND ($_data['west'] !='' AND ($_data['west_prefix']!='' OR $_data['postfix_west']!='')))
    				{
    					$west = $_data['west_prefix'].($_data['west']+$_data['option_west']*$increase).$_data['postfix_west'];
    				}elseif($_data['west'] =='' AND $_data['postfix_west']==''){
    					$west = $_data['west_prefix'];
    				}
    				if($_data['east']!=-1 AND ($_data['east'] !='' AND ($_data['east_prefix']!='' OR $_data['postfix_east']!='')))
    				{
    					$east = $_data['east_prefix'].($_data['east']+$_data['option_east']*$increase).$_data['postfix_east'];
    				}elseif($_data['east'] =='' AND $_data['postfix_east']==''){
    					$east = $_data['east_prefix'];
    				}
    				
    				$_arr=array(
    						'branch_id'	  => $_data['branch_id'],
    						'land_code'	  => $land_code,
    						'land_address'=> strtoupper($_data['land_address_prefix'].$i.$_data['postfix_land_address']),
    						'street'	  => $_data['street'],
    						'price'	      => $_data['house_price'],
    						'land_price'  => 0,
    						'house_price' => $_data['house_price'],
    						'land_size'	  => $_data['size'],
    						'width'       => $_data['width'],
    						'height'      => $_data['height'],
    						'is_lock'     => 0,
    						'status'	  => 1,
    						'user_id'	  => $this->getUserId(),
    						'property_type'=> $_data['property_type'],
    						'type_tob'	  => $_data['type_tob'],
    						'south'	      => $south,
    						'north'	      => $north,
    						'west'	      => $west,
    						'east'	      => $east,
    						'note'        => $_data['desc'],
    						'create_date'=>date('Y-m-d'),
    						'street_code'	      => $street_code,
    				);
    				 
    				$key = new Application_Model_DbTable_DbKeycode();
    				$setting=$key->getKeyCodeMiniInv(TRUE);
    				$show_house = $setting['showhouseinfo'];
    				if($show_house==1){
    					$_arr['land_width'] = $_data['width_land'];
    					$_arr['land_height'] = $_data['height_land'];
    					$_arr['full_size'] = $_data['full_size'];
    					$_arr['floor']	=$_data['floor'];
    					$_arr['living']	 = $_data['living'];
    					$_arr['bedroom']  = $_data['bedroom'];
    					$_arr['dinnerroom']= $_data['dinnerroom'];
    					$_arr['buidingyear']= $_data['buidingyear'];
    					$_arr['parkingspace'] = $_data['parkingspace'];
    				}
    				 
    				$arrayCheck = array(
    						'branch_id'=>$_data['branch_id'],
    						'land_address'=>strtoupper($_data['land_address_prefix'].$i.$_data['postfix_land_address']),
    						'street'=>$_data['street'],
    						'property_type'=>$_data['property_type'],
    				);
    				$check=0;
    				if (!empty($arrayCheck['land_address']) AND !empty($arrayCheck['street'])){
    					$ch_query=$this->CheckTitle($arrayCheck);
    					if (!empty($ch_query)){
    						$check=1;// already exits
    					}
    				}
    				if ($check==0){
    					$this->insert($_arr);
    				}
    				$increase++;
    			}
//     		}else{
//     			for($i=$_data['land_address'];$i>=$_data['to_land_address'];$i--){
//     				$db = new Application_Model_DbTable_DbGlobal();
//     				$land_code = $db->getNewLandByBranch($_data['branch_id']);
//     				$south = '';
//     				$north='';
//     				$west='';
//     				$east='';
//     				if($_data['south']!=-1 AND ($_data['south'] !='' AND ($_data['south_prefix']!='' OR $_data['postfix_south']!='' )))
//     				{
//     					$south = $_data['south_prefix'].($_data['south']-$increase).$_data['postfix_south'];
//     				}elseif($_data['south'] =='' AND $_data['postfix_south']==''){
//     					$south = $_data['south_prefix'];
//     				}
    				 
//     				if($_data['north']!=-1 AND ($_data['north'] !='' AND ($_data['north_prefix']!='' OR $_data['postfix_north']!='' )))
//     				{
//     					$north = $_data['north_prefix'].($_data['north']-$increase).$_data['postfix_north'];
//     				}elseif($_data['north'] =='' AND $_data['postfix_north']==''){
//     					$north = $_data['north_prefix'];
//     				}
//     				if($_data['west']!=-1 AND ($_data['west'] !='' AND ($_data['west_prefix']!='' OR $_data['postfix_west']!='')))
//     				{
//     					$west = $_data['west_prefix'].($_data['west']-$increase).$_data['postfix_west'];
//     				}elseif($_data['west'] =='' AND $_data['postfix_west']==''){
//     					$west = $_data['west_prefix'];
//     				}
//     				if($_data['east']!=-1 AND ($_data['east'] !='' AND ($_data['east_prefix']!='' OR $_data['postfix_east']!='')))
//     				{
//     					$east = $_data['east_prefix'].($_data['east']-$increase).$_data['postfix_east'];
//     				}elseif($_data['east'] =='' AND $_data['postfix_east']==''){
//     					$east = $_data['east_prefix'];
//     				}
    				 
//     				$_arr=array(
//     						'branch_id'	  => $_data['branch_id'],
//     						'land_code'	  => $land_code,
//     						'land_address'=> strtoupper($_data['land_address_prefix'].$i.$_data['postfix_land_address']),
//     						'street'	  => $_data['street'],
//     						'price'	      => $_data['house_price'],
//     						'land_price'  => 0,
//     						'house_price' => $_data['house_price'],
//     						'land_size'	  => $_data['size'],
//     						'width'       => $_data['width'],
//     						'height'      => $_data['height'],
//     						'is_lock'     => 0,
//     						'status'	  => 1,
//     						'user_id'	  => $this->getUserId(),
//     						'property_type'=> $_data['property_type'],
//     						'type_tob'	  => $_data['type_tob'],
//     						'south'	      => $south,
//     						'north'	      => $north,
//     						'west'	      => $west,
//     						'east'	      => $east,
//     						'note'        => $_data['desc'],
//     						'create_date'=>date('Y-m-d')
//     				);
    				 
//     				$key = new Application_Model_DbTable_DbKeycode();
//     				$setting=$key->getKeyCodeMiniInv(TRUE);
//     				$show_house = $setting['showhouseinfo'];
//     				if($show_house==1){
//     					$_arr['land_width'] = $_data['width_land'];
//     					$_arr['land_height'] = $_data['height_land'];
//     					$_arr['full_size'] = $_data['full_size'];
//     					$_arr['floor']	=$_data['floor'];
//     					$_arr['living']	 = $_data['living'];
//     					$_arr['bedroom']  = $_data['bedroom'];
//     					$_arr['dinnerroom']= $_data['dinnerroom'];
//     					$_arr['buidingyear']= $_data['buidingyear'];
//     					$_arr['parkingspace'] = $_data['parkingspace'];
//     				}
    				 
//     				$arrayCheck = array(
//     						'branch_id'=>$_data['branch_id'],
//     						'land_address'=>strtoupper($_data['land_address_prefix'].$i.$_data['postfix_land_address']),
//     						'street'=>$_data['street'],
//     				);
//     				$check=0;
//     				if (!empty($arrayCheck['land_address']) AND !empty($arrayCheck['street'])){
//     					$ch_query=$this->CheckTitle($arrayCheck);
//     					if (!empty($ch_query)){
//     						$check=1;// already exits
//     					}
//     				}
//     				if ($check==0){
//     					$this->insert($_arr);
//     				}
//     				$increase++;
//     			}
//     		}
    		
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
	public function addLandinfo($_data){
		try{
			
			$record = $this->recordhistory($_data);
			$activityold = $record['activityold'];
			$after_edit_info = $record['after_edit_info'];
			$label_new = 'Create New Property';
			
			$status = empty($_data['status'])?0:1;
			$old_status = $status;
			if(!empty($_data['id'])){
				$oldCode = $this->getClientById($_data['id']);
				$land_code = $oldCode['land_code'];
				$old_status = $oldCode['status'];
			}else{
				$db = new Application_Model_DbTable_DbGlobal();
				$land_code = $db->getNewLandByBranch($_data['branch_id']);
			}
			
			$dbStreet = new Project_Model_DbTable_DbStreet();
			$titleStreet = empty($_data['street'])?"":$_data['street'];
			$streetInfo = $dbStreet->getStreetByTitle($titleStreet);
			$street_code = empty($streetInfo['code'])?0:$streetInfo['code'];
			if (empty($streetInfo)){
				$streetId = $dbStreet->addStreet($_data);
				$street_code = empty($_data['streetCode'])?"":$_data['streetCode'];
			}
			
		    $_arr=array(
		    	'branch_id'	  => $_data['branch_id'],
				'land_code'	  => $land_code,
				'land_address'=> strtoupper($_data['land_address']),
		    	'street'	  => $_data['street'],
				'price'	      => $_data['price'],
		    	'land_price'  => $_data['land_price'],
		    	'house_price' => $_data['house_price'],
				'land_size'	  => $_data['size'],
				'width'       => $_data['width'],
				'height'      => $_data['height'],
// 	    		'is_lock'     => $_data['buy_status'],
				'hardtitle'   => $_data['hardtitle'],
				'note'        => $_data['desc'],
				'user_id'	  => $this->getUserId(),
		    	'property_type'	  => $_data['property_type'],
		    	'type_tob'	  => $_data['type_tob'],
	    		'south'	  => $_data['south'],
	    		'north'	  => $_data['north'],
	    		'west'	  => $_data['west'],
	    		'east'	  => $_data['east'],
		    		
		    	'street_code'	  => $street_code,
			);
	    $key = new Application_Model_DbTable_DbKeycode();
	    $setting=$key->getKeyCodeMiniInv(TRUE);
	    $show_house = $setting['showhouseinfo'];
	    if($show_house==1){
	    	$_arr['land_width'] = $_data['width_land'];
	    	$_arr['land_height'] = $_data['height_land'];
	    	$_arr['full_size'] = $_data['full_size'];
	    	$_arr['floor']	=$_data['floor'];
	    	$_arr['living']	 = $_data['living'];
	    	$_arr['bedroom']  = $_data['bedroom'];
	    	$_arr['dinnerroom']= $_data['dinnerroom'];
	    	$_arr['buidingyear']= $_data['buidingyear'];
	    	$_arr['parkingspace'] = $_data['parkingspace'];
	    }
		    
		if(!empty($_data['id'])){
			
			if($old_status==-2 || $old_status==-1){
				//$_arr['status']=-2;
			}else{
				$_arr['status']= $status;
			}
			
			$_arr['is_lock']=$_data['buy_status'];
			$where = 'id = '.$_data['id'];
			$this->update($_arr, $where);
			$id = $_data['id'];
			$label_new = 'Edit Property '.$land_code; 
		}else{
			$_arr['is_lock']=0;
			$_arr['status']=1;
			$_arr['create_date']=date('Y-m-d');
			$id= $this->insert($_arr);
		}
		
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$_datas = array('description'=>$label_new,'activityold'=>$activityold,'after_edit_info'=>$after_edit_info);
			$dbgb->addActivityUser($_datas);
			return $id;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function recordhistory($_data){
		$arr=array();
		$stringold="";
		$string="";
		$db_pro = new Project_Model_DbTable_DbProject();
		$db_type = new Project_Model_DbTable_DbProperyType();
		
		if (!empty($_data['id'])){
	
			$row=$this->getClientById($_data['id']);
			
			$project = $db_pro->getBranchById($row['branch_id']);
			$typename="";
			if (!empty($row['property_type'])){
				$type = $db_type->getPropertyTypeById($row['property_type']);
				$typename = $type['type_namekh'];
			}
			$stringold="Project : ID:".$_data['branch_id']."-".$project['project_name']."<br />";
			$stringold.="Property Code : ".strtoupper($row['land_address'])."<br />";
			$stringold.="Street : ".$row['street']."<br />";
			$stringold.="Price : ".$row['price']."<br />";
			$stringold.="Land price : ".$row['land_price']."<br />";
			$stringold.="House price : ".$row['house_price']."<br />";
			$stringold.="Property type : id:".$row['property_type']."-".$typename."<br />";
			
	
			$project = $db_pro->getBranchById($_data['branch_id']);
			$typename="";
			if (!empty($_data['property_type'])){
				$type = $db_type->getPropertyTypeById($_data['property_type']);
				$typename = $type['type_namekh'];
			}
			$string="Project : ID:".$_data['branch_id']."-".$project['project_name']."<br />";
			$string.="Property Code : ".strtoupper($_data['land_address'])."<br />";
			$string.="Street : ".$_data['street']."<br />";
			$string.="Price : ".$_data['price']."<br />";
			$string.="Land price : ".$_data['land_price']."<br />";
			$string.="House price : ".$_data['house_price']."<br />";
			$string.="Property type : id:".$_data['property_type']."-".$typename."<br />";
	
		}else{
			
			
			$string="";
			$project = $db_pro->getBranchById($_data['branch_id']);
			$typename="";
			if (!empty($_data['property_type'])){
				$type = $db_type->getPropertyTypeById($_data['property_type']);
				$typename = $type['type_namekh'];
			}
			$stringold="Project : ID:".$_data['branch_id']."-".$project['project_name']."<br />";
			$stringold.="Property Code : ".strtoupper($_data['land_address'])."<br />";
			$stringold.="Street : ".$_data['street']."<br />";
			$stringold.="Price : ".$_data['price']."<br />";
			$stringold.="Land price : ".$_data['land_price']."<br />";
			$stringold.="House price : ".$_data['house_price']."<br />";
			$stringold.="Property type : id:".$_data['property_type']."-".$typename."<br />";
		}
		$arr['activityold']=$stringold;
		$arr['after_edit_info']=$string;
		return $arr;
	}
	
	public function getClientById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE id = ".$db->quote($id);
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
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
		$sql = "SELECT  * FROM `ln_properties` AS p WHERE p.`land_address` = '".$data['land_address']."' AND p.`branch_id` = ".$data['branch_id']." AND p.`street` = '".$data['street']."'" ;
		if(!empty($data['property_type'])){
			$sql.=" AND p.`property_type` ='".$data['property_type']."'";
		}
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
		(SELECT project.logo FROM `ln_project` AS project WHERE project.br_id=pro.`branch_id` LIMIT 1) AS project_logo,
		(SELECT project.project_name FROM `ln_project` AS project WHERE project.br_id=pro.`branch_id` LIMIT 1) AS project_name,
		(SELECT project.br_address FROM `ln_project` AS project WHERE project.br_id=pro.`branch_id` LIMIT 1) AS br_address,
		(SELECT p_type.type_nameen FROM `ln_properties_type` AS p_type WHERE p_type.id = pro.property_type) AS pro_type
		 FROM `ln_properties` AS  pro WHERE pro.`id`=".$id;
		return $db->fetchRow($sql);
	}
	public function getCheckPropertyInSale($id){
		$db = $this->getAdapter();
		$sql="SELECT s.id FROM `ln_sale` AS s WHERE s.house_id=$id ORDER BY s.id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}
	function deleteLand($land_id){
		
		$info = $this->getPropertyInfor($land_id);
			
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$_datas = array('description'=>"Delete Property ".$info['land_address']." id = ($land_id) Street ".$info['street']);
		$dbgb->addActivityUser($_datas);
		
		$where ="id=".$land_id;
		$this->_name="ln_properties";
		$this->delete($where);
		
		return $land_id;
	}
	
	function getAllTypeTob(){
		$db = $this->getAdapter();
		$sql = " SELECT DISTINCT type_tob as name,type_tob as id FROM `ln_properties` WHERE type_tob!='' ORDER BY type_tob ASC ";
		return $db->fetchAll($sql);
	}
	
	public function countAmountProperty($data){
		$db =$this->getAdapter();
		$sql = "SELECT  count(p.id) FROM `ln_properties` AS p WHERE p.`branch_id` = ".$data['branch_id'];
		return $db->fetchOne($sql);
	}
}