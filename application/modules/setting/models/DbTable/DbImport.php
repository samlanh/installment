<?php

class Setting_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_sale';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function updateItemsByImport($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$count = count($data);
	    	$a_time= 0;
	    	$install = 1;
	    	$first_pay = 0;
	    	$cum_interest=0;
	    	$first_payment=0;
	    	$branch_id=1;
	    	$n=0;
	    	$oldland_str='';
	    	$payment_id = array('រំលស់'=>4,'ផ្តាច់'=>6,'ដំណាក់កាល'=>3);
			
			$SaleIdGenerate =1;
	    	for($i=2; $i<=$count; $i++){
	    		if(empty($data[$i]['E'])){
// 	    			continue;
	    		}
	    		if($oldland_str!=$data[$i]['L']){
		    		$sql="SELECT `client_id` FROM `ln_client` WHERE name_kh='".$data[$i]['K']."'";
		    		$client_id = $db->fetchOne($sql);
		    		if(empty($client_id)){
		    			$dbg = new Application_Model_DbTable_DbGlobal();
		    			$client_code = $dbg->getNewClientIdByBranch();
		    			
				    		$_arr=array(
			    				'client_number'=> $client_code,
			    				'name_kh'	  => $data[$i]['K'],
			    				'sex'	      => 1,
			    				'pro_id'      => 12,
			    				'dis_id'      => 0,
			    				'com_id'      => 0,
			    				'village_id'  => 0,
			    				'street'	  => 0,
			    				'house'	      => 0,
			    				'nationality' => 'ខ្មែរ',
			    				'phone'	      => $data[$i]['X'],
				    			'remark'	      => $data[$i]['Y'],
			    				'create_date' => date("Y-m-d"),
			    				'status'      => 1,
			    				'client_d_type'=> 4,
			    				'user_id'	  => $this->getUserId(),
			    				'p_nationality'=> 'ខ្មែរ',
			    				'ksex'        => 1,
			    				'adistrict'   => 0,
			    				'cprovince'   => 12,
			    				'dcommune'    => 0,
			    				'qvillage'    => 0,
			    				'dstreet'     => 0,
			    				'branch_id'   => 1,
			    				'joint_doc_type' => 4,
				    		);
				    		$this->_name='ln_client';
				    		$client_id = $this->insert($_arr);
		    		}
		    		
		    		$sql="SELECT id FROM `ln_properties` WHERE branch_id = $branch_id AND land_address = '".$data[$i]['L']."'";
		    		$land_id = $db->fetchOne($sql);
		    		if(empty($land_id)){
		    			$land_string = $data[$i]['L'];
		    			$str_arr = explode ("+", $land_string);
		    			$oldlandid = '';
		    			if(count($str_arr)>1){
		    			foreach($str_arr as $land_address){
		    				
			    				$_arr=array(
			    						'branch_id'	  => $branch_id,
			    						'land_code'	  => '',
			    						'land_address'=> $land_address,
			    						'street'	  => $data[$i]['M'],
			    						'price'	      => $data[$i]['O']/count($str_arr),
			    						'land_price'  => $data[$i]['O']/count($str_arr),
			    						'house_price' => 0,
			    						'land_size'	  => '',
			    						'width'       => '',
			    						'height'      => '',
			    						'is_lock'     => 1,
			    						'status'	  => 1,
			    						'user_id'	  => $this->getUserId(),
			    						'property_type'=> '',
			    						'south'	      => '',
			    						'north'	      => '',
			    						'west'	      => '',
			    						'east'	      => '',
			    						'create_date'	  =>	date("Y-m-d"),
			    				);
			    				$this->_name='ln_properties';
			    				$land_id = $this->insert($_arr);
			    				$oldlandid = $oldlandid.','.$land_id;
		    				}
		    			}
		    			
		    			$_arr=array(
	    					'branch_id'	  => $branch_id,
	    					'land_code'	  => '',
	    					'land_address'=> $data[$i]['L'],
	    					'street'	  => $data[$i]['M'],
	    					'price'	      => $data[$i]['O'],
	    					'land_price'  => $data[$i]['O'],
		    				'old_land_id' => $oldlandid,
	    					'house_price' => 0,
	    					'land_size'	  => $data[$i]['W'],
	    					'width'       => '',
	    					'height'      => '',
	    					'is_lock'     => 1,
	    					'status'	  => 1,
	    					'user_id'	  => $this->getUserId(),
	    					'property_type'=> '',
	    					'south'	      => '',
	    					'north'	      => '',
	    					'west'	      => '',
	    					'east'	      => '',
							'create_date'	  =>	date("Y-m-d"),
		    			);
		    			$this->_name='ln_properties';
		    			$land_id = $this->insert($_arr);
		    		}else{
		    			$arr = array('is_lock'=>1);
		    			$this->_name='ln_properties';
		    			$where="id = ".$land_id;
		    			$this->update($arr, $where);
		    		}
		    		
		    		$dbtable = new Application_Model_DbTable_DbGlobal();
		    		$loan_number = $dbtable->getLoanNumber();
		    		$arr = array(
	    				'branch_id'=>$branch_id,
	    			    'house_id'=>$land_id,
	    			   	'receipt_no'=>'',
	    				'sale_number'=>$loan_number,
	    			   	'payment_id'=>4,
		    			'note'=>$data[$i]['G'],
	    				'client_id'=>$client_id,
	    				'price_before'=>$data[$i]['O'],
	    				'discount_amount'=>$data[$i]['P'],
	    			   	'discount_percent'=>$data[$i]['Q'],
	    				'price_sold'=>($data[$i]['O']),
	    				'other_fee'=>0,
// 	    				'paid_amount'=>$data[$i]['E'],
	    				'balance'=>0,//($data[$i]['0'])-$data[$i]['D'],
// 	    				'interest_rate'=>$data[$i]['P'],
	    				'total_duration'=>$data[$i]['U'],
	    			   	'validate_date'=>$data[$i]['R'],
	    				'payment_method'=>1,
	    			   	'land_price'=>0,//$data['house_price'],
	    			   	'total_installamount'=>$data[$i]['N'],
	    			   	'typesale'=>1,
	    				'build_start'=>'',
	    				'amount_build'=>0,
	    				'is_reschedule'=>1,
	    				'staff_id'=>0,
	    				'comission'=>0,
	    			   	'full_commission'=>0,
	    				'create_date'=>date("Y-m-d"),
	    			   	'startcal_date'=>date("Y-m-d",strtotime($data[$i]['R'])),
	    			   	'first_payment'=>date("Y-m-d",strtotime($data[$i]['R'])),
	    			   	'agreement_date'=>date("Y-m-d",strtotime($data[$i]['T'])),
    			   		'buy_date'=>date("Y-m-d",strtotime($data[$i]['R'])),
    			   		'end_line'=>date("Y-m-d",strtotime($data[$i]['S'])),
	    			   	'validate_date'=>date("Y-m-d",strtotime($data[$i]['R'])),
	    				'user_id'=>$this->getUserId(),
	    			   	'amount_daydelay'=>0
		    		);
		    			   
		    		$this->_name='ln_sale';
		    		$sale_id = $this->insert($arr);//add group loan
		    		$a_time=1;
					
// 					$SaleIdGenerate = $SaleIdGenerate +1;
// 					$sale_id = $SaleIdGenerate;
	    		}
	    		
// 	    		if($first_pay==1 AND $first_payment==0){
// 		    		$this->_name='ln_sale';
// 		    		$where=" id =".$sale_id;
// 		    		$arr = array(
// 		    			'first_payment'=>date("Y-m-d",strtotime($data[$i]['B']))
// 		    		);
// 		    	   $this->update($arr, $where);
// 		    	   $first_payment=1;
// 	    		}
	    		
	    		$is_completed =($data[$i]['I']==0)?1:0;
	    		$cum_interest = 0;//$cum_interest+$data[$i]['E'];
	    		$this->_name="ln_saleschedule";
// 	    		if($n==0){
	    			$begining = $data[$i]['C'];
	    			$ending=$data[$i]['E'];
	    			$is_completed=0;
// 	    		}else{
// 	    			$begining = $ending;
// 	    			$ending = $data[$i]['F'];
// 	    		}
	    			//if($oldland_str!=$data[$i]['L']){
	    			if(!empty($data[$i]['G']) OR !empty($data[$i]['H']) OR !empty($data[$i]['I'])){
	    				$is_completed=1;
	    			}
	    		$n++;
	    		$datapayment = array(
    				'branch_id'=>1,
    				'sale_id'=>$sale_id,//good
    				'begining_balance'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    				'begining_balance_after'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    				'principal_permonth'=> $data[$i]['D'],//good
    				'principal_permonthafter'=>$data[$i]['D'],//good
    				'total_interest'=>0,//$data[$i]['D'],//good
    				'total_interest_after'=>0,//$data[$i]['D'],//good
    				'total_payment'=>$data[$i]['D'],//good
    				'total_payment_after'=>$data[$i]['D'],//good
    				'ending_balance'=>$data[$i]['E'],
    				'cum_interest'=>$cum_interest,
    				'amount_day'=>30,
    				'is_completed'=>$is_completed,
    				'date_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
					//'ispay_bank'=>($data[$i]['B']=='បានប្លង់រឹង'?2:0),
    				'paid_date'=>date("Y-m-d",strtotime($data[$i]['H'])),
    				'note'=>'',
//     				'percent'=>($first_pay==0)?$data[$i]['N']:0,
//     				'percent_agree'=>($first_pay==0)?$data[$i]['N']:0,
    				'is_installment'=>1,//($first_pay==0)?1:0,
    				'no_installment'=>$install,
    				'last_optiontype'=>1,
	    			'note'=>$data[$i]['I'],
	    		);
	    		$saledetailid = $this->insert($datapayment);
	    		
	    		$dbg->updateLateRecordSaleschedule($sale_id);
	    		
// 	    		if($oldland_str!=$data[$i]['L']){
	    		if(!empty($data[$i]['G']) OR !empty($data[$i]['H']) OR !empty($data[$i]['I'])){
	    			
	    			$arr_client_pay = array(
	    					'branch_id'						=>	$branch_id,
	    					'receipt_no'					=>	$data[$i]['G'],
	    					'date_pay'					    =>	date("Y-m-d",strtotime($data[$i]['H'])),
	    					'date_input'					=>	date("Y-m-d",strtotime($data[$i]['H'])),
	    					'from_date'						=>	date("Y-m-d",strtotime($data[$i]['B'])),//check more
	    					'client_id'                     =>	$client_id,
	    					'sale_id'						=>	$sale_id,
	    					'land_id'						=>	$land_id,
	    					'outstanding'                   =>	$data[$i]['C'],//ប្រាក់ដើមមុនបង់
	    					'total_principal_permonth'		=>	$data[$i]['D'],//ប្រាក់ដើមត្រូវបង់
	    					'total_interest_permonth'		=>	0,//$data[$i]['D'],
	    					'penalize_amount'				=>	0,
	    					'total_payment'					=>	$data[$i]['D'],//ប្រាក់ត្រូវបង់ok
	    					'service_charge'				=>	0,
	    					'principal_amount'				=>	$data[$i]['E'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
	    					'balance'						=>	0,
	    					'recieve_amount'				=>	$data[$i]['D'],//ok
	    					'amount_payment'				=>	$data[$i]['D'],//brak ban borng
	    					'return_amount'					=>	0,//ok
	    					'note'							=>	'',
	    					'cheque'						=>	'',
	    					'user_id'						=>	$this->getUserId(),
	    					'payment_option'				=>	1,
	    					'status'						=>	1,
	    					'is_completed'					=>	1,
	    					'field3'						=>  ($first_pay==0)?1:3,
	    					'is_payoff'						=>  0,
	    					'extra_payment' 				=>  0,
	    					'payment_times'					=>  $install,
	    					'payment_method'				=>  1,
	    					
	    					'total_principal_permonthpaid'	=>	$data[$i]['D'],//ok ប្រាក់ដើមបានបង
	    					'total_interest_permonthpaid'	=>	0,//$data[$i]['D'],//ok ការប្រាក់បានបង
	    					'penalize_amountpaid'			=>	0,// ok បានបង
	    					'service_chargepaid'			=>	0,// okបានបង
	    			);
	    			
	    			$this->_name = "ln_client_receipt_money";
	    			$client_pay = $this->insert($arr_client_pay);
	    			
	    			$arr = array(
	    					'crm_id'				=>	$client_pay,
	    					'land_id'			    =>	$land_id,//ok
	    					'lfd_id'				=>	$saledetailid,//ok
	    					'date_payment'			=>	date("Y-m-d",strtotime($data[$i]['B'])), // ថ្ងៃដែលត្រូវបង់
	    					'principal_permonth'	=>	0,
	    					'total_interest'		=>	0,
	    					'total_payment'			=>	0,
	    					'total_recieve'			=>	0,
	    					'pay_after'				=>	0,
	    					'penelize_amount'		=>	0,
	    					'service_charge'		=>	0,
	    					'penelize_new'			=>	0,
	    					'service_charge_new'	=>	0,
	    					'capital'				=>  $data[$i]['C'],
	    					'remain_capital'		=>	$data[$i]['E'], // remain balance after paid
	    					'old_principal_permonth'=>	$data[$i]['D'],
	    					'old_total_priciple'	=>	$data[$i]['D'],
	    					'old_interest'			=>	0,//$data[$i]['D'],
	    					'old_total_payment'		=>	0,//$data[$i]['E'],
	    					'old_penelize'			=>	0,
	    					'old_service_charge'	=>	0,
	    					'last_pay_date'			=>	date("Y-m-d",strtotime($data[$i]['B'])),
	    					'paid_date'				=>	date("Y-m-d",strtotime($data[$i]['B'])),
	    					'is_completed'			=>	1,
	    					'status'				=>	1);
	    			$this->_name='ln_client_receipt_money_detail';
	    			$this->insert($arr);
	    			
	    		}
	    		$oldland_str=$data[$i]['L'];
	    		$install = $install+1;
	    	}
// 	    	exit();
	    	$db->commit();	    	
	   }catch(Exception $e){
// 	   		$db->rollBack();	
	   		echo $e->getMessage();
	   		exit();   		 
       } 
    }  
    public function ImportPPLand($data){
    	$db = $this->getAdapter();
    	//$db->beginTransaction();
    	try{
    		$count = count($data);
    		$a_time= 0;
    		$install = 1;
    		$first_pay = 0;
    		$cum_interest=0;
    		$first_payment=0;
    		$branch_id=1;
    		$n=0;
    		$oldland_str='';
    		$payment_id = array('រំលស់'=>4,'ផ្តាច់'=>6,'ដំណាក់កាល'=>3);
    			
    		$SaleIdGenerate =90;
    		for($i=2; $i<=$count; $i++){
    			if(empty($data[$i]['O'])){
    				continue;
    			}
    			if($oldland_str!=$data[$i]['O']){
    				$sql="SELECT `client_id` FROM `ln_client` WHERE name_kh='".$data[$i]['N']."'";
    				$client_id = $db->fetchOne($sql);
    				if(empty($client_id)){
    					$dbg = new Application_Model_DbTable_DbGlobal();
    					$client_code = $dbg->getNewClientIdByBranch();
    					 
    					$_arr=array(
    							'client_number'=> $client_code,
    							'name_kh'	  => $data[$i]['N'],
    							'sex'	      => 1,
    							'pro_id'      => 12,
    							'dis_id'      => 0,
    							'com_id'      => 0,
    							'village_id'  => 0,
    							'street'	  => 0,
    							'house'	      => 0,
    							'nationality' => 'ខ្មែរ',
    							'phone'	      => '',
    							'create_date' => date("Y-m-d"),
    							'status'      => 1,
    							'client_d_type'=> 4,
    							'user_id'	  => $this->getUserId(),
    							'p_nationality'=> 'ខ្មែរ',
    							'ksex'        => 1,
    							'adistrict'   => 0,
    							'cprovince'   => 12,
    							'dcommune'    => 0,
    							'qvillage'    => 0,
    							'dstreet'     => 0,
    							'branch_id'   => 1,
    							'joint_doc_type' => 4,
    					);
    					$this->_name='ln_client';
    					$client_id = $this->insert($_arr);
    					
    				}
    				$street = $data[$i]['P'];
    				$sql="SELECT id FROM `ln_properties` WHERE branch_id = $branch_id AND land_address = '".$data[$i]['O']."' AND street='".$street."'";
    				$land_id = $db->fetchOne($sql);
    				
    				if(empty($land_id)){
    					$_arr=array(
    							'branch_id'	  => $branch_id,
    							'land_code'	  => '',
    							'land_address'=> $data[$i]['O'],
    							'street'	  => $data[$i]['P'],
    							'price'	      => $data[$i]['R'],
    							'land_price'  => $data[$i]['R'],
    							'house_price' => 0,
    							'land_size'	  => '',
    							'width'       => '',
    							'height'      => '',
    							'is_lock'     => 1,
    							'status'	  => 1,
    							'user_id'	  => $this->getUserId(),
    							'property_type'=> '',
    							'south'	      => '',
    							'north'	      => '',
    							'west'	      => '',
    							'east'	      => '',
    							'create_date'	  =>	date("Y-m-d"),
    					);
    					$this->_name='ln_properties';
    					$land_id = $this->insert($_arr);
    				}else{
    					$arr = array('is_lock'=>1);
    					$this->_name='ln_properties';
    					$where="id = ".$land_id;
    					$this->update($arr, $where);
    				}
    				$dbtable = new Application_Model_DbTable_DbGlobal();
    				$loan_number = $dbtable->getLoanNumber();
    				$arr = array(
    						'branch_id'=>$branch_id,
    						'house_id'=>$land_id,
    						'receipt_no'=>'',
    						'sale_number'=>$data[$i]['M'],
    						'payment_id'=>4,
    						'note'=>$data[$i]['L'],
    						'client_id'=>$client_id,
    						'price_before'=>$data[$i]['R'],
    						'discount_amount'=>$data[$i]['S'],
    						'discount_percent'=>0,
    						'price_sold'=>$data[$i]['R'],
    						'other_fee'=>0,
    						'paid_amount'=>$data[$i]['E'],
    						'balance'=>($data[$i]['R']-$data[$i]['E']),
    						'interest_rate'=>$data[$i]['V'],
    						'total_duration'=>$data[$i]['AB'],
    						'validate_date'=>$data[$i]['Y'],
    						'payment_method'=>1,
    						'land_price'=>0,//$data['house_price'],
    						'total_installamount'=>$data[$i]['Q'],
    						'typesale'=>1,
    						'build_start'=>'',
    						'amount_build'=>0,
    						'is_reschedule'=>1,
    						'staff_id'=>0,
    						'comission'=>0,
    						'full_commission'=>0,
    						'create_date'=>date("Y-m-d"),
    						'startcal_date'=>date("Y-m-d",strtotime($data[$i]['W'])),
    						'first_payment'=>date("Y-m-d",strtotime($data[$i]['W'])),
    						'agreement_date'=>date("Y-m-d",strtotime($data[$i]['Y'])),
    						'buy_date'=>date("Y-m-d",strtotime($data[$i]['W'])),
    						'end_line'=>date("Y-m-d",strtotime($data[$i]['X'])),
    						'validate_date'=>date("Y-m-d",strtotime($data[$i]['X'])),
    						'user_id'=>$this->getUserId(),
    						'amount_daydelay'=>0,
    						'receipt_no'=>$data[$i]['I'],
							'excel_note'=>$data[$i]['V'],
    				);
    
    				$this->_name='ln_sale';
    				$sale_id = $this->insert($arr);//add group loan
    				$a_time=1;
    					
    				$SaleIdGenerate = $SaleIdGenerate +1;
    				$sale_id = $SaleIdGenerate;
    			}
    	   
    			// 	    		if($first_pay==1 AND $first_payment==0){
    			// 		    		$this->_name='ln_sale';
    			// 		    		$where=" id =".$sale_id;
    			// 		    		$arr = array(
    			// 		    			'first_payment'=>date("Y-m-d",strtotime($data[$i]['B']))
    			// 		    		);
    			// 		    	   $this->update($arr, $where);
    			// 		    	   $first_payment=1;
    			// 	    		}
    	   
    			$is_completed =!empty($data[$i]['J'])?1:0;
    			$this->_name="ln_saleschedule";
    			// 	    		if($n==0){
    			$begining = $data[$i]['H'];
    			$ending=$data[$i]['H']-$data[$i]['C'];
    			$is_completed=0;
    			// 	    		}else{
    			// 	    			$begining = $ending;
    			// 	    			$ending = $data[$i]['F'];
    			// 	    		}
    			if($oldland_str!=$data[$i]['O']){
    				$is_completed=1;
    			}
    			$n++;
    			$datapayment = array(
    					'branch_id'=>1,
    					'sale_id'=>$sale_id,//good
    					'begining_balance'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    					'begining_balance_after'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    					'principal_permonth'=> $data[$i]['C'],//good
    					'principal_permonthafter'=>$data[$i]['C'],//good
    					'total_interest'=>$data[$i]['D'],//good
    					'total_interest_after'=>$data[$i]['D'],//good
    					'total_payment'=>$data[$i]['E'],//good
    					'total_payment_after'=>$data[$i]['E'],//good
    					'ending_balance'=>$ending,
    					'amount_day'=>30,
    					'is_completed'=>$is_completed,
    					'date_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
    					'received_date'=>date("Y-m-d",strtotime($data[$i]['K'])),
    					//'ispay_bank'=>($data[$i]['B']=='បានប្លង់រឹង'?2:0),
    					'paid_date'=>date("Y-m-d",strtotime($data[$i]['B'])),
    					'note'=>'',
    					'percent'=>0,
    					'percent_agree'=>0,
    					'is_installment'=>($oldland_str!=$data[$i]['O'])?1:0,
    					'no_installment'=>$data[$i]['A'],
    					'last_optiontype'=>1,
    					'note'=>$data[$i]['L'],
    					
    			);
    			$saledetailid = $this->insert($datapayment);
    			
    			if($data[$i]['F']>0){//extra payment
    				$datapayment = array(
    						'branch_id'=>1,
    						'sale_id'=>$sale_id,//good
    						'begining_balance'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    						'begining_balance_after'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    						'principal_permonth'=> $data[$i]['F'],//good
    						'principal_permonthafter'=>$data[$i]['F'],//good
    						'total_interest'=>$data[$i]['D'],//good
    						'total_interest_after'=>$data[$i]['D'],//good
    						'total_payment'=>$data[$i]['F'],//good
    						'total_payment_after'=>$data[$i]['F'],//good
    						'ending_balance'=>$ending,
    						'amount_day'=>30,
    						'is_completed'=>$is_completed,
    						'date_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
    						'received_date'=>date("Y-m-d",strtotime($data[$i]['K'])),
    						//'ispay_bank'=>($data[$i]['B']=='បានប្លង់រឹង'?2:0),
    						'paid_date'=>date("Y-m-d",strtotime($data[$i]['B'])),
    						'note'=>'',
    						'percent'=>0,
    						'percent_agree'=>0,
    						'is_installment'=>($oldland_str!=$data[$i]['O'])?1:0,
    						'no_installment'=>$data[$i]['A'],
    						'last_optiontype'=>1,
    						'note'=>$data[$i]['L'],
    						'collect_by'=>2,
    						'status'=>0,
    						
    				);
    				$saledetailid = $this->insert($datapayment);
    			}
    	   
//     			if($oldland_str!=$data[$i]['O']){
    			if(!empty($data[$i]['J'])){
    
    				$arr_client_pay = array(
    						'branch_id'						=>	$branch_id,//$data["branch_id"],
    						'receipt_no'					=>	$data[$i]['J'],
    						'date_pay'					    =>	date("Y-m-d",strtotime($data[$i]['K'])),
    						'date_input'					=>	date("Y-m-d",strtotime($data[$i]['K'])),
    						'from_date'						=>	date("Y-m-d",strtotime($data[$i]['K'])),//check more
    						'client_id'                     =>	$client_id,
    						'sale_id'						=>	$sale_id,
    						'land_id'						=>	$land_id,
    						'outstanding'                   =>	$data[$i]['H'],//ប្រាក់ដើមមុនបង់
    						'total_principal_permonth'		=>	$data[$i]['C'],//ប្រាក់ដើមត្រូវបង់
    						'total_interest_permonth'		=>	$data[$i]['D'],
    						'penalize_amount'				=>	$data[$i]['G'],
    						'total_payment'					=>	$data[$i]['E'],//ប្រាក់ត្រូវបង់ok
    						'service_charge'				=>	0,
    						'principal_amount'				=>	$ending,//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    						'balance'						=>	0,
    						'recieve_amount'				=>	$data[$i]['E']+$data[$i]['G'],//ok
    						'amount_payment'				=>	$data[$i]['E']+$data[$i]['G'],//brak ban borng
    						'return_amount'					=>	0,//ok
    						'note'							=>	'',
    						'cheque'						=>	'',
    						'user_id'						=>	$this->getUserId(),
    						'payment_option'				=>	1,
    						'status'						=>	1,
    						'is_completed'					=>	1,
    						'field3'						=>  ($first_pay==0)?1:3,
    						'is_payoff'						=>  0,
    						'extra_payment' 				=>  0,
    						'payment_times'					=>  $install,
    						'payment_method'				=>  1,
    
    						'total_principal_permonthpaid'	=>	$data[$i]['C'],//ok ប្រាក់ដើមបានបង
    						'total_interest_permonthpaid'	=>	$data[$i]['D'],//ok ការប្រាក់បានបង
    						'penalize_amountpaid'			=>	$data[$i]['G'],// ok បានបង
    						'service_chargepaid'			=>	0,// okបានបង
    				);
    
    				$this->_name = "ln_client_receipt_money";
    				$client_pay = $this->insert($arr_client_pay);
    
    				$arr = array(
    						'crm_id'				=>	$client_pay,
    						'land_id'			    =>	$land_id,//ok
    						'lfd_id'				=>	$saledetailid,//ok
    						'date_payment'			=>	date("Y-m-d",strtotime($data[$i]['B'])), // ថ្ងៃដែលត្រូវបង់
    						'principal_permonth'	=>	0,
    						'total_interest'		=>	0,
    						'total_payment'			=>	0,
    						'total_recieve'			=>	0,
    						'pay_after'				=>	0,
    						'penelize_amount'		=>	0,
    						'service_charge'		=>	0,
    						'penelize_new'			=>	0,
    						'service_charge_new'	=>	0,
    						'capital'				=>  $data[$i]['H'],
    						'remain_capital'		=>	$ending, // remain balance after paid
    						'old_principal_permonth'=>	$data[$i]['C'],
    						'old_total_priciple'	=>	$data[$i]['C'],
    						'old_interest'			=>	$data[$i]['D'],
    						'old_total_payment'		=>	$data[$i]['E'],
    						'old_penelize'			=>	0,
    						'old_service_charge'	=>	0,
    						'last_pay_date'			=>	date("Y-m-d",strtotime($data[$i]['B'])),
    						'paid_date'				=>	date("Y-m-d",strtotime($data[$i]['B'])),
    						'is_completed'			=>	1,
    						'status'				=>	1
    						);
    				$this->_name='ln_client_receipt_money_detail';
    				$this->insert($arr);
    
    			}
    			$oldland_str=$data[$i]['O'];
    			$install = $install+1;
    		}
    		// 	    	exit();
    			    	$db->commit();
    	}catch(Exception $e){
//     		$db->rollBack();
    	    echo $e->getMessage();
    		exit();
    	}
    } 

    /*
     * 
     public function ImportPPLand($data){
    	$count = count($data);
    	$branch_id=11;
    	$dbEx = new Incexp_Model_DbTable_DbExpense();
    	for($i=2; $i<=$count; $i++){
    		$payment_id=1;
			if ($data[$i]['H']=="សែក"){
				$payment_id=2;
			}
			$invoice = $dbEx->getInvoiceNo($branch_id);
    		$arr_ = array(
    		    	'branch_id'			=>	$branch_id,//$data["branch_id"],
    		    	'title'             =>	$data[$i]['C'],
    		    	'invoice'			=>	$invoice,//$data[$i]['G'],
    		    	'total_amount'		=>	$data[$i]['F'],
    				'category_id'			=>	3,
    				
    		    	'for_date'			=>	date("Y-m-d",strtotime($data[$i]['I'])),
    				'date'				=>	date("Y-m-d",strtotime($data[$i]['I'])),
    				'create_date'		=>	date("Y-m-d"),
    				'status'			=>	1,
    				'user_id'			=>	1,
    				
    				'desc'				=>	$data[$i]['D']." ".$data[$i]['E'],
    				'description'		=>	$data[$i]['D']." ".$data[$i]['E'],
    				'payment_id'		=>	$payment_id,
    				
    				'cheque'			=>	"",
    				'cheque_issuer'		=>	"",
    				'other_invoice'		=>	$data[$i]['G'],
    				);
    		$this->_name='ln_expense';
    		$this->insert($arr_);
    	}
    }
     * 
     * */
    
    
    public function ImportADLand($data){
    	$db = $this->getAdapter();
    	//$db->beginTransaction();
    	try{
    		$count = count($data);
    		$a_time= 0;
    		$install = 1;
    		$first_pay = 0;
    		$cum_interest=0;
    		$first_payment=0;
    		$branch_id=1;
    		$n=0;
    		$oldland_str='';
    		$payment_id = array('រំលស់'=>4,'បង់ដំណាក់កាល'=>6,'ផ្ដាច់'=>6,'ដំណាក់កាលថេរ'=>3);
    		$installMentNumber=0; 
    		
    		$SaleIdGenerate =0;
    		for($i=2; $i<=$count; $i++){
    			if(empty($data[$i]['K'])){
    				continue;
    			}
    			if($oldland_str!=$data[$i]['K']){
    				$installMentNumber=0;
    				$sql="SELECT `client_id` FROM `ln_client` WHERE name_kh='".$data[$i]['J']."'";
    				$client_id = $db->fetchOne($sql);
    				if(empty($client_id)){
    					$dbg = new Application_Model_DbTable_DbGlobal();
    					$client_code = $dbg->getNewClientIdByBranch();
    
    					$_arr=array(
    							'client_number'=> $client_code,
    							'name_kh'	  => $data[$i]['J'],
    							'sex'	      => 1,
    							'pro_id'      => 12,
    							'dis_id'      => 0,
    							'com_id'      => 0,
    							'village_id'  => 0,
    							'street'	  => 0,
    							'house'	      => 0,
    							'nationality' => 'ខ្មែរ',
    							'phone'	      => $data[$i]['U'],
    							'create_date' => date("Y-m-d"),
    							'status'      => 1,
    							'client_d_type'=> 4,
    							'user_id'	  => $this->getUserId(),
    							'p_nationality'=> 'ខ្មែរ',
    							'ksex'        => 1,
    							'adistrict'   => 0,
    							'cprovince'   => 12,
    							'dcommune'    => 0,
    							'qvillage'    => 0,
    							'dstreet'     => 0,
    							'branch_id'   => 1,
    							'joint_doc_type' => 4,
    					);
    					$this->_name='ln_client';
    					$client_id = $this->insert($_arr);
    						
    				}
    
    				$typesale =1;
    				$propertyList = explode(",", $data[$i]['K']);
    				$street = $data[$i]['L'];
    				if (!empty($propertyList)){
    						
    					$countLand = count($propertyList);
    					if ($countLand>1){
    						$typesale =2;
    						$oldId = "";
    						foreach ($propertyList as $land){
    							$sql="SELECT id FROM `ln_properties` WHERE branch_id = $branch_id AND land_address = '".$land."' AND street='".$street."'";
    							$land_id = $db->fetchOne($sql);
    
    							if(empty($land_id)){
    								$_arr=array(
    										'branch_id'	  => $branch_id,
    										'land_code'	  => '',
    										'land_address'=> $land,
    										'street'	  => $street,
    										'price'	      => $data[$i]['N'],
    										'land_price'  => $data[$i]['N'],
    										'house_price' => 0,
    										'land_size'	  => '',
    										'width'       => '',
    										'height'      => '',
    										'is_lock'     => 1,
    										'status'	  => 1,
    										'user_id'	  => $this->getUserId(),
    										'property_type'=> 1,
    										'south'	      => '',
    										'north'	      => '',
    										'west'	      => '',
    										'east'	      => '',
    										'create_date'	  =>	date("Y-m-d"),
    								);
    								$this->_name='ln_properties';
    								$land_id = $this->insert($_arr);
    							}else{
    								$arr = array('is_lock'=>1);
    								$this->_name='ln_properties';
    								$where="id = ".$land_id;
    								$this->update($arr, $where);
    							}
    								
    							if (empty($oldId)){
    								$oldId =$land_id;
    							}else{
    								$oldId =$oldId.",".$land_id;
    							}
    
    						}
    
    						$_arr=array(
    								'branch_id'	  => $branch_id,
    								'land_code'	  => '',
    								'land_address'=> $data[$i]['K'],
    								'street'	  => $street,
    								'price'	      => $data[$i]['N'],
    								'land_price'  => $data[$i]['N'],
    								'house_price' => 0,
    								'land_size'	  => '',
    								'width'       => '',
    								'height'      => '',
    								'is_lock'     => 1,
    								'status'	  => -2,
    								'user_id'	  => $this->getUserId(),
    								'property_type'=> 1,
    								'south'	      => '',
    								'north'	      => '',
    								'west'	      => '',
    								'east'	      => '',
    								'old_land_id'	      => $oldId,
    								'create_date'	  =>	date("Y-m-d"),
    						);
    						$this->_name='ln_properties';
    						$land_id = $this->insert($_arr);
    					}else{
    						$sql="SELECT id FROM `ln_properties` WHERE branch_id = $branch_id AND land_address = '".$data[$i]['K']."' AND street='".$street."'";
    						$land_id = $db->fetchOne($sql);
    
    						if(empty($land_id)){
    							$_arr=array(
    									'branch_id'	  => $branch_id,
    									'land_code'	  => '',
    									'land_address'=> $data[$i]['K'],
    									'street'	  => $street,
    									'price'	      => $data[$i]['N'],
    									'land_price'  => $data[$i]['N'],
    									'house_price' => 0,
    									'land_size'	  => '',
    									'width'       => '',
    									'height'      => '',
    									'is_lock'     => 1,
    									'status'	  => 1,
    									'user_id'	  => $this->getUserId(),
    									'property_type'=> 1,
    									'south'	      => '',
    									'north'	      => '',
    									'west'	      => '',
    									'east'	      => '',
    									'create_date'	  =>	date("Y-m-d"),
    							);
    							$this->_name='ln_properties';
    							$land_id = $this->insert($_arr);
    						}else{
    							$arr = array('is_lock'=>1);
    							$this->_name='ln_properties';
    							$where="id = ".$land_id;
    							$this->update($arr, $where);
    						}
    					}
    				}
    
    				$duration = str_replace("ខែ", "", $data[$i]['T']);
    				$storeNumber = 0;
    				if (!empty($data[$i]['W'])){
    				$storeNumber = str_replace("Free​", "", $data[$i]['W']);
    				$storeNumber = str_replace(" ​", "", str_replace("តូប​", "", $storeNumber));
    				}
    				
    				$totalInstallamount=0;
    				if (!empty($payment_id[$data[$i]['F']])){
    					if($payment_id[$data[$i]['F']]==4){
    						$totalInstallamount = ($data[$i]['N']-$data[$i]['O'])-$data[$i]['M'];
    					}
    				}
    				
    				
    				$dbtable = new Application_Model_DbTable_DbGlobal();
    				$loan_number = $dbtable->getLoanNumber();
    				$payType=4;
    				if (!empty($payment_id[$data[$i]['F']])){
    					$payType = $payment_id[$data[$i]['F']];
    				}
    				
    				$arr = array(
    						'branch_id'=>$branch_id,
    						'house_id'=>$land_id,
    						'receipt_no'=>'',
    						'sale_number'=>$loan_number,//$data[$i]['M'],
    						'payment_id'=>$payType,
    						'note'=>$data[$i]['X'],
    						'client_id'=>$client_id,
    						'price_before'=>$data[$i]['N'],
    						'discount_amount'=>$data[$i]['O'],
    						'discount_percent'=>0,
    						'price_sold'=>$data[$i]['N']-$data[$i]['O'],
    						'other_fee'=>0,
    				//     						'paid_amount'=>$data[$i]['E'],
    				//     						'balance'=>($data[$i]['N']-$data[$i]['E']),
    						'interest_rate'=>0,//$data[$i]['V'],
    						'total_duration'=>$duration,
    						'validate_date'=>date("Y-m-d",strtotime($data[$i]['R'])),
    						'payment_method'=>1,
    						'land_price'=>0,//$data['house_price'],
    
    						'total_installamount'=>$totalInstallamount,
    						'typesale'=>$typesale,
    						'build_start'=>'',
    						'amount_build'=>0,
    						'is_reschedule'=>1,
    						'staff_id'=>0,
    						'comission'=>0,
    						'full_commission'=>0,
    						'create_date'=>date("Y-m-d"),
    
    						'startcal_date'=>date("Y-m-d",strtotime($data[$i]['B'])),
    						'first_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
    						'agreement_date'=>date("Y-m-d",strtotime($data[$i]['S'])),
    						'buy_date'=>date("Y-m-d",strtotime($data[$i]['Q'])),
    						'end_line'=>date("Y-m-d",strtotime($data[$i]['R'])),
    						'validate_date'=>date("Y-m-d",strtotime($data[$i]['R'])),
    						'user_id'=>$this->getUserId(),
    						'amount_daydelay'=>0,
    						'receipt_no'=>$data[$i]['G'],
    						'excel_note'=>$data[$i]['X'],//$data[$i]['AC'],
    						
    						'store_number'=>$storeNumber,
    				);
    
    				$this->_name='ln_sale';
    				$sale_id = $this->insert($arr);//add group loan
    				$a_time=1;
    					
    				$SaleIdGenerate = $SaleIdGenerate +1;
    				$sale_id = $SaleIdGenerate;
    			}
    			
    			$installMentNumber = $installMentNumber+1;
    
    			// 	    		if($first_pay==1 AND $first_payment==0){
    			// 		    		$this->_name='ln_sale';
    			// 		    		$where=" id =".$sale_id;
    			// 		    		$arr = array(
    			// 		    			'first_payment'=>date("Y-m-d",strtotime($data[$i]['B']))
    			// 		    		);
    			// 		    	   $this->update($arr, $where);
    			// 		    	   $first_payment=1;
    			// 	    		}
    			
    			$is_completed=0;
    			$is_completed =!empty($data[$i]['G'])?1:0;
    			$this->_name="ln_saleschedule";
    			// 	    		if($n==0){
    			$begining = $data[$i]['C'];
    			$ending=$data[$i]['C']-$data[$i]['D'];
    			
    			// 	    		}else{
    			// 	    			$begining = $ending;
    			// 	    			$ending = $data[$i]['F'];
    			// 	    		}
//     			if($oldland_str!=$data[$i]['K']){
//     				$is_completed=1;
//     			}
    			
    			$ispay_bank=0;
    			if ($data[$i]['B']=="បាន់ប្លង់បង់ផ្ដាច់" || $data[$i]['B']=="បានប្លង់បង់ផ្តាច់"){
    				$ispay_bank=1;
    			}
    			$n++;
    			$datapayment = array(
    					'branch_id'=>1,
    					'sale_id'=>$sale_id,//good
    					'begining_balance'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    					'begining_balance_after'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    					'principal_permonth'=> $data[$i]['D'],//good
    					'principal_permonthafter'=>$data[$i]['D'],//good
    					'total_interest'=>0,//$data[$i]['D'],//good
    					'total_interest_after'=>0,//$data[$i]['D'],//good
    					'total_payment'=>$data[$i]['D'],//$data[$i]['E'],//good
    					'total_payment_after'=>$data[$i]['D'],//$data[$i]['E'],//good
    					'ending_balance'=>$ending,
    					'amount_day'=>30,
    					'is_completed'=>$is_completed,
    					'date_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
    					'received_date'=>date("Y-m-d",strtotime($data[$i]['H'])),
    					//'ispay_bank'=>($data[$i]['B']=='បានប្លង់រឹង'?2:0),
    					'paid_date'=>date("Y-m-d",strtotime($data[$i]['B'])),
    					'note'=>'',
    					'percent'=>0,
    					'percent_agree'=>0,
    					'is_installment'=>($oldland_str!=$data[$i]['K'])?1:0,
    					'no_installment'=>$installMentNumber,//$data[$i]['A'],
    					'last_optiontype'=>1,
    					'ispay_bank'=>$ispay_bank,
//     					'note'=>$data[$i]['L'],
    						
    			);
    			$saledetailid = $this->insert($datapayment);
    			 
    			//     			if($data[$i]['F']>0){//extra payment
    			//     				$datapayment = array(
    			//     						'branch_id'=>1,
    			//     						'sale_id'=>$sale_id,//good
    			//     						'begining_balance'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    			//     						'begining_balance_after'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    			//     						'principal_permonth'=> $data[$i]['F'],//good
    			//     						'principal_permonthafter'=>$data[$i]['F'],//good
    			//     						'total_interest'=>$data[$i]['D'],//good
    			//     						'total_interest_after'=>$data[$i]['D'],//good
    			//     						'total_payment'=>$data[$i]['F'],//good
    			//     						'total_payment_after'=>$data[$i]['F'],//good
    			//     						'ending_balance'=>$ending,
    			//     						'amount_day'=>30,
    			//     						'is_completed'=>$is_completed,
    			//     						'date_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
    			//     						'received_date'=>date("Y-m-d",strtotime($data[$i]['K'])),
    			//     						//'ispay_bank'=>($data[$i]['B']=='បានប្លង់រឹង'?2:0),
    					//     						'paid_date'=>date("Y-m-d",strtotime($data[$i]['B'])),
    					//     						'note'=>'',
    					//     						'percent'=>0,
    					//     						'percent_agree'=>0,
    					//     						'is_installment'=>($oldland_str!=$data[$i]['O'])?1:0,
    					//     						'no_installment'=>$data[$i]['A'],
    					//     						'last_optiontype'=>1,
    					//     						'note'=>$data[$i]['L'],
    					//     						'collect_by'=>2,
    					//     						'status'=>0,
    
    					//     				);
    			//     				$saledetailid = $this->insert($datapayment);
    			//     			}
    
    			//     			if($oldland_str!=$data[$i]['O']){
    			$total_principal_permonthpaid=0;
    			if(!empty($data[$i]['G'])){
    				$total_principal_permonthpaid= $data[$i]['D']+$total_principal_permonthpaid;
    				$arr_client_pay = array(
    						'branch_id'						=>	$branch_id,//$data["branch_id"],
    						'receipt_no'					=>	$data[$i]['G'],
    						'date_pay'					    =>	date("Y-m-d",strtotime($data[$i]['H'])),
    						'date_input'					=>	date("Y-m-d",strtotime($data[$i]['H'])),
    						'from_date'						=>	date("Y-m-d",strtotime($data[$i]['H'])),//check more
    						'client_id'                     =>	$client_id,
    						'sale_id'						=>	$sale_id,
    						'land_id'						=>	$land_id,
    						'outstanding'                   =>	$data[$i]['C'],//ប្រាក់ដើមមុនបង់
    						'total_principal_permonth'		=>	$data[$i]['D'],//ប្រាក់ដើមត្រូវបង់
    						'total_interest_permonth'		=>	0,//$data[$i]['D'],
    						'penalize_amount'				=>	0,//$data[$i]['G'],
    						'total_payment'					=>	$data[$i]['D'],//$data[$i]['E'],//ប្រាក់ត្រូវបង់ok
    						'service_charge'				=>	0,
    						'principal_amount'				=>	$ending,//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    						'balance'						=>	0,
    						'recieve_amount'				=>	$data[$i]['D'],//$data[$i]['E']+$data[$i]['G'],//ok
    						'amount_payment'				=>	$data[$i]['D'],//$data[$i]['E']+$data[$i]['G'],//brak ban borng
    						'return_amount'					=>	0,//ok
    						'note'							=>	'',
    						'cheque'						=>	'',
    						'user_id'						=>	$this->getUserId(),
    						'payment_option'				=>	1,
    						'status'						=>	1,
    						'is_completed'					=>	1,
    						'field3'						=>  ($first_pay==0)?1:3,
    						'is_payoff'						=>  0,
    						'extra_payment' 				=>  0,
    						'payment_times'					=>  $installMentNumber,
    						'payment_method'				=>  1,
    
    						'total_principal_permonthpaid'	=>	$total_principal_permonthpaid,//$data[$i]['C'],//ok ប្រាក់ដើមបានបង
    						'total_interest_permonthpaid'	=>	0,//$data[$i]['D'],//ok ការប្រាក់បានបង
    						'penalize_amountpaid'			=>	0,//$data[$i]['G'],// ok បានបង
    						'service_chargepaid'			=>	0,// okបានបង
    				);
    
    				$this->_name = "ln_client_receipt_money";
    				$client_pay = $this->insert($arr_client_pay);
    
    				$arr = array(
    						'crm_id'				=>	$client_pay,
    						'land_id'			    =>	$land_id,//ok
    						'lfd_id'				=>	$saledetailid,//ok
    						'date_payment'			=>	date("Y-m-d",strtotime($data[$i]['B'])), // ថ្ងៃដែលត្រូវបង់
    						'principal_permonth'	=>	0,
    						'total_interest'		=>	0,
    						'total_payment'			=>	0,
    						'total_recieve'			=>	0,
    						'pay_after'				=>	0,
    						'penelize_amount'		=>	0,
    						'service_charge'		=>	0,
    						'penelize_new'			=>	0,
    						'service_charge_new'	=>	0,
    						'capital'				=>  $data[$i]['C'],
    						'remain_capital'		=>	$ending, // remain balance after paid
    						'old_principal_permonth'=>	$data[$i]['D'],
    						'old_total_priciple'	=>	$data[$i]['D'],
    						'old_interest'			=>	0,//$data[$i]['D'],
    						'old_total_payment'		=>	$data[$i]['D'],
    						'old_penelize'			=>	0,
    						'old_service_charge'	=>	0,
    						'last_pay_date'			=>	date("Y-m-d",strtotime($data[$i]['B'])),
    						'paid_date'				=>	date("Y-m-d",strtotime($data[$i]['B'])),
    						'is_completed'			=>	1,
    						'status'				=>	1
    				);
    				$this->_name='ln_client_receipt_money_detail';
    				$this->insert($arr);
    
    			}
    			$oldland_str=$data[$i]['K'];
    			$install = $install+1;
    		}
    		// 	    	exit();
    		// 	    	$db->commit();
    	}catch(Exception $e){
    		//     		$db->rollBack();
    		echo $e->getMessage();
    		exit();
    	}
    }
}   

