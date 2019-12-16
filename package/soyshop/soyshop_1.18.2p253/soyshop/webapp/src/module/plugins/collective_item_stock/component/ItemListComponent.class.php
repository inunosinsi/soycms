<?php

class ItemListComponent extends HTMLList{
	
	private $detailLink;
	private $categories;
	private $itemOrderDAO;
	private $categoriesDAO;
	private $config;
	
	function populateItem($item, $key){
		
		$this->addLabel("ranking", array(
			"text" => $key + 1
		));
		
		$this->addLabel("item_id", array(
			"text" => $item->getId()
		));
		
		$this->addLabel("update_date", array(
			"text" => print_update_date($item->getUpdateDate())
		));
		
		$this->addLabel("item_publish", array(
			"text" => $item->getPublishText()// . ($item->isOnSale() ? MessageManager::get("ITEM_ON_SALE") : "")
		));
		$this->addLabel("sale_text", array(
			"text" => " " . MessageManager::get("ITEM_ON_SALE"),
			"visible" => $item->isOnSale()
		));

		$this->addLabel("item_name", array(
			"text" => $item->getName()
		));

		$this->addLabel("item_code", array(
			"text" => $item->getCode()
		));

		$this->addLabel("item_price", array(
			"text" => number_format($item->getPrice())
		));
		$this->addModel("is_sale", array(
			"visible" => $item->isOnSale()
		));
		$this->addLabel("sale_price", array(
			"text" => number_format($item->getSalePrice())
		));
		
		$this->addInput("item_stock_input", array(
			"name" => "Stock[" . $item->getId() . "]",
			"value" => $item->getStock(),
			"style" => "width:80px;text-align:right;"
		));
		
		$detailLink = $this->getDetailLink() . $item->getId();
		$this->addLink("detail_link", array(
			"link" => $detailLink
		));

		$this->addLabel("order_count", array(
			"text" => number_format(self::getOrderCount($item))
		));
		
		$this->addLabel("item_category", array(
			"text" => (isset($this->categories[$item->getCategory()])) ? $this->categories[$item->getCategory()]->getNameWithStatus() : "-"
		));
	}
	
	private function getOrderCount($item){

		$childItemStock = $this->config->getChildItemStock();
		//子商品の在庫管理設定をオン(子商品の注文数合計を取得する)
		if($childItemStock){
			//子商品のIDを取得する
			$ids = $this->getChildItemIds($item->getId());
			$count = 0;
			if(count($ids) > 0){
				
				foreach($ids as $id){
					try{
						$count = $count + $this->itemOrderDAO->countByItemId($id);
					}catch(Exception $e){
						//
					}
				}
				return $count;	
			}
		}

		try{
			return $this->itemOrderDAO->countByItemId($item->getId());
		}catch(Exception $e){
			return 0;
		}				
	}
	
	function getDetailLink() {
		return $this->detailLink;
	}
	function setDetailLink($detailLink) {
		$this->detailLink = $detailLink;
	}

	function getCategories() {
		return $this->categories;
	}
	function setCategories($categories) {
		$this->categories = $categories;
	}

	function getItemOrderDAO() {
		return $this->itemOrderDAO;
	}
	function setItemOrderDAO($itemOrderDAO) {
		$this->itemOrderDAO = $itemOrderDAO;
	}
	
	function getCategoriesDAO(){
		return $this->categoriesDAO;
	}
	function setCategoriesDAO($categoriesDAO){
		$this->categoriesDAO = $categoriesDAO;
	}
	
	function setConfig($config){
		$this->config = $config;
	}
}
?>