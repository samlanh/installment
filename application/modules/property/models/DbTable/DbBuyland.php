<?php

class Property_Model_DbTable_DbBuyland extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_buy_land';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    
    }
    function getBuyland($search=null){
    	$db = $this->getAdapter();
    	$sql = 'SELECT bl.`id`,bl.`buy_no`,bl.`title`,bl.`sale_name`,bl.`sale_relevent_name`,
    	(SELECT l.title_kh FROM `ln_land_blog` AS l WHERE l.id = bl.`land_blog` LIMIT 1) AS land_blog,
		bl.`width`,bl.`height`,bl.`size`,bl.`price`,bl.`location`,bl.`buy_date`,bl.`is_lock`,bl.`status`
		 FROM `ln_buy_land` AS bl WHERE 
		bl.`status`>-1 ';
    	$where='';
    	$from_date =(empty($search['start_date']))? '1': "bl.`buy_date` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "bl.`buy_date` <= '".$search['end_date']." 23:59:59'";
    	if (empty($search['show_all'])){
    		$where = " AND ".$from_date." AND ".$to_date;
//     		if($search['status']>-1){
//     			$where.= " AND bl.`status` = ".$search['status'];
//     		}
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] = " bl.`title` LIKE '%{$s_search}%'";
    			$s_where[] = " bl.`buy_no` LIKE '%{$s_search}%'";
    			$s_where[] = " bl.`width` LIKE '%{$s_search}%'";
    			$s_where[] = " bl.`height` LIKE '%{$s_search}%'";
    			$s_where[] = " bl.`size` LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if($search['land_blog']>0){
    			 $where.= " AND bl.`land_blog` = ".$search['land_blog'];
    		}
    	}
    	$order=' ORDER BY bl.id DESC';
    	return $db->fetchAll($sql.$where.$order);
    }
    function addBuyLand($data){
    	try{
    		$part= PUBLIC_PATH.'/images/';
    		$land_photo1='';
    		$land_photo2='';
    		$sale_photo1='';
    		$sale_photo2='';
    		$photo1 = $_FILES['land_img1'];
    		if($photo1["name"]!=""){
    			$temp = explode(".", $photo1["name"]);
    			$newfilename1 = "photo_land_buy".date("Y-m-d").round(microtime(true)).'_1.' . end($temp);
    			if(file_exists("$part.$newfilename1")) unlink("$part.$newfilename1");
    			move_uploaded_file($_FILES['land_img1']["tmp_name"], $part . $newfilename1);
    			$land_photo1 = $newfilename1;
    		}
    		$photo2 = $_FILES['land_img2'];
    		if($photo2["name"]!=""){
    			$temp = explode(".", $photo2["name"]);
    			$newfilename2 = "photo_land_buy".date("Y-m-d").round(microtime(true)).'_2.' . end($temp);
    			if(file_exists("$part.$newfilename2")) unlink("$part.$newfilename2");
    			move_uploaded_file($_FILES['land_img2']["tmp_name"], $part . $newfilename2);
    			$land_photo2 = $newfilename2;
    		}
    		$photo3 = $_FILES['sale_img1'];
    		if($photo3["name"]!=""){
    			$temp = explode(".", $photo3["name"]);
    			$newfilename3 = "photo_sale".date("Y-m-d").round(microtime(true)).'_1.' . end($temp);
    			if(file_exists("$part.$newfilename3")) unlink("$part.$newfilename3");
    			move_uploaded_file($_FILES['sale_img1']["tmp_name"], $part . $newfilename3);
    			$sale_photo1 = $newfilename3;
    		}
    		$photo4 = $_FILES['sale_img2'];
    		if($photo4["name"]!=""){
    			$temp = explode(".", $photo4["name"]);
    			$newfilename4 = "photo_sale".date("Y-m-d").round(microtime(true)).'_2.' . end($temp);
    			if(file_exists("$part.$newfilename4")) unlink("$part.$newfilename4");
    			move_uploaded_file($_FILES['sale_img2']["tmp_name"], $part . $newfilename4);
    			$sale_photo2 = $newfilename4;
    		}
    		$db = new Application_Model_DbTable_DbGlobal();
    		$buy_no = '';
    		$arr = array(
    				'buy_no'=>$buy_no,
    				'title'=>$data['title'],
//     				'land_type'=>$data['property_type'],
    				'land_blog'=>$data['land_blog'],
    				'price'=>$data['land_price'],
    				'width'=>$data['width'],
    				'height'=>$data['height'],
    				'size'=>$data['size'],
    				'buy_date'=>$data['buy_date'],
    				'credentail_no'=>$data['credentail_no'],
    				'issue_date'=>$data['issue_date'],
    				'location'=>$data['location'],
    				'border_north'=>$data['border_north'],
    				'border_south'=>$data['border_south'],
    				'border_west'=>$data['border_west'],
    				'border_east'=>$data['border_east'],
    				'sale_name'=>$data['sale_name_kh'],
    				'sale_sex'=>$data['sale_sex'],
    				'sale_age'=>$data['sale_age'],
    				'sale_nationlity'=>$data['sale_nationality'],
    				'sale_nation_id'=>$data['sale_nation_id'],
    				'sale_phone'=>$data['sale_phone'],
    				'sale_addrees'=>$data['sale_address'],
    				
    				'sale_relevent_name'=>$data['sale_relevent_name'],
    				'sale_relevent_sex'=>$data['sale_relevent_sex'],
    				'sale_relevent_age'=>$data['sale_relevent_age'],
    				'sale_relevent_nationlity'=>$data['sale_relevent_nationlity'],
    				'sale_relevent_nationid'=>$data['sale_relevent_nationid'],
    				'sale_relevent_is'=>$data['sale_relevent_is'],
    				
    				'buyer_name'=>$data['buy_name_kh'],
    				'buyer_sex'=>$data['buy_sex'],
    				'buyer_age'=>$data['buy_age'],
    				'buyer_nationality'=>$data['buy_nationality'],
    				'buyer_nation_id'=>$data['buy_nation_id'],
    				'buyer_phone'=>$data['buy_phone'],
    				'buyer_address'=>$data['buy_address'],
    				
    				'buyer_relevent_name'=>$data['buyer_relevent_name'],
    				'buyer_relevent_sex'=>$data['buyer_relevent_sex'],
    				'buyer_relevent_age'=>$data['buyer_relevent_age'],
    				'buyer_relevent_nationality'=>$data['buyer_relevent_nationality'],
    				'buyer_relevent_nationid'=>$data['buyer_relevent_nationid'],
    				'buyer_relevent_is'=>$data['buyer_relevent_is'],
    				
    				'note'=>$data['note'],
    				'status'=>$data['status'],
    				'user_id'=>$this->getUserId(),
    				'create_date'=>date("Y-m-d H:i:s"),
					'modify_date'=>date("Y-m-d H:i:s"),
    				
    				'land_photo1'=>$land_photo1,
    				'land_photo2'=>$land_photo2,
    				'sale_photo1'=>$sale_photo1,
    				'sale_photo2'=>$sale_photo2,
					
					'sale_d_type'=>$data['sale_d_type'],
					'sale_relevent_d_type'=>$data['sale_relevent_d_type'],
					'buyer_d_type'=>$data['buyer_d_type'],
					'buyer_relevent_d_type'=>$data['buyer_relevent_d_type'],
    				);
    		$this->insert($arr);
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    } 
    function updateBuyLand($data){
    	try{
    		$part= PUBLIC_PATH.'/images/';
    		$land_photo1='';
    		$land_photo2='';
    		$sale_photo1='';
    		$sale_photo2='';
    		$photo1 = $_FILES['land_img1'];
    		if($photo1["name"]!=""){
    			$temp = explode(".", $photo1["name"]);
    			$newfilename1 = "photo_land_buy".date("Y-m-d").round(microtime(true)).'_1.' . end($temp);
    			if(file_exists("$part.$newfilename1")) unlink("$part.$newfilename1");
    			move_uploaded_file($_FILES['land_img1']["tmp_name"], $part . $newfilename1);
    			$land_photo1 = $newfilename1;
    		}else{$land_photo1=$data['old_land_img1'];}
    		
    		$photo2 = $_FILES['land_img2'];
    		if($photo2["name"]!=""){
    			$temp = explode(".", $photo2["name"]);
    			$newfilename2 = "photo_land_buy".date("Y-m-d").round(microtime(true)).'_2.' . end($temp);
    			if(file_exists("$part.$newfilename2")) unlink("$part.$newfilename2");
    			move_uploaded_file($_FILES['land_img2']["tmp_name"], $part . $newfilename2);
    			$land_photo2 = $newfilename2;
    		}else{$land_photo2=$data['old_land_img2'];}
    		
    		$photo3 = $_FILES['sale_img1'];
    		if($photo3["name"]!=""){
    			$temp = explode(".", $photo3["name"]);
    			$newfilename3 = "photo_sale".date("Y-m-d").round(microtime(true)).'_1.' . end($temp);
    			if(file_exists("$part.$newfilename3")) unlink("$part.$newfilename3");
    			move_uploaded_file($_FILES['sale_img1']["tmp_name"], $part . $newfilename3);
    			$sale_photo1 = $newfilename3;
    		}else{$sale_photo1=$data['old_sale_img1'];}
    		
    		$photo4 = $_FILES['sale_img2'];
    		if($photo4["name"]!=""){
    			$temp = explode(".", $photo4["name"]);
    			$newfilename4 = "photo_sale".date("Y-m-d").round(microtime(true)).'_2.' . end($temp);
    			if(file_exists("$part.$newfilename4")) unlink("$part.$newfilename4");
    			move_uploaded_file($_FILES['sale_img2']["tmp_name"], $part . $newfilename4);
    			$sale_photo2 = $newfilename4;
    		}else{$sale_photo2=$data['old_sale_img2'];}
    		
    		$db = new Application_Model_DbTable_DbGlobal();
    		$buy_no = '';
    		$arr = array(
    				'buy_no'=>$data['buy_no'],
    				'title'=>$data['title'],
//     				'land_type'=>$data['property_type'],
    				'land_blog'=>$data['land_blog'],
    				'price'=>$data['land_price'],
    				'width'=>$data['width'],
    				'height'=>$data['height'],
    				'size'=>$data['size'],
    				'buy_date'=>$data['buy_date'],
    				'credentail_no'=>$data['credentail_no'],
    				'issue_date'=>$data['issue_date'],
    				'location'=>$data['location'],
    				'border_north'=>$data['border_north'],
    				'border_south'=>$data['border_south'],
    				'border_west'=>$data['border_west'],
    				'border_east'=>$data['border_east'],
    				
    				'sale_name'=>$data['sale_name_kh'],
    				'sale_sex'=>$data['sale_sex'],
    				'sale_age'=>$data['sale_age'],
    				'sale_nationlity'=>$data['sale_nationality'],
    				'sale_nation_id'=>$data['sale_nation_id'],
    				'sale_phone'=>$data['sale_phone'],
    				'sale_addrees'=>$data['sale_address'],
    				
    				'sale_relevent_name'=>$data['sale_relevent_name'],
    				'sale_relevent_sex'=>$data['sale_relevent_sex'],
    				'sale_relevent_age'=>$data['sale_relevent_age'],
    				'sale_relevent_nationlity'=>$data['sale_relevent_nationlity'],
    				'sale_relevent_nationid'=>$data['sale_relevent_nationid'],
    				'sale_relevent_is'=>$data['sale_relevent_is'],
    				
    				'buyer_name'=>$data['buy_name_kh'],
    				'buyer_sex'=>$data['buy_sex'],
    				'buyer_age'=>$data['buy_age'],
    				'buyer_nationality'=>$data['buy_nationality'],
    				'buyer_nation_id'=>$data['buy_nation_id'],
    				'buyer_phone'=>$data['buy_phone'],
    				'buyer_address'=>$data['buy_address'],
    				
    				'buyer_relevent_name'=>$data['buyer_relevent_name'],
    				'buyer_relevent_sex'=>$data['buyer_relevent_sex'],
    				'buyer_relevent_age'=>$data['buyer_relevent_age'],
    				'buyer_relevent_nationality'=>$data['buyer_relevent_nationality'],
    				'buyer_relevent_nationid'=>$data['buyer_relevent_nationid'],
    				'buyer_relevent_is'=>$data['buyer_relevent_is'],
    				
    				'note'=>$data['note'],
    				'status'=>$data['status'],
    				'user_id'=>$this->getUserId(),
    				
    				'land_photo1'=>$land_photo1,
    				'land_photo2'=>$land_photo2,
    				'sale_photo1'=>$sale_photo1,
    				'sale_photo2'=>$sale_photo2,
					
					'sale_d_type'=>$data['sale_d_type'],
					'sale_relevent_d_type'=>$data['sale_relevent_d_type'],
					'buyer_d_type'=>$data['buyer_d_type'],
					'buyer_relevent_d_type'=>$data['buyer_relevent_d_type'],
					'modify_date'=>date("Y-m-d H:i:s"),
//     				'create_date'=>date("Y-m-d"),

    		);
    		$where = 'id = '.$data['id'];
    		$this->update($arr, $where);
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function getBuyLandById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `ln_buy_land` AS bl WHERE bl.`id`=".$id;
    	return $db->fetchRow($sql);
    }
    function deleteBuyLand($id){
    	$db = $this->getAdapter();
    	$arr = array( 'status'=> -1);
    	$where = ' id = '.$id;
    	$this->_name = "ln_buy_land";
    	$this->update($arr, $where);
    }
    function checkLandIslock($id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `ln_buy_land` AS bl WHERE  bl.`is_lock`=0 AND bl.`id`=".$id;
    	return $db->fetchRow($sql);
    }
}

