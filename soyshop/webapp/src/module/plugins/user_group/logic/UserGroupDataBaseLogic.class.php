<?php

class UserGroupDataBaseLogic extends SOY2LogicBase{

	function __construct(){
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function addColumn($key, $type){
		if(!preg_match("/^[[0-9a-zA-Z-_]+$/", $key)) return false;

		$sql = "ALTER TABLE soyshop_user_group_custom_search ADD COLUMN " . $key . " ";

		switch($type){
			case UserGroupCustomSearchFieldUtil::TYPE_INTEGER:
			case UserGroupCustomSearchFieldUtil::TYPE_RANGE:
			case UserGroupCustomSearchFieldUtil::TYPE_DATE:
				$sql .= "INTEGER";
				break;
			case UserGroupCustomSearchFieldUtil::TYPE_TEXTAREA:
			case UserGroupCustomSearchFieldUtil::TYPE_RICHTEXT:
				$sql .= "TEXT";
				break;
			default:
				$sql .= "VARCHAR(255)";
		}

		$dao = new SOY2DAO();

		try{
			$dao->executeUpdateQuery($sql, array());
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	function deleteColumn($key){
		if(!preg_match("/^[[0-9a-zA-Z-_]+$/", $key)) return;

		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery("ALTER TABLE soyshop_user_group_custom_search DROP COLUMN " . $key, array());
		}catch(Exception $e){
			//SQLiteではカラムを削除できない
		}
	}

	/**
	 * @params itemId integer, values array(array("field_id" => string))
	 */
	function save($groupId, $values){

		$sets = array();

		foreach(UserGroupCustomSearchFieldUtil::getConfig() as $key => $field){
			if(!isset($values[$key])) {
				$sets[$key] = null;
				continue;
			}

			switch($field["type"]){
				case UserGroupCustomSearchFieldUtil::TYPE_INTEGER:
				case UserGroupCustomSearchFieldUtil::TYPE_RANGE:
					$sets[$key] = (is_numeric($values[$key])) ? (int)$values[$key] : null;
					break;
				case UserGroupCustomSearchFieldUtil::TYPE_CHECKBOX:
					if(is_array($values[$key]) && count($values[$key])){
						$sets[$key] = implode(",", $values[$key]);

					//一括更新の際は、そのまま値を入れなければならない 一応条件分岐は残しておく
					}elseif(strpos($values[$key], ",")){
						$sets[$key] = trim($values[$key]);

					//値が一つの時はカンマがないので未加工で挿入する
					}elseif(strlen($values[$key])){
						$sets[$key] = trim($values[$key]);

					//その他の処理
					}else{
						$sets[$key] = null;
					}
					break;
					case UserGroupCustomSearchFieldUtil::TYPE_DATE:
						if(strlen($values[$key])){
							$dateArray = explode("-", $values[$key]);
							$sets[$key] = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
						}else{
							$sets[$key] = null;
						}
						break;
				default:
					$sets[$key] = (strlen($values[$key])) ? $values[$key] : null;
			}
		}
		$this->insert($groupId, $sets);
	}

	function insert($groupId, $sets){
		$columns = array();
		$values = array();
		$binds = array();

		$columns[] = "group_id";
		$values[] = (int)$groupId;

		foreach($sets as $key => $value){
			$columns[] = $key;
			$values[] = ":" . $key;
			$binds[":" . $key] = $value;
		}

		$sql = "INSERT INTO soyshop_user_group_custom_search ".
				"(" . implode(",", $columns) . ") ".
				"VALUES (" . implode(",", $values) . ")";

		$dao = new SOY2DAO();

		try{
			$dao->executeQuery($sql, $binds);
		}catch(Exception $e){
			$this->update($groupId, $columns, $values, $binds);
		}
	}

	function update($groupId, $columns, $values, $binds){
		$sql = "UPDATE soyshop_user_group_custom_search SET ";
		$first = true;
		foreach($columns as $i => $column){
			if($column == "group_id") continue;
			if(!$first) $sql .= ", ";
			$first = false;
			$sql .= $column . " = " . $values[$i];
		}
		$sql .= " WHERE group_id = " . $groupId;
		$dao = new SOY2DAO();
		try{
			$dao->executeUpdateQuery($sql, $binds);
		}catch(Exception $e){
			//
		}
	}

	function delete($groupId){
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery("DELETE FROM soyshop_user_group_custom_search WHERE group_id = :group_id", array(":group_id" => $groupId));
		}catch(Exception $e){
			//
		}
	}

	function migrate(){}

	function getByGroupId($groupId){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery("SELECT * FROM soyshop_user_group_custom_search WHERE group_id = :group_id LIMIT 1", array(":group_id" => $groupId));
		}catch(Exception $e){
			return array();
		}

		return (isset($res[0])) ? $res[0] : array();
	}
}
