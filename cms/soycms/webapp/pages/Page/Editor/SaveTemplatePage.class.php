<?php

class SaveTemplatePage extends CMSWebPageBase {

	var $id;

	function doPost(){
		if(soy2_check_token()){

			$result = $this->run("Page.SaveTemplateAction",array(
				"id" => $this->id
			));

			if($result->success()){
				$webPage = SOY2HTMLFactory::createInstance("Block.BlockListPage",array(
					"pageId" => $this->id[0]
				));

				//BlockListPageはコンポーネントなので
				$webPage->execute();
				$html = $webPage->getObject();

				echo json_encode(array("soy2_token"=>soy2_get_token(), "text"=>$html));
			}else{
				echo json_encode(array("soy2_token"=>soy2_get_token(), "text"=>"0"));;
			}
		}else{
			echo json_encode(array("soy2_token"=>soy2_get_token(), "text"=>"0"));;
		}

		exit;

	}

	function __construct($args) {
		$this->id = $args;
		parent::__construct();
		exit;
	}
}
