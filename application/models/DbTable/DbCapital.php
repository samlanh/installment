<?php
/**
 * @Date 19/06/2013
 * @author sovann
 * @version 1.0
 */
class Application_Model_DbTable_DbCapital extends Zend_Db_Table_Abstract
{
    protected $_name = 'cs_current_capital';
    
    /**
     * Get current balances 
     * @param int $user_id
     * @param int $currency_type
     * @return number amouont of current user and currency type
     */
    function getCapitalDetailById($tran_id,$tran_type,$curr_type){//for record capital detail by id 
    	$db = $this->getAdapter();
    	$this->_name='cs_capital_detail';
    	$sql = " SELECT * FROM ". $this->_name ." WHERE tran_id = $tran_id AND tran_type = $tran_type AND curr_type = ".$curr_type." AND status=1";
    	return $db->fetchRow($sql);
    }
    
    public function DetechCapitalExist($user_id,$currency_type,$current_date=null){//check add current capital exist
    	$db = $this->getAdapter();
    	if($current_date==null){
    		$current_date  = date('Y-m-d');
    	}
    	
    	$this->_name='cs_current_capital';
    	$sql = "SELECT * FROM ". $this->_name ." WHERE userid=". $user_id . " AND currencyType=". $currency_type;
    	$sql.=" AND `statusDate` ='".$current_date."' GROUP BY currencyType LIMIT 1 ";
//     	$sql.=" AND DATE_FORMAT(`statusDate`, '%d/%m/%Y') ='".$current_date."' GROUP BY currencyType LIMIT 1 ";
    	return ($db->fetchRow($sql));
    }
  
    public function AddCurrentBalanceById($arr){
    	$this->_name = 'cs_current_capital';
    	$this->insert($arr);
    	
    }
    
    public function updateCurrentBalanceById($id,$data){
    	$this->_name='cs_current_capital';
    	$where = $this->getAdapter()->quoteInto('id = ?', $id);
    	$this->update($data, $where);
    }
    
    public function addMoneyToCapitalDetail($data){//add capital detail
    	$this->_name = 'cs_capital_detail';
    	if(empty($data['user_id'])){
    		$session_user=new Zend_Session_Namespace('auth');
    		$b = new Application_Model_DbTable_DbCapital();
    		$data['user_id'] =  $session_user->user_id;
    	}
    	if(empty($data['date'])){
    		$data['date']= date("Y-m-d H:i:s");
    	}
    	
    	$data['sign']=1;
    	if($data['amount']<=0){
    		$data['sign']=0;
    		$data['amount']=-$data['amount'];//cos not store unsign
    	}
    	$this->insert($data);
    }
    
    public function updateCapitalDetailById($id,$data){//update capital detail by id
    	$this->_name='cs_capital_detail';
    	$where = $this->getAdapter()->quoteInto('id = ?', $id);
    	$this->update($data, $where);
    }
    
    public function getLastRecordCapitalDetail(){//use for add capital only
    	$db = $this->getAdapter();
    	$sql = " SELECT id FROM `cs_capital_detail` ORDER BY id DESC LIMIT 1 ";
    	$num = $db->fetchOne($sql);
    	if(empty($num)){
    		$num = 0;
    	}
    	return ($num+1);
    }
//     function addBalanceByUser($user_id, $data){
//     	$db = $this->getAdapter();
//     	$db_loan = new Application_Model_DbTable_DbLoan();
//     	$db->beginTransaction();
//     	try {
//     		$amount = 0;
//     		foreach ($data as $k => $val){
//     			if($k != "dollar" && $k != "bath" && $k != "rail") {
//     				continue;
//     			}
//     			$currency_type = 0;
//     			if($k == "dollar"){
//     				$currency_type = 1;
//     				$amount = $data['dollar'];
//     			}
//     			elseif($k == "bath"){
//     				$currency_type = 2;
//     				$amount = $data['bath'];
//     			}
//     			elseif($k == "rail"){
//     				$currency_type = 3;
//     				$amount = $data['rail'];
//     			}
// //     			echo $amount;exit();
//     			$date = date("Y-m-d H:i:s");
//     			$record = $this->getLastRecordCapitalDetail();
//     			$_arr = array(
//     					'tran_id' 	=>$record,
//     					'tran_type' =>1,
//     					'curr_type'	=>$currency_type,
//     					'amount'	=>$amount,
//     					'date'		=>$data['date'],
//     					'user_id'	=>$data['user_id']
//     			);
//     			if($amount!=0){
//     				$this->addMoneyToCapitalDetail($_arr);//add to capital detail
//     				$rs = $this->DetechCapitalExist($data['user_id'],$currency_type,$data['date']);//check if add capital exist
//     				if(!empty($rs)){
//     					$arr = array(
//     							'amount'=>$rs['amount']+$amount
//     					);
//     					$this->updateCurrentBalanceById($rs['id'],$arr);
//     				}else{
//     					$arr =array(
//     							'amount'=>$amount,
//     							'currencyType'=>$currency_type,
//     							'userid'=>$data['user_id'],
//     							'statusDate'=>$data['date']
//     					);
//     					$this->AddCurrentBalanceById($arr);
//     				}
//     			}
//     		}
//     		return  $db->commit();
//     	} catch (Exception $e) {
//     		$db->rollBack();
//     	}
//     }
    public function AddCSPCurrency($user_id,$data){
    	$db=$this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$ids = explode(',',$data['record_row']);
			$this->_name='cs_pscamount';
	    	foreach ($ids as $i){
	    		for($j=1; $j<=3; $j++){
	    				if(!empty($data["pcs_".$j."_".$i])){
	    					$arr =array(
	    							'currency_type'=>$j,
	    							'volum'=>$data["curr_".$j."_".$i],
	    							'psc_amount'=>$data["pcs_".$j."_".$i],
	    							'note'=>$data["note_".$i],
	    							'date'=>$data['date'],
	    							'create_date'=>date('Y-m-d'),
	    							'user_id'=>$user_id,
	    					);
	    					$this->insert($arr);
	    				}
	    		}
    	}
    	  return  $db->commit();
    	} catch (Exception $e) {
    		echo $e->getMessage();
    		$db->rollBack();
    	}
    }
    function getCurrentBallancesByCurrentUser($user_id,$current_date=null){//total money add capital to agent only
    	$cur = new Application_Model_DbTable_DbCurrencies();
    	$cur_type = $cur->getCurrencyList();
    	if($current_date==null){
    		$current_date  = date('d/m/Y');
    	}
    
    	$balance = array();
    	foreach($cur_type AS $i => $t){
    		if($t['symbol'] == "$"){
    			$balance["dollar"] = $this->getAmountBalanceByUser($user_id,1, $t['id'],$current_date);
    		}
    		elseif($t['symbol'] == "B"){
    			$balance["bath"] = $this->getAmountBalanceByUser($user_id,1, $t['id'],$current_date);
    		}
    		elseif($t['symbol'] == "R"){
    			$balance["rail"] = $this->getAmountBalanceByUser($user_id,1, $t['id'],$current_date);
    		}
    	}
    	return $balance;
    }
    function getAmountBalanceByUser($user_id,$tran_type,$currency_type,$current_date=null){//សំរាប់បូកសរុបដើមទុនបន្ថែមសរុបក្នុងថ្ងៃនេះ
    	$db = $this->getAdapter();
    	if($current_date==null){
    		$current_date  = date('d/m/Y');
    	}
    	if($user_id==null){
    		$session_user=new Zend_Session_Namespace('auth');
    		$user_id = $session_user->user_id;
    	}
    
    	$this->_name='cs_capital_detail';
    	$sql = "SELECT SUM(amount) as amount FROM ". $this->_name ." WHERE user_id=". $user_id . " AND curr_type=". $currency_type;
    	$sql.=" AND tran_type = ".$tran_type." AND DATE_FORMAT(`date`, '%d/%m/%Y') ='".$current_date."' ";
//     	$amount =  $db->fetchOne($sql);
    	$group = " GROUP BY curr_type ";
    	$where_incr = ' AND sign = 1';
    	$increase_total =  $db->fetchOne($sql.$where_incr.$group);
    	$where_decr = ' AND sign = 0';
    	$decrease_total =  $db->fetchOne($sql.$where_decr.$group);
    	$amount = $increase_total-$decrease_total;
    	if(empty($amount)){
    		$amount = 0;
    	}
    	return $amount;
    }
    
    
    
    //     public function addCurrentCapitalAgent($user_id,$amount,$current_type){
    //     	$this->_name='cs_current_capital';
    //     	$this->checkExits($user_id,$current_type);
    //     	$arr = array(
    //     			'currencyType'=>$current_type,
    //     			'statusDate'=> date("Y-m-d H:i:s"),
    //     			'amount'=> $amount,
    //     			'userid'=>$user_id,
    //     			 );
    //     	$this->insert($arr);
    //     }
    
    
    /**
     * Check recodre exits on not
     * @param int $user_id
     * @param int $currency_type
     * @return if exits recorde return id else return false
     */
    //     function checkExits($user_id, $currency_type){
    //     	$db = $this->getAdapter();
    //     	$sql = "SELECT id FROM ". $this->_name ." WHERE userid=". $user_id . " AND currencyType=". $currency_type;
    //     	$id = $db->fetchOne($sql);
    //     	if(!empty($id)){
    //     		return $id;
    //     	}
    //     	return false;
    //     }
    
    /**
     * For update balance of user for exchange modules
     * @param int $user_id
     * @param int $currency_type
    * @param double $amount
    */
    //     function updateBalanceByUserId($user_id, $currency_type, $amount){
    //     	$id = $this->checkExits($user_id, $currency_type);
    //     	$date = date("Y-m-d H:i:s");
    //     	if(!empty($id)){
    // 	    	$data=array(
    		// 	    			"amount"=>$amount,
    		// 	    			"statusDate"=>$date
    		// 	    	);
    // 	    	$where = "userid=". $user_id . " AND currencyType=". $currency_type;
    // 	    	$this->update($data, $where);
    //     	}
    //     	else{
    //     		$data=array(
    		//     				"amount"=>$amount,
    		//     				"userid"=>$user_id,
    		//     				"currencyType"=>$currency_type,
    		//     				"statusDate"=>$date
    		//     				);
    //     		$this->insert($data);
    //     	}
    //     }
}


