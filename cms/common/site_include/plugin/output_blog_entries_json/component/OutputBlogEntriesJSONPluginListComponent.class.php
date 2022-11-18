<?php

class OutputBlogEntriesJSONPluginListComponent extends HTMLList {

    private $keys;

    protected function populateItem($entity){
        $arr = (is_array($entity)) ? $entity : array();
        $entryLink = (is_numeric(array_search("url", $this->keys)) && isset($arr["url"]) && is_string($arr["url"])) ? htmlspecialchars($arr["url"], ENT_QUOTES, "UTF-8") : "";
        
        foreach($this->keys as $key){
            switch($key){
                case "cdate":
                case "udate":
                    $k = ($key == "cdate") ? "create_date" : "update_date";
                    $this->createAdd($k,"DateLabel",array(
                        "soy2prefix"=>"cms",
                        "text" => (isset($arr[$key]) && is_numeric($arr[$key])) ? (int)$arr[$key] : 0
                    ));
                    break;
                case "title":
                    $title = (isset($arr[$key]) && is_string($arr[$key])) ? htmlspecialchars($arr[$key], ENT_QUOTES, "UTF-8") : "";
                    $this->createAdd($key, "CMSLabel", array(
                        "soy2prefix" => "cms",
                        "html" => (strlen($entryLink)) ? "<a href=\"".$entryLink."\">".$title."</a>" : $title
                    ));
                    $this->createAdd($key."_plain", "CMSLabel", array(
                        "soy2prefix" => "cms",
                        "text" => $title
                    ));
                    break;
                case "content":
                case "more":
                    $this->createAdd($key, "CMSLabel", array(
                        "soy2prefix" => "cms",
                        "html" => (isset($arr[$key]) && is_string($arr[$key])) ? $arr[$key] : ""
                    ));
                    break;
                case "url":
                    $this->addLink("entry_link", array(
                        "soy2prefix" => "cms",
                        "link" => $entryLink
                    ));
                    break;
                case "thumbnail":
                    $tmbArr = (isset($arr[$key]) && is_array($arr[$key])) ? $arr[$key] : array();
                    foreach(array("thumbnail", "trimming", "upload") as $tmbIdx){
                        $tmbImgPath = (isset($tmbArr[$tmbIdx]) && is_string($tmbArr[$tmbIdx])) ? trim($tmbArr[$tmbIdx]) : "";
                        $this->addModel("is_".$tmbIdx, array(
                            "soy2prefix" => "cms",
                            "visible" => (strlen($tmbImgPath) > 0)
                        ));

                        $this->addModel("no_".$tmbIdx, array(
                            "soy2prefix" => "cms",
                            "visible" => (strlen($tmbImgPath) === 0)
                        ));

                        $this->addImage($tmbIdx, array(
                            "soy2prefix" => "cms",
                            "src" => $tmbImgPath
                        ));

                        $this->addLabel($tmbIdx."_path_text", array(
                            "soy2prefix" => "cms",
                            "text" => $tmbImgPath
                        ));
                    }
                    break;
                default:
                    $this->createAdd($key, "CMSLabel", array(
                        "soy2prefix" => "cms",
                        "text" => (isset($arr[$key]) && is_string($arr[$key])) ? $arr[$key] : ""
                    ));
                    break;
            }
        }
    }

    function setKeys($keys){
        $this->keys = $keys;
    }
}