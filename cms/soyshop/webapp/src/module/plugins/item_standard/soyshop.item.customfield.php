<?php
/*
 */
class ItemStandardField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "item_standard_plugin";

	private $attrDao;
	private $itemDao;

	function doPost(SOYShop_Item $item){
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		if(isset($_POST["Standard"])){
			self::prepare();
			foreach($_POST["Standard"] as $confId => $value){
				if(!strlen($value)) $value = null;

				$obj = self::get($item->getId(), $confId);
				$obj->setValue(trim($value));

				//新規
				if(is_null($obj->getItemId())){
					$obj->setItemId($item->getId());
					$obj->setFieldId(self::PLUGIN_ID . "_" . $confId);
					try{
						$this->attrDao->insert($obj);
						$res = true;
					}catch(Exception $e){
						//
					}

				//更新
				}else{
					try{
						$this->attrDao->update($obj);
					}catch(Exception $e){
						//
					}
				}
			}

			//登録終了した後、商品のタイプをsingleからgroupに変更　逆もある
			$res = false;
			foreach($_POST["Standard"] as $std){
				if(strlen($std)) {
					$res = true;
					break;
				}
			}

			$exe = false;
			if($res){
				if($item->getType() == SOYShop_Item::TYPE_SINGLE){
					$item->setType(SOYShop_Item::TYPE_GROUP);
					$exe = true;
				}else if($item->getType() == SOYShop_Item::TYPE_DOWNLOAD){
					$item->setType(SOYShop_Item::TYPE_DOWNLOAD_GROUP);
					$exe = true;
				}
			}else{
				if($item->getType() == SOYShop_Item::TYPE_GROUP){
					$item->setType(SOYShop_Item::TYPE_SINGLE);
					$exe = true;
				}else if($item->getType() == SOYShop_Item::TYPE_DOWNLOAD_GROUP){
					$item->setType(SOYShop_Item::TYPE_DOWNLOAD);
					$exe = true;
				}
			}

			if($exe){
				try{
					$itemDao->update($item);
				}catch(Exception $e){
					//
				}

				//SINGLE(またはDOWNLOAD)に戻すとき、子商品をすべて削除したい
				if($item->getType() == SOYShop_Item::TYPE_SINGLE || $item->getType() == SOYShop_Item::TYPE_DOWNLOAD){
					$children = soyshop_get_item_children($item->getId());
					if(!count($children)) return;

					//データベース高速化のために完全削除
					foreach($children as $child){
						try{
							$itemDao->delete($child->getId());
						}catch(Exception $e){

						}
					}
				}
			}

			//セールの一括設定と公開設定
			$children = soyshop_get_item_children($item->getId());

			//名前の候補
			$cands = SOY2Logic::createInstance("module.plugins.item_standard.logic.BuildFormLogic", array("parentId" => $item->getId()))->getCandidate();

			$saleFlag = (int)$item->getSaleFlag();
			if(count($children)) foreach($children as $child){
				$child->setSaleFlag($saleFlag);

				//非公開にするか調べる
				$hit = false;
				foreach($cands as $cand){
					if(strpos($child->getName(), $cand)) $hit = true;
				}
				
				if($hit){
					$child->setIsOpen(SOYShop_Item::IS_OPEN);
				}else{
					$child->setIsOpen(SOYShop_Item::NO_OPEN);
				}

				try{
					$itemDao->update($child);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	function getForm(SOYShop_Item $item){

		//規格用のフォームを表示
		if(!is_numeric($item->getType())){
			return SOY2Logic::createInstance("module.plugins.item_standard.logic.BuildFormLogic", array("parentId" => $item->getId()))->buildCustomFieldArea();

		//商品名と商品コードは変更させない様にする
		}else{
			$html = "<script>";
			$html .= file_get_contents(dirname(__FILE__) . "/js/readonly.js");
			$html .= "</script>";
			return $html;
		}
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");
		self::prepare();

		foreach(ItemStandardUtil::getConfig() as $values){
			$obj = self::get($item->getId(), $values["id"]);


			$htmlObj->addModel("item_standard_" . $values["id"] . "_show", array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"visible" => (strlen($obj->getValue()))
			));

			$opts = explode("\n", $obj->getValue());
			$list = array();
			foreach($opts as $opt){
				$list[] = trim($opt);
			}

			$htmlObj->addSelect("item_standard_" . $values["id"], array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"name" => "Standard[" . $values["id"] . "]",
				"options" => $list,
				"id" => "item_standard_" . $values["id"] . "_" . $item->getId()
			));

		}

		//小商品に在庫切れのものがあるか？
		$htmlObj->addModel("has_no_stock_child", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (self::checkIsChildItemStock($item->getId(), $item->getType()))
		));

		//小商品の価格の最小値
		list($sellingMin, $normalMin, $saleMin) = self::getItemStandardPrice($item, "min");
		$htmlObj->addLabel("standard_price_min", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($sellingMin)
		));

		$htmlObj->addLabel("standard_normal_price_min", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($normalMin)
		));

		$htmlObj->addLabel("standard_sale_price_min", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => number_format($saleMin)
		));

		//小商品の価格の最大値
		list($sellingMax, $normalMax, $saleMax) = self::getItemStandardPrice($item, "max");
		$htmlObj->addLabel("standard_price_max", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => ($sellingMax > $sellingMin) ? soy2_number_format($sellingMax) : ""
		));

		$htmlObj->addLabel("standard_normal_price_max", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => ($normalMax > $normalMin) ? soy2_number_format($normalMax) : ""
		));

		$htmlObj->addLabel("standard_sale_price_max", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => ($saleMax > $saleMin) ? soy2_number_format($saleMax) : ""
		));



		$htmlObj->addModel("standart_price_not_same", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($sellingMax > $sellingMin)
		));

		$htmlObj->addModel("standart_normal_price_not_same", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($normalMax > $normalMin)
		));

		$htmlObj->addModel("standart_sale_price_not_same", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($saleMax > $saleMin)
		));

		$htmlObj->addLabel("standard_chain", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => self::getStandartChain($item)
		));

		//カートを表示する場合は$obj->getValue()が1ではない
//		$htmlObj->addModel("has_cart_link", array(
//			"soy2prefix" => SOYSHOP_SITE_PREFIX,
//			"visible" => ($obj->getValue() != self::CHECKED)
//		));
	}

	function onDelete($id){}

	private function get($itemId, $confId){
		try{
			return $this->attrDao->get($itemId, self::PLUGIN_ID . "_" . $confId);
		}catch(Exception $e){
			return new SOYShop_ItemAttribute();
		}
	}

	private function checkIsChildItemStock($parentId, $type){
		if($type != "group") return false;

		$sql = "SELECT COUNT(*) FROM soyshop_item ".
				"WHERE item_type = :parentId ".
				"AND item_stock = 0 ".
				"AND is_disabled != 1";

		try{
			$res = $this->attrDao->executeQuery($sql, array(":parentId" => $parentId));
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]["COUNT(*)"]) && $res[0]["COUNT(*)"] > 0);
	}

	private function getItemStandardPrice(SOYShop_Item $item, $mode = "min"){
		if($item->getType() != SOYShop_Item::TYPE_GROUP) return 0;

		$sql = "SELECT item_price, item_sale_price, item_selling_price FROM soyshop_item ".
				"WHERE item_type = :t ".
				"AND item_is_open = " . SOYShop_Item::IS_OPEN . " ".
				"AND is_disabled != " . SOYShop_Item::IS_DISABLED . " ".
				"AND order_period_start < ". time() . " ".
				"AND order_period_end > " . time();

		try{
			$res = $this->attrDao->executeQuery($sql, array(":t" => $item->getId()));
		}catch(Exception $e){
			return array(0, 0, 0);
		}

		if(!count($res)) return array(0, 0, 0);

		$sellingPrice = null;
		$normalPrice = null;
		$salePrice = null;

		foreach($res as $v){
			if(!isset($v["item_selling_price"])) continue;

			//初回は必ずデータを入れる
			if(is_null($normalPrice)) $normalPrice = (int)$v["item_price"];
			if(is_null($salePrice)) $salePrice = (int)$v["item_sale_price"];
			if(is_null($sellingPrice)) $sellingPrice = (int)$v["item_selling_price"];

			if($mode == "min"){
				if($v["item_price"] < $normalPrice) $normalPrice = (int)$v["item_price"];
				if($v["item_sale_price"] < $salePrice) $salePrice = (int)$v["item_sale_price"];
				if($v["item_selling_price"] < $sellingPrice) $sellingPrice = (int)$v["item_selling_price"];
			}else{
				if($v["item_price"] > $normalPrice) $normalPrice = (int)$v["item_price"];
				if($v["item_sale_price"] > $salePrice) $salePrice = (int)$v["item_sale_price"];
				if($v["item_selling_price"] > $sellingPrice) $sellingPrice = (int)$v["item_selling_price"];
			}
		}

		return array($sellingPrice, $normalPrice, $salePrice);
	}

	private function getStandartChain(SOYShop_Item $item){
		if(!is_numeric($item->getType())) return "";

		try{
			$parent = $this->itemDao->getById($item->getType());
		}catch(Exception $e){
			return "";
		}

		return trim(str_replace($parent->getName(), "", $item->getName()));
	}

	private function prepare(){
		if(!$this->attrDao) $this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		if(!$this->itemDao) $this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "item_standard", "ItemStandardField");
