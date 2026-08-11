<?php

class FeeExceptionLogic extends SOY2LogicBase {

	/**
	 * 指定の商品コード一覧から有効な商品コード一覧を還す
	 * @param array
	 * @return array
	 */
	function checkIsExistItemCodes(array $codes){
		if(!count($codes)) return array();

		for($i = 0; $i < count($codes); $i++){
			$codes[$i] = htmlspecialchars(trim($codes[$i]), ENT_QUOTES, "UTF-8");
		}

		$dao = new SOY2DAO();

		try{
			$results = $dao->executeQuery("SELECT item_code FROM soyshop_item WHERE item_code IN ('" . implode("','", $codes) . "')");
		}catch(Exception $e){
			$results = array();
		}
		if(!count($results)) return array();

		$codes = array();
		foreach($results as $res){
			$codes[] = $res["item_code"];
			$codes = array_values($codes);
		}
		if(count($codes) > 1) array_unique($codes);

		return $codes;
	}
}
