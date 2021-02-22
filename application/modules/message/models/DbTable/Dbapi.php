<?php

class Message_Model_DbTable_Dbapi extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_sendsms';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllSentSMS($data){
    	$sql="SELECT id,
		    	  contance,
		    	  phone_number,
		    	  case when length(phone_number) > 60 
					then concat(substring(phone_number, 1, 60), '...')
					else phone_number end as phone_number,
		    	  send_date,
		    	  send_opttype,
		    	 (SELECT first_name FROM `rms_users` WHERE id=sms.user_id LIMIT 1) AS user_name
		    	 FROM `ln_sendsms` AS sms WHERE 1 ";
    	$where='';
    	$from_date =(empty($data['start_date']))? '1': " send_date >= '".$data['start_date']." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " send_date <= '".$data['end_date']." 23:59:59'";
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	if(!empty($data['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($data['adv_search']));
    		$s_where[] = " sms.contance LIKE '%{$s_search}%'";
    		$s_where[] = " sms.phone_number LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	 $where.=' ORDER BY id DESC  ';
    	
    	$db = $this->getAdapter();
//     	echo $sql.$where_date.$where;exit();
    	return $db->fetchAll($sql.$where_date.$where);//sale_detail
    }
   
    public function SendSMSAPI($data){
    	try
    	{
    		$db = $this->getAdapter();
    		
    		$message=$data['smscontance'];
    		$phone='';
    		$respone='';
    		$phone ='';
    		$count =0;
    		$saleids ='';
    		$amtphonenumber=2;
    		if($data['opt_sms']==1){//only selected id
    			if(!empty($data['id_selected'])){
    				$ids = explode(',', $data['id_selected']);
    				$key = 1;
    				foreach ($ids as $i){
    					$count++;
    					if(!empty($saleids) AND $count>1){
    						$saleids.=','.$data['sale_id'.$i];
    						$phone.=';'.str_replace(' ', '', $data['phone_num'.$i]);
    					}else{
    						$saleids = $data['sale_id'.$i];
    						$phone=str_replace(' ', '', $data['phone_num'.$i]);
    					}
    					if($count%$amtphonenumber==0){
							$this->insertMessage($message,$phone,$data['opt_sms']);
    						$phone='';
    						$count=0;
    					}
    				}
    				if($count>0){
    				}
    			}
    		}elseif($data['opt_sms']==2){//all clients
    			$sql="SELECT DISTINCT(`phone`) AS phone_nums,s.id AS sale_id
    			      FROM `ln_client` c,
    			    		`ln_sale` s
    			      WHERE c.client_id=s.client_id
    			    		AND c.phone!='' ";
    			$result = $db->fetchAll($sql);
    			if(!empty($result)){
    				foreach ($result as $key => $rs){
    					$count++;
    					if(!empty($saleids) AND $count>1){
    						$saleids.=','.$rs['sale_id'];
    						$phone.=';'.str_replace(' ', '', $rs['phone_nums']);
    					}else{
    						$saleids = $rs['sale_id'];
    						$phone=str_replace(' ', '', $rs['phone_nums']);
    					}
    					if($count%$amtphonenumber==0){
    						$this->insertMessage($message,$phone,$data['opt_sms']);
    						$phone='';
    						$count=0;
    					}
    				}
    				if($count>0){
    					$this->sendSMS($message,$phone);
    					$this->insertMessage($message,$phone,$data['opt_sms']);
    				}
    				
    			}
    		}
    		elseif($data['opt_sms']==3){//all phone number input
    			$phone = $data['receiver'];
    			$this->insertMessage($message,$phone,$data['opt_sms']);
    		}
//     		return $respone;
    	}
    	catch (Exception $ex)
    	{
    		echo $ex;
    	}
    }
    function insertMessage($message,$phone,$opt){
    	$this->sendSMS($message,$phone);
    	$arr = array(
    			'contance'=>$message,
    			'phone_number'=>$phone,
    			'send_opttype'=>$opt,
    			'send_date'=>date('Y-m-d h:i:s'),
    			'user_id'=>$this->getUserId(),
    	);
    	$this->insert($arr);
    }
    function sendSMS($message,$phone){
    	$url="http://sandbox.mekongsms.com/api/postsms.aspx";
    	$username='camapp_free@apitest';
    	$pass='d5c5d91288a32cad367ae7170f54860c';
    	$sender='CAM APP';
    	
    	//create fields array
    	//for param cd is optional param that allow customers insert some data to our database ex:transactionid
    	$fields =('username='.$username.'&pass='.$pass.'&sender='.$sender.'&smstext='.urlencode($message).'&isflash=0'.'&gsm='.$phone.'&int=0');
    	    		$headers = array('Content-Type: application/x-www-form-urlencoded');
    	    			//open curl
    	    $curl = curl_init();
    	    curl_setopt_array($curl, array(
    	    	CURLOPT_URL => $url,
    	    	CURLOPT_RETURNTRANSFER => true,
    	    	CURLOPT_TIMEOUT => 300000,
    	    	CURLOPT_POST => true,
    	    	CURLOPT_POSTFIELDS =>$fields,
    	    	CURLOPT_HTTPHEADER => $headers ,));
    	    	$respone = curl_exec($curl);
    	$err = curl_error($curl);//you can echo curl error
    	curl_close($curl);//you need to close curl connection
    }
}