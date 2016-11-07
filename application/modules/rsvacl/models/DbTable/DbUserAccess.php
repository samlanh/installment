<?php 

class RsvAcl_Model_DbTable_DbUserAccess extends Zend_Db_Table_Abstract
{
	protected  $_name = "rms_acl_user_access";
	
	public function getUserAccess($id)
	{
		$db=RsvAcl_Model_DbTable_DbUserAccess::getAdapter();  
		$sql = "SELECT ua.id,ut.user_type, CONCAT(acl.module,'/', acl.controller,'/', acl.action) AS user_access, ua.status FROM rsv_acl_user_access AS ua 
					        INNER JOIN rms_acl_user_type AS ut ON (ua.user_type_id = ut.user_type_id)
					        INNER JOIN rms_acl_acl AS acl ON (acl.acl_id = ua.acl_id) WHERE ua.id =".$id;		
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	//get user name
	public function getUserName($user_id)
	{
		$select=$this->select();
		$select->from($this,'username')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return null; 
		return $row['username'];
	}
	//change password user wanted
	public function changePassword($user_id,$password)
	{
		$data=array('password'=>$password);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	//is valid password
	public function isValidCurrentPassword($user_id,$current_password)
	{
		$select=$this->select();
		$select->from($this,'password')
			->where("user_id=?",$user_id);
		$row=$this->fetchRow($select);
		if($row){
			$current_password=md5($current_password);
			$password=$row['password'];			 
			if($password==$current_password) return true;
		}
		return false;
	}
	//get infomation of user access
	public function getUserAccessInfo($sql)
	{
		$db=RsvAcl_Model_DbTable_DbUserAccess::getAdapter();  		
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	//function get user id from database
	public function getUserID($username)
	{
		$select=$this->select();
			$select->from($this,'user_id')
			->where('username=?',$username);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['user_id'];
	}
	//function check user have exist
	public function isUserExist($username)
	{
		$select=$this->select();
		$select->from($this,'username')
			->where("username=?",$username);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//function check id number have exist
	public function isIdNubmerExist($id_number)
	{
		$select=$this->select();
		$select->from($this,'id_number')
			->where("id_number=?",$id_number);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//add user
	public function insertUserAccess($arr)
	{
		$data=array(); 
     	$data['user_type_id']=$arr['user_type_id'];
     	$data['acl_id']=$arr['acl_id'];
     	$data['status']='1';
    	return $this->insert($data); 
	}	
	//update user
	public function updateUserType($arr,$id)
	{
		$data=array(); 	
		//Sophen add here
		$data['user_type_id']=$arr['user_type_id'];  
		$data['acl_id']=$arr['acl_id']; 
		$data['status']='1';    	
    	$where=$this->getAdapter()->quoteInto('id=?',$id);
		$this->update($data,$where); 
	}
	
    //Function admin assign group to module/controller/action    
    public  function assignAcl($arr,$user_type_id,$counter){
    	foreach ($arr as $k => $value){
    		if($k == 'user_type' || $k == "save") continue;    		
    		$data=array('acl_id'=>$value, 'user_type_id'=>$user_type_id);
    		    		
    		if(empty($value)){
    			$acl_id = split("_", $k); 
    			$where="user_type_id='".$user_type_id."' AND acl_id='". $acl_id[(count($acl_id) -1)] . "'";
    			$this->delete($where);
    		}
    		elseif(! $this->checkExistAcl($data)){    
    			$this->insert($data);    			
    		}    		
    	}
    } 
    
    protected function checkExistAcl($data){
    	$sql = "SELECT id FROM ". $this->_name . " WHERE user_type_id='".$data['user_type_id']."' AND acl_id='". $data['acl_id'] . "'";
    	
    	$row=$this->getAdapter()->fetchOne($sql);
    	if(!empty($row)) return true;
    	return false;
    }
}
?>
