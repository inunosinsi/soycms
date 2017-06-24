<?php

class SitemapXMLUtil{

  public static function getConfig(){
    return SOYShop_DataSets::get("sitemap_xml.config", array());
  }

  public static function saveConfig($values){
    SOYShop_DataSets::put("sitemap_xml.config", $values);
  }
}
