<?php
/**
 * <!-- shop:module="common.breadcrumb_navigation" -->
 *	 <p id="pankuzu">
 *	<a cms:id="top_link">トップ</a>
 *	<!-- block:id="breadcrumb" -->
 *	&nbsp;&gt;&nbsp;
 *	<a cms:id="breadcrumb_link">カテゴリー名</a>
 *	<!-- /block:id="breadcrumb" -->
 *	&nbsp;&gt;&nbsp;
 *	<a cms:id="current_name_link">子のカテゴリー名</a>
 *	&nbsp;&gt;&nbsp;
 *	<!-- cms:id="current_item_name" -->
 *	商品名
 *	<!-- /cms:id="current_item_name" -->
 * </p>
 * <!-- /shop:module="common.breadcrumb_navigation" -->
 */

/**
 * 隠しモード
 * <!-- cms:id="current_item_name" cms:list_url="商品一覧ページのURLを書き換え" cms:detail_url="親商品の商品詳細ページのURLの書き換え" -->商品名<!-- /cms:id="current_item_name" -->
 */
SOY2::import("util.SOYShopPluginUtil");
function soyshop_breadcrumb_navigation($html, $page){
	$obj = $page->create("soyshop_breadcrumb_navigation", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_breadcrumb_navigation", $html)
	));

	$name = "";

	if(SOYShopPluginUtil::checkIsActive("common_breadcrumb")){

		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

		$pageObject = $page->getPageObject();
		$className = (isset($pageObject)) ? get_class($pageObject) : "";
		if($className == "SOYShop_Page"){

			$type = $pageObject->getType();
			switch($type){

				case SOYShop_Page::TYPE_LIST:
					$current = $pageObject->getObject()->getCurrentCategory();
					$uri = null;
					$categories = array();
					$alias = "";
					if(isset($current)){
						$uri = $pageObject->getUri();
						$args = $page->getArguments();
						$name = null;
						//下記のif文で商品一覧ページのuriを_homeにした時にカテゴリ未選択でページを開いた場合にパンくずを表示しないことを実現する
						if($uri == SOYSHOP_TOP_PAGE_MARKER && count($args) == 0){
							//何もしない
						}else{
							//argsにaliasが含まれているか？確認する
							$alias = $current->getAlias();
							if(is_numeric(array_search($alias, $args))){
								$categories = $dao->getAncestry($current, false);
								$name = $current->getOpenCategoryName();
							}
						}
					}else{
						//カスタムフィールドの場合
						if($pageObject->getObject()->getType() == "field"){
							$itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
							$list = SOYShop_ItemAttributeConfig::load(true);
							$object = $pageObject->getObject();
							$name = (isset($list[$object->getFieldId()])) ? $list[$object->getFieldId()]->getLabel() : "";
						//その他
						}else{
							//カスタムサーチフィールド
							if(!is_null($pageObject->getObject()->getModuleId()) && $pageObject->getObject()->getModuleId() === "custom_search_field"){
								$args = $page->getArguments();
								if(isset($args[1])) $name = htmlspecialchars($args[1], ENT_QUOTES, "UTF-8");
							}
						}

					}
					break;

				case SOYShop_Page::TYPE_DETAIL:
					$item = $page->getItem();

					//商品グループの子商品の時
					if(is_numeric($item->getType())){
						$parent = soyshop_get_item_object($item->getType());
						$categoryId = $parent->getCategory();

						SOY2::import("module.plugins.common_breadcrumb.util.BreadcrumbUtil");
						$config = BreadcrumbUtil::getConfig();

						//パンくずに子商品まで表示させる
						if(isset($config["displayChild"]) && $config["displayChild"] == 1){
							$parentUrl = soyshop_get_site_url() . soyshop_get_page_object($parent->getDetailPageId())->getUri() . "/" . $parent->getAlias();
							$itemName = "<a href=\"" . $parentUrl."\">" . $parent->getOpenItemName() . "</a>"."&nbsp;&gt;&nbsp;" .$item->getOpenItemName();

						//パンくずに表示する商品を親商品までにする
						}else{
							$itemName = $parent->getOpenItemName();
						}


					//子商品以外の時
					}else{
						$categoryId = $item->getCategory();
						$itemName = $item->getOpenItemName();
					}

					//表示中の商品名
					$obj->addLabel("current_item_name", array(
						"html" => $itemName,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));

					$current = soyshop_get_category_object($categoryId);
					if(is_null($current->getId())) return;

					SOY2::imports("module.plugins.common_breadcrumb.domain.*");
					$uri = SOY2DAOFactory::create("SOYShop_BreadcrumbDAO")->getPageUriByItemId($item->getId());

					$categories = $dao->getAncestry($current, false);

					$name = $current->getOpenCategoryName();
					$alias = $current->getAlias();

					break;
				case SOYShop_Page::TYPE_SEARCH:
					$categories = array();

					$uri = "";
					$name = "";
					if(isset($_GET["q"])){
						$name = trim($_GET["q"]);
					//カスタムサーチフィールド
					}else if(isset($_GET["c_search"]["item_name"])){
						$name = trim($_GET["c_search"]["item_name"]);
					}
					$alias = "";
					break;
				case SOYShop_Page::TYPE_FREE:
				case SOYShop_Page::TYPE_COMPLEX:
				default:
					$categories = array();
					$uri = "";
					$alias = "";
					$name = $pageObject->getName();

					//商品詳細表示プラグインでcms:id="current_item_name"を使用出来るようにする
					if(SOYShopPluginUtil::checkIsActive("parts_item_detail")){
						$args = $page->getArguments();
						if(isset($args[0])){
							SOY2::import("module.plugins.parts_item_detail.util.PartsItemDetailUtil");
							$item = PartsItemDetailUtil::getItemByAlias(trim($args[0]));
							$itemName = $item->getOpenItemName();

							//商品グループの子商品の時
							if(is_numeric($item->getType())){
								$parent = soyshop_get_item_object($item->getType());
								$category = soyshop_get_category_object($parent->getCategory());

								SOY2::import("module.plugins.common_breadcrumb.util.BreadcrumbUtil");
								$config = BreadcrumbUtil::getConfig();

								//パンくずに子商品まで表示させる
								if(isset($config["displayChild"]) && $config["displayChild"] == 1){
									$parentUrl = null;

									//隠しモード cms:detail_urlがある場合はこちらの値を利用する
									if(strpos($html, "cms:detail_url") !== false){
										preg_match('/cms:id="current_item_name".*cms:detail_url="(.*?)"/', $html, $tmp);
										if(isset($tmp[1])){
											$parentUrl = soyshop_get_site_url() . htmlspecialchars(trim(trim($tmp[1]), "/"), ENT_QUOTES, "UTF-8") . "/" . $parent->getAlias();
										}
									}

									//URIの書き換え
									if(is_null($parentUrl)){
										$pageId = PartsItemDetailUtil::getAttr($item->getId(), PartsItemDetailUtil::PARENT_FIELD_ID)->getValue();
										$parentPageId = (is_numeric($pageId) && $pageId > 0) ? $pageId : $parent->getDetailPageId();

										$parentUrl = soyshop_get_site_url() . soyshop_get_page_object($parentPageId)->getUri() . "/" . $parent->getAlias();
									}

									$itemName = "<a href=\"" . $parentUrl."\">" . $parent->getOpenItemName() . "</a>"."&nbsp;&gt;&nbsp;" .$item->getOpenItemName();


								//パンくずに表示する商品を親商品までにする
								}else{
									$itemName = $parent->getOpenItemName();
								}
							}else{
								$category = soyshop_get_category_object($item->getCategory());
							}

							$categories = $dao->getAncestry($category, false);
							$name = $category->getOpenCategoryName();
							$alias = $category->getAlias();

							$uri = null;

							//隠しモード cms:list_urlがある場合はこちらの値を利用する
							if(strpos($html, "cms:list_url") !== false){
								preg_match('/cms:id="current_item_name".*cms:list_url="(.*?)"/', $html, $tmp);
								if(isset($tmp[1])){
									$uri = htmlspecialchars(trim(trim($tmp[1]), "/"), ENT_QUOTES, "UTF-8");
								}
							}

							if(is_null($uri)){
								//URIの書き換え
								$pageId = PartsItemDetailUtil::getAttr($item->getId(), PartsItemDetailUtil::FIELD_ID)->getValue();
								if(is_numeric($pageId) && $pageId > 0){
									$uri = soyshop_get_page_object($pageId)->getUri();
								}else{
									$uri = SOY2DAOFactory::create("SOYShop_BreadcrumbDAO")->getPageUriByItemId($item->getId());
								}
							}

						}else{
							$itemName = "";
						}

						//表示中の商品名
						$obj->addLabel("current_item_name", array(
							"html" => $itemName,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}

					break;
			}

		//カートページとマイページ
		}else{
			$className = get_class($page);
			if($className == "SOYShop_CartPage"){
				$name = SOYShop_DataSets::get("config.cart.cart_title", "ショッピングカート");
			//マイページ
			}else{
				//マイページのタイトルフォーマットで置換文字列を使用
				$name = MyPageLogic::getMyPage()->getTitleFormat($page->getArgs());
			}

			$categories = array();
			$uri = "";
			$alias = "";
		}

		$obj->createAdd("breadcrumb", "BreadcrumbNavigation", array(
			"list" => $categories,
			"uri" => $uri,
			"soy2prefix" => "block"
		));

		//隠しモード
		$obj->addModel("is_current_name", array(
			"visible" => (!is_null($name)),
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
		));

		//表示中のカテゴリ名
		$obj->addLabel("current_name", array(
			"text" => $name,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
		));

		//表示中のカテゴリ名
		$obj->addLink("current_name_link", array(
			"text" => $name,
			"link" => soyshop_get_site_url() . $uri . "/" . $alias,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
		));

		//リンクのみ出力したい場合
		$obj->addLink("current_name_link_only", array(
			"link" => soyshop_get_site_url() . $uri . "/" . $alias,
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
		));

		$obj->addLink("top_link", array(
			"link" => soyshop_get_site_url(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//検索ワード
		$obj->addLabel("search_word", array(
			"text" => $name,
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}

	$obj->display();
}

/**
 * パンくず
 */
if(!class_exists("BreadcrumbNavigation")){
class BreadcrumbNavigation extends HTMLList{

	private $uri;

	protected function populateItem($entity, $key){
		if(false == ($entity instanceof SOYShop_Category)){
			$entity = new SOYShop_Category();
		}

		$this->addLink("breadcrumb_link", array(
			"text" => $entity->getOpenCategoryName(),
			"link" => soyshop_get_site_url() . $this->uri . "/" . $entity->getAlias(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));

		//リンクのみ出力したい場合
		$this->addLink("breadcrumb_link_only", array(
			"link" => soyshop_get_site_url() . $this->uri . "/" . $entity->getAlias(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));


		$this->addLabel("breadcrumb_name", array(
			"text" => $entity->getOpenCategoryName(),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}

	function setUri($uri){
		$this->uri = $uri;
	}
}
}
