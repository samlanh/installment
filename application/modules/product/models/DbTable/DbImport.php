<?php

class Product_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'st_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    
    }
    
    public function ProductByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();

    	for($i=1; $i<=$count; $i++){

			if(empty($data[$i]['B']) OR empty($data[$i]['C'])){
				continue;
			}

			$sql=" SELECT id FROM `st_category` WHERE categoryName = '".$data[$i]['B']." ' ";
			$cateId =  $db->fetchOne($sql);
			
			if(empty($cateId)){
					$isMaterial = ($data[$i]['B']=="បេតុង")?1:0;
					$_arr=array(
						
						'categoryName'   => $data[$i]['B'],
						'parentId'     	 => 0,
						'status'     	 => 1,
						'isMaterial'     => $isMaterial,
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

			$isCountStock = ($data[$i]['H']=="មិនរាប់")?0:1;
			$dbp = new Product_Model_DbTable_DbProduct();
			$proCode = $dbp->generateProductCode();
			$this->_name = "st_product";
			$_arr=array(
				'proName'  	  => $data[$i]['C'],
				'proCode'  	  => $proCode ,
				'categoryId'  	  => $cateId,
				'measureId'   	 => $measureId,
				'isConvertMeasure' => 0,
				'measureLabel' => $data[$i]['D'],
				'measureValue' => 1,
				'isCountStock'   => $isCountStock,
				'createDate' 	=> date("Y-m-d H:i:s"),
				'userId'	 	=> $this->getUserId()
			);
			
			if (!empty($data[$i]['E'])){

				$_arr['proName']=$_arr['proName']." សំណង់";
				$_arr['budgetId']=1;
				$this->insert($_arr);

			}
			
			if (!empty($data[$i]['F'])){
				$_arr['proName']=$data[$i]['C']." ហេដ្ឋារចនាសម្ព័ន្ធខាងក្រៅ";
				$_arr['budgetId']=2;
				$this->insert($_arr);
			}
		
			if (!empty($data[$i]['G'])){
				$_arr['proName']=$data[$i]['C']." ហេដ្ឋារចនាសម្ព័ន្ធខាងក្នុង";
				$_arr['budgetId']=3;
				$proId =  $this->insert($_arr);

			}

			

    	}
    }
}   

