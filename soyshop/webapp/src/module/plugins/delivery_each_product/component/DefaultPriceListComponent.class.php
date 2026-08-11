<?php

class DefaultPriceListComponent extends HTMLList {

  function populateItem($entity, $key, $counter, $length){
    $this->addInput("price", array(
      "attr:id"  => "default_price_" . $key,
      "value" => (isset($entity) && is_numeric($entity)) ? $entity : null
    ));
  }
}
