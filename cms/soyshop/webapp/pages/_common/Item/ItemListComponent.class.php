<?php

class ItemListComponent extends HTMLList{

	private $detailLink;
	private $categories;
	private $itemOrderDAO;
	private $categoriesDAO;
	private $config;
	private $appLimit;
	
	//マルチカテゴリの設定
	private $multi;

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
			"visible" => $this->appLimit
		));

		$this->addLabel("item_publish", array(
			"text" => $item->getPublishText()// . ($item->isOnSale() ? MessageManager::get("ITEM_ON_SALE") : "")
		));
		
		
		$this->addImage("item_small_image", array(
			"src" => $item->getAttribute("image_small"),
			"width" => 60
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
			"text" => number_format((int)$item->getPrice())
		));
		$this->addModel("is_sale", array(
			"visible" => $item->isOnSale()
		));
		$this->addLabel("sale_price", array(
			"text" => number_format((int)$item->getSalePrice())
		));
		
		//在庫無視モード
		$this->addModel("ignore_stock", array(
			"visible" => ($this->config->getIgnoreStock())
		));

		$this->addModel("display_stock", array(
			"visible" => (is_null($this->config->getIgnoreStock()) || $this->config->getIgnoreStock() == 0)
		));

		$this->addLabel("item_stock", array(
			"text" => number_format($item->getStock())
		));

		//カテゴリー
		if($this->multi == 1){
			try{
				$categories = $this->categoriesDAO->getByItemId($item->getId());
			}catch(Exception $e){
				$categories = array();
			}
			$text = (count($categories) > 0) ? "マルチ" : "-";
		}else{
			$text = (isset($this->categories[$item->getCategory()])) ? $this->categories[$item->getCategory()]->getNameWithStatus() : "-";
		}

		$this->addLabel("item_category", array(
			"text" => $text
		));

		$detailLink = $this->getDetailLink() . $item->getId();
		$this->addLink("detail_link", array(
			"link" => $detailLink
		));

		$this->addLabel("order_count", array(
			"text" => number_format($this->getOrderCount($item))
		));
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

	function getOrderCount($item){

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
	
	function getChildItemIds($itemId){
		
		$ids = array();
		
		$dao = new SOY2DAO();
		$sql = "select id from soyshop_item where item_type = :id";
		$binds = array(":id" => $itemId);
		try{
			$result = $dao->executeQuery($sql,$binds);
		}catch(Exception $e){
			return 0;
		}
		$ids = array();
		foreach($result as $value){
			$ids[] = $value["id"];
		}
		
		return $ids;
	}
	
	function getMulti(){
		return $this->multi;
	}
	function setMulti($multi){
		$this->multi = $multi;
	}
	
	function setConfig($config){
		$this->config = $config;
	}
	
	function setAppLimit($appLimit){
		$this->appLimit = $appLimit;
	}
}
?>