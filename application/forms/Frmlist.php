<?php
/*
 * Author: 	Mok channy
 * Date	 : 	18-Feb-2014
 */
class Application_Form_Frmlist //extends Zend_Controller_Action
{
//   public function getPagine($url,$start,$limit,$record_count){
//   	$frm_list = new Application_Model_GlobalClass();
//     $rs_page =$frm_list->getList($url,"list",$start, $limit,$record_count); 
//     return $rs_page;
//   }	
  /* $url     : url for current action 
   * $collumn : number of collumn in gride view
   * $result  : result for execute query 
   * $start   : number of start select result
   * $limit   :n
   **/
  public function grideView($edit_url,$url,$collumn,$result=null,$start,$limit,$record_count){
  //$page = $this->getPagine($url,$start,$limit,$recount_count);
  $frm_list = new Application_Model_GlobalClass();
  $page =$frm_list->getList($url,"list",$start, $limit,$record_count);
  $result_row = $page["result_row"];
  $rows_per_page = $page["rows_per_page"];
  $nevigation = $page["nevigation"];
  $stringPagination='<style>
  	#grid{
  	margin: 0 auto;
  	}
  	.dojoxGridSortNode{
  		text-align: center;
  		height: 30px;line-height:27px;
  	}
  	.height-text{height:30px; min-width: 350px;
  	}
  	</style>';
  $stringPagination.='<script>
  	dojo.require("dojox.grid.DataGrid");
  	dojo.require("dijit.Dialog");
  	dojo.require("dojo.data.ItemFileWriteStore");
  	dojo.require("dojo.store.Memory");';
    $rs=(Zend_Json::encode($result));
    $stringPagination.="var tran_store  = getDataStorefromJSON('id','name',$rs)";
  	$stringPagination.=';dojo.ready(function(){
  	
  			grid = new dojox.grid.DataGrid({						
  				store: tran_store,	
  				autoHeight: true, 		
  				structure: [
  					{ name: "N.", field: "num", width: "40px", cellStyles: "text-align: center;" },
  					{ name: "id", field: "id", hidden: "true" },';
  	if(!empty($result['err'])){
  		echo '<script>alert("មិន​ទាន់​មាន​ទន្និន័យ​​ទេ!");</script>';
  	}
  	$key_col = @array_keys(@$result[0]);$key_index = 2;
  	$tr=Application_Form_FrmLanguages::getCurrentlanguage();
  	for($i=0;$i<count($collumn);$i++){
  		$stringPagination.="{ name:'".$tr->translate($collumn[$i])."',field: '".$key_col[$key_index]."', width: 'auto'},";
  		$key_index++;
  	}
  			$stringPagination.="]
  			}, 'grid');
  			grid.startup();
  	
  			dojo.connect(grid,  'onRowClick', grid, function(evt){
  				var idx = evt.rowIndex,
  					item = this.getItem(idx);
  				window.location = '".$edit_url."/id/' + this.store.getValue(item, 'id');
  			});
  		});	
  	</script>";
  	$stringPagination.='<table class="full">
  	<tr>
  	<td colspan="2">
  	<div id="grid" ></div>
  	</td>
  	</tr>
  	<tr>
  	<td>'.$result_row.'</td>	
  	          <td align="right" >'
  			    .$rows_per_page.
  			  '</td>
  		  </tr>
  		  <tr>
  			  	<td colspan="2" align="center">
  			  		<div id="navigetion" style="margin: 0 auto;">'.$nevigation.'</div>
  			  	</td>
  		  </tr>	  
  	</table>';
  	
  	return $stringPagination;
  }
}