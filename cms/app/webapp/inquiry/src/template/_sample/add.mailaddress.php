<?php
/**
 * 管理画面では設定できない管理者へのメールの送付先を追加
 * 使える変数
 * array $columns
 * array $userMailAddress
 * array $mailBody
 * 
 * フォームに商品名 [SOY Shop連携]のカラムを追加している場合は下記の定数が利用可能です。
 * SOYSHOP_SITE_ID	連携しているショップID
 * SOYSHOP_ITEM_ID	連携しているショップでフォームと連携中の商品のID
 * 
 * $sendTo[]にアドレスを追加すれば良い
 */

/**
 * SOY Shopの簡易ショッピングモールプラグインと連携するためのコード
if(SOYSHOP_ITEM_ID > 0){
	SOY2::import("util.SOYInquiryUtil");
	$old = SOYInquiryUtil::switchSOYShopConfig(SOYSHOP_SITE_ID);

	$mailaddress = SOY2Logic::createInstance("module.plugins.shopping_mall.logic.MallRelationLogic")->getAdminMailByItemId(SOYSHOP_ITEM_ID);

	SOYInquiryUtil::resetConfig($old);

	if(strlen($mailaddress)) $sendTo[] = $mailaddress;
}
 */