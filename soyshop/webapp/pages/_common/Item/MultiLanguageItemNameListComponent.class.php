<?php
SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
class MultiLanguageItemNameListComponent extends HTMLList{

    protected function populateItem($entity, $key){

        $this->addCheckBox("language", array(
            "name" => "item[customfield(item_name_" . $key . ")]",
            "value" => 1,
            "label" => "商品名(" . $key . ")",
            "selected" => true
        ));

        if($key == UtilMultiLanguageUtil::LANGUAGE_JP) return false;
    }
}
