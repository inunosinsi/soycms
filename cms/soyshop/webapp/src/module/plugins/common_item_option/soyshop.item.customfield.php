<?php
class CommonItemOptionCustomField extends SOYShopItemCustomFieldBase{

	private $prefix;	//多言語化のプレフィックスを保持

	/**
	 * 管理画面側で商品情報を更新する際に読み込まれる
	 * 設定内容をデータベースに放り込む
	 * @param object SOYShop_Item
	 */
	function doPost(SOYShop_Item $item){

		if(isset($_POST["item_option"])){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

			$options = $_POST["item_option"];
			foreach($options as $key => $value){
				try{
					$attr = $dao->get($item->getId(), "item_option_" . $key);
				}catch(Exception $e){
					$attr = new SOYShop_ItemAttribute();
					$attr->setItemId($item->getId());
					$attr->setFieldId("item_option_" . $key);
				}

				$attr->setValue($value);

				try{
					$dao->insert($attr);
				}catch(Exception $e){
					try{
						$dao->update($attr);
					}catch(Exception $e){
						//
					}
				}
			}
		}
	}

	/**
	 * 管理画面側の商品詳細画面でフォームを表示します。
	 * @param object SOYShop_Item
	 * @return string html
	 */
	function getForm(SOYShop_Item $item){
		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return "";

		$html = array();

		$html[] = "<div class=\"alert alert-info\">商品オプションの設定</div>";
		$html[] = "<div class=\"alert alert-warning\">商品のオプション項目のセレクトボックスまたはラジオボタンを作成します。<br />";
		$html[] = "表示したいオプション項目を改行で区切って入力してください。</div>";

		$types = ItemOptionUtil::getTypes();
		foreach($opts as $key => $conf){
			$html[] = self::buildTextArea($key, $conf, $item->getId(), $types);
		}

		$html[] = "<div class=\"alert alert-info\">商品オプションの設定ここまで</div>";

		return implode("\n", $html);
	}

	/**
	 * プラグイン詳細で設定したオプションのフォームを出力する
	 * @param string key, string value, integer itemId
	 * @retrun string html
	 */
	private function buildTextArea($key, $conf, $itemId, $types){

		$v = ItemOptionUtil::getFieldValue($key, $itemId, $this->prefix);

		//古いバージョンから使用していて、typeの値がない場合はselectにする
		$type = (isset($conf["type"])) ? $conf["type"] : "select";

		$html = array();

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label for=\"item_option_" . $key . "\">オプション名：" . $conf["name"] . "&nbsp;タイプ：" . $types[$type] . "</label><br>";
		if($type == "text"){
			$html[] = "<input type=\"hidden\" name=\"item_option[" . $key . "]\" value=\"0\"><input type=\"checkbox\" name=\"item_option[" . $key . "]\" id=\"item_option_" . $key ."\" value=\"1\"".( $v ? " checked" : "" )."><label for=\"item_option_" . $key ."\">使う</label>";
		}else{
			$html[] = "<textarea name=\"item_option[" . $key . "]\" class=\"form-control\">" . $v . "</textarea>";
		}
		$html[] = "</div>";

		return implode("\n", $html);
	}

	/**
	 * 公開側のblock:id="item"で囲まれた箇所にフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		self::prepare();

		$opts = ItemOptionUtil::getOptions();
		if(count($opts)){
			foreach($opts as $key => $conf){
				$html = ItemOptionUtil::buildOptions($key, $conf, $item->getId(), $this->prefix);

				$htmlObj->addModel($key . "_visible", array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"visible" => (strlen($html) > 0)
				));

				$htmlObj->addLabel($key, array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"html" => $html
				));
			}
		}
	}

	/**
	 * 管理画面でフォームを出力する
	 * @param object htmlObj, object SOYShop_Item
	 */
	function outputFormForAdmin($htmlObj, SOYShop_Item $item, $nameBase, $itemIndex){
		self::prepare();

		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return "";

		$html = array();
		foreach($opts as $key => $conf){
			$name = $nameBase . "[" . $key . "]";

			$cart = CartLogic::getCart();
			$value = trim($cart->getAttribute("item_option_{$key}_{$itemIndex}_{$item->getId()}"));

			//古いバージョンから使用していて、typeの値がない場合はselectにする
			$type = (isset($conf["type"])) ? $conf["type"] : "select";
			$v = ItemOptionUtil::getFieldValue($key, $item->getId(), $this->prefix);
			if(!strlen($v)) continue;

			$html[] = htmlspecialchars($conf["name"], ENT_QUOTES, "UTF-8") . ": ";

			//選択したタイプによって、HTMLの出力を変える
			switch($type){
				case "text":
					$html[] = "<input type=\"text\" name=\"" . htmlspecialchars($name, ENT_QUOTES, "UTF-8") . "\" value=\"" . htmlspecialchars($value, ENT_QUOTES, "UTF-8") . "\">";
					break;

				case "radio":
					$options = explode("\n", trim($obj->getValue()));
					$first = true;
					foreach($options as $option){
						$option = trim($option);
						if($first){
							$first = false;
							$checked = strlen($value) ? "" : " checked=\"checked\"" ;
						}else{
							$checked = "";
						}
						if($option == $value) $checked = " checked=\"checked\"";

						$html[] = "<label><input type=\"radio\" name=\"" . htmlspecialchars($name, ENT_QUOTES, "UTF-8") . "\" value=\"" . htmlspecialchars($option, ENT_QUOTES, "UTF-8") . "\"".$checked.">" . htmlspecialchars($option, ENT_QUOTES, "UTF-8") . "</label>&nbsp;";
					}
					break;

				case "select":
				default:
					$html[] = "<select name=\"" . htmlspecialchars($name, ENT_QUOTES, "UTF-8") . "\">";

					$options = explode("\n", trim($obj->getValue()));
					foreach($options as $option){
						$option = trim($option);
						$selected = ($option == $value) ? " selected=\"selected\"" : "";
						$html[] = "<option{$selected}>" . htmlspecialchars($option, ENT_QUOTES, "UTF-8") . "</option>";
					}

					$html[] = "</select>";
					break;
			}
			$html[] = "<br>";
		}

		echo implode("", $html);

	}

	/**
	 * 管理画面側で商品情報を削除した時にオプション設定も一緒に削除する
	 * @param integer id
	 */
	function onDelete($id){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($id);
	}

	private function prepare(){
		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");

		//多言語の方も念のため
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");

		//多言語化のプレフィックスでも調べてみる
		if(is_null($this->prefix) && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
			if(class_exists("UtilMultiLanguageUtil")){
				$config = UtilMultiLanguageUtil::getConfig();
				$this->prefix = (isset($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"])) ? trim($config[SOYSHOP_PUBLISH_LANGUAGE]["prefix"]) : SOYSHOP_PUBLISH_LANGUAGE;
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_item_option", "CommonItemOptionCustomField");
