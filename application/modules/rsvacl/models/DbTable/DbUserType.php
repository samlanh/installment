<?php

class RsvAcl_Model_DbTable_DbUserType extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_acl_user_type';
	//function for getting record user_type by user_type_id
	public function getUserType($user_id)
	{
		$select=$this->select();		
		$select->where('user_type_id=?',$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row->toArray();
	}
	//get user name
	public function getUserTypeName($user_id)
	{
		$select=$this->select();
		$select->from($this,'user_type')
			->where("user_type_id=?",$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return null; 
		return $row['user_type'];
	}
	//get infomation of user
	public function getUserTypeInfo($sql)
	{
		$db=$this->getAdapter();  		
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	//function get user id from database
	public function getUserTypeID($username)
	{
		$select=$this->select();
			$select->from($this,'user_type_id')
			->where('user_type=?',$username);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['user_type_id'];
	}
	//function check user have exist
	public function isUserTypeExist($username)
	{
		$select=$this->select();
		$select->from($this,'user_type')
			->where("user_type=?",$username);
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
	public function insertUserType($arr)
	{
		$data=array(); 
		/*foreach($arr as $key=>$value){
     		if(!($key=='confirm_password' ||
     			 $key=='password' ||
     		     $key=='user_level_id' ||
     		     substr($key, 0,11)=='division_id' ||
     		     $key=='save'
     		  ) 	 
     		){     		
     			$data[$key]=$value;
     		}     	
     	}	*/
		$data['user_type']=$arr['user_type'];  
		$data['parent_id']=$arr['parent_id'];  	
     	$data['status']='1';
     	//print_r($data);exit;
    	return $this->insert($data); 
	}	
	//update user
	public function updateUserType($arr,$user_type_id)
	{
		//print_r($arr); exit;
		$data=array(); 	
		//Sophen commented on 17 May 2012   	
    /* 	foreach($arr as $key=>$value){
     		if(!($key=='confirm_password' ||
     			 $key=='password' ||
     		     $key=='user_level_id' ||
     		     substr($key, 0,11)=='division_id' ||
     		     $key=='save'
     		  ) 	 
     		){     		
     			$data[$key]=$value;
     			
     		}     
     		
     	}*/	
		//Sophen add here
		$data['user_type']=$arr['user_type'];   
		$data['parent_id']=$arr['parent_id']; 	
    	$where=$this->getAdapter()->quoteInto('user_type_id=?',$user_type_id);
		$this->update($data,$where); 
	}
	
	public function updateUserTypeAccess($arr,$user_type_id)
	{
		//print_r($arr); exit;
		$data=array(); 	  		
        $data['user_type']=$arr;	
        //echo $data['user_type'].$user_type_id; exit;
    	$where=$this->getAdapter()->quoteInto('user_type_id=?',$user_type_id);
		$this->update($data,$where); 
	}
	//function dupdate field status user to force use become inaction
	public function inactiveUser($user_id)
	{
		$data=array('status'=>0);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	public function getAlluserType(){
		$db = $this->getAdapter();
		$sql = "SELECT u.user_type_id,u.user_type,
		(SELECT u1.user_type FROM `rms_acl_user_type` u1 WHERE u1.user_type_id = u.parent_id LIMIT 1)
		 parent_id, status FROM `rms_acl_user_type` u ";
		return $db->fetchAll($sql);
	}
}

