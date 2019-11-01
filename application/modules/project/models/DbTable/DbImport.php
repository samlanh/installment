<?php

class Project_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'ldc_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    
    function checkItemsTypeId($category){
    	$db = $this->getAdapter();
    	$sql="SELECT d.* FROM ln_properties_type AS d WHERE d.type_nameen = '$category' LIMIT 1";
    	return $db->fetchRow($sql);
    }
    
    public function updateItemsByImport($data,$branch_id){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	for($i=1; $i<=$count; $i++){
    		$land_code = $dbg->getNewLandByBranch($branch_id);
    		
//     		$cate_title = empty($data[$i]['D'])?null:$data[$i]['D'];
//     		$cate = $this->checkItemsTypeId($cate_title);
//     		$property_type = 0;
//     		if (!empty($cate['id'])){
//     			$property_type = $cate['id'];
//     		}
    		$_arr=array(
    				'branch_id'	  => $branch_id,
    				'land_code'	  => $land_code,
    				'property_type'		=> 2,
    				'land_address'			=> $data[$i]['B'],
    				'street'    		=> $data[$i]['C'],
    				
    				'land_price'  => empty($data[$i]['E'])?0:$data[$i]['E'],
    				'house_price' => 0,
    				'price'	  => empty($data[$i]['E'])?0:$data[$i]['E'],
    				
    				'land_size'	  => $data[$i]['H'],
					'width'       => $data[$i]['G'],
					'height'      => $data[$i]['F'],
    				
    				'north'       => $data[$i]['I'],
    				'south'	  => $data[$i]['J'],
    				'west'      => $data[$i]['K'],
    				'east'      => $data[$i]['L'],
    				
    				'status'      => 1,
    				'create_date' 	=> date("Y-m-d H:i:s"),
    				'user_id'	 	=> $this->getUserId()
    		);
    		$this->_name = "ln_properties";
    		$pro_id =  $this->insert($_arr);
    	}
    }
}   

