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

			$sql=" SELECT id FROM `st_category` WHERE categoryName = '".$data[$i]['B']." ' ";
			$cateId =  $db->fetchOne($sql);
			
			if(empty($cateId)){
					$_arr=array(
						
						'categoryName'       => $data[$i]['B'],
						'status'      => 1,
						'createDate' 	=> date("Y-m-d H:i:s"),
						'userId'	 	=> $this->getUserId()
				);
				$this->_name = "st_category";
				$cateId =  $this->insert($_arr);
			}

			$sql=" SELECT id FROM `st_measure` WHERE name = '".$data[$i]['D']." ' ";
			$measureId =  $db->fetchOne($sql);
			
			if(empty($measureId)){
				$_arr=array(
						
						'name'       => $data[$i]['D'],
						'status'     => 1,
						'date'     	=> date("Y-m-d H:i:s"),
						'user_id'	 => $this->getUserId()
				);
				$this->_name = "st_measure";
				$measureId =  $this->insert($_arr);

			}
			if (!empty($data[$i]['E'])){
			

				$isCountStock = ($data[$i]['H']=="មិនរាប់")?0:1;

				$_arr=array(

					'proName'     	  => $data[$i]['C'],
					'categoryId'  	  => $cateId,
					'measureId'   	 => $measureId,
					'isCountStock'   => $isCountStock,
					'budgetId'   	 => 1,
					'createDate' 	=> date("Y-m-d H:i:s"),
					'userId'	 	=> $this->getUserId()
				);
				$this->_name = "st_product";
				$proId =  $this->insert($_arr);

			}
			if (!empty($data[$i]['F'])){
			

				$isCountStock = ($data[$i]['H']=="មិនរាប់")?0:1;

				$_arr=array(

					'proName'     	  => $data[$i]['C'],
					'categoryId'  	  => $cateId,
					'measureId'   	 => $measureId,
					'isCountStock'   => $isCountStock,
					'budgetId'   	 => 2,
					'createDate' 	=> date("Y-m-d H:i:s"),
					'userId'	 	=> $this->getUserId()
				);
				$this->_name = "st_product";
				$proId =  $this->insert($_arr);

			}

			if (!empty($data[$i]['G'])){
			

				$isCountStock = ($data[$i]['H']=="មិនរាប់")?0:1;

				$_arr=array(

					'proName'     	  => $data[$i]['C'],
					'categoryId'  	  => $cateId,
					'measureId'   	 => $measureId,
					'isCountStock'   => $isCountStock,
					'budgetId'   	 => 3,
					'createDate' 	=> date("Y-m-d H:i:s"),
					'userId'	 	=> $this->getUserId()
				);
				$this->_name = "st_product";
				$proId =  $this->insert($_arr);

			}

    	}
    }
}   

