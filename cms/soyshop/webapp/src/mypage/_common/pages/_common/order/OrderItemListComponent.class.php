<?php

class OrderItemListComponent extends HTMLList{

    private $itemDao;

    function populateItem($entity){
		$itemId = (is_numeric($entity->getItemId())) ? (int)$entity->getItemId() : 0;
        $item = soyshop_get_item_object($itemId);

        $this->addLink("item_link", array(
            "link" => soyshop_get_item_detail_link($item)
        ));

        $this->addLabel("item_name", array(
            "text" => (strlen($item->getCode())) ? $entity->getItemName() : "---"
        ));

		$smallImagePath = $item->getAttribute("image_small");
        $this->addImage("item_small_image", array(
            "src" => (is_string($smallImagePath)) ? soyshop_convert_file_path($smallImagePath, $item) : ""
        ));

		$html = ($entity instanceof SOYShop_ItemOrder) ? soyshop_build_item_option_html_on_item_order($entity) : "";

        $this->addModel("has_option", array(
            "visible" => (strlen($html) > 0)
        ));

        $this->addLabel("item_option", array(
            "html" => $html
        ));
    }
}
