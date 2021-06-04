<?php
SOY2::import("module.site.common.output_item",".php");
SOY2::import("domain.shop.SOYShop_Item");

class SOYShop_ItemListComponent extends HTMLList{

	private $obj;
	private $pageClassName;
	private $iteration;
	private $forAdminOnly;

	function execute(){
		$iteration = $this->getAttribute("cms:count");
		if(strlen($iteration)){
			$this->iteration = $iteration;
		}
		parent::execute();
	}

	protected function populateItem($entity, $key, $counter, $length){
		if(false == ($entity instanceof SOYShop_Item)){
			$entity = new SOYShop_Item();
		}

		//指定の表示回数を超えていたら表示しない
		if($this->iteration > 0 && $counter > $this->iteration){
			return false;
		}

		//実行
		soyshop_output_item($this, $entity, $this->obj);

		//非公開は表示しない。ただし、商品詳細確認モードがtrueの場合は表示する。
		return $this->getIsDisplay($entity);
	}

	/**
	 * 商品詳細ページを表示するか？
	 * @param object SOYShop_Item
	 * @return boolean
	 */
	function getIsDisplay($entity){
		//商品詳細確認モード forAdminOnlyがboolean値の場合
		if(isset($this->forAdminOnly) && is_bool($this->forAdminOnly)){
			$isDisplay = $this->forAdminOnly;

		//通常モード forAdminOnlyがnullの場合
		}else{
			$isDisplay = ($entity->isPublished());
		}
		return $isDisplay;
	}

	function getObj(){
		return $this->obj;
	}

	function setObj($obj){
		$this->obj = $obj;
		if($obj){
			$this->pageClassName = get_class($obj);
		}
	}

	function setForAdminOnly($forAdminOnly){
		$this->forAdminOnly = $forAdminOnly;
	}

	public function getPageClassName(){
		return $this->pageClassName;
	}
}

class SOYShop_ChildItemListComponent extends SOYShop_ItemListComponent{};
