<?php

class UrlListComponent extends HTMLList{

    private $languages;

    protected function populateItem($entity, $key){

        $url = (isset($entity["url"])) ? $entity["url"] : "";
        $this->addLink("url", array(
            "link" => $url,
            "text" => $url,
            "target" => "_blank"
        ));

        $this->addActionLink("remove_link", array(
            "link" => SOY2PageController::createLink("Config.Detail?plugin=common_sitemap_xml&remove=" . $key),
            "onclick" => "return confirm('削除しますか？');"
        ));

        $this->createAdd("language_list", "LanguageListComponent", array(
            "list" => $this->languages,
            "index" => $key,
            "values" => $entity
        ));
    }

    function setLanguages($languages){
        $this->languages = $languages;
    }
}
