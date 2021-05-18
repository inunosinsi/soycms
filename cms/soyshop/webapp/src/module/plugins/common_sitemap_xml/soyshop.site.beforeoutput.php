<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CommonSitemapXmlBeforeOutput extends SOYShopSiteBeforeOutputAction{

	private $csfConfigs;
	private $languages = array();

	function beforeOutput($page){
		$pageObj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($pageObj) || get_class($pageObj) != "SOYShop_Page") return;

		//sitemap.xmlでない場合は読み込まない
		if(!preg_match('/sitemap.xml/', $pageObj->getUri())) return;

		//フリーページ以外では読み込まない
		$pageType = $pageObj->getType();
		if($pageType != SOYShop_Page::TYPE_FREE) return;

		$pages = self::_getPages();
		if(count($pages) == 0) return;

		SOY2::import("module.plugins.common_sitemap_xml.util.SitemapXMLUtil");

		//多言語プラグイン
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
		if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
			$this->languages = array_keys(UtilMultiLanguageUtil::allowLanguages());
		}

		//子商品の表示モードか？
		$isDisplayChildItem = SOYShop_ShopConfig::load()->getDisplayChildItem();

		$url = soyshop_get_site_url(true);

		header("Content-Type: text/xml");

		$html = array();
		$html[] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		if(count($this->languages)){
			$html[] = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xhtml=\"http://www.w3.org/1999/xhtml\">";
		}else{
			$html[] = "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:image=\"http://www.sitemaps.org/schemas/sitemap-image/1.1\" xmlns:video=\"http://www.sitemaps.org/schemas/sitemap-video/1.1\">";
		}


		foreach($pages as $obj){
			$uri = $obj->getUri();

			//多言語化プラグインで無視するurl
			if(count($this->languages)){
				$isStop = false;
				foreach($this->languages as $lang){
					if(strpos($uri, $lang . "/") === 0 || $lang == $uri) $isStop = true;
				}
				if($isStop) continue;
			}

			if($uri==SOYSHOP_TOP_PAGE_MARKER){
				$uri = "";

			//404 or メンテナンスの場合はスルー
			}else if($uri == SOYSHOP_404_PAGE_MARKER || $uri == SOYSHOP_MAINTENANCE_PAGE_MARKER){
				continue;
			}

			switch($obj->getType()){
				case SOYShop_Page::TYPE_LIST:
					$value = array();
					$pageObject = $obj->getPageObject();
					switch($pageObject->getType()){
						case SOYShop_ListPage::TYPE_CATEGORY:
							//ディフォルトカテゴリがある場合
							if(!is_null($pageObject->getDefaultCategory())){
								$html[] = self::_buildUrlTag($url, $uri . "/", "", 0.8, $obj->getUpdateDate());
							}

							$categoryIds = $pageObject->getCategories();
							foreach($categoryIds as $categoryId){
								$category = soyshop_get_category_object($categoryId);
								$html[] = self::_buildUrlTag($url, $uri, $category->getAlias(), 0.5, $obj->getUpdateDate());
							}
							break;
						case SOYShop_ListPage::TYPE_FIELD:
							/**
							 * @ToDo 引数の設定の方は未着手
							 */
							$html[] = self::_buildUrlTag($url, $uri . "/", "", 0.5, $obj->getUpdateDate());
							break;
						case SOYShop_ListPage::TYPE_CUSTOM:
							$moduleId = $pageObject->getModuleId();
							if(isset($moduleId)){
								//カスタムサーチフィールド
								if(strpos($moduleId, "custom_search_field") === 0){
									if(is_null($this->csfConfigs)){
											SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
											$this->csfConfigs = CustomSearchFieldUtil::getConfig();
									}

									if(!count($this->csfConfigs)) break;
									foreach($this->csfConfigs as $fieldId => $config){
										if(!isset($config["sitemap"]) || !is_numeric($config["sitemap"]) || (int)$config["sitemap"] !== (int)$obj->getId()) continue;
										if(!isset($config["option"][UtilMultiLanguageUtil::LANGUAGE_JP]) || !strlen($config["option"][UtilMultiLanguageUtil::LANGUAGE_JP])) continue;

										foreach(explode("\n", $config["option"][UtilMultiLanguageUtil::LANGUAGE_JP]) as $index => $opt){
											$opt = trim($opt);
											if(strlen($opt)){
												$html[] = "	<url>";
												$html[] = "		<loc>" . $url . $uri . "/" . $fieldId . "/" . $opt . "</loc>";
												//多言語化
												if(count($this->languages)){
													foreach($this->languages as $lang){
														if(!self::_isMultiLanguagePage($uri, $lang)) continue;
														if($lang == UtilMultiLanguageUtil::LANGUAGE_JP || !isset($config["option"][$lang]) || !strlen($config["option"][$lang])) continue;
														$multiOpts = explode("\n", $config["option"][$lang]);
														if(!isset($multiOpts[$index])) continue;
														$multiOpt = trim($multiOpts[$index]);
														if(!strlen($multiOpt)) continue;
														$html[] = self::_buildMultiLangagePageUrl($url, $uri . "/" . $fieldId . "/" . $multiOpt, $lang);
													}
												}
		 										$html[] = "		<priority>0.5</priority>";
		 										$html[] = "		<lastmod>" . self::_getDate($obj->getUpdateDate()) . "</lastmod>";
		 										$html[] = "	</url>";
											}
										}
									}
								}
							}
							break;
					}
					break;
				case SOYShop_Page::TYPE_DETAIL:
					$value = array();
					$items = self::_getItems($obj->getId());
					if(count($items)){
						foreach($items as $item){
							if(!$item->isPublished()) continue;	//非公開の商品は除く
							if(!$isDisplayChildItem && is_numeric($item->getType())) continue;	//子商品を表示しないモードの場合は除く

							$html[] = self::_buildUrlTag($url, $uri, $item->getAlias(), 0.8, $item->getUpdateDate());
						}
					}
					break;

				case SOYShop_Page::TYPE_COMPLEX:
				case SOYShop_Page::TYPE_FREE:
				case SOYShop_Page::TYPE_SEARCH:
				default:
					//トップページをリダイレクト専用にしている場合がある。その場合はテンプレートにbodyがない
					if(!strlen($uri) && $obj instanceof SOYShop_Page && !SitemapXMLUtil::checkIsBodyTag($obj)) break;

					//レビュープラグインが有効であり、レビュープラグイン用のページであれば除く
					if(SOYShopPluginUtil::checkIsActive("item_review")){
						SOY2::import("module.plugins.item_review.util.ItemReviewSitemapUtil");
						if(ItemReviewSitemapUtil::checkReviewPageId($obj->getId())) break;
					}
					if(strpos($uri, ".xml") !== false) break;

					$priority = (!strlen($uri)) ? "1.0" : "0.8";
					$html[] = self::_buildUrlTag($url, $uri, "", $priority, $obj->getUpdateDate());
					break;
			}
		}

		//管理画面で手動で追加したURL分
		$configs = SitemapXMLUtil::getConfig();
		if(count($configs)){
			foreach($configs as $config){
				if(isset($config["url"]) && strlen($config["url"]) && strpos($config["url"], "http") === 0){
					$html[] = "	<url>";
					$html[] = "		<loc>" . htmlspecialchars($config["url"], ENT_QUOTES, "UTF-8") . "</loc>";

					//多言語
					if(count($this->languages)){
						foreach($this->languages as $lang){
							if(isset($config[$lang]) && strlen($config[$lang])){
								$langUrl = trim(htmlspecialchars($config[$lang], ENT_QUOTES, "UTF-8"));
								$html[] = '		<xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . $langUrl . '" />';
							}
						}
					}

					$html[] = "		<priority>0.5</priority>";
					$html[] = "		<lastmod>" . self::_getDate($config["lastmod"]) . "</lastmod>";
					$html[] = "	</url>";
				}
			}
		}

		// 拡張ポイントを追加
		SOYShopPlugin::load("soyshop.sitemap");
		$urlItems = SOYShopPlugin::invoke("soyshop.sitemap")->getItems();
		if(is_array($urlItems) && count($urlItems)){
			foreach($urlItems as $urlItem){
				if(!isset($urlItem["loc"])) continue;
				$uri = $urlItem["loc"];
				$priority = (isset($urlItem["priority"]) && is_numeric($urlItem["priority"])) ? $urlItem["priority"] : 0.1;
				$lastmod = (isset($urlItem["lastmod"]) && is_numeric($urlItem["lastmod"])) ? $urlItem["lastmod"] : time();
				$html[] = self::_buildUrlTag($url, $uri, "", $priority, $lastmod);
			}
		}

		$html[] = "</urlset>";

		$page->addLabel("sitemap.xml", array(
			//"html" => "",
			"html" => implode("\n", $html),
			"soy2prefix" => SOYSHOP_SITE_PREFIX
		));
	}

	private function _buildUrlTag($url, $uri, $alias, $priority = 0.8, $updateDate = 0){
		if(strpos($uri, ".html") == false && strlen($uri) > 1) $uri = $uri . "/";

		if(strlen($alias)) $alias = "/" . $alias;
		$uriConcatedAlias = ltrim($uri . $alias, "/");
		if(is_numeric(strpos($uriConcatedAlias, "//"))) $uriConcatedAlias = str_replace("//", "/", $uriConcatedAlias);

		//末尾のスラッシュを外す カノニカルURLの設定と合わせる
		$uriConcatedAlias = rtrim($uriConcatedAlias, "/");
		if(self::_isTrailingSlash()) {
			preg_match('/.+\.(html|htm|php?)/i', $uriConcatedAlias, $tmp);
			if(!count($tmp)) $uriConcatedAlias .= "/";
		}

		$html = array();
		$html[] = "	<url>";
		$html[] = "		<loc>" . $url . $uriConcatedAlias . "</loc>";
		//多言語化
		if(count($this->languages)){
			foreach($this->languages as $lang){
				if(self::_isMultiLanguagePage($uri, $lang)){
					$html[] = self::_buildMultiLangagePageUrl($url, $uriConcatedAlias, $lang);
				}
			}
		}
		$html[] = "		<priority>" . $priority . "</priority>";
		$html[] = "		<lastmod>" . self::_getDate($updateDate) . "</lastmod>";
		$html[] = "	</url>";
		return implode("\n", $html);
	}

	private function _isTrailingSlash(){
		static $is;
		if(is_bool($is)) return $is;
		$is = (SOYShop_ShopConfig::load()->getIsTrailingSlash() == 1);
		return $is;
	}

	private function _isMultiLanguagePage($uri, $lang){
		if(!count($this->languages) || !array_search($lang, $this->languages)) return false;
		$filename = $lang . "_" . str_replace(array("/", "."), "_", $uri) . "_page.php";
		$filename = str_replace("__", "_", $filename);
		return file_exists(SOYSHOP_SITE_DIRECTORY . ".page/" . $filename);
	}

	private function _buildMultiLangagePageUrl($url, $uri, $lang){
		return '		<xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . $url . $lang . "/" . $uri . '" />';
	}

	private function _getDate($time){
		return date("Y", $time) . "-" . date("m", $time) . "-" . date("d", $time) . "T" . date("H", $time) . ":" . date("i", $time) . ":" . date("s", $time) . "+09:00";
	}

	private function _getPages(){
		try{
			return SOY2DAOFactory::create("site.SOYShop_PageDAO")->get();
		}catch(Exception $e){
			return array();
		}
	}

	private function _getItems($pageId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		try{
			return $dao->getByDetailPageIdIsOpen($pageId);
		}catch(Exception $e){
			return array();
		}
	}
}

SOYShopPlugin::extension("soyshop.site.beforeoutput", "common_sitemap_xml", "CommonSitemapXmlBeforeOutput");
