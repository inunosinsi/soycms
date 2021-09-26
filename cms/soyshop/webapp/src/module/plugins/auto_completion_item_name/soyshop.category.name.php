<?php
/*
 */
class AutoCompletionCategoryName extends SOYShopCategoryNameBase{

	function getForm(SOYShop_Category $category){
		if(!is_numeric($category->getId())) return "";

		SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
		$cnf = AutoCompletionUtil::getConfig();
		if(!isset($cnf["include_category"]) || (int)$cnf["include_category"] !== AutoCompletionUtil::INCLUDE_CATEGORY) return "";

		$readings = SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.AutoCompleteDictionaryLogic")->getReadingsByCategoryId($category->getId());

		$html = array();

		//読み方
		foreach(AutoCompletionUtil::getItemTypes() as $t => $label){
			$html[] = "<div class=\"form-group\">";
			$html[] = "<label>読み方(" . $label . ")</label>";
			$html[] = "<input type=\"text\" name=\"AutoCompletion[" . $t . "]\" class=\"form-control\" value=\"" . $readings[$t] . "\">";
			$html[] = "</div>";
		}

		return implode("\n", $html);
	}

	function doPost(SOYShop_Category $category){
		SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
		$cnf = AutoCompletionUtil::getConfig();
		if(!isset($cnf["include_category"]) || (int)$cnf["include_category"] !== AutoCompletionUtil::INCLUDE_CATEGORY);

		if(isset($_POST["AutoCompletion"])){
			$arr = array();
			foreach(AutoCompletionUtil::getItemTypes() as $t => $label){
				$arr[$t] = (isset($_POST["AutoCompletion"][$t])) ? $_POST["AutoCompletion"][$t] : "";
			}

			SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.AutoCompleteDictionaryLogic")->saveCategoryReadings($category->getId(), $arr);
		}
	}
}
SOYShopPlugin::extension("soyshop.category.name", "auto_completion_category_name", "AutoCompletionCategoryName");
