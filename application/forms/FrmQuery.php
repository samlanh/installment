<?php

class Application_Form_FrmQuery 
{
    public function init()
    {
        /* Form Elements & Other Definitions Here ... */    	
    }
    // actual disbursement
 	public static function getFilterActualDisbursementTable($table_name)
    {
    	$str='<script language="javascript">
    		$(document).ready(function(){
    			$("#fund_no").change(function(){
    				var value=$("#fund_no option:selected").val();
    				$("#fund_label").text(value);    				    				       					    				 				    				
			    		$.post(
							"/jquery/filter/budgetsupport",
							{
								"fund_no" : value				
							},
							function(data){
								//$("#'.$table_name.' tr").remove();
								$("#'.$table_name.'  tbody").empty();							
								$("#'.$table_name.'").html(data[0].table);								
								for(var i=1;i<=10;i++){	
									$("#amount_"+i).ForceCurrencyOnly();
									for(var j=1;j<=4;j++){																				
										$("#code"+j+"_"+i).ForceOnlyIntDigit();									
									} 
								}																																																					
							},
							"json"				
						 );				
						 return false;					
				});		
    		});
    		</script>
    	';
    	return $str;
    }
    //filter table detail when fund selected
    public static function getFilterBudgetSupportTable($table_name)
    {
    	$str='<script language="javascript">
    		$(document).ready(function(){
    			$("#fund_no").change(function(){
    				var value=$("#fund_no option:selected").val();
    				$("#fund_label").text(value);    				    				   
    				if($("#type_of").val()=="COMMITMENT"){	    				 				    				
			    		$.post(
							"/jquery/filter/budgetsupport",
							{
								"fund_no" : value				
							},
							function(data){
								//$("#'.$table_name.' tr").remove();
								$("#'.$table_name.'  tbody").empty();							
								$("#'.$table_name.'").html(data[0].table);								
								for(var i=1;i<=10;i++){	
									$("#amount_"+i).ForceCurrencyOnly();
									for(var j=1;j<=4;j++){																				
										$("#code"+j+"_"+i).ForceOnlyIntDigit();									
									} 
								}																																																					
							},
							"json"				
						 );				
						 return false;
					}
				});		
    		});
    		</script>
    	';
    	return $str;
    }
    //filter table detail when fund selected
    public static function getFilterAllocatedTable($table_name)
    {
    	$str='<script language="javascript">
    		$(document).ready(function(){
    			$("#fund_no").change(function(){
    				var value=$("#fund_no option:selected").val();    				    				
		    		$.post(
						"/jquery/filter/allocateddetail",
						{
							"fund_no" : value				
						},
						function(data){
							//$("#'.$table_name.' tr").remove();
							$("#'.$table_name.'  tbody").empty();							
							$("#'.$table_name.'").html(data[0].table);
							
							for(var i=1;i<=10;i++){	
								for(var j=1;j<=4;j++){
									$("#quater"+j+"_"+i).ForceCurrencyOnly();										
									$("#code"+j+"_"+i).ForceOnlyIntDigit();									
								} 
							}
							$("#fund_label").text(value);																																													
						},
						"json"				
					 );
					 return false;
				});		
    		});
    		</script>
    	';
    	return $str;
    }
  	//filter when fund select
    public static function getFilterSelect($action,$name,$lable,$value)
    {
    	$str='<script language="javascript">
    		$(document).ready(function(){
    			$("#fund_no").change(function(){
    				var value=$("#fund_no option:selected").text();    				    				
		    		$.post(
						"/jquery/filter/'.$action.'",
						{
							"fund_no" : value				
						},
						function(data){
							var val=$("#'.$name.'").val();					
							var temp="<option value=\'\'>--------------None-----------------</option>";
							if(data)temp="<option value=\'\'>--------------Select-----------------</option>";
							$("#'.$name.' option").each(function(i, option){ $(option).remove(); });													
							$("#'.$name.'").append(temp);							
							for(var i=0;i<data.length;i++){						
								$("#'.$name.'").append(\'<option value="\'+data[i].'.$value.'+\'">\'+data[i].'.$lable.'+\'</option>\');					
							}											
							$("#'.$name.'").val(val);														
						},
						"json"				
					 );
					 return false;
				});		
    		});
    		</script>
    	';
    	return $str;
    }
    //filter when fund select name
    public static function getFilterFundName()
    {
    	$str='<script language="javascript">
    	$(document).ready(function(){
    		var value=$("#fund_no option:selected").val();
    	$.post(
    	"/jquery/filter/fundname",
    	{
    	"fund_no" : value
    },
    function(data){
    	$("#project_name").text(data[0].title_khmer+" - "+data[0].title_english);    	
    },
    "json"
    );
    
    	$("#fund_no").change(function(){
    	var value=$("#fund_no option:selected").val();
    	$("#fund_label").text(value);
    	$("#project_name").text("");
    	$.post(
    	"/jquery/filter/fundname",
    	{
    	"fund_no" : value
    },
    function(data){
    	$("#project_name").text(data[0].title_khmer+" - "+data[0].title_english);    	
    },
    "json"
    );
    return false;
    });
    });
    </script>
    ';
    	return $str;
    }
    //division
  	public static function getFilterSelectDivision($action,$name,$lable,$value)
    {
    	$str='<script language="javascript">
    		$(document).ready(function(){
    			$("#division_id").change(function(){
    				var value=$("#division_id").val();    				    				
		    		$.post(
						"/jquery/filter/'.$action.'",
						{
							"division_id" : value				
						},
						function(data){
							var val=$("#'.$name.'").val();														
							var temp="<option value=\'\'>--------------None-----------------</option>";
							if(data)temp="<option value=\'\'>--------------Select-----------------</option>";
							$("#'.$name.' option").each(function(i, option){ $(option).remove(); });													
							$("#'.$name.'").append(temp);
							for(var i=0;i<data.length;i++){						
								$("#'.$name.'").append(\'<option value="\'+data[i].'.$value.'+\'">\'+data[i].'.$lable.'+\'</option>\');					
							}				
							$("#'.$name.'").val(val);							
						},
						"json"				
					 );
					 return false;
				});		
    		});
    		</script>
    	';
    	return $str;
    }
 //division diff
  	public static function getFilterSelectDivisionDiff($table)
    {
    	$str='<script language="javascript">
    		$(document).ready(function(){
    			$("#division_id").change(function(){
    				var value=$("#division_id").val();    				    				
		    		$.post(
						"/jquery/filter/diff",
						{
							"division_id" : value,"table":"'.$table.'"			
						},
						function(data){
							var val=$("#fund_no").val();														
							var temp="<option value=\'\'>--------------None-----------------</option>";
							if(data)temp="<option value=\'\'>--------------Select-----------------</option>";
							$("#fund_no option").each(function(i, option){ $(option).remove(); });													
							$("#fund_no").append(temp);
							for(var i=0;i<data.length;i++){						
								$("#fund_no").append(\'<option value="\'+data[i].fund_no+\'">\'+data[i].fund_no+\'</option>\');					
							}				
							$("#fund_no").val(val);							
						},
						"json"				
					 );
					 return false;
				});		
    		});
    		</script>
    	';
    	return $str;
    }    
    //set filter in filter
    public static function setFilter($items,$table)
    {
    	$str='';
    	foreach($items as $read){
//     		echo $read;exit();	
    		if($read=='province')	$str.=Application_Form_FrmQuery::getFilterSelect('index', 'province', 'province_en_name', 'pro_id');
    		/*elseif($read=='donor')	$str.=Application_Form_FrmQuery::getFilterSelect('donor', 'donor_id', 'name_english', 'donor_id');
			elseif($read=='ea') $str.=Application_Form_FrmQuery::getFilterSelect('ea', 'executive_agency_no', 'name_english', 'executive_agency_no');
			elseif($read=='ia')$str.=Application_Form_FrmQuery::getFilterSelect('ia', 'implementing_agency_no', 'name_english', 'executive_agency_no');
			elseif($read=='fund')$str.=Application_Form_FrmQuery::getFilterSelectDivisionDiff($table);
			elseif($read=='currency')	$str.=Application_Form_FrmQuery::getFilterSelect('currency', 'currency_type_id', 'currency', 'currency_type_id');
			elseif($read=='wa')$str.=Application_Form_FrmQuery::getFilterSelect('wa', 'wa_no', 'wa_no', 'wa_no');
			elseif($read=='contract')$str.=Application_Form_FrmQuery::getFilterSelect('contract', 'contract_no', 'contract_no', 'contract_no');
			elseif($read=='fundname')$str.=Application_Form_FrmQuery::getFilterFundName();*/
    	}
    	return $str;
    }
	//division allocated
  	public static function getFilterSelectDivisionAllcoated($table)
    {
    	$str='<script language="javascript">
    		$(document).ready(function(){
    			$("#division_id").change(function(){
    				var value=$("#division_id").val();    				    				
		    		$.post(
						"/jquery/filter/allocated",
						{
							"division_id" : value,"table":"'.$table.'"			
						},
						function(data){
							var val=$("#fund_no").val();														
							var temp="<option value=\'\'>--------------None-----------------</option>";
							if(data)temp="<option value=\'\'>--------------Select-----------------</option>";
							$("#fund_no option").each(function(i, option){ $(option).remove(); });													
							$("#fund_no").append(temp);
							for(var i=0;i<data.length;i++){						
								$("#fund_no").append(\'<option value="\'+data[i].fund_no+\'">\'+data[i].fund_no+\'</option>\');					
							}				
							$("#fund_no").val(val);							
						},
						"json"				
					 );
					 return false;
				});		
    		});
    		</script>
    	';
    	return $str;
    }
    //set filter in filter by allocated table
	public static function setFilterAllocated($items,$table)
    {
    	$str='';
    	foreach($items as $read){	
    		if($read=='division')	$str.=Application_Form_FrmQuery::getFilterSelect('division', 'division_id', 'name_english', 'division_id');
    		elseif($read=='donor')	$str.=Application_Form_FrmQuery::getFilterSelect('donor', 'donor_id', 'name_english', 'donor_id');
    		elseif($read=='fiscalyear')	$str.=Application_Form_FrmQuery::getFilterSelect('fiscalyear', 'fiscal_year', 'fiscal_year', 'fiscal_year');
    		elseif($read=='currency')	$str.=Application_Form_FrmQuery::getFilterSelect('currency', 'currency_type_id', 'currency', 'currency_type_id');
			elseif($read=='ea') $str.=Application_Form_FrmQuery::getFilterSelect('ea', 'executive_agency_no', 'name_english', 'executive_agency_no');
			elseif($read=='ia')$str.=Application_Form_FrmQuery::getFilterSelect('ia', 'implementing_agency_no', 'name_english', 'executive_agency_no');
			elseif($read=='fund')$str.=Application_Form_FrmQuery::getFilterSelectDivisionAllcoated($table);
			elseif($read=='allocated')$str.=Application_Form_FrmQuery::getFilterAllocatedTable('table_detail');
			elseif($read=='wa')$str.=Application_Form_FrmQuery::getFilterSelect('wa', 'wa_no', 'wa_no', 'wa_no');
			elseif($read=='contract')$str.=Application_Form_FrmQuery::getFilterSelect('contract', 'contract_no', 'contract_no', 'contract_no');
			//elseif($read=='budgetsupport')$str.=Application_Form_FrmQuery::getFilterBudgetSupportTable('table_detail');
			//elseif($read=='actual')$str.=Application_Form_FrmQuery::getFilterActualDisbursementTable('table_detail');
			
    	}
    	return $str;
    }
    //filter table allocation detail
    public static function getTableAllocationDetail($fund_no)
    {        	
    	$form=new Budget_Form_FrmProjectDisbursement();              	
       	$form->setFundNo($fund_no);             	
       	$form->setFormDetailForAdding();        	    
       	Application_Model_Decorator::removeAllDecorator($form);
    	$str='
    		<table class="collape">
				<tr>
					<td rowspan="2" class="sub-tdheader">Categories</td>
					<td rowspan="2" class="sub-tdheader">Chapter</td>
					<td colspan="5" class="sub-tdheader">Amount in: <span id="currency_text"></span></td>					
				</tr>
				<tr>					
					<td class="sub-tdheader">Quater 1</td>
					<td class="sub-tdheader">Quater 2</td>
					<td class="sub-tdheader">Quater 3</td>
					<td class="sub-tdheader">Quater 4</td>
					<td class="total">Total</td>
				</tr>
				@detail
				<tr>
					<td class="sub-tdheader" colspan="2">Total</td>
					<td class="total-qauter"><span id="total_col_1" </span></td>
					<td class="total-qauter"><span id="total_col_2"> </span></td>
					<td class="total-qauter"><span id="total_col_3"> </span></td>
					<td class="total-qauter"><span id="total_col_4"> </span></td>
					<td class="total-qauter"><span id="total_col_row"> </span></td>
				</tr>
			</table>
    	';
    	$detail='';				
		for($i=1;$i<=$form->getCounter();$i++){ 				
			$detail.='<tr>
				<td class="sub-tdheader-left">'.$form->getElement('quater1_'.$i)->getLabel().$form->getElement('fund_allocation_'.$i).'</td>
				<td class="sub-tdheader-left">
					'.$form->getElement('code1_'.$i).'
					'.$form->getElement('code2_'.$i).'
					'.$form->getElement('code3_'.$i).'
					'.$form->getElement('code4_'.$i).'
				</td>
				<td class="quater-input">'.$form->getElement('quater1_'.$i).'</td>
				<td class="quater-input">'.$form->getElement('quater2_'.$i).'</td>
				<td class="quater-input">'.$form->getElement('quater3_'.$i).'</td>
				<td class="quater-input">'.$form->getElement('quater4_'.$i).'</td>
				<td class="total-qauter"><span id="total_row_'.$i.'"> </span></td>
			</tr>';				
		}
		$str.=$form->getElement('save_counter'); 
		$str=str_replace('@detail', $detail, $str);
		return $str;				 
    }
    //get table detail for budget support
    public static function getBudgetSupportCommitmentDetail($fund_no)
    {
    	$form=new Budget_Form_FrmProjectDisbursement();              	
       	$form->setFundNo($fund_no);             	
       	$form->setFormDetailForAdding();   
       	Application_Model_Decorator::removeAllDecorator($form);     	    
		$str='
    		<table class="collape">
				<tr>
					<td class="sub-tdheader">Categories</td>
					<td class="sub-tdheader">Chapter</td>
					<td class="sub-tdheader">Amount in: <span id="currency_text"></span></td>					
				</tr>				
				@detail
				<tr>
					<td class="sub-tdheader" colspan="2">Total</td>
					<td class="total-qauter"><span id="total_amount"> </span></td>
				</tr>
			</table>
    	';
    	$detail='';				
		for($i=1;$i<=$form->getCounter();$i++){ 				
			$detail.='<tr>
				<td class="sub-tdheader-left">'.$form->getElement('quater1_'.$i)->getLabel().$form->getElement('fund_allocation_'.$i).'</td>
				<td class="sub-tdheader-left" style="text-align:center">
					'.$form->getElement('code1_'.$i).'
					'.$form->getElement('code2_'.$i).'
					'.$form->getElement('code3_'.$i).'
					'.$form->getElement('code4_'.$i).'
				</td>
				<td class="quater-input"><input id="amount_'.$i.'" type="text" onkeyup="totalAmount('.$form->getCounter().');" onblur="totalAmount('.$form->getCounter().');" value="" name="amount_'.$i.'"></td>
			</tr>';				
		}
		$str.=$form->getElement('save_counter'); 
		$str=str_replace('@detail', $detail, $str);
		return $str;				 					 
    }

    //get bank swift code
    public static function getBankSwiftCode($swift_id){
        $str='
			$("#bank_name").change(function(){
				var value=$("#bank_name").val();
				$.post(
					"/jquery/query/swiftcode",
					{
					"bank_name" : value
				},
				function(data){
					if(data!=null) $("#"+"'.$swift_id.'").val(data[0].bank_swift_code);
					else  $("#"+'.$swift_id.').val("");   	
				},
				"json"
				);
			});					
        ';
        return $str;
    }
    
}

