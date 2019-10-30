<?php

class Stock_Model_DbTable_DbPurchase extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_supplier';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    function getAllSupPurchase($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$lang = $dbp->currentlang();
    	if($lang==1){// khmer
    		$label = "name_kh";
    	}else{ // English
    		$label = "name_en";
    	}
    	$sql="SELECT sp.id,
    		 (SELECT project_name FROM ln_project WHERE br_id=sp.branch_id LIMIT 1) AS branch_name,
    		 sp.supplier_no,
    		 sp.invoice_no,
    		 s.sup_name,
    	 	(SELECT $label FROM ln_view WHERE ln_view.key_code=s.sex AND ln_view.type=2) AS sex,s.tel,s.email, 
		    sp.amount_due,sp.date,
		    (SELECT first_name FROM rms_users WHERE sp.user_id=id LIMIT 1 ) AS user_name 
     		    ";
    	$sql.=$dbp->caseStatusShowImage("sp.status");
    	$sql.=" FROM rms_supplier AS s,
     		   rms_purchase AS sp
			WHERE s.id=sp.sup_id ";
    	
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " sp.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " sp.supplier_no LIKE '%{$s_search}%'";
    		$s_where[]="  s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]="  sp.invoice_no LIKE '%{$s_search}%'";
    		$s_where[]= " s.tel LIKE '%{$s_search}%'";
    		$s_where[]= " s.email LIKE '%{$s_search}%'";
    		$s_where[]= " sp.amount_due LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['supplier_id'])){
    		$where.=" AND s.id=".$search['supplier_id'];
    	}
    	if(!empty($search['branch_id'])){
    		$where.=" AND sp.branch_id=".$search['branch_id'];
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND sp.status=".$search['status_search'];
    	}
    	$where.=$dbp->getAccessPermission('sp.branch_id');
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getAllProductsetbyId($pro_id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `rms_product_setdetail` WHERE pro_id=$pro_id";
    	return $db->fetchAll($sql);
    }
    function updateStock($pro_id,$location_id,$qty_order){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM `rms_product` WHERE id=$pro_id LIMIT 1 ";
    	$rs_pro = $db->fetchRow($sql);
    	if($rs_pro['is_productseat']==1){//for set
    		$rs_set = $this->getAllProductsetbyId($pro_id);
    		
    		$this->_name="rms_product_location";
    		if(!empty($rs_set)){
    			foreach($rs_set AS $rs){
    				$sql="SELECT * FROM rms_product_location WHERE pro_id=".$rs['subpro_id']." AND brand_id=$location_id ";
    				$qty_stock = $db->fetchRow($sql);
    				$qty = $qty_stock['pro_qty'] + ($qty_order*$rs['qty']);

    				if(!empty($qty_stock)){
    					$array = array(
    							'pro_qty'=>$qty,
    					);
    					$where = " id = ".$qty_stock['id'];
    					$this->update($array, $where);   				
    				}elseif(empty($qty_stock)){
    					$this->_name="rms_product_location";
    					$_arrs = array(
    							'pro_id'=>$rs['subpro_id'],
    							'brand_id'=>$location_id,
    							'pro_qty'=>$qty,
    							'price'=>0,
    					);
    					$this->insert($_arrs);
    				}
    			}
    		}
    	}else{//for normal product
    		$sql="SELECT * FROM rms_product_location WHERE pro_id=$pro_id AND brand_id=$location_id ";
    		$qty_stock = $db->fetchRow($sql);
    		 
    		$this->_name="rms_product_location";
    		if(!empty($qty_stock)){
    			$qty = $qty_stock['pro_qty'] + $qty_order;
    			$array = array(
    					'pro_qty'=>$qty,
    			);
    			$where = " id = ".$qty_stock['id'];
    			$this->update($array, $where);
    		
    		}elseif(empty($qty_stock)){
    			$this->_name="rms_product_location";
    			$_arrs = array(
    					'pro_id'=>$pro_id,
    					'brand_id'=>$location_id,
    					'pro_qty'=>$qty_order,
    					'price'=>0,
    			);
    			$this->insert($_arrs);
    		}
    	}
    }
    public function addPurchase($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'sup_name'		=> $_data['supplier_name'],
    				'purchase_no'	=> $_data['purchase_no'],
    				'sex'			=> $_data['sex'],
    				'tel'			=> $_data['phone'],
    				'email'			=> $_data['email'],
    				'address'		=> $_data['address'],
    				'status'		=> 1,
    				'date'			=> $_data['purchase_date'],
    				'user_id'		=> $this->getUserId()
    				);
    		$this->_name='rms_supplier';
    		if(!empty($_data['is_new_cu'])){
    			$sup_id=$_data['sup_id'];
    			$where=" id =".$_data['sup_id'];
    			$this->update($_arr, $where);
    		}else{
    			$sup_id = $this->insert($_arr);
    		}
	    		//Purchasing Order Product
	    		
    		$dbgb = new Stock_Model_DbTable_DbGlobalProduct();
    		$purchase_no = $dbgb->getPuchaseNo($_data['branch']);
    		$this->_name='rms_purchase';
    		$_arr = array(
    				'sup_id'		=>$sup_id,
    				'supplier_no'	=>$purchase_no,
    				'invoice_no'    =>$_data['invoice_no'],
    				'amount_due'	=>$_data['amount_due'],
    				'amount_due_after'=>$_data['amount_due'],
    				'branch_id'		=>$_data['branch'],
    				'date'			=>$_data['purchase_date'],
    				'status'		=>1,
    				'user_id'		=>$this->getUserId(),
    				'create_date'	=>date("Y-m-d H:i:s"),
    				'modify_date'	=>date("Y-m-d H:i:s"),
    		);
    		$sup_proid=$this->insert($_arr);
	    		
	    		$this->_name='rms_purchase_detail';
	    		$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
	    			$this->_name='rms_purchase_detail';
	    				$_arr = array(
	    						'supproduct_id'=>$sup_proid,
	    						'pro_id'=>$_data['product_name_'.$i],
	    						'qty'	=>$_data['qty_'.$i],
	    						'cost'	=>$_data['cost_'.$i],
	    						'date'	=>$_data['purchase_date'],
	    						'amount'=>$_data['amount_'.$i],
	    						'note'	=>$_data['note_'.$i],
	    				);
    				$this->insert($_arr);
    				$this->updateProductCost($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i],$_data['amount_'.$i]);
    				$this->updateStock($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i]);
	    		}
    			$db->commit();
		   	}catch (Exception $e){
		   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		   		$db->rollBack();
		   		Application_Form_FrmMessage::message("INSERT_FAIL");
		   	}
    }
    function updateProductCost($pro_id,$branch,$qty,$total_amount_purchase){
    	$db = $this->getAdapter();
    	$sql="SELECT 
    				p.id,
    				p.cost,
    				SUM(pl.pro_qty) pro_qty
    			from
    				rms_product as p,
    				rms_product_location as pl
    			where 
    				p.id = pl.pro_id
    				and p.status = 1
    				and p.id = $pro_id ";
    	$result = $db->fetchRow($sql);
    	
    	if(!empty($result)){
    		$total_amount_in_stock = $result['pro_qty'] * $result['cost'];
    		$total_qty_sum = $result['pro_qty'] + $qty;
    		
    		$last_cost = ($total_amount_in_stock + $total_amount_purchase)/$total_qty_sum;
			
    		$array = array(
    				"cost"=>$last_cost,
    				);
    		
    		$this->_name = "rms_product";
    		$where = " id = ".$result['id'];
			$this->update($array, $where);
    	}
    }
    function updateStockBack($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT 
				  sp.branch_id,
				  spd.id,
				  spd.pro_id,
				  spd.qty,
				  spd.amount 
				FROM
				  rms_purchase AS sp,
				  rms_purchase_detail AS spd 
				WHERE 
				  sp.id = spd.supproduct_id 
				  AND sp.id = $id  ";
    	$result = $db->fetchAll($sql);
    	
    	if(!empty($result)){
    		foreach ($result as $row){
    			$this->updateProductCost($row['pro_id'],$row['branch_id'],-$row['qty'],-$row['amount']);
    			
				$sql1 = "SELECT id,pro_qty FROM rms_product_location where pro_id =".$row['pro_id']." and brand_id = ".$row['branch_id'] ;
				$result1 = $db->fetchRow($sql1);
				$qty = $result1['pro_qty'] - $row['qty']; 
				$this->_name = "rms_product_location";
				$array = array(
						'pro_qty'=> $qty,
						);
				$where=" id = ".$result1['id'] ;				
				$this->update($array, $where);
    		}
    	}
    }
    
 	function updatePurchase($_data,$id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{ 
    		$oldrs = $this->getSupplierById($_data['id']);
    		$old_status = $oldrs['status'];
    		if($old_status==1){//if old po active
    			$this->updateStockBack($_data['id']);//to back history data
    		}
    		
    		$this->_name = "rms_supplier";
	    		$_arr = array(
	    				'sup_name'		=>$_data['supplier_name'],
	    				'purchase_no'	=>$_data['purchase_no'],
	    				'sex'			=>$_data['sex'],
	    				'tel'			=>$_data['phone'],
	    				'email'			=>$_data['email'],
	    				'address'		=>$_data['address'],
	    				'amount_due'	=>$_data['amount_due'],
	    				'status'		=>1,
	    				'date'			=>$_data['purchase_date'],
	    				'user_id'		=>$this->getUserId()
	    				);
	    		 
	    			$sup_id=$_data['sup_id'];
	    			$where=" id =".$_data['sup_id'];
	    			$this->update($_arr, $where);
	    		
	    		//Purchasing Order Product
	    		$this->_name='rms_purchase';
	    		$_arr = array(
	    				'sup_id'		=> $sup_id,
	    				'supplier_no'	=> $_data['purchase_no'],
	    				'invoice_no'    =>$_data['invoice_no'],
	    				'amount_due'	=> $_data['amount_due'],
	    				'amount_due_after'=> $_data['amount_due'],
	    				'branch_id'		=> $_data['branch'],
	    				'date'			=> $_data['purchase_date'],
	    				'status'		=> $_data['status'],
	    				'user_id'		=> $this->getUserId(),
	    				'modify_date'	=> date("Y-m-d H:i:s"),
	    		);
	    		$where=" id =".$_data['id'];
	    		$this->update($_arr, $where);
	    		
	    		$this->_name='rms_purchase_detail';
	    		$where=" supproduct_id =".$_data['id'];
	    		$this->delete($where);
	    		$ids = explode(',', $_data['identity']);
	    		foreach ($ids as $i){
	    			$this->_name='rms_purchase_detail';
    				$_arr = array(
    					'supproduct_id'=> $_data['id'],
    					'pro_id'	   => $_data['product_name_'.$i],
    					'qty'		   => $_data['qty_'.$i],
    					'cost'		   => $_data['cost_'.$i],
    					'date'		   => date("Y-m-d"),
    					'amount'	   => $_data['amount_'.$i],
    					'note'		   => $_data['note_'.$i],
    				);
    				$this->insert($_arr);
    				
    				if($_data['status']==1){
    					$this->updateProductCost($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i],$_data['amount_'.$i]);
	    				$this->updateStock($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i]);
    				}
	    		}
    			$db->commit();
		   	}catch (Exception $e){
		   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		   		$db->rollBack();
		   		Application_Form_FrmMessage::message("INSERT_FAIL");
		   	}
    }
    function getProductNames(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
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
    
    function getProductName(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM rms_product AS p,rms_product_location AS pl
    	WHERE p.id=pl.pro_id AND p.status=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
    	return $db->fetchAll($sql);
    }
    
    function getPurchaseCode($branch_id=null){
    	$db = new Stock_Model_DbTable_DbGlobalProduct();
    	return $db->getPuchaseNo($branch_id);
    }
    function getSuplierName(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,sup_name FROM rms_supplier WHERE status=1 ORDER BY id DESC";
    	return $db->fetchAll($sql);
    }
    function getSuplierInfo($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,sup_name,purchase_no,sex,tel,email,address,amount_due FROM rms_supplier WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    function getProductById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM rms_product WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    function getSupplierById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT sp.*,s.sup_name,s.purchase_no,s.sex,s.tel,s.email,s.address,sp.amount_due,sp.branch_id,sp.status
		       FROM rms_supplier AS s,rms_purchase AS sp
		       WHERE s.id=sp.sup_id AND sp.id=$id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('sp.branch_id');
    	$sql.="LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getSupplierProducts($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,supproduct_id,pro_id,qty,cost,amount,note,status,
		(SELECT ide.title FROM `rms_product` AS ide WHERE ide.items_type=3 AND ide.id = pro_id LIMIT 1) AS pro_name
    	FROM rms_purchase_detail WHERE supproduct_id=$id";
    	return $db->fetchAll($sql);
    }
    function getAllBranch(){
    	$db = $this->getAdapter();
    	$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch where status=1 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('br_id');
    	return $db->fetchAll($sql);
    }
   
    function checkHaspayment($purchase_id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `rms_purchase_payment_detail` AS pr WHERE pr.`purchase_id`=$purchase_id
    	AND (SELECT p.`status` FROM `rms_purchase_payment` AS p WHERE p.`id` = pr.`payment_id` LIMIT 1) =1 LIMIT 1";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('(SELECT p.`branch_id` FROM `rms_purchase_payment` AS p WHERE p.`id` = pr.`payment_id` LIMIT 1)');
    	return $db->fetchRow($sql);
    }
}