<?php
class ConvertImageFileFormatConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.convert_image_file_format.config.ImgFmtPage");
		$form = SOY2HTMLFactory::createInstance("ImgFmtPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "画像フォーマット変換プラグインの設定";
	}

}
SOYShopPlugin::extension("soyshop.config", "convert_image_file_format", "ConvertImageFileFormatConfig");
