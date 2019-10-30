<?php

class Stock_Model_DbTable_DbTransferstock extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_transferstock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllTransfer($search=null){  
    	$db=$this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT s.id,s.transfer_no,s.transfer_date,
    	(SELECT project_name FROM ln_project WHERE ln_project.br_id=s.from_location LIMIT 1) AS fromlocation,
    	(SELECT project_name FROM ln_project WHERE ln_project.br_id=s.to_location LIMIT 1) AS tolocation,
    	s.note,
    	(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) as user_name ";
    	
    	$sql.=$dbp->caseStatusShowImage("s.status");
    	$sql.=" FROM `rms_transferstock` AS s WHERE 1 ";
    	
    	//(SELECT name_en FROM `rms_view` WHERE TYPE=1 AND key_code=s.status ) as status
    	$from_date =(empty($search['start_date']))? '1': " s.transfer_date>= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.transfer_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
	    if(!empty($search['title'])){
	    	$s_where = array();
	    	$s_search = addslashes(trim($search['title']));
	    	$s_where[] = " s.transfer_no LIKE '%{$s_search}%'";
	    	$s_where[] = " s.note LIKE '%{$s_search}%'";
	    	$where .=' AND ( '.implode(' OR ',$s_where).')';
	    }
// 	    $where.=$dbp->getAccessPermission('s.from_location');
	    
    	$order=" ORDER BY s.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getTransferById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT t.* ,
				(SELECT p.project_name FROM ln_project AS p WHERE p.br_id=t.from_location) AS f_branch ,
				(SELECT p.project_name FROM ln_project AS p WHERE p.br_id=t.to_location) AS t_branch 
			FROM 
				rms_transferstock AS t 
			WHERE 
				t.id='".$id."'";
    	return $db->fetchRow($sql);
    }
    function getTransferByIdDetail($id,$frombranch=null){
    	$db = $this->getAdapter();
    	if (!empty($frombranch)){
    		$sql = "
    			SELECT trd.*,
				(SELECT pl.pro_qty FROM rms_product_location AS pl WHERE pl.brand_id = $frombranch AND pl.pro_id = trd.pro_id LIMIT 1 ) AS curr_qty,
				(SELECT ide.title FROM `rms_product` AS ide WHERE ide.items_type=3 AND ide.id = trd.pro_id LIMIT 1) AS pro_name
		    	FROM rms_transferdetail  AS trd WHERE trd.transferid=$id";
    	}else{
	    	$sql = "SELECT *,
			(SELECT ide.title FROM `rms_product` AS ide WHERE ide.items_type=3 AND ide.id = pro_id LIMIT 1) AS pro_name
	    	FROM rms_transferdetail WHERE transferid=".$id;
    	}
    	return $db->fetchAll($sql);
    }
    public function addTransferStock($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$key = new Application_Model_DbTable_DbKeycode();
	    	$keydata=$key->getKeyCodeMiniInv(TRUE);
	    	$condictionTransfer = empty($keydata['trasfer_st_cut'])?0:$keydata['trasfer_st_cut'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
    		   
    		$tran_no = $this->getTransferNo();
	    	$_arr = array(
	    			'transfer_no'=>$tran_no,
	    			'transfer_date'=>$_data['date'],
	    			'from_location'=>$_data['f_branch'],
	    			'to_location'=>$_data['branch'],
	    			'note'=>$_data['remark'],
	    			'create_date'=>date("Y-m-d H:i:s"),
	    			'modify_date'=>date("Y-m-d H:i:s"),
	    			'user_id'=>$this->getUserId(),
	    			'status'=>1,
	    		);
	    	if ($condictionTransfer!=1){
	    		$_arr['is_received']=1;
	    	}else{
	    		$_arr['is_received']=0;
	    	}
	    	$tranid= $this->insert($_arr);
	    	
	    	$ids = explode(',', $_data['identity']);
    		foreach ($ids as $i){
   				$_arr = array(
   					'transferid'=> $tranid,
   					'pro_id'	=> $_data['pro_id_'.$i],
   					'qty'		=> $_data['qty_'.$i],
   					'qty_after' => $_data['qty_'.$i],
   					'note'		=> $_data['remark_'.$i],
   				);
   				$this->_name='rms_transferdetail';
   				$this->insert($_arr);
    				
   				if ($condictionTransfer!=1){
   					$rs = $this->checkisProductSet($_data['pro_id_'.$i]);
   					if(empty($rs)){//normal product
   						$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['f_branch']);
   						$this->_name="rms_product_location";
   						if(!empty($qty_stock)){
   							$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
   							$array = array(
   									'pro_qty'=>$qty,
   							);
   							$where = " id = ".$qty_stock['id'];
   							$this->update($array, $where);
   						}
   						//to location
   						$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['branch']);
   						if(!empty($qty_stock)){
   							$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
   							$array = array(
									'pro_qty'=>$qty,
   							);
    						$where = " id = ".$qty_stock['id'];
    						$this->update($array, $where);
    					}else{
    						$data = array(
    								'pro_id'	=>$_data['pro_id_'.$i],
    								'brand_id'	=>$_data['branch'],
    								'pro_qty'	=>$_data['qty_'.$i],
    								'note'		=>'ពីផ្ទេរទំនិញចូល',
    								'date' 		=>date("Y-m-d"),
    								'user_id'	=>$this->getUserId(),
    								'status'	=>1
    							);
    						$this->insert($data);
    					}
    				}//prodcut set
    				else{
    					$rsset = $this->getProductSet($_data['pro_id_'.$i]);
    					if(!empty($rsset)){
    						foreach($rsset as $rs){
    							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['f_branch']);
    							$this->_name="rms_product_location";
    							if(!empty($qty_stock)){
    								$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];//ត្រូវគុណចំនួនត្រូវផ្ទេរជាមួយនឹងបរិមាណទំនិញក្នុងមួយ setទើបត្រូវ
    								$array = array(
    										'pro_qty'=>$qty,
    								);
    								$where = " id = ".$qty_stock['id'];
    								$this->update($array, $where);
    							}
    							//to location
    							$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['branch']);
    							if(!empty($qty_stock)){
    								$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
    								$array = array(
    										'pro_qty'=>$qty,
    								);
    								$where = " id = ".$qty_stock['id'];
    								$this->update($array, $where);
    							}else{
    								$data = array(
    										'pro_id'=>$_data['pro_id_'.$i],
    										'brand_id'=>$_data['branch'],
    										'pro_qty'=>$_data['qty_'.$i],
    										'note'=>'ពីផ្ទេរទំនិញចូល',
    										'date' =>date("Y-m-d"),
    										'user_id'=>$this->getUserId(),
    										'status'=>1
    								);
    								$this->insert($data);
    							}
    						}
    					}
    				}
    			}
	    	}
	    	$db->commit();
	    	return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		//echo $e->getMessage();exit();
    		return false;
    	}
    }
    function getProductSet($product_id){// select item for product set
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `rms_product_setdetail` WHERE pro_id  = ".$product_id;
    	return $db->fetchAll($sql);
    }
    function checkisProductSet($product_id){//if product is set
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM  rms_product WHERE is_productseat=1 AND id = ".$product_id;
    	return $db->fetchRow($sql);
    }
    function getTransferNo(){
    	$db = $this->getAdapter();
    	$sql="SELECT (id+1)FROM `rms_transferstock` ORDER BY id DESC  LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
	    $new_acc_no= (int)$acc_no+1;
	  	$acc_no= strlen((int)$acc_no+1);
	  	$pre="";
	  	for($i = $acc_no;$i<5;$i++){
	  		$pre.='0';
	  	}
    	return $pre.$new_acc_no;
    }
    function getProductLocation($pro_id,$location_id){
    	$db = $this->getAdapter();
    	$sql="select * from rms_product_location where pro_id=".$pro_id." AND brand_id = ".$location_id;
    	$row = $db->fetchRow($sql);
    	return $row;
    }
	public function updateTransferStock($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$key = new Application_Model_DbTable_DbKeycode();
    		$keydata=$key->getKeyCodeMiniInv(TRUE);
    		$condictionTransfer = empty($keydata['trasfer_st_cut'])?0:$keydata['trasfer_st_cut'];//0=Transfer Cut Stock Direct,1=Transfer  Cut Stock with Receive
    		
   			$rsold = $this->getTransferById($_data['id']);
    		$_arr = array(
    				'transfer_no'=>$_data['transfer_no'],
    				'transfer_date'=>$_data['date'],
    				'from_location'=>$_data['from_location'],
    				'to_location'=>$_data['to_location'],
    				'note'=>$_data['remark'],
    				'user_id'=>$this->getUserId(),
    				'status'=>$_data['status'],
    			);
    		if ($condictionTransfer!=1){
    			$_arr['is_received']=1;
    		}else{
    			$_arr['is_received']=0;
    		}
    		
    		$where="id = ".$_data['id'];
    		$this->update($_arr, $where);
    		$rsdetail = $this->getTransferByIdDetail($_data['id']);
    		$this->_name='rms_transferdetail';
    		
    		if ($condictionTransfer!=1){
    			if(!empty($rsdetail) AND $rsold['status']==1){// if old status active
	   				foreach($rsdetail as $rsd){
    					$qty_stock = $this->getProductLocation($rsd['pro_id'],$_data['old_flocation']);
    					$this->_name="rms_product_location";
    					if(!empty($qty_stock)){
    						$qty = $qty_stock['pro_qty'] + $rsd['qty'];
    						$array = array(
    								'pro_qty'=>$qty,
    						);
    						$where = " id = ".$qty_stock['id'];
    						$this->update($array, $where);
    					}
    					$qty_stock = $this->getProductLocation($rsd['pro_id'],$_data['old_tlocation']);
    					if(!empty($qty_stock)){
    						$qty = $qty_stock['pro_qty'] - $rsd['qty'];
    						$array = array(
    								'pro_qty'=>$qty,
    						);
    						$where = " id = ".$qty_stock['id'];
    						$this->update($array, $where);
    					}
    				}
    			}
    		}
	    		
	    	$this->_name='rms_transferdetail';
	    	$where = "transferid = ".$_data['id']; 
	    	$this->delete($where);
	    		
	    	$ids = explode(',', $_data['identity']);
    		foreach ($ids as $i){
   				$_arr = array(
   						'transferid'=>$_data['id'],
   						'pro_id'	=>$_data['pro_id_'.$i],
   						'qty'		=>$_data['qty_'.$i],
   						'qty_after' =>$_data['qty_'.$i],
   						'note'		=>$_data['remark_'.$i],
   					);
   				$this->_name='rms_transferdetail';
   				$this->insert($_arr);

   				if ($condictionTransfer!=1){
   					if($_data['status']==1){
   						$rs = $this->checkisProductSet($_data['pro_id_'.$i]);
   						if(empty($rs)){//normal product
   							$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['from_location']);
   							$this->_name="rms_product_location";
   							if(!empty($qty_stock)){
   								$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
   								$array = array(
   										'pro_qty'=>$qty,
   								);
   								$where = " id = ".$qty_stock['id'];
   								$this->update($array, $where);
   							}
   							//to location
   							$qty_stock = $this->getProductLocation($_data['pro_id_'.$i],$_data['to_location']);
   							if(!empty($qty_stock)){
   								$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
   								$array = array(
   										'pro_qty'=>$qty,
   								);
   								$where = " id = ".$qty_stock['id'];
   								$this->update($array, $where);
   							}else{
   								$data = array(
   										'pro_id'	=>$_data['pro_id_'.$i],
   										'brand_id'	=>$_data['to_location'],
   										'pro_qty'	=>$_data['qty_'.$i],
   										'note'		=>'ពីផ្ទេរទំនិញចូល',
   										'date' 		=>date("Y-m-d"),
   										'user_id'	=>$this->getUserId(),
   										'status'	=>1
   									);
   								$this->insert($data);
  							}
   						}//prodcut set
   						else{
   							$rsset = $this->getProductSet($_data['pro_id_'.$i]);
   							if(!empty($rsset)){
   								foreach($rsset as $rs){
   									$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['from_location']);
   									$this->_name="rms_product_location";
   									if(!empty($qty_stock)){
   										$qty = $qty_stock['pro_qty'] - $_data['qty_'.$i];
   										$array = array(
   												'pro_qty'=>$qty,
   										);
   										$where = " id = ".$qty_stock['id'];
   										$this->update($array, $where);
   									}
   									//to location
   									$qty_stock = $this->getProductLocation($rs['subpro_id'],$_data['to_location']);
   									if(!empty($qty_stock)){
   										$qty = $qty_stock['pro_qty'] + $_data['qty_'.$i];
   										$array = array(
   												'pro_qty'=>$qty,
   										);
   										$where = " id = ".$qty_stock['id'];
   										$this->update($array, $where);
   									}else{
   										$data = array(
   												'pro_id'=>$_data['pro_id_'.$i],
   												'brand_id'=>$_data['to_location'],
   												'pro_qty'=>$_data['qty_'.$i],
   												'note'=>'ពីផ្ទេរទំនិញចូល',
   												'date' =>date("Y-m-d"),
   												'user_id'=>$this->getUserId(),
   												'status'=>1
   										);
   										$this->insert($data);
   									}
   								}
   							}
   						}
   					}
   				}
    		}
    	    $db->commit();
    	    return true;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
    
    function getCustomer($search=null){
    	$db=$this->getAdapter();
    	$sql="SELECT c.*,cp.status As cp_status FROM rms_customer AS c,rms_customer_paymentdetail AS cp
            		WHERE c.id=cp.cus_id";
    	$where = '';
    	$from_date =(empty($search['start_date']))? '1': "c.start_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "c.end_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  c.customer_code LIKE '%{$s_search}%'";
    		$s_where[]="  c.first_name LIKE '%{$s_search}%'";
    		$s_where[]="  c.phone LIKE '%{$s_search}%'";
    		$s_where[]="  c.email LIKE '%{$s_search}%'";
    		$s_where[]="  c.address LIKE '%{$s_search}%'";
    		
    		$s_where[]="  cp.rent_receipt_no LIKE '%{$s_search}%'";
    		$s_where[]="  cp.water_total LIKE '%{$s_search}%'";
    		$s_where[]="  cp.fire_total LIKE '%{$s_search}%'";
    		$s_where[]="  cp.all_total_amount LIKE '%{$s_search}%'";
    		$s_where[]="  cp.paid LIKE '%{$s_search}%'";
    		$s_where[]="  cp.balance LIKE '%{$s_search}%'";
    		$s_where[]="  cp.rent_paid LIKE '%{$s_search}%'";
    		$s_where[]="  cp.hygiene_price LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['cus_name']>0){
    		$where.=' AND c.id='.$search["cus_name"];
    	}
    	
    	if($search["status_search"]>-1){
    		$where.=' AND cp.status='.$search["status_search"];
    	}
    	$order=" GROUP BY c.id ORDER BY c.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getCusPaymentDetail($id,$status){
    	$db=$this->getAdapter();
    	$sql="SELECT 
		       cp.*,
		       (SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=cp.user_id LIMIT 1) AS user_name,
		       (SELECT name_kh FROM rms_view WHERE rms_view.key_code=cp.status AND rms_view.type=1 LIMIT 1) AS active
		       FROM rms_customer AS c,rms_customer_paymentdetail AS cp
		       WHERE c.id=cp.cus_id
		       AND cp.cus_id=$id AND cp.status=$status";
    	return $db->fetchAll($sql);
    }
    
    function getNearlydayEndServiceCustomer($search=null){
    	$db=$this->getAdapter();
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('sp.branch_id');
    	$sql="SELECT c.*,(SELECT v.name_en FROM rms_view AS v WHERE v.key_code=c.sex AND v.type=2 LIMIT 1 ) AS c_sex,
        (SELECT v.name_en FROM rms_view AS v WHERE v.key_code=c.status AND v.type=1 LIMIT 1 ) AS c_status,
        (SELECT CONCAT(first_name,' ',last_name) FROM rms_users WHERE rms_users.id=c.user_id LIMIT 1) AS user_name
        FROM rms_customer AS c WHERE 1";
    	$where = '';
    	if(!empty($search["title"])){
    		$s_where=array();
    		$s_search = addslashes(trim($search['title']));
    		$s_where[]="  c.customer_code LIKE '%{$s_search}%'";
    		$s_where[]="  c.first_name LIKE '%{$s_search}%'";
    		$s_where[]="  c.address LIKE '%{$s_search}%'";
    		$s_where[]="  c.phone LIKE '%{$s_search}%'";
    		$s_where[]="  c.email LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    		
    	if($search["status_search"]>-1){
    		$where.=' AND c.status='.$search["status_search"];
    	}
    
    	if($search['cus_name']>0){
    		$where.=' AND c.id='.$search["cus_name"];
    	}
    
    	$order=" ORDER BY c.id DESC ";
    	$str_next = '+1 3 days';
    	$search['end_date']=date("Y-m-d", strtotime($search['end_date'].$str_next));
    	$to_date = (empty($search['end_date']))? '1': " c.end_date <= '".$search['end_date']." 23:59:59'";
    	$where .= " AND ".$to_date;
    	return $db->fetchAll($sql.$where);
    }
}