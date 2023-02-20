<?php

class Product_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'ldc_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    
    }
    
    // function checkItemsTypeId($category){
    // 	$db = $this->getAdapter();
    // 	$sql="SELECT d.* FROM ln_properties_type AS d WHERE d.type_nameen = '$category' LIMIT 1";
    // 	return $db->fetchRow($sql);
    // }
    
    public function ProductByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();

    	for($i=1; $i<=$count; $i++){

			$sql=" SELECT * FROM `st_category` WHERE categoryName = '".$data[$i]['B']." ' ";
			$category =  $db->fetchRow($sql);
			
			if(!empty($category)){

				$cateId= $category['id'];   
			}else{
				
				$_arr=array(
						//'id'       => $data[$i]['A'],
						'categoryName'       => $data[$i]['B'],
						'status'      => 1,
						'createDate' 	=> date("Y-m-d H:i:s"),
						'userId'	 	=> $this->getUserId()
				);
				$this->_name = "st_category";
				$cateId =  $this->insert($_arr);

			}

			$sql=" SELECT * FROM `st_measure` WHERE name = '".$data[$i]['D']." ' ";
			$measure =  $db->fetchRow($sql);
			
			if(!empty($measure)){

				$measureId= $measure['id'];   
			}else{
				
				$_arr=array(
						
						'name'       => $data[$i]['D'],
						'status'     => 1,
						'date'     	=> date("Y-m-d H:i:s"),
						'user_id'	 => $this->getUserId()
				);
				$this->_name = "st_measure";
				$measureId =  $this->insert($_arr);

			}

			// $_arr=array(
				
			// 	'proName'       => $data[$i]['C'],
			// 	'categoryId'    => $cateId,
			// 	'status'     	=> 1,
			// 	'createDate' 	=> date("Y-m-d H:i:s"),
			// 	'userId'	 	=> $this->getUserId()
			// );
			// $this->_name = "st_product";
			// $proId =  $this->insert($_arr);

    	}
    }
}   

