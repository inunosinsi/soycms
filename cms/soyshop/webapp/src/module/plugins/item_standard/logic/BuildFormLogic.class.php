<?php

class BuildFormLogic extends SOY2LogicBase{

	const PLUGIN_ID = "item_standard_plugin";

	private $parentId;	//コンストラクト時に商品IDを指定しておく
	private $childLogic;
	private $parentItem;

	private $isFirst = false;

	function __construct(){
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");
		$this->childLogic = SOY2Logic::createInstance("module.plugins.item_standard.logic.ChildItemLogic");
	}

	function buildCustomFieldArea(){
		$cnfs = ItemStandardUtil::getConfig();

		if(!count($cnfs)) return "";

		$html = array();
		$html[] = "<br>";
		$html[] = "<div class=\"alert alert-info\">商品の規格設定</div>";
		$html[] = "<div style=\"float:left;margin-right:5px;\">※規格を改行区切りで入力してください</div>";
		$html[] = "<div style=\"float:left;\"><a href=\"" . SOY2PageController::createLink("Config.Detail") . "?plugin=item_standard&item_id=" . $this->parentId . "\" class=\"btn btn-success\">規格毎の料金設定</a></div>";
		$html[] = "<br style=\"clear:both;\">";
		foreach($cnfs as $cnf){
			$html[] = "<div class=\"form-group\">";
			$html[] = "<label>" . $cnf["standard"] . "(" . $cnf["id"] . ")</label>";
			$html[] = "<textarea name=\"Standard[" . self::_cnfId($cnf["id"]) . "]\" class=\"form-control\" style=\"height:100px;\">" . ItemStandardUtil::getStandardValueByItemId($this->parentId, $cnf["id"]) . "</textarea>";
			$html[] = "</div>";
		}
		$html[] = "<div class=\"alert alert-info\">商品の規格設定ここまで</div>";

		//子商品のエリアを非表示にする
		$html[] = "<script>";
		$html[] = file_get_contents(SOY2::rootDir() . "module/plugins/item_standard/js/form.js");
		$html[] = "</script>";

		return implode("\n", $html);
	}

	function buildCollectiveFormArea(){
		$cnfs = ItemStandardUtil::getConfig();

		if(!count($cnfs)) return "";

		$html = array();
		$html[] = "<dl>";
		foreach($cnfs as $cnf){
			$html[] = "<dt>" . $cnf["standard"] . "(" . $cnf["id"] . ")</dt>";
			$html[] = "<dd><textarea name=\"Standard[" . self::_cnfId($cnf["id"]) . "]\" class=\"form-control\" style=\"height:200px;\"></textarea>";
		}
		$html[] = "</dl>";

		return implode("\n", $html);
	}

	/**
	 * どこかのバージョンでFIELD_IDからitem_standard_plugin_を除いてしまったからその対策
	 * @param string
	 * @return string
	 */
	private function _cnfId(string $cnfId){
		$res = strpos($cnfId, ItemStandardUtil::FIELD_ID_PREFIX);
		if(!is_numeric($res) || $res > 0) $cnfId =  ItemStandardUtil::FIELD_ID_PREFIX . $cnfId;
		return $cnfId;
	}

	function buildStandardListArea(){
		$list = array();	//使用する規格を保持しておく配列

		$cnfs = ItemStandardUtil::getConfig();

		$html = array();

		$html[] = "<div class=\"table-responsive\">";
		$html[] = "<table class=\"table table-striped\" style=\"width:60px;\">";
		$html[] = "<caption>規格毎の設定</caption>";
		$html[] = "	<thead>";
		$html[] = "		<tr>";

		foreach($cnfs as $cnf){
			if(isset($cnf["id"]) && self::checkFieldValue($cnf["id"])){
				$html[] = "<th nowrap>" . $cnf["standard"] . "</th>";
				$list[] = $cnf["id"];		//使用する規格を保持しておく
			}
		}
		$html[] = "<th nowrap>在庫数</th>";
		$html[] = "<th nowrap>価格</th>";
		$html[] = "<th nowrap>セール価格</th>";
		$html[] = "<th></th>";

		$html[] = "		</tr>";
		$html[] = "	</thead>";

		$html[] = "	<tbody>";

		//候補を作成する
		if(count($list)) foreach(self::_getCandidate($list) as $key =>  $candidate){
			$html[] = "		<tr>";
			$html[] = self::buildTd($candidate, $key);
			$html[] = "		</tr>";
		}

		$html[] = "	</tbody>";
		$html[] = "</table>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	function getCandidate(){
		$list = array();	//使用する規格を保持しておく配列

		foreach(ItemStandardUtil::getConfig() as $cnf){
			if(isset($cnf["id"]) && self::checkFieldValue($cnf["id"])){
				$list[] = $cnf["id"];		//使用する規格を保持しておく
			}
		}

		$cands = self::_getCandidate($list);

		$list = array();
		if(count($cands)){
			foreach($cands as $cand){
				$list[] = str_replace(";", " ", $cand);
			}
		}

		return $list;
	}

	private function _getCandidate(array $list){
		$array = array();
		$cmb = 1;	//組み合わせの数
		$cnt = array();	//各々の要素数

		foreach($list as $cnfId){
			$attrValue = soyshop_get_item_attribute_value($this->parentId, $cnfId, "string");
			if(!strlen($attrValue)) continue;

			$values = explode("\n", $attrValue);
			$new = array();
			foreach($values as $value){
				$new[] = trim($value);
			}
			$array[] = $new;
		}

		//
		$cands = array();	//候補
		if(isset($array[0])) $cands = $array[0];
		if(count($array)){
			for($i = 1; $i < count($array); $i++){
				$cands = self::direct_product($cands, $array[$i]);
				if(!isset($array[$i + 1])) break;
			}
		}
		return $cands;
	}

	private function direct_product(array $array1, array $array2) {
		$new = array();

		foreach ($array1 as $v0) {
			foreach ($array2 as $v1) {
				$new[] = $v0 . ";" . $v1;
			}
		}

		return $new;
	}

	private function buildTd($candidate, $key){
		$values = explode(";", $candidate);
		$child = $this->childLogic->getChildItem($this->parentId, $values);

		$stock = (!is_null($child->getStock())) ? (int)$child->getStock() : 0;
		$price = (!is_null($child->getPrice())) ? (int)$child->getPrice() : null;
		$salePrice = (!is_null($child->getSalePrice())) ? (int)$child->getSalePrice() : null;

		//子商品の情報が無ければ親商品の情報を取得しておく
		if(is_null($child->getId())){
			$parent = self::getParentItem();
			if(!$price) $price = (int)$parent->getPrice();
			if(!$salePrice) $salePrice = (int)$parent->getSalePrice();
			$this->isFirst = true;
		}

		$html = array();

		foreach($values as $value){
			$value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
			$html[] = "<td nowrap>";
			$html[] = $value;
			$html[] = "<input type=\"hidden\" name=\"Item[" . $key . "][key][]\" value=\"" . $value . "\">";
			$html[] = "</td>";
		}

		$html[] = "<td><input type=\"number\" name=\"Item[" . $key . "][stock]\" value=\"" . $stock . "\" class=\"short\"></td>";
		$html[] = "<td><input type=\"number\" name=\"Item[" . $key . "][price]\" value=\"" . $price . "\"></td>";
		$html[] = "<td><input type=\"number\" name=\"Item[" . $key . "][salePrice]\" value=\"" . $salePrice . "\"></td>";
		$html[] = "<td nowrap>";
		$html[] = "<input type=\"submit\" class=\"btn btn-primary btn-sm\" value=\"更新\">";
		//詳細ページへのリンク
		if(!is_null($child->getId())){
			$html[] = "  <a href=\"" . SOY2PageController::createLink("Item.Detail.". $child->getId()) . "\" class=\"btn btn-info btn-sm\">詳細</a>";
		}
		$html[] = "</td>";
		return implode("\n", $html);
	}

	private function checkFieldValue($cnfId){
		return (strlen(soyshop_get_item_attribute_value($this->parentId, $cnfId, "string")));
	}

	private function getParentItem(){
		if(!$this->parentItem) $this->parentItem = soyshop_get_item_object($this->parentId);
		return $this->parentItem;
	}

	function getIsFirst(){
		return $this->isFirst;
	}

	function setParentId($parentId){
		$this->parentId = (int)$parentId;
	}
}
