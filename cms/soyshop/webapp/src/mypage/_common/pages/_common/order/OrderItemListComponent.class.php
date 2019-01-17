<?php

class OrderItemListComponent extends HTMLList{

    private $itemDao;

    function populateItem($entity){
        $item = ($entity instanceof SOYShop_ItemOrder) ? self::getItem($entity) : new SOYShop_Item();

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

    private function getItem(SOYShop_ItemOrder $itemOrder){
        if(!$this->itemDao) $this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
        try{
            return $this->itemDao->getById($itemOrder->getItemId());
        }catch(Exception $e){
            return new SOYShop_Item();
        }
    }
}
