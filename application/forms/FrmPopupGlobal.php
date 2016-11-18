<?php

class Application_Form_FrmPopupGlobal extends Zend_Dojo_Form
{
	public function init()
	{
		
	}
// 	public function getArrCsType(){
// 		$arr_type = array(
// 				"fi_cs_type" => "CS-TYPE",
// 				"fi_cs_zone" => "CS-ZONE",
// 				"fi_cs_items" => "CS-ITEMS",
// 				"fi_cs_subject" => "CS-SUBJECT",
// 				"fi_cs_living_situation" => "CS-LIVING",
// 				"fi_cs_ass_provided" => "CS-ASS-PROVIDED",
// 				"fi_cs_ass_requested" => "CS-ASS-REQUESTED",
// 				"fi_cs_current_condition" => "CS-CONDITION",
// 				"fi_cs_referal" => "CS-REFERAL",
// 				"fi_cs_type_caller" => "CS-TYPE-CALLER",
// 				"fi_cs_type_case" => "CS-TYPE-CASE",
// 				"fi_cs_ar_category" => "CS-AR-CATEGORY",
				 
// 				"cs-refresh" => "CS-REFRESH",
// 				"cs-member" => "CS-MEMBER"
// 		);
// 		return $arr_type;
// 	}
// 	public function getForm($action, $method, $url_cancel, $elements, $legend = null, $hidenvalues = null, $type=null, $page=null, $tableadd=null,$id_popup){
// 		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 		if($type!=null && $page!=null){
// 			$arr_type = $this->getArrCsType();
// 		}
		 
// 		$script= '<script type="text/javascript">
// 		jQuery(document).ready(function(){
// 		//binds form submission and fields to the validation engine
// 		jQuery("#frm").validationEngine();
// 	});
// 	</script>';
// 		$form="<div class='dijitHidden'>
// 				<div data-dojo-type='dijit.Dialog'  id='".$id_popup."' >";
// 		$form.= "<form  id=\"frm\" method='". $method ."' action='". $action ."' accept-charset=\"utf-8\" enctype=\"multipart/form-data\" style=\"position:relative;\"> ";
		 
// 		$form .= '<div class="btn" align="right">
// 		<button type="submit" class="positive">
// 		<img src="'.BASE_URL.'/images/icon/apply2.png" alt=""/>
// 		Save
// 		</button>
// 		<a href="'. $url_cancel .'" class="negative">
// 		<img src="'.BASE_URL.'/images/icon/cross.png" alt=""/>
// 		Cancel
// 		</a>
// 		</div>
// 		<fieldset>
// 		<legend>'.$tr->translate($legend).'</legend>
// 		<table>';
	
// 		foreach ($elements as $lbl => $element){
// 			$form .= '<tr>
// 			<td class="field">'. $tr->translate($lbl) .'</td>
// 			<td class="add-edit">'. $element .'</td>
// 			</tr>';
// 		}
// 		$form .= '</table>';
// 		if($tableadd!=null){
// 			$form .= $tableadd;
// 		}
// 		$form .= '</fieldset>';
// 		if(!empty($hidenvalues)){
// 			foreach ($hidenvalues as $i =>$h){
// 				$form .= $h;
// 			}
// 		}
		 
// 		$form .= "</form></div></div>";
// 		return $form ;
// 	}
	public function frmPopupClient(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frmco = new Group_Form_FrmClient();
		$frm = $frmco->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
					<div data-dojo-type="dijit.Dialog"  id="frm_client" >
					<form id="form_client" name="form_client" />';
				$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
							<tr>
							       <td>Is Group</td>
									<td>'.$frm->getElement('is_group').'</td>
									<td>Client N</td>
									<td>'.$frm->getElement('client_no').'</td>
								</tr>
								<tr>
									<td>Name Khmer</td>
									<td>'.$frm->getElement('name_kh').'</td>
									<td>Name Eng</td>
									<td>'.$frm->getElement('name_en').'</td>
								</tr>
								<tr>
									<td>Sex</td>
									<td>'.$frm->getElement('sex').'</td>
									<td>Status</td>
									<td>'.$frm->getElement('situ_status').'</td>
								</tr>
								<tr>
									<td>Province</td>
									<td>'.$frm->getElement('province').'</td>
									<td>District</td>
									<td>'.$frm->getElement('district').'</td>
								</tr>
								<tr>
									<td>Commune</td>
									<td>'.$frm->getElement('commune').'</td>
									<td>'.$tr->translate("Village").'</td>
									<td>'.$frm->getElement('village').'</td>
								</tr>
								<tr>
									<td>Street</td>
									<td>'.$frm->getElement('street').'</td>
									<td>'.$tr->translate("House N.").'</td>
									<td>'.$frm->getElement('house').'</td>
									
								</tr>
								<tr>
									<td>ID Type</td>
									<td>'.$frm->getElement('id_type').'</td>
									<td>'.$tr->translate("ID Card").'</td>
									<td>'.$frm->getElement('id_no').'</td>
								</tr>
								<tr>
									<td>'.$tr->translate("Phone").'</td>
									<td>'.$frm->getElement('phone').'</td>
									<td>'.$tr->translate("Spouse Name").'</td>
									<td>'.$frm->getElement('spouse').'</td>
								</tr>
								<tr>
									<td>'.$tr->translate("Status").'</td>
									<td>'.$frm->getElement('status').'</td>
									<td>'.$tr->translate("Note").'</td>
									<td>'.$frm->getElement('desc').'</td>
								</tr>
								<tr>
									<td colspan="4" align="center">
									<input type="button" value="Save" label="Save" dojoType="dijit.form.Button" 
										 iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewClient();"/>
									</td>
								</tr>
							</table>';	
							
		$str.='	</form>	</div>
				</div>';
		return $str;
	}
	public function frmPopupCO(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frmclient = new Other_Form_FrmCO();
		$frm = $frmclient->FrmAddCO();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="frm_co" >
					<form id="form_co" name="form_co" >';
			$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
					<tr>
						<td>Name Khmer</td>
						<td>'.$frm->getElement('namsdfse_kh').'</td>
					</tr>
					<tr>
						<td>First Name</td>
						<td>'.$frm->getElement('first_name').'</td>
					</tr>
					<tr>
						<td>Last Name</td>
						<td>'.$frm->getElement('last_name').'</td>
					</tr>
					<tr>
						<td>Sex</td>
						<td>'.$frm->getElement('co_sex').'</td>
					</tr>
					<tr>
						<td>Tel</td>
						<td>'.$frm->getElement('tel').'</td>
					</tr>
					<tr>
						<td>Email</td>
						<td>'.$frm->getElement('email').'</td>
					</tr>
					<tr>
						<td>Address</td>
						<td>'.$frm->getElement('address').'</td>
					</tr>
					<tr>
						<td colspan="4" align="center">
						<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
						iconClass="dijitEditorIcon dijitEditorIconSave" onclick="AddNewCo();"/>
						</td>
					</tr>						
		       </table>';
		$str.='</form>	</div>
		  </div>';
		return $str;								
	}
	public function frmPopupZone(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frmzone = new Other_Form_FrmZone();
		$frm = $frmzone->FrmAddZone();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="frm_zone" >
			<form id="form_zone" dojoType="dijit.form.Form" method="post" enctype="application/x-www-form-urlencoded">
				<script type="dojo/method" event="onSubmit">
					if(this.validate()) {
						return true;
					}else {
						return false;
					}
		      </script>';
			$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
					<tr>
						<td>Zone Name</td>
						<td>'.$frm->getElement('zone_name').'</td>
					</tr>
					<tr>
						<td>Zone Number</td>
						<td>'.$frm->getElement('zone_number').'</td>
					</tr>
					<tr>
						<td colspan="4" align="center">
						<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
						iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewZone();"/>
						</td>
					</tr>
				</table>';
		$str.='</form>		</div>
		</div>';
		return $str;
	}
	public function frmPopupDistrict(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frm = new Other_Form_FrmDistrict();
		$frm = $frm->FrmAddDistrict();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="frm_district" >
				<form id="form_district" >';
		$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
					<tr>
						<td>District Name English</td>
						<td>'.$frm->getElement('pop_district_name').'</td>
					</tr>
								<tr>
						<td>Province Name English</td>
						<td>'.$frm->getElement('pop_district_namekh').'</td>
					</tr>
					<tr>
						<td>District Name Khmer</td>
						<td>'.$frm->getElement('province_names').'</td>
					</tr>
					
					<tr>
						<td colspan="2" align="center">
						<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
						iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewDistrict();"/>
						</td>
				    </tr>
				</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}
	public function frmPopupCommune(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="frm_commune" >
					<form id="form_commune" >';
			$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
					<tr>
						<td>Commune Name EN</td>
						<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="commune_nameen" name="commune_nameen" value="" type="text">'.'</td>
					</tr>
					<tr>
						<td>Commune KH</td>
						<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="commune_namekh" name="commune_namekh" value="" type="text">'.'</td>
					</tr>
					<tr>
						<td></td>
						<td>'.'<input dojoType="dijit.form.TextBox" required="true" class="fullside" id="district_nameen" name="district_nameen" value="" type="hidden">'.'</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
						<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
						iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewCommune();"/>
						</td>
					</tr>
				</table>';
		$str.='</form></div>
			</div>';
		return $str;
	}
	public function frmPopupVillage(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="frm_village" >
					<form id="form_village" dojoType="dijit.form.Form" method="post" enctype="application/x-www-form-urlencoded">
		 <script type="dojo/method" event="onSubmit">			
			if(this.validate()) {
				return true;
			} else {
				return false;
			}
        </script>
		';
		$str.='<table style="margin: 0 auto; width: 95%;" cellspacing="10">
					    <tr>
							<td>'.$tr->translate("VILLAGE_KH").'</td>
							<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" missingMessage="Invalid Module!" class="fullside" id="village_namekh" name="village_namekh" value="" type="text">'.'</td>
						</tr>
						<tr>
							<td>'.$tr->translate("VILLAGE_NAME").'</td>
							<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" missingMessage="Invalid Module!" class="fullside" id="village_name" name="village_name" value="" type="text">'.'</td>
						</tr>
						<tr>
							<td>'. $tr->translate("DISPLAY_BY").'</td>
							<td>'.'<select name="display" id="display" dojoType="dijit.form.FilteringSelect" class="fullside">
									    <option value="1" label="Name KH">Name KH</option>
									    <option value="2" label="Name EN">Name EN</option>
									</select>'.'</td>
						</tr>
						<tr>
							<td>'.'<input dojoType="dijit.form.TextBox" class="fullside" id="province_name" name="province_name" value="" type="hidden">
								<input dojoType="dijit.form.TextBox" id="district_name" name="district_name" value="" type="hidden">
							'.'</td>
							<td>'.'<input dojoType="dijit.form.TextBox" id="commune_name" name="commune_name" value="" type="hidden">'.'</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
											<input type="reset" value="សំអាត" label='.$tr->translate('CLEAR').' dojoType="dijit.form.Button" iconClass="dijitIconClear"/>
											<input type="button" value="save_close" name="save_close" label="'. $tr->translate('SAVE').'" dojoType="dijit.form.Button" 
												iconClass="dijitEditorIcon dijitEditorIconSave" Onclick="addVillage();"  />
							</td>
						</tr>
					</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}
	
	public function frmPopupclienttype(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frm = new Group_Form_FrmClient();
		$frm = $frm->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="frm_clienttype" >
					<form id="form_clienttype" >';
		$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
					<tr>
						<td>Document Type EN</td>
						<td>'.$frm->getElement('clienttype_nameen').'</td>
					</tr>
					<tr>
						<td>Document Type KH</td>
						<td>'.$frm->getElement('clienttype_namekh').'</td>
					</tr>
					
					<tr>
						<td colspan="2" align="center">
						<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
						iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewDocumentType();"/>
						</td>
					</tr>
				</table>';
		$str.='</form></div>
			</div>';
		return $str;
	}
	public function frmPopupLoanTye(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	
		$fm = new Other_Form_FrmVeiwType();
		$frm = $fm->FrmViewType();
		Application_Model_Decorator::removeAllDecorator($frm);
	
		$str='<div class="dijitHidden">
		<div data-dojo-type="dijit.Dialog"  id="frm_loantype" >
		<form id="form_loantype" dojoType="dijit.form.Form" method="post" enctype="application/x-www-form-urlencoded">
		<script type="dojo/method" event="onSubmit">
		if(this.validate()) {
		return true;
	} else {
	return false;
	}
	</script>
	';
		$str.='<table style="margin: 0 auto; width: 95%;" cellspacing="10">
		<tr>
		<td>'.$tr->translate("TITLE_KH").'</td>
		<td>'.$frm->getElement('title_kh').'</td>
		</tr>
		<tr>
		<td>'.$tr->translate("TITLE_EN").'</td>
		<td>'.$frm->getElement('title_en').'</td>
		</tr>
		<tr>
		<td>'. $tr->translate("DISPLAY_BY").'</td>
		<td>'.$frm->getElement('display_by').'</td>
		</tr>
		<tr>
		<td>'.$tr->translate("STATUS").'</td>
		<td>'. $frm->getElement('status').'</td>
		</tr>
		<tr>
		<td colspan="2" align="center">
		<input type="reset" value="សំអាត" label='.$tr->translate('CLEAR').' dojoType="dijit.form.Button" iconClass="dijitIconClear"/>
		<input type="button" value="save_close" name="save_close" label="'. $tr->translate('SAVE').'" dojoType="dijit.form.Button"
		iconClass="dijitEditorIcon dijitEditorIconSave" Onclick="addNewloanType();"  />
		</td>
		</tr>
		</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}
	public function frmPopupindividualclient(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$fm = new Group_Form_FrmClient();
		$frms = $fm->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frms);
		
		$str="<div class='dijitHidden'>
				<div data-dojo-type='dijit.Dialog'   id='frmpop_client' >
					<form id='addclient' dojoType='dijit.form.Form' method='post' enctype='application/x-www-form-urlencoded'>
		<script type='dojo/method' event='onSubmit'>
		if(this.validate()) {
		  return true;
	    } else {
	    return false;
	    }
	   </script>";
		$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
					<tr>
						<td>'.$tr->translate("NAME_KHMER").'</td>
						<td>'.$frms->getElement('name_kh').'</td>
						<td>'.$tr->translate("NAME_ENG").'</td>
						<td>'.$frms->getElement('name_en').'</td>		
						<td>'.$tr->translate("SEX").'</td>
						<td>'.$frms->getElement('sex').'</td>
					</tr>
					<tr>
						<td>'.$tr->translate("SITU_STATUS").'</td>
						<td>'.$frms->getElement('situ_status').'</td>	
						<td>'.$tr->translate("NATIONAL_ID").'</td>
						<td>'.$frms->getElement('client_d_type').'</td>	
						<td>'.$tr->translate("NUMBER").'</td>
						<td>'.$frms->getElement('national_id').'</td>	
					</tr>
					<tr>
						<td>'.$tr->translate("JOB_TYPE").'</td>
						<td>'.$frms->getElement('job').'</td>	
						<td>'.$tr->translate("PHONE").'</td>
						<td>'.$frms->getElement('phone').'</td>	
						<td>'.$tr->translate("DOB").'</td>
						<td>'.$frms->getElement('dob_client').'</td>	
					</tr>
					<tr>
						<td>'.$tr->translate("PROVINCE").'</td>
						<td>'.$frms->getElement('province').'</td>	
						<td>'.$tr->translate("DISTRICT").'</td>
						<td>'.$frms->getElement('district').'</td>
						<td>'.$frms->getElement('COMMUNE').'</td>
						<td>'.$frms->getElement('commune').'</td>		
					</tr>
					<tr>
						<td>'.$tr->translate('VILLAGE').'</td>
						<td>'.$frms->getElement('village').'</td>	
						<td>'.$tr->translate('STREET').'</td>
						<td>'.$frms->getElement('street').'</td>	
						<td>'.$tr->translate('HOUSE').'</td>
						<td>'.$frms->getElement('house').'</td>	
					</tr>
					<tr>
						<td colspan="6" align="center" colspan="3">
						<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
						iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewindividual();"/>
						</td>
					</tr>
				</table>';
		$str.='</form></div>
			</div>';
		return $str;
	}
	
	public function frmPopupDepartment(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frm = new Group_Form_FrmDepartment();
		$frm = $frm->FrmAddDepartment();
		Application_Model_Decorator::removeAllDecorator($frm);
		$str='<div class="dijitHidden">
				<div data-dojo-type="dijit.Dialog"  id="department" >
					<form id="frm_department" >';
		$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
					<tr>
						<td>'.$tr->translate("DEPARTMENT_EN").'</td>
						<td>'.$frm->getElement('department_en').'</td>
					</tr>
					<tr>
						<td>'.$tr->translate("DEPARTMENT_KH").'</td>
						<td>'.$frm->getElement('department_kh').'</td>
					</tr>
					<tr>
						<td>'.$tr->translate("DISPAY").'</td>
						<td>'.$frm->getElement('display_pop').'</td>
					</tr>
					<tr>
						<td>'.$tr->translate("STATUS").'</td>
						<td>'.$frm->getElement('status_pop').'</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
						<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
						iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewDepartment();"/>
						</td>
					</tr>
				</table>';
		$str.='</form></div>
			</div>';
		return $str;
	}
	public function frmPopupPropertyType(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
		<div data-dojo-type="dijit.Dialog"  id="frm_propertytype" >
			<form id="form_propertytype" >';
			$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
						<tr>
							<td>Title</td>
							<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="type_nameen" name="type_nameen" value="" type="text">'.'</td>
							</tr>
							<tr>
							<td colspan="2" align="center">
							<input type="button" value="Save" label="Save" dojoType="dijit.form.Button"
							iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewPropertytype();"/>
							</td>
						</tr>
			</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}
	
}

