<?php
class Accounting_Model_DbTable_DbJournal extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_journal';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
	function addnewJournal($data){
		$arr = array(
				'branch_id'=>$data['branch_id'],
				'receipt_number'=>$data['invoice'],
				'journal_code'=>$data['journal_code'],
				'currency_id'=>$data['currency_type'],
				'debit'=>$data['debit'],
				'credit'=>$data['credit'],
				'date'=>$data['add_date'],
				'create_date'=>date('Y-m-d'),
				'note'=>$data['note'],
				'user_id'=>$this->getUserId(),
				'from_location'=>0,
				'is_direct'=>1,
			); 
		$id =  $this->insert($arr);
		$ids = explode(',', $data['record_row']) ;
		$this->_name='ln_journal_detail';
		foreach($ids as $i){
			//$increase = ($increase==1)?'+':'-';
			 $arr = array(
			 	'jur_id'=>$id,
				'branch_id'=>$data['branch_id'],
				'account_id'=>$data['account_id'.$i],
				'currency_type'=>$data['currency_type'],
				'debit'=>$data['debit_'.$i],
				'credit'=>$data['credit_'.$i],
				'note'=>$data['note'.$i],
			 	'is_increase'=>'',
			); 
		    $this->insert($arr);
		}
		
	}
	function upDateJournal($data){
		$arr = array(
				'branch_id'=>$data['branch_id'],
				'receipt_number'=>$data['invoice'],
				'journal_code'=>$data['journal_code'],
				'currency_id'=>$data['currency_type'],
				'debit'=>$data['debit'],
				'credit'=>$data['credit'],
				'date'=>$data['add_date'],
				'create_date'=>date('Y-m-d'),
				'note'=>$data['note'],
				'user_id'=>$this->getUserId(),
				'from_location'=>0,
				'is_direct'=>1,
		);
		$where = " id =".$data['id'];
		$this->update($arr, $where);
		
		$this->_name='ln_journal_detail';
		$where = " jur_id =".$data['id'];
		$this->delete($where);
		
		$ids = explode(',', $data['record_row']) ;
		foreach($ids as $i){
			//$increase = ($increase==1)?'+':'-';
			$arr = array(
					'jur_id'=>$data['id'],
					'branch_id'=>$data['branch_id'],
					'account_id'=>$data['account_id'.$i],
					'currency_type'=>$data['currency_type'],
					'debit'=>$data['debit_'.$i],
					'credit'=>$data['credit_'.$i],
					'note'=>$data['note'.$i],
					'is_increase'=>'',
			);
			$this->insert($arr);
		}
	
	}
	function addTransactionJournal($data){
				
		$arr =array(
				'branch_id'=>$data['branch_id'],
				'client_id'=>$data['client_id'],
				'receipt_number'=>$data['receipt_number'],
				'date'=>$data['date'],
				'create_date'=>date('Y-m-d'),
				'note'=>$data['note'],
				'user_id'=>$this->getUserId(),
				'from_location'=>$data['from_location'],
				'receipt_number'=>$data['receipt_number'],
				); 
		$id =  $this->insert($arr);
		unset($arr);
		$this->_name='ln_journal_detail';
		$acc_id =1 ;
		$db_g = new Application_Model_DbTable_DbGlobal();
		$db_g->getAccountBranchByOther($acc_id,$data['branch_id'],$data['currency_type'],$data['balance'],1);//update data to server 
		
		$arr = array(
				'jur_id'=>$id,
				'branch_id'=>$data['branch_id'],
				'account_type'=>1,
				'balance'=>$data['balance'],
				'account_id'=>$acc_id,//for loan number
				'is_increase'=>1,//for loan
				'currency_type'=>$data['currency_type']
				
		);
		$this->insert($arr);
		$arr['is_increase']=0;
		$acc_id=2;
		$arr['account_id']=$acc_id;
		$arr['account_type']=2;
		$accs = $db_g->getAccountBranchByOther($arr['account_id'], $data['branch_id'], $data['currency_type'],$data['balance'],$arr['is_increase']);
		$this->insert($arr);
		
		if(!empty($data['loan_fee'])){
			$arr['is_increase']=1;
			$arr['account_id']=2;
			$arr['note']='Admin fee from disburse loan ';
			$this->insert($arr);
			$db_g->getAccountBranchByOther($arr['account_id'], $data['branch_id'], $data['currency_type'],$data['loan_fee'],$arr['is_increase']);
			
			$arr['is_increase']=1;
			$arr['account_id']=3;
			$this->insert($arr);
			$db_g->getAccountBranchByOther($arr['account_id'], $data['branch_id'], $data['currency_type'],$data['loan_fee'],$arr['is_increase']);
		}
		
	}
	function addJournalDetail($data){
		$this->_name='ln_journal_detail';
		for($i=0;$i<=$data['count'];$i++){
			$arr = array(
					'jur_id'=>$data['jurnal_id'],
					'branch_id'=>$data['branch_id'],
					'account_id'=>$data['account_id'],
					'amount'=>$data['amount'],
					'account_type'=>$data['account_type'],
					'note'=>$data['note']
					);
			$this->insert($data);
		}
		
	}
	function getAllAccountByParrents($parents='',$opt=null){
		$db=$this->getAdapter();
		$sql = "SELECT id,account_code,CONCAT(account_name_en,'-',account_name_kh,'-',account_code) AS name FROM `ln_account_name` WHERE STATUS =1 AND 
				option_type=1 ";
		if($parents!=''){
			$sql.=" AND parent_id =".$parents;
		}
		$row= $db->fetchAll($sql);
		
		if($opt!=null){
			$option = '';
			foreach($row as $r){
				$option .= '<option value="'.$r['id'].'">'.htmlspecialchars($r['name'], ENT_QUOTES).'</option>';
			}
			return $option;
		}else{
			return $row;
		}
	}
	function getParrentIdByAccountId($account_id){
		$db=$this->getAdapter();
		$sql = "SELECT parent_id FROM `ln_account_name` WHERE id=$account_id LIMIT 1";
		return $db->fetchOne($sql);
		
	}
	function getAllParrentAccount($opt = null,$type=3){
		$db=$this->getAdapter();
		$sql = "SELECT id,account_code,CONCAT(account_name_en,'-',account_name_kh,'-',account_code) AS name FROM `ln_account_name` WHERE STATUS =1 AND
		option_type=$type";
		$rows = $db->fetchAll($sql);
		if($opt!=null){
			$option = '<option value="">--- Select ---</option>';
			   	foreach($rows as $row){
			   			
			   		$option .= '<option value="'.$row['id'].'">'.htmlspecialchars($row['name'], ENT_QUOTES).'</option>';
			   	}
			   	return $option;
			
		}else{
			return $rows;
		}
		
	}
	function getJEntryCode($branch_id){
		$db=$this->getAdapter();
		$sql = "SELECT COUNT(id) FROM `ln_journal` WHERE branch_id= $branch_id ";
		
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = $this->getPrefixCode($branch_id)."J";
		
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
		
	}
	function getPrefixCode($branch_id){
		$db  = $this->getAdapter();
		$sql = " SELECT prefix FROM `ln_branch` WHERE br_id = $branch_id  LIMIT 1";
		return $db->fetchOne($sql);
	}
	
}