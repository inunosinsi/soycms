<?php

class DetailPage extends CMSWebPageBase {

	var $id;
	var $pageId;

	function doPost(){
		//更新処理

		if(soy2_check_token()){

			try{
				$result = $this->run("Block.UpdateAction",array("id"=>$this->id));

				$block = $result->getAttribute("Block");

				if(!$result->success()){
					//TODO ブロックの更新失敗エラー処理
				}

				if(isset($_POST["after_submit"]) && $_POST["after_submit"] == "reload"){
					$this->jump("Block.Detail.".$this->id);
					exit;
				}

				header("Content-Type: text/html; charset=utf-8;");
				echo '<html><head>';
				echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8" />';
				echo '<script type="text/javascript" src="'.htmlspecialchars(SOY2PageController::createRelativeLink("./webapp/pages/files/vendor/jquery/jquery.min.js")."?".SOYCMS_BUILD_TIME,ENT_QUOTES,"UTF-8").'"></script>';
				echo "<script type=\"text/javascript\">";
				if($block){
					echo '$("#block_info_'.htmlspecialchars($this->id, ENT_QUOTES, "UTF-8").'", parent.document).html("'.htmlspecialchars($block->getObjectInstance()->getInfoPage(), ENT_QUOTES, "UTF-8").'");';
					echo '$("#main_form [name=soy2_token]", parent.document).val("'.soy2_get_token().'");';
					echo '$.each($(".block_action_link", parent.document), function(i, obj){ $(obj).attr("href", $(obj).attr("href").replace(/soy2_token=[0-9A-z]*/,"soy2_token='.soy2_get_token().'")); });';
				}
				echo "window.parent.common_close_layer(window.parent);";
				echo "</script>";
				echo "</head><body></body></html>";

				exit;

			}catch(Exception $e){
				error_log(var_export($e,true));
			}
		}

		$this->jump("Block.Detail.".$this->id);

	}

	function __construct($args) {
		$id = $args[0];
		$this->id = $id;
		$block = $this->getBlock($id);
		$this->pageId = $block->getPageId();

		parent::__construct();

		$component = $block->getBlockComponent();
		//Block ID will be required in some cases.
		if(method_exists($component,"setBlockId")){
			$component->setBlockId($id);
		}else{
			$component->blockId = $id;
		}
		$this->add("block_form",$component->getFormPage());

		$this->createAdd("block_id","HTMLLabel",array(
			"text" => "ID: ".$block->getSoyId()
		));
		$this->createAdd("block_name","HTMLLabel",array(
			"text" => $component->getComponentName()
		));
		$this->createAdd("block_description","HTMLLabel",array(
			"html" => $component->getComponentDescription()
		));
	}

	/**
	 * Get Block information
	 * @param $id Block ID
	 * @return Block
	 */
	private function getBlock($id){
		$result = $this->run("Block.DetailAction",array("id"=>$id));
		return $result->getAttribute("Block");
	}
}
