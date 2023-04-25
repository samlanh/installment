<?php

class Mobileapp_Model_DbTable_DbSlide extends Zend_Db_Table_Abstract
{

   /* protected $_name = 'mini_website_setting';*/
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
 /*   function getWebsiteSetting($label){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `mini_website_setting` AS ws WHERE ws.`label`='$label' AND ws.`status`=1";
    	return $db->fetchRow($sql);
    }*/
    function getSlideShow(){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `mobile_slideshow` AS ws";
    	return $db->fetchAll($sql);
    }
// 	function updateSlide($_data,$label_name){
// 		$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	try{
//     		$valid_formats = array("jpg", "png", "gif", "bmp","jpeg");
//     		$part= PUBLIC_PATH.'/images/slide/';
//     		if ($label_name=='slide_partner'){
//     			$part= PUBLIC_PATH.'/images/slide/partner/';
//     		}
//     		$dbg = new Application_Model_DbTable_DbGlobal();
    		
//     		$identity = $_data['identity'];
//     		$image_list="";
//     		$ids = explode(',', $identity);
//     		foreach ($ids as $i){
//     			if (!empty($_FILES['photo'.$i]['name'])){
//     				$ss = 	explode(".", $_FILES['photo'.$i]['name']);
//     				$new_image_name = date("Y").date("m").date("d").time().$i.".".end($ss);
//     				$image_name = $dbg->resizeImase($_FILES['photo'.$i], $part,$new_image_name);
// //     				$ss = 	explode(".", $name);
// //     				$image_name = date("Y").date("m").date("d").time().$i.".".end($ss);
// //     				$tmp = $_FILES['photo'.$i]['tmp_name'];
// //     				if(move_uploaded_file($tmp, $part.$image_name)){
//     					$photo = $image_name;
// //     				}
// //     				else
// //     					$string = "Image Upload failed";
    				 
//     			}else{
//     				$image_name = $_data['old_photo'.$i];
//     			}
//     			if (empty($image_list )){
//     				$image_list=$image_name;
//     			}else{
//     				$image_list = $image_list.",".$image_name;
//     			}
//     		}
//     		$_arr=array(
//     				'value'      => $image_list,
//     				'date_modify'  =>date("Y-m-d"),
//     				'status'=>1,
//     				'user_id'      => $this->getUserId(),
//     		);
//     		$this->_name="mini_website_setting";
//     		$where=" label= '".$label_name."'";
//     		$this->update($_arr, $where);
//     		$db->commit();
//     	}catch(exception $e){
//     		Application_Form_FrmMessage::message("Application Error");
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		$db->rollBack();
//     	}
// 	}
	function updateslideshow($_data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$dbg = new Application_Model_DbTable_DbGlobal();
			$part= PUBLIC_PATH.'/images/slide/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$identity = $_data['identity'];
			$ids = explode(',', $identity);
			$image_name='';
			
			$detail_list="";
			foreach ($ids as $i){
				if (!empty($_data['detail_id'.$i])){
					if (empty($detail_list)){$detail_list=$_data['detail_id'.$i];}else{$detail_list=$detail_list.",".$_data['detail_id'.$i];}
				}
			}
			if (!empty($detail_list)){
				$this->_name="mobile_slideshow";
				$where = "id NOT IN (".$detail_list.")";
				$this->delete($where);
			}
			foreach ($ids as $i){
				if (!empty($_data['detail_id'.$i])){
					if (!empty($_FILES['photo'.$i]['name'])){
						$ss = 	explode(".", $_FILES['photo'.$i]['name']);
						$new_image_name = date("Y").date("m").date("d").time().$i.".".end($ss);
						$image_name = $dbg->resizeImase($_FILES['photo'.$i], $part,$new_image_name);
					}else{
						$image_name = $_data['old_photo'.$i];
					}
					$_arr=array(
							'title'      => '',
							'images'      => $image_name,
							'create_date'=>date("Y-m-d H:i:s"),
							'modify_date'=>date("Y-m-d H:i:s"),
							'user_id'      => $this->getUserId(),
					);
					$this->_name="mobile_slideshow";
					$where = ' id = '.$_data['detail_id'.$i];
					$this->update($_arr, $where);
				}else{
					if (!empty($_FILES['photo'.$i]['name'])){
						$ss = 	explode(".", $_FILES['photo'.$i]['name']);
						$new_image_name = date("Y").date("m").date("d").time().$i.".".end($ss);
						$image_name = $dbg->resizeImase($_FILES['photo'.$i], $part,$new_image_name);
					}else{
						$image_name = "";
					}
					$_arr=array(
							'title'      => "",
							'images'      => $image_name,
// 							'link'  =>"",
							'create_date'=>date("Y-m-d H:i:s"),
							'modify_date'=>date("Y-m-d H:i:s"),
							'user_id'      => $this->getUserId(),
					);
					$this->_name="mobile_slideshow";
					$this->insert($_arr);
				}
			}
			$db->commit();
		}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
	}
	/*function updateBanner($_data,$label_name){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$valid_formats = array("jpg", "png", "gif", "bmp","jpeg");
			$part= PUBLIC_PATH.'/images/banner/';
	
			$identity = $_data['identity'];
			$image_list="";
			$ids = explode(',', $identity);
			foreach ($ids as $i){
				$name = $_FILES['photo'.$i]['name'];
				if (!empty($name)){
					$ss = 	explode(".", $name);
					$image_name = date("Y").date("m").date("d").time().$i.".".end($ss);
					$tmp = $_FILES['photo'.$i]['tmp_name'];
					if(move_uploaded_file($tmp, $part.$image_name)){
						$photo = $image_name;
					}
					else
						$string = "Image Upload failed";
						
				}else{
					$image_name = $_data['old_photo'.$i];
				}
				if (empty($image_list )){
					$image_list=$image_name;
				}else{
					$image_list = $image_list.",".$image_name;
				}
			}
			$_arr=array(
					'value'      => $image_list,
					'date_modify'  =>date("Y-m-d"),
					'status'=>1,
					'user_id'      => $this->getUserId(),
			);
			$this->_name="mini_website_setting";
			$where=" label= '".$label_name."'";
			$this->update($_arr, $where);
			$db->commit();
		}catch(exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	
	function updateAnnouncement($_data,$label_name){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$identity = $_data['identity'];
    		$ids = explode(',', $identity);
			$value='';
    		foreach ($ids as $i){
				if(empty($value)){$value= $_data['article'.$i];}elseif(!empty($value)){ $value= $value.",".$_data['article'.$i];}
			}
				$_arr=array(
    				'value'      => $value,
    				'date_modify'  =>date("Y-m-d"),
    				'status'=>1,
    				'user_id'      => $this->getUserId(),
    		);
			$this->_name="mini_website_setting";
    		$where=" label= '".$label_name."'";
			$this->update($_arr, $where);
			$db->commit();
		}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
	}*/
}

