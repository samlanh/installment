<?php
class Dailywork_Model_DbTable_DbResult extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_result';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){
	  $this->_name='tb_result';
	  $_arr = array(
	    'name'=>$_data['employeename'],
	    'email'=>$_data['emailemployee'],
	    'result'=>$_data['workresult'],
	    );
	  $this->insert($_arr);// insert data into database
 }//add product
 
 public function updateresultById($_data){
 //print_r($_data); exit();
 	$_arr = array(
 			'name'=>$_data['employeename'],
 			'email'=>$_data['emailemployee'],
 			'result'=>$_data['workresult'],
 	);
 	$where = "id =".$_data['id'];
 	$this->update($_arr, $where);
 
 }// updateproductById
 
 public function getresultById($id)
 {
 	$db = $this->getAdapter();
 	$sql = "SELECT id , name , email , result  FROM tb_result WHERE id = $id";
 	return $db->fetchRow($sql);
 }// getproduct1ById
 
 
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
    
  public function getBrand(){
  	$db = $this->getAdapter();
  	$sql = "SELECT brand_id from sp_product";
  		return $db->fetchAll($sql);
  }
  
  public function getModel(){
  	$db = $this->getAdapter();
  	$sql = "SELECT v.`id`,v.`name_kh` as name,v.`status`,v.`key_code`,`type` FROM `sp_view` AS v WHERE v.`type` = 2";
  	return $db->fetchAll($sql);
  }
  
public  function getResult($search=null){
  $db = $this->getAdapter();
  $sql = " SELECT id , name , email , result FROM tb_result WHERE 1 ";
  $where = '';
  if(!empty($search["adv_search"])){
  	$s_where=array();
  	$s_search = addslashes(trim($search['adv_search']));
  	$s_where[]= " name LIKE '%{$s_search}%'";
  	$where.=' AND ('.implode(' OR ', $s_where).')';
  }
  if(!empty($search["name"])){
  	$where.=' AND id='.$search["name"];
  }
 // echo $sql.$where;
  return $db->fetchAll($sql.$where);
 }
  
  public function getSize(){
  	$db = $this->getAdapter();
  	$sql = "SELECT v.`id`,v.`name_kh` as name,v.`status`,v.`key_code`,`type` FROM `sp_view` AS v WHERE v.`type` = 3";
  	return $db->fetchAll($sql);
  }
  
  public function getColor(){
  	$db = $this->getAdapter();
  	$sql = "SELECT v.`id`,v.`name_en` as name,v.`status`,v.`key_code`,`type` FROM `sp_view` AS v WHERE v.`type` = 4";
  	return $db->fetchAll($sql);
  }
  
  public function getProductCode(){
  	$db =$this->getAdapter();
  	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$pre = "PR-";
  	for($i = $acc_no;$i<3;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }
  
  function getAllProduct($data=null){
  	$db = $this->getAdapter();
  	$db_globle = new Application_Model_DbTable_DbGlobal();
  	$sql ="SELECT 
			  p.`id`,
			  p.`item_name` ,
			  p.`item_code`,
			  p.`barcode` ,
  			  p.`status`,
			  (SELECT c.category_name FROM `sp_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
			  pl.`qty`
			FROM
			  `sp_product` AS p 
			WHERE p.`id`=pl.`pro_id`";
  	$where = '';
  	if($data["ad_search"]!=""){
  		$s_where=array();
  		$s_search = addslashes(trim($data['ad_search']));
  		$s_where[]= " p.item_name LIKE '%{$s_search}%'";
  		$s_where[]=" p.barcode LIKE '%{$s_search}%'";
  		$s_where[]= " p.item_code LIKE '%{$s_search}%'";
  		$s_where[]= " p.serial_number LIKE '%{$s_search}%'";
  		//$s_where[]= " cate LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  	}
  	if($data["branch"]!=""){
  		$where.=' AND pl.`location_id`='.$data["branch"];
  	}
  	if($data["brand"]!=""){
  		$where.=' AND p.brand_id='.$data["brand"];
  	}
  	if($data["category"]!=""){
  		$where.=' AND p.cate_id='.$data["category"];
  	}
  	if($data["category"]!=""){
  		$where.=' AND p.cate_id='.$data["category"];
  	}
  	if($data["model"]!=""){
  		$where.=' AND p.model_id='.$data["model"];
  	}
  	if($data["size"]!=""){
  		$where.=' AND p.size_id='.$data["size"];
  	}
  	if($data["color"]!=""){
  		$where.=' AND p.color_id='.$data["color"];
  	}
  	if($data["status"]!=""){
  		$where.=' AND p.status='.$data["status"];
  	}
  	//echo $location;
  	return $db->fetchAll($sql.$where);
  	
  }
  
  function getProductById($id){
  	$db = $this->getAdapter();
  	$sql ="SELECT 
			  p.`id`,
			  p.`barcode`,
			  p.`brand_id`,
			  p.`cate_id`,
			  p.`color_id`,
			  p.`item_code`,
			  p.`item_name`,
			  p.`measure_id`,
			  p.`model_id`,
			  p.`note`,
			  p.`qty_perunit`,
			  p.`serial_number`,
			  p.`size_id`,
			  p.`status`,
			  p.`unit_label` 
			FROM
			  `tb_product` AS p 
			WHERE p.id = $id ";
  	return $db->fetchRow($sql);
  }
  function getProductLocation($id){
  	$db = $this->getAdapter();
  	$sql = "SELECT 
			  pl.`id`,
			  pl.`pro_id`,
			  pl.`qty`,
			  pl.`qty_warning`,
			  pl.`location_id`,
			  s.`name` 
			FROM
			  `tb_prolocation` AS pl,
			  `tb_sublocation` AS s 
			WHERE pl.`pro_id` = $id 
			  AND pl.`location_id` = s.`id` ";
  	return $db->fetchAll($sql);
  }
  
  function getPriceType(){
  	$db = $this->getAdapter();
  	$sql ="SELECT p.`id`,p.`name` FROM `sp_price_type` AS p WHERE p.`status`=1";
  	return $db->fetchAll($sql);
  }
  function getProductPrcie($id){
  	$db = $this->getAdapter();
  	$sql ="SELECT p.`id`,p.`pro_id`,p.`price`,pt.`name`,p.`remark`,p.`type_id` FROM `sp_product_price` AS p,`sp_price_type` AS pt WHERE p.`type_id`=pt.`id` AND p.`pro_id`=$id";
  	return $db->fetchAll($sql);
  }
  // Insert and  Update section
    public function add1($data){
    	//print_r($data);exit();
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$arr = array(
    			'item_name'		=>	$data["name"],
    			'item_code'		=>	$data["pro_code"],
    			'barcode'		=>	$data["barcode"],
    			'cate_id'		=>	$data["category"],
    			'brand_id'		=>	$data["brand"],
    			'model_id'		=>	$data["model"],
    			'color_id'		=>	$data["color"],
    			'measure_id'	=>	$data["measure"],
    			'size_id'		=>	$data["size"],
    			'serial_number'	=>	$data["serial"],
    			'qty_perunit'	=>	$data["qty_unit"],
    			'unit_label'	=>	$data["label"],
    			'user_id'		=>	$this->getUserId(),
    			'note'			=>	$data["description"],
    			'status'		=>	$data["status"],
    		);
    		$this->_name="sp_product";
    		$id = $this->insert($arr);
    		
    		// For Product Location Section
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
    				$arr1 = array(
    					'pro_id'			=>	$id,
    					'location_id'		=>	$data["branch_id_".$i],
    					'qty'				=>	$data["current_qty_".$i],
    					'qty_warning'		=>	$data["qty_warnning_".$i],
    					'last_mod_userid'	=>	$this->getUserId(),
    					'last_mod_date'		=>	new Zend_Date(),
    				);
    				$this->_name = "sp_prolocation";
    				$this->insert($arr1);
    			}
    		}
    		// For Product Price
    		if(!empty($data['identity_price'])){
    			$identitys = explode(',',$data['identity_price']);
    			foreach($identitys as $i)
    			{
    				$arr2 = array(
    						'pro_id'			=>	$id,
    						'type_id'			=>	$data["price_type_".$i],
    						//'location_id'		=>	$data["current_qty_".$i],
    						'price'				=>	$data["price_".$i],
    						'remark'			=>	$data["price_remark_".$i],
    						//'last_mod_userid'	=>	$this->getUserId(),
    						//'last_mod_date'		=>	new Zend_Date(),
    				);
    				$this->_name = "sp_product_price";
    				$this->insert($arr2);
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e);
    		echo $e->getMessage();exit();
    	}
    }
    
 
    public function getOrderItemVeiw($id){
    	$db = $this->getAdapter();
    	$user = $this->GetuserInfo();
    	    	$itemSql = "SELECT 
							  lo.Name,
							  p.pro_id,
							  p.cate_id,
							  p.item_name,
							  p.`qty_onhand`,
							  p.`qty_available`,
							  p.`qty_onorder`,
							  p.`qty_onsold`,
  							  pl.`qty_warn`,
							  pl.ProLocationID,
							  pl.pro_id,
							  pl.LocationId,
							  pl.qty,
							  pl.`qty_avaliable`,
							  pl.`qty_onorder` AS plqty_order,
							  pl.`qty_onsold` AS plqty_onsold
							
							FROM
							  sp_product AS p 
							  INNER JOIN sp_prolocation AS pl 
							    ON pl.pro_id = p.pro_id 
							  INNER JOIN sp_sublocation AS lo 
							    ON lo.LocationId = pl.LocationId 
							WHERE p.pro_id =".$id;
    	    	
//     	    	$itemSql = "SELECT lo.Name, p.pro_id, p.cate_id, p.item_name,pl.ProLocationID, pl.pro_id, pl.LocationId, pl.qty,
//     	    				pt.QuantityOnHand,pt.QuantityOnOrder,pt.QuantitySold
//     						FROM tb_product AS p
//     						INNER JOIN tb_prolocation AS pl ON  pl.pro_id  = p.pro_id
//     						INNER JOIN tb_inventorytotal AS pt ON  pt.ProdId  = p.pro_id
//     						INNER JOIN tb_sublocation AS lo ON  lo.LocationId = pl.LocationId
//     						WHERE p.pro_id =".$id;
    	    	if($user["level"]!=1){
    	    		$itemSql.= " AND pl.LocationId = ".$user["location_id"];
    	    	}
    	$rows = $db->fetchAll($itemSql);
    	return $rows;
    }
    //get product info 22/8/13
    public function getProductInfo($id){
    	$db=$this->getAdapter();
    	$sql = "SELECT 
					  p.pro_id,
					  p.cate_id,
					  p.brand_id,
					  p.stock_type,
					  p.item_name,
					  p.item_code,
					  p.item_size,
					  p.purchase_tax,
					  p.sale_tax,
					  measure_id,
					  label,
					  qty_perunit,
					  p.unit_sale_price,
					  qty_onhand,
					  p.photo,
					  p.is_avaliable,
					  p.remark 
					FROM
					  sp_product AS p 
					WHERE p.pro_id =".$db->quote($id)." LIMIT 1";
    	$rows = $db->fetchRow($sql);
    	return ($rows);
    }
    public function getProductInfoDetail($id){//for view item detail
    	$db=$this->getAdapter();
    	$sql = "SELECT p.pro_id,p.cate_id,p.stock_type,p.item_name,p.item_code,p.price_per_qty,p.brand_id,
    	p.photo,p.is_avaliable,p.remark,c.Name,b.Name As branch_name
    	FROM tb_product AS p
    	INNER JOIN sp_category AS c ON c.CategoryID=p.cate_id
    	INNER JOIN sp_branch AS b ON b.branch_id=p.brand_id
    	WHERE p.pro_id=".$id." LIMIT 1";
    	$rows = $db->fetchRow($sql);
    	return ($rows);
    }
    // for get product info 8/22/13
    public function getProductStock($id){
    	$db= $this->getAdapter();
    	$sql_inventory="SELECT qty_onhand, qty_available,qty_onorder,qty_onsold
    	FROM sp_product WHERE pro_id= ".$id." LIMIT 1";
    	$rows = $db->fetchRow($sql_inventory);
    	return $rows;
    }
    //select before 10-7-13
//     public function getSaleHistory($id){
//     	$db= $this->getAdapter();
//     	$sql_sale="SELECT os.history_id, os.type, so.order, os.customer_id, os.date, os.status, os.order_total, os.qty, os.unit_price, os.sub_total, os.customer_id
//     	FROM tb_order_history AS os
//     	LEFT JOIN tb_purchase_order AS po ON os.order = po.order_id
//     	WHERE pro_id=".$id." ORDER BY os.history_id  DESC ";
//     	$rows = $db->fetchAll($sql_sale);
//     	return $rows;
//     }
    // for get product order history 8/22/13
    public function getSaleHistory($id){
    	$db= $this->getAdapter();
    	$sql_sale="SELECT os.history_id, os.type, r.order, cus.cust_name, os.date, os.status, os.order_total, os.qty, os.unit_price, os.sub_total
		 FROM sp_order_history AS os
		 INNER JOIN tb_customer AS cus ON 
		 	cus.customer_id = os.customer_id
		 LEFT JOIN sp_sales_order AS r ON os.order = r.order_id
    	 WHERE pro_id=".$id." ORDER BY os.history_id  DESC ";
    	$rows = $db->fetchAll($sql_sale);
    	return $rows;
    }
    //for select purchase history
    public function getPurchaseHistory($id){
    	$db= $this->getAdapter();
    	$sql_purchase="SELECT ph.history_id, ph.type, pur.order, v.v_name, ph.date, ph.status, ph.order_total, ph.qty, ph.unit_price, ph.sub_total
					FROM sp_purchase_order_history AS ph
					INNER JOIN tb_vendor AS v ON v.vendor_id = ph.customer_id
					LEFT JOIN sp_purchase_order AS pur ON ph.order = pur.order_id
					WHERE pro_id =".$id."
					ORDER BY ph.history_id DESC ";
    	$rows = $db->fetchAll($sql_purchase);
    	return $rows;
    }
    public function moveproduct($id){
    	$db=$this->getAdapter();
    	$user = $this->GetuserInfo();
    	$sql_move = "SELECT h.history_id, h.transaction_type, h.date, l.Name,
    	h.qty_edit, h.qty_before, h.qty_after,h.Remark, u.username FROM tb_move_history AS h
    	INNER JOIN tb_sublocation AS l ON l.LocationId = h.location_id
    	INNER JOIN rsv_acl_user as u ON u.user_id=h.user_mod
    	WHERE pro_id=".$id;
    	if($user["level"]!=1){
    		$sql_move.=" AND h.location_id = ".$user["location_id"];
    	}
    	$sql_move.=" ORDER BY h.history_id DESC ";
    	$rows=$db->fetchAll($sql_move);
    	return $rows;
    }

    public function getProductVendor($id){
    	$db=$this->getAdapter();
    	$sql_move = "SELECT pro_id FROM tb_purchase_order_item
        WHERE pro_id =".$id;
    	$rows=$db->fetchAll($sql_move);
    	return $rows;
    }
    /**
    * Update Order item
    * @param array $itemsData
    * @author May Dara
    */
//     public function UpdateOrderItem($itemsData){
// 	    $db = $this->getAdapter();
// 	    $dataInfo = array(
// 	    'name' => $itemsData['name'],
// 	    'purchase_code' => $itemsData['purchase_code'],
// 	    'status' => $itemsData['status'],
// 	    'description' => $itemsData['description'],
// 	    'stock_id' => $itemsData['stock_id'],
// 	    'vendor_id' => $itemsData['vendor_id'],
// 	    'discount_type' => $itemsData['discount_type'],
// 	    'discount_value' => $itemsData['discount_value'],
// 	    'shipping_id' => $itemsData['shipping_id'],
// 	    'shipping_charge' => $itemsData['shipping_charge'],
// 	    'assign_contact' => $itemsData['assign_contact'],
// 	    'order_date' => $itemsData['order_date'],
// 	    'net_total' => $itemsData['net_total'],
// 	    'all_total' => $itemsData['all_total']
// 	    );
// 	    $where=$this->getAdapter()->quoteInto('id=?',$itemsData['id']);
// 	    $this->update($dataInfo,$where);
	    
// 	    		$this->_name = "rsmk_purchase_item";
// 	    		$this->delete("purchase_id = " . $itemsData['id']);
// 	    		$identitys = explode(',',$itemsData['identity']);
// 	    		foreach($identitys as $i){
// 	    		$data = array(
// 		    		'purchase_id'  => $itemsData['id'],
// 		    		'item_id' 	   => $itemsData['item_id_'.$i],
// 		    		'qty_purchase' => $itemsData['qty'.$i],
// 		   			'price'        => $itemsData['price'.$i],
// 			    	'dis-type'     => $itemsData['dis-type-'.$i],
// 			    	'dis-value'    => $itemsData['dis-value'.$i]
// 	    	);
// 	    	$this->insert($data);
// 	    }
//     }
	public function getTransferInfo($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM sp_stocktransfer WHERE transfer_id = ".$id." LIMIT 1";
		$row = $db->fetchRow($sql);
		return $row;
	}
	public function getTransferItem($id){
		$db=$this->getAdapter();
		$sql = "SELECT * FROM sp_transfer_item WHERE transfer_id = ".$id;
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	public function getAddProduct(){
		$db=$this->getAdapter();
		$sql =" SELECT item_name , item_code , barcode , cate_id , brand_id , color_id , measure_id , price , photo , unit_label , qty_perunit , user_id , note , STATUS , qty  FROM `sp_product`";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getCategoryName(){
		$db=$this->getAdapter();
		$sql="SELECT id , category_name FROM `sp_category`";
		return $db->fetchAll($sql);
	}
	
    
}