<?php
SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
class CustomSearchFieldImExportListComponent extends HTMLList{

    private $languages;

    protected function populateItem($item, $fieldId){
        $this->createAdd("csf_language_list", "CustomSearchFieldLanguagesListComponet", array(
            "list" => $this->languages,
            "item" => $item,
            "fieldId" => $fieldId
        ));
    }

    function setLanguages($languages){
        $this->languages = $languages;
    }
}

class CustomSearchFieldLanguagesListComponet extends HTMLList{
    private $item;
    private $fieldId;

    protected function populateItem($entity, $lang){

        $this->addCheckBox("custom_search_field_input", array(
            "label" => self::getLabel($lang),
            "name" => (ITEM_CSV_IMEXPORT_MODE == "export") ? "item[custom_search_field(" . $this->fieldId . ")][" . $lang . "]" : "item[custom_search_field(" . $this->fieldId . ")(" . $lang . ")]",
            "value" => 1,
            "selected" => true
        ));
    }

    private function getLabel($lang){
        if(!isset($lang) || !isset($this->item["label"])) return "";

        $label = $this->item["label"];
        if($lang == UtilMultiLanguageUtil::LANGUAGE_JP) return $label;

        return $label . "(" . $lang . ")";
    }

    function setItem($item){
        $this->item = $item;
    }

    function setFieldId($fieldId){
        $this->fieldId = $fieldId;
    }
}
