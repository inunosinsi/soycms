<?php

class SlipNumberSearch extends SOYShopOrderSearch{

	function setParameter($params){
		$queries = array();
		$binds = array();

		//伝票番号が登録されているものを検索
		$numbers = (isset($params["numbers"]) && strlen($params["numbers"])) ? self::_getParameters($params["numbers"]) : array();
		if(count($numbers)){
			$q = array();
			$i = 0;
			foreach($numbers as $n){
				$n = trim($n);
				if(!strlen($n)) continue;
				$q[] = "slip_number LIKE :slip" . $i;
				$binds[":slip" . $i++] = "%" . $n . "%";
			}

			if(count($q)){
				$queries[] = "id IN (SELECT order_id FROM soyshop_slip_number WHERE " . implode(" OR ", $q) . ")";
			}
		}

		//伝票番号が未登録の注文を検索する
		if(isset($params["none"]) && $params["none"] == 1){
			$queries[] = "id NOT IN (SELECT order_id FROM soyshop_slip_number)";
		}

		return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems($params){
		$ipt = (isset($params["numbers"]) && strlen($params["numbers"])) ? self::_getParameter($params["numbers"]) : "";

		$html = array();
		$html[] = "<input type=\"text\" name=\"search[customs][slip_number][numbers]\" value=\"" . $ipt . "\" placeholder=\"スペース区切りで複数ワードで検索できます。\" style=\"width:95%;\">";

		if(isset($params["none"]) && $params["none"] == 1){
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][slip_number][none]\" value=\"1\" checked=\"checked\"> 伝票番号未登録の注文</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"search[customs][slip_number][none]\" value=\"1\"> 伝票番号未登録の注文</label>";
		}
		return array("label" => "発送伝票番号", "form" => implode("<br>", $html));
	}

	private function _getParameter($param){
		return (!is_array($param) && is_string($param) && strlen($param)) ? $param : null;
	}

	private function _getParameters($param){
		$str = str_replace("　", " ", self::_getParameter($param));
		return (strlen($str)) ? explode(" ", $str) : array();
	}
}
SOYShopPlugin::extension("soyshop.order.search", "slip_number", "SlipNumberSearch");
