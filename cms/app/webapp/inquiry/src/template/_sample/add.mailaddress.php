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
if(!defined("SOYSHOP_ITEM_ID")){
	// 定数SOYSHOP_ITEM_IDの定義を行う
	SOY2Logic::createInstance("logic.SOYShopConnectLogic")->setSOYShopSiteIdConstant();
}
if(SOYSHOP_ITEM_ID > 0){
	// SOY Shopのデータベースを使用するための手続き
	SOY2::import("util.SOYInquiryUtil");
	$old = SOYInquiryUtil::switchSOYShopConfig(SOYSHOP_SITE_ID);

	// 商品IDに紐付いたメールアドレスを取得
	$mailaddress = SOY2Logic::createInstance("module.plugins.shopping_mall.logic.MallRelationLogic")->getAdminMailByItemId(SOYSHOP_ITEM_ID);

	// SOY Inquiryのデータベースの使用に戻す
	SOYInquiryUtil::resetConfig($old);

	if(strlen($mailaddress)) $sendTo[] = $mailaddress;
}
 */