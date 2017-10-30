<?php
class Dailywork_Model_DbTable_DbDailywork extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_dailywork';
    public function setName($name)
    {
    	$this->_name=$name;
    }
	
	public function addTask($_data){
	  $this->_name='tb_project';
	  $_arr = array(
	    'projectname'=>$_data['task'],
	    'date'=>date("Y-m-d",strtotime($_data['start_date'])),
	  	'createdate'=>date("Y-m-d"),
	    'status'=>$_data['status'],
	    );
	  return $this->insert($_arr);// insert data into database
 }//add product
	 public function getUserInfo(){
    	$session_user=new Zend_Session_Namespace('auth');
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$level = $session_user->level;
    	$location_id = $session_user->location_id;
    	$info = array("user_name"=>$userName,"user_id"=>$GetUserId,"level"=>$level,"branch_id"=>$location_id);
    	return $info;
    }
    public function add($_data){
	  $this->_name='tb_dailywork';
	  $_arr = array(
	    'work'=>$_data['work'],
	  	//'customer_id'=>$_data['customer_id'],
	    'date'=>date("Y-m-d",strtotime($_data['start_date'])),
	  	'createdate'=>date("Y-m-d"),
	    'user'=>$_data['user'],
	  	'projectname'=>$_data['projectname'],
	  	'status'=>$_data['status'],
	  	'description'=>$_data['description'],
	    );
	  $this->insert($_arr);// insert data into database
 }//add product
 
 public function updatedailyworkById($_data){
 	$_arr = array(
 			'work'=>$_data['work'],
 			//'customer_id'=>$_data['customer_id'],
 			'date'=>date("Y-m-d",strtotime($_data['start_date'])),
 			'user'=>$_data['user'],
 			'status'=>$_data['status'],
 			'description'=>$_data['description'],
 			'projectname'=>$_data['projectname'],
 	);
 	$where = "id =".$_data['id'];
 	$this->update($_arr, $where);
 }// updateproductById
 
 public function getdailyById($id)
 {
 	$db = $this->getAdapter();
 	$sql = "SELECT  * FROM tb_dailywork WHERE id=$id ";
 	return $db->fetchRow($sql);
 }// getproduct1ById
 
 
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
public  function getDailywork($search=null){
  $db = $this->getAdapter();
  $result = $this->getUserInfo();
  $sql = "SELECT id, work ,
	
	(SELECT projectname FROM `tb_project`
     WHERE`tb_project`.`id`=`tb_dailywork`.`projectname` limit 1) AS `projectname`,
   	date ,user, description ,
	(SELECT `name_en` FROM `tb_view` WHERE `tb_view`.`key_code`=`tb_dailywork`.`status` AND type=5) AS  status, 
	(SELECT first_name FROM `rms_users` WHERE rms_users.id=`tb_dailywork`.`user`) AS `user`
 	FROM `tb_dailywork` WHERE 1 ";
  $where = '';
  if(!empty($search["adv_search"])){
  	$s_where=array();
  	$s_search = addslashes(trim($search['adv_search']));
  	$s_where[]= " work LIKE '%{$s_search}%'";
  	$s_where[]= " date LIKE '%{$s_search}%'";
  	$s_where[]= " description LIKE '%{$s_search}%'";
  	$where.=' AND ('.implode(' OR ', $s_where).')';
  }
  if(!empty($search["user"])){
  	$where.=' AND user='.$search["user"];
  }
  
  if(!empty($search["projectname"])){
  	$where.=' AND projectname='.$search["projectname"];
  }
  $from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
  $to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
  $where .= " AND ".$from_date." AND ".$to_date;
  if($result['level']==1){
			$where.='';
		}
		else{
			$where.= " AND  user=".$result['user_id'];
			//return $result;
		}
  $where.=" ORDER BY id DESC";
  return $db->fetchAll($sql.$where);
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
	
	function getProjectName(){
		$db = $this->getAdapter();
		$sql ="SELECT id,projectname FROM `tb_project` WHERE status=1 AND projectname!='' ORDER BY id DESC ";
		return $db->fetchAll($sql);
	}
	function getworkbyId($work_id){
		$db = $this->getAdapter();
		$sql ="SELECT * FROM `tb_dailywork` WHERE id= $work_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function addprocesswork($_data){
		$this->_name='tb_dailyworkprocess';
		$_arr = array(
				'work_id'=>$_data['work'],
				'customer_id'=>$_data['customer_id'],
				'date'=>date("Y-m-d",strtotime($_data['start_date'])),
				'createdate'=>date("Y-m-d"),
				'user'=>$_data['user'],
				'project_id'=>$_data['projectname'],
				'work_status'=>$_data['work_status'],
				'description'=>$_data['description'],
		);
		$this->insert($_arr);// insert data into database
	}//add product
	
	public function edittprocesswork($_data){
		$this->_name='tb_dailyworkprocess';
		$_arr = array(
				'work_id'=>$_data['work'],
//				'customer_id'=>$_data['customer_id'],
				'date'=>date("Y-m-d",strtotime($_data['start_date'])),
				'createdate'=>date("Y-m-d"),
				'user'=>$_data['user'],
				'project_id'=>$_data['projectname'],
				'work_status'=>$_data['work_status'],
				'description'=>$_data['description'],
		);
		$where = "id="."'".$_data['id']."'";
		$this->update($_arr,$where);// insert data into database
	}
	
	public function addworkcompleted($_data){
		$this->_name='tb_dailyworkprocess';
		$_arr = array(
				'work_id'=>$_data['work'],
				//'customer_id'=>$_data['customer_id'],
				'date'=>date("Y-m-d",strtotime($_data['start_date'])),
				'createdate'=>date("Y-m-d"),
				'user'=>$_data['user'],
				'project_id'=>$_data['projectname'],
				'work_status'=>$_data['work_status'],
				'description'=>$_data['description'],
		);
		$this->insert($_arr);// insert data into database
	}//add product
	
	public function editworkcompleted($_data){
		$this->_name='tb_dailyworkprocess';
		$_arr = array(
				'work_id'=>$_data['work'],
				//'customer_id'=>$_data['customer_id'],
				'date'=>date("Y-m-d",strtotime($_data['start_date'])),
				'createdate'=>date("Y-m-d"),
				'user'=>$_data['user'],
				'project_id'=>$_data['projectname'],
				'work_status'=>$_data['work_status'],
				'description'=>$_data['description'],
		);
		$where = "id="."'".$_data['id']."'";
		$this->update($_arr,$where);// insert data into database
	}
	public  function getDailyworkProcess($search=null){
		$db = $this->getAdapter();
		$result = $this->getUserInfo();
		$sql = "SELECT id,(SELECT work FROM `tb_dailywork` WHERE id=work_id) AS work_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=tb_dailyworkprocess.customer_id LIMIT 1 ) AS customer_name,
		(SELECT projectname FROM `tb_project`
		WHERE`tb_project`.`id`=`tb_dailyworkprocess`.`project_id` LIMIT 1) AS `projectname`,
		date ,
		(SELECT first_name FROM `rms_users` WHERE rms_users.id=`tb_dailyworkprocess`.`user`) AS `user`,
		description ,
		(SELECT name_en FROM `tb_view` WHERE type=11 AND key_code=work_status LIMIT 1) as work_status
		FROM `tb_dailyworkprocess` WHERE 1 ";
		$where = '';
		if(!empty($search["adv_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]= " work LIKE '%{$s_search}%'";
			$s_where[]= " date LIKE '%{$s_search}%'";
			$s_where[]= " description LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search["user"])){
			$where.=' AND user='.$search["user"];
		}
	
		if(!empty($search["projectname"])){
			$where.=' AND projectname='.$search["projectname"];
		}
		if($search["status"]!=""){
			$where.=' AND work_status='.$search["status"];
		}else{
			$where.=' AND work_status IN(2,3)';
		}
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;
		if($result['level']==1){
			$where.='';
		}
		else{
			$where.= " AND  user=".$result['user_id'];
			//return $result;
		}
		$where.=" ORDER BY id DESC";
		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	
	public  function getDailyworkCompleted($search=null){
		$db = $this->getAdapter();
		$result = $this->getUserInfo();
		$sql = "SELECT id,
		(SELECT work FROM `tb_dailywork` WHERE id=work_id) AS work_name,
		(SELECT projectname FROM `tb_project`
		WHERE`tb_project`.`id`=`tb_dailyworkprocess`.`project_id` LIMIT 1) AS `projectname`,
		date ,
		(SELECT first_name FROM `rms_users` WHERE rms_users.id=`tb_dailyworkprocess`.`user`) AS `user`,
		description ,
		(SELECT name_en FROM `tb_view` WHERE type=11 AND key_code=work_status LIMIT 1) as work_status
		FROM `tb_dailyworkprocess` WHERE work_status=1 ";
		$where = '';
		if(!empty($search["adv_search"])){
			$s_where=array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]= " work LIKE '%{$s_search}%'";
			$s_where[]= " date LIKE '%{$s_search}%'";
			$s_where[]= " description LIKE '%{$s_search}%'";
			$where.=' AND ('.implode(' OR ', $s_where).')';
		}
		if(!empty($search["user"])){
			$where.=' AND user='.$search["user"];
		}
	
		if(!empty($search["projectname"])){
			$where.=' AND projectname='.$search["projectname"];
		}
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where .= " AND ".$from_date." AND ".$to_date;
		if($result['level']==1){
 		
		}
		else{
			$where.= " AND  user=".$result['user_id'];
			//return $result;
		}
		$where.=" ORDER BY id DESC";
		return $db->fetchAll($sql.$where);
	}
	function getDailyworkProcessById($id){
		$db = $this->getAdapter();
		$sql ="SELECT * FROM `tb_dailyworkprocess` WHERE id=$id ";
		return $db->fetchRow($sql);
	}
	function getAllDailywork(){
		$db = $this->getAdapter();
		$result = $this->getUserInfo();
		$sql ="SELECT id,work FROM `tb_dailywork` WHERE work!='' ";
		if($result['level']==1){
 		
		}
		else{
			$sql.= " AND  user=".$result['user_id'];
			//return $result;
		}
		return $db->fetchAll($sql);
	}
	
}