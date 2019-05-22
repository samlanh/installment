<?php

class Other_Model_DbTable_DbNews extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_news';
    
    public static function getUserId(){
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	return $dbg->getUserId();
    }
    static function getCurrentLang(){
    	$session_lang=new Zend_Session_Namespace('lang');
    	return $session_lang->lang_id;
    }
    public function getAllArticle($search){
    	$db=$this->getAdapter();
    	$lang = $this->getCurrentLang();
    	$sql="SELECT
    	act.`id`,
    	(SELECT ad.title FROM `ln_news_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$lang LIMIT 1) AS title,
    	act.`publish_date`,
    	act.`status`,
    	(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = act.`user_id` LIMIT 1) AS user_name
    	FROM `ln_news` AS act
    	WHERE act.`status`>-1";
    	$where='';
    	$from_date =(empty($search['start_date']))? '1': " act.publish_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " act.publish_date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    	$s_where = array();
    	$s_search = addslashes(trim($search['adv_search']));
    	$s_where[] = " (SELECT ad.title FROM `ln_news_detail` AS ad WHERE ad.news_id = act.`id` AND language_id=2 LIMIT 1) LIKE '%{$s_search}%'";
    	$s_where[] = " (SELECT ad.title FROM `ln_news_detail` AS ad WHERE ad.news_id = act.`id` AND language_id=1 LIMIT 1) LIKE '%{$s_search}%'";
    	$s_where[] = " (SELECT ad.title FROM `ln_news_detail` AS ad WHERE ad.news_id = act.`id` AND language_id=3 LIMIT 1) LIKE '%{$s_search}%'";
    	$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if (!empty($search['status_search'])){
    		$where.=" AND act.`status` =".$search['status_search'];
    	}
    	$order = "  ORDER BY act.`id` DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function addArticle($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$valid_formats = array("jpg", "png", "gif", "bmp","jpeg");
    		$part= PUBLIC_PATH.'/images/news/';
    		if (!file_exists($part)) {
    			mkdir($part, 0777, true);
    		}
    		$name = $_FILES['photo']['name'];
    		$size = $_FILES['photo']['size'];
    		$photo='news.jpg';
    		if (!empty($name)){
    			$tem =explode(".", $name);
    			$image_name = date("Y").date("m").date("d").time().".".end($tem);
    			$tmp = $_FILES['photo']['tmp_name'];
    			if(move_uploaded_file($tmp, $part.$image_name)){
    				$photo = $image_name;
    			}
    			else
    				$string = "Image Upload failed";
    				
    		}
    		$dbglobal = new Application_Model_DbTable_DbGlobal();
    		$lang = $dbglobal->getLaguage();
    		$arr = array(
    				'status'=>$data['status'],
    				'publish_date'=>$data['public_date'],
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId(),
    		);
    		
    		if (!empty($data['id'])){
    			if (!empty($name)){
    				$arr['image_feature']= $photo;
    			}
    			
    			$where=" id=".$data['id'];
    			$this->_name="ln_news";
    			$this->update($arr, $where);
    			$article_id =$data['id'];
    			
    			if(!empty($lang)){
    				$iddetail="";
    				foreach($lang as $row){
    					$title = str_replace(' ','',$row['title']);
    					if (empty($iddetail)){
    						if (!empty($data['iddetail'.$title])){
    							$iddetail=$data['iddetail'.$title];
    						}
    					}
    					else{
    						if (!empty($data['iddetail'.$title])){
    							$iddetail=$iddetail.",".$data['iddetail'.$title];
    						}
    					}
    				}
    	    
    				$this->_name="ln_news_detail";
    				$where1=" news_id=".$data['id'];
    				if (!empty($iddetail)){
    					$where1.=" AND id NOT IN (".$iddetail.")";
    				}
    				$this->delete($where1);
    	    
    				foreach($lang as $row){
    					$title = str_replace(' ','',$row['title']);
    					if (!empty($data['iddetail'.$title])){
    						$arr_article = array(
    								'news_id'=>$article_id,
		    						'title'=>$data['title'.$title],
		    						'description'=>$data['description'.$title],
		    						'lang'=>$row['id'],
    						);
    						$this->_name="ln_news_detail";
    						$wheredetail=" news_id=".$data['id']." AND id=".$data['iddetail'.$title];
    						$this->update($arr_article,$wheredetail);
    					}else{
    						$arr_article = array(
    								'news_id'=>$article_id,
		    						'title'=>$data['title'.$title],
		    						'description'=>$data['description'.$title],
		    						'lang'=>$row['id'],
    						);
    						$this->_name="ln_news_detail";
    						$this->insert($arr_article);
    					}
    				}
    			}
    		}else{
    			$this->_name="ln_news";
    			$arr['image_feature']= $photo;
    			$arr['created_date']= date("Y-m-d H:i:s");
    			$article_id = $this->insert($arr);
    
    			if(!empty($lang)) foreach($lang as $row){
    				$title = str_replace(' ','',$row['title']);
    				$arr_article = array(
    						'news_id'=>$article_id,
    						'title'=>$data['title'.$title],
    						'description'=>$data['description'.$title],
    						'lang'=>$row['id'],
    				);
    				$this->_name="ln_news_detail";
    				$this->insert($arr_article);
    			}
    		}
    
    		$db->commit();
    	}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    
    function getArticleById($id){
    	$db= $this->getAdapter();
    	$sql="SELECT * FROM $this->_name WHERE id =".$id;
    	return $db->fetchRow($sql);
    }
    function getArticleTitleByLang($article_id,$lang){
    	$db = $this->getAdapter();
    	$sql="SELECT acd.id,acd.`title`,acd.`lang`,acd.description FROM `ln_news_detail` AS acd WHERE acd.`news_id`=$article_id AND acd.`lang`=$lang";
    	return $db->fetchRow($sql);
    }
    function deleteNews($id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$where=" id= $id";
	    	$this->delete($where);
	    	
	    	$this->_name="ln_news_detail";
	    	$where=" news_id= $id";
	    	$this->delete($where);
	    	$db->commit();
    	}catch(exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
}  
	  

