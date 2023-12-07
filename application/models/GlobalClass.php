<?php

class Application_Model_GlobalClass  extends Zend_Db_Table_Abstract
{
   
   public function getOptonsHtml($sql, $display, $value){
   	$db = $this->getAdapter();
   	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
   	$option = '<option value="">'.$tr->translate("PLEASE_SELECT").'</option>';
   	foreach($db->fetchAll($sql) as $r){
   		$option .= '<option value="'.$r[$value].'">'.htmlspecialchars($tr->translate(strtoupper($r[$display])), ENT_QUOTES).'</option>';
   	}
   	return $option;
   }

	/**
	 * add element "delete" to $rows
	 * @param array $rows
	 * @param string $url_delete
	 * @param string $base_url
	 * @return array $rows
	 */
	public static function getImgDelete($rows,$url_delete,$base_url){
		foreach($rows as $key=>$row){
			$url = $url_delete.$row["id"];
			$row['delete'] = '<a href="'.$url.'"><img src="'.BASE_URL.'/images/icon/cross.png"/></a>';
			$rows[$key] = $row;
		}
		return $rows;
	}
	
	/**
	 * Get Day name With multiple Languages
	 * @param string $key
	 * @var $key ('mo', 'tu', 'we', 'th', 'fr', 'sa', 'su')
	 */
	public function getDayName($key = ''){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$day_name = array(
							'su' => $tr->translate('SU'),
							'mo' => $tr->translate('MO'),
							'tu' => $tr->translate('TU'),
							'we' => $tr->translate('WE'),
							'th' => $tr->translate('TH'),
							'fr' => $tr->translate('FR'),
							'sa' => $tr->translate('SA')							
						 );
		if(empty($key)){
			return $day_name;
		}
		return  $day_name[$key];
	}
	
	/**
	 * Get all Hour per day
	 * @param int $key
	 * @return multitype:string |Ambigous <string>
	 * @var $key = [0-23]
	 */
	
	/**
	 * Generate Age for child
	 */

	
	
	/**
	 * get phone number in format
	 * @param string $str
	 * @return string
	 */
	public static function getPhoneNumber($str)
	{
		$str = str_replace(" ", "", $str);
		$firt = substr($str, 0,3);
		$second = substr($str, 3, strlen($str)-3);
		$phone = $firt." ".$second;
		return $phone;
	}
	public function getImgActive($rows,$base_url, $case='',$degree=null,$display=null){
			if($rows){
				$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
				$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
		
				foreach ($rows as $i =>$row){
					if($degree!=null){
						$dg = new Application_Model_DbTable_DbGlobal();
						
						$rows[$i]['degree']  = $dg->getAllDegree($row['degree']);
					}
					if($display!=null){
						$rows[$i]['displayby']= ($row['displayby']==1)?'Khmer':'English';
					}
					if($row['status'] == 1){
						$rows[$i]['status']= $imgtick;
					}
					else{
						$rows[$i]['status'] = $imgnone;
						
					}
					
				}
			}
			return $rows;
		}
		
	
		
		public function getAllExpenseIncomeType($type){
			$_db = new Invpayment_Model_DbTable_DbCateExpense();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$rows = $_db->getParentCateExpense();
			$options = '';
			$options .= '<option Value="0">'.$tr->translate("SELECT_CATEGORY").'</option>';
			$options .= '<option Value="-1">'.$tr->translate("ADD_NEW").'</option>';
			if(!empty($rows))foreach($rows as $value){
				$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars(addslashes($value['name'])).'</option>';
			}			
			return $options;
		}
		
		
		
	
		
		
}

