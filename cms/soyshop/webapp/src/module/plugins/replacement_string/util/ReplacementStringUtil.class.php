<?php

class ReplacementStringUtil {

  public static function getConfig(){
    return SOYShop_DataSets::get("replacement_string.config", array());
  }

  public static function saveConfig($values){
    SOYShop_DataSets::put("replacement_string.config", $values);
  }
}
