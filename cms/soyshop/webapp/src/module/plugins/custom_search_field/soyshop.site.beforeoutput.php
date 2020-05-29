<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CustomSearchFieldBeforeOutput extends SOYShopSiteBeforeOutputAction{

    function beforeOutput($page){

        //カートとマイページで動作しない様にする
        if(is_null($page->getPageObject())) return;
        if($page->getPageObject()->getType() == SOYShop_Page::TYPE_COMPLEX || $page->getPageObject()->getType() == SOYShop_Page::TYPE_FREE) return;

        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");

        //一覧で動作する
		$pageUrl = "";
        if($page->getPageObject()->getType() == SOYShop_Page::TYPE_LIST || $page->getPageObject()->getType() == SOYShop_Page::TYPE_SEARCH){

            //_homeでもソートボタン設置プラグインを使用できるようにする
            if($page->getPageObject()->getUri() == SOYShop_Page::URI_HOME){
                $pageUrl = soyshop_get_page_url(null);
            }else{
                $pageUrl = soyshop_get_page_url($page->getPageObject()->getUri());
            }

            //検索結果ページの内容をそのまま引き継ぐ
            $query = "";
            if(strlen($_SERVER["QUERY_STRING"]) && strpos($_SERVER["QUERY_STRING"], "&")){
                //値の整理をしながら
                $queries = explode("&", $_SERVER["QUERY_STRING"]);
                if(count($queries)){
                    foreach($queries as $q){
                        if(strpos($q, "=") === false) continue;

                        //custom_search_sortとrは除く
                        if(strpos($q, "custom_search_sort=") === 0 || strpos($q, "r=") === 0) continue;

                        $query .= "&" . $q;
                    }
                }
            }

            $args = $page->getArguments();
            for($i = 0; $i < count($args); $i++){
                if(isset($args[$i]) && strlen($args[$i])){
                    $pageUrl .= "/" . htmlspecialChars($args[$i], ENT_QUOTES, "UTF-8");
                }
            }

            foreach(CustomSearchFieldUtil::getConfig() as $fieldId => $values){
                $page->addLink("custom_search_sort_" . $fieldId . "_desc", array(
                    "soy2prefix" => "css",
                    "link" => $pageUrl . "?custom_search_sort=" . $fieldId . "&r=1" . $query
                ));

                $page->addLink("custom_search_sort_" . $fieldId . "_asc", array(
                    "soy2prefix" => "css",
                    "link" => $pageUrl . "?custom_search_sort=" . $fieldId . "&r=0" . $query
                ));
            }
        }

        //カテゴリカスタムサーチフィールド
        switch($page->getPageObject()->getType()){
			case SOYShop_Page::TYPE_LIST:
				$currentCategory = $page->getPageObject()->getObject()->getCurrentCategory();
				if(is_null($currentCategory)) $currentCategory = new SOYShop_Category();
				$categoryId = $currentCategory->getId();
				break;
			case SOYShop_Page::TYPE_DETAIL:
		  		$item = $page->getPageObject()->getObject()->getCurrentItem();
				if(!is_null($item)){
					$categoryId = $item->getCategory();
				}else{
					$categoryId = null;
            	}
            	break;
			default:
		  		$categoryId = null;
        }
        //if(is_null($categoryId)) return;

		if(isset($categoryId)){
			$values = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic", array("mode" => "category"))->getByCategoryId($categoryId);
	        foreach(CustomSearchFieldUtil::getCategoryConfig() as $key => $field){

	            //多言語化対応はデータベースから値を取得した時点で行っている
	            $csfValue = (isset($values[$key])) ? $values[$key] : null;
				if(isset($csfValue) && $field["type"] == CustomSearchFieldUtil::TYPE_TEXTAREA){
					$csfValue = soyshop_customfield_nl2br($csfValue);
				}

				$csfValueLength = strlen(trim(strip_tags($csfValue)));

	            $page->addModel($key . "_visible", array(
	                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
	                "visible" => ($csfValueLength > 0)
	            ));

				$page->addModel($key . "_is_not_empty", array(
	                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
	                "visible" => ($csfValueLength > 0)
	            ));

				$page->addModel($key . "_is_empty", array(
	                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
	                "visible" => ($csfValueLength === 0)
	            ));

	            $page->addLabel($key, array(
	                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
	                "html" => $csfValue
	            ));

	            switch($field["type"]){
	                case CustomSearchFieldUtil::TYPE_CHECKBOX:
	                    if(strlen($field["option"][SOYSHOP_PUBLISH_LANGUAGE])){
	                        $vals = explode(",", $csfValue);
	                        $opts = explode("\n", $field["option"][SOYSHOP_PUBLISH_LANGUAGE]);
	                        foreach($opts as $i => $opt){
	                            $opt = trim($opt);
	                            $page->addModel($key . "_"  . $i . "_visible", array(
	                                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
	                                "visible" => (in_array($opt, $vals))
	                            ));

	                            $page->addLabel($key . "_" . $i, array(
	                                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX,
	                                "text" => $opt
	                            ));
	                        }
	                    }
	                    break;
	            }
	        }
		}

		//カスタムサーチフィールドのカスタムフィールド
		SOY2::imports("module.plugins.custom_search_field.domain.*");
		$configs = SOYShop_CustomSearchAttributeConfig::load();
		if(count($configs)){
			preg_match('/' . str_replace("/", "\/", $page->getPageObject()->getUri()) . '\/(.*)\//', $pageUrl, $tmp);
			$csfTag = (isset($tmp[1])) ? trim(htmlspecialchars($tmp[1], ENT_QUOTES, "UTF-8")) : null;

			try{
				$array = SOY2DAOFactory::create("SOYShop_CustomSearchAttributeDAO")->getBySearchId($csfTag);
			}catch(Exception $e){
				$array = array();
			}

			foreach($configs as $config){
				$value = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue() : null;
				$value2 = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue2() : null;

				//空の時の挙動
				if(!is_null($config->getConfig()) && (is_null($value) || !strlen($value))){
					$fieldConf = $config->getConfig();
					if(isset($fieldConf["hideIfEmpty"]) && !$fieldConf["hideIfEmpty"] && isset($fieldConf["emptyValue"])){
						$value = $fieldConf["emptyValue"];
					}
				}

				$page->addModel($config->getFieldId() . "_visible", array(
					"visible" => (strlen(strip_tags($value)) > 0),
					"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
				));

				switch($config->getType()){

					case "image":
						/**
						 * 隠し機能:携帯自動振り分け、多言語化プラグイン用で画像の配置場所を別で用意する
						 * @ToDo 管理画面でもいじれる様にしたい
						 */
						$value = soyshop_convert_file_path($value, new SOYShop_Item());

						if(strlen($config->getOutput()) > 0){
							$page->addModel($config->getFieldId(), array(
								"attr:" . htmlspecialchars($config->getOutput()) => $value,
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
							));
						}else{
							//imgタグにalt属性を追加するか？
							if(isset($value2) && strlen($value2) > 0){
								$page->addImage($config->getFieldId(), array(
									"src" => $value,
									"attr:alt" => $value2,
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
									"visible" => ($value) ? true : false
								));
							}else{
								$page->addImage($config->getFieldId(), array(
									"src" => $value,
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
									"visible" => ($value)?true:false
								));
							}
						}
						$page->addLink($config->getFieldId() . "_link", array(
							"link" => $value,
							"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
						));

						$page->addLabel($config->getFieldId() . "_text", array(
							"text" => $value,
							"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
						));
						break;

					case "textarea":
						if(strlen($config->getOutput()) > 0){
							$page->addModel($config->getFieldId(), array(
								"attr:" . htmlspecialchars($config->getOutput()) => nl2br($value),
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
							));
						}else{
							$page->addLabel($config->getFieldId(), array(
								"html" => nl2br($value),
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
							));
						}
						break;

					case "link":
						if(strlen($config->getOutput()) > 0){
							$page->addModel($config->getFieldId(), array(
								"attr:" . htmlspecialchars($config->getOutput()) => $value,
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
							));
						}else{
							$page->addLink($config->getFieldId(), array(
								"link" => $value,
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
							));
						}

						$page->addLabel($config->getFieldId() . "_text", array(
							"text" => $value,
							"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
						));
						break;

					default:
						if(strlen($config->getOutput()) > 0){
							if($config->getOutput() == "href" && $config->getType() != "link"){
								$page->addLink($config->getFieldId(), array(
									"link" => $value,
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
								));
							}else{
								$page->addModel($config->getFieldId(), array(
									"attr:" . htmlspecialchars($config->getOutput()) => $value,
									"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
								));
							}
						}else{
							$page->addLabel($config->getFieldId(), array(
								"html" => $value,
								"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX
							));
						}
				}
			}
		}
    }
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "custom_search_field", "CustomSearchFieldBeforeOutput");
