<?php

class Loan_Model_DbTable_Dbchangehouse extends Zend_Db_Table_Abstract
{

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
   function getAllChangeHouse($search){
   	$db = $this->getAdapter();
   	$from_date =(empty($search['start_date']))? '1': " s.change_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " s.change_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	$sql="SELECT cp.id,
   		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.from_branchid LIMIT 1) AS from_branch,
	c.name_kh,
	(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_houseid LIMIT 1) from_property,
	cp.soldprice_before,cp.paid_before,cp.balance_before,
	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branchid LIMIT 1) AS to_branch,
	(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_houseid LIMIT 1) to_propertype,
	cp.house_priceafter,cp.discount_percentafter,cp.discount_amountafter,cp.sold_priceafter,cp.balance_after,
	cp.change_date,
	(SELECT  first_name FROM rms_users WHERE id=cp.user_id limit 1 ) AS user_name,
	cp.status
	FROM `ln_change_house` AS cp,`ln_client` c WHERE c.client_id=cp.client_id ";
   	
   	$from_date =(empty($search['start_date']))? '1': " cp.change_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " cp.change_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	if(!empty($search['adv_search'])){
   		$s_where = array();
//    		$s_search = addslashes(trim($search['adv_search']));
//    		$s_where[] = " cp.receipt_no LIKE '%{$s_search}%'";
//    		$s_where[] = " p.land_code LIKE '%{$s_search}%'";
//    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
//    		$s_where[] = " c.client_number LIKE '%{$s_search}%'";
//    		$s_where[] = " c.name_en LIKE '%{$s_search}%'";
//    		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
//    		$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
//    		$s_where[] = " s.comission LIKE '%{$s_search}%'";
//    		$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
//    		$where .=' AND ( '.implode(' OR ',$s_where).')';
   	}
   	if($search['status']>-1){
   		$where.= " AND cp.status = ".$search['status'];
   	}
   	if(($search['client_name'])>0){
   		$where.= " AND `cp`.`client_id`=".$search['client_name'];
   	}
   	if(($search['branch_id'])>0){
   		$where.= " AND ( cp.from_branchid = ".$search['branch_id']." OR cp.to_branchid = ".$search['branch_id']." )";
   	}
   	
   	$order = " ORDER BY id DESC ";
   	
   	$dbp = new Application_Model_DbTable_DbGlobal();
    $where.=$dbp->getAccessPermission("cp.from_branchid");
    
   	return $db->fetchAll($sql.$where.$order);
   }
   function getProperty($id){
   	$db = $this->getAdapter();
   	$sql="SELECT * FROM `ln_properties` AS p WHERE p.`id`=".$id;
   	return $db->fetchRow($sql);
   }
   public function addChangeHouse($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{//need add schedule
    		
    		if ($data['typesale']==2){//លក់ម្តងច្រើន
    			$ids_land = explode(',', $data['identity_land']);
    			$size = 0; $width=''; $height='';
    			$land_address='';
    			$land_code='';
    			$price = 0;
    			$land_price=0;
    			$house_price=0;
    			$property_type='';
    			foreach ($ids_land as $key => $i){
    				$this->_name="ln_properties";
    				$where = "id =".$ids_land[$key];
    				$arr = array(
    						"is_lock"=>1,
    				);
    				$this->update($arr, $where);
    				$newpro = $this->getProperty($ids_land[$key]);
    				$size = $size + $newpro['land_size'];
    		
    				$width = $width+$newpro['width'];
    				$height =$newpro['height'];
    		
    				$price = $price + $newpro['price'];
    				$land_price = $land_price+$newpro['land_price'];
    				$house_price = $house_price+$newpro['house_price'];
    				
    				if(!empty($land_address)){
    					//$land_address= $land_address.'&'.$newpro['land_address'];
    					$land_address= $land_address.','.$newpro['land_address'];
    				}else{ 
    					$land_address =$newpro['land_address'];
    				}
    				if(!empty($land_code)){
    					$land_code=$land_code.','.$newpro['land_code'];
    				}else{ $land_code =$newpro['land_code'];
    				}
    				$property_type = $newpro['property_type'];
    			}//end loop
    			
    			$newproperty = array(
    					'branch_id'=>$data['branch_id'],
    					'land_code'=>$land_code,
    					'land_address'=>$land_address,
    					'street'=>$newpro['street'],
    					'price'=>$price,
    					'land_price'=>$land_price,
    					'house_price'=>$house_price,
    					'property_type'=>$property_type,
    					"is_lock"=>1,
    					"status"=>-2,
    					"create_date"=>date("Y-m-d"),
    					"user_id"=>$this->getUserId(),
    					"old_land_id"=>$data['identity_land'],
    					
    					'street_code'=>$newpro['street_code'],
    			);
    			$this->_name="ln_properties";
    			$land_id = $this->insert($newproperty);
    			$data['to_land_code']=$land_id;
    		}else{
    			$this->_name="ln_properties";
    			$where=" id=".$data['to_land_code'];
	    		$arr = array(
	    			'is_lock'=>1
	    		);
	    		$this->update($arr, $where);//lock new house
    		}
    		$sql=" SELECT SUM(total_principal_permonthpaid) AS total_permonth FROM `ln_client_receipt_money` WHERE sale_id =".$data['loan_number']." AND status=1 ";
    		$paid_amount = $db->fetchOne($sql);
    		
    		
    		
    		$arr = array(
    				'price_before'=>$data['house_price'],
    				'discount_amount'=>$data['discount'],
    				'discount_percent'=>$data['discount_percent'],
    				'price_sold'=>$data['to_total_sold'],
    				'paid_amount'=>$paid_amount,
    				'is_reschedule'=>3,
    				'other_discount'=>$data['other_discount'],
    		);
    		
    		$where = " id = ".$data['loan_number'];
    		$this->_name="ln_sale";
    		$this->update($arr, $where);//add group loan
    		
    		$dbs = new Loan_Model_DbTable_DbLandpayment();
    		$id = $data['loan_number'];
    		$rows = $dbs->getTranLoanByIdWithBranch($id);
    		
    		if(!empty($rows)){
    			$ids = explode(',', $rows['all_land_id']);
    			if (!empty($rows['all_land_id'])){
    				foreach($ids as $land){ //unlock old house
    					$this->_name="ln_properties";
    					$arr = array(
    						"is_lock"=>0
    					);
    					$where = "id =".$land;
    					$this->update($arr, $where);
    				}
    			}
    		}
    		
    		$arr = array(
    			'sale_id'=>$id,
    			'client_id'=>$data['member'],
    			'from_branchid'=>$data['branch_id'],
    			'from_houseid'=>$rows['house_id'],
    			'soldprice_before'=>$data['total_sold'],
    			'paid_before'=>$data['paid_before'],
    			'balance_before'=>$data['balance_before'],
    			'to_branchid'=>$data['to_branch_id'],
    			'to_houseid'=>$data['to_land_code'],
    			'house_priceafter'=>$data['house_price'],
    			'discount_percentafter'=>$data['discount_percent'],
    			'discount_amountafter'=>$data['discount'],
    			'sold_priceafter'=>$data['to_total_sold'],    				
    			'balance_after'=>$data['balance'],
    			'change_date'=>$data['release_date'],//$data['date_buy'],
    			'note'=>$data['note'],
    			'user_id'=>$this->getUserId(),
    			'typesale'=>$data['typesale'],
    			'other_discount'=>$data['other_discount'],
    		);
    		$this->_name="ln_change_house";
    		$changeid = $this->insert($arr);

    			$arr = array(
    				'branch_id'=>$data['to_branch_id'],
    				'house_id'=>$data["to_land_code"],
    				'typesale'=>$data['typesale'],
    			);
    			
    			$dbGlobal = new Application_Model_DbTable_DbGlobal();
    			if (CONTRACT_NO_SETING==1){
    				$arrSale = array(
    						'branch_id'=>$data['to_branch_id'],
    						'land_code'=>$data['to_land_code'],
    				);
    				$arr['sale_number'] = $dbGlobal->getLoanNumber($arrSale);
    			}
    			
	    		$where = " id = ".$data['loan_number'];
	    		$this->_name="ln_sale";
	    		$id = $this->update($arr, $where);
	    		
    			$db->commit();
    			return 1;
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }
    public function UpdateChangeHouse($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{//need add schedule
    		
    		if ($data['typesale']==2){//លក់ម្តងច្រើន
    			$ids_land = explode(',', $data['identity_land']);
    			$size = 0; $width=''; $height='';
    			$land_address='';
    			$land_code='';
    			$price = 0;
    			$land_price=0;
    			$house_price=0;
    			$property_type='';
    			foreach ($ids_land as $key => $i){
    				$this->_name="ln_properties";
    				$where = "id =".$ids_land[$key];
    				$arr = array(
    						"is_lock"=>1,
    				);
    				$this->update($arr, $where);
    				$newpro = $this->getProperty($ids_land[$key]);
    				$size = $size + $newpro['land_size'];
    		
    				$width = $width+$newpro['width'];
    				$height =$newpro['height'];
    		
    				$price = $price + $newpro['price'];
    				$land_price = $land_price+$newpro['land_price'];
    				$house_price = $house_price+$newpro['house_price'];
    		
    				if(!empty($land_address)){
    					//$land_address= $land_address.'&'.$newpro['land_address'];
    					$land_address= $land_address.','.$newpro['land_address'];
    				}else{
    					$land_address =$newpro['land_address'];
    				}
    				if(!empty($land_code)){
    					$land_code=$land_code.','.$newpro['land_code'];
    				}else{ $land_code =$newpro['land_code'];
    				}
    				$property_type = $newpro['property_type'];
    			}//end loop
    			 
    			$newproperty = array(
    					'branch_id'=>$data['branch_id'],
    					'land_code'=>$land_code,
    					'land_address'=>$land_address,
    					'street'=>$newpro['street'],
    					'price'=>$price,
    					'land_price'=>$land_price,
    					'house_price'=>$house_price,
    					'property_type'=>$property_type,
    					"is_lock"=>1,
    					"status"=>-2,
    					"create_date"=>date("Y-m-d"),
    					"user_id"=>$this->getUserId(),
    					"old_land_id"=>$data['identity_land'],
    					
    					'street_code'=>$newpro['street_code'],
    			);
    			$this->_name="ln_properties";
    			$land_id = $this->insert($newproperty);
    			$data['to_land_code']=$land_id;
    		}else{
    			$this->_name="ln_properties";
    			$where=" id=".$data['to_land_code'];
    			$arr = array(
    					'is_lock'=>1
    			);
    			$this->update($arr, $where);//lock new house
    		}
    		
    		$dbs = new Loan_Model_DbTable_DbLandpayment();
    		$id = $data['loan_number'];
    		$rows = $dbs->getTranLoanByIdWithBranch($id);
    		
    		if(!empty($rows)){
    			$ids = explode(',', $rows['all_land_id']);
    			if (!empty($rows['all_land_id'])){
    				foreach($ids as $land){ //unlock old house
    					$this->_name="ln_properties";
    					$arr = array(
    							"is_lock"=>0
    					);
    					$where = "id =".$land;
    					$this->update($arr, $where);
    				}
    			}
//     			if($rows['typesale']==2){ //case old Sale Multi sale
//     				$this->_name = 'ln_properties';
//     				$where="id = ".$rows["house_id"];
//     				$this->delete($where);
//     			}
    		}
    		
    		$arr = array(
    				'from_branchid'=>$data['branch_id'],
    				'from_houseid'=>$rows['house_id'],
    				'sale_id'=>$id,
    				'client_id'=>$data['member'],
    				'change_date'=>date('Y-m-d'),//$data['date_buy'],
    				'to_branchid'=>$data['to_branch_id'],
    				'to_houseid'=>$data['to_land_code'],
    				'note'=>'',//$data['note'],
    				'user_id'=>$this->getUserId(),
    				'typesale'=>$data['typesale'],
    		);
    		$this->_name="ln_change_house";
    		$where=" id =".$data['id'];
    		$changeid = $this->update($arr,$where);
    
    		$arr = array(
    				'branch_id'=>$data['branch_id'],
    				'house_id'=>$data["to_land_code"],
    				'typesale'=>$data['typesale'],
    		);
    		$dbGlobal = new Application_Model_DbTable_DbGlobal();
    		if (CONTRACT_NO_SETING==1){
    			$arrSale = array(
    					'branch_id'=>$data['to_branch_id'],
    					'land_code'=>$data['to_land_code'],
    			);
    			$arr['sale_number'] = $dbGlobal->getLoanNumber($arrSale);
    		}
    		$where = " id = ".$data['loan_number'];
    		$this->_name="ln_sale";
    		$id = $this->update($arr, $where);//add group loan
    		 
//     		$this->_name="ln_properties";
//     		$where=" id=".$data['land_code'];
//     		$arr = array(
//     				'is_lock'=>0
//     		);
//     		$this->update($arr, $where);//unlock old house
    		 
//     		$where=" id=".$data['to_land_code'];
//     		$arr = array(
//     				'is_lock'=>1
//     		);
//     		$this->update($arr, $where);//lock new house
    		$db->commit();
    		return 1;
    		 
    	}catch (Exception $e){
    		$db->rollBack();
    		$err =$e->getMessage();
    		echo $err;exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
    function getTransferProject($id){
    	$sql=" SELECT cp.*,
			(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.from_branchid LIMIT 1) AS fromBranchName,
			(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_houseid LIMIT 1) fromPropertyTitle,
			
			(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branchid LIMIT 1) AS toBranchTitle,
			(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_houseid LIMIT 1) toPropertyTitle,
			
			c.name_kh as clientName,
			(SELECT  	CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=cp.user_id limit 1 ) AS userName
	
		FROM `ln_change_house` AS cp,`ln_client` c WHERE c.client_id=cp.client_id AND cp.id= $id ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("from_branchid");
    	$sql.=" LIMIT 1 ";
    	
    	$db = $this->getAdapter();
    	return $db->fetchRow($sql);
    }
}