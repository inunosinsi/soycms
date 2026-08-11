<?php

class LanguageListComponent extends HTMLList{

    private $index;
    private $values;

    protected function populateItem($entity, $key){

        $this->addLabel("lang", array(
            "text" => $key
        ));

        $this->addInput("lang_url", array(
            "name" => "config[" . $this->index . "][" . $key . "]",
            "value" => (!is_null($key) && isset($this->values[$key])) ? $this->values[$key] : null
        ));

        if($key == UtilMultiLanguageUtil::LANGUAGE_JP) return false;
    }

    function setIndex($index){
        $this->index = $index;
    }

    function setValues($values){
        $this->values = $values;
    }
}
