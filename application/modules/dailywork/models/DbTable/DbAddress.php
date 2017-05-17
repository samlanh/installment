<?php
class Dailywork_Model_DbTable_DbAddress extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_address';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){
	  $this->_name='tb_address';
	  $_arr = array(
	    	'contactname'=>$_data['name'],
	    	'contactnumber'=>$_data['phone'],
	  		'category'=>$_data['category'],
	  		'address'=>$_data['address'],
	  		'note'=>$_data['note'],
	  		'wherenote'=>$_data['note'],
	  		'status'=>$_data['status'],
	  		'datecreat'=>date('Y-m-d'),
	    );
	  $this->insert($_arr);// insert data into database
 }//add product
 
 public function updateaddressById($_data){
 //print_r($_data); exit();
 	$_arr = array(
 				
 			'contactname'=>$_data['name'],
 			'contactnumber'=>$_data['phone'],
 			'wherenote'=>$_data['notewhere'],
 			'category'=>$_data['category'],
 	);
 	$where = "id =".$_data['id'];
 	$this->update($_arr, $where);
 
 }// updateproductById
 
 public function getaddressById($id)
 {
 	$db = $this->getAdapter();
 	$sql = "SELECT id , contactname , contactnumber , address ,note ,wherenote,
 	status  FROM tb_address WHERE id = $id";
 	return $db->fetchRow($sql);
 }// getproduct1ById
 
 
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
    
public  function getAddress($search=null){
  $db = $this->getAdapter();
  $sql = "SELECT id , contactname , contactnumber,
  
  (SELECT name FROM tb_dailycategory
	WHERE`tb_dailycategory`.`id`=`tb_address`.`category`) 
	AS `category`
  
  ,datecreat, address ,note , 
	(SELECT name_en FROM `tb_view`
	WHERE `tb_view`.`id`=`tb_address`.`wherenote`) AS `wherenote` ,
(SELECT `name_en` FROM `tb_view` WHERE `tb_view`.`key_code`=tb_address.`status` AND type=5) AS  status
  FROM tb_address WHERE 1 ";
  $where = '';
  if(!empty($search["adv_search"])){
  	$s_where=array();
  	$s_search = addslashes(trim($search['adv_search']));
  	$s_where[]= " contactname LIKE '%{$s_search}%'";
  	$where.=' AND ('.implode(' OR ', $s_where).')';
  }
  if(!empty($search["category"])){
  	$where.=' AND category='.$search["category"];
  }
 // echo $sql.$where;
  return $db->fetchAll($sql.$where);
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
   
	public function getAddProduct(){
		$db=$this->getAdapter();
		$sql =" SELECT item_name , item_code , barcode , cate_id , brand_id , color_id , measure_id , price , photo , unit_label , qty_perunit , user_id , note , STATUS , qty  FROM `sp_product`";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getCategoryName(){
		$db=$this->getAdapter();
		$sql="SELECT id , name FROM `tb_dailycategory`";
		return $db->fetchAll($sql);
	}
	
    
}