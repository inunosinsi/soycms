<?php

class OrderItemListComponent extends HTMLList{

    private $itemDao;

    function populateItem($entity){
        $item = soyshop_get_item_object($entity->getItemId());

        $this->addLink("item_link", array(
            "link" => soyshop_get_item_detail_link($item)
        ));

        $this->addLabel("item_name", array(
            "text" => (strlen($item->getCode())) ? $entity->getItemName() : "---"
        ));

        $this->addImage("item_small_image", array(
            "src" => soyshop_convert_file_path($item->getAttribute("image_small"), $item)
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
