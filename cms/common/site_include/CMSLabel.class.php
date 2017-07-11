<?php

class CMSLabel extends HTMLLabel{

    private $defaultSuffix = "...";

    function getObject(){
        $parent_obj = parent::getObject();
        return $this->replaceCMSLink($parent_obj);
    }

    function execute(){

        // Use cms:alt if text is empty.
        $alt = $this->getAttribute("cms:alt");
        if(!is_null($alt)){
            if(is_null($this->text) || strlen($this->text) == 0){
                $this->setText($alt);
            }
        }

        // Trim text to the designated length and remove html tags of text.
        $length = $this->getAttribute("cms:length");
        if(!is_null($length) && is_numeric($length)){
            $text = trim(SOY2HTML::ToText($this->text));
            mb_internal_encoding("UTF-8");
            $shortText = mb_substr($text,0,(int)$length);

            $suffix = "";
            if(strlen($shortText) < strlen($text)){
                $suffix = $this->getAttribute("cms:suffix");
                if(strlen($suffix)==0){
                    $suffix = $this->defaultSuffix;
                }
            }

            $this->setText($shortText.$suffix);
        }

        parent::execute();

    }


    public function replaceCMSLink($content){

        //リンクの置換え
        $plugin = new CMSPageLinkPlugin();

        $plugin->setSiteRoot(SOY2PageController::createLink(""));

        while(true){
            list($tag,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) =
                $plugin->parse("link","[0-9]+",$content);

            if(!strlen($tag))break;

            $plugin->_attribute = array();
            $plugin->_soy2_attribute = array();

            $plugin->setTag($tag);
            $plugin->parseAttributes($line);
            $plugin->setInnerHTML($innerHTML);
            $plugin->setOuterHTML($outerHTML);
            $plugin->setParent($this);
            $plugin->setSkipEndTag($skipendtag);
            $plugin->setSoyValue($value);

            //特別動作
            $plugin->executeReplace($value);

            $content = $this->getContent($plugin,$content);
        }

        return $content;
    }



    public function getDefaultSuffix() {
        return $this->defaultSuffix;
    }
    public function setDefaultSuffix($defaultSuffix) {
        $this->defaultSuffix = $defaultSuffix;
    }
}
