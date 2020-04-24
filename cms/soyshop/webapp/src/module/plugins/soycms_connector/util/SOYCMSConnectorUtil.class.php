<?php

class SOYCMSConnectorUtil {

  public static function getConfig(){
    return SOYShop_DataSets::get("soycms_connector.config", array("siteId" => "site"));
  }

  public static function saveConfig($values){
    SOYShop_DataSets::put("soycms_connector.config", $values);
  }
}
