<?php
if($row_brance_to["amount_dollar"] < $dollar_transfer or $row_brance_to["amount_bath"] < $bath_transfer or $row_brance_to["amount_riel"] < $riel_transfer){
   						Application_Form_FrmMessage::message("Money not enought");
   						//continue;
   					}else {
   						$update_brance_to  = array(
					   								'amount_dollar'		=>	$_data['usa'],
					   								'amount_riel'		=>	$_data['reil'],
					   								'amount_bath'		=>	$_data['bath'],
   						);
   						$this->_name = "ln_branch_capital";
   						$where = $db->quoteInto("branch_id=?", $_data["brance_to"]);
   						$this->update($update_brance_to, $where);
   					}