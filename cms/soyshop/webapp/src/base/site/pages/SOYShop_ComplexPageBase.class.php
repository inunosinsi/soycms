<?php

class SOYShop_ComplexPageBase extends SOYShopPageBase{

	private $logic;

	function build($args){
		if($this->getPageObject() instanceof SOYShop_Page){
			$obj = $this->getPageObject()->getPageObject();

			//条件によってはComplexページでなくても、このファイルを読み込む可能性があるため、別ページの場合の対策
			if(method_exists($obj, "getBlocks")){
				//SearchItemUtilの作成。ソート順作成のためlistPageオブジェクトを渡す
				$this->logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array("mode" => "complex"));

				$blocks = $obj->getBlocks();
				foreach($blocks as $blockId => $block){
					//念のために、クラス名を調べておく
					if(get_class($block) != "SOYShop_ComplexPageBlock") continue;

					//item_list
					$this->createAdd($blockId, "SOYShop_ItemListComponent", array(
						"list" => self::getItems($block),
						"soy2prefix" => "block"
					));
				}
			}
		}

	}

	private function getItems(SOYShop_ComplexPageBlock $block){

		$isAnd = $block->isAndCustomFieldCordination();

		//表示件数が無記入だった場合、1～10件を表示する
		if(strlen($block->getCountStart()) === 0 && strlen($block->getCountEnd()) === 0){
			$countStart = 1;
			$countEnd = 10;
		}else{
			$countStart = (is_numeric($block->getCountStart())) ? (int)$block->getCountStart() : 1;
			$countEnd = (is_numeric($block->getCountEnd())) ? (int)$block->getCountEnd() : 10;
		}

		//設定は1～。送信するデータは0開始
		$offset = (strlen($countStart) > 0) ? (int)($countStart-1) : null;
		$limit = ($countEnd) ? $countEnd - $countStart + 1 : null;

		$customFields = array();
		$customFieldCordinations = $block->getCustomFields();

		if(count($customFieldCordinations)){
			foreach($customFieldCordinations as $array){
				if(is_numeric($array["type"]) && (int)$array["type"] < 0) continue;

				$value = $array["value"];
				if(false !== strpos($array["type"],"LIKE")){
					$value = "%" . $value . "%";
				}

				$customFields[] = array(
					"fieldId" => $array["fieldId"],
					"value" => $value,
					"type" => $array["type"]
				);
			}
		}

		//ソート情報用にSOYShop_ComplexPageBlockを渡す
		$this->logic->setSort($block);

		list($items,$total) = $this->logic->searchItems(
			$block->getCategories(),
			$customFields,
			$block->getParams(),
			$offset,
			$limit,
			$isAnd
		);

		//商品ブロックの条件に子商品がある場合は除く
		$result = array();
		foreach($items as $item){
			$result[] = $item;
		}

		return $result;
	}
}
