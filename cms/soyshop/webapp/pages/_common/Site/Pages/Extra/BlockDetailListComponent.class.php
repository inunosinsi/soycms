<?php

class BlockDetailListComponent extends HTMLList{

	private $selected = null;

	protected function populateItem($entity,$key){

		$block = ($entity instanceof SOYShop_ComplexPageBlock) ? $entity : new SOYShop_ComplexPageBlock();
		$blockId = $entity->getBlockId();

		$this->addLabel("block_id_text", array(
			"text" => $blockId
		));

		$this->addModel("block_detail_row", array(
			"attr:id" => "block_detail_" . $blockId,
			"style" => ($this->selected == $blockId) ? "" : "display:none;"
		));

		//カテゴリ

		$categories = $block->getCategories();

		$this->createAdd("category_cordination_list","_common.Site.Pages.Extra.CategoryCordinationListComponent", array(
			"blockId" => $blockId,
			"list" => (count($categories) > 0) ? $categories : array(-1)
		));

		//カスタムフィールド
		$this->addCheckBox("customfield_cordination_type_and", array(
			"name" => "Block[" . $blockId."][isAndCustomFieldCordination]",
			"value"=>1,
			"selected"=>$block->isAndCustomFieldCordination(),
			"label"=>"全て一致",
		));
		$this->addCheckBox("customfield_cordination_type_or" , array(
			"name" => "Block[" . $blockId."][isAndCustomFieldCordination]",
			"value" => 0,
			"selected" => !$block->isAndCustomFieldCordination(),
			"label" => "いずれかと一致",
		));

		$customField = $block->getCustomFields();

		$this->createAdd("customfield_cordination_list", "_common.Site.Pages.Extra.CustomFieldCordinationListComponent", array(
			"blockId" => $blockId,
			"list" => (count($customField) > 0) ? array_values($customField) : array(array("",""))
		));

		//表示件数
		$this->addInput("item_count_start", array(
			"name" => "Block[" . $blockId."][countStart]",
			"value" => $block->getCountStart()
		));

		$this->addInput("item_count_end", array(
			"name" => "Block[" . $blockId."][countEnd]",
			"value" => $block->getCountEnd()
		));


		//記述例

		$this->addTextArea("template_example", array(
			"value" => 	'<!-- block:id="'.$blockId.'" -->' . "\n" .
						'<div>' . "\n".
						'<p><a cms:id="item_link"><img cms:id="item_small_image" /></a></p>' . "\n".
						'<dl>' . "\n".
						'<dt cms:id="item_name">商品名</dt>' . "\n".
						'<dd cms:id="item_price">商品の価格</dd>' . "\n".
						'</dl>' . "\n".
						'<p><a cms:id="item_cart_link">カートに入れる</a></p>' . "\n".
						'</div>' . "\n".
						'<!-- /block:id="'.$blockId.'" -->'
		));

		/* sort */
		$this->createAdd("sort_list","HTMLList", array(
			"list" => array(
				"name" => "商品名",
				"code" => "商品コード",
				"stock" => "在庫数",
				"price" => "標準価格",
				"cdate" => "作成日",
				"udate" => "更新日"
			),
			'populateItem:function($entity,$key)' =>
					'$this->createAdd("sort_input","HTMLCheckbox", array(' .
						'"name" => "Block['.$blockId.'][defaultSort]",' .
						'"value" => $key,' .
						'"label" => $entity,' .
						'"selected" => ($key == "'.$block->getDefaultSort().'")' .
					'));'
		));

		$this->addCheckBox("sort_custom", array(
			"name" => "Block[" . $blockId."][defaultSort]",
			"value" => "_custom",
			"selected" => ($block->getDefaultSort() == "_custom"),
			"label" => "カスタム項目でソート"
		));

		//ソートで使用するカスタム項目
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$config = SOYShop_ItemAttributeConfig::load(true);
		$indexed = SOYShop_ItemAttributeConfig::getIndexFields();
		$indexed = array_flip($indexed);
		$indexed = array_intersect_key($config,$indexed);
		$this->addSelect("sort_custom_field_list", array(
			"name" => "Block[" . $blockId."][customSort]",
			"selected" => $block->getCustomSort(),
			"options" =>$indexed,
			"property" => "label"
		));


		$this->addCheckBox("sort_normal", array(
			"name" => "Block[" . $blockId."][isReverse]",
			"selected" => (!$block->getIsReverse()),
			"value" => 0,
			"label" => "昇順",
		));

		$this->addCheckBox("sort_reverse", array(
			"name" => "Block[" . $blockId."][isReverse]",
			"selected" => ($block->getIsReverse()),
			"value" => 1,
			"label" => "降順",
		));

		$params = $block->getParams();
		
		//商品グループ 通常商品や親商品	初回設定の場合はparamsが配列ではない
		$this->addCheckBox("is_parent", array(
			"name" => "Block[" . $blockId . "][params][is_parent]",
			"selected" => (is_null($params) || (isset($params["is_parent"]) && $params["is_parent"] == 1)),
			"value" => 1,
			"label" => "通常商品(親商品を含む)"
		));

		//子商品
		$this->addCheckBox("is_child", array(
			"name" => "Block[" . $blockId . "][params][is_child]",
			"selected" => (isset($params["is_child"]) && $params["is_child"] == 1),
			"value" => 1,
			"label" => "子商品"
		));

		//削除
		$this->addModel("remove_link", array(
			"onclick" => 'if(confirm(\'本当に削除しますか？\'))$(\'#block_remove_submit_btn\').val(\''.$blockId.'\').trigger(\'click\');'
		));
	}

	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}
