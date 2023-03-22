<?php

class Stockinout_Model_DbTable_DbReceiveTransfer extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_transfer_receive';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllReceiveTransferStock($search)
	{


		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql = "
			SELECT 
				rts.id
				,(SELECT project_name FROM `ln_project` WHERE br_id=rts.projectId LIMIT 1) AS projectName
				,rts.receiveNo
				,rts.receiveDate
				,(SELECT project_name FROM `ln_project` WHERE br_id=rts.fromProjectId LIMIT 1) AS fromProjectName
				,trs.transferNo
				,trs.transferDate
				,trs.driver
				,trs.transferer
				,(SELECT u.first_name FROM rms_users AS u WHERE u.id=rts.userId LIMIT 1 ) AS userName
				
			";
		$sql .= $dbGb->caseStatusShowImage("rts.status");
		$sql .= "
			FROM `st_transfer_receive` AS rts 
				LEFT JOIN `st_transferstock` AS trs ON trs.id = rts.transferId AND trs.toProjectId =rts.projectId
			WHERE 1
		";

		$from_date = (empty($search['start_date'])) ? '1' : " rts.receiveDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " rts.receiveDate <= '" . $search['end_date'] . " 23:59:59'";
		$where = '';
		$where_date = " AND " . $from_date . " AND " . $to_date;

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes((trim($search['adv_search'])));
			$s_where[] = " rts.receiveNo LIKE '%{$s_search}%'";
			$s_where[] = " trs.transferNo LIKE '%{$s_search}%'";
			$s_where[] = " trs.driver LIKE '%{$s_search}%'";
			$s_where[] = " trs.transferer LIKE '%{$s_search}%'";
			$s_where[] = " trs.userFor LIKE '%{$s_search}%'";

			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if ($search['status'] > -1 and $search['status'] != '') {
			$where .= " AND rts.status = " . $search['status'];
		}

		if ($search['branch_id'] > 0) {
			$where .= " AND rts.projectId = " . $search['branch_id'];
		}

		$dbg = new Application_Model_DbTable_DbGlobal();
		$where .= $dbg->getAccessPermission('rts.projectId');

		$order = ' ORDER BY rts.id DESC  ';
		$db = $this->getAdapter();
		return $db->fetchAll($sql . $where . $where_date . $order);
	}
	function getDataRow($recordId)
	{
		$db = $this->getAdapter();
		$sql = " SELECT * FROM $this->_name WHERE id=" . $recordId;
		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbg->getAccessPermission('projectId');
		return $db->fetchRow($sql);
	}
	function getReceiveDetailById($recordId)
	{
		$db = $this->getAdapter();
		$sql = " SELECT 
    				sd.*,
    				sd.proId,
					(SELECT p.proName FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proName,
					(SELECT p.proCode FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proCode,
					(SELECT p.measureLabel FROM st_product p WHERE p.proId=sd.proId LIMIT 1) measureLabel,
					sd.qtyReceive,
					sd.qtyAfterReceive,
					sd.isClosed,
					sd.note
				FROM `st_transfer_receive_detail` AS sd
				WHERE sd.receiveId=" . $recordId;
		return $db->fetchAll($sql);
	}
	function addReceiveTransferStock($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbs = new Application_Model_DbTable_DbGlobalStock();
			$receiveNo = $dbs->generateReceiveTransferNo($data);

			$transferId = $data['transferId'];
			$arr = array(
				'fromProjectId' => $data['fromProjectId'],
				'transferId' => $data['transferId'],

				'projectId' => $data['branch_id'],
				'receiveNo' => $receiveNo,
				'receiveDate' => $data['receiveDate'],

				'note' => $data['note'],
				'userId' => $this->getUserId(),
				'createDate' => date('Y-m-d H:i:s'),
				'modifyDate' => date('Y-m-d H:i:s'),
			);
			$this->_name = 'st_transfer_receive';
			$receivedId = $this->insert($arr);

			$ids = explode(',', $data['identity']);
			if (!empty($ids)) {
				foreach ($ids as $i) {

					$isClosed = 0;
					if ($data['receiveStatus' . $i] == 1) {
						$isClosed = 1;
					} else {
						if ($data['qtyReceive' . $i] >= $data['qtyAfter' . $i]) {
							$isClosed = 1;
						}
					}

					$qtyAfterReceive = $data['qtyAfter' . $i] - $data['qtyReceive' . $i];
					$arrTransferDetail = array(
						'isCompleted' => $isClosed,
						'qtyAppAfter' => $qtyAfterReceive,
						'modifyDate' => date("Y-m-d H:i:s"),
					);
					$this->_name = 'st_transferstock_detail';
					$whereTransferDetail = " transferId = $transferId AND proId=" . $data['productId' . $i];
					$this->update($arrTransferDetail, $whereTransferDetail);

					$arr = array(
						'receiveId' => $receivedId,
						'proId' => $data['productId' . $i],
						'qtyReceive' => $data['qtyReceive' . $i],
						'qtyAfterReceive' => $qtyAfterReceive,
						'price' => $data['price' . $i],
						'totalDiscount' => $data['discountAmount' . $i] / $data['qtyTransfer' . $i] * $data['qtyReceive' . $i],

						'subTotal' => $data['qtyReceive' . $i] * $data['price' . $i],
						'isClosed' => $isClosed,
						'note' => $data['note' . $i],
					);

					$this->_name = 'st_transfer_receive_detail';
					$id = $this->insert($arr);

					$param = array(
						'branch_id' => $data['branch_id'],
						'productId' => $data['productId' . $i],
						'EntyQty' => $data['qtyReceive' . $i],
						'EntyPrice' => $data['price' . $i]
					);

					$dbs->updateStockbyBranchAndProductId($param); //Update Stock qty and new costing
					$dbs->addProductHistoryQty($data['branch_id'], $data['productId' . $i], 6, $data['qtyReceive' . $i], $id); //movement'
				}
			}

			$db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/index/add", 2);
		}
	}

	function editReceiveTransferStock($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$dbs = new Application_Model_DbTable_DbGlobalStock();


			$transferId = $data['transferId'];
			$arr = array(
				'fromProjectId' => $data['fromProjectId'],
				'transferId' => $data['transferId'],

				'projectId' => $data['branch_id'],
				'receiveDate' => $data['receiveDate'],

				'note' => $data['note'],
				'status' => $data['status'],
				'userId' => $this->getUserId(),

				'modifyDate' => date('Y-m-d H:i:s'),
			);
			$this->_name = 'st_transfer_receive';
			$receivedId = $data['id'];
			$where = "id=" . $receivedId;
			$this->update($arr, $where);

			$this->reverseReceivedTransaction($receivedId, $data['transferId'], $data['branch_id']); //reverse 

			if ($data['status'] == 0) {
				$db->commit();
				return true;
			}

			$ids = explode(',', $data['identity']);
			if (!empty($ids)) {
				foreach ($ids as $i) {

					$isClosed = 0;
					if ($data['receiveStatus' . $i] == 1) {
						$isClosed = 1;
					} else {
						if ($data['qtyReceive' . $i] >= $data['qtyAfter' . $i]) {
							$isClosed = 1;
						}
					}

					$qtyAfterReceive = $data['qtyAfter' . $i] - $data['qtyReceive' . $i];
					$arrTransferDetail = array(
						'isCompleted' => $isClosed,
						'qtyAppAfter' => $qtyAfterReceive,
						'modifyDate' => date("Y-m-d H:i:s"),
					);
					$this->_name = 'st_transferstock_detail';
					$whereTransferDetail = " transferId = $transferId AND proId=" . $data['productId' . $i];
					$this->update($arrTransferDetail, $whereTransferDetail);

					$arr = array(
						'receiveId' => $receivedId,
						'proId' => $data['productId' . $i],
						'qtyReceive' => $data['qtyReceive' . $i],
						'qtyAfterReceive' => $qtyAfterReceive,
						'price' => $data['price' . $i],
						'totalDiscount' => $data['discountAmount' . $i] / $data['qtyTransfer' . $i] * $data['qtyReceive' . $i],

						'subTotal' => $data['qtyReceive' . $i] * $data['price' . $i],
						'isClosed' => $isClosed,
						'note' => $data['note' . $i],
					);

					$this->_name = 'st_transfer_receive_detail';
					$id = $this->insert($arr);

					$param = array(
						'branch_id' => $data['branch_id'],
						'productId' => $data['productId' . $i],
						'EntyQty' => $data['qtyReceive' . $i],
						'EntyPrice' => $data['price' . $i]
					);

					$dbs->updateStockbyBranchAndProductId($param); //Update Stock qty and new costing
					$dbs->addProductHistoryQty($data['branch_id'], $data['productId' . $i], 6, $data['qtyReceive' . $i], $id); //movement'
				}
			}

			$db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
			Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/index/add", 2);
		}
	}

	function reverseReceivedTransaction($receivedId, $transferId, $branchId)
	{
		$dbs = new Application_Model_DbTable_DbGlobalStock();
		$results = $this->getReceiveDetailById($receivedId);
		if (!empty($results)) {
			foreach ($results as $result) {
				$paramPro = array(
					'fetchRow' => 1,
					'isClosed' => -1,
					'transferId' => $transferId,
					'proId' => $result['proId']
				);

				$poProduct = $this->getTransferProductInfo($paramPro);
				if (!empty($poProduct)) { //update po product detail and po

					$currentAfter = $poProduct['qtyAppAfter'];
					$Receiveqty = $result['qtyReceive'];

					$arr = array(
						'qtyAppAfter' => $currentAfter + $Receiveqty,
						'isCompleted' => 0
					);

					$where = "transferId = " . $transferId . " AND proId=" . $poProduct['proId'];
					$this->_name = 'st_transferstock_detail';
					$this->update($arr, $where);

					$param = array(
						'EntyQty' => -$result['qtyReceive'],
						'branch_id' => $branchId,
						'productId' => $result['proId'],
					);
					$dbs->updateProductLocation($param);

				}

				$dbs->DeleteProductHistoryQty($result['id'], 6);
			}

			$where = "receiveId = " . $receivedId;
			$this->_name = 'st_transfer_receive_detail';
			$this->delete($where);

		}

	}
	function getTransferProductInfo($data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT
						p.*
						,(SELECT b.project_name FROM `ln_project` AS b WHERE b.br_id = p.fromProjectId LIMIT 1) AS projectName
						,(SELECT b.project_name FROM `ln_project` AS b WHERE b.br_id = p.toProjectId LIMIT 1) AS toProjectName
						,DATE_FORMAT(p.transferDate,'" . DATE_FORMAT_FOR_SQL . "') As transferDateFormat
						,DATE_FORMAT(p.createDate,'" . DATE_FORMAT_FOR_SQL . "') As createDateFormat
						
						,pd.transferId
						,pd.proId
						,(SELECT proName FROM `st_product` WHERE st_product.proId=pd.proId LIMIT 1) AS proName
						,(SELECT measureLabel FROM `st_product` WHERE st_product.proId=pd.proId LIMIT 1) AS measureLabel
						,pd.qtyRequest
						,pd.qtyApproved
						,pd.qtyAppAfter
						,pd.unitPrice
						,pd.isCompleted
					FROM
						`st_transferstock` p,
						`st_transferstock_detail` pd
					WHERE
						p.id=pd.transferId ";
		if (!empty($data['transferId'])) {
			$sql .= " AND pd.transferId = " . $data['transferId'];
		}

		if (!empty($data['proId'])) {
			$sql .= " AND pd.proId = " . $data['proId'];
		}
		if ($data['isClosed'] > -1) {
			$sql .= " AND pd.isCompleted = " . $data['isClosed'];
		}
		if (!empty($data['orderisClosedASC'])) {
			$sql .= " ORDER BY pd.isCompleted ASC ";
		}
		if (!empty($data['fetchRow'])) {
			$rs = $db->fetchRow($sql);
		} else {
			$rs = $db->fetchAll($sql);
		}

		return $rs;
	}
	function getQtyReceivedByProId($data)
	{
		$db = $this->getAdapter();
		$sql = " SELECT rd.note,rd.qtyReceive 
    					FROM `st_transfer_receive` r,
    						 `st_transfer_receive_detail` rd
					WHERE r.id=rd.receiveId ";
		if (!empty($data['proId'])) {
			$sql .= " AND proId=" . $data['proId'];
		}
		if (!empty($data['receiveId'])) {
			$sql .= " AND r.id=" . $data['receiveId'];
		}
		return $db->fetchRow($sql);
	}
	function getAllProductByTransfer($data)
	{


		$db = new Application_Model_DbTable_DbGlobalStock();
		$rs = $this->getTransferProductInfo($data);

		$string = '';
		$no = $data['keyindex'];
		$identity = '';
		$identityCheck = '';
		$strPOInfo = '';

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();

		if (!empty($rs)) {
			foreach ($rs as $key => $row) {
				if (empty($identity)) {
					$identity = $no;
				} else {
					$identity = $identity . "," . $no;
				}

				$classRowBg = "odd";
				if (($key % 2) == 0) {
					$classRowBg = "regurlar";
				}

				$qtyReceived = 0;
				$row['qtyReceive'] = $row['qtyAppAfter'];
				$note = '';
				$checked = "";
				if (!empty($data['receiveId'])) {
					$param = array(
						'proId' => $row['proId'],
						'receiveId' => $data['receiveId']
					);
					$result = $this->getQtyReceivedByProId($param);
					$qtyReceived = empty($result['qtyReceive']) ? 0 : $result['qtyReceive'];
					$note = $result['note'];
					$row['qtyReceive'] = $qtyReceived;
					$row['qtyAppAfter'] = $row['qtyAppAfter'] + $qtyReceived;
					if ($qtyReceived > 0) {
						$checked = "checked";
						if (empty($identityCheck)) {
							$identityCheck = $no;
						} else {
							$identityCheck = $identityCheck . "," . $no;
						}
					}
				}

				$Message = "rangeMessage:'" . $tr->translate('CAN_NOT_RECEIVE_OVER') . "'";
				$string .= '
		    		<tr id="row' . $no . '" class="rowData ' . $classRowBg . '" >
			    		<td align="center" style="padding: 0 10px;"><input ' . $checked . ' OnChange="CheckAllTotal(' . $no . ')" style="vertical-align: top; height: initial;" type="checkbox" class="checkbox" id="mfdid_' . $no . '" value="' . $no . '"  name="selector[]"/></td>
			    		<td class="textCenter">' . ($key + 1) . '</td>
			    		<td class="textCenter">' . $row['proName'] . '(' . $row['measureLabel'] . ')</td>
		    			<td>
							<input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyTransfer' . $no . '"  value="' . $row['qtyApproved'] . '" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="productId' . $no . '" id="productId' . $no . '" value="' . $row['proId'] . '" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="price' . $no . '" id="price' . $no . '" value="' . $row['unitPrice'] . '" style="text-align: center;" >
		    				<input type="hidden" class="fullside" name="discountAmount' . $no . '" id="discountAmount' . $no . '" value="0" style="text-align: center;" >
		    			</td>
		    			<td><input type="text" class="fullside" readonly dojoType="dijit.form.NumberTextBox" required="required" name="qtyAfter' . $no . '" id="qtyAfter' . $no . '" value="' . $row['qtyAppAfter'] . '" style="text-align: center;" ></td>
		    			<td><input type="text" class="fullside" data-dojo-props="constraints:{min:0,max:' . $row['qtyAppAfter'] . '},' . $Message . '" dojoType="dijit.form.NumberTextBox" required="required" name="qtyReceive' . $no . '" id="qtyReceive' . $no . '" value="' . $row['qtyReceive'] . '" style="text-align: center;" ></td>
		    			<td><select dojoType="dijit.form.FilteringSelect" class="fullside" name="receiveStatus' . $no . '" id="receiveStatus' . $no . '" >
		    					<option value="0">ទទួលមិនគ្រប់</option>
		    					<option value="1">ទទួលគ្រប់</option>
		    				</select>
		    			</td>
		    			<td><input type="text" class="fullside" dojoType="dijit.form.TextBox" name="note' . $no . '" id="note' . $no . '" value="' . $note . '" /></td>
		    			</tr>
	    			';
				$no++;
			}
		} else {
			$no++;
		}
		$data['fetchRow'] = 1;
		$data['isClosed'] = -1;
		$rowData = $this->getTransferProductInfo($data);

		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

		$urlPO = $baseUrl . "/report/stockmg/purchase-letter/id/";
		$urlRequest = $baseUrl . "/report/stockmg/request-letter/id/";

		$strPOInfo = '
	    		<div class="form-group" style="padding: 4px !important;">
	             	<span class="note_score">&nbsp;&nbsp; <i class="fa fa-file-text-o" aria-hidden="true"></i>&nbsp;&nbsp;
	                   	' . $tr->translate("TRANSFER_INFO") . '</span>
                   		 <ul>
                   		 	<li><span class="lbl-tt">' . $rowData['projectName'] . ' => ' . $rowData['toProjectName'] . '</span></li>
                   		 	<li><span class="lbl-tt">' . $tr->translate("TRANSFER_DATE") . '</span>: <span class="red">' . $rowData['transferDateFormat'] . '</span></li>
                   		 	<li><span class="lbl-tt">' . $tr->translate("TRANSFER_NO") . '</span>: <span class="red">' . $rowData['transferNo'] . '</span></li>
                   		 	<li><span class="lbl-tt">' . $tr->translate("DRIVER") . '</span>: <span class="red">' . $rowData['driver'] . '</span></li>
                   		 	<li><span class="lbl-tt">' . $tr->translate("DISTRIBUTOR") . '</span>: <span class="red">' . $rowData['transferer'] . '</span></li>
                   		</ul>
             	</div>';

		$array = array('stringrow' => $string, 'POInfoDataBlog' => $strPOInfo, 'keyindex' => $no, 'identity' => $identity, 'identityCheck' => $identityCheck, 'tranferInfo' => $rowData);
		return $array;
	}


	function getTransferFromDataRow($recordId)
	{
		$db = $this->getAdapter();
		$sql = " SELECT trs.*,
			COALESCE((SELECT trsd.isCompleted FROM `st_transferstock_detail` AS trsd WHERE trsd.transferId =trs.id  ORDER BY trsd.isCompleted ASC LIMIT 1 ),0) AS isCompletedReceive
		FROM st_transferstock AS trs WHERE trs.id=" . $recordId;

		$dbg = new Application_Model_DbTable_DbGlobal();
		$sql .= $dbg->getAccessPermission('trs.toProjectId');

		$sql .= " LIMIT 1";
		return $db->fetchRow($sql);
	}
}