<?php
/*
 */
class TagCloudItemCustomField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){

		//登録されているタグを一旦削除
		SOY2::imports("module.plugins.tag_cloud.domain.*");
		$linkDao = SOY2DAOFactory::create("SOYShop_TagCloudLinkingDAO");
		try{
			$linkDao->deleteByItemId($item->getId());
		}catch(Exception $e){
			//
		}

		if(isset($_POST["TagCloudPlugin"]["tag"]) && strlen($_POST["TagCloudPlugin"]["tag"])){
			$tagStr = str_replace("、", ",", trim($_POST["TagCloudPlugin"]["tag"]));
			$tags = explode(",", $tagStr);
			if(count($tags)){
				$dicDao = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO");
				foreach($tags as $tag){
					$tag = trim($tag);
					try{
						$tagObj = $dicDao->getByWord($tag);
						$wordId = $tagObj->getId();
					}catch(Exception $e){
						$tagObj = new SOYShop_TagCloudDictionary();
						$tagObj->setWord($tag);
						try{
							$wordId = $dicDao->insert($tagObj);
						}catch(Exception $e){
							$wordId = null;
						}
					}

					if(isset($wordId)){
						$linkObj = new SOYShop_TagCloudLinking();
						$linkObj->setItemId($item->getId());
						$linkObj->setWordId($wordId);
						try{
							$linkDao->insert($linkObj);
						}catch(Exception $e){
							//
						}
					}
				}
			}
		}
	}

	function getForm(SOYShop_Item $item){
		$html = array();
		$html[] = "<div class=\"alert alert-success\">タグクラウドの登録</div>";
		SOY2::import("module.plugins.tag_cloud.component.TagCloudCustomFieldForm");
		$html[] = TagCloudCustomFieldForm::buildForm($item->getId());
		$html[] = "<div class=\"alert alert-success\">タグクラウドの登録ここまで</div>";
		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		$tags = (is_numeric($item->getId())) ? TagCloudUtil::getRegisterdTagsByItemId($item->getId()) : array();
		
		$cnt = count($tags);

		// 多言語化
		if($cnt > 0 && defined("SOYSHOP_PUBLISH_LANGUAGE") && SOYSHOP_PUBLISH_LANGUAGE != "jp"){
			$tags = SOY2Logic::createInstance("module.plugins.tag_cloud.logic.MultilingualLogic")->translateOnCustomField($tags);
		}

		$htmlObj->addModel("no_tag_cloud", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($cnt === 0)
		));

		$htmlObj->addModel("is_tag_cloud", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($cnt > 0)
		));

		SOY2::import("module.plugins.tag_cloud.component.TagCloudTagListComponent");
		$htmlObj->createAdd("tag_cloud_tag_list", "TagCloudTagListComponent", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"list" =>  $tags,
			"url" => ($cnt > 0) ? TagCloudUtil::getPageUrlSettedTagCloud() : ""
		));
	}

	function onDelete(int $itemId){}
}

SOYShopPlugin::extension("soyshop.item.customfield", "tag_cloud", "TagCloudItemCustomField");
