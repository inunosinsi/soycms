<?php

class SOYShopPageModulePlugin extends PluginBase{

	protected $_soy2_prefix = "shop";

	function execute(){
		$soyValue = $this->soyValue;

		$array = explode(".", $soyValue);
		if(count($array) > 1){
			unset($array[0]);
		}
		$func = "soyshop_" . implode("_", $array);

		$modulePath = SOYSHOP_SITE_DIRECTORY . ".module/" . str_replace(".", "/", $soyValue) . ".php";

		$this->setInnerHTML('<?php ob_start(); ' .
						'if(file_exists("' . $modulePath . '")){include_once("' . $modulePath . '");}else{@SOY2::import("module.site.' . $soyValue . '",".php");} ?>' .
						$this->getInnerHTML() . '' .
						'<?php $tmp_html=ob_get_contents();ob_end_clean(); ' .
						'if(function_exists("' . $func . '")){echo call_user_func("' . $func . '",$tmp_html,$this);}else{ echo "function not found : ' . $func . '";} ?>');
	}
}
?>