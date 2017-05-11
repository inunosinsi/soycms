<?php
SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
class MultiLanguageCategoryNameListComponent extends HTMLList{

    protected function populateItem($entity, $key){

        $this->addCheckBox("language", array(
            "name" => "item[customfield(category_name_" . $key . ")]",
            "value" => 1,
            "label" => "カテゴリ(" . $key . ")",
            "selected" => true
        ));

        if($key == UtilMultiLanguageUtil::LANGUAGE_JP) return false;
    }
}
