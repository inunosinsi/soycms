<?php

class SearchInquiryLogic extends SOY2LogicBase {

	private $where = array();
    private $binds = array();
	private $limit = 15;
	private $offset = 0;

	function __construct(){
		SOY2::import("module.plugins.inquiry_on_mypage.domain.SOYShop_InquiryDAO");
		SOY2::import("module.plugins.inquiry_on_mypage.util.InquiryOnMypageUtil");
	}

	function search(){
		$sql = "SELECT * FROM soyshop_inquiry ";
		$sql .= self::_buildWhere();
		$sql .= " ORDER BY create_date DESC ";
		$sql .= " LIMIT " . $this->limit;
		$sql .= " OFFSET " . $this->offset;


		$dao = self::_dao();

		try{
			$res = $dao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $dao->getObject($v);
		}
		return $list;
	}

	function getTotal(){
		$sql = "SELECT COUNT(id) AS CNT FROM soyshop_inquiry";
		try{
			$res = self::_dao()->executeQuery($sql);
			return (isset($res[0]["CNT"])) ? (int)$res[0]["CNT"] : 0;
		}catch(Exception $e){
			return 0;
		}
	}

	private function _buildWhere(){
		self::_setSearchCondition();

		if(count($this->where)){
			return "WHERE " . implode(" AND ", $this->where);
		}
		return "";
	}

	private function _setSearchCondition(){
		$cnds = InquiryOnMypageUtil::getParameter("Search");
		if(is_array($cnds) && count($cnds)){
			foreach($cnds as $key => $cnd){
				if(is_string($cnd)){
					$cnd = trim($cnd);
					if(!strlen($cnd)) continue;
				}else if(is_array($cnd)){
					if(!count($cnd)) continue;
				}
				switch($key){
					case "user_name":
						$this->where[$key] = "user_id IN (SELECT id FROM soyshop_user WHERE name LIKE :" . $key . ")";
						$this->binds[":" . $key] = "%" . $cnd . "%";
						break;
					case "mail_address":
						$this->where[$key] = "user_id IN (SELECT id FROM soyshop_user WHERE mail_address LIKE :" . $key . ")";
						$this->binds[":" . $key] = "%" . $cnd . "%";
						break;
					case "content":
						$this->where[$key] = "(content LIKE :content OR requirement LIKE :req)";
						$this->binds[":content"] = "%" . $cnd . "%";
						$this->binds[":req"] = $this->binds[":content"];
						break;
					case "is_confirm":
						if(!count($cnd)) break;
						$q = array();
						if(is_numeric(array_search(SOYShop_Inquiry::NO_CONFIRM, $cnd))){
							$q[] = "is_confirm = " . SOYShop_Inquiry::NO_CONFIRM;
						}
						if(is_numeric(array_search(SOYShop_Inquiry::IS_CONFIRM, $cnd))){
							$q[] = "is_confirm = " . SOYShop_Inquiry::IS_CONFIRM;
						}
						if(count($q)) $this->where[$key] = "(" . implode(" OR ", $q) . ")";
						break;
					case "create_date":
						$q = array();
						if(isset($cnd["start"]) && strlen($cnd["start"])){
							$q[] = "create_date >= " . soyshop_convert_timestamp($cnd["start"]);
						}else if(isset($cnd["end"]) && strlen($cnd["end"])){
							$q[] = "create_date <= " . soyshop_convert_timestamp($cnd["end"], "end");
						}
						if(count($q)) $this->where[$key] = "(" . implode(" AND ", $q) . ")";
						break;
					case "tracking_number":
					default:
						$this->where[$key] = $key . " LIKE :" . $key;
						$this->binds[":" . $key] = "%" . $cnd . "%";
				}
			}
		}
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
	function setOffset($offset){
		$this->offset = $offset;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_InquiryDAO");
		return $dao;
	}
}
