<?php
class Stock_Model_DbTable_DbGlobalProduct extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_tuitionfee';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	function getItemsDetailCodeByItemsType($type){
		$db = $this->getAdapter();
		$this->_name = "rms_product";
		$sql ="SELECT COUNT(id) AS number FROM $this->_name WHERE items_type =$type  LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$pre = $this->getItemType($type);
		$pre = empty($pre['prefix'])?"":$pre['prefix'];
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		//$pre="";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function getItemType($type){
		$db = $this->getAdapter();
		$this->_name = "rms_itemstype";
		$sql="SELECT * FROM $this->_name AS t WHERE t.id = $type LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getAllItems($type=null,$branchlists=null,$schooloption=null){
		$db = $this->getAdapter();
		$_db = new Application_Model_DbTable_DbGlobal();
		$currentLang = $_db->currentlang();
		$colunmname='title_en';
		if ($currentLang==1){
			$colunmname='title';
		}
		
		$this->_name = "rms_product_cate";
		$sql="SELECT m.id, m.$colunmname AS name FROM $this->_name AS m WHERE m.status=1 ";
		if (!empty($type)){
			$sql.=" AND m.type=$type";
		}
		$sql .=' ORDER BY m.schoolOption ASC,m.type DESC,m.ordering DESC, m.title ASC';	
		return $db->fetchAll($sql);
	}
	function getProductLocation($id){
		$db = $this->getAdapter();
		$sql = "
		SELECT pl.*,
		(SELECT p.project_name FROM `ln_project` as p WHERE pl.`brand_id` = p.`br_id` LIMIT 1  ) AS branch_name
		FROM `rms_product_location` AS pl WHERE pl.pro_id = $id";
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$location = $dbgb->getAccessPermission('pl.`brand_id`');
		return $db->fetchAll($sql);
	}
	function getPuchaseNo($branch_id){//used global
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$branch_id = empty($branch_id)?0:$branch_id;
	
		$db = $this->getAdapter();
		$sql="SELECT COUNT(id) FROM rms_purchase WHERE branch_id=$branch_id ORDER BY id DESC";
		$stu_num = $db->fetchOne($sql);
		$pre = $dbgb->getPrefixCode($branch_id);//by branch
		$pre.='PO-';
		$new_acc_no= (int)$stu_num+1;
		$length = strlen((int)$new_acc_no);
		for($i = $length;$i<4;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function getProductbyBranch($category_id=null,$product_type=null){
		$db = $this->getAdapter();
		$sql="SELECT t.id,title AS name FROM `rms_product` AS t,
		`rms_product_location`
		WHERE t.items_type=3 AND t.status=1
		AND ( t.is_productseat=1 OR (t.id=rms_product_location.pro_id)) ";
		$_db = new Application_Model_DbTable_DbGlobal();
		$sql.=$_db->getAccessPermission("brand_id");
		 
		if($category_id!=null AND $category_id>0 ){
			$sql.=' AND t.items_id='.$category_id;
		}
		if(empty($product_type)){
			$sql.=" AND t.product_type=1 ";
		}
		$sql.=" GROUP BY t.id ";
		return $db->fetchAll($sql);
	}
	function getFooterStockReport($spacing=1,$font_size="12px",$font_family="Times New Roman,Khmer OS Muol Light;"){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str="<table width='100%' style='font-size: $font_size;font-family:$font_family'>";
		for($i=1;$i<=$spacing;$i++){
			$str.="<tr><td>&nbsp;</td></tr>";
		}
		$str.="	<tr>
		<td width='25%' align='center'>
		<span>".$tr->translate('APPROVED_BY')."</span>
		</td>
		<td width='50%' align='center'>
		<span>".$tr->translate('VERIFIED_BY')."</span>
		</td>
		<td width='25%' align='center'>
		<span>".$tr->translate('PREPARED_BY')."</span>
		</td>
		</tr>
		</table>";
		return $str;
	}
} 