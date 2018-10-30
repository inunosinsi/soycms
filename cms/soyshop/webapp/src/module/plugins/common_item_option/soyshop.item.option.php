<?php
class CommonItemOption extends SOYShopItemOptionBase{

	const ADMIN_OPTION_KEY = "admin";

	private function getCartAttributeId($optionId, $itemIndex, $itemId){
		return "item_option_{$optionId}_{$itemIndex}_{$itemId}";
	}

	/**
	 * カートから商品を削除した時にセッションに放り込んだ値を削除する
	 * @param integer index, object CartLogic
	 */
	function clear($index, CartLogic $cart){
		self::prepare();

		$opts = ItemOptionUtil::getOptions();
		if(count($opts)){
			$items = $cart->getItems();
			if(isset($items[$index])){
				$itemId = $items[$index]->getItemId();

				foreach($opts as $key => $conf){
					$cart->clearAttribute(self::getCartAttributeId($key, $index, $itemId));
				}
			}
		}
	}

	/**
	 * 配列が一致したindexを返す
	 * カートに入れた商品がすでにカートに入っている商品と一致しているか？を調べるメソッド
	 * @param array postedOption, object CartLogic
	 * @return integer index
	 */
	function compare($postedOption, CartLogic $cart){
		self::prepare();

		$checkOptionId = null;

		$opts = ItemOptionUtil::getOptions();
		if(count($opts)){

			$items = $cart->getItems();

			//比較用の配列を作成する
			$attributes = array();
			foreach($items as $index => $item){
				//管理画面側では商品一覧のセッションの中にオプションが格納されている
				if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
					$attrs = $item->getAttributes();
					$currentOptions = (isset($attrs)) ? soy2_unserialize($attrs) : array();
					$currentOptions["itemId"] = $item->getItemId();
				//公開側の場合はカートのセッション内にオプションが格納されている
				}else{
					foreach($opts as $key => $conf){
						$attrs[$index][$key] = $cart->getAttribute(self::getCartAttributeId($key, $index, $item->getItemId()));
					}

					$currentOptions = array_diff($attrs[$index], array(null));
				}

				if($postedOption == $currentOptions){
					$checkOptionId = $index;
					break;
				}
			}
		}

		return $checkOptionId;
	}

	/**
	 * src/base/cart/cart.phpでカートに商品を入れたときの対応
	 * オプション内容をセッションに放り込む
	 * @param integer index, object CartLogic
	 */
	function doPost($index, CartLogic $cart){

		if(isset($_POST["item_option"]) && is_array($_POST["item_option"]) && count($_POST["item_option"])){
			$opts = $_POST["item_option"];

			$items = $cart->getItems();
			if(isset($items[$index])){
				$itemId = $items[$index]->getItemId();

				foreach($opts as $key => $value){
					$cart->setAttribute(self::getCartAttributeId($key, $index, $itemId), $value);
				}
			}
		}
	}

	/**
	 * 商品情報の下に表示される情報
	 * @param htmlObj, integer index
	 * @return string html
	 */
	function onOutput($htmlObj, $index){
		self::prepare();

		$cart = CartLogic::getCart();

		$items = $cart->getItems();
		if(!isset($items[$index])){
			return "";
		}

		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return "";

		$itemId = $items[$index]->getItemId();

		$html = array();
		foreach($opts as $key => $conf){
			$opt = $cart->getAttribute(self::getCartAttributeId($key, $index, $itemId));

			if(strlen($opt) > 0){
				$html[] = self::getOptionName($conf) . ":" . trim(htmlspecialchars($opt, ENT_QUOTES, "UTF-8"));
			}
		}

		return implode("<br />", $html);
	}

	/**
	 * 注文確定時に商品とオプション内容を紐づける
	 * @param integer index
	 */
	function order($index){
		$cart = CartLogic::getCart();

		$items = $cart->getItems();
		if(!isset($items[$index])) return null;

		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return null;

		$itemId = $items[$index]->getItemId();

		$array = array();
		foreach($opts as $key => $conf){
			$array[$key] = $cart->getAttribute(self::getCartAttributeId($key, $index, $itemId));
		}

		//管理画面での注文の追加オプション
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
			$optionId = self::ADMIN_OPTION_KEY;
			$value = $cart->getAttribute(self::getCartAttributeId($optionId, $index, $itemId));
			if(isset($value) && strlen($value)) {
				$array[$optionId] = $value;
			//セッションに値が格納されていない場合はitemOrderのattributeを見る
			}else{
				$array = $items[$index]->getAttributeList();
			}
		}

		return (count($array) > 0) ? soy2_serialize($array) : null;
	}

	/**
	 * 注文確定後の注文詳細の商品情報の下に表示される
	 * @param object SOYShop_ItemOrder
	 * @return string html
	 */
	function display(SOYShop_ItemOrder $itemOrder){
		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return "";

		$attrs = $itemOrder->getAttributeList();
		if(!count($attrs)) return "";

		$html = array();
		foreach($attrs as $key => $value){
			if(isset($opts[$key]["name"]) && strlen($value) > 0){
				$html[] = $opts[$key]["name"] . " : " . trim(htmlspecialchars($value, ENT_QUOTES, "UTF-8"));
			}
		}

		return implode("<br />", $html);
	}

	/**
	 * マイページで商品オプションを変更できるようにする
	 * @param object SOYShop_ItemOrder
	 * @return string html
	 */
	function form(SOYShop_ItemOrder $itemOrder){
		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return "";

		$attrs = $itemOrder->getAttributeList();
		if(!count($attrs)) return "";

		$html = array();
		foreach($opts as $key => $conf){
			if(!isset($opts[$key])) continue;
			$selected = (isset($attrs[$key])) ? trim($attrs[$key]) : null;
			$html[] = $opts[$key]["name"] . " : " . ItemOptionUtil::buildOptionsWithSelected($key, $conf, $itemOrder, $selected, SOYSHOP_PUBLISH_LANGUAGE, false);
		}

		return implode("<br />", $html);
	}

	function change($itemOrders){
		if(is_null($itemOrders) || !count($itemOrders)) return;

		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return array();

		foreach($itemOrders as $itemOrder){
			$attrs = $itemOrder->getAttributeList();
			foreach($opts as $key => $conf){
				$attrs[$key] = (isset($_POST["item_option"][$itemOrder->getId()][$key])) ? trim($_POST["item_option"][$itemOrder->getId()][$key]) : null;
			}
			$itemOrder->setAttributes($attrs);
		}
	}

	//変更履歴の取得のみ
	function history($newItemOrder, $oldItemOrder){
		if(is_null($newItemOrder)) return array();

		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts)) return array();

		$changes = array();
		$newAttrs = $newItemOrder->getAttributeList();
		$oldAttrs = $oldItemOrder->getAttributeList();

		foreach($opts as $key => $conf){
			$new = (isset($newAttrs[$key])) ? trim($newAttrs[$key]) : null;
			$old = (isset($oldAttrs[$key])) ? trim($oldAttrs[$key]) : null;

			if($new != $old){
				$changes[] = $conf["name"] . "を『" . $old . "』から『" . $new . "』に変更しました";
			}
		}

		return $changes;
	}


	function add(){
		self::prepare();
		$opts = ItemOptionUtil::getOptions();

		/** @ToDo 何をしたかったか？を調べる **/
	}

	/**
	 * 注文詳細で登録されている商品オプションを変更できるようにする
	 */
	function edit($key){
		if($key == self::ADMIN_OPTION_KEY) return "オプション";

		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		return (isset($opts[$key]["name"])) ? $opts[$key]["name"] : "";
	}

	function build($itemOrderId, $key, $selected){
		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts) || !isset($opts[$key])) return "";

		$type = (isset($opts[$key]["type"])) ? $opts[$key]["type"]: "select";
		$name = "Item[" . $itemOrderId . "][attributes][" . $key . "]";	//必須

		/** 多言語化を加味 **/
		if(!defined("SOYSHOP_ADMIN_LANGUAGE")) define("SOYSHOP_ADMIN_LANGUAGE", "jp");
		$v = ItemOptionUtil::getFieldValueByItemOrderId($key, $itemOrderId, SOYSHOP_ADMIN_LANGUAGE);
		if(!strlen($v)) return "";

		return ItemOptionUtil::buildOption($name, $type, $v, $selected, false);
	}

	function buildOnAdmin($index, $fieldValue, $key, $selected){
		self::prepare();
		$opts = ItemOptionUtil::getOptions();
		if(!count($opts) || !isset($opts[$key])) return "";

		$type = (isset($opts[$key]["type"])) ? $opts[$key]["type"]: "select";
		$name = "Item[" . $index . "][attributes][" . $key . "]";	//必須

		return ItemOptionUtil::buildOption($name, $type, $fieldValue, $selected, false);
	}

	private function getOptionName($values){
		if(defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			return (isset($values["name_" . SOYSHOP_PUBLISH_LANGUAGE]) && strlen($values["name_" . SOYSHOP_PUBLISH_LANGUAGE])) ? $values["name_" . SOYSHOP_PUBLISH_LANGUAGE] : $values["name"];
		}else{
			return $values["name"];
		}
	}

	private function prepare(){
		SOY2::import("module.plugins.common_item_option.util.ItemOptionUtil");

		//多言語の方も念のため
		if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
	}
}

SOYShopPlugin::extension("soyshop.item.option", "common_item_option", "CommonItemOption");
