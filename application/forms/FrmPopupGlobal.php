<?php

class Application_Form_FrmPopupGlobal extends Zend_Dojo_Form
{
	public function init()
	{
		
	}
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
									<td>ខ្មែរmer</td>
									<td>'.$frm->getElement('name_kh').'</td>
									<td>Englishg</td>
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
						<td>ខ្មែរmer</td>
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
						<td>'.$tr->translate('DISTRICT_KH').'</td>
						<td>'.$frm->getElement('pop_district_namekh').'</td>
					</tr>
					<tr>
						<td>'.$tr->translate('DISTRICT_ENG').'</td>
						<td>'.$frm->getElement('pop_district_name').'</td>
					</tr>
					<tr>
						<td>'.$tr->translate('PROVINCE').'</td>
						<td>'.$frm->getElement('province_names').'</td>
					</tr>
					
					<tr>
						<td colspan="2" align="center">
						<input type="button" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
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
			$str.='<table style="margin: 0 auto; width:500px;" cellspacing="7">
					<tr>
						<td>'.$tr->translate('COMMUNE_NAME_KH').'</td>
						<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="commune_namekh" name="commune_namekh" value="" type="text">'.'</td>
					</tr>
					<tr>
						<td>'.$tr->translate('COMUNE_NAME_EN').'</td>
						<td>'.'<input dojoType="dijit.form.ValidationTextBox" class="fullside" id="commune_nameen" name="commune_nameen" value="" type="text">'.'</td>
					</tr>
					<tr>
						<td></td>
						<td>'.'<input dojoType="dijit.form.TextBox" required="true" class="fullside" id="district_nameen" name="district_nameen" value="" type="hidden">'.'</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
						<input type="button" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
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
		$str.='<table style="margin: 0 auto;  width:500px" cellspacing="10">
					    <tr>
							<td>'.$tr->translate("VILLAGE_KH").'</td>
							<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" missingMessage="Invalid Module!" class="fullside" id="village_namekh" name="village_namekh" value="" type="text">'.'</td>
						</tr>
						<tr>
							<td>'.$tr->translate("VILLAGE_NAME").'</td>
							<td>'.'<input dojoType="dijit.form.ValidationTextBox" missingMessage="Invalid Module!" class="fullside" id="village_name" name="village_name" value="" type="text">'.'</td>
						</tr>
						<tr>
							<td>'. $tr->translate("DISPLAY_BY").'</td>
							<td>'.'<select name="display" id="display" dojoType="dijit.form.FilteringSelect" class="fullside">
									    <option value="1" label="ខ្មែរ">ខ្មែរ</option>
									    <option value="2" label="English">English</option>
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
						<input type="button" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
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
						<input type="button" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
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
						<input type="button" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
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
							<td>'.$tr->translate('PROPERTIESTYPE').'</td>
							<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="type_nameen" name="type_nameen" value="" type="text">'.'</td>
							</tr>
							<tr>
							<td colspan="2" align="center">
							<input type="button" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
							iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewPropertytype();"/>
							</td>
						</tr>
			</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}
	public function frmPopupLandblog(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
		<div data-dojo-type="dijit.Dialog"  id="frm_landblog" >
		<form id="form_landblog" >';
		$str.='<table style="margin: 0 auto; width: 100%;" cellspacing="7">
		<tr>
		<td>Title</td>
		<td>'.'<input dojoType="dijit.form.ValidationTextBox" required="true" class="fullside" id="title_kh" name="title_kh" value="" type="text">'.'</td>
		</tr>
		<tr>
		<td colspan="2" align="center">
		<input type="button" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
		iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewLandBlog();"/>
		</td>
		</tr>
		</table>';
		$str.='</form></div>
		</div>';
		return $str;
	}
	public function frmPopupCustomer(){
		$fm = new Property_Form_FrmClient();
		$frm = $fm->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frm);
		$db = new Application_Model_DbTable_DbGlobal();
		$client_type = $db->getclientdtype();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<div class="dijitHidden">
		<div data-dojo-type="dijit.Dialog"  id="frm_customer" style="width:1100px;">
		<form id="form_customer" >
		<script type="dojo/method" event="onSubmit">
		if(this.validate()) {
		  return true;
	    } else {
	    return false;
	    }
	   </script>
		';
		$str.='
		<table width="100%" style="margin-top: -5px;">
			<tr>
				<td style="overflow: scroll;overflow-y:hidden; width:1100px">
					<style>
					.dojoxGridSortNode{
						text-align: center;	
						height: 30px;		
					}
					.dijitTabPaneWrapper , .dijitContentPane,.dijitTabListContainer-top{max-width: 99% !important;}
				</style>
				<div id="mainTabContainer" style="max-width:100%; height: 500px;overflow-y:hidden;" dojoType="dijit.layout.TabContainer" region="center"  >
						<div style="width:100%;overflow-x:hidden;overflow-y:hidden;" dojoType="dijit.layout.ContentPane" title="'.$tr->translate('ADD_CLIENT').'" selected="true">
							<fieldset>	
							<legend><strong>'.$tr->translate("ADD_CLIENT").'</strong></legend>
							<table style="margin: 0 auto; width: 100%;" cellspacing="10">
								<tr>
									<td>'.$tr->translate("CLIENT_NUM").'</td>
									<td>'.$frm->getElement('client_no').'</td>
									<td>'.$tr->translate("AGE").'</td>
									<td>'.$frm->getElement('age').'</td>
									<td>'.$tr->translate("DOCMENT_TYPE").'</td>
									<td>
										<select dojoType="dijit.form.FilteringSelect"  class="fullside" id="client_d_type" name="client_d_type"  type="text">';
											if(!empty($client_type)){foreach($client_type as $row){
												$str.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
											}}
									$str.='</select>
									</td>
								</tr>
								<tr>
									<td>'.$tr->translate("CUSTOMER_NAME").'*</td>
									<td>'.$frm->getElement('name_kh').'</td>
									<td>'.$tr->translate("NATIONALITY").'</td>
									<td>'.$frm->getElement('nationality').'</td>
									<td>'.$tr->translate("NATIONAL_ID").'</td>
									<td>'.$frm->getElement('national_id').'</td>
								</tr>
								<tr>
									<td>'.$tr->translate("SEX").'</td>
									<td>'.$frm->getElement('customer_sex').'</td>
									<td>'.$tr->translate("PHONE").'</td>
									<td>'.$frm->getElement('cus_phone').'</td>
									<td>'.$tr->translate("Issue Date").'</td>
									<td>'.$frm->getElement('national_id_issue_date').'</td>
									
								</tr>
								<tr>
									
									<td>'.$tr->translate("EMAIL").'</td>
									<td>'.$frm->getElement('email').'</td>
									
								</tr>
								<tr>
									<td colspan="6" style="border-bottom: solid 1px #ccc;">'.$tr->translate("Current Address").'</td>
								</tr>
								<tr>
									<td colspan="6">'.$frm->getElement('current_address').'</td>
								</tr>
								<tr>
									<td colspan="6" style="border-bottom: solid 1px #ccc;">'.$tr->translate("NOTE").'</td>
								</tr>
								<tr>
									<td colspan="6">'.$frm->getElement('desc').'</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<div  dojoType="dijit.layout.ContentPane" style="overflow-x:hidden;overflow-y:hidden;" title="'.$tr->translate('RELEVANT_STAKEHOLDER').'" selected="false">
						<fieldset>
							<legend><strong>'.$tr->translate("RELEVANT_STAKEHOLDER").'</strong></legend>
								<table style="margin: 0 auto; width: 100%;" cellspacing="10">
									<tr>
										<td>'.$tr->translate("JOIN_NAME").'</td>
										<td>'.$frm->getElement('hname_kh').'</td>
										<td>'.$tr->translate("NATIONALITY").'</td>
										<td>'.$frm->getElement('p_nationality').'</td>
										<td>'.$tr->translate("DOCMENT_TYPE").'</td>
										<td>
										<select dojoType="dijit.form.FilteringSelect"  class="fullside" id="join_d_type" name="join_d_type"  type="text">';
											if(!empty($client_type)){foreach($client_type as $row){
												$str.='<option value="'.$row['id'].'">'.$row['name'].'</option>';
											}}
									$str.='</select>
										</td> 
									</tr>
									<tr>
										<td>'.$tr->translate("SEX").'</td>
										<td>'.$frm->getElement('ksex').'</td>
										<td>'.$tr->translate("PHONE").'</td>
										<td>'.$frm->getElement('lphone').'</td>
										<td>'.$tr->translate("NATIONAL_ID").'</td>
										<td>'.$frm->getElement('rid_no').'</td>
										
										
									</tr>
									<tr>
										<td>'.$tr->translate("AGE").'</td>
										<td>'.$frm->getElement('p_age').'</td>
										
										<td>'.$tr->translate("JOIN_TYPE").'</td>
										<td>'.$frm->getElement('is_type_of_relevant').'</td>
										<td>'.$tr->translate("Issue Date").'</td>
										<td>'.$frm->getElement('p_national_id_issue_date').'</td>
									</tr>
									<tr>
										<td>'.$tr->translate("SPOUSE_NAME").'</td>
										<td>'.$frm->getElement('arid_no').'</td>
										<td>'.$tr->translate("SPOUSE_NATION_ID").'</td>
										<td>'.$frm->getElement('reference_national_id').'</td>
									</tr>
								</table>
						</fieldset>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td  align="center">
				<input type="button" value="Save" label="'.$tr->translate("SAVE").'" dojoType="dijit.form.Button"
				iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addNewCustomer();"/>
			</td>
		</tr>
		';
		$str.='</form></div>
		</div>';
		return $str;
	}
}