<?php

class SearchLogic extends SOY2LogicBase{

	private $fieldId;
	private $config;
	private $limit;
	private $userDao;

	private $where = array();
	private $binds = array();

	function __construct(){
		SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
		$this->config = UserCustomSearchFieldUtil::getConfig();
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}

	function get(){
		self::register();

		$sql = self::buildQuery();

		try{
			$res = $this->userDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			var_dump($e);
			$res = array();
		}

		if(!count($res)) return array();

		$users = array();
		foreach($res as $v){
			$users[] = $this->userDao->getObject($v);
		}

		return $users;
	}

	private function buildQuery(){
		$sql = "SELECT u.* from soyshop_user u ".
				"INNER JOIN soyshop_user_custom_search s ".
				"ON u.id = s.user_id ".
				"WHERE u.is_disabled != " . SOYShop_User::USER_IS_DISABLED . " ";

		foreach($this->where as $where){
			$sql .= " AND " . $where;
		}

		$sql .= " Limit " . $this->limit;

		return $sql;
	}

	function setCondition($conditions){
		if(count($conditions)) foreach($conditions as $key => $value){
			switch($key){
				case $this->fieldId:
					switch($this->config[$this->fieldId]["type"]){
						case UserCustomSearchFieldUtil::TYPE_CHECKBOX:
							foreach($value as $i => $v){
								$this->where[] = "s." . $this->fieldId . " LIKE :" . $this->fieldId . $i;
								$this->binds[":" . $this->fieldId . $i] = "%" . trim($v) . "%";
							}

							break;
						default:
							$this->where[] = "s." . $this->fieldId . " LIKE :" . $this->fieldId;
							$this->binds[":" . $this->fieldId] = "%" . trim($value) . "%";
					}
					break;
				case "nothing":
					$this->where[] = "s." . $this->fieldId . " IS NULL";
					break;
				default:
					$this->where[] = "u." . $key . " LIKE :" . $key;
					$this->binds[":" . $key] = "%" . trim($value) . "%";
			}
		}
	}

	private function register(){
		try{
			$res = $this->userDao->executeQuery("SELECT user_id FROM soyshop_user_custom_search ORDER BY user_id DESC LIMIT 1;", array());
		}catch(Exception $e){
			return;
		}

		if(!isset($res[0]["item_id"])) return;

		$lastId = (int)$res[0]["item_id"];

		try{
			$res = $this->userDao->executeQuery("SELECT id FROM soyshop_user WHERE id > :id;", array(":id" => $lastId));
		}catch(Exception $e){
			return;
		}

		if(!count($res)) return;

		foreach($res as $v){
			try{
				$this->userDao->executeQuery("INSERT INTO soyshop_user_custom_search (user_id) VALUES (:id)", array(":id" => $v["id"]));
			}catch(Exception $e){
				//
			}
		}
	}

	function setFieldId($fieldId){
		$this->fieldId = $fieldId;
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
}
?>
