<?php
class Tellerandexchange_Model_DbTable_Dbexchange extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_exchange';
	function addXchange($_data){
		$arr = array(
				'branch_id'=>$_data['branch_id'],
				'invoice_code'=>$_data['number_code'],
				'receive_amount'=>$_data['onetomany'],
				'date'=>$_data['date'],
				'user_id'=>$_data['cusomer'],
				
		);
		$id = $this->insert($arr);
		$this->_name='ln_exchange_detail';
		$ids = explode(',', $_data['record_row']);
		foreach ($ids as $i){
			$arr = array(
				'exchange_id'=>$id,
				'from_currency_type'=>$_data['from_type'.$i],
				'to_currency_type'=>$_data['to_type'.$i],
				'specail_customer'=>empty($_data['is_spacial'.$i])?0:1,
				'exchange_rate'=>$_data['exchange_rate'.$i],
				'changed_amount'=>$_data['exchange_amount'.$i],
				'recieved_amount'=>$_data['amount_exchanged'.$i],
				);
		$this->insert($arr);
		
		}
	}
 	
// 	public function updatxchangById($id,$data){//update capital detail by id
// 		$this->_name='ln_exchange_detail';
// 		$where = $this->getAdapter()->quoteInto('id = ?', $id);
// 		$this->update($data, $where);
// 	}
	
	function updatexchange($_data){
		$arr = array(
				'branch_id'=>$_data['branch_id'],
				'invoice_code'=>$_data['number_code'],
				'receive_amount'=>$_data['onetomany'],
				'date'=>$_data['date'],
				'user_id'=>$_data['cusomer'],
		);
		$where=" id = ".$_data['id'];
		$this->update($arr, $where);
	}
	///for sigle exchange:
	function getAllExchangeList(){
		$db = $this->getAdapter();
		$sql = "SELECT id ,
		(SELECT name_en FROM `ln_client` WHERE client_id= customer_id LIMIT 1) AS client_name
		,date,invoice_code
		,(SELECT from_to FROM `ln_exchange_detail` WHERE exchange_id = id LIMIT 1) AS from_to
		,(SELECT from_amount FROM `ln_exchange_detail` WHERE exchange_id = id LIMIT 1) AS from_amount
		,(SELECT to_amount FROM `ln_exchange_detail` WHERE exchange_id = id LIMIT 1) AS to_amount
		,(SELECT exchange_rate FROM `ln_exchange_detail` WHERE exchange_id = id LIMIT 1) AS exchange_rate
		,receive_dollar,return_dollar
		,(SELECT name_en FROM `ln_view` WHERE TYPE=12 AND key_code=is_single LIMIT 1) as exchange_type
		,status 
		FROM `ln_exchange`";
		return $db->fetchAll($sql);
	}
	function getAllExchangeListMulti($search){
		$db = $this->getAdapter();
		$sql = "SELECT e.id ,
		(SELECT name_en FROM `ln_client` WHERE client_id= e.customer_id LIMIT 1) AS client_name
		,e.date,e.invoice_code
		,(SELECT d.from_to FROM `ln_exchange_detail`d WHERE d.exchange_id = e.id LIMIT 1) AS from_to
		,(SELECT from_amount FROM `ln_exchange_detail` WHERE exchange_id = e.id LIMIT 1) AS from_amount
		,(SELECT exchange_rate FROM `ln_exchange_detail` WHERE exchange_id = e.id LIMIT 1) AS exchange_rate
		,(SELECT to_amount FROM `ln_exchange_detail` WHERE exchange_id = e.id LIMIT 1) AS to_amount
		,e.receive_dollar,e.return_dollar
		,(SELECT name_en FROM `ln_view` WHERE TYPE=12 AND key_code=e.is_single LIMIT 1) as exchange_type
		,e.status
		FROM `ln_exchange` AS e WHERE 1 ";
		
		$from_date =(empty($search['from_date']))? '1': "e.date >= '".$search['from_date']." 00:00:00'";
		$to_date = (empty($search['to_date']))? '1': "e.date <= '".$search['to_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if($search['user_id']>0){
			$where.=" AND user_id = ".$search['user_id'];
		}
		$order=' ORDER BY e.id';
		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	function getAllSigleExchange($search){
		$db = $this->getAdapter();
		$from_date =(empty($search['from_date']))? '1': "e.statusDate >= '".$search['from_date']." 00:00:00'";
		$to_date = (empty($search['to_date']))? '1': "e.statusDate <= '".$search['to_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if($search['user_id']>0){
			$where.=" AND e.userid = ".$search['user_id'];
		}
		$sql = "SELECT
		e.`id`,
		DATE_FORMAT(e.`statusDate`,'%d/%m/%Y') as `statusDate`,
		e.`from_to`,
		e.`fromAmount`,
		e.`rate`,
		e.`toAmount`,
		e.`recievedAmount`,
		e.`changedAmount`,
		e.status
		FROM
		`ln_xchange` AS e ";
		
		$order=' ORDER BY e.id';
		return $db->fetchAll($sql.$where);
	}
	function getExchangeDetail($id){
		$db = $this->getAdapter();
		$sql = "SELECT from_to,from_amount,to_amount,exchange_rate,from_currency_type,to_currency_type,
			(SELECT c.symbol FROM `ln_currency` AS c WHERE c.id=from_currency_type LIMIT 1) AS from_name,
			(SELECT c.symbol FROM `ln_currency` AS c WHERE c.id=to_currency_type LIMIT 1) AS to_name
		FROM `ln_exchange_detail` WHERE exchange_id= $id ";
		return $db->fetchAll($sql);
	}
	function addXchangeMoney($_data){
		$arr = array(
				'branch_id'=>$_data['branch_id'],
				'invoice_code'=>$_data['number_code'],
				'exchange_type'=>$_data['onetomany'],
				'customer_id'=>$_data['customer'],
				'date'=>$_data['date'],
				'receive_dollar'=>$_data['getusa'],
				'receive_riel'=>$_data['getr'],
				'receive_bath'=>$_data['getb'],
				'return_dollar'=>$_data['returnusa'],
				'return_riel'=>$_data['returnr'],
				'return_bath'=>$_data['returnb'],
				'is_sigle'=>1
		);
		$id = $this->insert($arr);
	
		$this->_name='ln_exchange_detail';
		$ids = explode(',', $_data['record_row']);
		foreach ($ids as $i){
			$from_type = $this->getCurrencyById("`curr_nameen`, `symbol`", $_data["from_type".$i]);
			$to_type = $this->getCurrencyById("`curr_nameen`, `symbol`", $_data["to_type".$i]);
			$arr = array(
					'exchange_id'=>$id,
					'from_currency_type'=>$_data['from_type'.$i],
					'to_currency_type'=>$_data['to_type'.$i],
					'from_amount'=>$_data['exchange_amount'.$i],
					'to_amount'=>$_data['amount_exchanged'.$i],
					'specail_customer'=>empty($_data['is_spacial'.$i])?0:1,
					'exchange_rate'=>$_data['exchange_rate'.$i],
					'from_to'=>$from_type['curr_nameen'] . " - " . $to_type["curr_nameen"],//
					'specail_customer'=>(empty($_data['special_cus']))? 0 : 1,//
					'date'=>date('Y-m-d')
			);
			$this->insert($arr);
		}
	}
	function saveExchange($data){//for save exchange sigle
		$session_user=new Zend_Session_Namespace('auth');
		$data["to_amount_type"] =$data['exchangeto'];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$user_id = $session_user->user_id;
			$_data = array(
				'customer_id'=>0,
				'is_single'=>1,
				'receive_amount'=>$data['recieve_money'],
				'return_amount'=>$data['return_money'],
				'invoice_code'=>$data['inv_no'],
				'date'=>date("Y-m-d"),
				'user_id'=>$user_id,
				'status'=>1
			);
			$x_id = $this->insert($_data);
			$to_type = $this->getCurrencyById("`curr_nameen`, `symbol`", $data["to_amount_type"]);
			$from_type = $this->getCurrencyById("`curr_nameen`, `symbol`", $data["exchangefrom"]);
	
				$x_detail = array(
						'exchange_id'=>$x_id,//
						'to_currency_type'=>$data["to_amount_type"],//
						'from_currency_type'=>$data["exchangefrom"],//
						'from_amount'=>$data['from_amount'],//
						'to_amount'=>$data['to_amount'],//
						'exchange_rate'=>0,
						'from_to'=>$from_type['curr_nameen'] . " - " . $to_type["curr_nameen"],//
// 						'recieved_amount'=>$data['recieve_money'],//
						'status'=>1,//
						'date'=>date("Y-m-d"),//
						'specail_customer'=>(empty($data['special_cus']))? 0 : 1//
// 						'to_amount'=>$data['to_amount'],//
						
						
						
				);
			$this->_name='ln_exchange_detail';
			$this->insert($x_detail);
			return  $db->commit();
		} catch (Exception $e) {
			$db->rollBack();
			echo $e->getMessage();
		}
	}
	}
	function getCurrencyById($fieldname,$id){
		$db = $this->getAdapter();
		$sql = "SELECT ". $fieldname ."
		FROM `ln_currency`
		WHERE id = ". $id;
// 		    	echo $sql; exit();
		return $db->fetchRow($sql);
	}
function getexpensebyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT id,branch_id,invoice_code,receive_amount,date,user_id FROM $this->_name where id=$id ";
	return $db->fetchRow($sql);
}
	
?>