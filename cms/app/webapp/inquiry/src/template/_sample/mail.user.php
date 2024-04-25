<?php
/**
 * 使用できる変数
 * $columns 各フォームのオブジェクト(入力内容も含む)
 * $this->form フォームの設定
 * $inquiryMailBody　出力されたお問い合わせ内容
 *
 * フォームに商品名 [SOY Shop連携]のカラムを追加している場合は下記の定数が利用可能です。
 * SOYSHOP_SITE_ID	連携しているショップID
 * SOYSHOP_ITEM_ID	連携しているショップでフォームと連携中の商品のID
 * 
 * 拡張方法
 * echo "追加したい文字列";でメールのコンテンツ末尾に追加されます。
 * $mailBody[1]の値を上書きすると出力内容が変わります。
 */

//お問い合わせ内容をコンパクトにして出力するサンプルコード
/**
$lines = explode("\n", $mailBody[1]);
if(count($lines)){
	$contents = array();
	foreach($lines as $line){
		//内容が空の場合はスルー
		$c = trim(substr($line, strpos($line, ":") + 1));
		if(!strlen($c)) continue;

		$line = trim(str_replace(array(" ", "　"), "", $line));
		$contents[] = $line;
	}

	$mailBody[1] = implode("\n", $contents);
}
**/
