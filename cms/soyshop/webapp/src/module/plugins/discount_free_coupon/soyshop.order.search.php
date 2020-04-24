<?php

class DiscountFreeCouponSearch extends SOYShopOrderSearch{

	const PLUGIN_ID = "discount_free_coupon";

	function setParameter($params){
		$queries = array();
		$binds = array();

		//クーポンコード
		if(isset($params["code"]) && strlen($params["code"])){
			$v = str_replace(array(" ", "　"), ",", $params["code"]);
			$codes = explode(",", str_replace("、", ",", $v));

			if(count($codes)){
				$q = array();
				for($i = 0; $i < count($codes); $i++){
					$q[] = "cou.coupon_code LIKE :code" . $i;
					$binds[":code" . $i] = "%" . $codes[$i] . "%";
					$b[":code" . $i] = "%" . $codes[$i] . "%";
				}

				$queries[] = " id IN (SELECT order_id FROM soyshop_coupon_history his ".
								"INNER JOIN soyshop_coupon cou ".
								"ON his.coupon_id = cou.id ".
								"WHERE " . implode(" OR ", $q) . ")";
			}
		}

		//クーポンを使用した注文すべて
		if(isset($params["all"]) && (int)$params["all"] === 1){
			$queries[] = "id IN (SELECT order_id FROM soyshop_coupon_history WHERE coupon_id IS NOT NULL)";
		}

		if(count($queries)) return array("queries" => $queries, "binds" => $binds);
	}

	function searchItems($params){
		$form = array();

		$code = (isset($params["code"])) ? htmlspecialchars($params["code"], ENT_QUOTES, "UTF-8") : "";
		$form[] = " <input type=\"text\" name=\"search[customs][" . self::PLUGIN_ID . "][code]\" value=\"" . $code . "\" style=\"width:50%;\" placeholder=\"クーポンコードで検索\">  ";

		if(isset($params["all"]) && (int)$params["all"] === 1){
			$form[] = "<label><input type=\"checkbox\" name=\"search[customs][" . self::PLUGIN_ID . "][all]\" value=\"1\" checked=\"checked\">クーポンを使用した注文</label>";
		}else{
			$form[] = "<label><input type=\"checkbox\" name=\"search[customs][" . self::PLUGIN_ID . "][all]\" value=\"1\">クーポンを使用した注文</label>";
		}

		return array("label" => "クーポン", "form" => implode("\n", $form));
	}
}
SOYShopPlugin::extension("soyshop.order.search", "discount_free_coupon", "DiscountFreeCouponSearch");
