<?php

class Application_Form_FrmPopupGlobal extends Zend_Dojo_Form
{
	public function init()
	{
		
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
					@media print{
							@page{
								margin:0.5cm 0.5cm 0.0cm 0.5cm;
								page:4;size:portrait;
							}
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
		}elseif ($reciept_type==5){ //Vue Auston
		$str='
				<style>
					span.titelTop, span.valueData {
						font-size: 12px;
					}
					.rowDivTitle {
						display: inline-block;
						vertical-align: top;
						min-width: 95px;
					}
					.rowDivTitle span.titelTop {
						font-weight: bold;
						color: #e56600;
						display: block;
						line-height: 16px;
					}
					.rowDivTitle span.titelTop.en {
						font-weight: 200;
					}
					.rowValueBg {
						display: inline-block;
						vertical-align: top;
						min-width: 160px;
						background: #e5660087;
						padding: 5px 0;
					}
					.rowValueBg span.valueData {
						text-align: center;
						display: block;
						width: 100%;
						padding: 0 2px;
					}
					span.postingdate,
					div.customerName{
						position: absolute;
						top: 160px;
					}
					span.postingdate {
					    left: 40px;
					}
					div.customerName {
						right: 40px;
					}
					.receiptNoBlog {
						position: absolute;
						top: 70px;
						right: 50px;
						font-weight: bold;
						vertical-align: top;
					}
					.receiptTl {
						display: inline-block;
						vertical-align: top;
						min-width: 70px;
						line-height: 14px;
					}
					.receiptTl span.titelTop {
						display: block;
						color: #e66c06;
					}
					.rowValue {
						display: inline-block;
						vertical-align: top;
						min-width: 90px;
					}
					span.receipt-class {
						color: #f00;
						font-size: 18px;
						vertical-align: top;
						margin-top: -5px;
						display: block;
					}
					
					
					
					
					
					div.logoBlog {
						text-align: center;
						padding-top: 35px;
					}
					span.receptTitle {
						POSITION: absolute;
						top: 120px;
						font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
						width: 100%;
						text-align: center;
					}
					span.receptTitle .enTitle{
						font-weight:bold;
					}
					
					
					.border-seperate,
					.border-seperate-two {
						width: 91%;
						height: 1px;
						margin: 0 auto;
						border-bottom: solid 1px #ccc;
					}
					.border-seperate {
						margin-top: 80px;
					}
					.border-seperate-two {
						margin-top: 300px;
					}
					@page {
						margin:0;
					}
					
					.rowData {
						position: absolute;
						left: 40px;
						width: 100%;
						margin: 0 2px;
					}
					.rowData.row-one {
						top: 215px;
						
					}
					.rowData.row-two {
						top: 280px;
						
					}
					.rowData.row-three {
						top: 345px;
						
					}
					div.blogRow {
						display: inline-block;
						vertical-align: top;
					}
					div.bgAmount {
						width: 30%;
					}
					div.bgAmountChar {
						width: 60%;
					}
					
					div.bgDescription {
						width: 60%;
					}
					div.bgPropertyNum {
						width: 30%;
					}

					.rowDataTitle {
						font-weight: 600;
					}
					.rowDataValue {
						background: #d3d3d333;
						margin: 4px 0;
						line-height: 16px;
						min-height: 32px;
					}
					span.valueDataRow {
						padding: 8px 4px;
						display: block;
						line-height: 16px;
						white-space: normal;
					}
					div.bgAmount span.valueDataRow {
						font-weight: 600;
					}
					.blogRow.bgPaymentMethod{
						width: 25%;
					}
					.blogRow.bgCustomerSign, 
					.blogRow.bgReceiverSign {
						width: 32.2%;
					}
					
					.blogRow.bgCustomerSign .rowDataValue, 
					.blogRow.bgReceiverSign .rowDataValue {
						height: 120px;
					}
					span.nonrefundable {
						position: absolute;
						left: 40px;
						bottom: 75px;
						color: #e56600;
						font-weight: 600;
						font-size: 10px;
					}
					.footerRecieptNew {
						position: absolute;
						bottom: 38px;
						color: #e56600;
						width: 100%;
					}
					.blogFooter {
						display: inline-block;
					}
					.blogFooter.footLeft {
						width: 40%;
						padding-left: 40px;
					}
					.blogFooter.footRight {
						text-align: right;
						width: 50%;
					}
					span.footerTitle {
						font-family: '."'Times New Roman'".','."'Khmer OS Muol Light'".';
						font-weight: 600;
					}
					span.footerAddress {
						font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".'; 
						font-size: 11px;
					}
					
					span.signatureBlog {
						position: absolute;
						bottom: 105px;
					}
					span.signatureBlog.userSingName {
						right: 50px;
					}
					span.signatureBlog.CustomerSingName {
						right: 305px;
					}
				</style>
				<div class="" style=" background-image: url('.$baseurl."/images/vue-aston-receipt.jpg".');background-size: 100%; font-size: 14px; font-family: '."'Times New Roman'".','."'Khmer OS Battambang'".';  color: #000; width: 21cm; height: 15cm;padding: 0px;margin: 0 auto;position: relative; " >
				<div id="projectlogo" class="logoBlog">
					<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
				</div>
				<span class="receptTitle">
				បង្កាន់ដៃបង់ប្រាក់ <span class="enTitle"> RECEIPT</span>
				</span>
				<div style="display: none;">
					<span id="lbl_project"></span>
					
					<span id="lb_saleprice"></span>
					<span id="lbl_total_paid1"></span>
					<span id="lbl_balance"></span>
					<span id="lb_descriptionall"></span>
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
					
					<span id="lb_customercode"></span>
					
					
				</div>
				<span class="postingdate">
						<div class="rowDivTitle">
							<span class="titelTop">កាលបរិច្ឆេទ</span>
							<span class="titelTop en">Date</span>
						</div>
						<div class="rowValueBg">
							<span id="lblpaid_date" class="valueData"></span>
						</div>
				</span>
				<div class="customerName">
						<div class="rowDivTitle">
							<span class="titelTop">បានទទួលពី</span>
							<span class="titelTop en">Received From</span>
						</div>
						<div class="rowValueBg">
							<span id="lb_customer" class="valueData"></span>
						</div>
				</div>
				<div class="border-seperate"></div>
				<div class="rowData row-one">
						<div class="blogRow bgAmount">
							<div class="rowDataTitle">
								ចំនួន (ដុល្លា) / Amount (USD)
							</div>
							<div class="rowDataValue">
								<span id="lbl_total_receive" class="valueDataRow"></span>
							</div>
						</div>
						<div class="blogRow bgAmountChar">
							<div class="rowDataTitle">
								ចំនួនទឹកប្រាក់ជាអក្សរ / Amount In Words
							</div>
							<div class="rowDataValue">
								<span id="lable_chartotalreceipt" class="valueDataRow"></span>
							</div>
						</div>
				</div>
				<div class="rowData row-two">
						<div class="blogRow bgDescription">
							<div class="rowDataTitle">
								គោលបំណងនៃការទូទាត់ / Purpose Of Payment
							</div>
							<div class="rowDataValue">
								<span id="lb_noted" class="valueDataRow"></span>
							</div>
						</div>
						<div class="blogRow bgPropertyNum">
							<div class="rowDataTitle">
								លេខបន្ទប់ / Room Number
							</div>
							<div class="rowDataValue">
								<span id="lb_hourseno" class="valueDataRow"></span>
							</div>
						</div>
				</div>
				<div class="rowData row-three">
						<div class="blogRow bgPaymentMethod">
							<div class="rowDataValue">
								<span id="lbl_paymenttype" class="valueDataRow"></span>
							</div>
							
							<div class="rowDataValue">
								<span id="lbl_cheque" class="valueDataRow"></span>
							</div>
						</div>
						<div class="blogRow bgCustomerSign">
							
							<div class="rowDataValue">
								
							</div>
							<div class="rowDataTitle">
								ហត្ថលេខាអតិថិជន / Purchaser Signature
							</div>
						</div>
						<div class="blogRow bgReceiverSign">
							<div class="rowDataValue">
								
							</div>
							<div class="rowDataTitle">
								ហត្ថលេខាបេឡាករ / Seller Signature
							</div>
							
						</div>
				</div>
				
				
				<div class="receiptNoBlog">
					<div class="receiptTl">
						<span class="titelTop">លេខរៀង</span>
						<span class="titelTop en">No</span>
					</div>
					<div class="rowValue">
						<span id="lb_receipt" class="receipt-class"></span>
					</div>
				</div>	
				
				<span class="signatureBlog CustomerSingName" id="lbl_customer"></span>
				<span class="signatureBlog userSingName" id="lbl_usersale">'.$last_name." ".$username.'</span>
				
				<span class="nonrefundable">មិនអាចដកវិញបាន / Non Refundable</span>
				<div class="border-seperate-two"></div>
				
				<div class="footerRecieptNew">
					<div class="blogFooter footLeft"><span class="footerTitle">'.$tr->translate("BRAND_FOOTER_TITLE").'</span></div>
					<div class="blogFooter footRight"><span class="footerAddress">'.$data["footer_branch"].'</span></div>
				</div>
				<input type="hidden" dojoType="dijit.form.TextBox" value="0" name="is_showinstallment" id="is_showinstallment" />
				
				
				
				
				
				
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
				@media print{
					@page{
						margin:0.5cm 0.5cm 0.0cm 0.5cm;
						page:4;size:portrait;
					}
				}
				span#lb_customercode{
					display:block;
					line-height:14px;
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
					<td>ឈ្មោះអតិថិជន </td>
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
		
		$str='<table class="footerLabel" align="center" width="100%">
				   <tr style="font-size: 12px;">
				        <td style="width:30%; text-align:center;  font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".'; vertical-align: top;">'.$tr->translate('APPROVED BY').'</td>
				        <td style="width:30%; text-align:center; font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".'; vertical-align: top;">'.$tr->translate('VERIFYED BY').'</td>
				        <td style="width:30%; text-align:center; font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".'; vertical-align: top;">'.$tr->translate('PREPARE BY').'<br /><br /><br /><br />'.$last_name." ".$username.'</td>
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
						ស្នាមមេដៃអ្នកទទួល
						</span>
					</td>
				    <td colspan="2" class="khmerbold" style="line-height: 14px;" align="center" >
						<span style=" font-family: Arial Black;font-family:'."'Khmer OS Muol Light'".';">
						ស្នាមមេដៃរដ្ឋបាល
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
				ul.paymentlist{width:100%; }
				ul.paymentlist li{ float:left;list-type:none; list-style-type:none;padding-right:10px;
	 			font: 16px '."'Times New Roman'".','."'Khmer OS Battambang'".';}
	 			ul.paymentlist li paymentlist{ padding:5px 10px;}
	 			.conditionpayment{font: 16px '."'Times New Roman'".','."'Khmer OS Battambang'".';}
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
				<tr class="lblpaymentdetail">
					<td colspan="4" class="conditionpayment">លក្ខខណ្ឌបង់ប្រាក់លម្អិត</td>
				</tr>
				<tr class="lblpaymentdetail">
					<td colspan="4" style="border-bottom:1px dotted #000;"></td>
				</tr>
				<tr class="lblpaymentdetail">
					<td colspan="4"><ul class="paymentlist"><li>រយៈពេលបង់ <label id="lbl_qty"></label></li> <li>តម្លៃ <label id="lbl_price"></label></li>​<li ><strong style="font-weight:bold;">ទឹកប្រាក់ត្រូវបង់សរុប <label id="lbl_amount"><label></strong></li> <li>សុពលភាពបង់ <label id="lbl_validate"></label></li></ul></td>
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
		                	<td width="30%" id="projectlogo"><img src="'.$defaultLogo.'" style="max-height:85px; max-width: 100%;" ></td>
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
				<table class="tableTop" width="100%" border="0" style="border-bottom: double 5px #337ab7;">
					<tr>
						<td width="20%" id="projectlogo"><img src="'.$defaultLogo.'" style="max-height:85px; max-width: 100%; " ></td>
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
	
	function printByFormat(){
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$userInfo = $dbGb->getUserInfo();
		$currentUserName = $userInfo['user_name'];
	
		$string='
				<style>
					ul.printInfo {
					position: absolute;
					right: 0;
					top: 35px;
					list-style: none;
					margin: 0;
					padding: 0;
					visibility: hidden;
				}

				ul.printInfo li {
					font-family:'.'"Times New Roman"'.','.'"Khmer OS Battambang"'.';
					font-size: 9px;
					text-align: left;
					line-height:10px;
				}
				@media print {
					ul.printInfo{visibility: visible;}
				}
				</style>
				<ul class="printInfo">
					<li>Print Date / Time : '.date(DATE_FORMAT_FOR_PHP." H:i:s").'</li>
					<li>Print By : '.$currentUserName.'</li>
				<ul>
		';
		return $string;
	}
	
	function getCombinePaymentOfficialReciept($filterOption=array()){//general
	
		
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
		
		
		$str='
				<style>
					*, :after, :before {
							box-sizing: unset;
						}
					.dataInfo {
						display:block; 
						text-align:left; 
						font-size:12px; 
						line-height:16px; 
						font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".';
					}
					td span {
						line-height: 16px;
						font-size: 12px;
					}
					table.content-data{
						border-collapse:collapse;
						border:1px solid #000; 
						font-size:12px; 
						font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".';
					}
					table.content-data thead tr.style {
						line-height: 20px;
						font-size: 12px !important;
						padding: 1px 0px;
						white-space: nowrap;
						height: 20px;
						background: #c1d0f3;
						font-weight: bold;
						text-align: center;
					}
					table.content-data tr.style {
						white-space: nowrap;
					}
					table.content-data tr td{
						padding:1px  2px;
					}
					table.content-data tr.odd {
						background: #eee;
					}
					td.colNote{
						width:120px;
					}
					td.noBorderBt {
						border-bottom: none;
					}
					td.noBorderTop {
						border-top: none;
					}
					p {
						margin: 0;
						line-height: inherit;
						font-size: inherit;
					}
					table.content-bank{
						border-collapse:collapse;
						border-top:0px solid #000; 
						font-size:12px;
						font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".';
						width:80%;
						text-align: center;
						margin: 0 auto;
					}
					span#lblpaid_date, span#lb_receipt {
						color: red;
						font-weight: bold;
					}
					td#bankPaymentInfo {
						border-top: solid 1px #000;
						padding-top: 10px;
						text-align: center;    margin: 0 auto;
					}
					table.content-bank th{
						font-family:'."'Times New Roman'".','."'Khmer OS Muol Light'".';
						font-weight:normal;
						text-align:center !important;
						padding-top: 10px;
					}
					table.content-bank td{
						padding-left:10px;
					}
					span.blockLine {
						display: block;
						line-height: 16px;
						text-align: left;
					}
					strong.classBlue {
						color: #3F51B5;
					}
					span.signBank {
						font-size: 16px;
						line-height: 16px;
					}
				</style>
				<table  width="100%" style="font-family:'."'Times New Roman'".','."'Khmer OS Battambang'".'; padding:0; margin:0; border:0px;" >
					<tr>
						<td width="30%" valign="top">
							<div id="projectlogo" class="logoBlog">
								<img style="height:80px; max-width: 100%;" src="'.$baseurl.'/images/bppt_logo.png">
							</div>
						</td>
						<td width="40%" align="center">
							<span style="text-align:center; font-size:18px; line-height:26px; font-family:'."'Khmer OS Muol Light'".';">ព្រះរាជាណាចក្រកម្ពុជា</span>
							<span style="text-align:center; font-size:14px; line-height:26px; width: 100%; display: block;font-family:'."'Khmer OS Muol Light'".';">ជាតិ សាសនា ព្រះមហាក្សត្រ</span>
							<span style="text-align:center; line-height:26px; width: 100%; display: block;  "><img src="'.$baseurl.'/images/agreementsign.jpg" height="18px"></span>
							
							<span style="text-decoration: underline; text-align:center; font-size:16px; line-height:24px; font-family:'."'Khmer OS Muol Light'".';">កិច្ចសន្យាបន្ថែមប្រាក់</span>
							<br />
							<span style="text-align:center; white-space:nowrap; font-size:18px; line-height:24px; font-family:'."'Times New Roman'".'; ">Recive Payment From Customer</span>
						   
						</td>
						<td width="30%" valign="top" align="center">
							<div style="height:80px; max-width: 100%;"></div>
							<span id="lb_receipt" class="receipt-class"></span>
							<br />
							<span id="lblpaid_date" class="valueData"></span>
						</td>
					</tr>
					<tr>
						<td colspan="3" valign="top">
							<span class="dataInfo" >
							-អតិថិជនឈ្មោះ <span id="lb_customer" class="valueData"></span>
							ភេទ <span id="customerGender" class="customerGender"></span> 
							
							គម្រោង <strong id="lbl_project" class="valueDataRow"></strong>
							</span>
							<span class="dataInfo" >
							-'.$tr->translate("PHONE").' <span id="lbl_phone" class="value"></span>
							</span>
							<span class="dataInfo" >
							-'.$tr->translate("LOCATION").' <span id="projecatLocation" class="value"></span>
							</span>
							<span class="dataInfo" >
							-តម្លៃសរុប <span id="lb_saleprice"></span>
							</span>
							
						</td>
					</tr>
					<tr>
						<td colspan="3" valign="top" id="tableContentInfo" >
							
						</td>
					</tr>
					<tr>
						<td colspan="3" valign="top">
							<span class="dataInfo" >
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;សរុបបង់បាន <strong id="lbl_total_paid1"></strong> នៅខ្វះ <strong id="lbl_balance"></strong> <span id="paymentOptintionDesc"></span> 
							</span>
							
						</td>
					</tr>
					
					<tr>
						<td colspan="3" valign="top" >
							<table class="signature-table" width="100%" border="0">
								<tr style="font-family: '."'Khmer OS Muol Light'".';">
									<td width="30%">&nbsp;
									អ្នកទទួល
									</td>
									<td align="center" width="40%">
									
									</td>
									<td align="center" width="30%">
									អ្នកប្រគល់
									</td>
								</tr>
								<tr height="55px">
									<td colspan="3">&nbsp;
									</td>
								</tr>
								<tr>
									<td width="30%"><span id="lbl_usersale" >'.$last_name." ".$username.$usertype.'</span></td>
									<td align="center" width="40%">
									
									</td>
									<td align="center" width="30%">
									<span id="lbl_customer" ></span>
									</td>
								</tr>
							</table>
							<br />
						</td>
					</tr>
					<tr>
						<td colspan="3" valign="top" id="bankPaymentInfo">
						</td>
					</tr>
					
				</table>
				<div style="display: none;">
					
					
					
					'.$footer.'
					
					
				</div>
			';
			
		return $str;
	}
}