<?php

class Loan_Model_DbTable_DbLoanILPayment extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_client_receipt_money';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    public function getAllIndividuleLoan($search){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$delete=$tr->translate('DELETE');
    	$db = $this->getAdapter();
    	
    	$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
    	$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
    	$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
    	
    	$sql = "SELECT lcrm.`id`,
    				(SELECT project_name FROM `ln_project` WHERE br_id=lcrm.branch_id LIMIT 1) AS branch_name,
					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcrm.`client_id` limit 1) AS team_group ,
					(SELECT land_address FROM `ln_properties` WHERE ln_properties.id=lcrm.land_id limit 1) AS land_id,
					(SELECT street FROM `ln_properties` WHERE ln_properties.id=lcrm.land_id limit 1) AS street,
					lcrm.`receipt_no`,
					lcrm.`total_principal_permonth`,
					lcrm.`total_interest_permonth`,
					lcrm.`penalize_amount`,
					lcrm.service_charge,
					lcrm.`total_payment`,
					lcrm.`recieve_amount`,
				    (SELECT lcrmd.`date_payment` from ln_client_receipt_money_detail AS lcrmd WHERE lcrm.id=lcrmd.`crm_id` ORDER BY lcrmd.id DESC LIMIT 1 ) AS date_payment,
					lcrm.`date_input`,
					(SELECT name_kh FROM `ln_view` WHERE type=7 AND key_code=lcrm.`payment_option`) AS payment_method,
					(SELECT  first_name FROM rms_users WHERE id=lcrm.user_id limit 1 ) AS user_name,
					CASE    
						WHEN  `lcrm`.`status` = 1 THEN '".$imgtick."'
						WHEN  `lcrm`.`status` = 0 THEN '".$imgnone."'
					END AS status
				FROM `ln_client_receipt_money` AS lcrm WHERE 1 ";
    	$where ='';
    	$from_date =(empty($search['start_date']))? '1': " lcrm.date_input >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " lcrm.date_input <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['advance_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " lcrm.`receipt_no` LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcrm.`client_id` limit 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT c.`phone` FROM `ln_client` AS c WHERE c.`client_id`=lcrm.`client_id` limit 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT land_address FROM `ln_properties` WHERE ln_properties.id=lcrm.land_id limit 1) LIKE '%{$s_search}%'";
      	 	$s_where[] = " (SELECT street FROM `ln_properties` WHERE ln_properties.id=lcrm.land_id limit 1) LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>0){
    		$where.= " AND lcrm.branch_id = ".$search['branch_id'];
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$search['status'];
    	}
    	if($search['client_name']>0){
    		$where.=" AND lcrm.`client_id`= ".$search['client_name'];
    	}
    	if($search['paymnet_type']>0){
    		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
    	}
    	if($search['land_id']>0){
    		$where.=" AND (SELECT s.`house_id` FROM `ln_sale` AS s WHERE s.id = lcrm.sale_id LIMIT 1) = ".$search['land_id'];
    	}
    	if($search['payment_method']>0){
    		$where.=" AND lcrm.`payment_method`= ".$search['payment_method'];
    	}
    	
    	$order = " ORDER BY id DESC";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("lcrm.branch_id");
    	
    	return $db->fetchAll($sql.$where.$order);
    }

	function getIlPaymentByID($id){
		$db = $this->getAdapter();
		$sql="SELECT 
				  *
				  ,rm.id AS paymentid
				  ,rm.status as status_parent
				  ,rm.total_payment as total_payment_parent
				  ,rm.service_charge as service_charge_parent
				  ,rm.penalize_amount as penalize_amount_parent
				  ,rm.total_interest_permonth as total_interest_permonth_parent
				  ,(SELECT s.price_sold FROM ln_sale AS s WHERE s.id = rm.sale_id LIMIT 1) AS price_sold
				FROM
				  `ln_client_receipt_money` AS rm LEFT JOIN `ln_client_receipt_money_detail` AS rmd ON rm.id=rmd.`crm_id`
				WHERE rm.id = $id
				 ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("rm.branch_id");
		$sql.=" LIMIT 1 ";
		
		return $db->fetchRow($sql);
	}
	public function getIlDetail($id){
		$db = $this->getAdapter();
		$sql="SELECT
		(SELECT d.`date_payment` FROM `ln_client_receipt_money_detail` AS d WHERE d.`loan_number`=crmd.loan_number AND d.id NOT IN($id) ORDER BY d.`date_payment` DESC LIMIT 1) AS installment_date ,
		(SELECT crm.`date_input` FROM `ln_client_receipt_money` AS crm,`ln_client_receipt_money_detail` AS crmd WHERE crm.`id`=crmd.`crm_id` AND crm.`id` != $id
		AND crmd.`lfd_id` = (SELECT c.`lfd_id` FROM `ln_client_receipt_money_detail` AS c WHERE c.`crm_id`=$id LIMIT 1) ORDER BY crm.`id` DESC LIMIT 1)  AS last_pay_date ,
		(SELECT `currency_id` FROM `ln_client_receipt_money_detail` WHERE crm_id = $id LIMIT 1) AS `currency_type`,
		(SELECT crm.`recieve_amount` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS recieve_amount,
		(SELECT crm.`receiver_id` FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=$id ) AS receiver_id,
		(SELECT c.`client_number` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS client_number,
		(SELECT c.`name_kh` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id` LIMIT 1) AS name_kh,
		crmd.*
		FROM
		`ln_client_receipt_money_detail` AS crmd
		WHERE crmd.`crm_id` =$id";
		return $db->fetchAll($sql);
	}
	
	public function getAllIlDetail($id){
		$db = $this->getAdapter();
		$sql="SELECT
	
			(SELECT `currency_id` FROM `ln_client_receipt_money_detail` WHERE crm_id = crmd.`crm_id` LIMIT 1) AS `currency_type`,
			(SELECT c.`client_number` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id`) AS client_number,
			(SELECT c.`name_kh` FROM `ln_client` AS c WHERE crmd.`client_id`=c.`client_id`) AS name_kh,
			crmd.*
		FROM
			`ln_client_receipt_money_detail` AS crmd WHERE crmd.`crm_id` = $id";
		return $db->fetchAll($sql);
	}
    function getPrefixCode($branch_id){
    	$db  = $this->getAdapter();
    	$sql = " SELECT prefix FROM `ln_project` WHERE br_id = $branch_id  LIMIT 1";
    	return $db->fetchOne($sql);
    }
    public function getIlPaymentNumber($branch_id=1){
    	$this->_name='ln_client_receipt_money';
    	$db = $this->getAdapter();
    	$sql=" SELECT id  FROM $this->_name WHERE branch_id = $branch_id  ORDER BY id DESC LIMIT 1 ";
    	$pre = "";
    	$pre = $this->getPrefixCode($branch_id)."-P";
    	
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function deleteReceipt($receipt_id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$sql = "SELECT
    			crm.receipt_no,
	    		crm.sale_id,
				crm.total_principal_permonthpaid,
				crm.extra_payment,
				crm.selling_price,
				crm.allpaid_before,
				crm.outstanding
			
    		 FROM ln_client_receipt_money AS crm
    		 WHERE  crm.`id` = $receipt_id ";
    		$rsreceipt = $db->fetchRow($sql);
    		
    		$sale_id= $rsreceipt['sale_id'];
    		
    		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    		$user_id = $session_user->user_id;
    		
    		$arr_client_pay = array(
    				//'outstanding'                 =>  0,//ប្រាក់ដើមមុនបង់
    				'total_principal_permonth'		=>	0,//ប្រាក់ដើមត្រូវបង់
    				'total_interest_permonth'		=>	0,
    				'penalize_amount'				=>	0,
    				'service_charge'				=>	0,
    				'principal_amount'				=>	0,//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    				'total_principal_permonthpaid'	=>	0,//ok ប្រាក់ដើមបានបង
    				'total_interest_permonthpaid'	=>	0,//ok ការប្រាក់បានបង
    				'penalize_amountpaid'			=>	0,// ok បានបង
    				'service_chargepaid'			=>	0,// okបានបង
    				'balance'						=>	0,
    				'total_payment'					=>	0,//ប្រាក់ត្រូវបង់ok
    				'recieve_amount'				=>	0,//ok
    				'amount_payment'				=>	0,//brak ban borng
    				'allpaid_before'				=>  $rsreceipt['allpaid_before']-$rsreceipt['total_principal_permonthpaid']-$rsreceipt['extra_payment'],
    				'return_amount'					=>	0,//ok
    				'note'							=>	"លុប",
    				'user_id'						=>	$user_id,
    				'status'						=>	1,
    				'extra_payment' =>0,
    		);
    		$this->_name = "ln_client_receipt_money";
    		$where="id =".$receipt_id;
    		$this->update($arr_client_pay, $where);
    		
    		$arr = array(
    				'is_completed'=>0
    		);
    		$this->_name="ln_sale";
    		$where="id=".$sale_id;
    		$this->update($arr, $where);
    			
    		$sql = "SELECT
		    		crmd.*,
		    		crm.branch_id
		    		
		    		FROM
		    		`ln_client_receipt_money_detail` AS crmd,
		    		ln_client_receipt_money as crm
		    		WHERE
		    		crm.id = crmd.`crm_id`
		    		and crmd.`crm_id` = $receipt_id ";
    		$receipt_money_detail = $db->fetchAll($sql);
    		
    		//delete extra payment in schedule
    		$where ="crm_id=".$receipt_id;
    		$this->_name="ln_saleschedule";
    		$this->delete($where);
    		$branc_id=1;
    		
    		if(!empty($receipt_money_detail)){
    			foreach ($receipt_money_detail as $rs){
    				if(!empty($rs['lfd_id'])){
	    				$branc_id = $rs['branch_id'];
	    				$arra = array(
	    						'begining_balance' 	     => $rs['capital'],
	    						'begining_balance_after' => $rs['capital'],
	    						'ending_balance'         => $rs['remain_capital'],
	    						//"principal_permonth"	 => $rs['old_principal_permonth'],
	    						//'total_interest'   	 => $rs['old_interest'],
	    						//'total_payment'    	 => $rs['old_total_payment'],
	    						"principal_permonthafter"=> $rs['old_principal_permonth'],
	    						'total_interest_after'   => $rs['old_interest'],
	    						'total_payment_after'    => $rs['old_total_payment'],
	    						'is_completed'           => 0,
	    						'received_userid'=>		0
	    				);
	    				$where ="id=".$rs['lfd_id'];
	    				$this->_name="ln_saleschedule";
	    				$updated = $this->update($arra, $where);
	    				
	    				if($updated==0){//ករណីមានលុបខ្លះ ត្រូវបញ្ចូលឡើងវិញ
	    					$sql="SELECT (no_installment) FROM ln_saleschedule WHERE status=1 AND sale_id=$sale_id ORDER BY no_installment DESC ";
	    					$start_id = $db->fetchOne($sql);
	    					
	    					$this->_name="ln_saleschedule";
	    					$datapayment = array(
		    					'branch_id'=>$branc_id,
		    					'sale_id'=>$sale_id,//good
		    					'begining_balance'=>$rs['capital'],//good
		    					'begining_balance_after'=>$rs['capital'],//good
		    					'principal_permonth'=>$rs['old_principal_permonth'],//good
		    					'principal_permonthafter'=>$rs['old_principal_permonth'],//good
		    					'total_interest'=>$rs['old_interest'],//good
		    					'total_interest_after'=>$rs['old_interest'],//good
		    					'total_payment'=>$rs['old_total_payment'],//good
		    					'total_payment_after'=>$rs['old_total_payment'],//good
		    					'ending_balance'=>$rs['remain_capital'],
		    					'cum_interest'=>0,//check more
		    					'amount_day'=>0,//check more
		    					'is_completed'=>0,
		    					'date_payment'=>$rs['date_payment'],
		    					'percent'=>100,
		    					'note'=>'',
	    						'status'=>1,
		    					'is_completed'=>0,
		    					'is_installment'=>1,
		    					'no_installment'=>$start_id+1,
	    						'received_userid'=>		0
	    				);
	    				$this->insert($datapayment);
    					}
    				}
    			}
    		}
    		$arr_money_detail = array(
    				'principal_permonth'	=>	0,
    				'total_interest'		=>	0,
    				'total_payment'			=>	0,
    				'total_recieve'			=>	0,
    				'pay_after'				=>	0,
    				'penelize_amount'		=>	0,
    				'service_charge'		=>	0,
    				'penelize_new'			=>	0,
    				'service_charge_new'	=>	0,
    				'capital'				=>  0,
    				'remain_capital'		=>	0, 
    				'old_principal_permonth'=>	0,
    				'old_total_priciple'	=>	0,
    				'old_interest'			=>	0,
    				'old_total_payment'		=>	0,
    				'old_penelize'			=>	0,
    				'old_service_charge'	=>	0,
//     				'last_pay_date'			=>	$row["date_payment"],
//     				'paid_date'			=>	$data["collect_date"],
    				'is_completed'			=>	0,//$is_compleated,
    				'status'				=>	1
    		);
    		
    		$where = " crm_id = ".$receipt_id;
    		$this->_name="ln_client_receipt_money_detail";
    		$this->update($arr_money_detail, $where);
    		
    		$db->commit();
    	}catch(Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function checkifExistingDelete($id){
    	$db = $this->getAdapter();
    	$sql="SELECT recieve_amount FROM `ln_client_receipt_money` WHERE id=$id AND recieve_amount>0";
    	return $db->fetchOne($sql);
    }
    function getSalebyId($sale_id){
    	$db= $this->getAdapter();
    	$sql="SELECT s.*,
				(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=$sale_id AND status=1 AND is_completed=0 LIMIT 1) as intallment
    		  FROM ln_sale AS s WHERE s.id = $sale_id LIMIT 1 "; 
    	return $db->fetchRow($sql);
    }
	public function addILPayment($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$user_id = $session_user->user_id;
    	try{
    		$reciept_no = $data['reciept_no'];
	    	$sql="SELECT id FROM ln_client_receipt_money WHERE receipt_no='$reciept_no' ORDER BY id DESC LIMIT 1 ";
	    	$acc_no = $db->fetchOne($sql);
	    	$dbc = new Application_Model_DbTable_DbGlobal();
	    	if($acc_no){
	    		$reciept_no = $dbc->getReceiptByBranch(array("branch_id"=>$data["branch_id"]));
	    	}else{
	    		$reciept_no = $data['reciept_no'];
	    	}
    		
		    $loan_number = $data['loan_number'];    	
	    	$amount_receive = $data["amount_receive"];
	    	$amount_payment=$amount_receive;
	    	$total_payment = $data["total_payment"];
	    	$return = 0;
	    	$option_pay = $data["option_pay"];
	    	$is_compleated=0;
	    	if($amount_receive>=$total_payment){
	    		$is_compleated = 1;
	    	}
	    	
	    	$pay_off = 0;
	    	if($data["option_pay"]==4){//payoff
	    		$pay_off = 1;
	    	}
			
			$rs_sale = $this->getSalebyId($loan_number);//get sale by id
			$propertyId = empty($data['property_id'])?$rs_sale['house_id']:$data['property_id'];
			$clientId 	= empty($data['client_id'])?$rs_sale['client_id']:$data['client_id'];
			
			$soldPrice = empty($rs_sale['price_sold']) ? 0 : $rs_sale['price_sold'];
			$soldPrice 	= empty($data['sold_price']) ? $soldPrice : $data['sold_price'];
			
			$datePaymentForReceipt = $data['date_payment']; // set defualt
			
    		$arr_client_pay = array(
    			'branch_id'						=>	$data["to_branch_id"],//$data["branch_id"],
    			'receipt_no'					=>	$reciept_no,
    			'date_pay'					    =>	$data['collect_date'],
    			'date_input'					=>	$data['collect_date'],
    			'from_date'						=>	$data['date_payment'],//check more
    			'sale_id'						=>	$data['loan_number'],
				
				//'client_id'                   =>	$data['client_id'],
    			//'land_id'						=>	$data['property_id'],
				
				'client_id'                     =>	$clientId,
    			'land_id'						=>	$propertyId,
    			'selling_price'					=>	$soldPrice,
				
    			'outstanding'                   =>	$data['outstanding_balance'],//ប្រាក់ដើមមុនបង់
    			//'selling_price'    				=>  $data['sold_price'],
    			'total_principal_permonth'		=>	$data["os_amount"]+$data["extrapayment"],//ប្រាក់ដើមត្រូវបង់
    			'total_interest_permonth'		=>	$data["total_interest"],
    			'penalize_amount'				=>	$data["penalize_amount"],
    			'total_payment'					=>	$data["total_payment"],//ប្រាក់ត្រូវបង់ok
    			'service_charge'				=>	$data["service_charge"],
    			'principal_amount'				=>	$data['priciple_amount'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    			'balance'						=>	$data["remain"],
    			'recieve_amount'				=>	$amount_receive,//ok
    			'amount_payment'				=>	$amount_payment,//brak ban borng
    			'return_amount'					=>	$return,//ok
    			'note'							=>	$data['note'],
    			'cheque'						=>	$data['cheque'],
    			'user_id'						=>	$user_id,
    			'payment_option'				=>	$data["option_pay"],
    			'status'						=>	1,
    			'is_completed'					=>	$is_compleated,
    		    'field3'						=>  3,
    			'is_payoff'						=>  $pay_off,
    			'extra_payment' 				=>  $data["extrapayment"],
    			'payment_times'					=>  $data['paid_times'],
    			'payment_method'				=>  $data['payment_method'],
				'bank_id'						=>  $data['bank_id'],
				
    			'date_payment'				=>  $datePaymentForReceipt,
    		);
    		
			$this->_name = "ln_client_receipt_money";
    		$client_pay = $this->insert($arr_client_pay);
    		
    		//$date_collect = $data["collect_date"];
    		$paid_principalall = 0;// ត្រូវទាំងអស់ព្រោះការពារបង់២ record ទី៣ វាបូកបញ្ចូល Paid អោយដែ
    		$paid_interestall = 0;
    		$paid_penaltyall = 0;
    		$paid_serviceall = 0;
    		$set_service=0;//សម្រាប់បង់ថ្លៃសេវាកម្មតែម្តង
    		$set_penalty=0;//សម្រាប់បង់ថ្លៃផាគពិន័យតែម្តង
    		
    		$service_charge= $data["service_charge"];//សេវាផ្សេងៗ
    		$penalize = empty($data["penalize_amount"]) ? 0 : $data["penalize_amount"];//ផាកពិន័យ
    		$total_interest = $data["total_interest"];//ត្រូវបង់សរុប
    		
    			$rows = $this->getSaleScheduleById($loan_number, 1);
				if(!empty($rows)){
					$remain_money = round($data['amount_receive']-$data['extrapayment'],2);
					foreach ($rows AS $key => $row){
						if($remain_money<=0){
							break;
						}
		    			if($data['option_pay']!=3){//ព្រោះបញ្ចូលជា Extra payment hz
		    				$arr_money_detail = array(
    							'crm_id'				=>	$client_pay,
    							'land_id'			    =>	$propertyId,//$data['property_id'],//ok
    							'lfd_id'				=>	$row["id"],//ok
    							'date_payment'			=>	$row["date_payment"], // ថ្ងៃដែលត្រូវបង់
    							'principal_permonth'	=>	0,
    							'total_interest'		=>	0,
    							'total_payment'			=>	0,
    							'total_recieve'			=>	0,
    							'pay_after'				=>	0,
    							'penelize_amount'		=>	0,
    							'service_charge'		=>	0,
    							'penelize_new'			=>	0,
    							'service_charge_new'	=>	0,
    							'capital'				=>  $row["begining_balance_after"],
    							'remain_capital'		=>	$row["ending_balance"], // remain balance after paid
    							'old_principal_permonth'=>	$row['principal_permonthafter'],
    							'old_total_priciple'	=>	$row["principal_permonthafter"],
    							'old_interest'			=>	$row["total_interest_after"],
    							'old_total_payment'		=>	$row['total_payment_after'],
    							'old_penelize'			=>	$row['penelize'],
    							'old_service_charge'	=>	$row['service_charge'],
    							'last_pay_date'			=>	$row["date_payment"],
    							'paid_date'				=>	$data["collect_date"],
    							'is_completed'			=>	1,
    							'status'				=>	1);
		    				$this->_name='ln_client_receipt_money_detail';
		    				$this->insert($arr_money_detail);
		    			}			
		    						
							$after_outstanding = $row['begining_balance_after'];
							$after_payment_after = $row['total_payment_after'];
							$after_principal = $row['principal_permonthafter'];//$data["principal_permonth_".$i];
							$total_principal = $after_principal;
							$after_interest = $row['total_interest_after'];
							
							if($option_pay==1){
								$total_interest = $after_interest;
							}
							$after_penalty = $row['penelize'];//$data["penelize_".$i];
							$date_payment = $row['date_payment'];//$data["date_payment_".$i];
							
							$datePaymentForReceipt = $date_payment;
							
							$paid_principal = 0;
							$paid_interest = 0;
							$paid_penalty = 0;
							$paid_service = 0;
							$is_compleated_d=0;
							
							if($key!=0){
								$penalize = 0;//ធ្លាប់បងហើយម្តង អោយ =0
								$service_charge=0;
								if($option_pay==4 OR $option_pay==3 ){
									$total_interest=0;
								}
							}
							if($option_pay==1){
								$total_principal =$after_principal;
							}elseif($option_pay==3){
								$total_principal = $after_principal;//$data["principal_permonth_".$i];
							}
							
							$remain_money = round($remain_money-$service_charge,2);
							if($remain_money>=0){//ដកសេវាកម្ម
								$paid_service=$service_charge;
								$after_service=0;
								$remain_money = round($remain_money - $penalize,2);
									
								if($remain_money>=0){//ដកផាគពិន័យ
									$paid_penalty = $penalize;
									$remain_money = round($remain_money - $total_interest,2);
									if($remain_money>=0){
										$paid_interest = $total_interest;
										$after_interest = 0;
										$remain_money = round($remain_money-$total_principal,2);
										if($remain_money>=0){//check here of គេបង់លើសខ្លះ
											$paid_principal = $total_principal;
											$after_principal = 0;
											$is_compleated_d=1;
										}else{
											$paid_principal = $total_principal-abs($remain_money);
											$after_principal = abs($remain_money);
											$is_compleated_d=0;
										}
									}else{
										$paid_interest = $total_interest-abs($remain_money);
										$after_interest =abs($remain_money);
									}
								}else{
									$paid_penalty =$penalize -abs($remain_money);
									$after_penalty = abs($remain_money);
								}
							}else{
								$paid_service=$service_charge-abs($remain_money);
								$after_service = abs($remain_money);
							}		    						
							
							//if($after_principal<=0){
								//$is_compleated_d=1;
							//}
							$total_payment_after = $after_principal+$after_interest;
							if($total_payment_after<=0){
								$is_compleated_d=1;
							}
							
							if($data['option_pay']!=3){//ព្រោះបញ្ចូលជា Extra payment hz
								 $arra = array(
										'begining_balance_after'=>$after_outstanding-$paid_principal,
										"principal_permonthafter"=>$after_principal,
										'total_interest_after'=>$after_interest,
										'total_payment_after'=>	$after_principal+$after_interest,
										'is_completed'=>$is_compleated_d,
										'paid_date'	=>	$data['collect_date'],
										'payment_option'	=>	$data["option_pay"],
										'paid_date'			=> 	$data['collect_date'],
										'received_userid'=> ($is_compleated_d==1)?$user_id:0,
										'received_date'=> ($is_compleated_d==1)?$data['collect_date']:0
								  );
								  $where = " id = ".$row['id'];
								  $this->_name="ln_saleschedule";
								  $this->update($arra, $where);	
								  
								  
								  if(AUTO_PAYCOMMISSION==1 AND $is_compleated_d==1 AND $row['commission']>0){
									$__data = array(
											'branch_id'      => $data['branch_id'],
											'sale_id'	     => $data['loan_number'],
											'sale_no' 		=> $data['loan_number'],
											'title'	         => '',
											'return_back'    => $row['commission'],
											'cheque'	     => '',
											'cheque_issuer'  => '',
											'other_invoice'  => '',
											'property_id'    => $propertyId,//$data['property_id'],
											'income_category'=> 16,
											'staff_id'		 => $data['co_id'],
											'payment_type'   => 1,
											'note'           => '',
											'date'           => $data['collect_date'],
											'supplier_id'    => '',
									);
									$db_exp = new Incexp_Model_DbTable_DbComission();
									$db_exp->addSaleComission($__data);
								  }
							}
							  
						$paid_principalall = $paid_principalall+$paid_principal;
						$paid_interestall = $paid_interestall+$paid_interest;
						$paid_penaltyall = $paid_penaltyall+$paid_penalty;
						$paid_serviceall = $paid_serviceall+$paid_service;
		    		}//end foreach 
		    	}
    		//}
    		$all_paidbefore = $dbc->getAllPaidBefore($data['loan_number']);
    		$arr = array(
    				'allpaid_before'				=> $all_paidbefore+$paid_principalall+$data["extrapayment"],
    				'total_principal_permonthpaid'	=> $paid_principalall,//ok ប្រាក់ដើមបានបង
    				'total_interest_permonthpaid'	=> $paid_interestall,//ok ការប្រាក់បានបង
    				'penalize_amountpaid'			=> $paid_penaltyall,// ok បានបង
    				'service_chargepaid'			=> $paid_serviceall,// okបានបង
    				'date_payment'			=> $datePaymentForReceipt,
    		);
    		$this->_name="ln_client_receipt_money";
    		$where = $db->quoteInto("id=?", $client_pay);
    		$this->update($arr, $where);
    		
    		////////////////////////////////////start extra payment///////////////////////////////////////
    		if($data['option_pay']==3){
    			$data['extrapayment'] = $data['amount_receive']-$data['total_interest']-$data['penalize_amount']-$data['service_charge'];
    		}
    		if($data['extrapayment']>0){
    			$extrapayment = $data['extrapayment'];
    			$extrapayment_after=$extrapayment;
    			$order=0;
//     			echo $data['schedule_opt'];exit();
    			if($data['schedule_opt']==4 OR $data['schedule_opt']==7){
    				$order=1;
    			}//ករណីរំលស់
    			
    			$rs = $this->getSaleScheduleById($loan_number,$order);
    			$principal = 0;
    			$last_record=0;
    			$ending_balance=0;
    			if(!empty($rs)){    				
//     				/*សម្រាប់បញ្ចូលថាប្រាក់ដើមប្រានរំលស់*/
    				$sql="SELECT (no_installment) FROM ln_saleschedule WHERE is_completed=1 AND status=1 AND sale_id=".$loan_number." ORDER BY no_installment DESC LIMIT 1 ";
    				$start_id = $db->fetchOne($sql);
    				
    					$this->_name="ln_saleschedule";
    					$datapayment = array(
    						'branch_id'=>$data['branch_id'],
    						'crm_id'=>$client_pay,//ទុកសម្រាប់លុប ពេលគេលុបបង្កាន់ដៃបង់ប្រាក់
    						'sale_id'=>$data['loan_number'],//good
    						'begining_balance'=> $data['outstanding_balance'],//good
    						'begining_balance_after'=> $data['outstanding_balance'],//good
    						'principal_permonth'=> $data['extrapayment'],//good
    						'principal_permonthafter'=> 0,//good
    						'total_interest'=>0,//good
    						'total_interest_after'=>0,//good
    						'total_payment'=> $data['extrapayment'],//good
    						'total_payment_after'=>0,//good
    						'ending_balance'=>$data['outstanding_balance']-$paid_principalall,
    						'cum_interest'=>0,
    						'amount_day'=>0,
    						'is_completed'=>1,
    						'date_payment'=>$data['collect_date'],
    						'percent'=>0,
    						'note'=>"",
    						'is_installment'=>0,
    						'no_installment'=>$start_id+1,
    						'status'=>0,
    						'collect_by'=>2,
    						'received_userid'=>$user_id
    					);
    				$this->insert($datapayment);
    				
    				$times = count($rs);
    				$rs_sale = $this->getSalebyId($loan_number);//get sale by id
    				
    				foreach ($rs as $index => $row){	//ដក Begining after ដើម្បីអោយបង្កាន់ដៃបង់លើកក្រោយចេញត្រូវនឹងប្រាក់ដែលធ្លាប់បានបង់
    					$arr = array(
    							'crm_id'				=>	$client_pay,
    							'land_id'			    =>	$propertyId,//$data['property_id'],//ok
    							'lfd_id'				=>	$row["id"],//ok
    							'date_payment'			=>	$row["date_payment"], // ថ្ងៃដែលត្រូវបង់
    							'principal_permonth'	=>	0,
    							'total_interest'		=>	0,
    							'total_payment'			=>	0,
    							'total_recieve'			=>	0,
    							'pay_after'				=>	0,
    							'penelize_amount'		=>	0,
    							'service_charge'		=>	0,
    							'penelize_new'			=>	0,
    							'service_charge_new'	=>	0,
    							'capital'				=>  $row["begining_balance_after"],
    							'remain_capital'		=>	$row["ending_balance"], // remain balance after paid
    							'old_principal_permonth'=>	$row['principal_permonthafter'],
    							'old_total_priciple'	=>	$row["principal_permonthafter"],
    							'old_interest'			=>	$row["total_interest_after"],
    							'old_total_payment'		=>	$row['total_payment_after'],
    							'old_penelize'			=>	$row['penelize'],
    							'old_service_charge'	=>	$row['service_charge'],
    							'last_pay_date'			=>	$row["date_payment"],
    							'paid_date'				=>	$data["collect_date"],
    							'is_completed'			=>	1,
    							'status'				=>	1);
    					
    					$db->insert("ln_client_receipt_money_detail", $arr);
    					
    					if($data['schedule_opt']==4 OR $data['schedule_opt']==7){//សម្រាប់រំលស់
	    					if($index==0){						
	    						//$begining_balance = $row['begining_balance_after']-$extrapayment;
								$begining_balance =  bcsub($row['begining_balance_after'],$extrapayment,2); 
	    						$remain_afterextrapayment = $begining_balance;//ប្រាក់ដើមនៅសល់ពីបង់រំលស់ដើម
	    						$interst_rate = ($data['interest_rate']/12/100);
							   if($interst_rate!=0){
			    					$top = pow(1+$interst_rate,$times);
			    					$bottom = pow(1+$interst_rate,$times)-1;
			    					$fixed_payment = round(($begining_balance*$interst_rate*$top/$bottom),0,PHP_ROUND_HALF_UP);//always round up
	    					   }else{
	    					   		$fixed_payment =$row['principal_permonthafter'];
	    					   }
	    					}else{
	    						$begining_balance = $ending_balance;
	    					}
	    					
		    				$total_interestafter = $begining_balance*$interst_rate;
		    				$total_interestafter = $this->round_up_currency(2, $total_interestafter);
		    				if($rs_sale['install_type']==2){//រំលស់ថយ ដើមថេរ
		    					$principal = $remain_afterextrapayment/$rs_sale['intallment'];
		    					$principal = $this->round_up_currency(2, $principal);
		    					if($index+1==$rs_sale['intallment']){//for end of record only
		    						$principal = $ending_balance;
		    					}
		    					$fixed_payment = $principal + $total_interestafter;
		    				}else{
		    					$principal = $fixed_payment-$total_interestafter;
		    					if($index+1==$rs_sale['intallment']){//for end of record only
		    						$principal = $ending_balance;
		    						$fixed_payment = $principal + $total_interestafter;
		    					}
		    				}
		    				$ending_balance = $begining_balance-$principal;
		    				
    					}else{//ក្រៅពីរំលស់ // ត្រូវដក បងបន្ថែមតែម្តងទេ សម្រាប់ប្រាក់ដើមរាល់ខែ
    						$total_interestafter=0;
    						$begining_balance = $row['begining_balance_after']-$extrapayment;
    						$extrapayment_after=$extrapayment_after-$row['principal_permonthafter'];
    						if($extrapayment_after>=0){
    							$principal=0;
    							$begining_balance=0;
    							$fixed_payment=0;
    						}else{
    							$principal=abs($extrapayment_after);
    							$extrapayment_after=0;
    							$fixed_payment=$principal;
    						}
    					}
    					if($data['schedule_opt']!=4){//ក្រៅរំលស់ គឺការប្រាក់សូន្យទាំងអស់
    						$interst_rate=0;
    					}
    					if($ending_balance<0){
    						$last_record = 1;
    						$principal = $begining_balance;
    						$fixed_payment = $principal+$interst_rate;
    						$ending_balance=0;
    					}
    					$is_completed=0;
    					if($principal<=0){
    						$is_completed=1;
    					}    	
    					if($last_record<=1){//check it ;
	    					$arra = array(
	    							'ending_balance'=>$begining_balance-$principal,
	    							'begining_balance'=>$begining_balance,
	    							'begining_balance_after'=>$begining_balance,
	    							'principal_permonth'=>$principal,
	    							'principal_permonthafter'=>$principal,
	    							'total_interest_after'=>$total_interestafter,
	    						    'total_interest'=>$total_interestafter,
	    							'total_payment'=>$fixed_payment,
	    							'total_payment_after'=>$fixed_payment,
								    'is_completed'=>$is_completed,
	    							);
	    					$where = "id = ".$row['id'];
	    					$this->_name="ln_saleschedule";
	    					if($begining_balance>0){
	    						$this->update($arra, $where);
	    					}else{//if រំលស់ដើមអស់ប្រាក់
	    						$this->delete($where);
	    					}
	    					if($last_record==1){
	    						$last_record=2;
	    					}
    					}else{
    						$where ="id = ".$row['id'];
    						$this->_name="ln_saleschedule";
    						$this->delete($where);
    					}
    				}
    			}
    		}
    		////////////////////////// end of extra payment ////////////////////////////
    		$rows = $this->getSaleScheduleById($loan_number, 1);
    		if(empty($rows)){
    			$this->updatePayoff($data['loan_number'],$client_pay);
    		}
			
			
			if (!empty($data['identity1'])){
				$part= PUBLIC_PATH.'/images/document/expense/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				
				$identity = $data['identity1'];
				$ids = explode(',', $identity);
				$image_name="";
				$photo="";
				foreach ($ids as $i){
					if(!empty($_FILES['attachment'.$i])){
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							Application_Model_DbTable_DbUserLog::writeMessageError($_FILES['attachment'.$i]["size"]);
							$newDocName = "document_payment_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$newDocConvert = $dbc->resizeImase($_FILES['attachment'.$i], $part,$newDocName);
							if(!empty($newDocConvert)){
								$photo = $newDocConvert;
								$arr = array(
										'exspense_id'		=>$client_pay,
										'document_name'		=>$photo,
										'title'				=>$data['title_'.$i],
										'date'   			=> date('Y-m-d H:i:s'),
										'documentforType'   =>3,
								);
								$this->_name = "ln_expense_document";
								$this->insert($arr);
							}
						}
					}
					
				}
			}
    		$db->commit();
    		return $client_pay;
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function round_up($value, $places)
    {
    	$mult = pow(10, abs($places));
    	return $places < 0 ?
    	ceil($value / $mult) * $mult :
    	ceil($value * $mult) / $mult;
    }
    function round_up_currency($curr_id, $value,$places=-2){
    	if ($curr_id==1){
    		return $this->round_up($value, $places);
    	}
    	else{
    		return round($value,2);
    	}
    }
    function addExtrapayment($data){//not used
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    	if($data['extrapayment']>0){
    		$extrapayment = $data['extrapayment'];
    		$extrapayment_after=$extrapayment;
    		$order=0;
    		if($data['schedule_opt']==4){
    			$order=1;
    		}//ករណីរំលស់
    		$loan_number=$data['loan_number'];
    		$client_pay = $data['receipt_id'];
    		$rs = $this->getSaleScheduleById($loan_number,$order);
    		$principal = 0;
    		$last_record=0;
    		$ending_balance=0;
    		if(!empty($rs)){
    			/*សម្រាប់បញ្ចូលថាប្រាក់ដើមប្រានរំលស់*/
    			$times = count($rs);
    			foreach ($rs as $index => $row){//ដក Begining after ដើម្បីអោយបង្កាន់ដៃបង់លើកក្រោយចេញត្រូវនឹងប្រាក់ដែលធ្លាប់បានបង់
    				if($data['schedule_opt']==4){//សម្រាប់រំលស់
    					if($index==0){
    						$begining_balance = $row['begining_balance_after']-$extrapayment;
    						$interst_rate = ($data['interest_rate']/12/100);
    						if($interst_rate!=0){
    							$top = pow(1+$interst_rate,$times);
    							$bottom = pow(1+$interst_rate,$times)-1;
    							$fixed_payment = ceil($begining_balance*$interst_rate*$top/$bottom);//always round up
    						}else{
    							$fixed_payment =$row['principal_permonthafter'];
    						}
    					}else{
    						$begining_balance = $ending_balance;
    					}
    					
    					$total_interestafter=$begining_balance*$interst_rate;
    					$total_interestafter = $this->round_up_currency(2, $total_interestafter);
    					$principal = $fixed_payment-$total_interestafter;
    					$ending_balance = $begining_balance-$principal;
    				}else{//ក្រៅពីរំលស់ // ត្រូវដក បងបន្ថែមតែម្តងទេ សម្រាប់ប្រាក់ដើមរាល់ខែ
    					$total_interestafter=0;
    					$begining_balance = $row['begining_balance_after']-$extrapayment;
    					$extrapayment_after=$extrapayment_after-$row['principal_permonthafter'];
    					if($extrapayment_after>=0){
    						$principal=0;
    						$begining_balance=0;
    						$fixed_payment=0;
    					}else{
    						$principal=abs($extrapayment_after);
    						$extrapayment_after=0;
    						$fixed_payment=$principal;
    					}
    				}
    				if($data['schedule_opt']!=4){//ក្រៅរំលស់ គឺការប្រាក់សូន្យទាំងអស់
    					$interst_rate=0;
    				}
    				if($ending_balance<0){
    					$last_record = 1;
    					$principal = $begining_balance;
    					$fixed_payment = $principal+$interst_rate;
    					$ending_balance=0;
    				}
    				$is_completed=0;
    				if($principal<=0){
    					$is_completed=1;
    				}
    				if($last_record<=1){//check it ;
    					$arra = array(
    							'ending_balance'=>$begining_balance-$principal,//$row['begining_balance']-$extrapayment,
    							'begining_balance'=>$begining_balance,//$row['begining_balance']-$extrapayment,
    							'begining_balance_after'=>$begining_balance,//$row['begining_balance_after']-$extrapayment,
    							'principal_permonth'=>$principal,
    							'principal_permonthafter'=>$principal,
    							'total_interest_after'=>$total_interestafter,//ok
    							'total_interest'=>$total_interestafter,
    							'total_payment'=>$fixed_payment,
    							'total_payment_after'=>$fixed_payment,//$row['total_payment']-$principal_paid-$row['total_interest_after'],
    							'is_completed'=>$is_completed
    					);
    					$where = "id = ".$row['id'];
    					$this->_name="ln_saleschedule";
    					if($begining_balance>0){
    						$this->update($arra, $where);
    					}else{//if រំលស់ដើមអស់ប្រាក់
    						$this->delete($where);
    					}
    	
    					if($last_record==1){
    						$last_record=2;
    					}
    				}else{
    					$where ="id = ".$row['id'];
    					$this->_name="ln_saleschedule";
    					$this->delete($where);
    				}
    			}
    		}
    	}
    	////////////////////////// end of extra payment ////////////////////////////
    	$rows = $this->getSaleScheduleById($loan_number, 1);
    	if(empty($rows)){
    		$this->updatePayoff($data['loan_number'],$client_pay);
    	}
    	$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    function updatePayoff($sale_id,$receipt_id){
    	$this->_name='ln_client_receipt_money';
    	$where = " id =".$receipt_id;
    	$data= array(
    			"is_payoff"=>1,
    			'payment_option'=>4
    	);
    	$this->update($data, $where);
    }
    function getSaleScheduleById($loan_number,$orderby=1){
    	$db = $this->getAdapter();
    	$sql="select * from  ln_saleschedule WHERE status=1 AND is_completed=0 AND sale_id=$loan_number ";
    	if($orderby==1){
    		$sql.=" ORDER BY id ASC ";
    	}else{
    		$sql.=" ORDER BY id DESC ";
    	}
		return $db->fetchAll($sql); 
    }
    function updateIlPayment($data,$id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$is_set = 0;
    	try{
    		if($data['status']==0){
    			if($data['option_pay']==4){//payoff update  ទៅជាOld History សិនទើប update  ក្រោយ Check it again 
    				$sql1="select sale_id as sale_id ,lfd_id as saleschedule_id from ln_client_receipt_money_detail where crm_id=$id";
    				$result = $db->fetchAll($sql1);
    				if(!empty($result)){
    					foreach ($result as $_data){
    						$array = array(
    								'is_completed'=>0,
    								);
    						$where=" id = ".$_data['saleschedule_id']." and sale_id = ".$_data['sale_id'];
    						$this->_name="ln_saleschedule";
    						$this->update($array, $where);
    					}
    				}
    				
    				$arr = array(
    						'status'=>0,
    						);
    				$where = " id = $id";
    				$this->_name="ln_client_receipt_money";
    				$this->update($arr, $where);
    				
    				$where1 = " crm_id = $id";
    				$this->_name="ln_client_receipt_money_detail";
    				$this->update($arr, $where1);
    			}else{//normal
    				
    				$old_rs = $this->getAllReceiptMoneyDetail($id);//if payoff?
    				if(!empty($old_rs)){
    					foreach($old_rs AS $rowrm){
    						$array =  array(
    								'begining_balance_after'	=>$rowrm['capital'],
    								'ending_balance'=>$rowrm['remain_capital'],
    								'principal_permonthafter'	=>$rowrm['old_principal_permonth'],
    								'total_interest_after'		=>$rowrm['old_interest'],
    								'total_payment_after'		=>$rowrm['old_total_payment'],
    								'penelize'					=>$rowrm['old_penelize'],
    								'service_charge'			=>$rowrm['old_service_charge'],
    								'is_completed'				=>0,
    						);
    						$where=" id = ".$rowrm['lfd_id'];
    						$this->_name="ln_saleschedule";
    						$this->update($array, $where);
    					}
    				} 	    			
	    			$arr = array(
    						'status'=>0,
    						);
    				$where = " id = $id";
    				$this->_name="ln_client_receipt_money";
    				$this->update($arr, $where);
    				
    				$where1 = " crm_id = $id";
    				$this->_name="ln_client_receipt_money_detail";
    				$this->update($arr, $where1);
    			}
    		}else{	//status active
    			$old_rs = $this->getAllReceiptMoneyDetail($id);//if payoff?
    			if(!empty($old_rs)){
    				if($data['option_pay']==1){
    					foreach($old_rs AS $rowrm){
    						$array =  array(
    								'begining_balance_after'	=>$rowrm['capital'],
    								'ending_balance'=>$rowrm['remain_capital'],
    								'principal_permonthafter'	=>$rowrm['old_principal_permonth'],
    								'total_interest_after'		=>$rowrm['old_interest'],
    								'total_payment_after'		=>$rowrm['old_total_payment'],
    								'penelize'					=>$rowrm['old_penelize'],
    								'service_charge'			=>$rowrm['old_service_charge'],
    								'is_completed'				=>0,);
    						$where=" id = ".$rowrm['lfd_id'];
    						$this->_name="ln_saleschedule";
    						$this->update($array, $where);
    					}
    					
    					$this->_name="ln_client_receipt_money_detail";
    					$where = " crm_id = $id";
    					$this->delete($where);
    					
    					$paid_amount = $data['amount_receive']-$data['extrapayment'];
    					$remain_principal=0;
    					$statuscomplete=0;
    					$principal_paid = 0;
    					$total_interest = 0;
    					$total_principal=0;
    					$total_interestpaid =0;
    					$remain_principal=0;
    					$old_paid=0;
    					$old_interest = 0;
    					$total_interestafter=0;
    					$penalize=0;$service_amount=0;
    					$rows = $this->getSaleScheduleById($data['loan_number'], 1);
    					if(!empty($rows)){
    						foreach ($rows as $row){
    							$old_interest=$paid_amount;
    							$paid_amountbefore = $paid_amount;
    							$paid_amount= $paid_amount-$data['service_charge'];
    							if($paid_amount>=0){
    								$service_amount =$data['service_charge'];
    								$paid_amount = $paid_amount-$data['penalize_amount'];
    								if($paid_amount>=0){
    									$penalize = 0;//$data['penalize_amount'];
    								}else{
    									$penalize = $data['penalize_amount']- abs($paid_amount);
    								}
    							}else{
    								$service_amount= $data['service_charge'] - abs($paid_amount);
    							}
    							
    							$paid_amount = $paid_amount-$row['total_interest_after'];
    							if($paid_amount>=0){
    								$total_interestafter=0;
    								$total_interestpaid=$row['total_interest_after'];
    								$old_paid = $paid_amount;
    								$paid_amount = $paid_amount-$row['principal_permonthafter'];
    								
    								if($paid_amount>=0){
    									$principal_paid = $row['principal_permonthafter'];
    									$statuscomplete=1;
    									$remain_principal=0;
    								}else{
    									$principal_paid = ($old_paid);
    									$remain_principal=abs($paid_amount);
    									$statuscomplete=0;
    								}
    							}else{
    								$remain_principal = 0;
    								$statuscomplete=0;
    								$principal_paid=0;
    								$total_interestpaid=($old_interest);
    								$total_interestafter=$total_interestpaid;
    							}
    							
    							$total_interest=$total_interest+$total_interestpaid;//ok
    							$total_principal = $total_principal+$principal_paid;
    							$pyament_after = $row['total_payment_after']-($principal_paid);//ប្រាក់ត្រូវបង់លើកក្រោយសំរាប់ installmet 1 1
    							$arra = array(
    									"principal_permonthafter"=>$remain_principal,
    									'total_interest_after'=>$row['total_interest_after']-$total_interestpaid,
    									'begining_balance_after'=>$row['begining_balance_after']-($principal_paid),
    									'ending_balance'=>$row['begining_balance_after']-($principal_paid+$remain_principal),
    									'is_completed'=>$statuscomplete,
    									'penelize'=>$penalize,
    									'paid_date'			=> 	$data['collect_date'],
    									'total_payment_after'	=>	$remain_principal+($row['total_interest_after']-$total_interestpaid),
    							);
    							$where = " id = ".$row['id'];
    							$this->_name="ln_saleschedule";
    							$this->update($arra, $where);
    							
    							$this->_name='ln_client_receipt_money_detail';
    							$array = array(
    									'crm_id'				=>$id,
    									'lfd_id'				=>$row['id'],
    									'client_id'				=>$data['client_id'],
    									'land_id'				=>$data['loan_number'],
    									'date_payment'			=>$row['date_payment'],
    									'paid_date'             =>$data['collect_date'],
    									'capital'				=>$row['begining_balance'],
    									'remain_capital'		=>$row['begining_balance']-$principal_paid,
    									'principal_permonth'	=>$principal_paid,
    									'total_interest'		=>0,
    									'total_payment'			=>$principal_paid+$total_interestpaid,
    									'total_recieve'			=>$principal_paid+$total_interestpaid,
    									'service_charge'		=>0,
    									'penelize_amount'		=>0,
    									'is_completed'			=>$statuscomplete,
    									'status'				=>1,
    									'old_interest'			 =>$row["total_interest_after"],
    									'old_principal_permonth'=>$row["principal_permonthafter"],
    									'old_total_payment'	 =>$row["total_payment_after"],
    							);
    							$this->insert($array);
    							if($paid_amount<=0){
    								break;
    							}
    						}
    						/////////////update receipt money
    						$loan_number = $data['loan_number'];
    						$amount_receive = $data["amount_receive"];
    						$total_payment = $data["total_payment"];
    						$return =0;// $data["amount_return"];
    						$option_pay = $data["option_pay"];
    						
    						if($amount_receive>$total_payment){
    							$amount_payment = $amount_receive - $return;
    							$is_compleated = 1;
    						}elseif($amount_receive<$total_payment){
    							$amount_payment = $amount_receive;
    							$is_compleated = 0;
    						}else{
    							$amount_payment = $total_payment;
    							$is_compleated = 1;
    						}
    						$principle = $data["os_amount"];
    						$penelize  = $data["penalize_amount"];
    						$service_charge = $data["service_charge"];
    						$interest = $data["total_interest"];
    						$total_pay = $data["total_payment"];
    						$recieve = $data["amount_receive"]-$data["extrapayment"];//-$data['amount_return'];
    						
    						$new_service = $recieve-$service_charge;
    						if($new_service>=0){
    							$service = $service_charge;
    							$new_penelize = $new_service - $penelize;
    							if($new_penelize>=0){
    								$penelize_amount =  $penelize;
    								$new_interest = $new_penelize - $interest;
    								if($new_interest>=0){
    									$interest_amount = $interest;
    									$new_printciple = $new_interest - $principle;
    									if($new_printciple>=0){
    										$principle_amount = $principle;
    									}else{
    										$principle_amount = abs($new_interest);
    									}
    								}else{
    									$interest_amount = abs($new_penelize);
    									$principle_amount=0;
    								}
    							}else{
    								$penelize_amount = abs($new_service);
    								$interest =0;
    								$principle_amount=0;
    							}
    						}else{
    							$service = abs($recieve);
    							$penelize_amount = 0;
    							$interest =0;
    							$principle_amount=0;
    						}
    						
    						$service_charge= $data["service_charge"];
    						$penalize = $data["penalize_amount"];
    						$arr_client_pay = array(
    								'branch_id'						=>	$data["branch_id"],
    								'receipt_no'					=>	$data['reciept_no'],
    								'date_pay'					    =>	$data['collect_date'],
    								'date_input'					=>	date("Y-m-d"),
    								'client_id'                     =>	$data['client_id'],
    								'sale_id'						=>	$data['loan_number'],
    								'land_id'						=>	$data['property_id'],
    								'outstanding'                   =>	$data['priciple_amount']+$principle_amount,//ប្រាក់ដើមមុនបង់
    								'total_principal_permonth'		=>	$data["os_amount"],//ប្រាក់ដើមត្រូវបង់
    								'total_interest_permonth'		=>	$data["total_interest"],
    								'penalize_amount'				=>	$penalize,
    								'service_charge'				=>	$data["service_charge"],
    								'principal_amount'				=>	$data['priciple_amount'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    								'total_principal_permonthpaid'	=>	$principle_amount,//ok ប្រាក់ដើមបានបង
    								'total_interest_permonthpaid'	=>	$interest_amount,//ok ការប្រាក់បានបង
    								'penalize_amountpaid'			=>	$penelize_amount,// ok បានបង
    								'service_chargepaid'			=>	$service,// okបានបង
    								'balance'						=>	$data["remain"],
    								'total_payment'					=>	$data["total_payment"],//ប្រាក់ត្រូវបង់ok
    								'recieve_amount'				=>	$data['amount_receive'],//ok
    								'amount_payment'				=>	$amount_payment,//brak ban borng
    								'return_amount'					=>	$return,//ok
    								'note'							=>	$data['note'],
    								'cheque'						=>	$data['cheque'],
    								//     							'user_id'						=>	$user_id,
    								'payment_option'				=>	$data["option_pay"],
    								'status'						=>	1,
    								'is_completed'					=>	$is_compleated,
    								'field3'			=>3,
    								'extra_payment' =>$data["extrapayment"],
    						);
    						$this->_name = "ln_client_receipt_money";
    						$where = "id = ".$id;
    						$this->update($arr_client_pay, $where);
    						$client_pay=$id;
//     						print_r($arr_client_pay);exit();
//     						$arr = array(
//     								'outstanding'		=> $data['sold_price'],
//     								'principal_amount'	=> $data['sold_price']-$total_principal,//សល់ពីបង់
//     								'total_principal_permonth'	=>$total_principal,
//     								'total_principal_permonthpaid'=>$total_principal,
//     								'total_interest_permonth'	=>$total_interest,
//     								'total_interest_permonthpaid'=>$total_interest
//     						);
//     						//need balance
    					
//     						$this->_name='ln_client_receipt_money';
//     						$where="id = ".$id;
//     						$crm_id = $this->update($arr, $where);
    					}
    					
	    					if($option_pay==1){//normal
	    					}else{//pay off
	    					}

    					if($data['extrapayment']>0){
				    			$extrapayment = $data['extrapayment'];
				    			$rs = $this->getSaleScheduleById($loan_number,2);
				    			if(!empty($rs)){
				    				foreach ($rs as $row){
				    						$total_interestafter=0;
				    						$extrapayment = $extrapayment-$row['principal_permonthafter'];
				    						if($extrapayment>=0){
				    							$principal_paid = $row['principal_permonthafter'];
				    							$statuscomplete=1;
				    							$remain_principal=0;
				    						}else{
				    							$principal_paid = abs($extrapayment);
				    							$remain_principal=$principal_paid;
				    							$statuscomplete=0;
				    						}
				    				
				    					$total_principal = $total_principal+$principal_paid;
				    					 
				    					$pyament_after = $row['total_payment_after']-($principal_paid);//ប្រាក់ត្រូវបង់លើកក្រោយសំរាប់ installmet 1 1
				    					$arra = array(
				    							"principal_permonthafter"=>$remain_principal,
				    							'total_interest_after'=>$total_interestafter,
				    							'begining_balance_after'=>$row['begining_balance_after']-$principal_paid,
				    							'is_completed'=>$statuscomplete,
				    							'paid_date'			=> 	$data['date_buy'],
				    							'total_payment_after'	=>	$pyament_after,
				    					);
				    					$where = " id = ".$row['id'];
				    					$this->_name="ln_saleschedule";
				    					$this->update($arra, $where);
				    					if($extrapayment<=0){
				    						break;
				    					}
				    				}
				    				
				    			}
				    		}//end of extra payment
    				}
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();exit();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
}
 
   function getAllPaymentListBySender($client_id){
   		$db = $this->getAdapter();
   		$sql = " CALL `stgetAllPaymentById`($client_id)";
   		return $db->fetchAll($sql);
   }

   function getLoanPaymentByLoanNumber($data){//tab1
    	$db = $this->getAdapter();
    	if($data['type']==1){
	    		$sql ="SELECT 
				 (SELECT CONCAT(COALESCE(ln_properties.land_address,''),',',COALESCE(ln_properties.street,'')) AS land_address  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_address,
				 (SELECT ln_properties.land_code  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_code,
				 (SELECT t.type_nameen FROM `ln_properties_type` as t WHERE t.id=(SELECT p.property_type FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) As property_type,
				  s.*,
				  s.interest_rate AS sale_interest_rate,
				  s.buy_date AS sold_date,
				  DATE_FORMAT(s.buy_date, '%d-%m-%Y') AS `buy_date`,
				  (SELECT name_kh FROM ln_view WHERE type =29 AND key_code = ss.ispay_bank LIMIT 1) AS payment_type,
				  (SELECT SUM(total_payment) FROM `ln_saleschedule` WHERE  total_payment<0 AND sale_id=".$data['loan_number']." LIMIT 1 ) AS total_discountafter, 
				  (SELECT phone FROM `ln_client` WHERE ln_client.client_id=s.client_id LIMIT 1) as phone,
				  (SELECT hname_kh FROM `ln_client` WHERE client_id=s.client_id LIMIT 1) as buy_with,
				  (SELECT crm.`from_date` FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id=s.id ORDER BY crm.id DESC LIMIT 1) AS from_date,
				  (SELECT SUM(crm.total_principal_permonthpaid+crm.extra_payment) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id=s.id AND crm.status=1 LIMIT 1) AS total_principal_permonthpaid,
				   ss.last_optiontype,
				   ss.ispay_bank,
				   ss.*,
				   DATE_FORMAT(ss.date_payment, '%d-%m-%Y') AS date_payments
				FROM
				  `ln_sale` AS s,
				  `ln_saleschedule` AS ss 
				WHERE s.id = ss.`sale_id` 
				  AND s.status = 1
				  AND ss.status = 1  
				  AND ss.is_completed = 0 
				  AND s.id = ".$data['loan_number']." ORDER BY ss.id ASC ";
    	}
    	return $db->fetchAll($sql);
   }
function getLoanPaymentschedulehistory($data){//used page edit il payment
   	$rows = $this->getReceiptMoneyDetailByID($data['crm_id']);
   	$s_where="";
   	$where='';
   	if(!empty($rows)){
   		$s_where = array();
   		foreach($rows as $rs){
   					$s_where[] = " ss.id = {$rs['lfd_id']}";
   			}
   			$where .=' '.implode(' OR ',$s_where).'';
   	}
   	$db = $this->getAdapter();
   	if($data['type']==1){
   		$sql ="SELECT
   		(SELECT CONCAT(ln_properties.land_address,',',ln_properties.street) AS land_address  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_address,
   		(SELECT ln_properties.land_code  FROM `ln_properties` WHERE ln_properties.id=s.`house_id` LIMIT 1) AS property_code,
   		(SELECT t.type_nameen FROM `ln_properties_type` as t WHERE t.id=(SELECT p.property_type FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) As property_type,
   		s.*,
   		ss.*,
   		ss.begining_balance_after AS begining_balance_afternew,
   		(ss.begining_balance_after+( (SELECT principal_permonth FROM `ln_client_receipt_money_detail` WHERE lfd_id = ss.id  AND ln_client_receipt_money_detail.status=1 LIMIT 1)) ) AS begining_balance_after,
   		(SELECT principal_permonth FROM `ln_client_receipt_money_detail` WHERE lfd_id = ss.id  AND ln_client_receipt_money_detail.status=1 LIMIT 1)  AS principal_permonthpaid,
   		(SELECT total_payment FROM `ln_client_receipt_money_detail` WHERE lfd_id = ss.id  AND ln_client_receipt_money_detail.status=1 LIMIT 1)  AS total_paymentbefore,
   		DATE_FORMAT(ss.date_payment, '%d-%m-%Y') AS date_payments
   		FROM
   		`ln_sale` AS s,
   		`ln_saleschedule` AS ss
   		WHERE s.id = ss.`sale_id`
   		AND s.status = 1
   		AND (ss.is_completed = 0 OR $where)
   		AND s.id = ".$data['loan_number'];
   	}

   	return $db->fetchAll($sql);
}   
function getLoanPaymentByLoanNumberEdit($data){
   	$db = $this->getAdapter();
	   	if($data['type']==1){
	   		$sql ="SELECT
	   		s.*,
	   		ss.*
	   		FROM
	   		`ln_sale` AS s,
	   		`ln_saleschedule` AS ss
	   		WHERE s.id = ss.`sale_id`
	   		AND s.status = 1
	   		AND s.id = ".$data['loan_number']." 
	   		AND (ss.is_completed = 0 or ss.id=".$data['edit_record'].")";
	   	}
   	return $db->fetchAll($sql);
   }
   
   function getAllLoanPaymentByLoanNumber($data){
	   	$db = $this->getAdapter();
	   	$loan_number= $data['loan_number'];
	   	$sql = "SELECT *,
			DATE_FORMAT(scd.date_payment, '%d-%m-%Y') AS `date_payment`,
			(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = scd.ispay_bank LIMIT 1) AS payment_type,
			 scd.last_optiontype,
			 scd.ispay_bank
			
	   	FROM 
	   		ln_sale as s ,
	   		ln_saleschedule as scd 
	   	WHERE 
		   	scd.status=1 
		   	AND s.id=scd.sale_id 
		   	AND sale_id = $loan_number 
		   	ORDER BY no_installment ASC ";
   		return $db->fetchAll($sql);
   	}

   function getAllCo(){
   			$db = $this->getAdapter();
   			$sql="SELECT `co_id` AS id,CONCAT(COALESCE(`co_firstname`,''),' ',COALESCE(`co_lastname`,''),'- ',COALESCE(`co_khname`,'')) AS `name`,`branch_id` FROM `ln_staff` WHERE `position_id`=1 AND (`co_khname`!=''  OR `co_firstname`!='')" ;
   			return $db->fetchAll($sql);
   		
   }
   function getAllClient(){
   	$db = $this->getAdapter();
   	$sql = "SELECT c.`client_id` AS id ,c.`name_kh` AS name FROM `ln_client` AS c WHERE c.`name_kh`!='' " ;
   	return $db->fetchAll($sql);
   }
   
   function getAllClientCode(){
   	$db = $this->getAdapter();
   	$sql = "SELECT c.`client_id` AS id ,c.`client_number` AS name FROM `ln_client` AS c WHERE c.`name_kh`!='' " ;
   	return $db->fetchAll($sql);
   }
   
   
   public function getLastPaymentDate($data){
   	$loanNumber = $data['loan_numbers'];
   	$fn_id = $data["fn_id"];
   	$db = $this->getAdapter();
   	$sql = "SELECT 
			  c.`date_input` 
			FROM
			  `ln_client_receipt_money` AS c,
			  `ln_client_receipt_money_detail` AS cr 
			WHERE c.`loan_number` = '$loanNumber' 
			  AND c.`id` = cr.`crm_id` 
			  AND cr.`lfd_id` = $fn_id 
			ORDER BY c.`receipt_no` DESC 
			LIMIT 1";
   	//return $sql;
   	return $db->fetchOne($sql);
   }
   public function getLaonHasPayByLoanNumber($loan_number){
   	$db= $this->getAdapter();
   	$sql=" SELECT 
			  (SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_name,
			  (SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id`=crm.`client_id` LIMIT 1) AS client_code,
			  crm.`receipt_no`,
			  crm.`land_id`,
			  DATE_FORMAT(crm.date_input, '%d-%m-%Y') AS `date_input`,
			  crm.outstanding,
			  crm.`principal_amount`,
			  crm.`total_principal_permonth`,
			  (total_principal_permonthpaid+extra_payment) AS total_principal_permonthpaid,
			  total_principal_permonthpaid AS permonthpaid,
			  total_interest_permonthpaid ,
			  penalize_amountpaid,
			  extra_payment,
			  crm.allpaid_before,
			  crm.selling_price,
			  crm.payment_times,
			  crm.`total_payment`,
			  crm.`total_interest_permonth`,
			  crm.`amount_payment`,
			  crm.`recieve_amount`,
			  crm.`return_amount`,
			  crm.`service_charge`,
			  crm.`penalize_amount`,
			  crm.`group_id`,
			  crm.`is_completed`,
			  (SELECT ln_sale.price_sold FROM `ln_sale` WHERE ln_sale.id=crm.sale_id LIMIT 1) AS price_sold,
			  (SELECT DATE_FORMAT(crmd.date_payment, '%d-%m-%Y') FROM `ln_client_receipt_money_detail` AS crmd WHERE crm.`id` = crmd.`crm_id` ORDER BY crmd.date_payment ASC LIMIT 1) AS `date_payment`
			FROM
			  `ln_client_receipt_money` AS crm
			WHERE 
			 crm.status=1
			  AND crm.`sale_id` = '$loan_number' ORDER BY crm.`id` DESC ";
   	return $db->fetchAll($sql);
   }
   
   function getAllLoanByCoId($data){ //quick Il Payment
		return array();
   }

   public function getReceiptMoneyById($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lc.id,lc.`service_charge`,lc.`penalize_amount`,lc.`payment_option`,lc.`recieve_amount`,lc.`total_interest`,lc.`total_payment` FROM `ln_client_receipt_money` AS lc WHERE lc.`id`=$id";
   	return $db->fetchRow($sql);
   }
    
   public function getReceiptMoneyDetailByID($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lc.`crm_id`,lc.`lfd_id`,lc.`land_id`,lc.`service_charge`,lc.`penelize_amount`,lc.`total_interest`,lc.`total_payment`,lc.`total_recieve`,lc.`principal_permonth`,old_penelize,old_service_charge FROM `ln_client_receipt_money_detail` AS lc WHERE lc.`crm_id`=$id";
   	return $db->fetchAll($sql);
   }
   public function getFunDetailById($id){
   	$db = $this->getAdapter();
   	$sql = "SELECT lf.`is_completed` FROM `ln_loanmember_funddetail` AS lf WHERE lf.id =$id";
   	return $db->fetchOne($sql);
   }
	/*
	public function getAllLoanNumberByBranch($branch_id,$is_issueplong=0){
		$db = $this->getAdapter();
		$sql= "SELECT id,
				  CONCAT((SELECT name_kh FROM ln_client WHERE ln_client.client_id=ln_sale.`client_id` LIMIT 1),'-',
				  (SELECT CONCAT(COALESCE(p.land_address,''),',',COALESCE(p.street,'')) FROM ln_properties AS p WHERE p.id = ln_sale.house_id LIMIT 1)) AS name
				FROM
				  ln_sale 
				WHERE status=1 
				  AND branch_id=$branch_id ";
		//new check is cancel 17-08-2018
		$sql.=" AND is_cancel=0";
		
		if($is_issueplong!=0){
			$sql.=" AND is_issueplong=0 ";
		}
		return $db->fetchAll($sql);
	}
	*/
	public function getAllLoanNumberByBranch($branch_id,$data){
		$db = $this->getAdapter();
		$sql= "SELECT id,
				  CONCAT((SELECT name_kh FROM ln_client WHERE ln_client.client_id=ln_sale.`client_id` LIMIT 1),'-',
				  (SELECT CONCAT(COALESCE(p.land_address,''),',',COALESCE(p.street,'')) FROM ln_properties AS p WHERE p.id = ln_sale.house_id LIMIT 1)) AS name
				FROM
				  ln_sale 
				WHERE status=1 
				  AND branch_id=$branch_id ";
		//new check is cancel 17-08-2018
		$sql.=" AND is_cancel=0";
		if($data["is_issueplong"]!=0){
			$sql.=" AND is_issueplong=0 ";
		}
		if($data["notInPlongStep"]!=0){
			$sql.=" AND ln_sale.house_id NOT IN (SELECT pr.property_id FROM ln_processing_plong AS pr WHERE ln_sale.house_id = `pr`.`property_id`) ";
		}
		if(isset($data["is_verify"])){
			$sql.=" AND is_verify=".$data["is_verify"];
		}
		return $db->fetchAll($sql);
	}
	public function getAllLoanNumberByBranchEdit($branch_id){
		$db = $this->getAdapter();
		$sql="select id,sale_number as name	from `ln_sale` where status=1  and branch_id=$branch_id";
		return $db->fetchAll($sql);
	}
	function getAllReceiptMoneyDetail($id){
		$db = $this->getAdapter();
		$sql = "select * from ln_client_receipt_money_detail where crm_id = $id AND status=1 ";
		return $db->fetchAll($sql);
	}
	function getPropertyInfo($property_id){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  s.*,
				 (SELECT t.type_nameen FROM `ln_properties_type` as t WHERE t.id=(SELECT p.property_type FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) As property_type,
				 (SELECT p.land_address FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1) AS property_address ,
				 (SELECT p.land_code FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1) AS property_code,
				 (SELECT p.street FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1) AS street
				FROM
				  ln_sale AS s 
				WHERE id = $property_id  ";
		$rs = $db->fetchRow($sql);
		$rs['house_type']=ltrim(strstr($rs['property_type'], '('), '.');
		return $rs;
	}
	function getLastDatePayment($loan_number){
		$db = $this->getAdapter();
		$sql = "SELECT 
				  date_pay ,
				  (SELECT date_payment FROM ln_saleschedule,ln_sale WHERE ln_saleschedule.`sale_id` = `ln_sale`.`id` AND `ln_sale`.id = $loan_number ORDER BY `ln_saleschedule`.id DESC LIMIT 1) AS datepaymentlastrecord
				FROM
				  ln_client_receipt_money 
				WHERE sale_id = $loan_number 
				ORDER BY id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function checkUserPermission($data){
		$user_name = empty($data['user_name'])?"":$data['user_name'];
		$password = empty($data['password'])?"":$data['password'];
		$db = $this->getAdapter();
		$sql="SELECT u.* FROM `rms_users` AS u WHERE u.`user_name`='$user_name' AND u.`password` = MD5('$password') AND u.`active` =1 LIMIT 1";
		$row = $db->fetchRow($sql);
		if (empty($row)){
			return 2;
		}elseif ($row['user_type']==1) {
			return 1;
		}else{
			return 2;
		}
	}
	
	function recordHistoryReceipt($_data,$receipt_id){
		$stringold="";
		$string="";
		$dbclient = new Group_Model_DbTable_DbClient();
		$db_pro = new Project_Model_DbTable_DbProject();
		$dbproper = new Project_Model_DbTable_DbLand();
		
		$land = $dbproper->getClientById($_data['property_id']);
		$project = $db_pro->getBranchById($_data['to_branch_id']);
		$client = $dbclient->getClientById($_data['client_id']);
		
		$row = $this->getIlPaymentByID($receipt_id);
		
		$stringold.="Project : ID:".$_data['to_branch_id']."-".$project['project_name']."<br />";
		$stringold.="Receipt No : ".$row['receipt_no']."<br />";
		$stringold.="Property : id=".$_data['property_id']."-".$land['land_address']." Street ".$land['street']."<br />";
		$stringold.="ថ្ងៃត្រូវបង់/Date Payment : ".$_data['collect_date']."<br />";
		$stringold.="ថ្ងៃទទួល/Date Receive : ".$_data['date_payment']."<br />";
		$stringold.="Customer : id=".$_data['client_id']."-".$client['name_kh']."<br />";
		$stringold.="បង់លើកទី : ".$_data['paid_times']."<br />";
		
		$stringold.="តម្លៃផ្ទះ : ".$_data['sold_price']."<br />";
		$stringold.="ប្រាក់បានបង់សរុប : ".$_data['total_pricipalpaid']."<br />";
		$stringold.="ប្រាក់នៅសល់ : ".$_data['priciple_amount']."<br />";
		
		$stringold.="ប្រាក់ដើមត្រូវបង់ : ".$_data['os_amount']."<br />";
		$stringold.="ប្រាក់ការ : ".$_data['total_interest']."<br />";
		$stringold.="ប្រាក់ពិន័យ : ".$_data['penalize_amount']."<br />";
		$stringold.="ប្រាក់បង់បន្ថែម : ".$_data['extrapayment']."<br />";
		$stringold.="ប្រាក់ត្រូវបង់ : ".$_data['total_payment']."<br />";
		$stringold.="ប្រាក់បានបង់ : ".$_data['amount_receive']."<br />";
		$payment="";
		if ($_data['payment_method']==1){
			$payment="សាច់ប្រាក់";
		}else if ($_data['payment_method']==3){
			$payment="សែក";
		}else if ($_data['payment_method']==2){
			$payment="ធនាគារ";
		}
		$paymenttype="";
		if ($_data['option_pay']==1){
			$payment="បង់ធម្មតា";
		}else if ($_data['option_pay']==3){
			$payment="រំលស់ដើម";
		}else if ($_data['option_pay']==4){
			$payment="បង់ផ្តាច់១០០%";
		}
		$stringold.="បង់ជា : ".$_data['payment_method']."-".$payment."<br />";
		$stringold.="ប្រភេទបង់ : ".$_data['option_pay']."-".$paymenttype."<br />";
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$_datas = array('description'=>'Issue OFFICIAL RECEIPT : '.$row['receipt_no'],'activityold'=>$stringold,'after_edit_info'=>$string);
		$dbgb->addActivityUser($_datas);
	}
	function recordhistory($receipt_id){
		$arr=array();
		$stringold="";
		$string="";
		$dbclient = new Group_Model_DbTable_DbClient();
		 
		if (!empty($receipt_id)){
	
			$row = $this->getIlPaymentByID($receipt_id);
	
			$client = $dbclient->getClientById($row['client_id']);
			$stringold="Receipt No : ".$row['receipt_no']."<br />";
			$stringold.="ថ្ងៃត្រូវបង់/Date Payment : ".$row['date_pay']."<br />";
			$stringold.="ថ្ងៃទទួល/Date Receive : ".$row['date_input']."<br />";
			$stringold.="Customer : id=".$row['client_id']."-".$client['name_kh']."<br />";
			$stringold.="Property Code : ".$row['land_id']."<br />";
			$stringold.="បង់លើកទី : ".$row['payment_times']."<br />";
	
			$stringold.="តម្លៃផ្ទះ : ".$row['selling_price']."<br />";
			$stringold.="ប្រាក់បានបង់សរុប : ".$row['allpaid_before']."<br />";
			$stringold.="ប្រាក់នៅសល់ : ".$row['balance']."<br />";
	
			$stringold.="ប្រាក់ដើមត្រូវបង់ : ".$row['total_principal_permonthpaid']."<br />";
			$stringold.="ប្រាក់ការ : ".$row['total_interest_permonthpaid']."<br />";
			$stringold.="ប្រាក់ពិន័យ : ".$row['penalize_amountpaid']."<br />";
			$stringold.="ប្រាក់បង់បន្ថែម : ".$row['extra_payment']."<br />";
			$stringold.="ប្រាក់ត្រូវបង់ : ".$row['total_payment']."<br />";
			$stringold.="ប្រាក់បានបង់ : ".$row['recieve_amount']."<br />";
			$payment="";
			if ($row['payment_method']==1){
				$payment="សាច់ប្រាក់";
			}else if ($row['payment_method']==3){
				$payment="សែក";
			}else if ($row['payment_method']==2){
				$payment="ធនាគារ";
			}
			$paymenttype="";
			if ($row['payment_option']==1){
				$payment="បង់ធម្មតា";
			}else if ($row['payment_option']==3){
				$payment="រំលស់ដើម";
			}else if ($row['payment_option']==4){
				$payment="បង់ផ្តាច់១០០%";
			}
			$stringold.="បង់ជា : ".$row['payment_method']."-".$payment."<br />";
			$stringold.="ប្រភេទបង់ : ".$row['payment_option']."-".$paymenttype."<br />";
	
			$string="Receipt No : ".$row['receipt_no']."<br />";
			$string.="ថ្ងៃត្រូវបង់/Date Payment : ".$row['date_pay']."<br />";
			$string.="ថ្ងៃទទួល/Date Receive : ".$row['date_input']."<br />";
			$string.="Customer : id=".$row['client_id']."-".$client['name_kh']."<br />";
			$string.="Property Code : ".$row['land_id']."<br />";
			$string.="បង់លើកទី : ".$row['payment_times']."<br />";
	
	
			$string.="តម្លៃផ្ទះ : ".$row['selling_price']."<br />";
			$string.="ប្រាក់បានបង់សរុប : ".$row['allpaid_before']."<br />";
			$string.="ប្រាក់នៅសល់ : ".$row['balance']."<br />";
	
			$string.="ប្រាក់ដើមត្រូវបង់ : 0<br />";
			$string.="ប្រាក់ការ : 0<br />";
			$string.="ប្រាក់ពិន័យ : 0<br />";
			$string.="ប្រាក់បង់បន្ថែម : 0<br />";
			$string.="ប្រាក់ត្រូវបង់ : 0<br />";
			$string.="ប្រាក់បានបង់ : 0<br />";
			$payment="";
			if ($row['payment_method']==1){
				$payment="សាច់ប្រាក់";
			}else if ($row['payment_method']==3){
				$payment="សែក";
			}else if ($row['payment_method']==2){
				$payment="ធនាគារ";
			}
			$paymenttype="";
			if ($row['payment_option']==1){
				$payment="បង់ធម្មតា";
			}else if ($row['payment_option']==3){
				$payment="រំលស់ដើម";
			}else if ($row['payment_option']==4){
				$payment="បង់ផ្តាច់១០០%";
			}
			$string.="បង់ជា : ".$row['payment_method']."-".$payment."<br />";
			$string.="ប្រភេទបង់ : ".$row['payment_option']."-".$paymenttype."<br />";
	
		}else{
			$string="";
			$stringold="";
		}
		$arr['activityold']=$stringold;
		$arr['after_edit_info']=$string;
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$_datas = array('description'=>'VOID OFFICIAL RECEIPT : '.$row['receipt_no'],'activityold'=>$stringold,'after_edit_info'=>$string);
		$dbgb->addActivityUser($_datas);
		
		return $arr;
	}
	
	function getOnlyReceiptNumber($receipt_id){
		$db = $this->getAdapter();
		$sql="SELECT crm.receipt_no FROM ln_client_receipt_money AS crm WHERE crm.id = $receipt_id LIMIT 1";
		return $db->fetchOne($sql);
	}
	
	
	function getLastPaymentRecord($sale_id){
		$db = $this->getAdapter();
		$sql="SELECT
		rm.*
		FROM
			`ln_client_receipt_money` AS rm
		WHERE rm.sale_id = $sale_id 
		AND rm.total_payment>0
		 ";
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("rm.branch_id");
		$sql.=" ORDER BY rm.id DESC ";
		$sql.=" LIMIT 1 ";
	
		return $db->fetchRow($sql);
	}
	
	public function setVoidReceiptReason($data){
			$db = $this->getAdapter();
			$db->beginTransaction();
			try{
		
				$arr = array(
						'void_reason'	=>$data['reason'],
						'void_by'		=>$this->getUserId(),
						'void_date'		=>date("Y-m-d H:i:s"),
				);
				$where=" id = ".$data['id'];
				$this->_name="ln_client_receipt_money";
				$this->update($arr, $where);
		
				$db->commit();
				return 1;
			}catch (Exception $e){
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
				$db->rollBack();
			}
	}
	
	function getTotalPrinciplePaidForSale($saleId){
		$db = $this->getAdapter();
		$sql="
				SELECT 
					(SUM(crm.total_principal_permonthpaid) + SUM(COALESCE(crm.extra_payment,0))) AS totalPrincipalPaid
				FROM `ln_client_receipt_money` AS crm 
				WHERE crm.sale_id = $saleId 
		 ";
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function checkSaleCompletedPaymentPrincipleAmount($receiptId){
		$db = $this->getAdapter();
		$row = $this->getIlPaymentByID($receiptId);
		if(!empty($row)){
			$saleId = empty($row["sale_id"])? 0 : $row["sale_id"];
			$priceSold = empty($row["price_sold"])? 0 : $row["price_sold"];
			
			$rowPaid = $this->getTotalPrinciplePaidForSale($saleId);
			$totalPrincipalPaid = empty($rowPaid["totalPrincipalPaid"])? 0 : $rowPaid["totalPrincipalPaid"];
			
			
			$isCompleted =0;
			if($priceSold==$totalPrincipalPaid){
				$isCompleted =1;
			}
			if($totalPrincipalPaid==0){
				$isCompleted =0;
			}
			$arr = array("is_completed"=>$isCompleted);
			
			$where=" id = ".$saleId;
			$this->_name="ln_sale";
			$this->update($arr, $where);
		}
	}
}