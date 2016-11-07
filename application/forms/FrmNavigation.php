<?php

class Application_Form_FrmNavigation{
	protected $tr;
	
	protected $_start=0;
	protected $_len=20;
	protected $_url;			
	protected $_current_page;
	protected $_record_count;
	protected $_sign_param;
	//constructor of class
	
	//get len
	public function getLang(){
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		return $this->tr;
	}
	public static function getLimit(){
		return  20;
	}
	
	//set len
	public function setLimit($limit)
	{
		$this->_len=$limit;
	}	
	//set url
	public function setUrl($url)
	{
		$this->_url=$url;
	}
	//set start
	public function setStart($start)
	{
		$this->_start=$start;		
	}
	//set record count
	public function setRecordCount($record_count)
	{
		$this->_record_count=$record_count;
	}
	//set sign parameter
	public function setSignParameter($sign_param)
	{
		$this->_sign_param=$sign_param;
	}
	
	//init function of class
    public function init($url, $start, $limit, $record_count,$sign_param="?"){
        /* Form Elements & Other Definitions Here ... */
    	$this->_url= Zend_Controller_Front::getInstance()->getBaseUrl().$url;
    	$this->_start=$start;
    	$this->_record_count=$record_count;
    	$this->_len=$limit;
    	$this->_sign_param=$sign_param;
    }
          
    //display navigation page         
	public function navigationPage(){	
		//validate if limit > total recorde
		if($this->_len > $this->_record_count || $this->_len == 'All') return '';
		
		//find numerber of page
		$total_page = (int)($this->_record_count / $this->_len);
		if(($this->_record_count % $this->_len) != 0 ){
			$total_page = (int)($this->_record_count / $this->_len) + 1;
		} 
		
		//find current page 
		$cur_page = $this->_start / $this->_len;
		
		
		//set default string
		$div='<div class="navigation">';
		$frs='';
		$nex='';
		$pre='';
		$lst='';
		$a='';							
		$space = '&nbsp;&nbsp;&nbsp;&nbsp;';
		//generate link Previous and First
		if($cur_page!=0){	
			$frs='<button dojoType="dijit.form.Button" showLabel="true" type="button" onclick="window.location =\''.$this->_url.'\'">First</button>';	
			$pre='<button dojoType="dijit.form.Button" showLabel="true" type="button" onclick="window.location =\''.$this->_url.$this->_sign_param.'limit_satrt='.$this->getLimitStart($cur_page-1).'\'">Previous</button>';
		}
		else{
			$frs='<button dojoType="dijit.form.Button" showLabel="true" type="button" disabled >First</button>';
			$pre='<button dojoType="dijit.form.Button" showLabel="true" type="button" disabled >Previous</button>';
		}
		//generate link number 1 2 3 4 5
		
		$i; $num_add;
		if($cur_page == 0){
			$i = 0;
			$num_add = 4;
		}
		elseif($cur_page == 1){
			$i = 0;
			$num_add = 3;
		}
		elseif(($total_page-1) == $cur_page){
		    $i = $cur_page - 4;
		    if($i<0) $i =0;
		    $num_add = 0;
		}
		elseif(($total_page-2) == $cur_page){
			$i = $cur_page - 3;
			if($i<0) $i =0;
			$num_add = 1;
		}
		else{
		    $i = $cur_page - 2;
			$num_add = 2;
		}

		for($i; $i<$total_page ;$i++){			
			if($i == $cur_page){
				$a.='<button dojoType="dijit.form.Button" showLabel="true" type="button" disabled>'.($i+1).'</button>';
			}
			else{ 
				$a.='<button dojoType="dijit.form.Button" showLabel="true" type="button" onclick="window.location =\''.$this->_url.$this->_sign_param.'limit_satrt='.$this->getLimitStart($i).'\'">'.($i+1).'</button>';
			}		
			
			if( $cur_page + $num_add == $i) break;
		}	
		
		//generate link next last
		if($total_page>1 && $cur_page != ($total_page-1)){
			$nex='<button dojoType="dijit.form.Button" showLabel="true" type="button" onclick="window.location =\''.$this->_url.$this->_sign_param.'limit_satrt='.$this->getLimitStart($cur_page+1).'\'">Next</button>';
			$lst='<button dojoType="dijit.form.Button" showLabel="true" type="button" onclick="window.location =\''.$this->_url.$this->_sign_param.'limit_satrt='.$this->getLimitStart($total_page-1).'\'">Last</button>';
		}
		else{
			$nex='<button dojoType="dijit.form.Button" showLabel="true" type="button" disabled >Next</button>';
			$lst='<button dojoType="dijit.form.Button" showLabel="true" type="button" disabled >Last</button>';
		}
		
		$close_div='</div>';
		$div.=$frs.$pre.$a.$nex.$lst.$close_div;
		
		return $div;	 	 		
	}

	private function getLimitStart($num_page){
		return 	$num_page * $this->_len;
	}

	public function getRowsPerPage($rowsperpage, $form_name){
		$numlist = array('5', '10', '15', '20', '25', '30', '35', '50', '100', 'All');
		$str = $this->getLang()->translate("RECODES").'<select name="rows_per_page" id="rows_per_page" style="width:70px"  
				onChange="document.'.$form_name.'.submit();" dojoType="dijit.form.FilteringSelect">'; 
		foreach ($numlist as $key => $num){ 
			if($num == $rowsperpage){
				$str .='<option value="'.$num.'" selected>'.$num.'</option>';
			}
			else{
				$str .='<option value="'.$num.'">'.$num.'</option>';	
			}			
		}
		$str .='</select>';
		return $str;
	}
	
	public function getResultRows(){
		$unit = ($this->_len > $this->_record_count)? $this->_record_count : $this->_len;
		$start = ($this->_record_count == 0)? $this->_record_count :$this->_start+1;
		$str = $this->getLang()->translate("RESULT")." $start - $unit ".$this->getLang()->translate('OF'). " $this->_record_count";
		return $str;
	}
}

