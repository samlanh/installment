<?php

class Loan_Model_DbTable_DdVerifySale extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getAllIndividuleLoan($search,$reschedule =null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$edit_sale = $tr->translate("EDITSALEONLY");
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	
    	$str = 'name_en';
    	if($lang==1){
    		$str = 'name_kh';
    	}
    	
    	$from_date =(empty($search['start_date']))? '1': " s.verifyDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.verifyDate <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
    	$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
    	$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
    	
    	$sql=" 
    	SELECT 
			vrf.`id` AS `id`
			,(SELECT
				`ln_project`.`project_name`
			FROM `ln_project`
			WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
			LIMIT 1 ) AS `branch_name`
			,`c`.`name_kh`         AS `name_kh`
			,`c`.`phone`         AS `phone`
			,`p`.`land_address`    AS `land_address`
			,`p`.`street`          AS `street`
			,(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype
			,vrf.`verifyDate` AS `verifyDate`
			,vrf.`priceBeforeNew`
			,vrf.`priceSoldNew`
			,vrf.`paidAmountNew`
			,vrf.`balanceNew`
			,(SELECT  first_name FROM rms_users WHERE id=vrf.user_id limit 1 ) AS user_name
			,vrf.`priceBefore`
			,vrf.`priceSold`
			,vrf.`paidAmount`
			,vrf.`balance`
			,CASE    
				WHEN  `vrf`.`status` = 1 THEN '".$imgtick."'
				WHEN  `vrf`.`status` = 0 THEN '".$imgnone."'
				END AS status
		FROM 
			ln_verificaton_sale AS vrf 
			JOIN `ln_sale` `s` ON s.id = vrf.saleId
			LEFT JOIN ln_client AS c ON `c`.`client_id` = `s`.`client_id`
			LEFT JOIN ln_properties AS p ON `p`.`id` = `s`.`house_id`
		WHERE 
			1 
			AND s.is_verify = 1
	   
	   ";
    	
    	$db = $this->getAdapter();
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_address LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_en LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.phone LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.comission LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	if($search['co_id']>0){
//     		$where.= " AND s.staff_id = ".$search['co_id'];
    		$condiction = $dbp->getChildAgency($search['co_id']);
    		if (!empty($condiction)){
    			$where.=" AND s.staff_id IN ($condiction)";
    		}else{
    			$where.=" AND s.staff_id=".$search['co_id'];
    		}
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	if(!empty($search['land_id']) AND $search['land_id']>-1){
    		$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if(($search['schedule_opt'])>0){
    		$where.= " AND s.payment_id = ".$search['schedule_opt'];
    	}
    	if(!empty($search['streetlist'])){
    		$where.= " AND p.street = '".$search['streetlist']."'";
    	}	
    	$order = " ORDER BY s.id DESC";
    	
    	$where.=$dbp->getAccessPermission("`s`.`branch_id`");
    	return $db->fetchAll($sql.$where.$order);
    }
	
	public function addVerifySale($_data){
		try{
			$db= $this->getAdapter();
			
			$saleId = empty($_data['saleId']) ? 0 : $_data['saleId'];
			$dbSale	= new Loan_Model_DbTable_DbRepaymentSchedule();
			$row	= $dbSale->getLoanInfoById($saleId);
			
			$priceSold = empty($row['price_sold']) ? 0 : $row['price_sold'];
			$totalPrincipalpaid = empty($row['total_principal']) ? 0 : $row['total_principal'];
			$balance = $priceSold - $totalPrincipalpaid;
			if($balance<0){
				$balance = 0;
			}
			
			$arrVerification = array(
					'saleId'	  		=> $saleId,
					'buyDate'	  		=> $row['buy_date'],
					'house_id'	  		=> $row['house_id'],
					'client_id'	  		=> $row['client_id'],
					'priceBefore'	  	=> $row['price_before'],
					'priceSold'	  		=> $priceSold,
					'paidAmount'	  	=> $totalPrincipalpaid,
					'balance'	  		=> $balance,
					
					'priceBeforeNew'	=> $_data['sold_price'],
					'priceSoldNew'	  	=> $_data['sold_price'],
					'paidAmountNew'	  	=> $_data['totalPrincipalpaid'],
					'balanceNew'	  	=> $_data['totalBalance'],
					'note'	  			=> $_data['note'],
					
					'is_verify'	  		=> 1,
					'user_id'	  		=> $this->getUserId(),
					'verify_by'	  		=> $this->getUserId(),
					'verifyDate'	  => date('Y-m-d'),
				);
			$this->_name="ln_verificaton_sale";
			$this->insert($arrVerification);
			
			$arr1 = array(
					'is_verify'	  		=> 1,
					'price_before'	  	=> $_data['sold_price'],
					'discount_amount'	  	=> 0,
					'discount_percent'	  	=> 0,
					'price_sold'	  	=> $_data['sold_price'],
					'paid_amount'	  	=> $_data['totalPrincipalpaid'],
					'balance'	  		=> $_data['totalBalance'],
					'verify_by'	  		=> $this->getUserId(),
					'verifyDate'	  	=> date('Y-m-d'),
				);
			$where="id = ".$saleId;
			$this->_name="ln_sale";
			$this->update($arr1, $where);
			
			$isCompleated = 0;
			if($_data['totalBalance']<=0){
				$isCompleated=1;
			}
			$arrClientReceipt = array(
				
				'outstanding'                   =>	$_data['sold_price'],//ប្រាក់ដើមមុនបង់
    			'selling_price'    				=>  $_data['sold_price'],
    			'total_principal_permonth'		=>	$_data["totalPrincipalpaid"],//ប្រាក់ដើមត្រូវបង់
    			'total_principal_permonthpaid'	=>	$_data["totalPrincipalpaid"],//ប្រាក់ដើមត្រូវបង់
    			
    			'total_payment'					=>	$_data["totalPrincipalpaid"],//ប្រាក់ត្រូវបង់ok
    			'principal_amount'				=>	$_data['totalPrincipalpaid'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    			'balance'						=>	$_data["totalBalance"],
				
    			'recieve_amount'				=>	$_data["totalPrincipalpaid"],//ok
    			'amount_payment'				=>	$_data["totalPrincipalpaid"],//brak ban borng
    			'return_amount'					=>	0,//ok
				
    			'user_id'						=>	$this->getUserId(),
    			'status'						=>	1,
    			'is_completed'					=>	$isCompleated,
    		    'field3'						=>  3,
				);
			$whereClientReceipt=" sale_id = ".$saleId;
			$this->_name="ln_client_receipt_money";
			$this->update($arrClientReceipt, $whereClientReceipt);
			
			$dbc = new Application_Model_DbTable_DbGlobal();
			if (!empty($_data['identity1'])){
				$part= PUBLIC_PATH.'/images/document/expense/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				
				$identity = $_data['identity1'];
				$ids = explode(',', $identity);
				$image_name="";
				$photo="";
				foreach ($ids as $i){
					if(!empty($_FILES['attachment'.$i])){
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$newDocName = "document_payment_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$newDocConvert = $dbc->resizeImase($_FILES['attachment'.$i], $part,$newDocName);
							if(!empty($newDocConvert)){
								$photo = $newDocConvert;
								$arr = array(
										'exspense_id'		=>$saleId,
										'document_name'		=>$photo,
										'title'				=>$_data['title_'.$i],
										'date'   			=> date('Y-m-d H:i:s'),
										'documentforType'   =>4, //documentforType = 4 is document of verify sale
								);
								$this->_name = "ln_expense_document";
								$this->insert($arr);
							}
						}
					}
				}
			}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
}

