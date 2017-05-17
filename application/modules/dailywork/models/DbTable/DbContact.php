<?php
class Dailywork_Model_DbTable_DbContact extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_contact';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){
	  $this->_name='tb_contact';
	  $_arr = array(
	    'contactname'=>$_data['name'],
	    'phone'=>$_data['phone'],
	    'date'=>$_data['start_date'],
	  	'cratedate'=>date("Y-m-d"),
	  	'note'=>$_data['note'],
	  	'category'=>$_data['category'],
	    );
	  $this->insert($_arr);// insert data into database
 }//add product
 
 public function updatecontactById($_data){
 //print_r($_data); exit();
 	$_arr = array(
 				
 			'contactname'=>$_data['name'],
 			'phone'=>$_data['phone'],
 			'note'=>$_data['note'],
 			'category'=>$_data['category'],
 	);
 	$where = "id =".$_data['id'];
 	$this->update($_arr, $where);
 
 }// updateproductById
 
 public function getcontactById($id)
 {
 	$db = $this->getAdapter();
 	$sql = "SELECT id , contactname , phone , date , note , category FROM tb_contact WHERE id = $id";
 	return $db->fetchRow($sql);
 }// getproduct1ById
public  function getContact($search=null){
  $db = $this->getAdapter();
  $sql = "SELECT id, contactname , phone ,date, note ,
	(SELECT NAME FROM tb_dailycategory
	WHERE`tb_dailycategory`.`id`=`tb_contact`.`category`) 
	AS `category`
 	FROM `tb_contact` WHERE 1 ";
  $where = '';
  if(!empty($search["adv_search"])){
  	$s_where=array();
  	$s_search = addslashes(trim($search['adv_search']));
  	$s_where[]= " contactname LIKE '%{$s_search}%'";
  	$where.=' AND ('.implode(' OR ', $s_where).')';
  }
  $from_date =(empty($search['start_date']))? '1': "date >= '".$search['start_date']." 00:00:00'";
  $to_date = (empty($search['end_date']))? '1': "date <= '".$search['end_date']." 23:59:59'";
  $where.= " AND ".$from_date." AND ".$to_date;
  if(!empty($search["category"])){
  	$where.=' AND category='.$search["category"];
  }
 //echo $sql.$where;
  return $db->fetchAll($sql.$where);
 }
	function getCategoryname(){
		$db=$this->getAdapter();
		$sql="SELECT id,name FROM tb_dailycategory
	WHERE name!='' AND status=1 ";
		return $db->fetchAll($sql);
	}
	function getSelectCategory(){
		$db=$this->getAdapter();
		$sql="SELECT id,name FROM tb_dailycategory WHERE 1 ";
		return $db->fetchAll($sql);
		
	}
	
    
}