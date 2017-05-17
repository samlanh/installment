<?php
class Dailywork_Model_DbTable_DbReport extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_dailywork';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){
	  $this->_name='tb_dailywork';
	  $_arr = array(
	    'work'=>$_data['work'],
	    'date'=>$_data['start_date'],
	  	'createdate'=>date("Y-m-d"),
	    'user'=>$_data['user'],
	  	'status'=>$_data['status'],
	  	'description'=>$_data['description'],
	    );
	  $this->insert($_arr);// insert data into database
 }//add product
 
 public function updatedailyworkById($_data){
 //print_r($_data); exit();
 	$_arr = array(
 				
 			'work'=>$_data['work'],
 	);
 	$where = "id =".$_data['id'];
 	$this->update($_arr, $where);
 	
 }// updateproductById
 
 public function getdailyById($id)
 {
 	$db = $this->getAdapter();
 	$sql = "SELECT  `work` , `date` , `user` FROM tb_dailywork WHERE id=$id ";
 	return $db->fetchRow($sql);
 }// getproduct1ById
 
public  function getDailywork($search){
  $db = $this->getAdapter();
  $sql = "SELECT id, work , date ,user, projectname,description ,status, 
	(SELECT first_name FROM `rms_users`
	WHERE`rms_users`.`id`=`tb_dailywork`.`user`) 
	AS `user`
 	FROM `tb_dailywork` WHERE 1 ";
  
  if(!empty($search["user"])){
  	$where.=' AND user='.$search["user"];
  }
  
  if(!empty($search["projectname"])){
  	$where.=' AND projectname='.$search["projectname"];
  }
  if($search["status_dailywork"]!=-1){
  	$where.=' AND status='.$search["status_dailywork"];
  }
  $from_date =(empty($search['start_date']))? '1': "date >= '".$search['start_date']." 00:00:00'";
  $to_date = (empty($search['end_date']))? '1': "date <= '".$search['end_date']." 23:59:59'";
  $where.= " AND ".$from_date." AND ".$to_date;
 // echo $sql.$where;
  return $db->fetchAll($sql.$where);
 }
  
  
  function getUser(){
  	$db = $this->getAdapter();
  	$sql ="SELECT u.id,u.`first_name` FROM `rms_users` AS u";
  	return $db->fetchAll($sql);
  }
  
  function getCategory(){
  	$db = $this->getAdapter();
  	$sql ="SELECT id,name FROM `tb_category`";
  	return $db->fetchAll($sql);
  }
  
  function getProjectName(){
  	$db = $this->getAdapter();
  	$sql ="SELECT id,projectname FROM `tb_project`";
  	return $db->fetchAll($sql);
  }
  
  function getCategoryname(){
  	$db=$this->getAdapter();
  	$sql="SELECT id,name FROM tb_category
  	WHERE name!='' AND status=1 ";
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
	public function getAddProduct(){
		$db=$this->getAdapter();
		$sql =" SELECT item_name , item_code , barcode , cate_id , brand_id , color_id , measure_id , price , photo , unit_label , qty_perunit , user_id , note , STATUS , qty  FROM `sp_product`";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	function getCate(){
		$db=$this->getAdapter();
		$sql="SELECT id , category_name FROM `sp_category`";
		return $db->fetchAll($sql);
	}
	function getReportDailywork($search){
		
		$db=$this->getAdapter();
		//print_r($search);
		$sql="SELECT id, `work` , `date` ,`description`, `user` ,`status`, 
	(SELECT first_name FROM `rms_users`
	WHERE`rms_users`.`id`=`tb_dailywork`.`user`)
	AS `user`,
	(SELECT projectname FROM `tb_project` 
	WHERE `tb_project`.`id`=`tb_dailywork`.`projectname`)
	AS `projectname`,
	(SELECT `name` FROM `tb_view` WHERE `tb_view`.`code`=`tb_dailywork`.`status` AND TYPE=2) AS  status
 	FROM `tb_dailywork` WHERE 1";	
		$where='';
		if(!empty($search["adv_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]= " work LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		
		$from_date =(empty($search['start_date']))? '1': "date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;
		
		if($search["status_dailywork"]!=-1){
			$where.=' AND status='.$search["status_dailywork"];
		}
		 if(!empty($search["user"])){
			$where.=' AND user='.$search["user"];
		}
		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
  function getReportContact($search){
  	$db=$this->getAdapter();
  	//print_r($search); exit();
  	$sql="SELECT id, contactname , phone ,date, note ,
	(SELECT NAME FROM tb_category
	WHERE`tb_category`.`id`=`tb_contact`.`category`) 
	AS `category`
 	FROM `tb_contact` WHERE 1 ";
  	$where='';
  	if(!empty($search["adv_search"])){
  		$s_where=array();
  		$s_search = addslashes(trim($search['adv_search']));
  		$s_where[]="contactname LIKE '%{$s_search}%'";
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
  function getReportAddress($search){
  	$db=$this->getAdapter();
  	$sql="SELECT id , contactname , contactnumber,
  
  (SELECT NAME FROM tb_category
	WHERE`tb_category`.`id`=`tb_address`.`category`) 
	AS `category`
  
  ,datecreat, address ,note , 
	(SELECT NAME FROM `tb_view`
	WHERE `tb_view`.`id`=`tb_address`.`wherenote`) 
	AS `wherenote` ,(SELECT NAME FROM `tb_status` WHERE `tb_status`.`id`=`tb_address`.`status`) AS 
 	STATUS FROM tb_address WHERE 1 ";
  	$where='';
  	if(!empty($search["adv_search"])){
  		$s_where=array();
  		$s_search = addslashes(trim($search['adv_search']));
  		$s_where[]="contactname LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  	
  	}
  	if(!empty($search["notewhere"])){
  		$where.=' AND wherenote='.$search["notewhere"];
  	}
  	if(!empty($search["status"])){
  		$where.=' AND status='.$search["status"];
  	}
  	if(!empty($search["categoryname"])){
  		$where.=' AND category='.$search["categoryname"];
  	}
  	//echo $sql.$where;
  	return $db->fetchAll($sql.$where);
  	
  }
  function getReportProject($search){
  	$db=$this->getAdapter();
  	$sql=" SELECT id, projectname ,date, 
	(SELECT NAME FROM `tb_view`
	WHERE`tb_view`.`code`=`tb_project`.`status` AND TYPE=2) 
	AS `status`
 	FROM `tb_project` WHERE 1";
  	
  	$where='';
  	if(!empty($search["adv_search"])){
  		$s_where=array();
  		$s_search = addslashes(trim($search['adv_search']));
  		$s_where[]="projectname LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  		 
  	}
  	$from_date =(empty($search['start_date']))? '1': "date >= '".$search['start_date']." 00:00:00'";
  	$to_date = (empty($search['end_date']))? '1': "date <= '".$search['end_date']." 23:59:59'";
  	$where.= " AND ".$from_date." AND ".$to_date;
  	if($search["status_project"]!=-1){
  		$where.=' AND status='.$search["status_project"];
  	}
  	echo $sql.$where;
  	return $db->fetchAll($sql.$where);
  }
  
  
  function getReportWorkResult($search){
  	$db=$this->getAdapter();
  	//print_r($search); exit();
  	$sql="SELECT id, name , email,result FROM `tb_result` WHERE 1";
  	$where='';
  if(!empty($search["adv_search"])){
  		$s_where=array();
  		$s_search = addslashes(trim($search['adv_search']));
  		$s_where[]="name LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  	}
  	//echo $sql.$where;
  	return $db->fetchAll($sql.$where);
  	
  }
  
  function getReportWorkComplete($search){
  	$db=$this->getAdapter();
  	//print_r($search);exit();
  	$sql="SELECT id,workcompleted , noteandidea FROM `tb_workcomplete` WHERE 1";
  	$where='';
  	if(!empty($search["adv_search"])){
  		$s_where=array();
  		$s_search = addslashes(trim($search['adv_search']));
  		$s_where[]="workcompleted LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  	}
  	//echo $sql.$where;
  	return $db->fetchAll($sql.$where);
  	
  }
  
  function getReportCategory($search){
  	$db=$this->getAdapter();
  	$sql="SELECT id, name ,
	(SELECT NAME FROM `tb_view`
	WHERE`tb_view`.`code`=`tb_category`.`status` AND TYPE=2) 
	AS `status`
 	FROM `tb_category` WHERE 1 ";
  	$where='';
  	if(!empty($search["adv_search"])){
  		$s_where=array();
  		$s_search = addslashes(trim($search['adv_search']));
  		$s_where[]="name LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  	}
  	if($search["status"]!=-1){
  		$where.=' AND status='.$search["status_category"];
  	}
  	//echo $sql.$where;
  	return $db->fetchAll($sql.$where);
  }
}