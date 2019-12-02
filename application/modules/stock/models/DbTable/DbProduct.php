<?php class Stock_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract{
	protected $_name = 'rms_product';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	function getAllProduct($search = '',$items_type=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$lang = $dbgb->currentlang();
		if($lang==1){// khmer
			$grade = "ide.title";
			$degree = "it.title";
		}else{ // English
			$grade = "ide.title_en";
			$degree = "it.title_en";
		}
		$result = $dbgb->getUserInfo();
		$level = $result["level"];
		$branch_id = $result["branch_id"];
		$string="";
		$location="";
		if ($level!=1){
			$string = $dbgb->getAccessPermission('pl.brand_id');
			$location = $dbgb->getAccessPermission('(SELECT pl.brand_id FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id LIMIT 1 )');
		}
		$sql = " SELECT 
					ide.id,
					ide.code,
					$grade,
					(SELECT $degree FROM `rms_product_cate` AS it WHERE it.id = ide.items_id LIMIT 1) AS degree,
					ide.cost,
					(SELECT SUM(pl.pro_qty) FROM `rms_product_location` AS pl WHERE pl.pro_id = ide.id  $string ) AS totalqty,
					CASE
						WHEN  ide.product_type = 1 THEN '".$tr->translate("PRODUCT_FOR_SELL")."'
						WHEN  ide.product_type = 2 THEN '".$tr->translate("OFFICE_MATERIAL")."'
					END AS product_type,
					CASE
						WHEN  ide.is_onepayment = 0 THEN '".$tr->translate("IS_VALIDATE")."'
						WHEN  ide.is_onepayment = 1 THEN '".$tr->translate("ONE_PAYMENTONLY")."'
					END AS is_onepayment,
					ide.modify_date,
					(SELECT CONCAT(first_name) FROM rms_users WHERE ide.user_id=id LIMIT 1 ) AS user_name
				";
		$sql.=$dbgb->caseStatusShowImage("ide.status");
		$sql.=" FROM `rms_product` AS ide WHERE 1 AND ide.is_productseat = 0 ";
		$where = ' ';
		if(!empty($items_type)){
			$where.= " AND ide.items_type = ".$db->quote($items_type);
		}
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
		if($search['is_onepayment']>-1){
			$where.= " AND is_onepayment = ".$db->quote($search['is_onepayment']);
		}
		if($search['product_type_search']>-1){
			$where.= " AND ide.product_type = ".$db->quote($search['product_type_search']);
		}
		$orderby = " ORDER BY ide.ordering ASC, ide.id DESC ";
		return $db->fetchAll($sql.$where.$location.$orderby);
	}
	
	public function AddProduct($_data){
		$_db= $this->getAdapter();
		try{
			$part= PUBLIC_PATH.'/images/proimage/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
			$name = $_FILES['images']['name'];
			if(!empty($name)){
				$ss = 	explode(".", $name);
				$image_name = "product_".date("Y").date("m").date("d").time().".".end($ss);
				$tmp = $_FILES['images']['tmp_name'];
				if(move_uploaded_file($tmp, $part.$image_name)){
					$photo = $image_name;
				}
				else{
					$string = "Image Upload failed";
				}
			}
			
			$dbgb = new Stock_Model_DbTable_DbGlobalProduct();
			$itemsCode = $dbgb->getItemsDetailCodeByItemsType(3);
			
			$_arr=array(
					'items_id'		=> $_data['items_id'],
					'items_type'	=> $_data['items_type'],
					'code'			=> $itemsCode,
					'title'	 	 	=> $_data['title'],
					'title_en'		=> $_data['title'],
					'note'    		=> $_data['note'],
					'product_type' 	=> $_data['product_type'],
					'measure' 		=> $_data['measure'],
					'cost'    		=> $_data['cost'],
					'images'   	 	=> $photo,
					'create_date' 	=> date("Y-m-d H:i:s"),
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> 1,
					'user_id'	 	=> $this->getUserId()
			);
			$this->_name = "rms_product";
			$id =  $this->insert($_arr);
			
			$this->_name='rms_product_location';
			$ids = explode(',', $_data['identity']);
			foreach ($ids as $i){
				$_arr = array(
						'pro_id'=>$id,
						'brand_id'=>$_data['brand_name_'.$i],
						'pro_qty'=>$_data['qty_'.$i],
						'price'=>$_data['price_'.$i],
						'stock_alert'=>$_data['qty_alert_'.$i],
						'note'=>$_data['note_'.$i],
				);
				$this->insert($_arr);
			}
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}
	
	function getProductLocation($id){
		$db = $this->getAdapter();
		$sql = "
		SELECT pl.*,
		(SELECT p.project_name FROM `ln_project` as p WHERE pl.`brand_id` = p.`br_id` LIMIT 1  ) AS branch_name
		FROM `rms_product_location` AS pl WHERE pl.pro_id = $id";
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$location = $dbgb->getAccessPermission('pl.`brand_id`');
		return $db->fetchAll($sql);
	}
	public function getItemsDetailById($degreeId,$type=null,$is_set=null){
		$db = $this->getAdapter();
		$sql=" SELECT ide.* FROM rms_product AS ide WHERE ide.`id` = $degreeId ";
		if(!empty($type)){
			$sql.=" AND ide.items_type=$type";
		}
		if(empty($is_set)){
			$sql.=" AND ide.is_productseat=0 ";
		}
		return $db->fetchRow($sql);
	}
	public function updateProduct($_data){
		$_db= $this->getAdapter();
		try{
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$result = $dbgb->getUserInfo();
			$level = $result["level"];
			$branch_id = $result["branch_id"];
			
			$part= PUBLIC_PATH.'/images/proimage/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			$photo = "";
				
			$_arr=array(
					'items_id'		=> $_data['items_id'],
					'items_type'	=> $_data['items_type'],
					'code'			=> $_data['code'],
					'title'	  		=> $_data['title'],
					'title_en'		=> $_data['title'],
					'note'   	 	=> $_data['note'],
					'product_type' 	=> $_data['product_type'],
					'measure' 		=> $_data['measure'],
					'cost'    		=> $_data['cost'],
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
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
			
			if ($level==1 AND $branch_id==1){ // only main Branch and Admin user
				// For Product Location Section
				$identitys = explode(',',$_data['identity']);
				$detailId="";
				if (!empty($identitys)){
					foreach ($identitys as $i){
						if (empty($detailId)){
							if (!empty($_data['detailid'.$i])){
								$detailId = $_data['detailid'.$i];
							}
						}else{
							if (!empty($_data['detailid'.$i])){
								$detailId= $detailId.",".$_data['detailid'.$i];
							}
						}
					}
				}
				$this->_name="rms_product_location";
				$where="pro_id = ".$_data["id"];
				if (!empty($detailId)){
					$where.=" AND id NOT IN ($detailId) ";
				}
				$this->delete($where);
			}
			
			if (!empty($_data['identity'])){
				$this->_name='rms_product_location';
				$ids = explode(',', $_data['identity']);
				foreach ($ids as $i){
					if (!empty($_data['detailid'.$i])){
						$_arr = array(
								'pro_id'=>$id,
								'brand_id'=>$_data['brand_name_'.$i],
								'pro_qty'=>$_data['qty_'.$i],
								'price'=>$_data['price_'.$i],
								'stock_alert'=>$_data['qty_alert_'.$i],
								'note'=>$_data['note_'.$i],
						);
						$where =" id =".$_data['detailid'.$i];
						$this->update($_arr, $where);
					}else{
						$_arr = array(
								'pro_id'=>$id,
								'brand_id'=>$_data['brand_name_'.$i],
								'pro_qty'=>$_data['qty_'.$i],
								'price'=>$_data['price_'.$i],
								'stock_alert'=>$_data['qty_alert_'.$i],
								'note'=>$_data['note_'.$i],
						);
						$this->insert($_arr);
					}
				}
			}
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}
	
	function CheckProductHasExit($data){
		$db = $this->getAdapter();
		$sql="SELECT ite.* FROM `rms_product` AS ite WHERE ite.title='".$data['title']."' AND ite.items_type=3 AND ite.items_id=".$data['category']." ";
		if (!empty($data['id'])){
			$sql.=" AND ite.id != ".$data['id'];
		}
		$sql.=" LIMIT 1";
		$row = $db->fetchRow($sql);
		if (empty($row)) {
			return 1;
		}else{
			return 2;
		}
	}
}