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
							<input type="button" id="save_property" value="Save" label="'.$tr->translate('GO_SAVE').'" dojoType="dijit.form.Button"
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
	function getFooterReceipt(){
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$str='<table width="100%" celpadding="0" cellspacing="0" style="font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".'; font-size:11px;line-height: 15px;margin-top: 4px;">
				<tr>
					<td width="22%">';
						$str.='<span id="ft_branch_title_lb" style="font-family:'."'Khmer OS Muol Light'".'; font-size:16px;white-space:nowrap;padding-top:5px;">'.$tr->translate("BRAND_FOOTER_TITLE").'</span>
					</td>
					<td width="40%">
						<span id="ft_website_lb">'.$data["website"].'</span>
					</td>
					<td width="40%" align="right">
						<span id="ft_email_client_lb" style="font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".';">'.$data["email_client"].'</span>
					</td>
				</tr>
				<tr style="white-space:nowrap;">
					<td colspan="2" id="ft_address_lb">'.$data["footer_branch"].'
					</td>
					<td width="40%" align="right">
						<span id="ft_phone_lb" style="font-weight:bold;font-family:arial,Khmer OS Battambang;">'.$data["tel-client"].'</span>
					</td>
				</tr>
			</table>';
		return $str;
	}
// 	function getOfficailReceipt(){//SH
// 		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 		$key = new Application_Model_DbTable_DbKeycode();
// 		$data=$key->getKeyCodeMiniInv(TRUE);
	
// 		$footer = $this->getFooterReceipt();
	
// 		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
	
// 		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
// 		$last_name=$session_user->last_name;
// 		$username = $session_user->first_name;
// 		$user_id = $session_user->user_id;
// 		$usertype="";
// 		// 		$dbuser = new Application_Model_DbTable_DbUsers();
// 		// 		$userinfo = $dbuser->getUserInformationById($user_id);
// 		// 		$usertype = " (".$userinfo['user_typetitle'].")";
	
// 		$fiveStarReciept=0;
// 		if ($fiveStarReciept==1){
// 			$str='
// 			<style>
// 			span.postingdate {
// 			position: absolute;
// 			top: 237px;
// 			left: 14px;
// 		}
// 		span#lb_receipt {
// 		position: absolute;
// 		top: 204px;
// 		right: 106px;
// 		font-weight: bold;
// 		}
// 		span#lb_customer {
// 		position: absolute;
// 		top: 270px;
// 		left: 215px;
// 		font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
// 		}
// 		span#lb_customercode {
// 		position: absolute;
// 		top: 300px;
// 		left: 215px;
// 		}
// 		span#lbl_total_receive {
// 		position: absolute;
// 		top: 270px;
// 		right: 132px;
// 		font-weight: bold;
// 		}
// 		span#lable_chartotalreceipt {
// 		position: absolute;
// 		top: 326px;
// 		left: 215px;
// 		font-weight: bold;
// 		}
// 		span#lb_hourseno {
// 		position: absolute;
// 		top: 384px;
// 		right: -10px;
// 		display: block;
// 		min-width: 200px;
// 		text-align: left;
// 		}
// 		span#lb_descriptionall {
// 		position: absolute;
// 		top: 382px;
// 		left: 120px;
// 		}
			
// 		span#lbl_customer,span#lbl_usersale {
// 		position: absolute;
// 		top: 525px;
// 		display: block;
// 		min-width: 215px;
// 		text-align: center;
// 		font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
// 		}
// 		span#lbl_customer {
// 		left: 70px;
// 		}
// 		span#lbl_usersale {
// 		right: 91px;
// 		}
// 		</style>
// 		<div class="five-startreceipt" style=" font-size: 16px; font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';  color: #000; width: 21cm; height: 15cm;padding: 0px;margin: 0 auto;position: relative; margin-top:-18px;" >
// 		<div style="display: none;">
// 		<span id="projectlogo"></span>
// 		<span id="lbl_project"></span>
			
// 		<span id="lb_saleprice"></span>
// 		<span id="lbl_total_paid1"></span>
// 		<span id="lbl_balance"></span>
// 		<span id="lb_noted"></span>
			
			
// 		<span id="lb_amount"></span>
// 		<span id="lbl_paidtimes"></span>
// 		<span id="lb_interest"></span>
// 		<span id="lb_penalty"></span>
// 		<span id="lb_extrapayment"></span>
// 		<span id="lbl_totalpayment"></span>
// 		<span id="lb_buydate1"></span>
// 		<span id=lbl_paid_date1></span>
// 		<span id="lbl_paymenttype"></span>
// 		<span id="lbl_cheque"></span>
			
// 		</div>
// 		<span class="postingdate">Posting Date: <span id=lblpaid_date></span></span>
// 		<span id="lb_receipt"></span>
// 		<span id="lb_customer"></span>
// 		<span id="lb_customercode"></span>
// 		<span id="lbl_total_receive"></span>
// 		<span id="lable_chartotalreceipt"></span>
// 		<span id="lb_hourseno"></span>
// 		<span id="lb_descriptionall"></span>
	
// 		<span id="lbl_customer"></span>
// 		<span id="lbl_usersale">'.$last_name." ".$username.'</span>
// 		</div>
// 		';
// 		}else{
// 			$str='
// 			<div >
// 			<style>
// 			.label{ font-size: 22px;}
// 			.value{font:16px '."Khmer OS Battambang".';border: 1px solid #000; min-height: 29px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
// 			line-height: 29px;
// 			text-align: left;
// 		}
// 		span#lb_hourseno {
// 		overflow-wrap: break-word;
// 		white-space: normal;
// 		width: 200px;
// 		display: inline-block;
// 		line-height: 24px;
// 		}
// 		.print tr td{
// 		padding:1px 2px;
// 		}
// 		.khmer{font:14px '."Khmer OS Battambang".';}
// 		.one{white-space:nowrap;}
// 		.h{ margin-top: -10px;}
// 		.noted{white-space: pre-wrap;
// 		word-wrap: break-word;
// 		word-break: break-all;
// 		white-space: pre;
// 		font:12px '."Khmer OS Battambang".';
// 		border: 1px solid #000;
// 		line-height:20px;font-weight: normal !important;
// 		}
// 		table.receipt-titile tr td {
// 		font-size:16px;
// 		}
// 		table.receipt-titile tr td span {
// 		font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
// 		}
// 		table.receipt-titile tr td div span {
// 		line-height:10px;
// 		font-weight: bold;
// 		}
// 		#lb_receipt {
// 		font-weight: bold;
// 		}
// 		table.print.contentdata{
// 		width:100%;
// 		white-space: nowrap;
// 		font-size:16px;
// 		margin-top: -28px;
// 		font-family: Times New Roman,'."Khmer OS Battambang".';
// 		}
// 		table.print.contentdata tr{
// 		white-space: nowrap;
// 		}
// 		tr.receipt-row {
// 		white-space: nowrap;
// 		font-size: 14px;
// 		margin-top: -15px;
// 		}
// 		table.signature-table{
// 		font-size:14px;line-height: 18px;
// 		}
// 		table.comment-footer{
// 		margin-top:-5px
// 		}
// 		table.comment-footer tr td span.lbnote {
// 		text-decoration:underline;
// 		font-size: 12px;
// 		margin-top: -5px;
// 		}
// 		table.comment-footer tr td p.comment1{
// 		font-size: 11px;
// 		margin:-5px 0px -5px 0px !important;
// 		padding:0 !important;
// 		}
// 		table.comment-footer tr td span.comment{
// 		white-space: pre-line;
// 		font-size: 11px;
// 		margin-top: -5px;
// 		}
// 		</style>
// 		<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top:-12px;" class="print" cellspacing="0"  cellpadding="0" >
// 		<tr>
// 		<td colspan="6">
// 		<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
// 		<tr>
// 		<td id="projectlogo" width="35%">
// 		<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
// 		</td>
// 		<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃទទួលប្រាក់</span></u>
// 		<div ><span >OFFICIAL RECEIPT</span></div>
// 		</td>
// 		<td width="35%"></td>
// 		</tr>
// 		</table>
// 		</td>
// 		</tr>
// 		</table>
// 		<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
// 		<tr class="receipt-row"  >
// 		<td colspan="5"></td>
// 		<td align="right">
// 		<span id="lb_receipt" ></span>
// 		</td>
// 		</tr>
// 		<tr >
// 		<td style="display: none;">លេខកូដលក់</td>
// 		<td style="display: none;"><strong><label class="value"></label></strong></td>
// 		<td>គម្រោង</td>
// 		<td><strong><strong><label id="lbl_project" class="value">3</label></strong></td>
// 		<td>&nbsp;&nbsp;ប្រាក់ដើម</td>
// 		<td><strong><label id="lb_amount" class="value"></label></strong></td>
// 		<td>&nbsp;បង់លើកទី</td>
// 		<td><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
// 		</tr>
// 		<tr >
// 		<td>ឈ្មោះ​អតិថិជន </td>
// 		<td><strong><label id="lb_customer" class="value"></label></strong></td>
// 		<td>&nbsp;&nbsp; ការប្រាក់</td>
// 		<td><strong><label id="lb_interest" class="value">0.00</label></strong></td>
// 		<td>&nbsp; ប្រាក់ពិន័យ</td>
// 		<td><strong><label id="lb_penalty" class="value">0.00</label></strong></td>
// 		</tr>
// 		<tr >
// 		<td>'.$tr->translate("PROPERTY_CODE").'</td>
// 		<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
// 		<td>&nbsp;&nbsp;ប្រាក់បង់បន្ថែម</td>
// 		<td colspan="3"><strong><label id="lb_extrapayment" class="value">0.00</label></strong></td>
// 		</tr>
// 		<tr >
// 		<td width="10%">'.$tr->translate("HOUSE_PRICE").'</td>
// 		<td width="40%"><strong><label id="lb_saleprice" class="value"></label></strong></td>
// 		<td>&nbsp;&nbsp;ប្រាក់ត្រូវបង់</td>
// 		<td colspan="3"><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
// 		</tr>
// 		<tr>
// 		<td>ប្រាក់បានបង់សរុប</td>
// 		<td valign="top">
// 		<table width="100%" cellpadding="0" cellspacing="0">
// 		<tr>
// 		<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_total_paid1" class="value"></label></td>
// 		<td width="33%" style="white-space: nowrap;">ប្រាក់នៅសល់</td>
// 		<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_balance"></label></td>
// 		</tr>
// 		</table>
// 		</td>
// 		<td>&nbsp;&nbsp;ប្រាក់បានទទួល</td>
// 		<td colspan="3"><strong><label  class="value" style="font-weight:700; font-family: Arial,Helvetica,sans-serif;" id="lbl_total_receive"></label></strong></td>
// 		</tr>
// 		<tr >
// 		<td rowspan="2">សម្គាល់</td>
// 		<td rowspan="2" class="noted" valign="top"><label id="lb_noted"></label></td>
// 		<td>&nbsp;&nbsp;ថ្ងៃត្រូវបង់</td>
// 		<td><strong><label id="lb_buydate1" class="value"></label></strong></td>
// 		<td>&nbsp;ថ្ងៃទទួល</td>
// 		<td><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
// 		</tr>
// 		<tr >
// 		<td>&nbsp;ទូទាត់ជា</td>
// 		<td><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
// 		<td>&nbsp;&nbsp;លេខ</td>
// 		<td><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
// 		</tr>
// 		<tr >
// 		<td colspan="6" valign="top">
// 		<table class="signature-table" width="100%" border="0">
// 		<tr>
// 		<td width="30%" align="center">&nbsp;
// 		'.$data['customer_sign'].'
// 		</td>
// 		<td align="center" width="40%">
			
// 		</td>
// 		<td align="center" width="30%">
// 		'.$data['teller_sign'].'
// 		</td>
// 		</tr>
// 		<tr height="80px">
// 		<td colspan="3">&nbsp;
// 		</td>
// 		</tr>
// 		<tr>
// 		<td align="center">
// 		<label id="lbl_customer" ></label>
// 		</td>
// 		<td >&nbsp;</td>
	
// 		<td align="center" width="30%">
// 		<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
// 		</td>
	
// 		</tr>
// 		</table>
// 		</td>
// 		</tr>
// 		<tr style="font-size: 11px;">
// 		<td colspan="6" valign="top">
// 		<table class="comment-footer" width="100%" border="0" >
// 		<tr>
// 		<td width="10%">
// 		<span class="lbnote" style=""></span>
// 		</td>
// 		<td colspan="5">
// 		<p class="comment1">សម្គាល់ ៖ '.$data['comment'].'</p>
	
// 		</td>
// 		</tr>
// 		</table>
// 		</td>
// 		</tr>
// 		<tr style="line-height: 15px;font-size: 10px;">
// 		<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
// 		</tr>
// 		<tr style="line-height: 18px;font-size: 10px;">
// 		<td colspan="6" >
// 		'.$footer.'
// 		</td>
// 		</tr>
// 		</table>
// 		<div style="display: none;">
// 		<span id="lable_chartotalreceipt"></span>
// 		<span id="lblpaid_date"></span>
// 		<span id="lb_descriptionall"></span>
// 		<span id="lb_customercode"></span>
// 		</div>
// 		</div>
// 		';
// 		}
// 		return $str;
// 	}
	function getOfficailReceipt($_receipt_type=null){//general
	
		//$_receipt_type 1 = បង្កាន់ដៃទទួលប្រាក់កក់
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
		
		$footer = $this->getFooterReceipt();
		
		$baseurl=Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$usertype="";
		
		$reciept_type=RECEIPT_TYPE;
		
		if ($reciept_type==1){//for 5star
			$str='
				<style>
					span.postingdate {
					    position: absolute;
					    top: 160px;
					    left: 14px;
					}
					span#lb_receipt {
					    position: absolute;
					    top: 142px;
					    right: 106px;
					    font-weight: bold;
					}
					span#lb_customer {
					    position: absolute;
					    top: 190px;
					    left: 215px;
					    font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
					}
					span#lb_customercode {
					    position: absolute;
					    top: 212px;
					    left: 215px;
					}
					span#lbl_total_receive {
					    position: absolute;
					    top: 190px;
					    right: 132px;
					    font-weight: bold;
					}
					span#lable_chartotalreceipt {
					    position: absolute;
					    top: 235px;
					    left: 215px;
					    font-weight: bold;
					}
					span#lb_hourseno {
					    position: absolute;
					    top: 277px;
					    right: -10px;
					    display: block;
					    min-width: 200px;
					    text-align: left;
					}
					span#lb_descriptionall {
					    position: absolute;
					    top: 280px;
					    left: 80px;
					    font-size: 15px;
					}
					
					span#lbl_customer,span#lbl_usersale {
					    position: absolute;
					    top: 470px;
					    display: block;
					    min-width: 215px;
					    text-align: center;
					     font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
					}
					span#lbl_customer {
						left: 70px;
					}
					span#lbl_usersale {
						right: 91px;
					}
				</style>
				<div class="five-startreceipt" style=" font-size: 16px; font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';  color: #000; width: 21cm; height: 15cm;padding: 0px;margin: 0 auto;position: relative; margin-top:-18px;" >
				<div style="display: none;">
					<span id="projectlogo"></span>
					<span id="lbl_project"></span>
					
					<span id="lb_saleprice"></span>
					<span id="lbl_total_paid1"></span>
					<span id="lbl_balance"></span>
					<span id="lb_noted"></span>
					<label id="lbl_phone" class="value"></label>
					<label id="lbl_pricelabel" class="value" ></label>
					
					<span id="lb_amount"></span>
					<span id="lbl_paidtimes"></span>
					<span id="lb_interest"></span>
					<span id="lb_penalty"></span>
					<span id="lb_extrapayment"></span>
					<span id="lbl_totalpayment"></span>
					<span id="lb_buydate1"></span>
					<span id=lbl_paid_date1></span>
					<span id="lbl_paymenttype"></span>
					<span id="lbl_cheque"></span>
					'.$footer.'
					
					<span id="lbl_priceSoldBefore"></span>
					<span id="lbl_discountAmount"></span>
					<span id="lbl_discountPercent"></span>
					<span id="lb_forCompletedAmount"></span>
					<span id="lb_completedDate"></span>
					<span id="lbl_discountOther"></span>
					
					<span id="lb_agreement_date"></span>
					<span id="lb_pre_schedule_opt"></span>
					<span id="lbl_pre_percent_payment"></span>
					<span id="lbl_pre_amount_month"></span>
					<span id="lbl_pre_percent_installment"></span>
					<span id="lbl_pre_amount_year"></span>
					<span id="lbl_pre_fix_payment"></span>
					<span id="lable_chartotalreceipt_in_kh" ></span>
				</div>
				<span class="postingdate">Posting Date: <span id=lblpaid_date></span></span>
				<input type="hidden" dojoType="dijit.form.TextBox" value="0" name="is_showinstallment" id="is_showinstallment" />
				<span id="lb_receipt"></span>
				<span id="lb_customer"></span>
				<span id="lb_customercode"></span>
				<span id="lbl_total_receive"></span>
				<span id="lable_chartotalreceipt"></span>
				<span id="lb_hourseno"></span>
				<span id="lb_descriptionall"></span>
				
				<span id="lbl_customer"></span>
				<span id="lbl_usersale">'.$last_name." ".$username.'</span>
			</div>
			';
		}elseif ($reciept_type==2){//for phnom mease
			$watermark = "background:url('$baseurl/images/phnommeaswatermark.jpg')";
			$key = new Application_Model_DbTable_DbKeycode();
			$data=$key->getKeyCodeMiniInv(TRUE);
			
			$str='
			<div >
			<style>
			.label{ font-size: 22px;}
			.value{font:16px '."Khmer OS Content".';border: 1px solid #000; min-height: 38px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
			line-height: 35px;
			text-align: left;
			}
			span#lb_hourseno {
			overflow-wrap: break-word;
			white-space: normal;
			width: 200px;
			display: inline-block;
			line-height: 24px;
			}
			.print tr td{
			padding:3px 4px;
			}
			.khmer{font:14px '."Khmer OS Content".';}
			.one{white-space:nowrap;}
			.h{ margin-top: -10px;}
			.noted{white-space: pre-wrap;
			word-wrap: break-word;
			word-break: break-all;
			white-space: pre;
			font:12px '."Khmer OS Content".';
			border: 1px solid #000;
			line-height:20px;font-weight: normal !important;
			min-height:50px;
			}
			table.receipt-titile tr td {
			font-size:18px;
			}
			table.receipt-titile tr td span {
			font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
			color:#036e97;
			}
			table.receipt-titile tr td div span {
			line-height:38px;
			font-weight: bold;
			color:#f7c25a;
			}
			#lb_receipt {
			font-weight: bold;
			}
			table.print.contentdata{
			width:100%;
			white-space: nowrap;
			font-size:16px;
			margin-top: -28px;
			font-family: Times New Roman,'."Khmer OS Content".';
			}
			table.print.contentdata tr{
			white-space: nowrap;
			}
			tr.receipt-row {
				white-space: nowrap;
				font-size: 14px;
				margin-top: 10px;
			}
			table.signature-table{
				font-size:16px;
				line-height: 20px;
			}
			table.comment-footer{
			margin-top:-5px
			}
			table.comment-footer tr td span.lbnote {
			text-decoration:underline;
			font-size: 14px;
			margin-top: -5px;
			}
			table.comment-footer tr td p.comment1{
			font-size: 12px;
			margin:10px 0px 5px 0px !important;
			padding:0 !important;
			}
			table.comment-footer tr td span.comment{
			white-space: pre-line;
			font-size: 12px;
			margin-top: 5px;
			}
			#printfooter {
			    position: absolute;
			    bottom: 0;
			    position: fixed;
			    display: block ;
			    font-size:14px;
			    border-top:2px solid #000;
			    width:100%;
			    text-align:center;
			}
			</style>
			<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;" class="print" cellspacing="0"  cellpadding="0" >
			<tr>
			<td colspan="6">
			<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
			<tr>
			<td id="projectlogo" width="35%">
				<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
			</td>
			<td width="30%"  align="center"><span>បង្កាន់ដៃទទួលប្រាក់</span>
			<div ><span >OFFICIAL RECEIPT</span></div>
				<img style="height:15px; max-width: 100%;margin-top:10px;" src="'.$baseurl.'/images/style.png" />
			</td>
			<td width="35%"></td>
			</tr>
			</table>
			</td>
			</tr>
			</table>
			<div class="displayfirst" style="">
				<div id="watermark" style="top:-55;opacity:0.2;position:fixed;z-index:-1;display: block;'.$watermark.' no-repeat center;background-size: 70%;z-index: -1; width:100%;height:100%;left:15;" ></div>
			<table style="margin-top:10px;"  class="print contentdata" cellspacing="5px"  cellpadding="0" >
				<tr class="receipt-row">
					<td colspan="5"></td>
					<td align="right">
						<span id="lb_receipt" style="font-size:18px;"></span>
					</td>
				</tr>
				<tr >
					<td>ឈ្មោះ​អតិថិជន </td>
					<td colspan="2" width="30%"><strong><label id="lb_customer" class="value"></label></strong></td>
					<td>គម្រោង</td>
					<td colspan="2" width="30%"><strong><strong><label id="lbl_project" class="value">3</label></strong></td>
				</tr>
				<tr>
					<td>'.$tr->translate("PROPERTY_CODE").'</td>
					<td colspan="2"><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
					<td>លេខទូរសព្ទ</td>
					<td colspan="2"><strong><strong><label id="lbl_phone" class="value"></label></strong></td>
				</tr>
				<tr>
					<td>'.$tr->translate("ទិញក្នុងតម្លៃ").'</td>
					<td colspan="2"><strong><label id="lb_saleprice" class="value"></label></strong></td>
					<td>ជាអក្សរ</td>
					<td colspan="2"><label id="lbl_pricelabel" class="value" style="font-size:11px;"></label></td>
				</tr>
				
				<tr>
					<td>ប្រាក់ត្រូវបង់</td>
					<td colspan="2"><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
					<td>ប្រាក់បានទទួល</td>
					<td colspan="2"><strong><label  class="value" style="font-weight:700; font-family: Arial,Helvetica,sans-serif;" id="lbl_total_receive"></label></strong></td>
				</tr>
				<tr>
					<td >សរុបប្រាក់បានបង់</td>
					<td colspan="2"><label id="lbl_total_paid1" class="value"></label></td>
					<td>ប្រាក់នៅសល់</td>
					<td colspan="2"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_balance"></label></td>
				</tr>
				<tr >
					<td>បង់ជា</td>
					<td colspan="2"><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
					<td>លេខសែក</td>
					<td colspan="2"><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
				</tr>
				<tr>
					<td>គោលបំណង</td>
					<td colspan="2"><strong><label id="lbl_purpose" class="value"></label></strong></td>
					<td>បង់លើកទី</td>
					<td colspan="2"><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
				</tr>
				<tr>
					<td>សម្គាល់</td>
					<td colspan="5" class="noted" valign="top"><label id="lb_noted"></label></td>
				</tr>
				<tr height="40px">
					<td colspan="6"><strong>សម្រាប់ភ្ញៀវរំលស់</strong></td>
				</tr>
				<tr>
					<td>ប្រាក់ដើម</td>
					<td colspan="2"><strong><label id="lb_amount" class="value"></label></strong></td>
					<td>ការប្រាក់</td>
					<td colspan="2"><strong><label id="lb_interest" class="value">0.00</label></strong></td>
				</tr>
				<tr>
					<td>ប្រាក់បន្ថែមដើម</td>
					<td colspan="2"><label id="lb_extrapayment" class="value">0.00</label></td>
					<td>ប្រាក់ពិន័យ</td>
					<td colspan="2"><strong><label id="lb_penalty" class="value">0.00</label></strong></td>
				</tr>
				<tr>
					<td>ថ្ងៃត្រូវបង់</td>
					<td colspan="2"><label id="lb_buydate1" class="value"></label></td>
					<td>ផ្សេងៗ</td>
					<td colspan="2"><label id="lb_other" class="value"></label></td>
				</tr>
				<tr>
					<td colspan="6" align="right">
						<strong>ថ្ងៃទទួល &nbsp;&nbsp;<label id="lbl_paid_date1" ></label></strong>
					</td>
				</tr>
				<tr>
				<td colspan="6">
						<table class="signature-table" width="100%" border="0" cellspacing="10">
							<tr>
								<td width="30%">&nbsp;
								'.$data['account_sign'].'
								</td>
								<td align="center" width="40%">
								'.$data['customer_sign'].'
								</td>
								<td align="center" width="30%">
								'.$data['teller_sign'].'
								</td>
							</tr>
							<tr height="110px">
							<td colspan="3">&nbsp;
							</td>
						</tr>
						<tr>
							<td width="30%">&nbsp;</td>
							<td align="center" width="40%">
							<label id="lbl_customer" ></label>
							</td>
							<td align="center" width="30%">
							<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
							</td>
						
						</tr>
					</table>
				</td>
			</tr>
			
			<tr style="line-height: 18px;font-size: 10px;">
			<td colspan="6" >
				<div id="printfooter" style="padding-top:20px;">
	        		'.$data["tel-client"].'
	        	</div>
			</td>
			</tr>
			</table>
			</div>
			
			<div style="display: none;">
				<input type="hidden" dojoType="dijit.form.TextBox" value="1" name="is_showinstallment" id="is_showinstallment" />
				<span id="lable_chartotalreceipt"></span>
				<span id="lable_chartotalreceipt_in_kh" ></span>
				<span id="lblpaid_date"></span>
				<span id="lb_descriptionall"></span>
				<span id="lb_customercode"></span>	
				'.$footer.'
				
				<span id="lbl_priceSoldBefore"></span>
				<span id="lbl_discountAmount"></span>
				<span id="lbl_discountPercent"></span>
				<span id="lb_forCompletedAmount"></span>
				<span id="lb_completedDate"></span>
				<span id="lbl_discountOther"></span>
				
				<span id="lb_agreement_date"></span>
				<span id="lb_pre_schedule_opt"></span>
				<span id="lbl_pre_percent_payment"></span>
				<span id="lbl_pre_amount_month"></span>
				<span id="lbl_pre_percent_installment"></span>
				<span id="lbl_pre_amount_year"></span>
				<span id="lbl_pre_fix_payment"></span>
			</div>
			</div>';
			/*<tr style="font-size: 11px;">
				<td colspan="6" valign="top">
					<table class="comment-footer" width="100%" border="0" >
					<tr>
					<td width="10%">
					<span class="lbnote" style="">សម្គាល់ ៖</span>
					</td>
					<td colspan="5">
					<p class="comment1">'.$data['comment'].'</p>
					<span class="comment">'.$data['comment1'].'</span>
					</td>
					</tr>
				</table>
				</td>
			</tr>*/
			//<div id="printfooter" style="padding-top:20px;">
	        		//'.$data["tel-client"].'
	        	//</div>
		}elseif ($reciept_type==3){// for phnom penh tmey
			if (!empty($_receipt_type)){
				$str='
				<div >
					<style>
						.label{ font-size: 22px;}
						.value{font: 16px Khmer OS Battambang;
							border: 1px solid #000;
							min-height: 40px;
							padding: 0 2px;
							width: 100%;
							margin-right: 2px;
							display: block;
							line-height: 38px;
							text-align: left;
					}
					span#lb_hourseno {
						overflow-wrap: break-word;
						white-space: normal;
						width: 200px;
						display: inline-block;
						line-height: 24px;
					}
					.print tr td{
						padding:2px 2px;
					}
					.khmer{font:14px '."Khmer OS Battambang".';}
					.one{white-space:nowrap;}
					.h{ margin-top: -10px;}
					.noted{white-space: pre-wrap;
						word-wrap: break-word;
						word-break: break-all;
						white-space: pre;
						font:12px '."Khmer OS Battambang".';
						border: 1px solid #000;
						line-height:20px;font-weight: normal !important;
					}
					table.receipt-titile tr td {
						font-size:16px;
					}
					table.receipt-titile tr td span {
						font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
					}
					table.receipt-titile tr td div span {
						line-height:10px;
						font-weight: bold;
					}
					#lb_receipt {
						font-weight: bold;
					}
					table.print.contentdata{
						width:100%;
						white-space: nowrap;
						font-size:16px;
						margin-top: -18px;
						font-family: Times New Roman,'."Khmer OS Battambang".';
					}
					table.print.contentdata tr{
						white-space: nowrap;
					}
					tr.receipt-row {
						white-space: nowrap;
						font-size: 14px;
						margin-top: -15px;
					}
					table.signature-table{
						font-size:14px;line-height: 18px;
					}
					table.comment-footer{
						margin-top:-5px
					}
					table.comment-footer tr td span.lbnote {
						text-decoration:underline;
						font-size: 12px;
						margin-top: -5px;
					}
					table.comment-footer tr td p.comment1{
						font-size: 11px;
						margin:-5px 0px -5px 0px !important;
						padding:0 !important;
					}
					table.comment-footer tr td span.comment{
						white-space: pre-line;
						font-size: 11px;
						margin-top: -5px;
					}
					tr.schedule_installment td label,
					tr.schedule_step td label {
					    display: block;
					    white-space: pre-line;
					}
				</style>
				<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top: -15px;" class="print" cellspacing="0"  cellpadding="0" >
					<tr>
						<td colspan="6">
							<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
								<tr>
									<td id="projectlogo" width="35%">
										<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
									</td>
									<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃទទួលប្រាក់</span></u>
										<div ><span >OFFICIAL RECEIPT</span></div>
									</td>
									<td width="35%"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
					<tr class="receipt-row"  >
						<td colspan="5"></td>
						<td align="right">
							<span id="lb_receipt" ></span>
						</td>
					</tr>
					<tr >
						<td style="display: none;">លេខកូដលក់</td>
						<td style="display: none;"><strong><label class="value"></label></strong></td>
						<td>គម្រោង</td>
						<td><strong><strong><label id="lbl_project" class="value">3</label></strong></td>
						<td>&nbsp;&nbsp;បង់លើកទី</td>
						<td colspan="3"><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
					</tr>
					<tr >
						<td>ឈ្មោះ​អតិថិជន </td>
						<td><strong><label id="lb_customer" class="value"></label></strong></td>
						<td>&nbsp;&nbsp;ប្រាក់ត្រូវបង់</td>
						<td colspan="3"><strong><label id="lbl_totalpayment" class="value">0.00</label></strong></td>
					</tr>
					<tr >
						<td>'.$tr->translate("PROPERTY_CODE").'</td>
						<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
						<td>&nbsp;&nbsp;ប្រាក់បានទទួល</td>
						<td colspan="3"><strong><label id="lbl_total_receive" class="value">0.00</label></strong></td>
					</tr>
					<tr>
						<td>តម្លៃដើម</td>
						<td valign="top">
							<table width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_priceSoldBefore" class="value">$ 0.00</label></td>
									<td width="33%" style="white-space: nowrap;">បញ្ចុះជាសាច់ប្រាក់</td>
									<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_discountAmount">$ 0.00</label></td>
								</tr>
							</table>
						</td>
						<td>&nbsp;&nbsp;ថ្ងៃទទួល</td>
						<td colspan="3" ><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
					</tr>
					<tr>
						<td>បញ្ចុះជាភាគរយ</td>
						<td valign="top">
							<table width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_discountPercent" class="value">$ 0.00</label></td>
									<td width="33%" style="white-space: nowrap;">បញ្ចុះផ្សេងៗ</td>
									<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_discountOther">$ 0.00</label></td>
								</tr>
							</table>
						</td>
						<td>&nbsp;&nbsp;ប្រភេទទូទាត់</td>
						<td><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
						<td>&nbsp;លេខ</td>
						<td><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
					</tr>
					<tr >
						<td width="10%">'.$tr->translate("SOLD_PRICE").'</td>
						<td width="40%"><strong><label id="lb_saleprice" class="value"></label></strong></td>
						<td>&nbsp;&nbsp;'.$tr->translate("DATE_MAKE_AGREEEMNT").'</td>
						<td colspan="3"><strong><label id="lb_agreement_date" class="value"></label></strong></td>
					</tr>
					<tr>
						<td>ប្រាក់បានបង់សរុប</td>
						<td valign="top">
							<table width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_total_paid1" class="value"></label></td>
									<td width="33%" style="white-space: nowrap;">ប្រាក់នៅសល់</td>
									<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_balance"></label></td>
								</tr>
							</table>
						</td>
						<td>&nbsp;&nbsp;'.$tr->translate("TERM_CODITION").'</td>
						<td colspan="3"><label  class="value" id="lb_pre_schedule_opt"></label></td>
					</tr>
					<tr >
						<td rowspan="4" valign="top">'.$tr->translate("NOTE").'</td>
						<td rowspan="4" class="noted" valign="top"><label id="lb_noted" style="min-height: 80px;display: block;   white-space: pre-line;"></label></td>
					</tr>
					<tr class="schedule_installment">
						<td valign="top">&nbsp;&nbsp;'.$tr->translate("PRE_PERCENT_PAYMENT").'</td>
						<td colspan="3" valign="top">
							<table  width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_pre_percent_payment" class="value"></label></td>
									<td width="33%" style="white-space: nowrap;">'.$tr->translate("PRE_AMOUNT_MONTH").'</td>
									<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_pre_amount_month"></label></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr class="schedule_installment">
						<td valign="top">&nbsp;&nbsp;'.$tr->translate("PRE_PERCENT_INSTALLMENT").'</td>
						<td colspan="3" valign="top">
							<table  width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_pre_percent_installment" class="value"></label></td>
									<td width="33%" style="white-space: nowrap;">'.$tr->translate("PRE_AMOUNT_YEAR").'</td>
									<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_pre_amount_year"></label></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr class="schedule_installment">
						<td colspan="4" valign="top">
						&nbsp;
						</td>
					</tr>
					
					<tr valign="top" class="schedule_step">
						<td valign="top">&nbsp;&nbsp;'.$tr->translate("PRE_FIX_PAYMENT").'</td>
						<td colspan="3" valign="top">
							<label style="margin-left: -4px;" id="lbl_pre_fix_payment" class="value"></label>
						</td>
					</tr>
					<tr class="schedule_step">
						<td colspan="4" valign="top">
						&nbsp;
						</td>
					</tr>
					<tr class="schedule_step">
						<td colspan="4" valign="top">
						&nbsp;
						</td>
					</tr>
					<tr >
						<td colspan="6" valign="top">
							&nbsp;
						</td>
					</td>
					<tr >
						<td colspan="6" valign="top">
							<table class="signature-table" width="100%" border="0">
								<tr>
									<td align="center" width="40%">
									'.$data['customer_sign'].'
									</td>
									<td align="center" width="30%">
									'.$data['teller_sign'].'
									</td>
								</tr>
								<tr height="85px">
									<td colspan="2">&nbsp;
									</td>
								</tr>
								<tr>
									<td align="center" width="40%">
										<label id="lbl_customer" ></label>
									</td>
									<td align="center" width="30%">
										<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr style="font-size: 11px;">
						<td colspan="6" valign="top">
							<table class="comment-footer" width="100%" border="0" >
								<tr>
									<td width="10%" valign="top">
										<span class="lbnote" style="">សម្គាល់ ៖</span>
									</td>
								</tr>
								<tr>
									<td width="10%" valign="top">
									</td>
									<td colspan="5" valign="top">
										<span style="font-size: 12px;">ក្នុងករណីដែលអ្នកទិញមិនបានបង់ប្រាក់បន្ថែមតាមការសន្យាខាងលើនោះប្រាក់ដែលបានបង់នឹងទៅជាកម្មសិទ្ធរបស់អ្នកលក់ដោយស្វ័យប្រវត្តិ។</span>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr style="line-height: 15px;font-size: 10px;">
						<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
					</tr>
					<tr style="line-height: 18px;font-size: 10px;">
						<td colspan="6" >
						'.$footer.'
						</td>
					</tr>
				</table>
				<div style="display: none;">
					<input type="hidden" dojoType="dijit.form.TextBox" value="0" name="is_showinstallment" id="is_showinstallment" />
					<label id="lbl_phone" class="value"></label>
					<label id="lbl_pricelabel" class="value" ></label>
					<span id="lable_chartotalreceipt"></span>
					<span id="lable_chartotalreceipt_in_kh" ></span>
					<span id="lblpaid_date"></span>
					<span id="lb_descriptionall"></span>
					<span id="lb_customercode"></span>
					
					<span id="lb_amount"></span>
					<span id="lb_extrapayment"></span>
					<span id="lb_interest"></span>
					
					<span id="lb_forCompletedAmount"></span>
					<span id="lb_completedDate"></span>
					<span id="lb_buydate1"></span>
					<span id="lb_penalty"></span>
				</div>
				</div>
				';
			}else{
				$str='
				<div >
				<style>
					.label{ font-size: 22px;}
					.value{font:16px '."Khmer OS Battambang".';border: 1px solid #000; min-height: 29px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
						line-height: 29px;
						text-align: left;
					}
					span#lb_hourseno {
						overflow-wrap: break-word;
						white-space: normal;
						width: 200px;
						display: inline-block;
						line-height: 24px;
					}
					.print tr td{
						padding:1px 2px;
					}
					.khmer{font:14px '."Khmer OS Battambang".';}
					.one{white-space:nowrap;}
					.h{ margin-top: -10px;}
					.noted{white-space: pre-wrap;
						word-wrap: break-word;
						word-break: break-all;
						white-space: pre;
						font:12px '."Khmer OS Battambang".';
						border: 1px solid #000;
						line-height:20px;font-weight: normal !important;
					}
					table.receipt-titile tr td {
						font-size:16px;
					}
					table.receipt-titile tr td span {
						font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
					}
					table.receipt-titile tr td div span {
						line-height:10px;
						font-weight: bold;
					}
					#lb_receipt {
						font-weight: bold;
					}
					table.print.contentdata{
						width:100%;
						white-space: nowrap;
						font-size:16px;
						margin-top: -28px;
						font-family: Times New Roman,'."Khmer OS Battambang".';
					}
					table.print.contentdata tr{
						white-space: nowrap;
					}
					tr.receipt-row {
						white-space: nowrap;
						font-size: 14px;
						margin-top: -15px;
					}
					table.signature-table{
						font-size:14px;line-height: 18px;
					}
					table.comment-footer{
						margin-top:-5px
					}
					table.comment-footer tr td span.lbnote {
						text-decoration:underline;
						font-size: 12px;
						margin-top: -5px;
					}
					table.comment-footer tr td p.comment1{
						font-size: 11px;
						margin:-5px 0px -5px 0px !important;
						padding:0 !important;
						line-height: 14px;
					}
					table.comment-footer tr td span.comment{
						white-space: pre-line;
						font-size: 11px;
						margin-top: 5px;
						display: block;
						line-height: 14px;
					}
				</style>
				<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top: -15px;" class="print" cellspacing="0"  cellpadding="0" >
					<tr>
						<td colspan="6">
							<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
							<tr>
								<td id="projectlogo" width="35%">
									<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
								</td>
								<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃទទួលប្រាក់</span></u>
									<div ><span >OFFICIAL RECEIPT</span></div>
								</td>
								<td width="35%"></td>
							</tr>
							</table>
						</td>
					</tr>
				</table>
				<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
					<tr class="receipt-row"  >
						<td colspan="5"></td>
						<td align="right">
							<span id="lb_receipt" ></span>
						</td>
					</tr>
					<tr >
						<td style="display: none;">លេខកូដលក់</td>
						<td style="display: none;"><strong><label class="value"></label></strong></td>
						<td>គម្រោង</td>
						<td><strong><strong><label id="lbl_project" class="value">3</label></strong></td>
						<td>&nbsp;&nbsp;ប្រាក់ដើម</td>
						<td><strong><label id="lb_amount" class="value"></label></strong></td>
						<td>&nbsp;បង់លើកទី</td>
						<td><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
					</tr>
					<tr >
						<td>ឈ្មោះ​អតិថិជន </td>
						<td><strong><label id="lb_customer" class="value"></label></strong></td>
						<td>&nbsp;&nbsp; ការប្រាក់</td>
						<td><strong><label id="lb_interest" class="value">0.00</label></strong></td>
						<td>&nbsp; ប្រាក់ពិន័យ</td>
						<td><strong><label id="lb_penalty" class="value">0.00</label></strong></td>
					</tr>
					<tr >
						<td>'.$tr->translate("PROPERTY_CODE").'</td>
						<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
						<td>&nbsp;&nbsp;ប្រាក់បង់បន្ថែម</td>
						<td colspan="3"><strong><label id="lb_extrapayment" class="value">0.00</label></strong></td>
					</tr>
					<tr >
						<td width="10%">'.$tr->translate("SOLD_PRICE").'</td>
						<td width="40%"><strong><label id="lb_saleprice" class="value"></label></strong></td>
						<td>&nbsp;&nbsp;ប្រាក់ត្រូវបង់</td>
						<td colspan="3"><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
					</tr>
					<tr>
						<td>ប្រាក់បានបង់សរុប</td>
						<td valign="top">
							<table width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_total_paid1" class="value"></label></td>
									<td width="33%" style="white-space: nowrap;">ប្រាក់នៅសល់</td>
									<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_balance"></label></td>
								</tr>
							</table>
						</td>
						<td>&nbsp;&nbsp;ប្រាក់បានទទួល</td>
						<td colspan="3"><strong><label  class="value" style="font-weight:700; font-family: Arial,Helvetica,sans-serif;" id="lbl_total_receive"></label></strong></td>
					</tr>
					<tr >
						<td rowspan="2">សម្គាល់</td>
						<td rowspan="2" class="noted" valign="top"><label id="lb_noted"></label></td>
						<td>&nbsp;&nbsp;ថ្ងៃត្រូវបង់</td>
						<td><strong><label id="lb_buydate1" class="value"></label></strong></td>
						<td>&nbsp;ថ្ងៃទទួល</td>
						<td><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
					</tr>
					<tr >
						<td>&nbsp;បង់ជា</td>
						<td><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
						<td>&nbsp;&nbsp;លេខ</td>
						<td><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
					</tr>
				<tr >
					<td colspan="6" valign="top">
						<table class="signature-table" width="100%" border="0">
							<tr>
								<td width="30%">&nbsp;
								'.$data['account_sign'].'
								</td>
								<td align="center" width="40%">
								'.$data['customer_sign'].'
								</td>
								<td align="center" width="30%">
								'.$data['teller_sign'].'
								</td>
							</tr>
							<tr height="85px">
								<td colspan="3">&nbsp;
								</td>
							</tr>
							<tr>
								<td width="30%">&nbsp;</td>
								<td align="center" width="40%">
								<label id="lbl_customer" ></label>
								</td>
								<td align="center" width="30%">
								<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
								</td>
								
							</tr>
						</table>
					</td>
				</tr>
				<tr style="font-size: 11px;">
					<td colspan="6" valign="top">
						<table class="comment-footer" width="100%" border="0" >
							<tr>
								<td width="10%">
									<span class="lbnote" style="">សម្គាល់ ៖</span>
								</td>
								<td colspan="5">
									<p class="comment1">'.$data['comment'].'</p>
									<span class="comment">'.$data['comment1'].'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="line-height: 15px;font-size: 10px;">
					<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
				</tr>
				<tr style="line-height: 18px;font-size: 10px;">
					<td colspan="6" >
					'.$footer.'
					</td>
				</tr>
				</table>
					<div style="display: none;">
						<input type="hidden" dojoType="dijit.form.TextBox" value="0" name="is_showinstallment" id="is_showinstallment" />
						<label id="lbl_phone" class="value"></label>
						<label id="lbl_pricelabel" class="value" ></label>
						<span id="lable_chartotalreceipt"></span>
						<span id="lable_chartotalreceipt_in_kh" ></span>
						<span id="lblpaid_date"></span>
						<span id="lb_descriptionall"></span>
						<span id="lb_customercode"></span>
						
						<span id="lbl_priceSoldBefore"></span>
						<span id="lbl_discountAmount"></span>
						<span id="lbl_discountPercent"></span>
						<span id="lb_forCompletedAmount"></span>
						<span id="lb_completedDate"></span>
						<span id="lbl_discountOther"></span>
						
						<span id="lb_agreement_date"></span>
						<span id="lb_pre_schedule_opt"></span>
						<span id="lbl_pre_percent_payment"></span>
						<span id="lbl_pre_amount_month"></span>
						<span id="lbl_pre_percent_installment"></span>
						<span id="lbl_pre_amount_year"></span>
						<span id="lbl_pre_fix_payment"></span>
					</div>
				</div>
				';
			}
		}elseif ($reciept_type==4){
			$str='
			<div >
			<style>
				.label{ font-size: 22px;}
				.value{font:16px '."Khmer OS Battambang".';border: 1px solid #000; min-height: 29px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
					line-height: 29px;
					text-align: left;
				}
				span#lb_hourseno {
					overflow-wrap: break-word;
					white-space: normal;
					width: 200px;
					display: inline-block;
					line-height: 24px;
				}
				.print tr td{
					padding:1px 2px;
				}
				.khmer{font:14px '."Khmer OS Battambang".';}
				.one{white-space:nowrap;}
				.h{ margin-top: -10px;}
				.noted{white-space: pre-wrap;
					word-wrap: break-word;
					word-break: break-all;
					white-space: pre;
					font:12px '."Khmer OS Battambang".';
					border: 1px solid #000;
					line-height:20px;font-weight: normal !important;
				}
				table.receipt-titile tr td {
					font-size:16px;
				}
				table.receipt-titile tr td span {
					font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
				}
				table.receipt-titile tr td div span {
					line-height:10px;
					font-weight: bold;
				}
				#lb_receipt {
					font-weight: bold;
				}
				table.print.contentdata{
					width:100%;
					white-space: nowrap;
					font-size:16px;
					margin-top: -28px;
					font-family: Times New Roman,'."Khmer OS Battambang".';
				}
				table.print.contentdata tr{
					white-space: nowrap;
				}
				tr.receipt-row {
					white-space: nowrap;
					font-size: 14px;
					margin-top: -15px;
				}
				table.signature-table{
					font-size:14px;line-height: 18px;
				}
				table.comment-footer{
					margin-top:-5px
				}
				table.comment-footer tr td span.lbnote {
					text-decoration:underline;
					font-size: 12px;
					margin-top: -5px;
				}
				table.comment-footer tr td p.comment1{
					font-size: 11px;
					margin:-5px 0px -5px 0px !important;
					padding:0 !important;
				}
				table.comment-footer tr td span.comment{
					white-space: pre-line;
					font-size: 11px;
					margin-top: -5px;
				}
			</style>
			<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top: -15px;" class="print" cellspacing="0"  cellpadding="0" >
				<tr>
					<td colspan="6">
						<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
							<tr>
								<td id="projectlogo" width="35%">
									<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
								</td>
								<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃទទួលប្រាក់</span></u>
									<div ><span >OFFICIAL RECEIPT</span></div>
								</td>
								<td width="35%"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
				<tr class="receipt-row"  >
					<td colspan="5"></td>
					<td align="right">
						<span id="lb_receipt" ></span>
					</td>
				</tr>
				<tr >
					<td style="display: none;">លេខកូដលក់</td>
					<td style="display: none;"><strong><label class="value"></label></strong></td>
					<td>គម្រោង</td>
					<td><strong><strong><label id="lbl_project" class="value">3</label></strong></td>
					<td>&nbsp;&nbsp;ប្រាក់ដើម</td>
					<td><strong><label id="lb_amount" class="value"></label></strong></td>
					<td>&nbsp;បង់លើកទី</td>
					<td><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
				</tr>
				<tr >
					<td>ឈ្មោះ​អតិថិជន </td>
					<td><strong><label id="lb_customer" class="value"></label></strong></td>
					<td>&nbsp;&nbsp; ការប្រាក់</td>
					<td><strong><label id="lb_interest" class="value">0.00</label></strong></td>
					<td>&nbsp; ប្រាក់ពិន័យ</td>
					<td><strong><label id="lb_penalty" class="value">0.00</label></strong></td>
				</tr>
				<tr >
					<td>'.$tr->translate("PROPERTY_CODE").'</td>
					<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
					<td>&nbsp;&nbsp;ប្រាក់បង់បន្ថែម</td>
					<td colspan="3"><strong><label id="lb_extrapayment" class="value">0.00</label></strong></td>
				</tr>
				<tr >
					<td width="10%">'.$tr->translate("SOLD_PRICE").'</td>
					<td width="40%"><strong><label id="lb_saleprice" class="value"></label></strong></td>
					<td>&nbsp;&nbsp;ប្រាក់ត្រូវបង់</td>
					<td colspan="3"><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
				</tr>
				<tr>
					<td>ប្រាក់បានបង់សរុប</td>
					<td valign="top">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_total_paid1" class="value"></label></td>
								<td width="33%" style="white-space: nowrap;">ប្រាក់នៅសល់</td>
								<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_balance"></label></td>
							</tr>
						</table>
					</td>
					<td>&nbsp;&nbsp;ប្រាក់បានទទួល</td>
					<td colspan="3">
						<strong>
							<label  class="value" style="font-weight:700; font-family: '."'Khmer OS Battambang'".',Arial,Helvetica,sans-serif;" >
							<span id="lbl_total_receive"></span>
							<span style="font-weight: 200; font-size: 12px;">(<span id="lable_chartotalreceipt_in_kh" ></span>)</span>
							</label>
						</strong>
					</td>
				</tr>
				<tr >
					<td rowspan="2">សម្គាល់</td>
					<td rowspan="2" class="noted" valign="top"><label id="lb_noted"></label></td>
					<td>&nbsp;&nbsp;ថ្ងៃត្រូវបង់</td>
					<td><strong><label id="lb_buydate1" class="value"></label></strong></td>
					<td>&nbsp;ថ្ងៃទទួល</td>
					<td><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
				</tr>
				<tr >
					<td>&nbsp;បង់ជា</td>
					<td><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
					<td>&nbsp;&nbsp;លេខ</td>
					<td><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
				</tr>
				<tr >
					<td colspan="6" valign="top">
						<table class="signature-table" width="100%" border="0">
							<tr>
								<td width="30%">&nbsp;
								'.$data['account_sign'].'
								</td>
								<td align="center" width="40%">
								'.$data['customer_sign'].'
								</td>
								<td align="center" width="30%">
								'.$data['teller_sign'].'
								</td>
							</tr>
							<tr height="85px">
								<td colspan="3">&nbsp;
								</td>
							</tr>
							<tr>
								<td width="30%">&nbsp;</td>
								<td align="center" width="40%">
								<label id="lbl_customer" ></label>
								</td>
								<td align="center" width="30%">
								<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="font-size: 11px;">
					<td colspan="6" valign="top">
						<table class="comment-footer" width="100%" border="0" >
							<tr>
								<td width="10%">
									<span class="lbnote" style="">សម្គាល់ ៖</span>
								</td>
								<td colspan="5">
									<p class="comment1">'.$data['comment'].'</p>
									<span class="comment">'.$data['comment1'].'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="line-height: 15px;font-size: 10px;">
					<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
				</tr>
				<tr style="line-height: 18px;font-size: 10px;">
					<td colspan="6" >
						'.$footer.'
					</td>
				</tr>
			</table>
			<div style="display: none;">
			<input type="hidden" dojoType="dijit.form.TextBox" value="0" name="is_showinstallment" id="is_showinstallment" />
			<label id="lbl_phone" class="value"></label>
			<label id="lbl_pricelabel" class="value" ></label>
			<span id="lable_chartotalreceipt" ></span>
			<span id="lblpaid_date"></span>
			<span id="lb_descriptionall"></span>
			<span id="lb_customercode"></span>
			
			<span id="lbl_priceSoldBefore"></span>
			<span id="lbl_discountAmount"></span>
			<span id="lbl_discountPercent"></span>
			<span id="lb_forCompletedAmount"></span>
			<span id="lb_completedDate"></span>
			<span id="lbl_discountOther"></span>
			
			<span id="lb_agreement_date"></span>
			<span id="lb_pre_schedule_opt"></span>
			<span id="lbl_pre_percent_payment"></span>
			<span id="lbl_pre_amount_month"></span>
			<span id="lbl_pre_percent_installment"></span>
			<span id="lbl_pre_amount_year"></span>
			<span id="lbl_pre_fix_payment"></span>
			</div>
			</div>
			';
		}else{
			$str='
			<div >
			<style>
				.label{ font-size: 22px;}
				.value{font:16px '."Khmer OS Battambang".';border: 1px solid #000; min-height: 29px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
					line-height: 29px;
					text-align: left;
				}
				span#lb_hourseno {
					overflow-wrap: break-word;
					white-space: normal;
					width: 200px;
					display: inline-block;
					line-height: 24px;
				}
				.print tr td{
					padding:1px 2px;
				}
				.khmer{font:14px '."Khmer OS Battambang".';}
				.one{white-space:nowrap;}
				.h{ margin-top: -10px;}
				.noted{
					white-space: pre-wrap;
					word-wrap: break-word;
					word-break: break-all;
					/*white-space: pre;*/
					font:11px '."Khmer OS Battambang".';
					border: 1px solid #000;
					line-height:14px;font-weight: normal !important;
					padding: 4px 2px !important;				 
				}
				table.receipt-titile tr td {
					font-size:16px;
				}
				table.receipt-titile tr td span {
					font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
				}
				table.receipt-titile tr td div span {
					line-height:10px;
					font-weight: bold;
				}
				#lb_receipt {
					font-weight: bold;
				}
				table.print.contentdata{
					width:100%;
					white-space: nowrap;
					font-size:16px;
					margin-top: -28px;
					font-family: Times New Roman,'."Khmer OS Battambang".';
				}
				table.print.contentdata tr{
					white-space: nowrap;
				}
				tr.receipt-row {
					white-space: nowrap;
					font-size: 14px;
					margin-top: -15px;
				}
				table.signature-table{
					font-size:14px;line-height: 18px;
				}
				table.comment-footer{
					margin-top:-5px
				}
				table.comment-footer tr td span.lbnote {
					text-decoration:underline;
					font-size: 12px;
					margin-top: -5px;
				}
				table.comment-footer tr td p.comment1{
					font-size: 11px;
					margin:-5px 0px -5px 0px !important;
					padding:0 !important;
				}
				table.comment-footer tr td span.comment{
					white-space: pre-line;
					font-size: 11px;
					margin-top: -5px;
				}
			</style>
			<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top: -15px;" class="print" cellspacing="0"  cellpadding="0" >
				<tr>
					<td colspan="6">
						<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
							<tr>
								<td id="projectlogo" width="35%">
									<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
								</td>
								<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃទទួលប្រាក់</span></u>
									<div ><span >OFFICIAL RECEIPT</span></div>
								</td>
								<td width="35%"></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
				<tr class="receipt-row"  >
					<td colspan="5"></td>
					<td align="right">
						<span id="lb_receipt" ></span>
					</td>
				</tr>
				<tr >
					<td style="display: none;">លេខកូដលក់</td>
					<td style="display: none;"><strong><label class="value"></label></strong></td>
					<td>គម្រោង</td>
					<td><strong><strong><label id="lbl_project" class="value">3</label></strong></td>
					<td>&nbsp;&nbsp;ប្រាក់ដើម</td>
					<td><strong><label id="lb_amount" class="value"></label></strong></td>
					<td>&nbsp;បង់លើកទី</td>
					<td><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
				</tr>
				<tr >
					<td>ឈ្មោះ​អតិថិជន </td>
					<td><strong><label id="lb_customer" class="value"></label></strong></td>
					<td>&nbsp;&nbsp; ការប្រាក់</td>
					<td><strong><label id="lb_interest" class="value">0.00</label></strong></td>
					<td>&nbsp; ប្រាក់ពិន័យ</td>
					<td><strong><label id="lb_penalty" class="value">0.00</label></strong></td>
				</tr>
				<tr >
					<td>'.$tr->translate("PROPERTY_CODE").'</td>
					<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
					<td>&nbsp;&nbsp;ប្រាក់បង់បន្ថែម</td>
					<td colspan="3"><strong><label id="lb_extrapayment" class="value">0.00</label></strong></td>
				</tr>
				<tr >
					<td width="10%">'.$tr->translate("SOLD_PRICE").'</td>
					<td width="40%"><strong><label id="lb_saleprice" class="value"></label></strong></td>
					<td>&nbsp;&nbsp;ប្រាក់ត្រូវបង់</td>
					<td colspan="3"><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
				</tr>
				<tr>
					<td>ប្រាក់បានបង់សរុប</td>
					<td valign="top">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_total_paid1" class="value"></label></td>
								<td width="33%" style="white-space: nowrap;">ប្រាក់នៅសល់</td>
								<td width="33.5%"><label style="white-space: nowrap;margin-right: -4px;" class="value" id="lbl_balance"></label></td>
							</tr>
						</table>
					</td>
					<td>&nbsp;&nbsp;ប្រាក់បានទទួល</td>
					<td colspan="3"><strong><label  class="value" style="font-weight:700; font-family: Arial,Helvetica,sans-serif;" id="lbl_total_receive"></label></strong></td>
				</tr>
				<tr >
					<td rowspan="2">សម្គាល់</td>
					<td rowspan="2" class="noted" valign="top"><label id="lb_noted"></label></td>
					<td>&nbsp;&nbsp;ថ្ងៃត្រូវបង់</td>
					<td><strong><label id="lb_buydate1" class="value"></label></strong></td>
					<td>&nbsp;ថ្ងៃទទួល</td>
					<td><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
				</tr>
				<tr >
					<td>&nbsp;បង់ជា</td>
					<td><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
					<td>&nbsp;&nbsp;លេខ</td>
					<td><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
				</tr>
				<tr >
					<td colspan="6" valign="top">
						<table class="signature-table" width="100%" border="0">
							<tr>
								<td width="30%">&nbsp;
								'.$data['account_sign'].'
								</td>
								<td align="center" width="40%">
								'.$data['customer_sign'].'
								</td>
								<td align="center" width="30%">
								'.$data['teller_sign'].'
								</td>
							</tr>
							<tr height="85px">
								<td colspan="3">&nbsp;
								</td>
							</tr>
							<tr>
								<td width="30%">&nbsp;</td>
								<td align="center" width="40%">
								<label id="lbl_customer" ></label>
								</td>
								<td align="center" width="30%">
								<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="font-size: 11px;">
					<td colspan="6" valign="top">
						<table class="comment-footer" width="100%" border="0" >
							<tr>
								<td width="10%">
									<span class="lbnote" style="">សម្គាល់ ៖</span>
								</td>
								<td colspan="5">
									<p class="comment1">'.$data['comment'].'</p>
									<span class="comment">'.$data['comment1'].'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="line-height: 15px;font-size: 10px;">
					<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
				</tr>
				<tr style="line-height: 18px;font-size: 10px;">
					<td colspan="6" >
						'.$footer.'
					</td>
				</tr>
			</table>
			<div style="display: none;">
			<input type="hidden" dojoType="dijit.form.TextBox" value="0" name="is_showinstallment" id="is_showinstallment" />
			<label id="lbl_phone" class="value"></label>
			<label id="lbl_pricelabel" class="value" ></label>
			<span id="lable_chartotalreceipt"></span>
			<span id="lable_chartotalreceipt_in_kh" ></span>
			<span id="lblpaid_date"></span>
			<span id="lb_descriptionall"></span>
			<span id="lb_customercode"></span>
			
			<span id="lbl_priceSoldBefore"></span>
			<span id="lbl_discountAmount"></span>
			<span id="lbl_discountPercent"></span>
			<span id="lb_forCompletedAmount"></span>
			<span id="lb_completedDate"></span>
			<span id="lbl_discountOther"></span>
			
			<span id="lb_agreement_date"></span>
			<span id="lb_pre_schedule_opt"></span>
			<span id="lbl_pre_percent_payment"></span>
			<span id="lbl_pre_amount_month"></span>
			<span id="lbl_pre_percent_installment"></span>
			<span id="lbl_pre_amount_year"></span>
			<span id="lbl_pre_fix_payment"></span>
			</div>
			</div>
			';
		}
		return $str;
	}
	function getFooterReport(){
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		
		$str='<table align="center" width="100%">
				   <tr style="font-size: 14px;">
				        <td style="width:20%;text-align:center;  font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".'">'.$tr->translate('APPROVED BY').'</td>
				        <td></td>
				        <td style="width:20%;text-align:center; font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".'">'.$tr->translate('VERIFYED BY').'</td>
				        <td></td>
				        <td style="width:20%;text-align:center; font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".'">'.$tr->translate('PREPARE BY').'<br /><br />'.$last_name.$username.'</td>
				   </tr>';
// 			$str.='<tr>
// 					<td style="height: 60px;">&nbsp;</td>
// 				  </tr>
// 				  <tr style="font-size: 14px;">
// 				        <td style="border-bottom: dashed 1px #000;">&nbsp;</td>
// 				        <td></td>
// 				        <td style="border-bottom: dashed 1px #000;">&nbsp;</td>
// 				        <td></td>
// 				        <td style="border-bottom: dashed 1px #000;">&nbsp;</td>
// 				   </tr>
// 			';
		$str.='</table>';
		return $str;
	}
	
	function getInvestmentReceipt(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
	
		$footer = $this->getFooterReceipt();
	
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
	
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$usertype="";
		// 		$dbuser = new Application_Model_DbTable_DbUsers();
		// 		$userinfo = $dbuser->getUserInformationById($user_id);
		// 		$usertype = " (".$userinfo['user_typetitle'].")";
	
		$str='
			<div >
			<style>
				.label{ font-size: 22px;}
				.value{font:16px '."Khmer OS Battambang".';border: 1px solid #000; min-height: 29px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
				line-height: 29px;
				text-align: left;
				}
				span#lb_hourseno {
					overflow-wrap: break-word;
					white-space: normal;
					width: 200px;
					display: inline-block;
					line-height: 24px;
				}
				.print tr td{
					padding:1px 2px;
				}
				.khmer{font:14px '."Khmer OS Battambang".';}
				.one{white-space:nowrap;}
				.h{ margin-top: -10px;}
				.noted{white-space: pre-wrap;
						word-wrap: break-word;
						word-break: break-all;
						white-space: pre;
						font:12px '."Khmer OS Battambang".';
						border: 1px solid #000;
						line-height:20px;font-weight: normal !important;
					}
					table.receipt-titile tr td {
						font-size:16px;
					}
					table.receipt-titile tr td span {
						font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
					}
					table.receipt-titile tr td div span {
						line-height:10px;
						font-weight: bold;
					}
					#lb_receipt {
						font-weight: bold;
					}
					table.print.contentdata{
						width:100%;
						white-space: nowrap;
						font-size:16px;
						margin-top: -28px;
						font-family: Times New Roman,'."Khmer OS Battambang".';
					}
					table.print.contentdata tr{
						white-space: nowrap;
					}
					tr.receipt-row {
						white-space: nowrap;
						font-size: 14px;
						margin-top: -15px;
					}
					table.signature-table{
						font-size:14px;line-height: 18px;
					}
					table.comment-footer{
						margin-top:-5px
					}
					table.comment-footer tr td span.lbnote {
						text-decoration:underline;
						font-size: 12px;
						margin-top: -5px;
					}
					table.comment-footer tr td p.comment1{
						font-size: 11px;
						margin:-5px 0px -5px 0px !important;
						padding:0 !important;
					}
					table.comment-footer tr td span.comment{
						white-space: pre-line;
						font-size: 11px;
						margin-top: -5px;
					}
					small {
				   		 font-size: 11px;
					}
				</style>
				<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top: -15px;" class="print" cellspacing="0"  cellpadding="0" >
					<tr>
						<td colspan="6">
							<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
								<tr>
									<td id="projectlogo" width="35%">
										<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
									</td>
									<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃប្រគល់ប្រាក់</span></u>
										<div ><span >PAYMENT VOCHER</span></div>
									</td>
									<td width="35%"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
					<tr class="receipt-row"  >
						<td colspan="5"></td>
						<td align="right">
						<span id="lb_receipt" ></span>
						</td>
					</tr>
					<tr >
						<td>ឈ្មោះអ្នកវិនិយោគ <br /><small>Investor Name</small></td>
						<td><strong><label id="lb_customer" class="value"></label></strong></td>
						<td>&nbsp;&nbsp;ដកលើកទី<br />&nbsp;&nbsp;<small>Time(s)</small></td>
						<td colspan="3"><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
					</tr>
					<tr >
						<td>លេខវិនិយោគ<br /><small>Investment No</small></td>
						<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
						<td>&nbsp;&nbsp;ប្រាក់ត្រូវទូទាត់<br />&nbsp;&nbsp;<small>Amount Return</small></td>
						<td colspan="3"><strong><label id="lb_interest" class="value">0.00</label></strong></td>
					</tr>
					<tr >
						<td>ថ្ងៃត្រូវទូទាត់<br /><small>Date Payment</small></td>
						<td valign="top">
							<table width="100%" cellpadding="0" cellspacing="0">
								<tr>
									<td width="33.5%" style="white-space: nowrap;"><strong><label id="lb_buydate1" class="value"></label></strong></td>
									<td width="33%" style="white-space: nowrap;">ថ្ងៃបានទូទាត់<br /><small>Date Paid</small></td>
									<td width="33.5%"><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
								</tr>
							</table>
						</td>
						<td>&nbsp;&nbsp;សរុបប្រាក់ត្រូវទូទាត់<br />&nbsp;&nbsp;<small>Total Amount Return</small></td>
						<td colspan="3"><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
					</tr>
					<tr>
						<td rowspan="2">សម្គាល់<br /><small>Note</small></td>
						<td rowspan="2" class="noted" valign="top"><label id="lb_noted"></label></td>
						<td>&nbsp;&nbsp;ប្រាក់បានទូទាត់<br />&nbsp;&nbsp;<small>Total Paid</small></td>
						<td colspan="3"><strong><label  class="value" style="font-weight:700; font-family: Arial,Helvetica,sans-serif;" id="lbl_total_receive"></label></strong></td>
					</tr>
					<tr >
						<td>&nbsp;&nbsp;ប្រភេទ<br /><small>&nbsp;&nbsp;Paid Type</small></td>
						<td><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
						<td>&nbsp;&nbsp;លេខ<br />&nbsp;&nbsp;<small>code</small></td>
						<td><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
					</tr>
					<tr >
						<td colspan="6" valign="top">
							<table class="signature-table" width="100%" border="0">
								<tr>
									<td width="30%" align="center">&nbsp;
									ហត្ថលេខាប្រធានផ្នែកហិរញ្ញវត្ថុ<br />Financial Manager
									</td>
									<td align="center" width="40%">
									ហត្ថលេខាអ្នកវិនិយោគ<br />Investor Sign
									</td>
									<td align="center" width="30%">
									ហត្ថលេខាអ្នកប្រគល់<br />Giver Sign
									</td>
								</tr>
								<tr height="75px">
									<td colspan="3">&nbsp;
									</td>
								</tr>
								<tr>
									<td width="30%">&nbsp;</td>
									<td align="center" width="40%">
										<label id="lbl_customer" ></label>
									</td>
									<td align="center" width="30%">
										<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
									</td>
						
								</tr>
							</table>
						</td>
					</tr>
		<tr style="font-size: 11px;">
			<td colspan="6" valign="top">
				
			</td>
		</tr>
		<tr style="line-height: 15px;font-size: 10px;">
			<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
		</tr>
		<tr style="line-height: 18px;font-size: 10px;">
			<td colspan="6" >
			'.$footer.'
			</td>
		</tr>
		</table>
		<div style="display: none;">
		<span id="lable_chartotalreceipt"></span>
		<span id="lblpaid_date"></span>
		<span id="lb_descriptionall"></span>
		<span id="lb_customercode"></span>
		<span id="lb_amount"></span>
		</div>
		</div>
		';
		return $str;
	}
	function getBrokerReceipt(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
	
		$footer = $this->getFooterReceipt();
	
		$baseurl= Zend_Controller_Front::getInstance()->getBaseUrl();
	
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$usertype="";
		// 		$dbuser = new Application_Model_DbTable_DbUsers();
		// 		$userinfo = $dbuser->getUserInformationById($user_id);
		// 		$usertype = " (".$userinfo['user_typetitle'].")";
	
		$str='
		<div >
		<style>
		.label{ font-size: 22px;}
			.value{font:16px '."Khmer OS Battambang".';border: 1px solid #000; min-height: 29px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
			line-height: 29px;
			text-align: left;
			}
		span#lb_hourseno {
			overflow-wrap: break-word;
			white-space: normal;
			width: 200px;
			display: inline-block;
			line-height: 24px;
		}
		.print tr td{
			padding:1px 2px;
		}
		.khmer{font:14px '."Khmer OS Battambang".';}
		.one{white-space:nowrap;}
		.h{ margin-top: -10px;}
		.noted{white-space: pre-wrap;
				word-wrap: break-word;
				word-break: break-all;
				white-space: pre;
				font:12px '."Khmer OS Battambang".';
				border: 1px solid #000;
				line-height:20px;font-weight: normal !important;
			}
			table.receipt-titile tr td {
				font-size:16px;
			}
			table.receipt-titile tr td span {
				font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
			}
			table.receipt-titile tr td div span {
				line-height:10px;
				font-weight: bold;
			}
			#lb_receipt {
				font-weight: bold;
			}
			table.print.contentdata{
				width:100%;
				white-space: nowrap;
				font-size:16px;
				margin-top: -28px;
				font-family: Times New Roman,'."Khmer OS Battambang".';
			}
			table.print.contentdata tr{
				white-space: nowrap;
			}
			tr.receipt-row {
				white-space: nowrap;
				font-size: 14px;
				margin-top: -15px;
			}
			table.signature-table{
				font-size:14px;line-height: 18px;
			}
			table.comment-footer{
				margin-top:-5px
			}
			table.comment-footer tr td span.lbnote {
				text-decoration:underline;
				font-size: 12px;
				margin-top: -5px;
			}
			table.comment-footer tr td p.comment1{
				font-size: 11px;
				margin:-5px 0px -5px 0px !important;
				padding:0 !important;
			}
			table.comment-footer tr td span.comment{
				white-space: pre-line;
				font-size: 11px;
				margin-top: -5px;
			}
			small {
				   		 font-size: 11px;
					}
		</style>
		<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top: -15px;" class="print" cellspacing="0"  cellpadding="0" >
			<tr>
				<td colspan="6">
					<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
						<tr>
							<td id="projectlogo" width="35%">
								<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
							</td>
							<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃប្រគល់ប្រាក់</span></u>
								<div ><span >PAYMENT VOCHER</span></div>
							</td>
							<td width="35%"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
			<tr class="receipt-row"  >
				<td colspan="5"></td>
				<td align="right">
					<span id="lb_receipt" ></span>
				</td>
			</tr>
			<tr >
				<td>ឈ្មោះភ្នាក់ងារ <br /><small>Broker Name</small></td>
				<td><strong><label id="lb_customer" class="value"></label></strong></td>
				<td>&nbsp;&nbsp;ដកលើកទី<br />&nbsp;&nbsp;<small>Time(s)</small></td>
				<td colspan="3"><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
			</tr>
			<tr >
				<td>លេខវិនិយោគ<br /><small>Investment No</small></td>
				<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
				<td>&nbsp;&nbsp;ប្រាក់ត្រូវទូទាត់<br />&nbsp;&nbsp;<small>Amount Return</small></td>
				<td colspan="3"><strong><label id="lb_interest" class="value">0.00</label></strong></td>
			</tr>
			<tr >
				<td>ថ្ងៃត្រូវទូទាត់<br /><small>Date Payment</small></td>
				<td valign="top">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="33.5%" style="white-space: nowrap;"><strong><label id="lb_buydate1" class="value"></label></strong></td>
							<td width="33%" style="white-space: nowrap;">ថ្ងៃបានទូទាត់<br /><small>Date Paid</small></td>
							<td width="33.5%"><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
						</tr>
					</table>
				</td>
				<td>&nbsp;&nbsp;សរុបប្រាក់ត្រូវទូទាត់<br /><small>&nbsp;&nbsp;Total Amount Return</small></td>
				<td colspan="3"><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
			</tr>
			<tr>
				<td rowspan="2">សម្គាល់<br /><small>Note</small></td>
				<td rowspan="2" class="noted" valign="top"><label id="lb_noted"></label></td>
				<td>&nbsp;&nbsp;ប្រាក់បានទូទាត់<br /><small>&nbsp;&nbsp;Total Paid</small></td>
				<td colspan="3"><strong><label  class="value" style="font-weight:700; font-family: Arial,Helvetica,sans-serif;" id="lbl_total_receive"></label></strong></td>
			</tr>
			<tr >
				<td>&nbsp;&nbsp;ប្រភេទ<br /><small>&nbsp;&nbsp;Paid Type</small></td>
				<td><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
				<td>&nbsp;&nbsp;លេខ<br /><small>&nbsp;&nbsp;Code</small></td>
				<td><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
			</tr>
			<tr >
			<td colspan="6" valign="top">
				<table class="signature-table" width="100%" border="0">
					<tr>
						<td width="30%">&nbsp;
						'.$data['account_sign'].'<br />Accountant Sign
						</td>
						<td align="center" width="40%">
						ស្នាមមេដៃភ្នាក់ងារ<br />Broker Sign
						</td>
						<td align="center" width="30%">
						'.$data['teller_sign'].'<br />Receiver Sign
						</td>
					</tr>
					<tr height="85px">
						<td colspan="3">&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%">&nbsp;</td>
						<td align="center" width="40%">
							<label id="lbl_customer" ></label>
						</td>
						<td align="center" width="30%">
							<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr style="font-size: 11px;">
			<td colspan="6" valign="top">
				
			</td>
		</tr>
		<tr style="line-height: 15px;font-size: 10px;">
			<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
		</tr>
		<tr style="line-height: 18px;font-size: 10px;">
			<td colspan="6" >
			'.$footer.'
			</td>
		</tr>
	</table>
	<div style="display: none;">
	<span id="lable_chartotalreceipt"></span>
	<span id="lblpaid_date"></span>
	<span id="lb_descriptionall"></span>
	<span id="lb_customercode"></span>
	</div>
	</div>
	';
		return $str;
	}
	
	public function frmPopupOther(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$frm = new Other_Form_FrmOther();
		$frm=$frm->FrmaddOther();
		Application_Model_Decorator::removeAllDecorator($frm);
		$string='
		<div class="dijitHidden">
			<div data-dojo-type="dijit.Dialog"  id="frm_datapop" data-dojo-props="title:'."'".$tr->translate("ADD_NEW")."'".'">
				<form id="form_popup" dojoType="dijit.form.Form" method="post" enctype="application/x-www-form-urlencoded">
					<div class="card-box">
						<div class="col-md-12 col-sm-12 col-xs-12">
							<div class="form-group">
								<label class="control-label col-md-12 col-sm-12 col-xs-12 title-blog bold" >
									<i class="fa fa-hand-o-right" aria-hidden="true"></i>
									<span id="title_form"></span>
								</label>
							</div>
							<div class="form-group">
								<label class="control-label col-md-5 col-sm-5 col-xs-12" >'.$tr->translate('TITLE').' :
								</label>
								<div class="col-md-7 col-sm-7 col-xs-12">
								'.$frm->getElement("title").'
								</div>
							</div>
							
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-12 border-top mt-20 ptb-10 text-center">
							<input type="button"  label="'.$tr->translate("SAVE").'" dojoType="dijit.form.Button"
							iconClass="dijitEditorIcon dijitEditorIconSave" onclick="addData();"/>
						</div>
					</div>
					</div>
				</form>
			</div>
		</div>
		';
		return $string;
	
	}
	
	function getOfficailReceiptRent(){//general
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
	
		$footer = $this->getFooterReceipt();
	
		$baseurl=Zend_Controller_Front::getInstance()->getBaseUrl();
	
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$usertype="";
	
// 		$reciept_type=RECEIPT_TYPE;
	
		$str='
			<div >
			<style>
				.label{ font-size: 22px;}
				.value{
					font:16px '."Khmer OS Battambang".';border: 1px solid #000; min-height: 29px; padding: 0 2px;width: 100%;margin-right:2px; display: block;
					line-height: 29px;
					text-align: left;
				}
				span#lb_hourseno {
					overflow-wrap: break-word;
					white-space: normal;
					width: 200px;
					display: inline-block;
					line-height: 24px;
				}
				.print tr td{
					padding:1px 2px;
				}
				.khmer{font:14px '."Khmer OS Battambang".';}
				.one{white-space:nowrap;}
				.h{ margin-top: -10px;}
				.noted{white-space: pre-wrap;
					word-wrap: break-word;
					word-break: break-all;
					white-space: pre;
					font:12px '."Khmer OS Battambang".';
					border: 1px solid #000;
					line-height:20px;font-weight: normal !important;
				}
				table.receipt-titile tr td {
					font-size:16px;
				}
				table.receipt-titile tr td span {
					font-family: Arial Black;font-family:'."Khmer OS Muol Light".';
				}
				table.receipt-titile tr td div span {
					line-height:10px;
					font-weight: bold;
				}
				#lb_receipt {
					font-weight: bold;
				}
				table.print.contentdata{
					width:100%;
					white-space: nowrap;
					font-size:16px;
					margin-top: -28px;
					font-family: Times New Roman,'."Khmer OS Battambang".';
				}
				table.print.contentdata tr{
					white-space: nowrap;
				}
				tr.receipt-row {
					white-space: nowrap;
					font-size: 14px;
					margin-top: -15px;
				}
				table.signature-table{
					font-size:14px;line-height: 18px;
				}
				table.comment-footer{
					margin-top:-5px
				}
				table.comment-footer tr td span.lbnote {
					text-decoration:underline;
					font-size: 12px;
					margin-top: -5px;
				}
				table.comment-footer tr td p.comment1{
					font-size: 11px;
					margin:-5px 0px -5px 0px !important;
					padding:0 !important;
				}
				table.comment-footer tr td span.comment{
					white-space: pre-line;
					font-size: 11px;
					margin-top: -5px;
				}
		</style>
		<table width="100%" style="backgroud:red;white-space: nowrap;font-size:16px; padding:0px;margin-top: -15px;" class="print" cellspacing="0"  cellpadding="0" >
			<tr>
				<td colspan="6">
					<table class="receipt-titile" width="100%" style="font-family:'."Khmer OS Muol Light".';white-space:nowrap;">
						<tr>
							<td id="projectlogo" width="35%">
								<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
							</td>
						<td width="30%" valign="top" align="center"><u><span>បង្កាន់ដៃទទួលប្រាក់</span></u>
							<div ><span >OFFICIAL RECEIPT</span></div>
						</td>
						<td width="35%"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table  class="print contentdata" cellspacing="3px"  cellpadding="0" >
			<tr class="receipt-row"  >
				<td colspan="5"></td>
				<td align="right">
					<span id="lb_receipt" ></span>
				</td>
			</tr>
			<tr >
				<td style="display: none;">'.$tr->translate("RENT_NO").'</td>
				<td style="display: none;"><strong><label class="value"></label></strong></td>
				<td>គម្រោង</td>
				<td><strong><strong><label id="lbl_project" class="value">3</label></strong></td>
				<td>&nbsp;&nbsp;បង់លើកទី</td>
				<td colspan="3"><strong><label id="lbl_paidtimes" class="value"></label></strong></td>
			</tr>
			<tr >
				<td>ឈ្មោះ​អតិថិជន </td>
				<td><strong><label id="lb_customer" class="value"></label></strong></td>
				<td>&nbsp;&nbsp;ប្រាក់ត្រូវបង់</td>
				<td><strong><label id="lbl_totalpayment" class="value"></label></strong></td>
				<td>&nbsp;ប្រាក់ពិន័យ</td>
				<td><strong><label id="lb_penalty" class="value">$ 0.00</label></strong></td>
			</tr>
			<tr >
				<td>'.$tr->translate("PROPERTY_CODE").'</td>
				<td><strong><label class="value"><span id="lb_hourseno"></span></label></strong></td>
				<td>&nbsp;&nbsp;ប្រាក់បានទទួល</td>
				<td colspan="3"><strong><label  class="value" style="font-weight:700; font-family: Arial,Helvetica,sans-serif;" id="lbl_total_receive"></label></strong></td>
			</tr>
			<tr >
				<td width="10%">'.$tr->translate("RENT_PRICE").'</td>
				<td width="40%"><strong><label id="lb_saleprice" class="value"></label></strong></td>
				<td>&nbsp;&nbsp;ថ្ងៃត្រូវបង់</td>
				<td><strong><label id="lb_buydate1" class="value"></label></strong></td>
				<td>&nbsp;ថ្ងៃទទួល</td>
				<td><strong><label id="lbl_paid_date1" class="value"></label></strong></td>
			</tr>
			<tr >
				<td  valign="top">សម្គាល់</td>
				<td class="noted" valign="top"><label id="lb_noted" style="min-height: 60px;display: block;   white-space: pre-line;"></label></td>
				<td valign="top">&nbsp;&nbsp;បង់ជា</td>
				<td valign="top"><strong><label id="lbl_paymenttype" class="value"></label></strong></td>
				<td valign="top">&nbsp;&nbsp;លេខ</td>
				<td valign="top"><strong><label id="lbl_cheque" class="value">N/A</label></strong></td>
			</tr>
			<tr >
				<td colspan="6" valign="top">
					<table class="signature-table" width="100%" border="0">
						<tr>
							<td width="30%">&nbsp;
							'.$data['account_sign'].'
							</td>
							<td align="center" width="40%">
							'.$data['customer_sign'].'
							</td>
							<td align="center" width="30%">
							'.$data['teller_sign'].'
							</td>
						</tr>
						<tr height="85px">
							<td colspan="3">&nbsp;
							</td>	
						</tr>
						<tr>
							<td width="30%">&nbsp;</td>
							<td align="center" width="40%">
							<label id="lbl_customer" ></label>
							</td>
							<td align="center" width="30%">
							<label id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</label>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		<tr style="font-size: 11px;">
			<td colspan="6" valign="top">
				<table class="comment-footer" width="100%" border="0" >
					<tr>
						<td width="10%">
							<span class="lbnote" style="">សម្គាល់ ៖</span>
						</td>
						<td colspan="5">
							<p class="comment1">'.$data['comment'].'</p>
							<span class="comment">'.$data['comment1'].'</span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr style="line-height: 15px;font-size: 10px;">
			<td colspan="6" style="border-top: 2px solid rgba(255, 235, 59, 0.88)"></td>
		</tr>
		<tr style="line-height: 18px;font-size: 10px;">
			<td colspan="6" >
			'.$footer.'
			</td>
		</tr>
	</table>
		<div style="display: none;">
			<input type="hidden" dojoType="dijit.form.TextBox" value="0" name="is_showinstallment" id="is_showinstallment" />
			<label id="lbl_phone" class="value"></label>
			<label id="lbl_pricelabel" class="value" ></label>
			<span id="lable_chartotalreceipt"></span>
			<span id="lblpaid_date"></span>
			<span id="lb_descriptionall"></span>
			<span id="lb_customercode"></span>
			
			<span id="lb_amount"></span>
			<span id="lb_penalty"></span>
			<span id="lb_interest"></span>
			<span id="lb_extrapayment"></span>
			
			<span id="lbl_total_paid1"></span>
			<span id="lbl_balance"></span>
		</div>
	</div>';
		return $str;
	}
	
	function templateExpenseReceipt(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
		
		$footer = $this->getFooterReceipt();
		
		$baseurl=Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$usertype="";
		
		$str='
			<style>
				.label{ font-weight: bold;font-size: 22px;}
				.value{ font:16px  '."'Times New Roman'".','."'Khmer OS Battambang'".';border: 1px solid #000; height: 30px; width: 100%;margin-right:5px; display: block;
						line-height: 28px;
					    text-align: left;
						padding-left: 5px;
						}
				.print tr td{
					padding:2px 2px; 
				}
				
				.khmerbold{font:16px '."'Times New Roman'".','."'Khmer OS Battambang'".';}
			   .khmer{font:14px '."'Times New Roman'".','."'Khmer OS Battambang'".';}
			   .one{white-space:nowrap;}
			   .h{ margin-top: -10px;}
			   .noted{
					white-space: pre-wrap;     
					word-wrap: break-word;      
					word-break: break-all;
					white-space: pre;
					font:12px '."'Times New Roman'".','."'Khmer OS Battambang'".';
					border: 1px solid #000;
                   line-height:20px;font-weight: normal !important;
                }
				label{margin-bottom: 0px !important}
				#lb_receipt {
					font-weight: bold;
				}
				table.mainBody{
					white-space: nowrap;
					font-size:14px;
					margin-top:-30px;
					font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".'; 
				}
				table.mainBody span,
				table.mainBody label{
					font-size:14px;
				}
			</style>
		';
		$str.='
			<table width="100%" style="white-space: nowrap;font-size:14px;margin-top: 0px;" class="print" cellspacing="0"  cellpadding="0" >
				<tr>
					<td colspan="6">
						<table width="100%" style="font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".';white-space:nowrap;">
							<tr>
								<td width="25%">
									
									<span id="projectlogo"></span>
								</td>					
								<td width="50%" style="font:18px '."'Times New Roman'".','."'Khmer OS Muol Light'".';" valign="top" align="center">
									<span style=" text-decoration:underline; font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';"> បង្កាន់ដៃចំណាយ </span>
									<div style="line-height:10px;"><span style="font-size: 18px;font-weight:bold">PAYMENT VOUCHER</span></div>
								</td>
								<td width="25%">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		';
		$str.='
			<table class="mainBody print" width="100%"  cellspacing="0"  cellpadding="0">
				<tr class="receipt-row"  >
					<td colspan="3"></td>
					<td align="right">
						<span id="lb_receipt" ></span>
					</td>
				</tr>
				<tr style="white-space: nowrap;">
					<td width="15%" class="one khmerbold">'.$tr->translate("BRANCH_NAME").'</td>
                    <td width="35%" ><strong><label id="lb_branch" class="value"></label></strong></td>
				    <td width="15%" class="one khmerbold">&nbsp;&nbsp;&nbsp;'.$tr->translate("INVOICE").'</td>
                    <td width="35%"><strong><label id="lb_invoice" class="value"></label></strong></td>
				</tr>
				<tr style="white-space: nowrap;">
					<td class="one khmerbold">'.$tr->translate("SUPPLIER").'</td>
                    <td ><strong><label id="lb_supplier" class="value"></label></strong></td>
                    <td class="one khmerbold">&nbsp;&nbsp;&nbsp;ថ្ងៃចំណាយ</td>
				    <td ><strong><label id="lb_date" class="value"></label></strong></td>
				</tr>
				<tr>
					<td class="one khmerbold">ប្រភេទចំណាយ</td>
					<td ><strong><label id="lb_expense_category" class="value"></label></strong></td>
					<td class="one khmerbold">&nbsp;&nbsp;&nbsp;'.$tr->translate("PAYMENT_TYPE").'</td>
				    <td >
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -4px;" id="lbl_paymenttype" class="value"></label></td>
								<td width="33%" style="white-space: nowrap;">&nbsp;'.$tr->translate("CHEQUE").'</td>
								<td width="33.5%"><strong style="white-space: nowrap;"><label style="white-space: nowrap;" id="lb_cheque" class="value"></label></strong></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="white-space: nowrap;">
					<td class="one khmerbold">ពណ៌នាចំនាយ</td>
				    <td><strong><label id="lb_expense_title" class="value"></label></strong></td>
				    <td class="one khmerbold" rowspan="2">&nbsp;&nbsp;&nbsp;សម្គាល់</td>
				    <td colspan="1" align="left" rowspan="2" style="vertical-align: top; border: 1px solid #000 !important;text-align: left;" class="noted"><label style="text-align: left;display: inline-block;max-width: 100%;font-weight: 600;" id="lb_description" ></label></td>
				</tr>
				<tr>
					<td class="one khmerbold">ចំណាយសរុប</td>
					<td><strong><label id="lb_total_amount" class="value"></label></strong></td>
				</tr>
				<tr style="white-space: nowrap;">
				    <td class="khmerbold" style="line-height: 14px;"colspan="2"  align="center" >&nbsp;&nbsp;
						<span style=" font-family: Arial Black;font-family:'."'Khmer OS Muol Light'".';">
						'.$data['customer_sign'].'
						</span>
					</td>
				    <td colspan="2" class="khmerbold" style="line-height: 14px;" align="center" >
						<span style=" font-family: Arial Black;font-family:'."'Khmer OS Muol Light'".';">
						'.$data['teller_sign'].'
						</span>
					</td>
				</tr>
				<tr style="white-space: nowrap;" height="70px;">
					<td class="one khmerbold" colspan="2" align="center" valign="bottom">
					</td>
				    <td class="one khmerbold" colspan="2" align="center" valign="bottom">&nbsp;
				  		<h4 style="font-weight:normal; padding-right: 5px ! important;margin-bottom: -10px  !important;">

					    </h4>  
					</td>
				</tr>
				<tr style="line-height: 15px;font-size: 10px;">
					<td colspan="4" valign="top" style="height: 75px;" >
					</td>
				</tr>
				<tr style="line-height: 15px;font-size: 10px;">
					<td colspan="4" style="border-top: 2px solid rgba(255, 235, 59, 0.88)">
					</td>
				</tr>
				<tr style="line-height: 20px;font-size: 10px;">
					<td colspan="6" >
						'.$footer.'
					</td>
				</tr>
			</table>
		';
		
		return $str;
	}
	function templateIncomeReceipt(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
		
		$footer = $this->getFooterReceipt();
		$baseurl=Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$usertype="";
		$str='
			<style>
				.fontbig{
					font-size: 15px;	
				}
				.fonttel{
					font-size: 18px;	
				}
				.pleft{
					width: 110px;	
				}

				.label{ font-size: 22px;}
				.value{
				    font: 16px '."'Times New Roman'".','."'Khmer OS Battambang'".';
				    border: 1px solid #000;
				    min-height: 30px;
				    width: 100%;
				    margin-right: 5px;
				    display: block;
				    line-height: 28px;
				    text-align: left;
					padding-left:5px;
				}
				.print tr td{
					padding:2px 2px; 
				}
			   .khmerbold{font:14px '."'Times New Roman'".','."'Khmer OS Battambang'".';}
			   .khmer{font:12px '."'Times New Roman'".','."'Khmer OS Battambang'".';}
			   .one{white-space:nowrap;}
			   .h{ margin-top: -10px;/*margin-left:4px;*/}
				.noted{
					white-space: pre-wrap;     
					word-wrap: break-word;      
					word-break: break-all;
					white-space: pre;
					font:12px '."'Times New Roman'".','."'Khmer OS Battambang'".';
					border: 1px solid #000;
                    line-height:20px;font-weight: normal !important;
                }
                span#lb_client_name {
				    overflow-wrap: break-word;
				    white-space: normal;
				    width: 200px;
				    display: inline-block;
				    line-height: 24px;
				}
				table.mainBody{
					white-space: nowrap;
					font-size:14px;
					margin-top: -8px;
				}
				table.mainBody span,
				table.mainBody label{
					font-size:14px;
				}
				table.comment-footer{
					margin-top:20px;
				}
				table.comment-footer tr td span.lbnote {
					text-decoration:underline;
					font-size: 12px;
					margin-top: -5px;
				}
				table.comment-footer tr td p.comment1{
					font-size: 11px;
					margin:-5px 0px -5px 0px !important;
					padding:0 !important;
				}
				table.comment-footer tr td span.comment{
					white-space: pre-line;
					font-size: 11px;
					margin-top: -5px;
				}
			</style>
		';
		$str.='
			<table width="100%" style="white-space: nowrap;font-size:14px;margin-top: 0px;" class="print" cellspacing="0"  cellpadding="0" >
				<tr>
					<td colspan="6">
						<table width="100%" style="font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".';white-space:nowrap;">
							<tr>
								<td width="25%">
									<span id="projectlogo"></span>
								</td>					
								<td width="50%" style="font:18px '."'Times New Roman'".','."'Khmer OS Muol Light'".';" valign="top" align="center"><u>
									<div id="titleReceipt"></div>
								</td>
								<td width="25%">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		';
		$str.='
			<table class="mainBody" width="100%" class="print" cellspacing="2px"  cellpadding="0">
				<tr>
					<td width="15%" ></td>
					<td width="35%" ></td>
					<td width="15%" ></td>
					<td width="35%" ></td>
				</tr>
				<tr style="white-space: nowrap;">
					<td class="one khmerbold">'.$tr->translate("BRANCH_NAME").'</td>
                    <td><strong><label class="value" id="lb_branch" ></label></strong></td>
				    <td class="one khmerbold">&nbsp;&nbsp;'.$tr->translate("RECEIPT_NO").'</td>
                    <td ><strong><label class="value" id="lb_receipt" ></label></strong></td>
				</tr>
				<tr style="white-space: nowrap;">
					<td class="one khmerbold" width="10%">អតិថិជន</td>
				    <td width="40%"><strong><label class="value"><span id="lb_client_name"></span></label></strong></td>
					<td class="one khmerbold">&nbsp;&nbsp;ប្រភេទចំនូល</td>
				    <td ><strong><label id="lb_category" class="value"></label></strong></td>
				</tr>

				<tr>
					<td class="one khmerbold">ពណ៌នាចំនូល</td>
					<td ><strong><label class="value" id="lb_title"></label></strong></td>
					<td class="one khmerbold">&nbsp;&nbsp;ចំនូលសរុប</td>
					<td ><strong><label id="lb_total_amount" class="value"></label></strong></td>
				</tr>
				<tr style="white-space: nowrap;">
					<td rowspan="2" valign="top" class="one khmerbold">សម្គាល់</td>
				    <td rowspan="2" style="border: 1px solid #000 !important;text-align: left; vertical-align: top;" class="noted"><label id="lb_description" style="text-align: left;display: block;width: 100%;font-weight: 600;">&nbsp;</label></td>
					<td class="one khmerbold">&nbsp;&nbsp;'.$tr->translate("PAYMENT_TYPE").'</td>
				    <td >
						<table width="100%" cellpadding="0" cellspacing="0" style="font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".'">
							<tr>
								<td width="33.5%" style="white-space: nowrap;"><label style="margin-left: -1px;" id="lbl_paymenttype" class="value"></label></td>
								<td width="33%" style="white-space: nowrap;">&nbsp;'.$tr->translate("CHEQUE").'</td>
								<td width="33.5%"><strong style="white-space: nowrap;"><label style="white-space: nowrap;" id="lb_cheque" class="value"></label></strong></td>
							</tr>
						</table>
					</td>
				</tr>
				
				<tr style="white-space:normal;">
					<td class="one khmerbold">&nbsp;&nbsp;ថ្ងៃទទួល</td>
				    <td ><strong><label class="value" id="lb_date"></label></strong></td>
				</tr>
				<tr>
					<td style="">&nbsp;</td>
				</tr>
				<tr style="white-space: nowrap;">
				    <td colspan="2" class="khmerbold" style="line-height: 14px;"  align="center" >&nbsp;&nbsp;<span style=" font-family: Arial Black;font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".';">'.$data['customer_sign'].'</span></td>
				    <td colspan="2" class="khmerbold" style="line-height: 14px;" align="center" ><span style=" font-family: Arial Black;font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".';">'.$data['teller_sign'].'</span></td>
				</tr>
				<tr style="white-space: nowrap;" height="70px;">
					<td class="one khmerbold" colspan="2" align="center" valign="bottom">
						<h4 style="font-weight:normal; padding-right: 5px ! important;margin-bottom: -10px  !important;">
							<span id="lb_customer_name"></span>
						</h4>
					</td>
				    <td class="one khmerbold" colspan="2" align="center" valign="bottom">&nbsp;
				  	  <h4 style="font-weight:normal; padding-right: 5px ! important;margin-bottom: -10px  !important;">
						<span id="lb_user_name">'.$last_name." ".$username.$usertype.'</span>
					  
					  </h4>  
					</td>
				</tr>
				
				<tr style="line-height: 20px;font-size: 11px;font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".'">
					<td colspan="4" valign="top">
						<table class="comment-footer" width="100%" border="0" >
							<tr>
								<td width="10%">
									<span class="lbnote" style="">សម្គាល់ ៖</span>
								</td>
								<td colspan="5">
									<p class="comment1">'.$data['comment'].'</p>
									<span class="comment">'.$data['comment1'].'</span>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr style="line-height: 15px;font-size: 12px;">
					<td colspan="4" style="border-top: 2px solid rgba(255, 235, 59, 0.88)">
						
					</td>
				</tr>
				<tr style="line-height: 15px;font-size: 12px;">
					<td colspan="4" >
						'.$footer.'
					</td>
				</tr>
			</table>
		';
		$str.='';
		
		return $str;
	}
	
	function getLetterHeadReport(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
	
	
		$baseurl=Zend_Controller_Front::getInstance()->getBaseUrl();
	
		$defaultLogo = $baseurl."/images/logo.jpg";
		if(!empty($data['logo'])){
			if (file_exists(PUBLIC_PATH."/images/photo/logo/".$data['logo'])){
				$defaultLogo = $baseurl.'/images/photo/logo/'.$data['logo'];
			}
		}
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$last_name=$session_user->last_name;
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$headerReportType=REPORT_LETER_HEAD;
		
		
		$branch_title = $tr->translate("BRAND_TITLE");
		$companyName=$tr->translate("BRAND_TITLE");
		$companyNameEn=$tr->translate("BRAND_TITLE_EN");
		$companyAddress=$data['footer_branch'];
		$companyTel="&#9743; ".$data['tel-client'];
		$companyEmail="";
		if(!empty($data['email_client'])){
			$companyEmail=" &#x2709; ".$data['email_client'];
		}
		$companyContact=$companyTel.$companyEmail;
		
		$string="";
		if($headerReportType==1){
			$string.='
				<style>
					ul.headReport,
					ul.reportTitle{
						margin: 0;
						padding: 0;
						list-style: none;
					}
					ul.headReport li span,
					ul.headReport li{
						line-height: 24px;
						text-align:center; 
						font-size:'.FONTSIZE_REPORT.';
						font-family:'.'"Times New Roman"'.','.'"Khmer OS Muol Light"'.';
						
					}
					ul.headReport li.small-text,
					ul.headReport li.small-text span{
						font-family:'.'"Times New Roman"'.','.'"Khmer OS Battambang"'.';
					}
					
					
					
				</style>
				
					<table width="100%">
		            	<tr>
		                	<td width="30%" id="projectlogo"><img src="'.$defaultLogo.'" style=" height:85px; max-width: 100%;" ></td>
		                	<td width="40%" valign="top">
		                		<ul class="headReport">
		                			<li ><span id="companyTitle"></span></li>
		                			<li ><span id="reportTitle"></span></li>
		                			<li class="small-text"><span id="dateReport"></span></li>
		                			<li class="small-text"><span id="projectName"></span></li>
									<li class="small-text"><span id="staff_lbl"></span></li>
		                		</ul>
		                	</td>
		                    <td width="30%"></td>
		                </tr> 
		            </table>
			';
		}else if($headerReportType==2){
			$string.='
				<style>
					ul.headReport,
					ul.reportTitle{
						margin: 0;
						padding: 0;
						list-style: none;
					}
					ul.headReport li span,
					ul.headReport li{
						line-height: 18px;
						text-align:center; 
						font-size:14px;
						font-family:'.'"Times New Roman"'.','.'"Khmer OS Muol Light"'.';
						
					}
					ul.headReport li.small-text,
					ul.headReport li.small-text span{
						line-height: 14px;
						text-align:center; 
						font-size:11px;
						font-family:'.'"Times New Roman"'.','.'"Khmer OS Battambang"'.';
						
					}
					ul.reportTitle {
						background: #ffffff;
						display: block;
						margin-top: -40px;
						
					}
					ul.reportTitle li,
					ul.reportTitle li span{
						line-height: 20px;
						text-align:center;
					}
					table.tableTop tr td span.project-name{
						font-size:12px ;
						font-family:'.'"Times New Roman"'.','.'"Khmer OS Muol Light"'.';
					}
				</style>
				<table class="tableTop" width="100%" style="border-bottom: double 5px #337ab7;">
					<tr>
						<td width="20%" id="projectlogo"><img src="'.$defaultLogo.'" style=" height:85px; max-width: 100%; " ></td>
						<td width="60%" valign="top" style=" padding-bottom: 40px;">
							<ul class="headReport">
								<li style="font-size:'.FONTSIZE_REPORT.'; " id="companyTitle">'.$companyName.'</li>
								<li><span id="companyTitleEn">'.$companyNameEn.'</span></li>
								<li class="small-text"><span id="companyAddress">'.$companyAddress.'</span></li>
								<li class="small-text" ><span id="companyPhone">'.$companyContact.'</span></li>
							</ul>
						</td>
						<td width="20%">
							<span class="project-name" id="projectName"></span>
						</td>
					</tr> 
				</table>
				<table width="100%" style="margin-bottom:10px;">
					<tr>
						<td width="20%" ></td>
						<td width="60%" valign="top">
							<ul class="reportTitle">
								<li style="font-size:'.FONTSIZE_REPORT.'; font-family:'."'Khmer OS Muol Light'".'"><span id="reportTitle"></span></li>
								<li style="font-size:12px;"><span id="dateReport"></span></li>
								<li style="font-size:12px;"><span id="staff_lbl"></span></li>
							</ul>
						</td>
						<td width="20%"></td>
					</tr> 
				</table>
			';
		}
		
		return $string;
		
	}
}