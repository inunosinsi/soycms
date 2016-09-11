<?php

class CMSPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "cms";

	function execute(){
		$soyValue = $this->soyValue;
		
		$array = explode(".", $soyValue);
		if(count($array) > 1){
			unset($array[0]);
		}
		$func = "soycms_" . implode("_", $array);
		
		//ダイナミック編集のためにここで定義を確認しておく
		if(!defined("_SITE_ROOT_")) define("_SITE_ROOT_", UserInfoUtil::getSiteDirectory());
		$modulePath = soy2_realpath(_SITE_ROOT_) . ".module/" . str_replace(".", "/", $soyValue) . ".php";
		
		$this->setInnerHTML('<?php ob_start(); ' .
						'if(file_exists("' . $modulePath . '")){include_once("' . $modulePath . '");} ?>' .
						$this->getInnerHTML() . '' .
						'<?php $tmp_html=ob_get_contents();ob_end_clean(); ' .
						'if(function_exists("' . $func . '")){echo call_user_func("' . $func . '",$tmp_html,$this);}else{ echo "function not found : ' . $func . '";} ?>');
	}
}
?>