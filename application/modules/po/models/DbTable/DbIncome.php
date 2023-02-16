<?php
class Po_Model_DbTable_DbIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_income';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllIncome($search=null){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="  SELECT id,
			(SELECT project_name FROM `ln_project` WHERE br_id= projectId LIMIT 1) AS projectName,	   
			(SELECT title FROM `st_cate_income` AS cate WHERE cate.id = cateIncome) AS cateIncome,
			incomeTitle,
			paymentNo,
		
			(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=`paymentMethod` LIMIT 1) AS paymentMethod,
			(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=`bankId` LIMIT 1) AS bankName,
			accNameAndChequeNo,
			totalAmount,
			paymentDate
		";
		
		$sql.=$dbp->caseStatusShowImage("st_income.status");
		$sql.=" FROM st_income ";
		
		$from_date =(empty($search['start_date']))? '1': " paymentDate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " paymentDate <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " totalAmount LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['cateIncome'])){
			$where.= " AND cateIncome = ".$search['cateIncome'];
		}
		if(!empty($search['branch_id'])){
			$where.= " AND projectId = ".$search['branch_id'];
		}
		
        $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}

		
	function addIncome($data){
		
		$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$array = array(
				'projectId'		=>$data['branch_id'],
				'cateIncome'	=>$data['cateIncome'],
				'incomeTitle'	=>$data['incomeTitle'],
				'paymentNo'		=>$data['paymentNo'],
				'paymentDate'	=>$data['paymentDate'],
				'totalAmount'	=>$data['totalAmount'],
				'paymentMethod' =>$data['paymentMethod'],
				'bankId'		=>$data['bankId'],
				'accNameAndChequeNo' =>$data['accNameAndChequeNo'],
				'note'			=>$data['note'],
				'userId'		=>$this->getUserId(),
				'createDate'	=>date('Y-m-d H:i:s'), 
			);
			$this->insert($array);
    	
    		$db->commit();
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	//	Application_Form_FrmMessage::Sucessfull("INSERT_FAIL","/po/income/add",2);
    	}
 	} 	 
	function updateIncome($data){
		$arr = array(
				'branch_id'		=>$data['branch_id'],
				'student_id'	=>$data['studentId'],
				'optionType'	=>$data['option_type'],
				'bank_id'		=>$data['bank_name'],
				'title'			=>$data['title'],
				'cate_income'	=>$data['cate_income'],
				'total_amount'	=>$data['total_income'],
				'invoice'		=>$data['invoice'],
				'payment_method'=>$data['payment_method'],
				'cheqe_no'		=>$data['cheqe_no'],
				'description'	=>$data['note'],
				'date'			=>$data['date'],
				'status'		=>$data['status'],
				'user_id'		=>$this->getUserId(),
			);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
	
	function getIncomeById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_income WHERE id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllExpenseReport($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		$sql=" SELECT id,
		(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
		account_id,
		invoice,
		total_amount,disc,date,status FROM $this->_name ";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " account_id LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}

	function getReceiptNumber($branch_id,$type){  // $type==1 => select from rms_income , $type==2 => select from rms_expense
		$db = $this->getAdapter();
		if($type==1){
			$table = 'ln_income';
		}else{
			$table = 'ln_expense';
		}
		$sql="select count(id) from $table where branch_id = $branch_id limit 1 ";
		$id = $db->fetchOne($sql);
		$id = $id + 1;
		$length = strlen($id) + 1;
		$pre = '';
		for($i=$length;$i<=6;$i++){
			$pre.='0';
		}
		return $pre.$id;
	}
	function getInvoiceNo(){
		$db = $this->getAdapter();
		$sql = " select count(id) from ln_income ";
		$amount = $db->fetchOne($sql);
	}

	
	function getPaymentMethod($type){ // $type = rms_view type
		$_db  = new Application_Model_DbTable_DbGlobal();
		$lang = $_db->currentlang();
		if($lang==1){// khmer
			$label = "name_kh";
		}else{ // English
			$label = "name_en";
		}
		
		$db=$this->getAdapter();
		$sql="SELECT key_code as id,$label as name FROM rms_view WHERE `type`=$type AND `status`=1 ";
		return $db->fetchAll($sql);
	}
	
	function getCateIncome(){ // $type = rms_view type
		$db=$this->getAdapter();
		$sql="SELECT id,category_name as name FROM rms_cate_income_expense WHERE status=1 AND parent=1 and category_name!='' ";
		return $db->fetchAll($sql);
	}
	
	function addNewCateIncome($data){
		$this->_name="rms_cate_income_expense";
		$array = array(
				'category_name'	=>$data['cate_title'],
				'parent'		=>$data['parent'],
				'account_code'	=>$data['acc_code'],
				'create_date'	=>date('Y-m-d'),
				'user_id'		=>$this->getUserId(),
				);
		return $this->insert($array);
	}
}
