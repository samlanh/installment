<?php

class Application_Model_DbTable_DbGlobal extends Zend_Db_Table_Abstract
{
	public function setName($name){
		$this->_name=$name;
	}
	public static function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	public function getUserInfo(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$level = $session_user->level;
		$location_id = $session_user->branch_id;
		$info = array("user_name"=>$userName,"user_id"=>$GetUserId,"level"=>$level,"branch_id"=>$location_id);
		return $info;
	}
	function currentlang(){
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = empty($session_lang->lang_id)?1:$session_lang->lang_id;
		return $lang;
	}
	public function getLaguage(){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `ln_language` AS l WHERE l.`status`=1 ORDER BY l.ordering ASC";
		return $db->fetchAll($sql);
	}
	function getUserIP(){ // get current ip
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
		if(filter_var($client, FILTER_VALIDATE_IP))
		{
			$ip = $client;
		}
		elseif(filter_var($forward, FILTER_VALIDATE_IP))
		{
			$ip = $forward;
		}
		else
		{
			$ip = $remote;
		}
		return $ip;
	}
	function addActivityUser($_data){
		try{
			$id = $this->getUserId();
			$row = $this->getUserInfo($id);
			$ipaddress = $this->getUserIP();
			$_arr=array(
					'user_id'	  => $id,
					'branch_id'	      => $row['branch_id'],
					'user_name'	      => $row['user_name'],
					'date_time'  => date("Y-m-d H:i:s"),
					'description'=> $_data['description'],
					'activityold'=> empty($_data['activityold'])?"":$_data['activityold'],
					'after_edit_info'=> empty($_data['after_edit_info'])?"":$_data['after_edit_info'],
					'ipaddress'=> empty($ipaddress)?"":$ipaddress,
			);
			$this->_name="rns_user_activity";
			$this->insert($_arr);
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public  function caseStatusShowImage($status="status"){
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
		$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
		$string=", CASE
		WHEN  $status = 1 THEN '$imgtick'
		WHEN  $status = 0 THEN '$imgnone'
		END AS status ";
		return $string;
	}
	function  getAllBranchByUser(){//USAGE FOR ALL BRANCH (TRANFER)
		$db = $this->getAdapter();
		$sql = 'select br_id as id,project_name as name FROM ln_project where status=1 and project_name!="" ORDER BY br_id DESC ';
		return $db->fetchAll($sql);
	}
	function  getAllBranchInfoByID($id){
		$db = $this->getAdapter();
		$sql = "SELECT *,project_name AS branch_name,project_name AS footer_title FROM ln_project where 1 and project_name!='' AND br_id = $id ORDER BY br_id DESC ";
		$row = $db->fetchRow($sql);
		
		$footer_reciept_type=FOOTER_RECEIPT_OPT;
		if ($footer_reciept_type!=2){
			$key = new Application_Model_DbTable_DbKeycode();
			$data=$key->getKeyCodeMiniInv(TRUE);
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$row['footer_title'] = $tr->translate("BRAND_FOOTER_TITLE");
			$row['office_website'] = $data["website"];
			$row['office_email'] = $data["email_client"];
			$row['office_tel'] = $data["tel-client"];
			$row['office_address'] = $data["footer_branch"];
		}
		$phblicpart = PUBLIC_PATH;
		$baseURl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$photo = $baseURl.'/images/logo.png';
		if (!empty($row['logo'])){
			if (file_exists($phblicpart."/images/projects/".$row['logo'])){
				$photo = $baseURl.'/images/projects/'.$row['logo'];
			}
		}
		$row['url_logo'] = $photo;
			
		return $row;
	}
	public function getReceiptnumber($branch_id=1){
		$this->_name='ln_client_receipt_money';
		$db = $this->getAdapter();
		$sql=" SELECT id  FROM $this->_name WHERE branch_id = $branch_id  ORDER BY id DESC LIMIT 1 ";
		$pre = "";
		$pre = $this->getPrefixCode($branch_id)."-P";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	public static function GlobalgetUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllCustomer(){
		return array();
	}

	public function getAccessPermission($branch_str='branch_id'){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$branch_list = $session_user->branch_list;
		$result="";
		if(!empty($branch_list)){
			$level = $session_user->level;
			if($level==1){
				$result.= "";
			}
			else{
				$result.= " AND $branch_str IN ($branch_list)";
			}
		}
		return $result;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	function getCurrentDatePayment($id){
		$db = $this->getAdapter();
		$sql="SELECT c.`date_input` FROM `ln_client_receipt_money` AS c WHERE c.`id`=$id ";
		return $db->fetchOne($sql);
	}
	function getLastDatePayment($id){
		$db = $this->getAdapter();
		$sql="SELECT crm.`date_input` FROM `ln_client_receipt_money` AS crm,`ln_client_receipt_money_detail` AS crmd WHERE crm.`id`!=$id AND crm.`id`=(SELECT crl.`crm_id` FROM `ln_client_receipt_money_detail` AS crl WHERE crl.`crm_id`=crm.`id` AND crl.`loan_number`=(SELECT c.loan_number FROM `ln_client_receipt_money_detail` AS c WHERE c.`crm_id`=crmd.id AND c.`crm_id`=$id LIMIT 1) LIMIT 1)  ORDER BY crm.`date_input` DESC LIMIT 1 ";
		return $db->fetchOne($sql);
	}
	public function getSaleNumberByBranch(){
		$db = $this->getAdapter();
		$sql="select id,
			sale_number as name
			from `ln_sale` where status=1 and is_completed=0 ";
		return $db->fetchAll($sql);
	}
	public function getGlobalDb($sql)
  	{
  		$db=$this->getAdapter();
  		$row=$db->fetchAll($sql);  		
  		if(!$row) return NULL;
  		return $row;
  	}
  	public function getGlobalDbRow($sql)
  	{
  		$db=$this->getAdapter();  		
  		$row=$db->fetchRow($sql);
  		if(!$row) return NULL;
  		return $row;
  	}
  	public static function getActionAccess($action)
    {
    	$arr=explode('-', $action);
    	return $arr[0];    	
    }     
    public function isRecordExist($conditions,$tbl_name){
		$db=$this->getAdapter();		
		$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions." LIMIT 1"; 
		$row= count($db->fetchRow($sql));
		if(!$row) return NULL;
		return $row;	
    }
    /*for select 1 record by id of earch table by using params*/
    public function GetRecordByID($conditions,$tbl_name){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM ".$tbl_name." WHERE ".$conditions." LIMIT 1";
    	$row = $this->fetchRow($sql);
    	return $row;
    	$row= $db->fetchRow($sql);
    	return $row;
    }
    /**
     * insert record to table $tbl_name
     * @param array $data
     * @param string $tbl_name
     */
    public function addRecord($data,$tbl_name){
    	$this->setName($tbl_name);
    	return $this->insert($data);
    }
    public function updateRecord($data,$id,$tbl_name){
    	$this->setName($tbl_name);
    	$where=$this->getAdapter()->quoteInto('id=?',$id);
    	$this->update($data,$where);    	
    }   
    public function DeleteRecord($tbl_name,$id){
    	$db = $this->getAdapter();
		$sql = "UPDATE ".$tbl_name." SET status=0 WHERE id=".$id;
		return $db->query($sql);
    } 
     public function DeleteData($tbl_name,$where){
    	$db = $this->getAdapter();
		$sql = "DELETE FROM ".$tbl_name.$where;
		return $db->query($sql);
    } 
    public function getDayInkhmerBystr($str){
    	
    	$rs=array(
    			'Mon'=>'ច័ន្ទ',
    			'Tue'=>'អង្គារ',
    			'Wed'=>'ពុធ',
    			'Thu'=>"ព្រហ",
    			'Fri'=>"សុក្រ",
    			'Sat'=>"សៅរី",
    			'Sun'=>"អាទិត្យ");
    	if($str==null){
    		return $rs;
    	}else{
    	return $rs[$str];
    	}
    
    }
    public function convertStringToDate($date, $format = "Y-m-d H:i:s")
    {
    	if(empty($date)) return NULL;
    	$time = strtotime($date);
    	return date($format, $time);
    }  
    function getNumberInkhmer($number){
    	$khmernumber = array("០","១","២","៣","៤","៥","៦","៧","៨","៩");
    	$spp = str_split($number);
    	$num="";
    	foreach ($spp as $ss){
    		if (!empty($khmernumber[$ss])){
    			$num.=$khmernumber[$ss];
    		}else{
    			$num.=$ss;
    		}
    	}
    	return $num;
    } 
    
    public static function getResultWarning(){
          return array('err'=>1,'msg'=>'មិន​ទាន់​មាន​ទន្និន័យ​នូវ​ឡើយ​ទេ!');	
    }
   /*@author Mok Channy
    * for use session navigetor 
    * */
//    public static function SessionNavigetor($name_space,$array=null){
//    	$session_name = new Zend_Session_Namespace($name_space);
//    	return $session_name;   	
//    }
   public function getAllProvince(){
   	$this->_name='ln_province';
   	$currentLang = $this->currentlang();
   	$title="province_en_name";
   	if ($currentLang==1){
   		$title="province_kh_name";
   	}
   	$sql = " SELECT province_id,$title AS province_en_name FROM $this->_name WHERE status=1 AND province_en_name!='' ORDER BY province_id DESC";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   public function getAllDistrict(){
   	$this->_name='ln_district';
   	
   	$currentLang = $this->currentlang();
   	$title="district_name";
   	if ($currentLang==1){
   		$title="district_namekh";
   	}
   	
   	$sql = " SELECT dis_id,pro_id,$title AS district_name FROM $this->_name WHERE status=1 AND district_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   public function getAllDistricts(){
   	$this->_name='ln_district';
   	
   	$currentLang = $this->currentlang();
   	$title="district_name";
   	if ($currentLang==1){
   		$title="district_namekh";
   	}
   	
   	$sql = " SELECT dis_id AS id,pro_id,$title AS name FROM $this->_name WHERE status=1 AND district_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
   public function getCommune(){
   	$this->_name='ln_commune';
   	$currentLang = $this->currentlang();
   	$title="commune_name";
   	if ($currentLang==1){
   		$title="commune_namekh";
   	}
   	$sql = " SELECT com_id,com_id AS id,commune_name,$title AS name,district_id FROM $this->_name WHERE status=1 AND commune_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
//    public function getCommuneByDistrict($district_id){
// 	   	$this->_name='ln_commune';
// 	   	$sql = " SELECT com_id,com_id AS id,commune_name,CONCAT(commune_name,'-',commune_namekh) AS name,district_id FROM $this->_name WHERE status=1 AND 
// 	   	commune_name!='' AND district_id=$district_id ORDER BY commune_name ASC";
// 	   	$db = $this->getAdapter();
// 	   	return $db->fetchAll($sql);
//    }
   
   public function getVillage(){
   	$this->_name='ln_village';
   	$currentLang = $this->currentlang();
   	$title="village_name";
   	if ($currentLang==1){
   		$title="village_namekh";
   	}
   	$sql = " SELECT vill_id,vill_id AS id,village_name,$title AS name,commune_id FROM $this->_name WHERE status=1 AND village_name!='' ";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql);
   }
  
   public function getAllCOName($data=null,$parent = 0,$spacing = '', $cate_tree_array = ''){
	   	$this->_name='ln_staff';
	   	$sql = " SELECT co_id AS id, 
	   					co_khname AS name ,
	   					co_id ,
	   					co_firstname,
	   					co_khname,co_code,
						parent_id AS parent
	   	 FROM 
	   	        ln_staff WHERE status=1 AND co_khname!='' AND `position_id`=1 ";
	   	$sql.=" AND parent_id=$parent";
	   	if (!empty($data['branch_id'])){
	   		$sql.=" AND branch_id=".$data['branch_id'];
	   	}
	   	$db = $this->getAdapter();
	   	$rows =  $db->fetchAll($sql);
	   
	   	if (!is_array($cate_tree_array))
	   		$cate_tree_array = array();
	   	if (count($rows) > 0) {
	   		foreach ($rows as $row){
	   			$cate_tree_array[] = array("id" => $row['id'],"parent" => $row['parent'], "name" => $spacing . $row['name'], "co_id" => $row['co_id'], "co_firstname" =>$row['co_firstname'], "co_khname" =>$row['co_khname'], "co_code" =>$row['co_code']);
	   			$cate_tree_array = $this->getAllCOName($data,$row['id'], $spacing . ' - ', $cate_tree_array);
	   		}
	   	}
	   	$rows = $cate_tree_array;
	   	return $rows;
   }
   public function getAllCoNameOnly(){
   	$db= $this->getAdapter();
   	$sql = " SELECT co_id AS id, co_khname AS name
   	  FROM ln_staff WHERE STATUS=1 AND co_khname!='' AND `position_id`=1 ";
   	return $db->fetchAll($sql);
   }
  
   public function getLoanNumber($data=array('branch_id'=>1,'is_group'=>0)){
   	$this->_name='ln_sale';
   	$db = $this->getAdapter();
   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE branch_id=".$data['branch_id']." ";
		if (SET_SALENO_COUNT==1){
			$sql.=" AND is_reschedule=1 ";
		}
		$sql.=" LIMIT 1 ";
		$pre = $this->getPrefixCode($data['branch_id']);
	   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	for($i = $acc_no;$i<4;$i++){
   		$pre.='0';
   	}
   	$saleNo =$pre.$new_acc_no;
   	if (CONTRACT_NO_SETING==1){
   		$dbLand = new Project_Model_DbTable_DbLand();
   		$landId = empty($data['land_code'])?0:$data['land_code'];
   		$property = $dbLand->getClientById($landId);
   		$streetCode = empty($property['street_code'])?"":$property['street_code'];
   		$landAddress = empty($property['land_address'])?"":$property['land_address'];
   		$saleNo =$pre.$streetCode.$landAddress;
   	}
   	return $saleNo;
   }
   function getPrefixCode($branch_id){
   	$db  = $this->getAdapter();
   	$sql = " SELECT prefix FROM `ln_project` WHERE br_id = $branch_id  LIMIT 1";
   	return $db->fetchOne($sql);
   }
   public function getReceiptByBranch($data=array('branch_id'=>1,'is_group'=>0)){
   	$this->_name='ln_client_receipt_money';
   	$db = $this->getAdapter();
   	$receipt_type_count=RECEIPT_TYPE_COUNT;
   	
   	$lenghtReceipt=6;
   	$oldNumber=0;
   	
   	if($receipt_type_count==0){
	   	//phnom penh thmey
	   	$pre='N1:'; //phnom penh thmey
	   	if ($data['branch_id']>=5){
	   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE branch_id =".$data['branch_id']." LIMIT 1 ";
	   	}else if ($data['branch_id']>2){
	   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE branch_id >2 AND  branch_id < 5 LIMIT 1 ";
	   	}else{
	   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE branch_id <=2 LIMIT 1 ";
	   	}
   	}elseif($receipt_type_count==1){
   		//For General
//    	$sql=" SELECT COUNT(id) FROM $this->_name WHERE 1 ";
   		$pre='№ ';
   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE branch_id =".$data['branch_id'];
   	}elseif($receipt_type_count==2){
   		$pre='№ ';
   		$currentDate = date("Y-m-d");
   		$dateSetting = "2021-01-01";
   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE 1 ";
   		if($currentDate>=date("Y-m-d",strtotime($dateSetting))){
   			$lenghtReceipt=6;
   			$pre=date("y")."-";
   				
   			$year=date("Y");
   			$startDate=date("Y-m-d",strtotime($year."-01-01"));
   			$endDate=date("Y-m-d",strtotime($year."-12-31"));
   			$from_date =(empty($startDate))? '1': " date_input >= '".$startDate." 00:00:00'";
   			$to_date = (empty($endDate))? '1': " date_input <= '".$endDate." 23:59:59'";
   			$sql.= " AND ".$from_date." AND ".$to_date;
   		}
   		$sql.=" AND branch_id = ".$data['branch_id'];
   		$sql.=" LIMIT 1 ";
   	}elseif($receipt_type_count==3){//svr
   		$pre='№ ';
   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE 1 ";
   		$currentDate = date("Y-m-d");
   		$dateSetting = "2021-01-18";
   		if($currentDate>=date("Y-m-d",strtotime($dateSetting))){
   			if ($data['branch_id']>=3){
   				$sql.=" AND branch_id =".$data['branch_id']." ";
   			}else{
   				$sql.= " AND branch_id !=3 ";
   				$oldNumber=358;
   			}
   		}
   		$sql.=" LIMIT 1 ";
   	}elseif($receipt_type_count==4){//count all branch
   		$pre='№ ';
   		$sql=" SELECT COUNT(id) FROM $this->_name WHERE 1 LIMIT 1";
   	}
   	
   	
   
	$receiptForCompany = 1; //1=general,2=fiveStar,3=for Svayrieng
// 	if($receiptForCompany==2){
		
		
// 	}else if ($receiptForCompany==3){
		
// 	}
	
   	
   	$acc_no = $db->fetchOne($sql);
   	$new_acc_no= (int)$acc_no+$oldNumber+1;
   	$acc_no= strlen((int)$acc_no+$oldNumber+1);
  	for($i = $acc_no;$i<$lenghtReceipt;$i++){//phnom penh thmey
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   public function getStaffNumberByBranch($branch_id){
   	$this->_name='ln_staff';
   	$db = $this->getAdapter();
   		$sql = "SELECT COUNT(co_id)FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
   		$pre = $this->getPrefixCode($branch_id)."";
   	$acc_no = $db->fetchOne($sql);
   
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   public function getSupplierCodeByBranch($branch_id){
   	$this->_name='ln_supplier';
   	$db = $this->getAdapter();
   	$sql = "SELECT COUNT(id)FROM $this->_name WHERE branch_id=".$branch_id." LIMIT 1 ";
   	$pre = $this->getPrefixCode($branch_id)."";
   	$acc_no = $db->fetchOne($sql);
   	 
   	$new_acc_no= (int)$acc_no+1;
   	$acc_no= strlen((int)$acc_no+1);
   	 
   	for($i = $acc_no;$i<5;$i++){
   		$pre.='0';
   	}
   	return $pre.$new_acc_no;
   }
   public function getClientByType($type=null,$client_id=null ,$row=null){
   $this->_name='ln_client';
   $session_lang=new Zend_Session_Namespace('lang');
   $lang_id=$session_lang->lang_id;
   $prvoince_str='province_kh_name';
   $district_str='district_namekh';
   $commune_str ='commune_namekh';
   $village_str='village_namekh';
   if($lang_id!=1){
   	$prvoince_str='province_en_name';
   	$district_str='district_name';
   	$commune_str ='commune_name';
   	$village_str='village_name';
   }
   $where='';
   	$sql = " SELECT client_id,name_en,client_number,remark,
   				(SELECT `ln_village`.$village_str FROM `ln_village` WHERE (`ln_village`.`vill_id` = `ln_client`.`village_id`)) AS `village_name`,
				(SELECT `c`.$commune_str FROM `ln_commune` `c` WHERE (`c`.`com_id` = `ln_client`.`com_id`) LIMIT 1) AS `commune_name`,
				(SELECT `d`.$district_str FROM `ln_district` `d` WHERE (`d`.`dis_id` = `ln_client`.`dis_id`) LIMIT 1) AS `district_name`,
				(SELECT $prvoince_str FROM `ln_province` WHERE province_id= ln_client.pro_id  LIMIT 1) AS province_en_name

   	FROM $this->_name WHERE status=1  ";
   	$db = $this->getAdapter();
   	if($row!=null){
   		if($client_id!=null){ $where.=" AND client_id  =".$client_id ." LIMIT 1";}
   		return $db->fetchRow($sql.$where);
   	}
   	return $db->fetchAll($sql.$where);
   }
   
  
  public function getAllBranchName($branch_id=null,$opt=null,$moreCondiction=array()){
	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$db = $this->getAdapter();
  	$sql= " SELECT br_id,project_name,
  	project_type,br_address,branch_code,branch_tel,displayby
  	FROM `ln_project` WHERE project_name !='' AND status=1 ";
	$moreCondiction['showAll'] = empty($moreCondiction['showAll'])?null:$moreCondiction['showAll'];
	
	if(empty($moreCondiction['showAll'])){
		$sql.= $this->getAccessPermission('br_id');
  	}
  	if($branch_id!=null){
  		$sql.=" AND br_id=$branch_id LIMIT 1";
  	}
  	$sql.=" ORDER BY br_id DESC";
  	$row = $db->fetchAll($sql);
  	if($opt==null){
  		return $row;
  	}else{
  		$options=array(0=> $tr->translate("SELECT_PROJECT"));
  		if(!empty($row)) foreach($row as $read) $options[$read['br_id']]=$read['project_name'];
  		return $options;
  	}
  }
  function countDaysByDate($start,$end){
  	$first_date = strtotime($start);
  	$second_date = strtotime($end);
  	$offset = $second_date-$first_date;
  	return floor($offset/60/60/24);
  
  }

 
  public function getClientByMemberIdGlobal($id){
  	$sql="SELECT 
		  `s`.`branch_id`       AS `branch_id`,
		  `s`.`client_id`       AS `client_id`,
		  `s`.`house_id`        AS `house_id`,
		  `s`.`price_before`    AS `price_before`,
		  `s`.`sale_number`      AS `sale_number`,
		  `s`.`price_sold`      AS `price_sold`,
		   s.lastpayment_amount,
		  `s`.`discount_amount` AS `discount_amount`,
		  s.land_price ,
		  s.discount_percent,
		  s.agreement_date,
		  s.full_commission,
		  s.commission_amt,
		  s.commission_times,
		  `s`.`admin_fee`       AS `admin_fee`,
		  `s`.`other_fee`       AS `other_fee`,
		  `s`.`paid_amount`     AS `paid_amount`,
		  `s`.`balance`         AS `balance`,
		  `s`.`create_date`     AS `create_date`,
		  `s`.`buy_date`        AS `buy_date`,
		  `s`.`startcal_date`   AS `startcal_date`,
		  `s`.`first_payment`   AS `first_payment`,
		  `s`.`validate_date`   AS `validate_date`,
		  `s`.`end_line`        AS `end_line`,
		  `s`.`interest_rate`   AS `interest_rate`,
		  `s`.`total_duration`  AS `total_duration`,
		  `s`.`payment_id`      AS `payment_id`,
		  `s`.`staff_id`        AS `staff_id`,
		  `s`.`comission`       AS `comission`,
		  `s`.`receipt_no`      AS `receipt_no`,
		  s.total_installamount,
		  s.for_installamount,
		    s.interest_policy,
		    (SELECT st.title FROM rms_interestsetting as st WHERE st.id=s.interest_policy AND st.type=1 LIMIT 1 ) AS interest_policy_title,
		 (SELECT project_name FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS branch_name,
		  (SELECT p_manager_namekh FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS project_manager_namekh,
		  (SELECT w_manager_namekh FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS w_manager_namekh,
		  (SELECT logo FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS project_logo,
  		(SELECT client_number FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS client_number,
  		(SELECT name_kh FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS client_name_kh,
  		(SELECT hname_kh FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS hname_kh,
  		(SELECT name_en FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS client_name_en,
  		(SELECT phone FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS tel,
  		(SELECT CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,''))  FROM `rms_users` WHERE id = s.user_id LIMIT 1) AS user_name,
	  	  `p`.`land_code`       AS `land_code`,
		  `p`.`land_address`    AS `land_address`,
		  `p`.`land_size`       AS `land_size`,
		  `p`.`street`           AS `stree`,
	  (SELECT
	     `ln_properties_type`.`type_nameen`
	   FROM `ln_properties_type`
	   WHERE (`ln_properties_type`.`id` = `p`.`property_type`)
	   LIMIT 1) AS `propertype`
  		FROM 
  	   `ln_sale` AS s,
  	   `ln_properties` AS p
  	 WHERE `p`.`id` = `s`.`house_id` AND s.id=$id ";
  	
  	$sql.=$this->getAccessPermission("`s`.`branch_id`");
  	$sql.=" LIMIT 1 ";
  	
  	$db=$this->getAdapter();
  	return $db->fetchRow($sql);
  }
  function getAllUserGlobal(){
  	$db=$this->getAdapter();  	 
  	$sql="SELECT id,first_name AS by_user,first_name AS name FROM `rms_users` WHERE active=1 ORDER BY id DESC ";
  	return $db->fetchAll($sql);
  }
 
 
 
  public  function getclientdtype(){
  	$db = $this->getAdapter();
  	$string = "name_kh";
  	if($this->currentlang()==2){
  		$string = "name_en";
  	}
  	$sql="SELECT key_code as id, $string AS name ,displayby FROM `ln_view` WHERE status =1 AND type=23 AND (name_kh!='' OR name_en!='')";
  	$rows = $db->fetchAll($sql);
  	return $rows;
  }
  public function getVewOptoinTypeByType($type=null,$option = null,$limit =null,$first_option =null){
  	$db = $this->getAdapter();
  	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
  	$string = "name_kh";
  	if($this->currentlang()==2 OR $this->currentlang()==3){
  		$string = "name_en";
  	}
	if(RECEIPT_TYPE==5){
		if($type==2){
			$string = " CONCAT(name_kh,' / ',name_en) ";
		}
	}
  	
  	$sql="SELECT id,key_code, $string AS name_en ,displayby FROM `ln_view` WHERE status =1 AND name_en!='' ";//just concate
  	if($type!=null){
  		$sql.=" AND type = $type ";
  	}
  	if($limit!=null){
  		$sql.=" LIMIT $limit ";
  	}
  	$rows = $db->fetchAll($sql);
  	if($option!=null){
  		$options=array();
  		if($first_option==null){//if don't want to get first select
  			$options=array(''=>$tr->translate("SELECT_TYPE"),-1=>"Add New",);
  		}
  		if(!empty($rows))foreach($rows AS $row){
  			$options[$row['key_code']]=$row['name_en'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
  		}
  		return $options;
  	}else{
  		return $rows;
  	}
  }
  public function getAllCategoryIncomeExpens($type,$parent = 0, $spacing = '', $cate_tree_array = ''){
  	$db=$this->getAdapter();
  	if (!is_array($cate_tree_array))
  		$cate_tree_array = array();
  	
  	$string = "name_kh";
  	if($this->currentlang()==2){
  		$string = "name_en";
  	}
  	$sql = " SELECT key_code AS id,$string as name FROM ln_view where status=1 AND type=$type AND $string!='' AND `parent_id` = $parent ";
  	$query = $db->fetchAll($sql);
  	$rowCount = count($query);
  	$id='';
  	if ($rowCount > 0) {
  		foreach ($query as $row){
  			$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
  			$cate_tree_array = $this->getAllCategoryIncomeExpens($type,$row['id'], $spacing . ' - ', $cate_tree_array);
  		}
  	}
  	return $cate_tree_array;
  }
  public function getAllLandInfo($branch_id=null,$option=null,$action=null,$propertytype=null,$foreditsale=null){
  	   $db = $this->getAdapter();
  	   $tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	   $sql="SELECT `id`,CONCAT(COALESCE(`land_address`,''),',',COALESCE(street,'')) AS name FROM `ln_properties` WHERE `land_address`!='' ";
  	   if (!empty($foreditsale)){
  	   		$sql.=" AND  status Not IN (-2,-1,0) ";
  	   }else{
  	   		$sql.=" AND  status Not IN (-1,0) ";
  	   }
  	   $key = new Application_Model_DbTable_DbKeycode();
  	   $_setting = $key->getKeyCodeMiniInv(TRUE);
  	   $is_show = empty($_setting['showhouseinfo'])?0:$_setting['showhouseinfo'];
  	   if($is_show==0){
	  	   	if($action=="changehouse" OR $action !=""){
	  	   		
	  	   	}else{
	  	   		$sql.=" AND `is_lock`=0 ";
	  	   	}
  	   }else{
	  	   	if($action==null){
	  	   		$sql.=" AND `is_lock`=0  ";
	  	   	}
  	   }
  	  
  	   if($action==null){
	  	   	if($is_show==0){
	  	   	}else{
	  	   		$sql.=" AND `is_lock`=0  ";
	  	   	}
  	   }
  	   if($branch_id!=null){
  	   	$sql.=" AND `branch_id`=$branch_id ";
  	   }
  	   if (!empty($propertytype)){
  	   	$sql.=" AND `property_type`=$propertytype ";
  	   }
  	   $sql.=" ORDER BY id DESC";
  	    $rows = $db->fetchAll($sql);
  	    if($option!=null){ return $rows;}
  		$options=array(''=>$tr->translate("SELECT_PROPERTY"));
  		if(!empty($rows))foreach($rows AS $row){
  			$options[$row['id']]=$row['name'];
  		}
  		return $options;
  }
  
  
  public function getNextPayment($str_next,$next_payment,$amount_amount,$holiday_status=null,$first_payment=null){//code make slow
	 $default_day = Date("d",strtotime($first_payment));
	 $prev_month=$next_payment;
	 if($str_next=='+1 month'){
	 	if($default_day>28){
		 	if($default_day==31){
		 		$date= new DateTime($next_payment);
		 		$date->modify('+1 day');
		 		$next_payment = $date->format("Y-m-t");
		 		return $next_payment;
		 	}elseif($default_day==30 OR $default_day==29){
		 		
		 		$date= new DateTime($prev_month);
		 		$pre_month = $date->format("m");
		 		$prev_month = $pre_month;
		 		
		 		if($pre_month=='01'){
		 			$date= new DateTime($next_payment);
		 			$next_payment = $date->format("Y-02-20");
		 			$date= new DateTime($next_payment);
		 			$next_payment = $date->format("Y-m-t");
		 		}//for Feb
		 		else{
		 			$date= new DateTime($next_payment);
		 			$date->modify('+1 month');
		 			$next_payment = $date->format("Y-m-$default_day");
		 		}
		 	}
	 	}else{
	 		if($str_next!='+1 month'){
	 			$default_day='d';
	 		}
	 		$date= new DateTime($next_payment);
	 		$date->modify('+1 month');
	 		$next_payment = $date->format("Y-m-$default_day");
	 	}
 }
 return $next_payment;
 
 for($i=0;$i<$amount_amount;$i++){
		if($default_day>28){
			$next_payment = date("Y-m-d", strtotime("$next_payment $str_next"));
		  		 if($str_next!='+1 month'){
				   	if($default_day==31){
		   			$next_payment = date("Y-m-t", strtotime("$next_payment $str_next"));
				   	return $next_payment;
				   	break;
				 }
				$default_day='d';
				$next_payment = date("Y-m-$default_day", strtotime("$next_payment $str_next"));
			}else{
				$next_payment = $this->checkEndOfMonth($default_day,$next_payment , $str_next);
			}
		}else{
			if($str_next!='+1 month'){
				$default_day='d';
			}
	  		$next_payment = date("Y-m-$default_day", strtotime("$next_payment $str_next"));
		}
  	}
  	return $next_payment;
  	if($holiday_status==3){
  		return $next_payment;//if normal day
  	}else{//check for sat and sunday
  		while($next_payment!=$this->checkHolidayExist($next_payment,$holiday_status)){
  			$next_payment = $this->checkHolidayExist($next_payment,$holiday_status);
  		}
//   		echo $next_payment;exit();
  		return $next_payment;
  	}
  	
  }
  
	  
  function checkFirstHoliday($next_payment,$holiday_status){
  	if($holiday_status==3){
  		return $next_payment;//if normal day
  	}else{
  		while($next_payment!=$this->checkHolidayExist($next_payment,$holiday_status)){
  			$next_payment = $this->checkHolidayExist($next_payment,$holiday_status);
  		}
  		return $next_payment;
  	}
  	
  	
  }
  function checkEndOfMonth($default_day,$next_payment,$str_next){//default = 31 ,
  	if($str_next=='+1 month'){
  		$str_next='-1 month';
  	}else if($str_next=='+1 week'){
  		$str_next='-1 week';
  	}else{
  		$str_next='-1 day';
  	}
  	
  	$next_payment = date("Y-m-d", strtotime("$next_payment $str_next"));
  	$m = (integer) date('m',strtotime($next_payment));
  	$end_date   = date('Y-m-d',mktime(1,1,1,++$m,0,date('Y',strtotime($next_payment))));
  	return $end_date;
  	
  }
  public function getNextDateById($pay_term,$amount_next_day){
  	if($pay_term==3){
  		$str_next = '+1 month';
  	}elseif($pay_term==2){
  		$str_next = '+1 week';
  	}else{
  		$str_next = '+1 day';
  	}
  	return $str_next;
  }
  public function checkHolidayExist($date_next,$holiday_option){//for check collect payment in holiday or not
  	$db = $this->getAdapter();
  	$sql="SELECT start_date FROM `ln_holiday` WHERE start_date='".$date_next."'";
  	$rs =  $db->fetchRow($sql);
  	$db = new Setting_Model_DbTable_DbLabel();
  	$array = $db->getAllSystemSetting();
  	if($rs){
  		$d = new DateTime($rs['start_date']);
  		if($holiday_option==1){
  			$str_option = 'previous day';
  		}elseif($holiday_option==2){
  			$str_option = 'next day';
  		}else{
  			return  $d->format( 'Y-m-d' );
  		}
  		$d->modify($str_option); //here check for holiday option //can next day,next week,next month
  		$date_next =  $d->format( 'Y-m-d');
  		
  		
  		$d = new DateTime($date_next);
  		$day_work = date("D",strtotime($date_next));
  		if($day_work=='Sat' OR $day_work=='Sun' ){//if 
  			if(($day_work=='Sat' AND $array['work_saturday']==1) OR ($day_work=='Sun' AND $array['work_sunday']==1)){//sat working
  				return $date_next;
  			}else if($day_work=='Sat' AND $array['work_saturday']==0){//sat not working
  				if($holiday_option==1){//after 
  					$str_next = '+2 day';//why
  				}else if($holiday_option==2){
  					$str_next = '+1 day';
  				
  				}else{//before
  					$str_next = '-1 day';//thu
  				}
  				$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  				$date_next =  $d->format( 'Y-m-d' );
  				return $date_next;
  			}else{//sun not working continue to monday // but not check if mon day not working
  				if($holiday_option==2){//after
  					$str_next = '+1 day';
  				}else{//before
  					$str_next = '-1 day';//thu
  				}
  				$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  				$date_next =  $d->format( 'Y-m-d' );
  				return $date_next;
  			}
  		}else{
  			return $date_next;
  		}
  	}
  	else{
  		$d = new DateTime($date_next);
  		$day_work = date("D",strtotime($date_next));
  	    if($day_work=='Sat' OR $day_work=='Sun' ){
  	    	if(($day_work=='Sat' AND $array['work_saturday']==1) OR ($day_work=='Sun' AND $array['work_sunday']==1)){//sat working
  	    		return $date_next;
  	    	}else if($day_work=='Sat' AND $array['work_saturday']==0){//sat not working
  	    		$str_next = '+2 day';
  	    		$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  	    		$date_next =  $d->format( 'Y-m-d' );
  	    		return $date_next;
  	    	}else{//sun not working continue to monday // but not check if mon day not working
  	    		$str_next = '+1 day';
  	    		$d->modify($str_next); //here check for holiday option //can next day,next week,next month
  	    		$date_next =  $d->format( 'Y-m-d' );
  	    		return $date_next;
  	    	}
  	    }else{
  	    	return $date_next;
  	    }
  	}
  }
  public function CountDayByDate($start,$end){
	$date = $this->countDaysByDate($start,$end);
  	return $date;
  }
 
  public function getSystemSetting($keycode){
  	$db = $this->getAdapter();
  	$sql = "SELECT * FROM `ln_system_setting` WHERE keycode ='".$keycode."'";

  	return $db->fetchRow($sql);
  }
  
  public function getAccountBranchByOther($acc_id, $br_id ,$curr_id,$balance=null,$increase=null){
		$sql =" SELECT * FROM ln_account_branch 
		WHERE  account_id = $acc_id AND branch_id=$br_id AND currency_type = $curr_id LIMIT 1";
  	$db = $this->getAdapter();
  	$row =  $db->fetchRow($sql);
  	$increase = ($increase==1)?'+':'-'; 
	$table='ln_account_branch';
  	if(empty($row)){
  		$arr =array(
  				'account_id'=>$acc_id,
				'branch_id'=>$br_id,
  				'currency_type'=>$curr_id,
				'balance'=>$increase.$balance,
				'user_id'=>self::getUserId(),
				'date'=>date('Y-m-d'),
  				);
		$db->insert($table, $arr);
  		return $arr;
  	}else{

 		$where ='id = '.$row['id'] ;
  		$data = array(
  				'balance'=>($increase.$balance)+$row['balance']
  				);
  		$db->update($table,$data,$where);
  	}
  }
  public function getGroupCodeById($diplayby=1,$group_type,$opt=null){
  	$db = $this->getAdapter();
  	
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	
  	$sql = " CALL `stGetAllClientType`($group_type)";
  	$result = $db->fetchAll($sql);
  	$options=array(''=>$tr->translate("CHOOSE_CUSTOEMR"));
  	if($opt!=null){
		if(!empty($result))foreach($result AS $row){
				$label =$row['client_number'];	
  			$options[$row['client_id']]=$label;
		}  
  		return $options;	
  	}else{
  		return $result;
  	}
  }
  public function getLoanFundExist($loan_id){
  	$sql = "CALL `stgetLoanFundExist`($loan_id) ";
  	$db = $this->getAdapter();
  	$result = $db->fetchRow($sql);
  	if(!empty($result)){
  		return true;}
  	else{ 
  		return false;}
  }
  
  function getAllClientGroup($branch_id=null){
  	$db = $this->getAdapter();
  	$sql = " SELECT c.`client_id` AS id  ,c.`branch_id`,
  	CONCAT(c.client_number ,'-',c.`name_en`,'-',c.`name_kh`) AS name , client_number
  	FROM `ln_client` AS c WHERE c.`name_en`!='' AND c.status=1 AND c.is_group=1 " ;
  	if($branch_id!=null){
  		$sql.=" AND c.`branch_id`= $branch_id ";
  
  	}
  	$sql.=" ORDER BY id DESC";
  	return $db->fetchAll($sql);
  }
  function getAllClientGroupCode($branch_id=null){
  	$db = $this->getAdapter();
  	$sql = " SELECT c.`client_id` AS id  ,c.`branch_id`,
  	group_code AS name
  	FROM `ln_client` AS c WHERE c.`name_en`!='' AND c.status=1 AND c.is_group=1 " ;
  	if($branch_id!=null){
  		$sql.=" AND c.`branch_id`= $branch_id ";
  
  	}
  	$sql.=" ORDER BY id DESC";
  	return $db->fetchAll($sql);
  }
  function getAllClientNumber($branch_id=null){
  	$db = $this->getAdapter();
  	$sql = " SELECT c.`client_id` AS id  ,c.client_number AS name
  	FROM `ln_client` AS c WHERE c.`name_kh`!='' AND c.client_number !='' AND c.status=1  " ;
  	$sql.=" ORDER BY c.`client_id` DESC";
  	return $db->fetchAll($sql);
  }
  
  function getAllClient($branch_id=null){
  	$db = $this->getAdapter();
  	$sql=" SELECT c.`client_id` AS id  ,c.`branch_id`,
  	c.`name_kh` AS name , client_number
  	FROM `ln_client` AS c WHERE c.`name_kh`!='' AND c.status=1  " ;
  	if($branch_id!=null){
  		$sql.=" AND c.`branch_id`= $branch_id ";
  	}
  	 $sql.=" ORDER BY c.`client_id` DESC";
  	return $db->fetchAll($sql);
  }

  function getAllLoanNumber(){//type ==1 is ilPayment, type==2 is group payment
  	$db = $this->getAdapter();
  	$sql ="SELECT id,
			  CONCAT((SELECT CONCAT(name_kh,'-',name_en) FROM ln_client WHERE ln_client.client_id=ln_sale.`client_id` ),' - ',sale_number) AS sale_number
			FROM
			  ln_sale 
			WHERE `is_completed` = 0 
			  AND `is_reschedule` != 1 
  			";
  	
  	return $db->fetchAll($sql);
  }
  
  function getAllViewType($opt=null,$filter=null){
  	$db = $this->getAdapter();
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$sql ="SELECT * FROM `ln_view_type`";
  	if($filter!=null){
  		$sql.=" WHERE id=12 OR id=13";
  	}
  	$result = $db->fetchAll($sql);
  	$options=array('-1'=>$tr->translate("SELECT_TYPE"));
  	if($opt!=null){
  		if(!empty($result))foreach($result AS $row){
  			    $options[$row['id']]=$row['name'];
  		}
  		return $options;
  	}else{
  		return $result;
  	}
  	
  }

  public function getNewClientIdByBranch($branch_id=null){// by vandy get new client no by branch
  	$this->_name='ln_client';
  	$db = $this->getAdapter();
  	$sql=" SELECT count(client_id)  FROM $this->_name WHERE 1 LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$prefix="";
//   	$prefix = $this->getPrefix($branch_id);
  	$pre= "";
  	for($i = $acc_no;$i<6;$i++){
  		$pre.='0';
  	}
  	return $prefix.$pre.$new_acc_no;
  }
  public function getNewLandByBranch($branch_id){// by vandy get new client no by branch
  	$this->_name='ln_properties';
  	$db = $this->getAdapter();
  	$sql=" SELECT count(id) FROM $this->_name WHERE branch_id = $branch_id LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	 
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$prefix = $this->getPrefix($branch_id);
  	$pre= "-P";
  	for($i = $acc_no;$i<3;$i++){
  		$pre.='0';
  	}
  	return $prefix.$pre.$new_acc_no;
  }
  public function getPrefix($branch_id){// by vandy get prefix by branch
  	$db = $this->getAdapter();
  	 $sql="SELECT p.prefix FROM `ln_project` AS p WHERE p.br_id=".$branch_id;
  	return $db->fetchOne($sql);
  }
  public function getPropertyType(){
  	$db= $this->getAdapter();
  	$sql="SELECT t.`id`,t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.`status`=1";
  	$rows =  $db->fetchAll($sql);
  	
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$options=array(''=>$tr->translate("PLEASE_SELECT"),-1=>$tr->translate("ADD_NEW"),);
  	if(!empty($rows))foreach($rows AS $row){
  		$options[$row['id']]=$row['name'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
  	}
  	return $options;
  }
  public function getPropertyTypeForsearch(){
  	$db= $this->getAdapter();
  	$sql="SELECT t.`id`,t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.`status`=1";
  	$rows =  $db->fetchAll($sql);
  	
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$options=array(''=>$tr->translate("PROPERTY_TYPE"));
  	if(!empty($rows))foreach($rows AS $row){
  		$options[$row['id']]=$row['name'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
  	}
  	return $options;
  }
  public function getNewCacelCodeByBranch($branch_id){// by vandy get new client no by branch
  	$this->_name='ln_sale_cancel';
  	$db = $this->getAdapter();
  	$sql=" SELECT count(id) FROM $this->_name WHERE branch_id = $branch_id LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$prefix = $this->getPrefix($branch_id);
  	$pre= "C";
  	for($i = $acc_no;$i<6;$i++){
  		$pre.='0';
  	}
  	return $prefix.$pre.$new_acc_no;
  }
  public function getSaleNoByProject($branch_id){
  	$db = $this->getAdapter();
  	$sql="SELECT s.`id`,CONCAT((SELECT c.client_number FROM `ln_client` AS c WHERE c.client_id = s.`client_id` LIMIT 1),' (',
	s.`sale_number`,')' ) AS `name`
  	FROM `ln_sale` AS s  
 	WHERE s.`is_completed` =0 AND s.`branch_id` =".$branch_id;
  	return $db->fetchAll($sql);
  }
  function  getAllStreet(){
  	$db = $this->getAdapter();
  	$sql = 'SELECT DISTINCT street FROM `ln_properties` WHERE street!="" ORDER BY street ASC ';
  	$rows =  $db->fetchAll($sql);
  	
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$options=array(''=>$tr->translate("CHOOSE_STREET"));
  	if(!empty($rows))foreach($rows AS $row){
  		$options[$row['street']]=$row['street'];//($row['displayby']==1)?$row['name_kh']:$row['name_en'];
  	}
  	return $options;
  }
  function  getAllStreetForOpt(){
  	$db = $this->getAdapter();
  	$sql = 'SELECT DISTINCT street as name,street as id FROM `ln_properties` WHERE street!="" ORDER BY street ASC ';
  	$rows =  $db->fetchAll($sql);
  	return $rows;
  }
  function  getAllInterestratestore(){
  	$db = $this->getAdapter();
  	$sql = 'SELECT DISTINCT(interest_rate) AS name, interest_rate AS id FROM `ln_sale` WHERE interest_rate>0 ORDER BY interest_rate ASC  ';
  	$rows =  $db->fetchAll($sql);
  	return $rows;
  }
  function  getAllInterestrate(){
  	$db = $this->getAdapter();
  	$sql = 'SELECT DISTINCT(interest_rate) FROM `ln_sale` WHERE interest_rate>0 ORDER BY interest_rate ASC ';
  	$rows =  $db->fetchAll($sql);
  	$options=array(0=>0);
  	if(!empty($rows))foreach($rows AS $row){
  		$options[$row['interest_rate']]=$row['interest_rate'];
  	}
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$options[-1]=$tr->translate("ADD_NEW");
  	return $options;
  }
  function updateLateRecordSaleschedule($sale_id){
  	$db = $this->getAdapter();
  	$sql = "SELECT * FROM ln_saleschedule WHERE sale_id = $sale_id ORDER BY id DESC limit 1 ";
  	$rs = $db->fetchRow($sql);
  	if(!empty($rs)){
  		$this->_name="ln_sale";
  		$arr = array(
  				'end_line'=>$rs['date_payment']
  		);
  		$where=" id = ".$sale_id;
  		$this->update($arr, $where);
  	}
  }
 
//   public function getSalePropertyNo(){
//   	$this->_name='ln_sale_property';
//   	$db = $this->getAdapter();
//   	$sql=" SELECT COUNT(id) FROM $this->_name  LIMIT 1 ";
//   	$pre = "PS";
//   	$acc_no = $db->fetchOne($sql);
//   	$new_acc_no= (int)$acc_no+1;
//   	$acc_no= strlen((int)$acc_no+1);
//   	for($i = $acc_no;$i<5;$i++){
//   		$pre.='0';
//   	}
//   	return $pre.$new_acc_no;
//   }
//   function getAllClientname(){
//   	$db = $this->getAdapter();
// 	$this->_name='ln_client_property';
//   	$sql = " SELECT c.`client_id` AS id,
//   	CONCAT(c.name_kh ,' , ',c.`hname_kh`) AS name , client_number
//   	FROM $this->_name AS c WHERE c.`name_kh`!='' AND c.status=1" ;
//   	$sql.=" ORDER BY id DESC";
//   	return $db->fetchAll($sql);
//   }
  public function getRentPropertyNo(){
  	$this->_name='ln_rent';
  	$db = $this->getAdapter();
  	$sql=" SELECT COUNT(id) FROM $this->_name  LIMIT 1 ";
  	$pre = "RN";
  	$acc_no = $db->fetchOne($sql);
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	for($i = $acc_no;$i<5;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }
 
 
  function getAllplongissue(){
  	$db=$this->getAdapter();
  	$sql ="SELECT `s`.`id` AS `id`,
    	(SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
	    `c`.`name_kh`         AS `name_kh`,
	    `c`.`phone`         AS `phone`,
	    `p`.`land_address`    AS `land_address`,
	    `p`.`street`          AS `street`,
	    sp.issue_date,
         s.status
		FROM (`ln_sale` `s`,
			ln_issueplong AS sp,
		     `ln_client` `c`
		   JOIN `ln_properties` `p`)
		WHERE (
		s.id=sp.sale_id
		AND (`c`.`client_id` = `s`.`client_id`)
       AND (`p`.`id` = `s`.`house_id`)) AND s.is_issueplong=1 AND s.is_receivedplong=0 ";
  	
  	$dbp = new Application_Model_DbTable_DbGlobal();
  	$sql.=$dbp->getAccessPermission("s.branch_id");
  	
  	$order=" ORDER BY end_line ASC ";
  	return $db->fetchAll($sql.$order);
  }
  function getAllSupplier(){
  	$db = $this->getAdapter();
  	$sql="SELECT s.`id`,s.`name` FROM `ln_supplier` AS s WHERE s.`status`=1 AND s.`name`!=''";
  	return $db->fetchAll($sql);
  }
  function getAllKnowBy($option=1,$_add_new=null){
  	$db = $this->getAdapter();
  	$sql="SELECT id,title,title as name FROM `rms_know_by` WHERE `status`=1 AND `title`!='' ";
  	$result=$db->fetchAll($sql);
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	if($option!=null){
  		
  		$options=array(''=>$tr->translate("SELECT_TYPE"));
  		if (!empty($_add_new)){
  			$options[-1]=$tr->translate("ADD_NEW");
  		}
  		if(!empty($result))foreach($result as $rs){
  			$options[$rs['id']]=$rs['title'];
  		}
  		return $options;
  	}else{
  		if (!empty($_add_new)){
  			array_unshift($result, array('id'=>'','name' => $tr->translate("SELECT_TYPE")), array('id'=>'-1', 'name'=>$tr->translate("ADD_NEW")));
  		}
  		return $result ;
  	}
  }
  function getAllPlong(){
  	$db = $this->getAdapter();
  	$sql="SELECT DISTINCT rp.`layout_type` AS `name`,rp.`layout_type` AS `id`  FROM `ln_receiveplong` AS rp WHERE rp.`status`=1 AND rp.`layout_type` !='' ORDER BY rp.layout_type ASC";
  	return $db->fetchAll($sql);
  }
  
  public function getOptionStepPayment(){
  	$db = $this->getAdapter();
  	$rows = $this->getVewOptoinTypeByType(29);
  	$option='';
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$option='<option value="0" >'.htmlspecialchars($tr->translate("PLEASE_SELECT"), ENT_QUOTES).'</option>'; 
  	if(!empty($rows))foreach($rows as $value){
  		$option .= '<option value="'.$value['key_code'].'" >'.htmlspecialchars($value['name_en'], ENT_QUOTES).'</option>';
  	}
  	return $option;
  }
  function getAllPaidBefore($sale_id){
    $db = $this->getAdapter();
    $sql=" SELECT SUM(total_principal_permonthpaid+extra_payment) AS paid_before FROM ln_client_receipt_money 
    	WHERE sale_id = $sale_id AND status =1 LIMIT 1";
    return $db->fetchOne($sql);
  }
  
  function getAllInvestor(){
  	$db = $this->getAdapter();
  	$sql="SELECT s.`id`,s.`name` FROM `rms_investor` AS s WHERE s.`status`=1 AND s.`name`!='' ORDER BY s.`name` ASC";
  	return $db->fetchAll($sql);
  }
  function getAllBroker(){
  	$db = $this->getAdapter();
  	$sql="SELECT s.`id`,s.`name` FROM `rms_broker` AS s WHERE s.`status`=1 AND s.`name`!='' ORDER BY s.`name` ASC";
  	return $db->fetchAll($sql);
  }
  
  function testTruncate($type=0,$param=null){
//   	# truncate data from all table
//   	# $sql = "SHOW TABLES IN 1hundred_2011";
//   	# or,
//   	$connection = $this->getAdapter();
//   	$sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE 'db_borey'";
  	
//   	# use the instantiated db connection object from the init.php, to process the query
//   	$tables = $connection ->fetchAll($sql);// fetch_all($sql);
  	
//   	foreach($tables as $table)
//   	{
//   		//echo $table['TABLE_NAME'].'<br/>';
  	
//   		# truncate data from this table
//   		# $sql = "TRUNCATE TABLE `developer_configurations_cms`";
  	
//   		# use the instantiated db connection object from the init.php, to process the query
//   		# $result = $connection -> query($sql);
  	
//   		# truncate data from this table
//   		$sql = "TRUNCATE TABLE `".$table['TABLE_NAME']."`";
  	
//   		# use the instantiated db connection object from the init.php, to process the query
//   		$result = $connection -> query($sql);
//   	}
		if ($param != "123456"){
			return -1;
  		}
	  	$connection = $this->getAdapter();
	  	$arr_table = array();
	  	if ($type==1 || $type==0){
	  		//Property
	  		$arr_table = array_merge($arr_table, array(
	  			'ln_properties',
	  			'ln_properties_type',
	  			'ln_property_price',)
	  				);
	  	}
	  	if ($type==2 || $type==0){
	  		//Customer,Supplier,Staff agency..
	  		$arr_table = array_merge($arr_table, 
	  			array(
	  				'ln_client',
		  			'ln_client_document',
		  			'ln_supplier',
		  			'ln_staff',
		  			'ln_supplier',)
	  		);
	  	}
	  	if ($type==3 || $type==0){
	  		//Investment Module
	  		$arr_table = array_merge($arr_table,
	  				array(
	  			'rms_investment',
	  			'rms_investment_detail',
	  			'rms_investment_detail_broker',
	  			'rms_investor',
	  			'rms_investor_withdraw',
	  			'rms_investor_withdraw_broker',
	  			'rms_investor_withdraw_broker_detail',
	  			'rms_investor_withdraw_detail',)
	  		);
	  	}
	  	
	  	if ($type==4 || $type==0){
	  		//Other Income/Expense
	  		$arr_table = array_merge($arr_table,
	  				array(
		  			'ln_expense',
		  			'ln_income',
		  			'ln_comission',
		  			'ln_otherincome',
		  			'ln_otherincome_detail',
		  			'ln_otherincomepayment',)
	  		);
	  	}
	  	if ($type==5 || $type==0){
	  		//Plong
	  		$arr_table = array_merge($arr_table,
	  				array(
	  				'ln_issueplong',
		  			'ln_processing_plong',
		  			'ln_processing_plong_detail',
		  			'ln_receiveplong',)
	  		);
	  	}
	  	if ($type==6 || $type==0){
	  		//Sale & Payment,Change House/Owner
	  		$arr_table = array_merge($arr_table,
	  				array(
	  				'ln_sale_cancel',
		  			'ln_sale',
		  			'ln_saleschedule',
		  			'ln_client_receipt_money',
		  			'ln_client_receipt_money_detail',
		  			'ln_reschedule',
	  				'ln_change_house',
	  				'ln_change_owner',
	  						)
	  		);
	  	}
	  if (!empty($arr_table)) foreach ($arr_table as $rs){
		  	$sql = "TRUNCATE TABLE `".$rs."`";
		  	# use the instantiated db connection object from the init.php, to process the query
		  	$result = $connection -> query($sql);
	  }
	  return 1;
  }
  
  public function checkSessionExpire()
  {
  	$user_id = $this->getUserId();
  	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
  	if (empty($user_id)){
  		return false;
  	}else{
  		return true;
  	}
  }
  function reloadPageExpireSession(){
  	$url="";
  	$tr= Application_Form_FrmLanguages::getCurrentlanguage();
  	$msg = $tr->translate("Session Expire");
  	echo '<script language="javascript">
  	alert("'.$msg.'");
  	window.location = "'.Zend_Controller_Front::getInstance()->getBaseUrl().$url.'";
  	</script>';
  }
  
  function getCollectPaymentSqlSt(){
	  //(SELECT COUNT(ln_saleschedule.id) FROM `ln_saleschedule` WHERE ln_saleschedule.sale_id=`crm`.`sale_id` LIMIT 1) As times,
  	$sql=" SELECT
			  (SELECT
			     `ln_project`.`project_name`
			   FROM `ln_project`
			   WHERE (`ln_project`.`br_id` = `crm`.`branch_id`)
			   LIMIT 1) AS `branch_name`,
			  `c`.`client_id`                      AS `client_id`,
			  `c`.`client_number`                  AS `client_number`,
			  `c`.`name_kh`                        AS `name_kh`,
			  `c`.`name_en`                        AS `client_name`,
			  `crm`.`id`                           AS `id`,
			  `crm`.`sale_id`                      AS `sale_id`,
			  `crm`.`branch_id`                    AS `branch_id`,
			  `crm`.`receipt_no`                   AS `receipt_no`,
			  `crm`.`date_pay`                     AS `date_pay`,
			  `crm`.`date_input`                   AS `date_input`,
			  `crm`.`note`                         AS `note`,
			  `crm`.`user_id`                      AS `user_id`,
			  `crm`.`return_amount`                AS `return_amount`,
			  `crm`.`status`                       AS `status`,
			  `crm`.`payment_option`               AS `payment_option`,
			  `crm`.`principal_amount`             AS `principal_amount`,
			  `crm`.`is_payoff`                    AS `is_payoff`,
			  `crm`.`total_principal_permonth`     AS `total_principal_permonth`,
			  `crm`.`total_principal_permonthpaid` AS `total_principal_permonthpaid`,
			  `crm`.`total_interest_permonth`      AS `total_interest_permonth`,
			  `crm`.`total_interest_permonthpaid`  AS `total_interest_permonthpaid`,
			  `crm`.`penalize_amount`              AS `penalize_amount`,
			  `crm`.`penalize_amountpaid`          AS `penalize_amountpaid`,
			  `crm`.`service_chargepaid`           AS `service_chargepaid`,
			  `crm`.`service_charge`               AS `service_charge`,
			  `crm`.`amount_payment`               AS `amount_payment`,
			  `crm`.`total_payment`                AS `total_payment`,
			  `crm`.`recieve_amount`               AS `amount_recieve`,
			  `crm`.`penalize_amount`              AS `penelize`,
			  `crm`.`service_charge`               AS `service`,
			  `crm`.`extra_payment`                AS `extra_payment`,
			  `crm`.`payment_times`                AS `payment_times`,
			  `crm`.`field3`                       AS `field3`,
			  `crm`.`is_closed`                    AS `is_closed`,
			  `crm`.`closing_note`                 AS `closing_note`,
			  `crm`.`bank_id`                      AS `bankId`,
			  `sl`.`sale_number`                   AS `sale_number`,
			  `sl`.`price_sold`                   AS `sold_price`,
			  `sl`.`total_duration`                   AS `times`,
			  
			  
			  (SELECT `l`.`land_address` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS land_address,
			  (SELECT `l`.`street` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS street,
			  
			  (SELECT `l`.`land_code` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS land_code,
			  (SELECT `l`.`land_size` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS land_size,
			  (SELECT `l`.`id` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS hous_id,
			  (SELECT bank_name FROM `st_bank` WHERE id =crm.bank_id LIMIT 1) AS bank,
			  `crm`.`payment_method`               AS `payment_methodid`,
			  `crm`.`payment_method`               AS `payment_id`,
			  `crm`.`date_payment`                 AS `date_payment`,
			  
			  `crm`.`void_reason`           AS `void_reason`,
			  `crm`.`void_date`             AS `void_date`,
			  `crm`.`void_by`               AS `void_by`,
			  (SELECT first_name FROM `rms_users` WHERE id=`crm`.`void_by` LIMIT 1) AS voidByUserName,
			  
			  (SELECT
			     `ln_view`.`name_kh`
			   FROM `ln_view`
			   WHERE ((`ln_view`.`key_code` = `crm`.`payment_method`)
			          AND (`ln_view`.`type` = 2))
			   LIMIT 1) AS `payment_method`,
			  (SELECT
			     `ln_view`.`name_en`
			   FROM `ln_view`
			   WHERE ((`ln_view`.`key_code` = `crm`.`payment_option`)
			          AND (`ln_view`.`type` = 7))
			   LIMIT 1) AS `paymentoption`,
			   (SELECT first_name FROM `rms_users` WHERE id=crm.user_id LIMIT 1) AS user_name,
			    (SELECT
		     `ln_staff`.`co_khname`
		   FROM `ln_staff`
		   WHERE (`ln_staff`.`co_id` = `sl`.`staff_id`)
		   LIMIT 1) AS `staff_name`
		   
		   ,(SELECT doc.document_name FROM `ln_expense_document` AS doc WHERE doc.exspense_id = crm.id AND doc.documentforType = 3 LIMIT 1) AS documentFile
			,(SELECT doc.title FROM `ln_expense_document` AS doc WHERE doc.exspense_id = crm.id AND doc.documentforType = 3 LIMIT 1) AS documentTitle
		   
			FROM ((`ln_client_receipt_money` `crm`
			    JOIN `ln_sale` `sl`)
			   JOIN `ln_client` `c`)
			WHERE ((`crm`.`client_id` = `c`.`client_id`)
			       AND (`sl`.`id` = `crm`.`sale_id`)
			      )
			        	";
  	return $sql;
  }
  
  function soldreportSqlStatement(){
//   	$lang = $this->currentlang();
//   	$str = 'name_en';
//   	if($lang==1){
//   		$str = 'name_kh';
//   	}
  	$sql="
	  	SELECT
		  `s`.`id`               AS `id`,
		  (SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
		  `s`.`sale_number`      AS `sale_number`,
		  `s`.`branch_id`        AS `branch_id`,
		  `s`.`client_id`        AS `client_id`,
		  `s`.`house_id`         AS `house_id`,
		  `s`.`price_before`     AS `price_before`,
		  `s`.`price_sold`       AS `price_sold`,
		  `s`.`discount_amount`  AS `discount_amount`,
		  `s`.`discount_percent` AS `discount_percent`,
		  `s`.`other_discount` AS `other_discount`,
		  s.verify_by,
		  (SELECT
		     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`))
		   FROM `ln_client_receipt_money` `cr`
		   WHERE (`cr`.`sale_id` = `s`.`id`)
		   LIMIT 1) AS `paid_amount`,
		  `s`.`create_date`      AS `create_date`,
		  `s`.`buy_date`         AS `buy_date`,
		  `s`.`startcal_date`    AS `startcal_date`,
		  `s`.`first_payment`    AS `first_payment`,
		  `s`.`validate_date`    AS `validate_date`,
		  `s`.`end_line`         AS `end_line`,
		  `s`.`interest_rate`    AS `interest_rate`,
		  `s`.`total_duration`   AS `total_duration`,
		  `s`.`payment_id`       AS `payment_id`,
		  `s`.`staff_id`         AS `staff_id`,
		 
		  `s`.`receipt_no`       AS `receipt_no`,
		  `s`.`agreement_date`   AS `agreement_date`,
		  `s`.`is_cancel`        AS `is_cancel`,
		  `s`.`user_id`          AS `user_id`,
		  `s`.`build_start`      AS `build_start`,
		  `s`.`amount_build`     AS `amount_build`, 
		   s.note,
		  `p`.`land_code`        AS `land_code`,
		  `p`.`land_address`     AS `land_address`,
		  `p`.`land_size`        AS `land_size`,
		  `p`.`street`           AS `street`,
		  (SELECT
		     `ln_properties_type`.`type_nameen`
		   FROM `ln_properties_type`
		   WHERE (`ln_properties_type`.`id` = `p`.`property_type`)
		   LIMIT 1) AS `propertype`,
		  `p`.`property_type`    AS `property_type`,
		  `c`.`client_number`    AS `client_number`,
		  `c`.`name_kh`          AS `name_kh`,
		  `c`.`name_en`          AS `name_en`,
		  `c`.`phone`            AS `phone`,
		  (SELECT
		     `ln_staff`.`co_khname`
		   FROM `ln_staff`
		   WHERE (`ln_staff`.`co_id` = `s`.`staff_id`)
		   LIMIT 1) AS `staff_name` ";
  	$where=" 
  	FROM ((`ln_sale` `s`
		    JOIN `ln_client` `c`)
		   JOIN `ln_properties` `p`)
	  	WHERE ((`c`.`client_id` = `s`.`client_id`)
			       AND (`p`.`id` = `s`.`house_id`)
			       AND (`s`.`status` = 1)) ";
  	$araa = array(
  			'sql'=>$sql,
  			'where'=>$where,
  			);
  	return $araa;
  }
  
  function getPh($type=null){
  	// Turn on output buffering
  	ob_start();
  	//Get the ipconfig details using system commond
  	system('ipconfig /all');
  	 
  	// Capture the output into a variable
  	$mycom=ob_get_contents();
  	// Clean (erase) the output buffer
  	ob_clean();
  	 
  	$findme = "Physical";
  	//Search the "Physical" | Find the position of Physical text
  	$pmac = strpos($mycom, $findme);
  	// Get Physical Address
  	$mac=substr($mycom,($pmac+36),17);
  	if ($type==1){
  		return $mac;
  	}else {
	  
	  	//Display Mac Address
	  	if ($mac!=PHISYCAL_CONFIG){
	  		return false;
	  	}
	  	return true;
  	}
  	 
  	//If you want you can track the page visitor's mac address and store in database
  	//Insert the visitor's mac address to database
  	// " INSERT INTO `table_name` (`column_name`) VALUES('".$mac_address."') ";
  }
  function getExDate(){
  	$key = new Setting_Model_DbTable_DbLabel();
  	$keyValue = $key->getAllSystemSetting();
  	$time = $keyValue['lY']."-".$keyValue['lMM']."-".$keyValue['lDD'];
  	$exDate = date("Y-m-d",strtotime($time));
  	return $exDate;
  }
  function getChildType($id,$idetity=null){
  	$where='';
  	$db = $this->getAdapter();
  	$sql=" SELECT c.`key_code` FROM `ln_view` AS c WHERE c.`parent_id` = $id AND c.`status`=1 ";
  	$child = $db->fetchAll($sql);
  	foreach ($child as $va) {
  		if (empty($idetity)){
  			$idetity=$id.",".$va['key_code'];
  		}else{$idetity=$idetity.",".$va['key_code'];
  		}
  		$idetity = $this->getChildType($va['key_code'],$idetity);
  	}
  	return $idetity;
  }
  
  function getChildAgency($id,$idetity=null){
  	$where='';
  	$db = $this->getAdapter();
  	$sql=" SELECT c.`co_id` FROM `ln_staff` AS c WHERE c.`parent_id` = $id AND c.`status`=1 ";
  	$child = $db->fetchAll($sql);
  	foreach ($child as $va) {
  		if (empty($idetity)){
  			$idetity=$id.",".$va['co_id'];
  		}else{$idetity=$idetity.",".$va['co_id'];
  		}
  		$idetity = $this->getChildAgency($va['co_id'],$idetity);
  	}
  	return $idetity;
  }
  
  
  public function getInterestPolicy(){
  	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
  	$db = $this->getAdapter();
  	$sql= " SELECT id,title AS name
  	FROM `rms_interestsetting` WHERE title !='' AND status=1 AND type=1 ";
  	$sql.=" ORDER BY id DESC";
  	$row = $db->fetchAll($sql);
  	return $row;
  }
  function getInterestRatebySetting($inter_settingid,$current_time){
  	$db = $this->getAdapter();
  	$sql="SELECT
  	percent_value AS interest_rate FROM `rms_interestsetting` AS ist,`rms_interestsetting_detail` AS istd
  	WHERE
  	ist.id=istd.settin_id
  	AND ist.type =1
  	AND $current_time <= max_month
  	AND ist.id=$inter_settingid LIMIT 1";
  	return $db->fetchOne($sql);
  }
  function getFixePaymentbyInterest($interst_rate,$begining_fixepayment,$times){
  	$db = $this->getAdapter();
  	$fixed_payment=0;
  	if(!empty($interst_rate)){
  		$interst_rate=$interst_rate/12/100;
  		$top = pow(1+$interst_rate,$times);
  		$bottom = pow(1+$interst_rate,$times)-1;
  		$fixed_payment = round(($begining_fixepayment*$interst_rate*$top/$bottom),0,PHP_ROUND_HALF_UP);//always round up
  	}
  	return $fixed_payment;
  }
  
  function getArrayLastPayment(){
  	$arr = array(
  			1=>$this->tr->translate("BY_SCHEDULE_DATE"),
  			0=>$this->tr->translate("RECEIVED_PROPERTY"),
  			2=>$this->tr->translate("RECEIVED_HOUSE")
  		);
  	return $arr;
  	 
  }
  
 
   function getAllItems($type=null,$branch=null,$schooloption=null){
  	$db = $this->getAdapter();
  	$this->_name = "rms_category";
  	$sql="SELECT m.id, m.title AS name FROM $this->_name AS m WHERE m.status=1 ";
  	if (!empty($type)){
  		$sql.=" AND m.type=$type";
  	}
  	$sql .=' ORDER BY m.type DESC, m.title ASC';
  	return $db->fetchAll($sql);
  }
  function getViewById($type,$is_opt=null){
   	$db=$this->getAdapter();
   	$sql="SELECT key_code AS id,name_kh AS name,key_code,name_kh AS view_name FROM ln_view WHERE `type`=$type AND `status`=1 ";
   	$rows = $db->fetchAll($sql);
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$options= array(-1=>$tr->translate("CHOOSE"));
   	if($is_opt!=null){
   		if(!empty($rows))foreach($rows AS $row){
   			$options[$row['key_code']]=$row['view_name'];
   		}
   	}else{
   		return $rows;
   	}
   	return $options;
   }
   
   
  public function getAllCustomerRequireNextContact($search=array()){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$day = 1;
		$end_date = date('Y-m-d',strtotime(" +$day day"));
		
		$sql="SELECT c.*,
				(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE c.user_contact=id LIMIT 1 ) AS user_contact_name,
				name, phone,
				(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=know_by LIMIT 1) as know_by,
				 from_price,to_price,requirement,type,description,	
				statusreq
			
		";
		$sql.=", CASE
		WHEN  c.proccess = 0 THEN '".$tr->translate("DROPPED")."'
		WHEN c.proccess = 1 THEN '".$tr->translate("PROCCESSING")."'
		WHEN c.proccess = 2 THEN '".$tr->translate("WAITING_RESPONSE")."'
		WHEN c.proccess = 3 THEN '".$tr->translate("COMPLETED_CONTACT")."'
		
		END AS proccess ";
		$sql.=" FROM `ln_history_contact` AS c,
				in_customer AS ct WHERE c.proccess IN (1,2) ";
		
		
		$to_date = (empty($end_date))? '1': " c.next_contact <= '".$end_date." 23:59:59'";
		$sql.= " AND ".$to_date;
		
		$sql.=" AND ct.id = c.customer_id ";
		
		$order=" ORDER BY c.next_contact DESC,c.id DESC ";
		return $db->fetchAll($sql.$order);
	}
	function titleGender(){
		$_gender_Opt = array(
				0=>$this->tr->translate("MR"),
				1=>$this->tr->translate("MRS"),
				2=>$this->tr->translate("MSS")
				);
		return $_gender_Opt;
	
	}
	function getSqlStLoanCollect(){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
		SELECT
				  `pd`.`id`                      AS `id`,
				  `pd`.`date_payment`            AS `date_payment`,
				  `pd`.`amount_day`              AS `amount_day`,
				  `pd`.`status`                  AS `status`,
				  `pd`.`is_completed`            AS `is_completed`,
				  `pd`.`begining_balance`        AS `begining_balance`,
				  `pd`.`begining_balance_after`  AS `begining_balance_after`,
				  SUM(`pd`.`principal_permonthafter`) AS `principal_permonthafter`,
				  SUM(`pd`.`total_interest_after`)    AS `total_interest_after`,
				  `pd`.`total_payment_after`     AS `total_payment_after`,
				  `pd`.`ending_balance`          AS `ending_balance`,
				  SUM(`pd`.`service_charge`)          AS `service_charge`,
				  `pd`.`no_installment`          AS `no_installment`,
				  `pd`.`last_optiontype`         AS `last_optiontype`,
				  `pd`.`ispay_bank`         	 AS `ispay_bank`,
				  COUNT(pd.id) 					AS amount_late,
				  (SELECT p.`project_name` FROM `ln_project` AS p WHERE p.`br_id` = `s`.`branch_id` LIMIT 1) AS `branch_name`,
				  
				 
				  `s`.`branch_id`                AS `branch_id`,
				  `s`.`id`                       AS `sale_id`,
				  `s`.`sale_number`              AS `sale_number`,
				  `s`.`client_id`                AS `client_id`,
				  `s`.`house_id`                 AS `house_id`,
				  `s`.`price_before`             AS `price_before`,
				  `s`.`price_sold`               AS `price_sold`,
				  `s`.`discount_amount`          AS `discount_amount`,
				  `s`.`admin_fee`                AS `admin_fee`,
				  `s`.`other_fee`                AS `other_fee`,
				  `s`.`paid_amount`              AS `paid_amount`,
				  `s`.`balance`                  AS `balance`,
				  `s`.`create_date`              AS `create_date`,
				  `s`.`buy_date`                 AS `buy_date`,
				  `s`.`startcal_date`            AS `startcal_date`,
				  `s`.`first_payment`            AS `first_payment`,
				  `s`.`validate_date`            AS `validate_date`,
				  `s`.`end_line`                 AS `end_line`,
				  `s`.`interest_rate`            AS `interest_rate`,
				  `s`.`total_duration`           AS `total_duration`,
				  `s`.`payment_id`               AS `payment_id`,
				  `s`.`staff_id`                 AS `staff_id`,
				  `s`.`expect_income_note`       AS `expect_income_note`,
				  
				  `l`.`land_code`                AS `land_code`,
				  `l`.`land_address`             AS `land_address`,
				  `l`.`street`                   AS `street`,
				  `l`.`old_land_id`              AS `old_land_id`,
				  
				  (SELECT `c`.`phone` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `phone_number`,
				  (SELECT `c`.`phone` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `phone`,
				 
				  (SELECT `c`.`client_number` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `client_number`,
				  (SELECT `c`.`name_kh` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `client_name`,
				  (SELECT `co`.`co_khname` FROM `ln_staff` `co` WHERE (`co`.`co_id` = `s`.`staff_id`) LIMIT 1) AS `co_name`,
				  (SELECT cr.date_input FROM `ln_client_receipt_money` AS cr WHERE cr.sale_id=pd.sale_id AND cr.recieve_amount>0 ORDER BY cr.date_input DESC LIMIT 1) As last_pay_date,
				  (SELECT ln_view.name_kh FROM ln_view WHERE ln_view.type =29 AND key_code = pd.ispay_bank LIMIT 1) AS payment_type
				   
				
		";
		$where=" 
				FROM ((`ln_saleschedule` `pd` JOIN `ln_sale` `s`) JOIN `ln_properties` `l`) 
				WHERE `s`.`house_id` = `l`.`id`
					   AND `s`.`status` = 1
					   AND `s`.`is_cancel` = 0
					   AND `pd`.`sale_id` = `s`.`id`
					   AND `pd`.`status` = 1
			";
		$araa = array(
				'sql'=>$sql,
				'where'=>$where,
				);
		return $araa;
			
	}
	function getSqlStExpectedIncome(){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT
			  `c`.`client_number`            AS `client_number`,
			  `c`.`name_kh`                  AS `name_kh`,
			  `c`.`phone`                    AS `phone`,
			  
			  (SELECT `ln_project`.`project_name` FROM `ln_project` WHERE (`ln_project`.`br_id` = `s`.`branch_id`) LIMIT 1) AS `branch_name`,
			  
			  `l`.`land_code`                AS `land_code`,
			  `l`.`land_address`             AS `land_address`,
			  `l`.`street`                   AS `street`,
			  
			  `s`.`sale_number`              AS `sale_number`,
			  `s`.`branch_id`                AS `branch_id`,
			  `s`.`client_id`                AS `client_id`,
			  `s`.`price_before`             AS `price_before`,
			  `s`.`price_sold`               AS `price_sold`,
			  `s`.`discount_amount`          AS `discount_amount`,
			  `s`.`other_fee`                AS `other_fee`,
			  `s`.`paid_amount`              AS `paid_amount`,
			  `s`.`buy_date`                 AS `buy_date`,
			  `s`.`startcal_date`            AS `startcal_date`,
			  `s`.`first_payment`            AS `first_payment`,
			  `s`.`validate_date`            AS `validate_date`,
			  `s`.`end_line`                 AS `end_line`,
			  `s`.`interest_rate`            AS `interest_rate`,
			  `s`.`total_duration`           AS `total_duration`,
			  `s`.`payment_id`               AS `payment_id`,
			  `s`.`expect_income_note`       AS `expect_income_note`,
			  
			  `sd`.`id`                      AS `id`,
			  `sd`.`sale_id`                AS `sale_id`,
			  `sd`.`penelize`                AS `penelize`,
			  `sd`.`date_payment`            AS `date_payment`,
			  `sd`.`amount_day`              AS `amount_day`,
			  `sd`.`status`                  AS `status`,
			  `sd`.`is_completed`            AS `is_completed`,
			  `sd`.`begining_balance`        AS `begining_balance`,
			  `sd`.`principal_permonthafter` AS `principal_permonthafter`,
			  `sd`.`total_interest_after`    AS `total_interest_after`,
			  `sd`.`total_payment_after`     AS `total_payment_after`,
			  `sd`.`ending_balance`          AS `ending_balance`,
			  `sd`.`principal_permonth`      AS `principal_permonth`,
			  `sd`.`total_interest`          AS `total_interest`,
			  `sd`.`total_payment`           AS `total_payment`,
			  `sd`.`service_charge`          AS `service_charge`,
			  `sd`.`no_installment`          AS `no_installment`,
			  `sd`.`ispay_bank`          AS `ispay_bank`,
			  `sd`.`last_optiontype`          AS `last_optiontype`,
			  (SELECT ln_view.name_kh FROM ln_view WHERE ln_view.type =29 AND key_code = sd.ispay_bank LIMIT 1) AS payment_type,
			(SELECT `ln_client_receipt_money`.`date_input`  FROM `ln_client_receipt_money` WHERE (`ln_client_receipt_money`.`land_id` = 1) ORDER BY `ln_client_receipt_money`.`date_input` DESC  LIMIT 1) AS `last_pay_date`
			
		";
		$where=" 
			FROM (((`ln_sale` `s` JOIN `ln_saleschedule` `sd`) JOIN `ln_properties` `l`) JOIN `ln_client` `c`)
			WHERE `s`.`id` = `sd`.`sale_id`
				   AND `l`.`id` = `s`.`house_id`
				   AND `s`.`status` = 1
				   AND `s`.`is_cancel` = 0
				   AND `sd`.`status` = 1
				   AND `c`.`client_id` = `s`.`client_id`
			
			";
		$order = "GROUP BY `s`.`id`,`sd`.`date_payment`
			ORDER BY `s`.`id`,`s`.`client_id`";
		$araa = array(
				'sql'=>$sql,
				'where'=>$where,
				'order'=>$order,
				);
		return $araa;
	}
	 public function getAllAmountBuild(){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT 
				DISTINCT(s.amount_build) AS value
			FROM `ln_sale` AS s 
			WHERE s.amount_build IS NOT NULL AND s.amount_build >0 
		";
		$order=" ORDER BY s.amount_build ASC ";
		return $db->fetchAll($sql.$order);
	}
	
	 public function getAllContractIssuer(){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT 
				ci.id AS id,
				CASE
					WHEN  ci.nameKhWith IS NULL THEN ci.nameKh
					WHEN  ci.nameKhWith ='' THEN ci.nameKh
					ELSE CONCAT(COALESCE(ci.nameKh,''),' & ',COALESCE(ci.nameKhWith,'')) 
					
				END AS `name` 
					
			 FROM `ln_contract_issuer` AS ci 
			 WHERE ci.status=1 
		";
		$order="  ORDER BY ci.id ASC ";
		return $db->fetchAll($sql.$order);
	}

	public function getPaymentColectionInfo($data){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM `ln_income` WHERE 1 ";
		if( !empty($data['income_category'])){
			$sql.= " AND category_id = ".$data['income_category'];
		}
		if( !empty($data['sale_id'])){
			$sql.= " AND sale_id = ".$data['sale_id'];
		}
		if( !empty($data['incomeType'])){
			$sql.= " AND incomeType = ".$data['incomeType'];
		}
  
		return $db->fetchRow($sql);
	}
	
	function resizeImase($image,$part,$new_imagename=null){
		$photo = $image;
		$temp = explode(".", $photo["name"]);
		$new_name = $temp[0].end($temp);
		if (!empty($new_imagename)){
		  $new_name = $new_imagename;
		}
		move_uploaded_file($image["tmp_name"], $part .$new_name);
		  
		$uploadimage=$part.$new_name;
	//    $newname=$image["name"];
	//    // Load the stamp and the photo to apply the watermark to
		if (end($temp) == 'jpg') {
		  $im = imagecreatefromjpeg($uploadimage);
		} else if (end($temp) == 'jpeg') {
		  $im = imagecreatefromjpeg($uploadimage);
		} else if (end($temp) == 'png') {
		  $im = imagecreatefrompng($uploadimage);
		} else if (end($temp) == 'gif') {
		  $im = imagecreatefromgif($uploadimage);
		}
		
		
		if ($image['size']>(1000000*5)){
		  // Save the image to file and free memory quality 70%
		  imagejpeg($im, $uploadimage, 10);
		}else if($image['size']>(1200000)){
			imagejpeg($im, $uploadimage, 15); //quality 50%
		}else if($image['size']>(1000000)){
		  imagejpeg($im, $uploadimage, 20); //quality 50%
		}else if($image['size']>512000){
		  // Save the image to file and free memory quality 80%
		  imagejpeg($im, $uploadimage, 60);
		}else if($image['size']>=102400){
			// Save the image to file and free memory quality 90%
			imagejpeg($im, $uploadimage, 70);
		}
	  //  imagedestroy($uploadimage);
		return $new_name;
  }
}
?>