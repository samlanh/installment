<?php
class Dailywork_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_dailycategory';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){
	  $this->_name='tb_dailycategory';
	  $_arr = array(
	    'name'=>$_data['title'],
	    'status'=>$_data['category'],
	    
	    );
	  $this->insert($_arr);// insert data into database
 }//add product
 
 public function updatecategoryById($_data){
 	$_arr = array(
 			'name'=>$_data['title'],
 			'status'=>$_data['category'],
 	);
 	$where = "id =".$_data['id'];
 	$this->update($_arr, $where);
 }// updateproductById
 
 public function getcategorytById($id)
 {
 	$db = $this->getAdapter();
 	$sql = "SELECT id , name , status FROM tb_dailycategory WHERE id = $id";
 	return $db->fetchRow($sql);
 }// getproduct1ById
 
public  function getCategory($search=null){
  $db = $this->getAdapter();
  $sql = "SELECT id, name  ,
	(SELECT `name_en` FROM `tb_view` WHERE `tb_view`.`key_code`=`tb_dailycategory`.`status` AND type=5) AS  status
 	FROM `tb_dailycategory` WHERE 1 ";
  $where = '';
  if(!empty($search["adv_search"])){
  	$s_where=array();
  	$s_search = addslashes(trim($search['adv_search']));
  	$s_where[]= " name LIKE '%{$s_search}%'";
  	$where.=' AND ('.implode(' OR ', $s_where).')';
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
	
	
    
}