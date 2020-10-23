<?php
/**
 * @class Site.Pages.DetailPage
 * @date 2009-11-17T18:50:35+09:00
 * @author SOY2HTMLFactory
 */
class DetailPage extends WebPage{

    function doPost(){

        if(soy2_check_token()){
            $logic = SOY2Logic::createInstance("logic.site.page.PageLogic");

            $page = soyshop_get_page_object($this->id);
            $obj = (object)$_POST["Page"];
            SOY2::cast($page, $obj);
            if(isset($_POST["Page"]["uri"])){
                $page->setUri(soyshop_remove_close_slash($_POST["Page"]["uri"]));
            }

            if($logic->validate($page)){

                //uriがNULLの場合は_homeを入れる
                if(is_null($page->getUri())) $page->setUri(SOYShop_Page::URI_HOME);

                $logic->update($page);

				SOYShopPlugin::load("soyshop.page.update");
				SOYShopPlugin::invoke("soyshop.page.update", array(
					"pageId" => $page->getId()
				));

                SOY2PageController::jump("Site.Pages.Detail." . $this->id . "?updated");
            }

            $this->page = $page;
            $this->errors = $logic->getErrors();
        }

    }

    private $id;
    private $page;
    private $errors = array();

    function __construct($args){
        $this->id = (isset($args[0])) ? (int)$args[0] : null;
        if(!$this->id) SOY2PageController::jump("Site.Pages");

        MessageManager::addMessagePath("admin");

        parent::__construct();

        $this->addForm("update_form");

        self::_buildForm();
    }

    private function _buildForm(){
        $logic = SOY2Logic::createInstance("logic.site.page.PageLogic");

        try{
            $obj = ($this->page) ? $this->page : soyshop_get_page_object($this->id);
        }catch(Exception $e){
            SOY2PageController::jump("Site.Pages");
            exit;
        }

        $this->page = $obj;

        $this->addInput("name", array(
            "name" => "Page[name]",
            "value" => $obj->getName()
        ));

        $this->addInput("uri", array(
            "name" => "Page[uri]",
            "value" => $obj->getUri(),
            "disabled" => ($obj->getUri() == SOYSHOP_TOP_PAGE_MARKER || $obj->getUri() == SOYSHOP_404_PAGE_MARKER || $obj->getUri() == SOYSHOP_MAINTENANCE_PAGE_MARKER),
        ));

        //_home
        $this->addModel("caution_for_home", array(
            "visible" => $obj->getUri() == SOYSHOP_TOP_PAGE_MARKER,
        ));
        $this->addLabel("SOYSHOP_TOP_PAGE_MARKER", array(
            "text" => SOYSHOP_TOP_PAGE_MARKER,
        ));

        //_404_not_found_
        $this->addModel("caution_for_404", array(
            "visible" => $obj->getUri() == SOYSHOP_404_PAGE_MARKER,
        ));
        $this->addLabel("SOYSHOP_404_PAGE_MARKER", array(
            "text" => SOYSHOP_404_PAGE_MARKER,
        ));

		//_maintenance
        $this->addModel("caution_for_maintenance", array(
            "visible" => $obj->getUri() == SOYSHOP_MAINTENANCE_PAGE_MARKER,
        ));
        $this->addLabel("SOYSHOP_MAINTENANCE_PAGE_MARKER", array(
            "text" => SOYSHOP_MAINTENANCE_PAGE_MARKER,
        ));

        $this->addLabel("type_text", array(
            "text" => $obj->getTypeText()
        ));

        /* template */

        $this->addSelect("template", array(
            "name" => "Page[template]",
            "options" => $obj->getTemplateList(),
            "selected" => $obj->getTemplate()
        ));

        $this->addLink("btn_template_edit", array(
            "link" => SOY2PageController::createLink("Site.Template.Editor.-.") . $obj->getType() ."/" . $obj->getTemplate(false)
        ));

        $this->addLink("btn_custom_template_edit", array(
            "link" => SOY2PageController::createLink("Site.Template.Editor.-.") . $obj->getCustomTemplateFilePath(false) . "?id=" . $obj->getId(),
        ));

        $customTemplateFilePath = $obj->getCustomTemplateFilePath();

        $this->addLink("btn_template_custom", array(
            "link" => SOY2PageController::createLink("Site.Pages.Template.Action." . $obj->getId() . "?generate"),
        ));

        $this->addLink("btn_template_restore", array(
            "link" => SOY2PageController::createLink("Site.Pages.Template.Action." . $obj->getId() . "?restore"),
        ));

        $this->addModel("template_select", array(
            "visible" => (false == file_exists($customTemplateFilePath))
        ));

        $this->addModel("template_custom", array(
            "visible" => (true == file_exists($customTemplateFilePath))
        ));


        /* /template */

        $this->addInput("keyword", array(
            "name" => "Page[config][keyword]",
            "value" => $obj->getKeyword()
        ));

        $this->addLabel("keyword_format_description", array(
            "html" => $obj->getPageObject()->getKeywordFormatDescription()
        ));

        $this->addTextArea("description", array(
            "name" => "Page[config][description]",
            "value" => $obj->getDescription()
        ));

        $this->addLabel("description_format_description", array(
            "html" => $obj->getPageObject()->getDescriptionFormatDescription()
        ));

        $this->addInput("canonical_format", array(
            "name" => "Page[config][canonical_format]",
            "value" => $obj->getCanonicalFormat()
        ));

        $this->addLabel("canonical_format_description", array(
            "html" => $obj->getPageObject()->getCanonicalFormatDescription()
        ));

        $this->addInput("title_format", array(
            "name" => "Page[config][title_format]",
            "value" => $obj->getTitleFormat()
        ));

        $this->addLabel("title_format_description", array(
            "html" => $obj->getPageObject()->getTitleFormatDescription()
        ));

        $this->addSelect("charset_list", array(
            "name" => "Page[config][charset]",
            "selected" => $obj->getCharset(),
            "options" => array(
                "UTF-8", "Shift_JIS", "EUC-JP"
            )
        ));

        $cssUrl = $obj->getCSSURL();
        $this->addLink("css_url", array(
            "text" => $cssUrl,
            "link" => $cssUrl
        ));
        $this->addModel("css_custom", array(
            "visible" => file_exists(str_replace(SOYSHOP_SITE_URL, SOYSHOP_SITE_DIRECTORY, $cssUrl))
        ));
        $this->addLink("btn_css_custom", array(
            "link" => SOY2PageController::createLink("Site.Pages.Template.Action." . $obj->getId() . "?generate_css"),
            "visible" => !file_exists(str_replace(SOYSHOP_SITE_URL,SOYSHOP_SITE_DIRECTORY, $cssUrl))
        ));

        //error
        foreach(array("name", "uri") as $key){
            $this->addLabel("error_$key", array(
                "text" => (isset($this->errors[$key])) ? $this->errors[$key] : "",
                "visible" => (isset($this->errors[$key]) && strlen($this->errors[$key]))
            ));
        }
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("ページ設定", array("Site.Pages" => "ページ管理"));
	}

    function getSubMenu(){
        $key = "Site.Pages.SubMenu.SubMenuPage";

        try{
            $subMenuPage = SOY2HTMLFactory::createInstance($key, array(
                "arguments" => array($this->id, $this->page)
            ));
            return $subMenuPage->getObject();
        }catch(Exception $e){
            return null;
        }
    }
}
