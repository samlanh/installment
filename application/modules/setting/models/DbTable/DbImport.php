<?php

class Setting_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'ldc_product';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function updateItemsByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	print_r($data);
    	for($i=3; $i<=$count; $i++){
    		$_arr=array(
    				'client_number'=> $data[$i]['H'],//$_data['client_no'],
    				'name_kh'	  => $data[$i]['G'],
    				'sex'	      => 1,
    				'pro_id'      => 12,
    				'dis_id'      => 0,
    				'com_id'      => 0,
    				'village_id'  => 0,
    				'street'	  => 0,
    				'house'	      => 0,
    				'nationality'=>'ខ្មែរ',
    				'phone'	      => '',
    				'create_date' => date("Y-m-d"),
//     				'remark'	  => $_data['desc'],
    				'status'      => 1,
    				'client_d_type'      => 4,
    				'user_id'	  => $this->getUserId(),
    				'p_nationality'      => 'ខ្មែរ',
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
    		
    		$dbtable = new Application_Model_DbTable_DbGlobal();
    		$loan_number = $dbtable->getLoanNumber($data);
    			   $arr = array(
    				'branch_id'=>1,
    			   	'receipt_no'=>'',
    				'sale_number'=>$loan_number,
//     			   	'house_id'=>$data["land_code"],
    			   	'payment_id'=>$data["schedule_opt"],
    				'client_id'=>$data['member'],
    				'price_before'=>$data['total_sold'],
    				'discount_amount'=>$data['discount'],
    			   	'discount_percent'=>$data['discount_percent'],
    				'price_sold'=>$data['sold_price'],
    				'other_fee'=>$data['other_fee'],
    				'paid_amount'=>$data['deposit'],
    				'balance'=>$data['balance'],
    				'buy_date'=>$data['date_buy'],
    				'end_line'=>$data['date_line'],
    				'interest_rate'=>$data['interest_rate'],
    				'total_duration'=>$data['period'],
    			   	'startcal_date'=>$data['release_date'],
    				'first_payment'=>$data['first_payment'],
    			   	'validate_date'=>$data['first_payment'],
    				'payment_method'=>1,
    				'note'=>$data['note'],
    			   	'land_price'=>0,//$data['house_price'],
    			   	'total_installamount'=>$data['total_installamount'],
    			   	'typesale'=>$data['typesale'],
    				'build_start'=>$data['start_building'],
    				'amount_build'=>$data['amount_build'],
    				'is_reschedule'=>$is_schedule,
    			    'agreement_date'=>$data['agreement_date'],
    				'staff_id'=>$data['staff_id'],
    				'comission'=>$data['commission'],
    			   	'full_commission'=>$data['full_commission'],
    				'create_date'=>date("Y-m-d"),
    				'user_id'=>$this->getUserId(),
    			   	'amount_daydelay'=>$data['delay_day']
    				);
    		$this->_name='ln_sale';
    		$id = $this->insert($arr);//add group loan
    		
    	}
    }
}   

