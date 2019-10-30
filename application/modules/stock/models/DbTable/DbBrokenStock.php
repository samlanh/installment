<?php

class Stock_Model_DbTable_DbBrokenStock extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_brokenstock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    function getAllBrokenStock($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	
    	$sql="SELECT id,
    			(SELECT b.project_name FROM `ln_project` AS b  WHERE b.br_id = branch_id LIMIT 1) AS branch_name,
    			broken_no,
    			request_name,
    			note,
    			request_date,
    		   (SELECT SUM(rd.qty_broken) FROM rms_brokenstock_detail AS rd WHERE rd.brokenstock_id=rms_brokenstock.id LIMIT 1)AS total_qty,
			   (SELECT first_name FROM rms_users WHERE id=rms_brokenstock.user_id LIMIT 1) AS user_name
    		 ";
    	$sql.=$dbp->caseStatusShowImage("status");
    	$sql.=" FROM rms_brokenstock WHERE 1 ";
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " request_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " request_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search = str_replace(' ', '', addslashes(trim($search['title'])));
    		$s_where[]= " REPLACE(adjust_no,' ','') LIKE '%{$s_search}%'";
    		$s_where[]="  REPLACE(request_name,' ','') LIKE '%{$s_search}%'";
    		$s_where[]= " REPLACE(note,' ','') LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND status=".$search['status_search'];
    	}
    	if($search['branch_id']>0 and !empty($search['branch_id'])){
    		$where.=" AND branch_id=".$search['branch_id'];
    	}
    	
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }

    public function getProQtyByLocation($branch_id,$pro_id){
    	$db=$this->getAdapter();
    	$sql=" SELECT pl.id,pl.pro_id,pl.pro_qty  
    		   FROM 
    		   		rms_product_location AS pl,
    		   		rms_product AS p
			   WHERE pl.pro_id=$pro_id AND pl.brand_id=$branch_id
				 AND   p.id=pl.pro_id ";
    	$row = $db->fetchRow($sql);
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		$array = array(
    				'pro_id'	=>$pro_id,
    				'brand_id'	=>$branch_id,
    				'pro_qty'	=>0,
    				'date'		=>date("Y-m-d"),
    				'status'	=>1,
    				"user_id"   =>$GetUserId,
    		);
    		$this->_name="rms_product_location";
    		$this->insert($array);
    		$sql=" SELECT pl.pro_id,pl.pro_qty  FROM rms_product_location AS pl,rms_product AS p
    		WHERE pl.pro_id=$pro_id AND pl.brand_id=$branch_id
    		AND   p.id=pl.pro_id ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		return $row;
    	}
    }
    
    public function addBrokenStock($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$brokenNo = $this->getBrokenCode($data["branch"]);
			$arr=array(
					"branch_id"    	=> 	$data["branch"],
					"broken_no"    	=> 	$brokenNo,
					"request_name"  => 	$data["request_name"],
					"note"     		=> 	$data["note"],
					"request_date"  => 	date("Y-m-d"),
					"create_date"   => 	date("Y-m-d H:i:s"),
					"user_id"       => 	$this->getUserId(),
					"status"        =>  1,
			);
			$this->_name="rms_brokenstock";
			$brokenstock_id = $this->insert($arr); 
			unset($arr);

			if($data['identity']!=""){
				$ids=explode(',',$data['identity']);
				foreach ($ids as $i)
				{
					$data_item= array(
							'brokenstock_id'=>  $brokenstock_id,
							'branch_id'		=> 	$data['branch_id_'.$i],
							'pro_id'		=>  $data['product_name_'.$i],
							'qty_before'	=> 	$data['curr_qty'.$i],
							'qty_broken'	=>  $data['broken_qty'.$i],
							'qty_after'		=> 	$data['remain_qty'.$i],
							'create_date'	=>  date("Y-m-d H:i:s"),
							'remark'  		=> 	$data['note_'.$i],
							'last_usermod'	=> 	$this->getUserId(),
							'status'		=> 	1,
					);
					$this->_name='rms_brokenstock_detail';
					$this->insert($data_item);
					
					$rows=$this->getProQtyByLocation($data['branch_id_'.$i], $data['product_name_'.$i]); 
					if($rows){
						$datatostock= array(
							'pro_qty' 	=> $data['remain_qty'.$i],
							'date'		=> date("Y-m-d H:i:s"),
							'user_id'	=> $this->getUserId()
						);
						$this->_name="rms_product_location";
						$where=" id = ".$rows['id'];
						$this->update($datatostock, $where);
					}
				 }
			}
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			Application_Form_FrmMessage::message('INSERT_FAIL');
		}
	}
    
 	function updateAdjustStock($data){
 		$db = $this->getAdapter();
 		$db->beginTransaction();
 		try{
 			//sum qty to current qty in stock
 			$oldrs = $this->getAdjustStockById($data['id']);
 			if($oldrs['status']==1){
	 			$rows=$this->getAdjustStockDetail($data['id']);
	 			if(!empty($rows))foreach ($rows AS $row){
	 				$qty=$this->getProQtyByLocation($row['branch_id'], $row['pro_id']);
	 				if($qty){
	 					$curr= array(
	 						'pro_qty' 	=>$qty['pro_qty']+$row['difference'],
	 						'date'		=> date("Y-m-d H:i:s"),
	 						'user_id'	=> $this->getUserId()
	 					);
	 					$this->_name="rms_product_location";
	 					$where=" id = ".$qty['id'];
	 					$this->update($curr, $where);
	 				}
	 			}
 			}
 			$arr=array(
 				"adjust_no"    	=> 	$data["adjust_no"],
 				"request_name"  => 	$data["request_name"],
 				"note"     		=> 	$data["note"],
 				"request_date"  => 	date("Y-m-d"),
 				"create_date"   => 	date("Y-m-d H:i:s"),
 				"user_id"       => 	$this->getUserId(),
 				"status"        => 	$data['status'],
 			);
 			$this->_name="rms_brokenstock";
 			$where="id=".$data['id'];
 			$this->update($arr, $where);
 			unset($arr);
 			
 			$this->_name='rms_brokenstock_detail';
 			$where="brokenstock_id=".$data['id'];
 			$this->delete($where);
 			
 			if($data['identity']!=""){
 				$ids=explode(',',$data['identity']);
 				foreach ($ids as $i)
 				{
 					$data_item= array(
 							'brokenstock_id'=>  $data['id'],
 							'branch_id'		=> 	$data['branch_id_'.$i],
 							'pro_id'		=>  $data['product_name_'.$i],
 							'qty_befor'		=> 	$data['curr_qty'.$i],
 							'qty_after'		=>  $data['request_qty'.$i],
 							'difference'	=> 	$data['spacing_qty'.$i],
 							'create_date'	=>  date("Y-m-d H:i:s"),
 							'remark'  		=> 	$data['note_'.$i],
 							'last_usermod'	=> 	$this->getUserId(),
 							'status'		=> 	$data['status'],
 					);
 					$this->_name='rms_brokenstock_detail';
 					$this->insert($data_item);
 					
 					if($data['status']==1){
	 					$rows=$this->getProQtyByLocation($data['branch_id_'.$i], $data['product_name_'.$i]);
	 					if($rows){
	 						$datatostock= array(
	 								'pro_qty' 	=>$data['request_qty'.$i],
	 								'date'		=> date("Y-m-d H:i:s"),
	 								'user_id'	=> $this->getUserId()
	 						);
	 						$this->_name="rms_product_location";
	 						$where=" id = ".$rows['id'];
	 						$this->update($datatostock, $where);
	 					}
 					}
 				}
 			}
 			$db->commit();
 		}catch(Exception $e){
 			$db->rollBack();
 			Application_Form_FrmMessage::message('INSERT_FAIL');
 			$err =$e->getMessage();
 			Application_Model_DbTable_DbUserLog::writeMessageError($err);
 		}
 	}
	function getAdjustStockById($id){
		$db=$this->getAdapter();
		$sql="SELECT * FROM rms_brokenstock WHERE id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}
	
	function getAdjustStockDetail($id){
		$db=$this->getAdapter();
		$sql="SELECT ad.branch_id,(SELECT p.title FROM rms_product AS p WHERE p.id=ad.pro_id ) AS pro_name,
			    ad.pro_id,ad.qty_before,ad.qty_broken,ad.qty_after,ad.remark FROM rms_brokenstock_detail  AS ad
				WHERE ad.brokenstock_id=$id";
		return $db->fetchAll($sql);
	}
	
    function getProductNames(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.brand_id,p.title AS `name` FROM rms_product AS p,rms_product_location AS pl
 				WHERE p.id=pl.pro_id AND p.status=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
        $rows=$db->fetchAll($sql);
        
        array_unshift($rows,array('id' => '',"name"=>"Please select product name"));
        $options = '';
        if(!empty($rows))foreach($rows as $value){
        	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $options;
    }
    
//     function getProductName(){
//     	$db=$this->getAdapter();
//     	$sql="SELECT p.id,pl.brand_id,p.title AS `name` FROM rms_product AS p,rms_product_location AS pl
//     	WHERE p.id=pl.pro_id AND p.status=1  ";
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$sql.=$dbp->getAccessPermission('brand_id');
//     	$sql.=" GROUP BY p.id ORDER BY id DESC ";
//     	return $db->fetchAll($sql);
//     }
    
//     function getProducCutStockLater(){
//     	$db=$this->getAdapter();
//     	$sql="SELECT p.id,pl.brand_id,p.pro_name AS `name` 
//     		FROM rms_product AS p,
//     			rms_product_location AS pl
// 		    	WHERE p.id=pl.pro_id AND p.status=1";
//     	$dbp = new Application_Model_DbTable_DbGlobal();
//     	$sql.=$dbp->getAccessPermission('brand_id');
//     	$sql.=" GROUP BY p.id ORDER BY id DESC ";
// //     	echo $sql;exit();
//     	return $db->fetchAll($sql);
//     }

    function getBrokenCode($branch_id=null){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM rms_brokenstock WHERE STATUS=1 ";
    	$pre="";
    	if (!empty($branch_id)){
    		$sql.=" AND branch_id=".$branch_id;
    		$_dbgb = new Application_Model_DbTable_DbGlobal();
    		$pre.= $_dbgb->getPrefixCode($branch_id);//by branch
    	}
    	$sql.=" ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre.='BR-';
    	for($i = $acc_no;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
     
    function getProductById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM rms_product WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    
    function getAllBranch(){
    	$db = $this->getAdapter();
    	$sql="select br_id as id, project_name as name from ln_project where status=1 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	return $db->fetchAll($sql);
    }
    
    public function ajaxAddProduct($data){
    	$db = $this->getAdapter();
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$_arr = array(
    			'pro_name'	=>$data['product_name'],
    			'pro_code'	=>$data['product_code'],
    			'cat_id'	=>$data['category_id'],
    			'pro_price'	=>$data['pro_price'],
    			'pro_des'	=>$data['descript'],
    			'pro_type'	=>$data['pro_type'],
    			'status'	=>$data['p_status'],
    			'date'		=>date("Y-m-d"),
    			'user_id'	=>$this->getUserId()
    	);
    	$this->_name = "rms_product";
    	$pro_id = $this->insert($_arr);
    	$_arr = array(
    			'pro_id'=>$pro_id,
    			'brand_id'=>$data['location_id'],
    			'pro_qty'=>0,
    			'total_amount'=>0,
    			'note'=>'',
    	);
    	$this->_name='rms_product_location';
    	$this->insert($_arr);
    	$array = array(
    			'ser_cate_id'	=>$pro_id,
    			'title'			=>$data['product_name'],
    			'description'	=>$data['descript'],
    			'price'			=>$data['pro_price'],
    			'status'		=>1,
    			'create_date'	=>date("Y-m-d H:i:s"),
    			'user_id'		=>$this->getUserId(),
    			'type'			=>1, // type=1 => product
    			'pro_type'		=>$data['pro_type'], // 1=cut stock , 2=cut stock later
    	);
    	$this->_name='rms_program_name';
    	$this->insert($array);
    	return $pro_id;
    }
    
    function getProductQty($location,$pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT pl.pro_qty FROM rms_product AS p,rms_product_location AS pl
		  WHERE p.id=pl.pro_id
		  AND pl.pro_id=$pro_id 
		  AND pl.brand_id=$location";
    	return $db->fetchOne($sql);
    }
    
    function getAllProductBybranch($branch_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
		    	WHERE p.id=pl.pro_id AND p.status=1
		    	AND p.pro_type=2 AND pl.brand_id=".$branch_id;
    	$order=' ORDER BY p.id DESC';
    	return $db->fetchAll($sql.$order);
    }
}