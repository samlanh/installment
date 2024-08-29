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
    
    function getPropertyType($value){
    	$sql="SELECT id FROM `ln_properties_type` WHERE type_nameen='".$value."'";
    	$db = $this->getAdapter();
    	$rs = $db->fetchOne($sql);
    	if(empty($rs)){
    		$this->_name="ln_properties_type";
    		$data = array(
    				'type_nameen'=>$value,
    				'type_namekh'=>$value,
    				);
    		return $this->insert($data);
    	}else{
    		return $rs;
    	}
    }
    public function updateItemsByImport($data,$branch_id){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$dbl = new Project_Model_DbTable_DbLand();
    	for($i=2; $i<=$count; $i++){
    		$land_code = $dbg->getNewLandByBranch($branch_id);
    		$param  = array(
    				'land_address'=>$data[$i]['B'],
    				'branch_id'=>$branch_id,
    				'street'	=>$data[$i]['C']
    				);
    		$result = $dbl->CheckTitle($param);
    		if(!empty($result)){continue;}
//     		$cate_title = empty($data[$i]['D'])?null:$data[$i]['D'];
//     		$cate = $this->checkItemsTypeId($cate_title);
//     		$property_type = 0;
//     		if (!empty($cate['id'])){
//     			$property_type = $cate['id'];
//     		}
			$propertyType = $this->getPropertyType($data[$i]['D']);
    		$_arr=array(
    				'branch_id'	  => $branch_id,
    				'land_code'	  => $land_code,
    				'property_type'=> $propertyType,
    				'land_address'			=> $data[$i]['B'],
    				'street'    		=> "St.".$data[$i]['C'],
    				
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
    				//'buildPercentage'=> $data[$i]['O'],
    				//'note'=> $data[$i]['M'],
    				
    				'status'      => 1,
    				'create_date' 	=> date("Y-m-d H:i:s"),
    				'user_id'	 	=> $this->getUserId()
    		);
    		$this->_name = "ln_properties";
    		$pro_id =  $this->insert($_arr);
    	}
    }
}   

