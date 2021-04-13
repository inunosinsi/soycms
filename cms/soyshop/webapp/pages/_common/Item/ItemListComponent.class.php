<?php

class ItemListComponent extends HTMLList{

	private $itemStocks = array();
	private $orderCounts = array();
    private $detailLink;

    protected function populateItem($item, $key) {

        $this->addLabel("ranking", array(
            "text" => $key + 1
        ));

        $this->addLabel("item_id", array(
            "text" => $item->getId()
        ));

        $this->addLabel("update_date", array(
            "text" => print_update_date($item->getUpdateDate())
        ));

        $this->addInput("item_check", array(
            "name" => "items[]",
            "value" => $item->getId(),
            "onchange" => '$(\'#items_operation\').show();',
            "visible" => AUTH_OPERATE
        ));

        $this->addLabel("item_publish", array(
            "text" => $item->getPublishText()// . ($item->isOnSale() ? MessageManager::get("ITEM_ON_SALE") : "")
        ));

		$imagePath = soyshop_convert_file_path_on_admin($item->getAttribute("image_small"));
		if(!strlen($imagePath)) $imagePath = soyshop_get_item_sample_image();
		$this->addImage("item_small_image", array(
            //"src" => "/" . SOYSHOP_ID . "/im.php?src=" . $imagePath . "&width=60",	im.phpが使えなくなった
			"src" => $imagePath,
			"attr:style" => "width:60px;"
        ));

        $this->addLabel("sale_text", array(
            "text" => " " . MessageManager::get("ITEM_ON_SALE"),
            "visible" => $item->isOnSale()
        ));

        $this->addLabel("item_name", array(
            "text" => $item->getOpenItemName()
        ));

        $this->addLabel("item_code", array(
            "text" => $item->getCode()
        ));

        $this->addLabel("item_price", array(
            "text" => soy2_number_format((int)$item->getPrice())
        ));
        $this->addModel("is_sale", array(
            "visible" => $item->isOnSale()
        ));
        $this->addLabel("sale_price", array(
            "text" => soy2_number_format((int)$item->getSalePrice())
        ));

        //在庫無視モード
		$isIgnoreStock = (self::_config()->getIgnoreStock() && self::_config()->getIsHiddenStockCount());
        $this->addModel("ignore_stock", array("visible" => ($isIgnoreStock)));
        $this->addModel("display_stock", array("visible" => (!$isIgnoreStock)));

        $this->addLabel("item_stock", array(
            "text" => ($item instanceof SOYShop_Item) ? self::_getItemStock($item) : 0
        ));

        //カテゴリー
        $this->addLabel("item_category", array(
            "text" => ($item instanceof SOYShop_Item) ? self::_getCategoryText($item) : ""
        ));

        $this->addLink("detail_link", array(
            "link" => $this->detailLink. $item->getId()
        ));

        $this->addLabel("order_count", array(
            "text" => (!$isIgnoreStock && $item instanceof SOYShop_Item) ? soy2_number_format(self::_getOrderCount($item)) : null
        ));
    }

	function setItemStocks($itemStocks){
		$this->itemStocks = $itemStocks;
	}
	function setOrderCounts($orderCounts){
		$this->orderCounts = $orderCounts;
	}
    function setDetailLink($detailLink) {
        $this->detailLink = $detailLink;
    }

	/** 便利な関数 **/
	private function _getItemStock(SOYShop_Item $item){
		//表示の高速化の為に事前に在庫数を取得しておく
		if(isset($this->itemStocks[$item->getId()])) return soy2_number_format($this->itemStocks[$item->getId()]);

		//親商品の時に子商品の合計を出力
		if($item->getType() == SOYShop_Item::TYPE_GROUP){
			try{
				$stock = self::_itemDao()->getChildStockTotalByItemId($item->getId());
				return (is_numeric($stock)) ? (int)$stock : 0;
			}catch(Exception $e){
				//
			}
		}
		return soy2_number_format($item->getStock());
	}

	private function _getCategoryText(SOYShop_Item $item){
		if(self::_config()->getMultiCategory()){
            try{
				return (count(self::_categoriesDao()->getByItemId($item->getId())) > 0) ? "マルチ" : "-";
            }catch(Exception $e){
                return "-";
            }
        }else{
			$categories = soyshop_get_category_objects();
            return (isset($categories[$item->getCategory()])) ? $categories[$item->getCategory()]->getNameWithStatus() : "-";
        }
	}

	private function _getOrderCount(SOYShop_Item $item){
		//表示の高速化の為に事前に注文数を取得しておく
		if(isset($this->orderCounts[$item->getId()])) return soy2_number_format($this->orderCounts[$item->getId()]);

		//子商品の在庫管理設定をオン(子商品の注文数合計を取得する)
        if($item->getType() == SOYShop_Item::TYPE_GROUP && self::_config()->getChildItemStock()){
			try{
				return self::_itemOrderDao()->countChildOrderTotalByItemId($item->getId());
			}catch(Exception $e){
				return 0;
			}
        }

		return soy2_number_format($item->getOrderCount());
    }

	/** dao周り **/
	private function _itemDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		return $dao;
	}

	private function _categoriesDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_CategoriesDAO");
		return $dao;
	}

	private function _itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}

	/** config **/
	private function _config(){
		static $cnf;
		if(is_null($cnf)) $cnf = SOYShop_ShopConfig::load();
		return $cnf;
	}
}
