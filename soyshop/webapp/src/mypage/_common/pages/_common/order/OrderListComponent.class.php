<?php

class OrderListComponent extends HTMLList{

    function populateItem($entity){

        //注文時刻
        $this->addLabel("order_date", array(
            "text" => (is_numeric($entity->getOrderDate())) ? date("Y年m月d日 H:i", $entity->getOrderDate()) : ""
        ));

        //注文番号
        $this->addLabel("order_number", array(
            "text" => $entity->getTrackingNumber()
        ));

        //合計金額
        $this->createAdd("order_price", "NumberFormatLabel", array(
            "text" => $entity->getPrice()
        ));
        //詳細リンク
        $this->addLink("order_link", array(
            "link" => soyshop_get_mypage_url() . "/order/detail/" . $entity->getId()
        ));

        $this->createAdd("order_item_list", "_common.order.OrderItemListComponent", array(
            "list" => $entity->getItems()
        ));

        return $entity->isOrderDisplay();
    }
}
