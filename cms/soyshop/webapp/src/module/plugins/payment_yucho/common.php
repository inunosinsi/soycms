<?php

class PaymentYuchoCommon{

	/**
	 * 文言取得
	 */
	public static function getConfigText(){
		$array = SOYShop_DataSets::get("payment_yucho.text", array(
			"account" => "記号-番号：00000-1234567\n" .
					"名義：○○株式会社\n\n",
					"他の金融機関からの振込用の店名・預金種目・口座番号\n" .
					"銀行名：ゆうちょ銀行（金融機関コード：9900）\n" .
					"預金種目：普通（または貯蓄）\n" .
					"店名：一二三店（番号：123）\n" .
					"口座番号：1234567\n" .
					"名義：○○株式会社",
			"text" => "振込先情報\n--------------------------\n#ACCOUNT#\n--------------------------",
			"mail" => "※振込先は以下です。お間違えの無い様よろしくお願いします。\n" .
					"=================================\n" .
					"#ACCOUNT#\n" .
					"=================================\n"

		));
		return $array;
	}

}

?>
