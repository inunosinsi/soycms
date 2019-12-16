<?php

SOY2DAOFactory::importEntity("SOYShop_DataSets");
class PaymentFurikomiUtil{
		
	/**
	 * 文言取得
	 */
	public static function getConfigText(){
		return SOYShop_DataSets::get("payment_furikomi.text", array(
			"account" => "○○銀行　△△支店\n普通　1234567\n口座名義　◇◇◇◇株式会社",
			"text" => "振込先情報\n--------------------------\n#ACCOUNT#\n--------------------------",
			"mail" => "※振込先は以下です。お間違えの無い様よろしくお願いします。\n" .
					"=================================\n" .
					"#ACCOUNT#\n" .
					"=================================\n"
		));
	}
	
	public static function saveConfigText($values){
		SOYShop_DataSets::put("payment_furikomi.text", $values);
	}
}
?>