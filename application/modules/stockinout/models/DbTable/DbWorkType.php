<?php

class Stockinout_Model_DbTable_DbWorkType extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_work_type';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllWorkType($search)
	{
		$sql = "SELECT wt.id,
    			wt.workTitle,
    		(SELECT t.workTitle FROM $this->_name As t WHERE t.id=wt.parentId LIMIT 1) AS parentTitle,
	    	wt.createDate,
	    	(SELECT first_name FROM rms_users as u WHERE u.id = wt.userId LIMIT 1) as user ,
    		(SELECT name_en FROM ln_view WHERE type=3 and key_code = wt.status LIMIT 1) AS status
    	FROM $this->_name As wt
    		WHERE 1
    	";

		$from_date = (empty($search['start_date'])) ? '1' : "wt.createDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " wt.createDate <= '" . $search['end_date'] . " 23:59:59'";

		$where_date = " AND " . $from_date . " AND " . $to_date;
		$where = '';

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes((trim($search['adv_search'])));
			$s_where[] = " wt.workTitle LIKE '%{$s_search}%'";
			$where .= ' AND ( ' . implode(' OR ', $s_where) . ')';
		}
		if ($search['status'] > -1 and $search['status'] != '') {
			$where .= " AND wt.status = " . $search['status'];
		}

		$order = ' ORDER BY wt.id DESC  ';

		$db = $this->getAdapter();
		return $db->fetchAll($sql . $where_date . $where . $order);
	}

	function addWorkType($data)
	{
		try {
			$db = new Application_Model_DbTable_DbGlobalStock();
			$result = $this->ifWorkTypeExisting($data);

			if (empty($result)) {
				$arr = array(
					'parentId' => $data['parent_id'],
					'workTitle' => $data['workTitle'],
					'createDate' => date("Y-m-d"),
					'status' => 1,
					'userId' => $this->getUserId(),
				);
				$this->insert($arr);
			} else {
				Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/stockinout/worktype/add", 2);
			}
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/worktype/add", 2);
		}
	}
	function updateWorkType($data)
	{
		try {
			$result = $this->ifWorkTypeExisting($data);
			if (!empty($result)) {
				$arr = array(
					'parentId' => $data['parent_id'],
					'workTitle' => $data['workTitle'],
					'status' => $data['status'],
					'userId' => $this->getUserId(),
				);

				$where = 'id = ' . $data['id'];
				$this->update($arr, $where);

			} else {
				Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/stockinout/worktype/index", 2);
			}

		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::Sucessfull("UPDATE_FAIL", "/stockinout/worktype/index", 2);
		}
	}
	function ifWorkTypeExisting($data)
	{

		$db = $this->getAdapter();
		$sql = " SELECT * FROM $this->_name WHERE workTitle='" . addslashes((trim($data['workTitle']))) . "'";
		if (!empty($data['id'])) {
			$sql .= " AND id !=" . $data['id'];
		}
		return $db->fetchRow($sql);
	}
	function getDataRow($recordId)
	{
		$db = $this->getAdapter();
		$sql = " SELECT * FROM $this->_name WHERE id=" . $recordId . " LIMIT 1";
		return $db->fetchRow($sql);
	}

}