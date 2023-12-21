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
    function importHanuman($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	$dbg = new Application_Model_DbTable_DbGlobal();
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
    			if(empty($data[$i]['A'])){
    				continue;
    			}
    			if($oldland_str!=$data[$i]['I']){
    				$sql="SELECT `client_id` FROM `ln_client` WHERE name_kh='".$data[$i]['U']."'";
    				$client_id = $db->fetchOne($sql);
    				if(empty($client_id)){
    					
    					$client_code = $dbg->getNewClientIdByBranch();
    			   
    					$_arr=array(
    							'client_number'=> $client_code,
    							'name_kh'	  => $data[$i]['U'],
    							'sex'	      => 1,
    							'pro_id'      => 12,
    							'dis_id'      => 0,
    							'com_id'      => 0,
    							'village_id'  => 0,
    							'street'	  => 0,
    							'house'	      => 0,
    							'nationality' => 'ខ្មែរ',
    							'phone'	      => $data[$i]['V'],
    							'remark'	      => $data[$i]['W'],
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
    	
    				$sql="SELECT id FROM `ln_properties` WHERE branch_id = $branch_id AND land_address = '".$data[$i]['I']."'";
    				$land_id = $db->fetchOne($sql);
    				if(empty($land_id)){
    					$land_string = $data[$i]['I'];
    					$str_arr = explode ("+", $land_string);
    					$oldlandid = '';
    					if(count($str_arr)>1){
    						foreach($str_arr as $land_address){
    	
    							$_arr=array(
    									'branch_id'	  => $branch_id,
    									'land_code'	  => '',
    									'land_address'=> $land_address,
    									'street'	  => $data[$i]['J'],
    									'price'	      => $data[$i]['M']/count($str_arr),
    									'land_price'  => $data[$i]['M']/count($str_arr),
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
    							'land_address'=> $data[$i]['I'],
    							'street'	  => $data[$i]['J'],
    							'price'	      => $data[$i]['M'],
    							'land_price'  => $data[$i]['M'],
    							'old_land_id' => $oldlandid,
    							'house_price' => 0,
//     							'land_size'	  => $data[$i]['W'],
    							'width'       => '',
    							'height'      => '',
    							'is_lock'     => 1,
    							'status'	  => 1,
    							'user_id'	  => $this->getUserId(),
    							'property_type'=> '1',
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
    						'sale_number'=>$data[$i]['H'],//$loan_number,
    						'payment_id'=>4,//$payment_id[$data[$i]['L']],
//     						'note'=>$data[$i]['G'],
    						'client_id'=>$client_id,
    						'price_before'=>$data[$i]['K'],
    						'discount_amount'=>$data[$i]['M']-$data[$i]['K'],
//     						'discount_percent'=>$data[$i]['Q'],
    						'price_sold'=>($data[$i]['M']),
    						'other_fee'=>0,
    						// 	    				'paid_amount'=>$data[$i]['E'],
    						'balance'=>0,//($data[$i]['0'])-$data[$i]['D'],
    						// 	    				'interest_rate'=>$data[$i]['P'],
    						'total_duration'=>$data[$i]['R'],
    						'validate_date'=>$data[$i]['P'],
    						'payment_method'=>1,
    						'land_price'=>0,//$data['house_price'],
    						'total_installamount'=>$data[$i]['R'],
    						'typesale'=>1,
    						'build_start'=>'',
    						'amount_build'=>0,
    						'is_reschedule'=>1,
    						'staff_id'=>0,
    						'comission'=>0,
    						'full_commission'=>0,
    						'create_date'=>date("Y-m-d"),
    						'startcal_date'=>date("Y-m-d",strtotime($data[$i]['O'])),
    						'first_payment'=>date("Y-m-d",strtotime($data[$i]['O'])),
    						'agreement_date'=>date("Y-m-d",strtotime($data[$i]['Q'])),
    						'buy_date'=>date("Y-m-d",strtotime($data[$i]['O'])),
    						'end_line'=>date("Y-m-d",strtotime($data[$i]['P'])),
    						'validate_date'=>date("Y-m-d",strtotime($data[$i]['P'])),
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
    			if($oldland_str!=$data[$i]['I']){
    				$install=1;
    			}
    			
    			$datapayment = array(
    					'branch_id'=>1,
    					'sale_id'=>$sale_id,//good
    					'begining_balance'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    					'begining_balance_after'=>$begining,//$data[$i]['F']+$data[$i]['C'],//good
    					'principal_permonth'=> $data[$i]['D'],//good
    					'principal_permonthafter'=>$data[$i]['D'],//good
    					'total_interest'=>0,//$data[$i]['D'],//good
    					'total_interest_after'=>$data[$i]['E'],//good
    					'total_payment'=>$data[$i]['F'],//good
    					'total_payment_after'=>$data[$i]['F'],//good
    					'ending_balance'=>$data[$i]['G'],
    					'cum_interest'=>$cum_interest,
    					'amount_day'=>30,
    					'is_completed'=>0,
    					'date_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
    					//'ispay_bank'=>($data[$i]['B']=='បានប្លង់រឹង'?2:0),
//     					'paid_date'=>date("Y-m-d",strtotime($data[$i]['H'])),
    					'note'=>'',
    					//     				'percent'=>($first_pay==0)?$data[$i]['N']:0,
    			//     				'percent_agree'=>($first_pay==0)?$data[$i]['N']:0,
    					'is_installment'=>1,//($first_pay==0)?1:0,
    					'no_installment'=>$install,
    					'last_optiontype'=>1,
//     					'note'=>$data[$i]['I'],
    			);
    			$saledetailid = $this->insert($datapayment);
    			 
    			$dbg->updateLateRecordSaleschedule($sale_id);
    			 
//     			if(!empty($data[$i]['G']) OR !empty($data[$i]['H']) OR !empty($data[$i]['I'])){
    	
//     				$arr_client_pay = array(
//     						'branch_id'						=>	$branch_id,
//     						'receipt_no'					=>	$data[$i]['G'],
//     						'date_pay'					    =>	date("Y-m-d",strtotime($data[$i]['H'])),
//     						'date_input'					=>	date("Y-m-d",strtotime($data[$i]['H'])),
//     						'from_date'						=>	date("Y-m-d",strtotime($data[$i]['B'])),//check more
//     						'client_id'                     =>	$client_id,
//     						'sale_id'						=>	$sale_id,
//     						'land_id'						=>	$land_id,
//     						'outstanding'                   =>	$data[$i]['C'],//ប្រាក់ដើមមុនបង់
//     						'total_principal_permonth'		=>	$data[$i]['D'],//ប្រាក់ដើមត្រូវបង់
//     						'total_interest_permonth'		=>	0,//$data[$i]['D'],
//     						'penalize_amount'				=>	0,
//     						'total_payment'					=>	$data[$i]['D'],//ប្រាក់ត្រូវបង់ok
//     						'service_charge'				=>	0,
//     						'principal_amount'				=>	$data[$i]['E'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
//     						'balance'						=>	0,
//     						'recieve_amount'				=>	$data[$i]['D'],//ok
//     						'amount_payment'				=>	$data[$i]['D'],//brak ban borng
//     						'return_amount'					=>	0,//ok
//     						'note'							=>	'',
//     						'cheque'						=>	'',
//     						'user_id'						=>	$this->getUserId(),
//     						'payment_option'				=>	1,
//     						'status'						=>	1,
//     						'is_completed'					=>	1,
//     						'field3'						=>  ($first_pay==0)?1:3,
//     						'is_payoff'						=>  0,
//     						'extra_payment' 				=>  0,
//     						'payment_times'					=>  $install,
//     						'payment_method'				=>  1,
    	
//     						'total_principal_permonthpaid'	=>	$data[$i]['D'],//ok ប្រាក់ដើមបានបង
//     						'total_interest_permonthpaid'	=>	0,//$data[$i]['D'],//ok ការប្រាក់បានបង
//     						'penalize_amountpaid'			=>	0,// ok បានបង
//     						'service_chargepaid'			=>	0,// okបានបង
//     				);
    	
//     				$this->_name = "ln_client_receipt_money";
//     				$client_pay = $this->insert($arr_client_pay);
    	
//     				$arr = array(
//     						'crm_id'				=>	$client_pay,
//     						'land_id'			    =>	$land_id,//ok
//     						'lfd_id'				=>	$saledetailid,//ok
//     						'date_payment'			=>	date("Y-m-d",strtotime($data[$i]['B'])), // ថ្ងៃដែលត្រូវបង់
//     						'principal_permonth'	=>	0,
//     						'total_interest'		=>	0,
//     						'total_payment'			=>	0,
//     						'total_recieve'			=>	0,
//     						'pay_after'				=>	0,
//     						'penelize_amount'		=>	0,
//     						'service_charge'		=>	0,
//     						'penelize_new'			=>	0,
//     						'service_charge_new'	=>	0,
//     						'capital'				=>  $data[$i]['C'],
//     						'remain_capital'		=>	$data[$i]['E'], // remain balance after paid
//     						'old_principal_permonth'=>	$data[$i]['D'],
//     						'old_total_priciple'	=>	$data[$i]['D'],
//     						'old_interest'			=>	0,//$data[$i]['D'],
//     						'old_total_payment'		=>	0,//$data[$i]['E'],
//     						'old_penelize'			=>	0,
//     						'old_service_charge'	=>	0,
//     						'last_pay_date'			=>	date("Y-m-d",strtotime($data[$i]['B'])),
//     						'paid_date'				=>	date("Y-m-d",strtotime($data[$i]['B'])),
//     						'is_completed'			=>	1,
//     						'status'				=>	1);
//     				$this->_name='ln_client_receipt_money_detail';
//     				$this->insert($arr);
    	
//     			}
				
    			$oldland_str=$data[$i]['I'];
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
    function insertPayment($data){
	    	$db = $this->getAdapter();
// 	    	$db->beginTransaction();
	    	$dbg = new Application_Model_DbTable_DbGlobal();
	    	
	    	$oldLot='';
	        $count = count($data);
    		for($i=1; $i<=$count; $i++){
    	    	if(!empty($data[$i]['B'])){
    	    		
    	    		$land_address = $data[$i]['B'];
    	    		$sql="SELECT *,
    	    			(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=s.id AND status=1 AND is_completed=0 LIMIT 1) as intallment
    	    		FROM `ln_sale` s
    	    			WHERE house_id IN(SELECT id FROM `ln_properties` WHERE land_address='".$land_address."')";
    	    		$rs = $db->fetchRow($sql);
    	    		if(empty($rs)){continue;}
    	    		if($oldLot !=$land_address){
    	    			$allPaid=0;
    	    		}
    	    		
    	    		$allPaid = $allPaid+$data[$i]['E'];
    	 
    	    				$arr_client_pay = array(
    	    						'branch_id'						=>	1,
    	    						'receipt_no'					=>	$data[$i]['C'],
    	    						'date_pay'					    =>	date("Y-m-d",strtotime($data[$i]['F'])),
    	    						'date_input'					=>	date("Y-m-d",strtotime($data[$i]['F'])),
    	    						'from_date'						=>	date("Y-m-d",strtotime($data[$i]['F'])),//check more
    	    						'client_id'                     =>	$rs['client_id'],
    	    						'sale_id'						=>	$rs['id'],
    	    						'land_id'						=>	$rs['house_id'],
//     	    						'payment_times'					=>  $rsSchedule['no_installment'],
//     	    						'outstanding'                   =>	$rsSchedule['begining_balance'],//ប្រាក់ដើមមុនបង់
//     	    						'principal_amount'				=>	$rsSchedule['begining_balance']-$data[$i]['E'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    	    						'total_principal_permonth'		=>	$data[$i]['E'],//ប្រាក់ដើមត្រូវបង់
    	    						'total_interest_permonth'		=>	0,//$data[$i]['D'],
    	    						'penalize_amount'				=>	0,
    	    						'total_payment'					=>	$data[$i]['E'],//ប្រាក់ត្រូវបង់ok
    	    						'service_charge'				=>	0,
    			    				'balance'						=>	0,
    			    				'recieve_amount'				=>	$data[$i]['E'],//ok
    			    				'amount_payment'				=>	$data[$i]['E'],//brak ban borng
    			    				'return_amount'					=>	0,//ok
    			    				'note'							=>	$data[$i]['G'],//$data[$i]['D'],
    	    						'closing_note'					=>	$data[$i]['D'],//$data[$i]['D'],
    	    						
    			    				'cheque'						=>	'',
    			    				'user_id'						=>	$this->getUserId(),
    			    				'payment_option'				=>	1,
    			    				'status'						=>	1,
    			    				'is_completed'					=>	1,
    			    				'field3'						=>  1,//($first_pay==0)?1:3,
    			    				'is_payoff'						=>  0,
    			    				'extra_payment' 				=>  0,
    			    				'payment_method'				=>  1,
    	    						'selling_price'					=>	$rs['price_sold'],
    	    						'allpaid_before'				=>	$allPaid,
    			    				'total_principal_permonthpaid'	=>	$data[$i]['E'],//ok ប្រាក់ដើមបានបង
    			    				'total_interest_permonthpaid'	=>	0,//$data[$i]['D'],//ok ការប្រាក់បានបង
    			    				'penalize_amountpaid'			=>	0,// ok បានបង
    			    				'service_chargepaid'			=>	0,// okបានបង
    			    				);
    	 
    	    				$this->_name = "ln_client_receipt_money";
    	    				$client_pay = $this->insert($arr_client_pay);
    	    				$oldLot = $data[$i]['B'];
    	    				 
    	    				
    	    				$sql="SELECT *
    	    					FROM `ln_saleschedule` s
    	    							WHERE is_completed=0 AND sale_id=".$rs['id'];
    	    						$sql.=" ORDER BY id ";
    	    				$rsSchedule = $db->fetchAll($sql);
    	    				
    	    				
    	    				$remain_money = $data[$i]['E'];
    	    				foreach($rsSchedule as $key=> $r){
    	    					
    	    					
    	    					if($remain_money<=0){
    	    						break;
    	    					}
    	    					$total_principal = $r['principal_permonthafter'];
    	    					$remain_money = round($remain_money-$r['principal_permonthafter'],2);
    	    					//echo $rs['principal_permonthafter'];exit();
    	    					$after_outstanding = $r['begining_balance_after'];
    	    					if($land_address=='A2' AND $data[$i]['E']==300){
    	    						
    	    					}
    	    					if($i==45){
//     	    						echo $sql;exit();
//     	    						echo $r['principal_permonthafter'];
//     	    						echo $remain_money;exit();
    	    					}
    	    					if($remain_money>=0){//check here of គេបង់លើសខ្លះ
    	    						$paid_principal = $total_principal;
    	    						$after_principal = 0;
    	    						$is_compleated_d=1;
    	    						//echo 1;
    	    					}else{
    	    						$paid_principal = $total_principal-abs($remain_money);
    	    						$is_compleated_d=0;
    	    						$after_principal = abs($remain_money);
    	    						//echo 0;
    	    					}
    	    					//exit();
    	    					
	    	    				$arr =array(
	    	    					'begining_balance_after'=>$after_outstanding-$paid_principal,
	    	    					'is_completed'=>$is_compleated_d,
	    	    					'total_payment_after'=>$after_principal,
	    	    					'principal_permonthafter'=>$after_principal,
	    	    					'paid_date'	=>	date("Y-m-d",strtotime($data[$i]['F'])),
	    	    					'received_date'=>date("Y-m-d",strtotime($data[$i]['F']))
	    	    				);
	    	    				$this->_name='ln_saleschedule';
	    	    				$where="id=".$r['id'];
	    	    				$this->update($arr, $where);  
    	    				}  	    				
    	 
    	    				$arr = array(
    			    						'crm_id'				=>	$client_pay,
    			    						'land_id'			    =>	$r['id'],//ok
    			    						'lfd_id'				=>	$r['id'],//ok
    			    						'date_payment'			=>	date("Y-m-d",strtotime($data[$i]['F'])), // ថ្ងៃដែលត្រូវបង់
    			    						'principal_permonth'	=>	0,
    			    						'total_interest'		=>	0,
    			    						'total_payment'			=>	0,
    			    						'total_recieve'			=>	0,
    			    						'pay_after'				=>	0,
    			    						'penelize_amount'		=>	0,
    			    						'service_charge'		=>	0,
    			    						'penelize_new'			=>	0,
    			    						'service_charge_new'	=>	0,
    			    						'capital'				=>  $r['begining_balance'],
    			    						'remain_capital'		=>	$r['begining_balance']-$data[$i]['E'], // remain balance after paid
    			    						'old_principal_permonth'=>	$data[$i]['E'],
    			    						'old_total_priciple'	=>	$data[$i]['E'],
    			    						'old_interest'			=>	0,//$data[$i]['D'],
    			    						'old_total_payment'		=>	0,//$data[$i]['E'],
    			    						'old_penelize'			=>	0,
    			    						'old_service_charge'	=>	0,
    			    						'last_pay_date'			=>	date("Y-m-d",strtotime($data[$i]['F'])),
    			    						'paid_date'				=>	date("Y-m-d",strtotime($data[$i]['F'])),
    			    						'is_completed'			=>	1,
    			    						'status'				=>	1);
    	    				$this->_name='ln_client_receipt_money_detail';
    	    				$this->insert($arr);
    	    				
    	    				$this->_name='ln_client_receipt_money';
    	    				$arr = array(
    	    						'date_payment'=>$r['date_payment']
    	    					);
    	    				$where = 'id='.$client_pay;
    	    				$this->update($arr, $where);
    	    			}else{
//     	    				echo $data[$i]['B'];exit();
    	    			}
    		}
//     		$db->commit();
    }
    
    public function ImportKPMorndany($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$count = count($data);
    		$a_time= 0;
    		$install = 1;
    		$first_pay = 0;
    		$cum_interest=0;
    		$first_payment=0;
    		$n=0;
    		$oldland_str='';
    		$payment_id = array('Monthly'=>4,'Holding'=>6,'ផ្ដាច់'=>6,'ដំណាក់កាលថេរ'=>3);
    		$genderStr = array(
					'ប្រុស'=>1,
    				'ស្រី'=>2
    			);
    		
    		$propertyTypeStr = array(
    				'House'=>1,
    				'Land'=>2,
    				'Market'=>3,
    		);
    		
    		$installMentNumber=0;
    
    		$SaleIdGenerate =0;
    		for($i=3; $i<=$count; $i++){
    			$ProjectName=$data[$i]['B'];
    			$lotNo=$data[$i]['C'];
    			$propertyType=$data[$i]['D'];
    			$streetNo=empty($data[$i]['E'])?0:"Str".$data[$i]['E'];
    			$saleBY=$data[$i]['F'];
    			$customerName=$data[$i]['G'];
    			$customerNameEn=$data[$i]['H'];
    			$cardNo=$data[$i]['I'];
    			$nationName=$data[$i]['J'];
    			$gender=empty($genderStr[$data[$i]['K']])?0:$genderStr[$data[$i]['K']];
    			$fullAddress=$data[$i]['L'];
    			$district=$data[$i]['M'];
//     			$customerName=$data[$i]['N'];
    			$province=$data[$i]['O'];
    			$dob=$data[$i]['P'];
    			$tel=$data[$i]['Q'];
    			$orgPrice=$data[$i]['R'];
    			$discount=$data[$i]['S'];
    			$sellingPrice=$data[$i]['T'];
    			$saleDate=date("Y-m-d",strtotime($data[$i]['U']));
    			$totalPaid=$data[$i]['V'];
    			$balance=$data[$i]['W'];
    			$lastPaidDate=!empty($data[$i]['X'])?date("Y-m-d",strtotime($data[$i]['X'])):$saleDate;
    			$agreementDate=!empty($data[$i]['Y'])?date("Y-m-d",strtotime($data[$i]['Y'])):$saleDate;
    			$agreementBY=$data[$i]['Z'];
    			$duration=$data[$i]['AA'];
    			$installmentType=$data[$i]['AB'];
    			$projectAddress=$data[$i]['AC'];
    			
    			$amtPerPaid=$data[$i]['AD'];
    			$lastPaid=$data[$i]['AE'];
    			$buildPercentage=$data[$i]['AF'];
    			$saleNoteStatus=$data[$i]['AG'];
    			
    			$strDuration = empty($duration)?'':'រយៈពេលបង់ '.$duration;
    			$strAmtPerPaid = empty($amtPerPaid)?'':'បង់ប្រចាំខែ'.$amtPerPaid;
    			$strLastPaidDate = empty($lastPaidDate)?'':'LastPaidDate'.$lastPaidDate;
    			$strLastPaid = empty($lastPaid)?'':'LastPaid'.$lastPaid;
    			
    			$saleNote = $strDuration.$strAmtPerPaid.$strLastPaidDate.$strLastPaid.$saleNoteStatus;
    			$userId='';
    			
    			$param = array(
    					'projectName'=>$ProjectName,
    					'projectAddress'=>$projectAddress
    				);
    			$branch_id = $this->getProjectId($param);
    			
    			$param = array(
    					'staffName'=>strtoupper($saleBY),
    					'branchId'=>$branch_id
    				);
    			$staffId = $this->getStaffId($param);
    			
    			if(empty($lotNo)){
    				continue;
    			}
    			if($oldland_str!=$data[$i]['C']){
    				$installMentNumber=0;
    				$sql="SELECT `client_id` FROM `ln_client` WHERE name_kh='".$customerName."'";
    				$client_id = $db->fetchOne($sql);
    				
    				$clients = explode("-",$customerName);
    				$client1 = !empty($clients[0])?$clients[0]:'';
    				$client2 = !empty($clients[1])?$clients[1]:'';
    				
    				if(empty($client_id)){
    					$dbg = new Application_Model_DbTable_DbGlobal();
    					$client_code = $dbg->getNewClientIdByBranch();
    
    					$_arr=array(
    							'client_number'=> $client_code,
    							'name_kh'	  => $client1,
    							'hname_kh'		=>$client2,
    							'sex'	      => $gender,
    							'pro_id'      => 0,
    							'dis_id'      => 0,
    							'com_id'      => 0,
    							'village_id'  => 0,
//     							'street'	  => 0,
//     							'house'	      => 0,
    							'nationality' => $nationName,
    							'phone'	      =>$tel,
    							'create_date' => $saleDate,
    							'status'      => 1,
    							'client_d_type'=> 4,
    							'user_id'	  => $userId,
    							'p_nationality'=> $nationName,
    							'ksex'        => 1,
    							'adistrict'   => 0,
    							'cprovince'   => 12,
    							'dcommune'    => 0,
    							'qvillage'    => 0,
    							'dstreet'     => 0,
    							'branch_id'   => 1,
    							'joint_doc_type'=> 4,
    							'remark'	      =>$fullAddress,
    					);
    					$this->_name='ln_client';
    					$client_id = $this->insert($_arr);
    				}
    
    				$typesale =1;
    				$propertyList = explode(",", $lotNo);
    				if (!empty($propertyList)){
    
    					$countLand = count($propertyList);
    					if ($countLand>1){
    						$typesale =2;
    						$oldId = "";
    						foreach ($propertyList as $land){
    							$sql="SELECT id FROM `ln_properties` WHERE branch_id = $branch_id AND land_address = '".$land."' AND street='".$streetNo."'";
    							$land_id = $db->fetchOne($sql);
    
    							if(empty($land_id)){
    								$_arr=array(
    										'branch_id'	  => $branch_id,
    										'land_code'	  => '',
    										'land_address'=> $land,
    										'street'	  => $streetNo,
    										'price'	      => $orgPrice,
    										'land_price'  => $orgPrice,
    										'buildPercentage'=>$buildPercentage,
    										'house_price' => 0,
    										'land_size'	  => '',
    										'width'       => '',
    										'height'      => '',
    										'is_lock'     => ($fullAddress=='Cancelled')?0:1,
    										'status'	  => 1,
    										'user_id'	  => $userId,
    										'property_type'=> empty($propertyTypeStr[$propertyType])?0:$propertyTypeStr[$propertyType],
    										'type_tob'		=>$propertyType,
    										'south'	      => '',
    										'north'	      => '',
    										'west'	      => '',
    										'east'	      => '',
    										'create_date' =>$saleDate,
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
    								'land_address'=> $lotNo,
    								'street'	  => $streetNo,
    								'price'	      => $orgPrice,
    								'land_price'  => $orgPrice,
    								'buildPercentage'=>$buildPercentage,
    								'house_price' => 0,
    								'land_size'	  => '',
    								'width'       => '',
    								'height'      => '',
    								'is_lock'     => ($fullAddress=='Cancelled')?0:1,
    								'status'	  => -2,
    								'user_id'	  => $userId,
    								'property_type'=> empty($propertyTypeStr[$propertyType])?0:$propertyTypeStr[$propertyType],
    								'type_tob'		=>$propertyType,
    								'south'	      => '',
    								'north'	      => '',
    								'west'	      => '',
    								'east'	      => '',
    								'old_land_id'=> $oldId,
    								'create_date'=>	$saleDate,
    						);
    						$this->_name='ln_properties';
    						$land_id = $this->insert($_arr);
    					}else{
    						$sql="SELECT id FROM `ln_properties` WHERE branch_id = $branch_id AND land_address = '".$lotNo."' AND street='".$streetNo."'";
    						$land_id = $db->fetchOne($sql);
    
    						if(empty($land_id)){
    							$_arr=array(
    									'branch_id'	  => $branch_id,
    									'land_code'	  => '',
    									'land_address'=> $lotNo,
    									'street'	  => $streetNo,
    									'price'	      => $orgPrice,
    									'land_price'  => $orgPrice,
    									'buildPercentage'=>$buildPercentage,
    									'house_price' => 0,
    									'land_size'	  => '',
    									'width'       => '',
    									'height'      => '',
    									'is_lock'     => ($fullAddress=='Cancelled')?0:1,
    									'status'	  => 1,
    									'user_id'	  => $userId,
    									'property_type'=> empty($propertyTypeStr[$propertyType])?0:$propertyTypeStr[$propertyType],
    									'type_tob'		=>empty($propertyTypeStr[$propertyType])?0:$propertyTypeStr[$propertyType],
    									'south'	      => '',
    									'north'	      => '',
    									'west'	      => '',
    									'east'	      => '',
    									'create_date'=>$saleDate,
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
    				
    				$totalInstallamount=0;
    				if (!empty($payment_id[$installmentType])){//check
    					if($payment_id[$installmentType]==4){//check
    						$totalInstallamount = $balance;
    					}
    				}
    
    				$dbtable = new Application_Model_DbTable_DbGlobal();
    				$loan_number = $dbtable->getLoanNumber();
    				$payType=4;
    				if(!empty($payment_id[$installmentType])){//check
    					$payType = $payment_id[$installmentType];
    				}
    
    				$arr = array(
    						'branch_id'=>$branch_id,
    						'house_id'=>$land_id,
    						'receipt_no'=>'',
    						'sale_number'=>$loan_number,
    						'payment_id'=>$payType,
    						'client_id'=>$client_id,
    						'price_before'=>$orgPrice,
    						'discount_amount'=>$discount,
    						'discount_percent'=>0,
    						'price_sold'=>$sellingPrice,
    						'other_fee'=>0,
    						'paid_amount'=>$totalPaid,
    						'balance'=>$balance,
    						'interest_rate'=>0,
    						'total_duration'=>$duration,
    						'payment_method'=>1,
    						'land_price'=>0,
    						'total_installamount'=>$totalInstallamount,
    						'typesale'=>$typesale,
    						'build_start'=>'',
    						'amount_build'=>0,
    						'is_reschedule'=>($totalPaid)>0?1:0,
    						'staff_id'=>$staffId,
    						'comission'=>0,
    						'full_commission'=>0,
    						'create_date'=>$saleDate,
    						'startcal_date'=>$lastPaidDate,
    						'first_payment'=>$lastPaidDate,
    						'agreement_date'=>$agreementDate,
    						'buy_date'=>$saleDate,
    						'end_line'=>$lastPaidDate,
    						'validate_date'=>$lastPaidDate,//check 
    						'amount_daydelay'=>0,
//     						'excel_note'=>$data[$i]['X'],//check
    						'user_id'=>$userId,
    						'note'=>$saleNote,
    						'is_cancel'=>($fullAddress=='Cancelled')?1:0,
    				);
    
    				$this->_name='ln_sale';
    				$sale_id = $this->insert($arr);//add group loan
    				$a_time=1;
    					
    				$SaleIdGenerate = $SaleIdGenerate +1;
    				$sale_id = $SaleIdGenerate;
    			}
    			 
    			$installMentNumber = $installMentNumber+1;
    
    			 
    			$is_completed =($balance<=0)?1:0;
    			$this->_name="ln_saleschedule";
//     			$begining = $data[$i]['C'];
//     			$ending=$data[$i]['C']-$data[$i]['D'];
    			 
    			$ispay_bank=0;
    			$n++;
    			if($balance<=0){
	    			$datapayment = array(
	    					'branch_id'=>1,
	    					'sale_id'=>$sale_id,//good
	    					'begining_balance'=>$sellingPrice,//$data[$i]['F']+$data[$i]['C'],//good
	    					'begining_balance_after'=>0,//$data[$i]['F']+$data[$i]['C'],//good
	    					'principal_permonth'=>$sellingPrice,//good
	    					'principal_permonthafter'=>0,//good
	    					'total_interest'=>0,//$data[$i]['D'],//good
	    					'total_interest_after'=>0,//$data[$i]['D'],//good
	    					'total_payment'=>$sellingPrice,//$data[$i]['E'],//good
	    					'total_payment_after'=>0,//$data[$i]['E'],//good
	    					'ending_balance'=>0,
	    					'amount_day'=>30,
	    					'is_completed'=>1,
	    					'date_payment'=>$lastPaidDate,
	    					'received_date'=>$lastPaidDate,
	    					//'ispay_bank'=>($data[$i]['B']=='បានប្លង់រឹង'?2:0),
	    					'paid_date'=>$lastPaidDate,
	    					'note'=>'',
	    					'percent'=>0,
	    					'percent_agree'=>0,
	    					'is_installment'=>1,
	    					'no_installment'=>1,//$data[$i]['A'],
	    					'last_optiontype'=>1,
	    					'ispay_bank'=>$ispay_bank,
	    					//'note'=>$data[$i]['L'],
	    
	    			);
	    			$saledetailid = $this->insert($datapayment);
    			}
    
    			$total_principal_permonthpaid=0;
    			if(!empty($totalPaid)){
    				$total_principal_permonthpaid=$totalPaid;
    				$arr_client_pay = array(
    						'branch_id'						=>	$branch_id,
    						'receipt_no'					=>	"OldSystem",
    						'selling_price'					=>  $sellingPrice,
    						'allpaid_before'				=>  $total_principal_permonthpaid,
    						'date_pay'					    =>	$lastPaidDate,
    						'date_input'					=>	$lastPaidDate,
    						'from_date'						=>	$lastPaidDate,//check more
    						'date_payment'					=>	$lastPaidDate,
    						'client_id'                     =>	$client_id,
    						'sale_id'						=>	$sale_id,
    						'land_id'						=>	$land_id,
    						'outstanding'                   =>	$balance,//ប្រាក់ដើមមុនបង់
    						'total_principal_permonth'		=>	$totalPaid,//ប្រាក់ដើមត្រូវបង់
    						'total_interest_permonth'		=>	0,
    						'penalize_amount'				=>	0,
    						'total_payment'					=>	$totalPaid,
    						'service_charge'				=>	0,
    						'principal_amount'				=>	$balance,//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    						'balance'						=>	0,
    						'recieve_amount'				=>	$totalPaid,
    						'amount_payment'				=>	$totalPaid,
    						'note'							=>	'',
    						'cheque'						=>	'',
    						'user_id'						=>	$userId,
    						'payment_option'				=>	1,
    						'status'						=>	1,
    						'is_completed'					=>	1,
    						'field3'						=>  ($first_pay==0)?1:3,
    						'is_payoff'						=>  0,
    						'extra_payment' 				=>  0,
    						'payment_times'					=>  $installMentNumber,
    						'payment_method'				=>  1,
    						'total_principal_permonthpaid'	=>	$total_principal_permonthpaid,
    						'total_interest_permonthpaid'	=>	0,//$data[$i]['D'],//ok ការប្រាក់បានបង
    						'penalize_amountpaid'			=>	0,//$data[$i]['G'],// ok បានបង
    						'service_chargepaid'			=>	0,// okបានបង
    				);
    
    				$this->_name = "ln_client_receipt_money";
    				$client_pay = $this->insert($arr_client_pay);
    
    				$arr = array(
    						'crm_id'				=>	$client_pay,
    						'land_id'			    =>	$land_id,//ok
//     						'lfd_id'				=>	$saledetailid,//ok
    						'date_payment'			=>	$lastPaidDate, // ថ្ងៃដែលត្រូវបង់
    						'principal_permonth'	=>	0,
    						'total_interest'		=>	0,
    						'total_payment'			=>	0,
    						'total_recieve'			=>	0,
    						'pay_after'				=>	0,
    						'penelize_amount'		=>	0,
    						'service_charge'		=>	0,
    						'penelize_new'			=>	0,
    						'service_charge_new'	=>	0,
    						'capital'				=>  $sellingPrice,
    						'remain_capital'		=>	$balance, 
    						'old_interest'			=>	0,
//     						'old_total_payment'		=>	$data[$i]['D'],
//     						'old_principal_permonth'=>	$data[$i]['D'],
//     						'old_total_priciple'	=>	$data[$i]['D'],
    						'old_penelize'			=>	0,
    						'old_service_charge'	=>	0,
    						'last_pay_date'			=>	$lastPaidDate,
    						'paid_date'				=>	$lastPaidDate,
    						'is_completed'			=>	1,
    						'status'				=>	1
    				);
    				$this->_name='ln_client_receipt_money_detail';
    				$this->insert($arr);
    
    			}
    			$oldland_str=$lotNo;
    			$install = $install+1;
    		}
    		$db->commit();
    	}catch(Exception $e){
    		$db->rollBack();
    		echo $e->getMessage();
    		exit();
    	}
    }
    
    function checkClientName($clientName){
    	$db = $this->getAdapter();
    	$sql="SELECT c.* FROM ln_client as c WHERE 1";
    	$sql.=" AND c.name_kh = '$clientName' LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    function checkClientNameInCopy($clientName){
    	$db = $this->getAdapter();
    	$sql="SELECT c.* FROM ln_client_copy as c WHERE 1";
    	$sql.=" AND c.name_kh = '$clientName' LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
    function KPmorndanyUpdateClientName($data){
		try{    	
    	$count = count($data);
    	
    	for($i=3; $i<=$count; $i++){
    		
    	$clientName =$data[$i]['G'];
    	$landAddress=$data[$i]['B'];
    	$salePrice=$data[$i]['H'];
    	
	    	$sql="SELECT 
			    		p.land_address,
						s.id AS saleId,
						c.client_id,
						c.`name_kh`
					FROM 
						`ln_properties` p,
						`ln_sale` s,
						`ln_client` c
					WHERE p.`id` = s.`house_id`
					AND s.`client_id`=c.`client_id`
					AND p.`land_address`='".$landAddress."' limit 1";
    		$db = $this->getAdapter();
    		$result = $db->fetchRow($sql);
    		
    		
    		$sql="SELECT
	    		p.land_address,
	    		s.id AS saleId,
	    		c.client_id,
	    		c.`name_kh`
    		FROM
	    		`ln_properties_copy` p,
	    		`ln_sale_copy` s,
	    		`ln_client_copy` c
    		WHERE p.`id` = s.`house_id`
    		AND s.`client_id`=c.`client_id`
    		AND p.`land_address`='".$landAddress."' limit 1";
    		$resultCopy = $db->fetchRow($sql);
    		
    		
    		if($result){
    			$clientCorrecName =$data[$i]['G'];
    			$checkClientCorrecName = $this->checkClientName($clientCorrecName);// check correct name in Client
    			if(!empty($checkClientCorrecName)){
    				$this->_name='ln_sale';
    				$arr = array(
    						'price_before'=>$salePrice,
    						'price_sold'=>$salePrice,
    						'discount_amount'=>0,
    						'discount_percent'=>0,
    						
    						'client_id'=>$checkClientCorrecName["client_id"]
    				);
    				$where = 'id='.$result['saleId'];
    				$this->update($arr, $where);
    			}else{
    				$sqlCountSale="
    				SELECT
    					COUNT(s.id)
    				FROM `ln_sale` s
    				WHERE  s.`client_id`= ".$result['client_id'];
    				$db = $this->getAdapter();
    				$countSaleClient = $db->fetchOne($sqlCountSale);
    				
    				if($countSaleClient>1){
    					$this->_name='ln_client';
    					$arrNewClient = array(
    							'name_kh'=>$clientName
    					);
    					$newClientId = $this->insert($arrNewClient);
    					
    					$this->_name='ln_sale';
    					$arr = array(
    							'client_id'=>$newClientId,
    							'price_before'=>$salePrice,
    							'price_sold'=>$salePrice,
    							'discount_amount'=>0,
    							'discount_percent'=>0,
    					);
    					$where = 'id='.$result['saleId'];
    					$this->update($arr, $where);
    				}else{
    					$this->_name='ln_client';
    					$arr = array(
    							'name_kh'=>$clientName
    					);
    					$where = 'client_id='.$result['client_id'];
    					$this->update($arr, $where);
    				}
    			}
    		}
    		
    		if($resultCopy){
    			$clientCorrecName =$data[$i]['G'];
    			$checkClientCorrecName = $this->checkClientNameInCopy($clientCorrecName);// check correct name in Client copy
    			if(!empty($checkClientCorrecName)){
    				$this->_name='ln_sale_copy';
    				$arr = array(
    						'client_id'=>$checkClientCorrecName["client_id"],
    						
    						'price_before'=>$salePrice,
    						'price_sold'=>$salePrice,
    						'discount_amount'=>0,
    						'discount_percent'=>0,
    				);
    				$where = 'id='.$resultCopy['saleId'];
    				$this->update($arr, $where);
    			}else{
    				$sqlCountSale="
    				SELECT
    				COUNT(s.id)
    				FROM `ln_sale_copy` s
    				WHERE  s.`client_id`= ".$resultCopy['client_id'];
    				$db = $this->getAdapter();
    				$countSaleClient = $db->fetchOne($sqlCountSale);
    		
    				if($countSaleClient>1){
    					$this->_name='ln_client_copy';
    					$arrNewClient = array(
    							'name_kh'=>$clientName
    					);
    					$newClientId = $this->insert($arrNewClient);
    						
    					$this->_name='ln_sale_copy';
    					$arr = array(
    							'price_before'=>$salePrice,
    							'price_sold'=>$salePrice,
    							'discount_amount'=>0,
    							'discount_percent'=>0,
    							
    							'client_id'=>$newClientId
    					);
    					$where = 'id='.$resultCopy['saleId'];
    					$this->update($arr, $where);
    				}else{
    					$this->_name='ln_client_copy';
    					$arr = array(
    							'name_kh'=>$clientName
    					);
    					$where = 'client_id='.$resultCopy['client_id'];
    					$this->update($arr, $where);
    				}
    			}
    		}
    	}
		}catch (Exception $e){
			echo $e->getMessage();exit();
		}
    }
    function getProjectId($data){
    	$db = $this->getAdapter();
    	$sql=" SELECT br_id  FROM `ln_project` WHERE 1 ";
    	if(!empty($data['projectName'])){
    		$sql.=" AND project_name='".$data['projectName']."'";
    	}
    	$projectId = $db->fetchOne($sql);
    	if(empty($projectId)){
    		$arr = array(
    				'project_name'=>$data['projectName'],
    				'br_address'=>$data['projectAddress'],
    		);
    		$this->_name='ln_project';
    		$projectId =  $this->insert($arr);
    	}
    	return $projectId;
    }
    function getStaffId($data){
    	$sql="SELECT co_id FROM `ln_staff` WHERE 1";
    	if(!empty($data['staffName'])){
    		$sql.=" AND co_khname='".$data['staffName']."'";
    	}
    	$db = $this->getAdapter();
    	$staffId = $db->fetchOne($sql);
    	if(empty($staffId)){
    		$arr = array(
    				'co_khname'=>$data['staffName'],
    				'position_id'=>1,
    				'branch_id'=>$data['branchId']
    		);
    		$this->_name='ln_staff';
    		$staffId =  $this->insert($arr);
    	}
    	return $staffId;
    }
    
}   

