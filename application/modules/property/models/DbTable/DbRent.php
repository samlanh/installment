<?php

class Property_Model_DbTable_DbRent extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_sale_property';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    
    }
    function getRent($search=null){
    	$db = $this->getAdapter();
    	$sql = 'SELECT 
			r.`id`,r.`rent_no`,
			(SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = r.`client_id` LIMIT 1) AS customer_name,
			(SELECT l.title FROM `ln_buy_land` AS l WHERE l.id = r.`land_id` LIMIT 1) AS land_name,
			(SELECT l.title_kh FROM `ln_land_blog` AS l WHERE l.id = r.`land_blog` LIMIT 1) AS land_blog,
			(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = (SELECT l.land_type FROM `ln_buy_land` AS l WHERE l.id = r.`land_id` LIMIT 1) LIMIT 1)AS land_type,
			r.`rent_duration`,r.`date_start`,r.`date_end`,r.`price_permont`,r.`total_price`,r.`pay_first`,r.`balance`,r.`date_rent`,r.`status`,r.`is_complete`
			FROM `ln_rent` AS r  WHERE 1';
    	$where='';
    	$from_date =(empty($search['start_date']))? '1': "r.`date_rent` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "r.`date_rent` <= '".$search['end_date']." 23:59:59'";
    	if (empty($search['show_all'])){
    		$where = " AND ".$from_date." AND ".$to_date;
//     		if($search['status']>-1){
//     			$where.= " AND bl.`status` = ".$search['status'];
//     		}
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] = " r.`rent_no` LIKE '%{$s_search}%'";
    			$s_where[] = " r.`price_permont` LIKE '%{$s_search}%'";
    			$s_where[] = " r.`total_price` LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT l.title FROM `ln_buy_land` AS l WHERE l.id = r.`land_id` LIMIT 1) LIKE '%{$s_search}%'";
    			$s_where[] = " (SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = r.`client_id` LIMIT 1) LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
//     		if($search['property_type_search']>0){
//     			 $where.= " AND (SELECT l.land_type FROM `ln_buy_land` AS l WHERE l.id = r.`land_id` LIMIT 1) = ".$search['property_type_search'];
//     		}
    		if($search['land_blog']>0){
    			$where.= " AND r.`land_blog` = ".$search['land_blog'];
    		}
    		if($search['status']>-1){
    			$where.= " AND r.`status` = ".$search['status'];
    		}
    	}
    	$order=' ORDER BY r.id DESC';
    	return $db->fetchAll($sql.$where.$order);
    }
    function addRent($data){
    	try{
    		
//     		$muliti =1;
//     		if (empty($data['land'])){
//     			$muliti = 2;
//     		}
    		$db = new Application_Model_DbTable_DbGlobal();
    		$rent_no = $db->getRentPropertyNo();
    		$arr = array(
    				'rent_no'=>$rent_no,
    				'land_blog'=>$data['land_blog'],
//     				'land_id'=>$data['land'],
    				'client_id'=>$data['customer'],
    				'date_rent'=>$data['date_rent'],
    				'rent_duration'=>$data['period'],
    				'date_start'=>$data['start_date'],
    				'date_end'=>$data['end_date'],
    				'price_permont'=>$data['rent_price_permonth'],
    				'total_price'=>$data['total_rent'],
    				'pay_first'=>$data['deposit'],
    				'balance'=>$data['balance'],

    				'north'=>$data['north'],
    				'south'=>$data['south'],
    				'west'=>$data['west'],
    				'east'=>$data['east'],
    				'width'=>$data['width'],
    				'height'=>$data['height'],
    				'size'=>$data['size'],
    				
    				'note'=>$data['note'],
    				'seller'=>$data['seller'],
    				
    				'status'=>1,
    				'user_id'=>$this->getUserId(),
    				'create_date'=>date("Y-m-d"),
//     				'is_multi_saleproperty'=>$muliti,// 2 multi land, 1 sigle land
    				);
    		$this->_name="ln_rent";
    		$id = $this->insert($arr);
    		
    		$identity = explode(',', $data['identity']);
    		foreach ($identity as $key => $i){
    			$this->_name="ln_buy_land";
    			$where = "id =".$identity[$key];
    			$arr = array(
    					"is_lock"=>2,
    			);
    			$this->update($arr, $where);
    		
    			$arr__detail = array(
    					"sale_pro_id"=>$id,
    					"house_id"=>$identity[$key],
    			);
    			$this->_name="ln_rent_detail";
    			$this->insert($arr__detail);
    		}
    		
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    } 
    function updateRent($data){
    	try{
//     		$muliti =1;
//     		if (empty($data['land'])){
//     			$muliti = 2;
//     		}
    		$rowdetail = $this->getRentPropertyDetail($data['id']);
    		if (!empty($rowdetail))foreach($rowdetail as $rs){
    			$this->_name="ln_buy_land";
    			$where = "id =".$rs['house_id'];
    			$arr = array(
    					"is_lock"=>0,
    			);
    			$this->update($arr, $where);
    		}
    		$arr = array(
    				'rent_no'=>$data['rent_code'],
    				'land_blog'=>$data['land_blog'],
//     				'land_id'=>$data['land'],
    				'client_id'=>$data['customer'],
    				'date_rent'=>$data['date_rent'],
    				'rent_duration'=>$data['period'],
    				'date_start'=>$data['start_date'],
    				'date_end'=>$data['end_date'],
    				'price_permont'=>$data['rent_price_permonth'],
    				'total_price'=>$data['total_rent'],
    				'pay_first'=>$data['deposit'],
    				'balance'=>$data['balance'],

    				'north'=>$data['north'],
    				'south'=>$data['south'],
    				'west'=>$data['west'],
    				'east'=>$data['east'],
    				'width'=>$data['width'],
    				'height'=>$data['height'],
    				'size'=>$data['size'],
    				
    				'seller'=>$data['seller'],
    				'note'=>$data['note'],
    				'status'=>$data['status'],
    				'user_id'=>$this->getUserId(),
//     				'is_multi_saleproperty'=>$muliti,// 2 multi land, 1 sigle land
    				'is_complete'=>$data['is_complete'],
    				);
    		$this->_name="ln_rent";
    		$where = 'id = '.$data['id'];
    		$this->update($arr, $where);
    		
    		$this->_name="ln_rent_detail";
    		$where="sale_pro_id = ".$data['id'];
    		$this->delete($where);
    		
    		$identity = explode(',', $data['identity']);
    		foreach ($identity as $key => $i){
    			if ($data['status']==1){
	    			$this->_name="ln_buy_land";
	    			$where = "id =".$identity[$key];
	    			$arr = array(
	    					"is_lock"=>2,
	    			);
	    			$this->update($arr, $where);
    			}
    		
    			$arr__detail = array(
    					"sale_pro_id"=>$data['id'],
    					"house_id"=>$identity[$key],
    			);
    			$this->_name="ln_rent_detail";
    			$this->insert($arr__detail);
    		}
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getRentById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `ln_rent` AS s WHERE s.`id`=".$id;
    	return $db->fetchRow($sql);
    }
    function deleteBuyLand($id){
    	$db = $this->getAdapter();
    	$arr = array( 'status'=> -1);
    	$where = ' id = '.$id;
    	$this->_name = "ln_buy_land";
    	$this->update($arr, $where);
    }
    public function addStaff($data){
    	 
    	$db = $this->getAdapter();
    	 
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$staff_id = $_db->getStaffNumberByBranch($data['branch_id_pop']);  // get new staff code by branch
    	 
    	$this->_name="ln_staff";
    	$array = array(
    			'branch_id'		=>$data['branch_id_pop'],
    			//     			'position_id'	=>1, // 1 => sale agent
    			'co_code'		=>$staff_id,
    			'co_khname'		=>$data['kh_name'],
    			'sex'			=>$data['sex'],
    			'tel'			=>$data['phone'],
    			'note'			=>$data['note_pop'],
    			'create_date'	=>date('Y-m-d'),
    			 
    	);
    	return $this->insert($array);
    }
    function getLandInfo($land_id,$type){
    	if ($type==2){
    		$db = $this->getAdapter();
    		$sql='SELECT *,
    		(SELECT l.title_kh FROM `ln_land_blog` AS l WHERE l.id = b.`land_blog` LIMIT 1) AS land_blog,
    		(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id= b.`land_type` LIMIT 1 ) AS property_type
    		FROM `ln_buy_land` AS b WHERE b.`id` ='.$land_id;
    		return $db->fetchRow($sql);
    	}else{
    		$db = $this->getAdapter();
    		$sql='SELECT *,
    		(SELECT l.title_kh FROM `ln_land_blog` AS l WHERE l.id = b.`land_blog` LIMIT 1) AS land_blog,
    		(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id= b.`land_type` LIMIT 1 ) AS property_type
    		FROM `ln_buy_land` AS b WHERE b.`status`=1 AND b.`is_lock`=0 AND b.`land_blog` ='.$land_id;
    		$row = $db->fetchAll($sql);
    		$price=0; $size=0; $blog='';
    		foreach($row as $rs){
    			$price = $price+$rs['price'];
    			$size = $size+$rs['size'];
    			$blog= $rs['land_blog'];
    		}
    		return array(
    				'price'=>$price,
    				'size'=>$size,
    				'land_blog'=>$blog,
    		);
    	}
    }
    function getRentPropertyDetail($sale_id){
    	$db = $this->getAdapter();
    	$sql= 'SELECT *,
    	(SELECT l.title FROM `ln_buy_land` AS l WHERE l.id = pd.`house_id` limit 1) AS land_name,
    	(SELECT l.sale_name FROM `ln_buy_land` AS l WHERE l.id = pd.`house_id` LIMIT 1) AS sale_name_before,
    	(SELECT l.sale_relevent_name FROM `ln_buy_land` AS l WHERE l.id = pd.`house_id` LIMIT 1) AS sale_relevent_name,
    	(SELECT l.size FROM `ln_buy_land` AS l WHERE l.id = pd.`house_id` LIMIT 1) AS size,
    	(SELECT l.price FROM `ln_buy_land` AS l WHERE l.id = pd.`house_id` LIMIT 1) AS price,
    	(SELECT b.title_kh FROM `ln_land_blog` AS b WHERE b.id = (SELECT l.land_blog FROM `ln_buy_land` AS l WHERE l.id = pd.`house_id` LIMIT 1)LIMIT 1) AS land_blog
    	FROM `ln_rent_detail` AS pd WHERE pd.`sale_pro_id` = '.$sale_id;
    	return $db->fetchAll($sql);
    }
    
    function getLandBlog($seller){
    	$db = $this->getAdapter();
    	$sql='
    	SELECT DISTINCT(bl.`id`) as id,bl.`title_kh` AS `name` FROM `ln_land_blog` AS bl ,
    	`ln_buy_land` AS bu
    	WHERE bu.`land_blog` = bl.`id` AND bu.`buyer_name` = "'.$seller.'"
    	';
    	$row = $db->fetchAll($sql);
    	array_unshift($row, array('id'=>'','name' => "ជ្រើសរើសប្លុកទីតាំងដី"));
    	return $row;
    }
    function getAllLand($land_blog){
    	$db = $this->getAdapter();
    	$sql='SELECT * FROM `ln_buy_land` AS b WHERE b.`status` = 1 AND b.`is_lock` = 0 AND b.`land_blog`='.$land_blog;
    	$row = $db->fetchAll($sql);
    	$row_data='';
    	$i=0;
    	$size=0;
    	$price=0;
    	$identity='';
    	if (!empty($row))foreach( $row AS $rs){
    		$i++;
    		$row_data.='<tr id=tr_'.$i.' style="background-color:none">';
    		$row_data.='<td align="center">&nbsp;<input type="checkbox" class="checkbox" id="mfdid_'.$rs['id'].'" value="'.$rs['id'].'" OnChange="calculateTotal('.$rs['id'].')" checked="checked" name="selector[]"/></td>';
    		$row_data.='<td>'.$rs['sale_name'].' , '.$rs['sale_relevent_name'].'<input type="hidden" name="buy_land_id'.$rs['id'].'" id="buy_land_id'.$rs['id'].'" value="'.$rs['id'].'" /></td></td>';
    		$row_data.='<td>'.$rs['size'].' ម៉ែត្រក្រឡា <input type="hidden" name="size'.$rs['id'].'" id="size'.$rs['id'].'" value="'.$rs['size'].'" /></td>';
    		$row_data.='<td>'.date("d-M-Y",strtotime($rs['buy_date'])).'<input type="hidden" name="price'.$rs['id'].'" id="price'.$rs['id'].'" value="'.$rs['price'].'" /></td>';
    		$row_data.='</tr>';
    		$size = $size+$rs['size'];
    		$price = $price+$rs['price'];
    		if (!empty($identity)){
    			$identity= $identity.",".$rs['id'];
    		}else{ $identity=$rs['id'];
    		}
    	}
    	$result = array('table'=>$row_data,
    			'price'=>$price,
    			'size'=>$size,
    			'identity'=>$identity);
    	return $result;
    }
    function getAllLandForEdit($land_blog,$sale_id){
    	$rowdetail = $this->getRentPropertyDetail($sale_id);
    	$land_id = "";
    	$array = array();
    	$newarray = array();
    	if (!empty($rowdetail)){
    		foreach($rowdetail as $rs){
    			$newarray = array($rs['house_id']=>$rs['house_id']);
    			$array = array_merge($array,$newarray);
    			if (!empty($land_id)){
    				$land_id = $land_id." OR b.`id`=".$rs['house_id'];
    			}else{ $land_id = " OR b.`id`=".$rs['house_id'];
    			}
    		}
    	}else{
    		$rs = $this->getSaleById($sale_id);
    		if (!empty($rs['house_id'])){
    			$land_id = " OR b.`id`=".$rs['house_id'];
    		}
    	}
    	$db = $this->getAdapter();
    	$sql='SELECT * FROM `ln_buy_land` AS b WHERE b.`status` = 1 ';
    	$where='';
    	$where.=" AND ( b.`is_lock` = 0 ".$land_id.") AND b.`land_blog`=".$land_blog;
    	$row = $db->fetchAll($sql.$where);
    	$row_data='';
    	$i=0;
    	$price=0;
    	$identity='';
    	$checked='';
    	$allidentity='';
    	if (!empty($row))foreach( $row AS $rs){
    		$i++;
    		if(in_array($rs['id'],$array)) {
    			$checked = 'checked="checked" ';
    			$price = $price+$rs['price'];
    			if (!empty($identity)){
    				$identity= $identity.",".$rs['id'];
    			}else{ $identity=$rs['id'];
    			}
    		}else{$checked='';
    		}
    		$row_data.='<tr id=tr_'.$i.' style="background-color:none">';
    		$row_data.='<td align="center">&nbsp;<input type="checkbox" class="checkbox" id="mfdid_'.$rs['id'].'" value="'.$rs['id'].'" OnChange="calculateTotal('.$rs['id'].')" '.$checked.' name="selector[]"/></td>';
    		$row_data.='<td>'.$rs['sale_name'].' , '.$rs['sale_relevent_name'].'<input type="hidden" name="buy_land_id'.$rs['id'].'" id="buy_land_id'.$rs['id'].'" value="'.$rs['id'].'" /></td></td>';
    		$row_data.='<td>'.$rs['size'].' ម៉ែត្រក្រឡា <input type="hidden" name="size'.$rs['id'].'" id="size'.$rs['id'].'" value="'.$rs['size'].'" /></td>';
    		$row_data.='<td>'.date("d-M-Y",strtotime($rs['buy_date'])).'<input type="hidden" name="price'.$rs['id'].'" id="price'.$rs['id'].'" value="'.$rs['price'].'" /></td>';
    		$row_data.='</tr>';
    
    		if (!empty($allidentity)){
    			$allidentity= $allidentity.",".$rs['id'];
    		}else{ $allidentity=$rs['id'];
    		}
    	}
    	$result = array('table'=>$row_data,
    			'price'=>$price,
    			'identity'=>$identity,
    			'allidentity'=>$allidentity);
    	return $result;
    }
}

