<?php

class SearchUserLogic extends SOY2LogicBase{

	private $query;
	private $limit;
	private $offset;
	private $order;
	private $where;
	private $binds;

	const SORT_ID				= "id";
	const SORT_ID_DESC		   = "id_desc";
	const SORT_NAME			  = "name";
	const SORT_NAME_DESC		 = "name_desc";
	const SORT_READING		   = "reading";
	const SORT_READING_DESC	  = "reading_desc";
	const SORT_MAIL_ADDRESS	  = "mail_address";
	const SORT_MAIL_ADDRESS_DESC = "mail_address_desc";
	const SORT_ACCOUNT_ID	  = "account_id";
	const SORT_ACCOUNT_ID_DESC = "account_id_desc";
	const SORT_ATTRIBUTE_1 = "attribute1";
	const SORT_ATTRIBUTE_1_DESC = "attribute1_desc";
	const SORT_ATTRIBUTE_2 = "attribute2";
	const SORT_ATTRIBUTE_2_DESC = "attribute2_desc";
	const SORT_ATTRIBUTE_3 = "attribute3";
	const SORT_ATTRIBUTE_3_DESC = "attribute3_desc";

	private $sorts = array(

		"id" =>  "id",
		"id_desc" =>  "id desc",

		"name" =>  "item_name",
		"name_desc" =>  "item_name desc",

		"reading" =>  "reading",
		"reading_desc" =>  "reading desc",

		"mail_address" =>  "mail_address",
		"mail_address_desc" =>  "mail_address desc",

		"account_id" =>  "account_id",
		"account_id_desc" =>  "account_id desc",

		"attribute1" => "attribute1",
		"attribute1_desc" => "attribute1 desc",

		"attribute2" => "attribute2",
		"attribute2_desc" => "attribute2 desc",

		"attribute3" => "attribute3",
		"attribute3_desc" => "attribute3 desc",
	);

	const TABLE_NAME = "soyshop_user";

	function getQuery(){
		if(is_null($this->query)){
			SOY2DAOConfig::setOption("limit_query", true);
			$this->query = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		}

		return $this->query;
	}

	function setLimit($value){
		$this->limit = $value;
	}
	function setOffset($value){
		$this->offset = $value;
	}

	function setOrder($sort){
		switch($sort){
			case self::SORT_ID :
				$order = " order by id ";
				break;
			case self::SORT_ID_DESC :
				$order = " order by id desc ";
				break;
			case self::SORT_NAME :
				$order = " order by name ";
				break;
			case self::SORT_NAME_DESC :
				$order = " order by name desc ";
				break;
			case self::SORT_READING :
				$order = " order by reading ";
				break;
			case self::SORT_READING_DESC :
				$order = " order by reading desc ";
				break;
			case self::SORT_MAIL_ADDRESS :
				$order = " order by mail_address ";
				break;
			case self::SORT_MAIL_ADDRESS_DESC :
				$order = " order by mail_address desc ";
				break;
			case self::SORT_ACCOUNT_ID :
				$order = " order by account_id ";
				break;
			case self::SORT_ACCOUNT_ID_DESC :
				$order = " order by account_id desc ";
				break;
			case self::SORT_ATTRIBUTE_1 :
				$order = " order by attribute1 ";
				break;
			case self::SORT_ATTRIBUTE_1_DESC :
				$order = " order by attribute1 desc ";
				break;
			case self::SORT_ATTRIBUTE_2 :
				$order = " order by attribute2 ";
				break;
			case self::SORT_ATTRIBUTE_2_DESC :
				$order = " order by attribute2 desc ";
				break;
			case self::SORT_ATTRIBUTE_3 :
				$order = " order by attribute3 ";
				break;
			case self::SORT_ATTRIBUTE_3_DESC :
				$order = " order by attribute3 desc ";
				break;
			default:
				$order = " order by id desc";
		}
		$this->order = $order;
	}

	function getSorts(){
		return $this->sorts;
	}

	function setSearchCondition($cnds, $custom = array()){

		$where = array();
		$binds = array();
		if(count($cnds)){
			foreach($cnds as $key => $value){
				if( is_string($value) && strlen($value) ){
					switch($key){
						case "area" :
						case "job_area" :
							if(!is_numeric($value)) break;
						case "id":
						case "name":
						case "reading":
						case "mail_address":
						case "account_id":
						case "user_code":
						case "zip_code" :
						case "address1" :
						case "address2" :
						case "telephone_number" :
						case "fax_number" :
						case "cellphone_number" :
						case "job_name" :
						case "job_zip_code" :
						case "job_address1" :
						case "job_address2" :
						case "job_telephone_number" :
						case "job_fax_number" :
						case "memo" :
						case "attribute1":
						case "attribute2":
						case "attribute3":
						case "shop_error_count" :
							$where[] = " $key like :$key ";
							$binds[":$key"] = "%" . $value."%";
							break;
						case "is_disabled" :
							$where[] = ($value == 1) ? " $key = 1 " : " $key != 1 " ;
							break;
					}
				}
				if( is_array($value) && count($value) ){
					switch($key){
						case "gender" :
							$where_gender = array();
							foreach($value as $key_2 => $value_2){
								if( is_string($value_2) && strlen($value_2) ){
									switch($key_2){
										case "male" :
											$where_gender[] = " gender = 0 ";
											break;
										case "female" :
											$where_gender[] = " gender = 1 ";
											break;
										case "other" :
											$where_gender[] = " gender != 0 AND gender != 1 ";
											break;
									}
								}
							}
							if(count($where_gender)) $where[] = " ( ".implode(" OR ", $where_gender). " ) ";
							break;
						case "birthday" :
							//年
							if(isset($value["year"]) && strlen($value["year"])){
								$where[] = " " . $key . " LIKE :birthday_year ";
								$binds[":birthday_year"] = (int)trim($value["year"]) . "-%";
							}
							if(isset($value["month"]) && strlen($value["month"])){
								$m = trim($value["month"]);
								if($m[0] == "0") $m = (int)substr($m, 1);
								//1〜9までの場合
								if(strlen($m) === 1){
									$where[] = " (" . $key . " LIKE :birthday_month OR " . $key . " LIKE :birthday_month1 )";
									$binds[":birthday_month1"] = "%-0" . $m . "-%";
								//10〜12の場合
								}else{
									$where[] = " " . $key . " LIKE :birthday_month ";
								}
								$binds[":birthday_month"] = "%-" . $m . "-%";
							}
							if(isset($value["day"]) && strlen($value["day"])){
								$d = trim($value["day"]);
								if($d[0] == "0") $d = (int)substr($d, 1);
								//1〜9までの場合
								if(strlen($d) === 1){
									$where[] = " (" . $key . " LIKE :birthday_day OR " . $key . " LIKE :birthday_day1 )";
									$binds[":birthday_day1"] = "%-0" . $d;
								//10〜31の場合
								}else{
									$where[] = " " . $key . " LIKE :birthday_day ";
								}
								$binds[":birthday_day"] = "%-" . $d;
							}
							break;
						case "user_type":
						case "is_publish":
							$where[] = " " . $key . " IN (" . implode(",", $value) . ") ";
							break;
						case "not_send":
							foreach($value as $key_2 => $value_2){
								if( is_string($value_2) && strlen($value_2) ){
										$where_complex[] = " " . $key . " = " . $value_2 . " ";
								}
							}
							if(count($where_complex)) $where[] = " ( ".implode(" OR ", $where_complex). " ) ";
							break;
						case "register_date" :
						case "update_date" :
							if(strlen(@$value["start"]["month"]) && strlen(@$value["start"]["day"]) && strlen(@$value["start"]["year"])){
								$value_start = @mktime(0,0,0,$value["start"]["month"],$value["start"]["day"],$value["start"]["year"]);
								$key_start = $key . "_start";
								$where[] = " $key >= :$key_start ";
								$binds[":$key_start"] = $value_start;
							}
							if(strlen(@$value["end"]["month"]) && strlen(@$value["end"]["day"]) && strlen(@$value["end"]["year"])){
								$value_end = @mktime(23,59,59,$value["end"]["month"],$value["end"]["day"],$value["end"]["year"]);
								$key_end = $key . "_end";
								$where[] = " $key <= :$key_end ";
								$binds[":$key_end"] = $value_end;
							}
							break;
						//一括設定用
						case "no":
							foreach($value as $k => $v){
								$where[] = "(" . $k . " IS NULL or " . $k . " = '')";
							}
							break;

						//注文状況
						case "order_price":
							if(isset($value["min"]) && (int)$value["min"] > 0){
								$where[] = "id IN (SELECT user_id FROM soyshop_order GROUP BY user_id HAVING SUM(price) >= :price_min)";
								$binds[":price_min"] = (int)$value["min"];
							}
							if(isset($value["max"]) && (int)$value["max"] > 0){
								$where[] = "id IN (SELECT user_id FROM soyshop_order GROUP BY user_id HAVING SUM(price) <= :price_max)";
								$binds[":price_max"] = (int)$value["max"];
							}
							break;

						case "purchase_count":
							if(isset($value["min"]) && (int)$value["min"] > 0){
								$where[] = "id IN (SELECT user_id FROM soyshop_order GROUP BY user_id HAVING COUNT(id) >= :count_min)";
								$binds[":count_min"] = (int)$value["min"];
							}
							if(isset($value["max"]) && (int)$value["max"] > 0){
								$where[] = "id IN (SELECT user_id FROM soyshop_order GROUP BY user_id HAVING COUNT(id) <= :count_max)";
								$binds[":count_max"] = (int)$value["max"];
							}
							break;
					}
				}
			}
		}

		/** カスタムサーチフィールド **/
		if(isset($custom) && is_array($custom) && count($custom)){
			SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
			$configs = UserCustomSearchFieldUtil::getConfig();

			$customWhere = array();
			foreach($custom as $key => $value){
				if((is_string($value) && !strlen($value)) || (is_array($value) && !count($value))) continue;

				if(is_string($value)){
					$customWhere[$key] = $key . " LIKE :" . $key;
					$binds[":" . $key] = "%" . trim(htmlspecialchars($value, ENT_QUOTES, "UTF-8")) . "%";
				}else if(is_array($value)){
					if(!isset($configs[$key])) continue;
					switch($configs[$key]["type"]){
						case UserCustomSearchFieldUtil :: TYPE_RANGE:
							$ws = array();
							if(isset($value["start"]) && is_numeric($value["start"])){
								$ws[] = $key . " >= :" . $key . "_start";
								$binds[":" . $key . "_start"] = (int)$value["start"];
							}
							if(isset($value["end"]) && is_numeric($value["end"])){
								$ws[] = $key . " <= :" . $key . "_end";
								$binds[":" . $key . "_end"] = (int)$value["end"];
							}
							if(count($ws)){
								$customWhere[$key] = "(" . implode(" AND ", $ws) . ")";
							}
							break;
						case UserCustomSearchFieldUtil :: TYPE_DATE:
							$ws = array();
							if(isset($value["start"]) && strlen($value["start"])){
								$ws[] = $key . " >= :" . $key . "_start";
								$binds[":" . $key . "_start"] = soyshop_convert_timestamp($value["start"], "start");
							}
							if(isset($value["end"]) && strlen($value["end"])){
								$ws[] = $key . " <= :" . $key . "_end";
								$binds[":" . $key . "_end"] = self::soyshop_convert_timestamp($value["end"], "end");
							}
							if(count($ws)){
								$customWhere[$key] = "(" . implode(" AND ", $ws) . ")";
							}
							break;
						default:
							$customWhere[$key] = $key . " IN (\"" . implode("\",\"", $value) . "\")";
					}
				}
			}

			if(count($customWhere)){
				$subquery = "(SELECT user_id FROM soyshop_user_custom_search WHERE " . implode(" AND ", $customWhere) .")";
				$where["custom"] = "id IN " . $subquery;
			}
		}

		//拡張ポイントから出力したフォーム用
		SOYShopPlugin::load("soyshop.user.search");
		$queries = SOYShopPlugin::invoke("soyshop.user.search", array(
			"mode" => "search",
			"params" => (isset($cnds["customs"])) ? $cnds["customs"] : array()
		))->getQueries();

		if(is_array($queries) && count($queries)){
			foreach($queries as $moduleId => $values){
				if(!isset($values["queries"]) || !is_array($values["queries"]) || !count($values["queries"])) continue;
				$where = array_merge($where, $values["queries"]);
				if(isset($values["binds"])) $binds = array_merge($binds, $values["binds"]);
			}
		}
		
		$this->where = $where;
		$this->binds = $binds;
	}

	protected function _countSql(){
		$countSql = "select count(*) as count from " . self::TABLE_NAME . " where ";
		if(count($this->where) > 0){
			$countSql .= implode(" and ", $this->where) . " and ";
		}
		$countSql .= "is_disabled = 0";

		return $countSql;
	}

	protected function _sql(){
		$sql = "select * from " . self::TABLE_NAME . " where ";
		if(count($this->where) > 0){
			$sql .= implode(" and ", $this->where) . " and ";
		}
		$sql .= "is_disabled = 0";

		if(strlen($this->order)) $sql .= $this->order;
		return $sql;
	}

	//合計件数取得
	function getTotalCount(){
		try{
			$countResult = $this->getQuery()->executeQuery(self::_countSql(), $this->binds);
		}catch(Exception $e){
			return 0;
		}
		return $countResult[0]["count"];
	}

	//ユーザー取得
	function getUsers(){
		$this->getQuery()->setLimit($this->limit);
		$this->getQuery()->setOffset($this->offset);
		$sql = self::_sql();
		try{
			$result = $this->getQuery()->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$result = array();
		}

		$users = array();
		foreach($result as $raw){
			$users[] = $this->getQuery()->getObject($raw);
		}

		return $users;
	}
}
