<?php
class CustomSearchField extends SOYShopItemCustomFieldBase{

    const FIELD_ID = "custom_search_field";
    private $dbLogic;

    /**
     * 管理画面側で商品情報を更新する際に読み込まれる
     * 設定内容をデータベースに放り込む
     * @param object SOYShop_Item
     */
    function doPost(SOYShop_Item $item){

        if(isset($_POST["custom_search"])){
            self::prepare();
			$this->dbLogic->save($item->getId(), $_POST["custom_search"]);
        }
    }

    /**
     * 管理画面側の商品詳細画面でフォームを表示します。
     * @param object SOYShop_Item
     * @return string html
     */
    function getForm(SOYShop_Item $item){

        self::prepare();
        $values = $this->dbLogic->getByItemId($item->getId());

		$associationMode = false;	//カテゴリとの関連付けモード

        $html = array();

        SOY2::import("module.plugins." . self::FIELD_ID . ".component.FieldFormComponent");
		$list = CustomSearchFieldUtil::getConfig();
        foreach($list as $key => $field){
			$html[] = "<div class=\"form-group\" id=\"csf_field_" . $key . "_group\">";
			$html[] = "<label>" . htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8") . " (" . CustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")</label><br>";
            $value = (isset($values[$key])) ? $values[$key] : null;
            $html[] = FieldFormComponent::buildForm($key, $field, $value);
            $html[] = "</div>";

			//関連付けモードを起動するか調べる
			if(!$associationMode && isset($field["showInput"]) && is_numeric($field["showInput"])) $associationMode = true;
        }

		if(!$associationMode) return implode("\n", $html);

		//カテゴリマップ
		$map = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getMapping();
		if(!count($map)) return implode("\n", $html);

		//カテゴリとの関連付けのJavaScript
		$html[] = "<script>";

		//カテゴリマップの連想配列を組み立てる
		$html[] = "var csf_category_map = {";
		foreach($map as $categoryId => $children){
			$html[] = "	\"" . $categoryId . "\" : [" . implode(",", $children) . "],";
		}
		$html[] = "};";

		$html[] = "setInterval(function(){";
		$html[] = 'var categoryId = $("#item_category").val();';
		$html[] = 'var isCategory';

		foreach($list as $key => $field){
			if(!isset($field["showInput"]) || !is_numeric($field["showInput"])) continue;
			$html[] = 'isCategory = (categoryId == ' . $field["showInput"] . ');';
			$html[] = 'if(!isCategory){';	//親カテゴリの方にあるか調べる
			$html[] = '	if(csf_category_map[categoryId].length > 0){';
			$html[] = '		isCategory = (csf_category_map[categoryId].indexOf(' . $field["showInput"]. ') >= 0);';
			$html[] = '	}';
			$html[] = '}';

			$html[] = 'if(isCategory){';
			$html[] = '	$("#csf_field_' . $key . '_group").show();';
			$html[] = '}else{';
			$html[] = '	$("#csf_field_' . $key . '_group").hide();';
			$html[] = '}';
		}
		$html[] = "}, 1300);";

		$html[] = "</script>";

        return implode("\n", $html);
    }

    /**
     * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
     * @param object htmlObj, object SOYShop_Item
     */
    function onOutput($htmlObj, SOYShop_Item $item){
        self::prepare();
        $values = $this->dbLogic->getByItemId($item->getId());

        foreach(CustomSearchFieldUtil::getConfig() as $key => $field){

            //多言語化対応はデータベースから値を取得した時点で行っている
            $csfValue = (isset($values[$key])) ? $values[$key] : null;
			if(isset($csfValue) && $field["type"] == CustomSearchFieldUtil::TYPE_TEXTAREA){
				$csfValue = soyshop_customfield_nl2br($csfValue);
			}

			$csfValueLength = strlen(trim(strip_tags($csfValue)));

            $htmlObj->addModel($key . "_visible", array(
                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
                "visible" => ($csfValueLength > 0)
            ));

			$htmlObj->addModel($key . "_is_not_empty", array(
                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
                "visible" => ($csfValueLength > 0)
            ));

			$htmlObj->addModel($key . "_is_empty", array(
                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
                "visible" => ($csfValueLength === 0)
            ));

            $htmlObj->addLabel($key, array(
                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
                "html" => (isset($csfValue)) ? $csfValue : null
            ));

			$htmlObj->addLink($key . "_link", array(
				"soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
				"link" => (isset($csfValue) && strlen($csfValue)) ? $csfValue : null
			));

            switch($field["type"]){
                case CustomSearchFieldUtil::TYPE_CHECKBOX:
                    if(isset($field["option"][SOYSHOP_PUBLISH_LANGUAGE]) && strlen($field["option"][SOYSHOP_PUBLISH_LANGUAGE])){
                        $vals = explode(",", $csfValue);
                        $opts = explode("\n", $field["option"][SOYSHOP_PUBLISH_LANGUAGE]);
                        foreach($opts as $i => $opt){
                            $opt = trim($opt);
                            $htmlObj->addModel($key . "_"  . $i . "_visible", array(
                                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
                                "visible" => (in_array($opt, $vals))
                            ));

                            $htmlObj->addLabel($key . "_" . $i, array(
                                "soy2prefix" => CustomSearchFieldUtil::PLUGIN_PREFIX,
                                "text" => $opt
                            ));
                        }
                    }
                    break;
            }
        }
    }

    /**
     * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
     * @param integer id
     */
    function onDelete($itemId){
        self::prepare();
        $this->dbLogic->delete($itemId);
    }

    private function prepare(){
        if(!$this->dbLogic){
            $this->dbLogic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic");
            SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
        }

        //多言語の方も念のため
        if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
    }
}

SOYShopPlugin::extension("soyshop.item.customfield", "custom_search_field", "CustomSearchField");
