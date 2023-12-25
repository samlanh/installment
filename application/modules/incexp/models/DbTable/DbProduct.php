<?php class Incexp_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_product';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	// For Module Product Stock
	public function AddProduct($_data){
		$_db= $this->getAdapter();
		try{
			
			$part= PUBLIC_PATH.'/images/proimage/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
			$name = $_FILES['images']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "product_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['images']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}
				else
					$string = "Image Upload failed";
			}
			
			$_arr=array(
					'items_id'=> $_data['items_id'],
					'code'=> $_data['code'],
					'title'	  => $_data['title'],
					'note'    => $_data['note'],
					'price'    => $_data['price'],					
					'images'    => $photo,
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> 1,
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_product";
			$id =  $this->insert($_arr);
			
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	
	function getAllProduct($search = '',$items_type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
		$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
		
		$sql = " SELECT ide.id,ide.code,ide.title,
			(SELECT it.title FROM `rms_category` AS it WHERE it.id = ide.items_id LIMIT 1) AS category,
			ide.price,			
			ide.modify_date,
			(SELECT CONCAT(first_name) FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name,
			 CASE    
				WHEN  `ide`.`status` = 1 THEN '".$imgtick."'
				WHEN  `ide`.`status` = 0 THEN '".$imgnone."'
				END AS status
				
			 FROM `rms_product` AS ide WHERE 1 
			";
		$orderby = " ORDER BY ide.items_id ASC,ide.ordering ASC, ide.id DESC ";
		$where = ' ';

		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " ide.title LIKE '%{$s_search}%'";
			$s_where[] = " ide.code LIKE '%{$s_search}%'";
			$s_where[] = " ide.cost LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['items_search'])){
			$where.= " AND ide.items_id  = ".$db->quote($search['items_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		return $db->fetchAll($sql.$where.$orderby);
	}
	
	
	public function updateProduct($_data){
		$_db= $this->getAdapter();
		try{
		
			$part= PUBLIC_PATH.'/images/proimage/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
				
			$_arr=array(
					'items_id'=> $_data['items_id'],
					'code'=> $_data['code'],
					'title'	  => $_data['title'],
					'note'    => $_data['note'],
					'price'    => $_data['price'],
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_product";
			$name = $_FILES['images']['name'];
			if (!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "product_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['images']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					if (file_exists($part.$_data["old_photo"])) {
						if (!empty($_data["old_photo"])){
							unlink($part.$_data["old_photo"]);//delete old file
						}
					}
					$_arr['images'] = $image_name;
				}
			}
			$id =  $_data["id"];
			$where = $_db->quoteInto("id=?", $id);
			$this->update($_arr, $where);
			
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	function getAllProductsNormal($product_type=null){
		$db = $this->getAdapter();
		$sql="SELECT i.id,
		CONCAT(i.title,' (',(SELECT it.title FROM `rms_category` AS it WHERE it.id = i.items_id LIMIT 1),')') AS name
		FROM `rms_product` AS i
		WHERE i.status =1 AND i.items_type=3 AND i.is_productseat=0  ";
		
		$sql.=" ORDER BY i.items_id ASC, i.ordering ASC";
		return $db->fetchAll($sql);
	}
	public function getItemsDetailById($prod_id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE `id` = $prod_id LIMIT 1 ";		
		return $db->fetchRow($sql);
	}
	
	function getAllProducts($category=null){
		$db = $this->getAdapter();
		$sql="SELECT i.id,
		i.title AS name
		FROM `rms_product` AS i
		WHERE i.status =1 ";
		if(!empty($category)){
			$sql.=" AND i.items_id=$category ";
		}
		$sql.=" ORDER BY i.items_id ASC";
		return $db->fetchAll($sql);
	}
	
}
