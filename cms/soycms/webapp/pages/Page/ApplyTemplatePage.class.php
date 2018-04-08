<?php

class ApplyTemplatePage extends CMSWebPageBase{

	private $id;
	private $page;

	function doPost(){
		if(soy2_check_token()){
			$res = $this->run("Page.ApplyTemplateAction",array("pageId"=>$this->id));

			if($res->success()){
			}else{
			}
		}

		echo "<html>";
		echo "<script type=\"text/javascript\">window.parent.location.href='".SOY2PageController::createLink("Page.Detail.".$this->id)."';</script>";
		echo "</html>";

	}

	function __construct($arg) {
		$id = @$arg[0];
		$this->id = $id;
		if(is_null($id)){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}

		parent::__construct();
		$this->createAdd("main_form","HTMLForm");

		$res = $this->run("Template.TemplateListAction");
		$templates = $res->getAttribute("list");

		$res = $this->run("Page.DetailAction",array("id"=>$id));
		if(!$res->success()){
			echo CMSMessageManager::get("SOYCMS_ERROR");
			exit;
		}
		$page = $res->getAttribute("Page");
		$this->page = $page;

		$this->createAdd("normal_template_select","HTMLLabel",array(
			"html" => $this->buildTemplateList(),
			"name" => "template",
			"visible"=>($page->getPageType() == Page::PAGE_TYPE_NORMAL)
		));
		$this->createAdd("blog_template_select","HTMLLabel",array(
			"html" => $this->buildBlogTemplateList(),
			"name" => "template",
			"visible"=>($page->getPageType() == Page::PAGE_TYPE_BLOG)
		));
	}

	function buildTemplateList(){
		$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
		$templates = $logic->getByPageType(Page::PAGE_TYPE_NORMAL);
		$html = array();
		$html[] = '<option value="">'.CMSMessageManager::get("SOYCMS_ASK_TO_CHOOSE_PAGE_TEMPLATE_PACK").'</option>';
		foreach($templates as $template){
			if(!$template->isActive())continue;

			$html[] = '<optgroup label="'.$template->getName().'">';

			$temps = $template->getTemplate();
			if(count($temps)){
				foreach($temps as $id => $array){
					$html[] = '<option value="'.$template->getId()."/". $id .'">' . $array["name"] . '</option>';
				}
			}


			$html[] = "</optgroup>";
		}

		return implode("\n",$html);
	}

	function buildBlogTemplateList(){
		$logic = SOY2Logic::createInstance("logic.site.Template.TemplateLogic");
		$templates = $logic->getByPageType(Page::PAGE_TYPE_BLOG);
		$html = array();
		$html[] = '<option value="">'.CMSMessageManager::get("SOYCMS_ASK_TO_CHOOSE_PAGE_TEMPLATE_PACK").'</option>';
		foreach($templates as $template){
			if(!$template->isActive())continue;
			$html[] = '<option value="'.$template->getId().'">' . $template->getName() . '</option>';
		}

		return implode("\n",$html);
	}

	function getTemplateList(){
		$result = SOY2ActionFactory::createInstance("Template.TemplateListAction")->run();

		$list = $result->getAttribute("list");

		return $list;
	}
}
