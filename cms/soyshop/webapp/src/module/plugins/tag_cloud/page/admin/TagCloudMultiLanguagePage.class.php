<?php

class TagCloudMultiLanguagePage extends WebPage {

	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionaryDAO");
		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudLanguageDAO");
		SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");	//多言語化
	}

	function doPost(){
		if(soy2_check_token()){
			if(count($_POST["Lang"])){
				$langDao = SOY2DAOFactory::create("SOYShop_TagCloudLanguageDAO");
				foreach($_POST["Lang"] as $lang => $words){
					foreach($words as $wordId => $word){
						$word = trim($word);
						if(!strlen($word)){
							try{
								$langDao->deleteByWordIdAndLang((int)$wordId, $lang);
							}catch(Exception $e){
								//
							}
						}else{
							$obj = new SOYShop_TagCloudLanguage();
							$obj->setLang($lang);
							$obj->setWordId($wordId);
							$obj->setLabel($word);
							
							try{
								$langDao->insert($obj);
							}catch(Exception $e){
								try{
									$langDao->update($obj);
								}catch(Exception $e){
									//
								}
							}
						}
						
					}
				}
			}
			if($this->itemId > 0){
				SOY2PageController::jump("Extension.tag_cloud.".$this->itemId."?updated");
			}else{
				SOY2PageController::jump("Extension.tag_cloud?updated");
			}
			
		}
		if($this->itemId > 0){
			SOY2PageController::jump("Extension.tag_cloud.".$this->itemId."?failed");
		}else{
			SOY2PageController::jump("Extension.tag_cloud?failed");
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$item = soyshop_get_item_object($this->itemId);
		DisplayPlugin::toggle("show_item_detail_link", is_numeric($item->getId()));
		$this->addLink("item_detail_link", array(
			"text" => $item->getName() . "の編集画面に戻る",
			"link" => SOY2PageController::createLink("Item.Detail.".$item->getId())
		));

		$this->addForm("form");

		$this->addLabel("config_table", array(
			"html" => self::_buildConfigTable()
		));
	}

	private function _buildConfigTable(){
		$langs = UtilMultiLanguageUtil::allowLanguages();
		if(!count($langs)) return "";

		if($this->itemId > 0){
			$words = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO")->getWordListByItemId($this->itemId);
		}else{
			$words = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO")->getWordList();
		}
		
		if(!count($words)) return array();

		$translatedList = SOY2DAOFactory::create("SOYShop_TagCloudLanguageDAO")->getTranslatedWordList();
		
		$h = array();
		$h[] = "<div class=\"table-responsive\">";
		$h[] = "	<table class=\"table table-striped\">";
		$h[] = "		<thead>";
		$h[] = "			<tr>";
		$h[] = "				<th>&nbsp;</th>";
		foreach($langs as $lang => $label){
			if($lang == "jp") continue;
			$h[] = "				<th>".$label."(".$lang.")</th>";
		}
		$h[] = "			</tr>";
		$h[] = "		</thead>";

		$h[] = "		<tbody>";
		$langs = array_keys($langs);
		foreach($words as $wordId => $word){
			$h[] = "			<tr>";
			$h[] = "			<td>".$word."</td>";
			foreach($langs as $lang){
				if($lang == "jp") continue;
				$t = (isset($translatedList[$lang][$wordId])) ? $translatedList[$lang][$wordId] : "";
				$h[] = "			<td class=\"form-inline\"><input type=\"text\" class=\"form-control\" name=\"Lang[".$lang."][".$wordId."]\" value=\"".$t."\"></td>";
			}
			$h[] = "			</tr>";
		}	
		$h[] = "		</tbody>";

		$h[] = "	</table>";
		$h[] = "</div>";
		return implode("\n", $h);
	}

	function setItemId(int $itemId){
		$this->itemId = $itemId;
	}
}