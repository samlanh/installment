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
	    	$n=0;
	    	for($i=2; $i<=$count; $i++){
	    		if(empty($data[$i]['C'])){
	    			$first_pay=1;
	    			continue;
	    		}
	    		if($a_time==0){
		    		$sql="SELECT `client_id` FROM `ln_client` WHERE name_kh='".$data[$i]['I']."'";
		    		$client_id = $db->fetchOne($sql);
		    		if(empty($client_id)){
			    		$_arr=array(
			    				'client_number' => $data[$i]['J'],//$_data['client_no'],
			    				'name_kh'	  => $data[$i]['I'],
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
		    		
		    		$sql="SELECT id FROM `ln_properties` WHERE land_address = '".$data[$i]['J']."'";
		    		$land_id = $db->fetchOne($sql);
		    		if(empty($land_id)){
		    			$_arr=array(
		    					'branch_id'	  => 1,
		    					'land_code'	  => '',
		    					'land_address'=> $data[$i]['J'],
		    					'street'	  => '',
		    					'price'	      => $data[$i]['L'],
		    					'land_price'  => $data[$i]['L'],
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
		    				'branch_id'=>1,
		    			    'house_id'=>$land_id,
		    			   	'receipt_no'=>'',
		    				'sale_number'=>$loan_number,
		    			   	'payment_id'=>4,
		    				'client_id'=>$client_id,
		    				'price_before'=>$data[$i]['L'],
		    				'discount_amount'=>$data[$i]['M'],
		    			   	'discount_percent'=>0,
		    				'price_sold'=>($data[$i]['L']-$data[$i]['M']),
		    				'other_fee'=>0,
		    				'paid_amount'=>$data[$i]['O'],
		    				'balance'=>($data[$i]['L']-$data[$i]['M'])-$data[$i]['O'],
		    				'interest_rate'=>$data[$i]['P'],
		    				'total_duration'=>$data[$i]['U'],
		    			   	'validate_date'=>$data[$i]['R'],
		    				'payment_method'=>1,
		    				'note'=>'',
		    			   	'land_price'=>0,//$data['house_price'],
		    			   	'total_installamount'=>$data[$i]['O'],
		    			   	'typesale'=>1,
		    				'build_start'=>'',
		    				'amount_build'=>0,
		    				'is_reschedule'=>1,
		    				'staff_id'=>0,
		    				'comission'=>0,
		    			   	'full_commission'=>0,
		    				'create_date'=>date("Y-m-d"),
		    			   	'startcal_date'=>date("Y-m-d",strtotime($data[$i]['Q'])),
		    			   	'first_payment'=>date("Y-m-d",strtotime($data[$i]['Q'])),
		    			   	'agreement_date'=>date("Y-m-d",strtotime($data[$i]['S'])),
	    			   		'buy_date'=>date("Y-m-d",strtotime($data[$i]['Q'])),
	    			   		'end_line'=>date("Y-m-d",strtotime($data[$i]['R'])),
		    			   	'validate_date'=>date("Y-m-d",strtotime($data[$i]['R'])),
		    				'user_id'=>$this->getUserId(),
		    			   	'amount_daydelay'=>0
		    			);
		    			   
		    		$this->_name='ln_sale';
		    		$sale_id = $this->insert($arr);//add group loan
		    		$a_time=1;
	    		}
	    		
	    		if($first_pay==1 AND $first_payment==0){
		    		$this->_name='ln_sale';
		    		$where=" id =".$sale_id;
		    		$arr = array(
		    			'first_payment'=>date("Y-m-d",strtotime($data[$i]['B']))
		    			);
		    	   $this->update($arr, $where);
		    	   $first_payment=1;
	    		}
	    		
	    		$is_completed =($data[$i]['G']=='Y')?1:0;
	    		$cum_interest = $cum_interest+$data[$i]['E'];
	    		$this->_name="ln_saleschedule";
	    		if($n==0){
	    			$begining = $data[$i]['F']+$data[$i]['C'];
	    			$ending=$data[$i]['F'];
	    		}else{
	    			$begining = $ending;
	    			$ending = $data[$i]['F'];
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
	    				'ending_balance'=>$data[$i]['F'],
	    				'cum_interest'=>$cum_interest,
	    				'amount_day'=>30,
	    				'is_completed'=>$is_completed,
	    				'date_payment'=>date("Y-m-d",strtotime($data[$i]['B'])),
	    				'paid_date'=>date("Y-m-d",strtotime($data[$i]['B'])),
	    				'note'=>'',
	    				'percent'=>($first_pay==0)?$data[$i]['N']:0,
	    				'percent_agree'=>($first_pay==0)?$data[$i]['N']:0,
	    				'is_installment'=>($first_pay==0)?1:0,
	    				'no_installment'=>$install,
	    				'last_optiontype'=>1,
	    		);
	    		$saledetailid = $this->insert($datapayment);
	    		
	    		if(!empty($data[$i]['H'])){
	    			$arr_client_pay = array(
	    					'branch_id'						=>	1,//$data["branch_id"],
	    					'receipt_no'					=>	$data[$i]['H'],
	    					'date_pay'					    =>	date("Y-m-d",strtotime($data[$i]['B'])),
	    					'date_input'					=>	date("Y-m-d",strtotime($data[$i]['B'])),
	    					'from_date'						=>	date("Y-m-d",strtotime($data[$i]['B'])),//check more
	    					'client_id'                     =>	$client_id,
	    					'sale_id'						=>	$sale_id,
	    					'land_id'						=>	$land_id,
	    					'outstanding'                   =>	$data[$i]['F']+$data[$i]['C'],//ប្រាក់ដើមមុនបង់
	    					'total_principal_permonth'		=>	$data[$i]['C'],//ប្រាក់ដើមត្រូវបង់
	    					'total_interest_permonth'		=>	$data[$i]['D'],
	    					'penalize_amount'				=>	0,
	    					'total_payment'					=>	$data[$i]['E'],//ប្រាក់ត្រូវបង់ok
	    					'service_charge'				=>	0,
	    					'principal_amount'				=>	$data[$i]['F'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
	    					'balance'						=>	0,
	    					'recieve_amount'				=>	$data[$i]['E'],//ok
	    					'amount_payment'				=>	$data[$i]['E'],//brak ban borng
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
	    					'capital'				=>  $data[$i]['F']+$data[$i]['C'],
	    					'remain_capital'		=>	$data[$i]['F'], // remain balance after paid
	    					'old_principal_permonth'=>	$data[$i]['C'],
	    					'old_total_priciple'	=>	$data[$i]['C'],
	    					'old_interest'			=>	$data[$i]['D'],
	    					'old_total_payment'		=>	$data[$i]['E'],
	    					'old_penelize'			=>	0,
	    					'old_service_charge'	=>	0,
	    					'last_pay_date'			=>	date("Y-m-d",strtotime($data[$i]['B'])),
	    					'paid_date'				=>	date("Y-m-d",strtotime($data[$i]['B'])),
	    					'is_completed'			=>	1,
	    					'status'				=>	1);
	    			$this->_name='ln_client_receipt_money_detail';
	    			$this->insert($arr);
	    			
	    		}
	    		$install = $install+1;
	    	}
// 	    	exit();
	    	$db->commit();	    	
	   }catch(Exception $e){
	   		$db->rollBack();	
	   		echo $e->getMessage();
	   		exit();   		 
       } 
    }   	  
}   

