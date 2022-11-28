<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class ItemInfoEditBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		//商品詳細ページ以外は動作しません
		$obj = $page->getPageObject();

		$button = null;
		$sslButton = null;

		//カートページとマイページでは読み込まない
		if(!is_object($obj) || get_class($obj) != "SOYShop_Page" || $obj->getType() != SOYShop_Page::TYPE_DETAIL){
			//何もしない
		}else{
			$session = SOY2ActionSession::getUserSession();
			if(!is_null($session->getAttribute("loginid"))){

				$adminDir = dirname(str_replace(dirname(SOYSHOP_SITE_DIRECTORY), "", SOYSHOP_WEBAPP));

				//チェック
				if(strpos($adminDir, $_SERVER["DOCUMENT_ROOT"]) !== false){
					$adminDir = "/" . str_replace($_SERVER["DOCUMENT_ROOT"], "", $adminDir);
				}

				$link = "http://" . $_SERVER["HTTP_HOST"] . $adminDir . "/index.php/Item/Detail/" . $obj->getObject()->getCurrentItem()->getId();
				$button = "<a href=\"" . $link . "\" target=\"_blank\"><button>商品編集</button></a>";

				$link = str_replace("http://", "https://", $link);
				$sslButton = "<a href=\"" . $link . "\" target=\"_blank\"><button>商品編集</button></a>";
			}
		}

		$page->addLabel("item_info_edit_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $button
		));

		$page->addLabel("item_info_edit_ssl_button", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $sslButton
		));
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "item_info_edit", "ItemInfoEditBeforeOutput");
