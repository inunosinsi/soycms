<?php

class InitPageLogic extends SOY2LogicBase{

	private $detailPageId = null;

	function initPage($isOnlyAdmin=false) {
		//ページの初期化用のCSVをインポート(/soyshop/webapp/src/logic/init/page/ini.csvにある)
		if(file_exists(SOY2::RootDir() . "logic/init/page/ini.csv")){
			$ini = SOY2::RootDir() . "logic/init/page/ini.csv";
		}else if($isOnlyAdmin){		//管理画面モード
			$ini = SOY2::RootDir() . "logic/init/page/ini.only_admin.csv";
		}else{
			$ini = SOY2::RootDir() . "logic/init/page/ini.default.csv";
		}

		//ページ一覧の初期化はPageCreateLogicの方で行う
		SOY2Logic::createInstance("logic.site.page.PageCreateLogic")->initPage($ini);

		return true;
	}

	function initCategoryCustomField(){
		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		$configs = SOYShop_CategoryAttributeConfig::load();

		$config = new SOYShop_CategoryAttributeConfig();
		$config->setLabel("カテゴリーバナー");
		$config->setFieldId("category_banner");
		$config->setType("image");

		$configs[] = $config;

		SOYShop_CategoryAttributeConfig::save($configs);

	}

	function initCategory($isOnlyAdmin=false){
		if($isOnlyAdmin) return true;	//管理画面モードの場合はカテゴリを作成しない

		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$categoryLogic = SOY2Logic::createInstance("logic.shop.CategoryLogic");

		try{

			$this->initCategoryCustomField();

			echo "ncategory";

			if(defined("SOYSHOP_TEMPLATE_ID")&&SOYSHOP_TEMPLATE_ID=="bryon"){
				$obj = new SOYShop_Category();
				$obj->setName("カテゴリ１");
				$obj->setAlias("category-1.html");
				$id = $dao->insertImpl($obj);
				$categoryLogic->setAttribute($id,"category_banner","/".SOYSHOP_ID."/files/category-1/category_main.jpg");

				$obj = new SOYShop_Category();
				$obj->setName("カテゴリ２");
				$obj->setAlias("category-2.html");
				$id = $dao->insertImpl($obj);

				$obj = new SOYShop_Category();
				$obj->setName("カテゴリ３");
				$obj->setAlias("category-3.html");
				$dao->insert($obj);
			}else{
				$obj = new SOYShop_Category();
				$obj->setName("カテゴリ１");
				$obj->setAlias("category-1.html");
				$dao->insertImpl($obj);

				$obj = new SOYShop_Category();
				$obj->setName("カテゴリ２");
				$obj->setAlias("category-2.html");
				$id = $dao->insertImpl($obj);

				$obj = new SOYShop_Category();
				$obj->setName("カテゴリ３");
				$obj->setAlias("category-3.html");
				$dao->insert($obj);
			}


		}catch(Exception $e){
			return false;
		}

		return true;
	}

	function initCustomField(){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$configs = SOYShop_ItemAttributeConfig::load();

		$config = new SOYShop_ItemAttributeConfig();
		$config->setLabel("説明１");
		$config->setFieldId("item_copy1");
		$config->setType("textarea");
		$configs[] = $config;

		$config = new SOYShop_ItemAttributeConfig();
		$config->setLabel("見出し");
		$config->setFieldId("item_copy2");
		$config->setType("input");
		$configs[] = $config;

		$config = new SOYShop_ItemAttributeConfig();
		$config->setLabel("説明３");
		$config->setFieldId("item_copy3");
		$config->setType("textarea");
		$configs[] = $config;


		SOYShop_ItemAttributeConfig::save($configs);

	}

	function initItems($isOnlyAdmin=false){
		if($isOnlyAdmin) return true;		//管理画面モードの場合は商品を登録しない

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemLogic = SOY2Logic::createInstance("logic.shop.item.ItemLogic");

		try{
			$this->initCustomField();

			echo "\nitems: ";

			if(defined("SOYSHOP_TEMPLATE_ID")&&SOYSHOP_TEMPLATE_ID=="bryon"){

				$siteUrl = soyshop_get_site_url();

				echo "item 0,";
				$obj = new SOYShop_Item();
				$obj->setName("苔オブジェ");
				$obj->setCode("item-001");
				$obj->setCategory(1);
				$obj->setStock(150);
				$obj->setPrice(2750);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","ロゴをかたどったオブジェ、本物の苔を植えています。");

				/* 1 */
				echo "item 1,";
				$obj = new SOYShop_Item();
				$obj->setName("ナノブロック");
				$obj->setCode("item-002");
				$obj->setCategory(1);
				$obj->setStock(150);
				$obj->setPrice(2100);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","ナノブロックのセット。犬シリーズです。ぜひ組み立てて遊んでみてください。");

				echo "item 2,";
				$obj = new SOYShop_Item();
				$obj->setName("回るサントスシリーズ");
				$obj->setCode("item-003");
				$obj->setCategory(1);
				$obj->setStock(150);
				$obj->setPrice(3150);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","回るサントスシリーズ。電池により、少しずつ、そしてずっと回り続けます。");

				echo "item 3,";
				$obj = new SOYShop_Item();
				$obj->setName("ランチボール4点セット");
				$obj->setCode("item-004");
				$obj->setCategory(1);
				$obj->setStock(150);
				$obj->setPrice(3150);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","シンプルでおしゃれなランチボールセット。木製のスプーンとフォーク、ランチョンマットのセットです。");

				echo "item 0,";
				$obj = new SOYShop_Item();
				$obj->setName("アートフラワー置きものセット");
				$obj->setCode("goods-001");
				$obj->setCategory(2);
				$obj->setStock(150);
				$obj->setPrice(2100);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","アートフラワーをお部屋の飾りにひとつ、いかがですか？");

				echo "item 1,";
				$obj = new SOYShop_Item();
				$obj->setName("ナノブロック");
				$obj->setCode("goods-002");
				$obj->setCategory(2);
				$obj->setStock(150);
				$obj->setPrice(2100);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","ナノブロックのセット。犬シリーズです。ぜひ組み立てて遊んでみてください。");

				echo "item 2,";
				$obj = new SOYShop_Item();
				$obj->setName("回るサントスシリーズ");
				$obj->setCode("goods-003");
				$obj->setCategory(2);
				$obj->setStock(150);
				$obj->setPrice(3150);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","回るサントスシリーズ。電池により、少しずつ、そしてずっと回り続けます。");

				echo "item 3,";
				$obj = new SOYShop_Item();
				$obj->setName("ランチボール4点セット");
				$obj->setCode("goods-004");
				$obj->setCategory(2);
				$obj->setStock(150);
				$obj->setPrice(3150);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","シンプルでおしゃれなランチボールセット。木製のスプーンとフォーク、ランチョンマットのセットです。");

				echo "item 1,";
				$obj = new SOYShop_Item();
				$obj->setName("ナノブロック");
				$obj->setCode("object-001");
				$obj->setCategory(3);
				$obj->setStock(150);
				$obj->setPrice(2100);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","ナノブロックのセット。犬シリーズです。ぜひ組み立てて遊んでみてください。");

				echo "item 2,";
				$obj = new SOYShop_Item();
				$obj->setName("回るサントスシリーズ");
				$obj->setCode("object-002");
				$obj->setCategory(3);
				$obj->setStock(150);
				$obj->setPrice(3150);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","回るサントスシリーズ。電池により、少しずつ、そしてずっと回り続けます。");

				echo "item 3,";
				$obj = new SOYShop_Item();
				$obj->setName("ランチボール4点セット");
				$obj->setCode("object-003");
				$obj->setCategory(3);
				$obj->setStock(150);
				$obj->setPrice(3150);
				$obj->setDetailPageId(3);
				$obj->setAttribute("image_small",$siteUrl . "themes/sample/sample1_thumb.jpg");
				$obj->setAttribute("image_large",$siteUrl . "themes/sample/sample1.jpg");
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","シンプルでおしゃれなランチボールセット。木製のスプーンとフォーク、ランチョンマットのセットです。");

			}else{
				echo "item 0,";
				$obj = new SOYShop_Item();
				$obj->setName("トマト");
				$obj->setCode("item-001");
				$obj->setCategory(1);
				$id = $dao->insert($obj);
				$itemLogic->setAttribute($id,"item_copy1","リコピンたっぷりトマト");
				$itemLogic->setAttribute($id,"item_copy2","トマトです。");
				$itemLogic->setAttribute($id,"item_copy3","トマトに関する説明文です。");

				/* 1 */
				echo "item 1,";
				$obj = new SOYShop_Item();
				$obj->setName("ピーマン");
				$obj->setCode("item-002");
				$obj->setCategory(1);
				$itemLogic->create($obj);

				echo "item 2,";
				$obj = new SOYShop_Item();
				$obj->setName("ナス");
				$obj->setCode("item-003");
				$obj->setCategory(1);
				$itemLogic->create($obj);

				/* 2 */
				echo "item 3,";
				$obj = new SOYShop_Item();
				$obj->setName("トマト２");
				$obj->setCode("goods-001");
				$obj->setCategory(2);
				$obj->setStock(150);
				$obj->setPrice(150);
				$itemLogic->create($obj);

				echo "item 4,";
				$obj = new SOYShop_Item();
				$obj->setName("ピーマン２");
				$obj->setCode("goods-002");
				$obj->setCategory(2);
				$itemLogic->create($obj);

				echo "item 5,";
				$obj = new SOYShop_Item();
				$obj->setName("ナス２");
				$obj->setCode("goods-003");
				$obj->setCategory(2);
				$itemLogic->create($obj);

				/* 3 */
				echo "item 6,";
				$obj = new SOYShop_Item();
				$obj->setName("トマト３");
				$obj->setCode("sample-001");
				$obj->setCategory(3);
				$obj->setStock(180);
				$obj->setPrice(200);
				$itemLogic->create($obj);

				echo "item 7,";
				$obj = new SOYShop_Item();
				$obj->setName("ピーマン３");
				$obj->setCode("sample-002");
				$obj->setCategory(3);
				$itemLogic->create($obj);

				echo "item 8,";
				$obj = new SOYShop_Item();
				$obj->setName("ナス３");
				$obj->setCode("sample-003");
				$obj->setCategory(3);
				$itemLogic->create($obj);
			}

			return true;
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * メール本文の初期設定
	 */
	function initDefaultMail($isOnlyAdmin=false){
		if($isOnlyAdmin) return true;		//管理画面モードの場合はメールの設定を行わない

		try{
			$logic = SOY2Logic::createInstance("logic.mail.MailLogic");

			//管理側メール：注文受付
			$mail = array(
				"title" => "[SOY Shop] 注文がありました",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/admin/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/admin/footer.txt"),
			);

			$logic->setAdminMailConfig($mail);

			//管理側メール：支払い確認
			$mail = array(
				"title" => "[SOY Shop] 支払いが完了しました",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/adminConfirmPayment/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/adminConfirmPayment/footer.txt"),
			);

			$logic->setAdminMailConfig($mail,"payment");

			//"" => "注文受付メール"
			$mail = array(
				"title" => "[#SHOP_NAME#] ご注文をありがとうございます",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/order/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/order/footer.txt")
			);
			$logic->setUserMailConfig($mail,"order");

			//"confirm" => "注文確認メール雛型設定",
			$mail = array(
				"title" => "[#SHOP_NAME#] 注文を確認いたしました",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/confirmOrder/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/confirmOrder/footer.txt")
			);
			$logic->setUserMailConfig($mail,"confirm");

			//"payment" => "支払確認メール雛型設定",
			$mail = array(
				"title" => "[#SHOP_NAME#] ご入金を確認いたしました",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/confirmPayment/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/confirmPayment/footer.txt")
			);
			$logic->setUserMailConfig($mail,"payment");

			//"delivery" => "配送連絡メール雛型設定"
			$mail = array(
				"title" => "[#SHOP_NAME#] 商品を発送いたしました",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/send/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/send/footer.txt")
			);
			$logic->setUserMailConfig($mail,"delivery");

			//"mypage.remind" => "マイページ　パスワードリマインドメール雛型設定"
			$mail = array(
				"title" => "[#SHOP_NAME#] パスワード再設定",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/mypage/remind/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/mypage/remind/footer.txt")
			);
			$logic->setMyPageMailConfig($mail,"remind");

			$mail = array(
				"title" => "[#SHOP_NAME#] 仮登録メール",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/mypage/tmp_register/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/mypage/tmp_register/footer.txt")
			);
			$logic->setMyPageMailConfig($mail,"tmp_register");

			$mail = array(
				"title" => "[#SHOP_NAME#] 登録完了メール",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/mypage/register/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/mypage/register/footer.txt")
			);
			$logic->setMyPageMailConfig($mail,"register");

			//"user" => "顧客宛メール雛型設定"
			$mail = array(
				"title" => "[#SHOP_NAME#] ",
				"header" => file_get_contents(dirname(__FILE__) . "/mail/user/header.txt"),
				"footer" => file_get_contents(dirname(__FILE__) . "/mail/user/footer.txt")
			);
			$logic->setUserMailConfig($mail,"user");

		}catch(Exception $e){
			return false;
		}

		return true;

	}

	/**
	 * カートの初期設定
	 */
	function initCart($isOnlyAdmin=false){
		try{
			SOYShop_DataSets::put("config.cart.cart_title","ショッピングカート");

			SOYShop_DataSets::put("config.cart.use_ssl", 0);
			SOYShop_DataSets::put("config.cart.ssl_url",str_replace("http","https",SOYSHOP_SITE_URL));

			$cartId = (!$isOnlyAdmin) ? SOYSHOP_TEMPLATE_ID : "none";
			SOYShop_DataSets::put("config.cart.cart_id", $cartId);
			SOYShop_DataSets::put("config.cart.cart_url","cart");
			SOYShop_DataSets::put("config.cart.cart_charset","UTF-8");
		}catch(Exception $e){
			return false;
		}

		return true;
	}

	/**
	 * マイページの初期設定
	 */
	function initMypage($isOnlyAdmin=false){
		try{
			SOYShop_DataSets::put("config.mypage.title","マイページ");

			SOYShop_DataSets::put("config.mypage.use_ssl", 0);
			SOYShop_DataSets::put("config.mypage.ssl_url",str_replace("http","https",SOYSHOP_SITE_URL));

			$mypageId = (!$isOnlyAdmin) ? SOYSHOP_TEMPLATE_ID : "none";
			SOYShop_DataSets::put("config.mypage.id",SOYSHOP_TEMPLATE_ID);
			SOYShop_DataSets::put("config.mypage.url","user");
			SOYShop_DataSets::put("config.mypage.top","order");
			SOYShop_DataSets::put("config.mypage.charset","UTF-8");

			SOYShop_DataSets::put("config.mypage.tmp_user_register", 1);
		}catch(Exception $e){
			return false;
		}

		return true;
	}
}
