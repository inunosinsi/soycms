<?php
class CommonOrderConfirmCheck extends SOYShopOrderConfirmBase{

	/**
	 * 確認用のチェックボタンをチェックしたか？
	 * @param 0 or 1
	 * @return boolean エラーがあった場合はtrueを返す
	 */
	function hasError($param){
		//エラーの場合は$paramの中は0になる
		return ($param == 0) ? true : false;
	}

	//確認HTML表示用のメソッド
	function display(){
		include_once(dirname(__FILE__) . "/class/common.php");
		$config = CommonOrderConfirmCheckCommon::getConfig();
		
		$html = array();
		$html[] = $this->buildCheckBox();
		$html[] = nl2br(htmlspecialchars($config["text"], ENT_QUOTES, "UTF-8"));
		
		return implode("\n", $html);
	}
	
	//エラーメッセージ表示用のメソッド
	function error($error){
		if($error===true){
			include_once(dirname(__FILE__) . "/class/common.php");
			$config = CommonOrderConfirmCheckCommon::getConfig();
			$html = (isset($config["error"]) && strlen($config["error"])) ? $config["error"] : "";
		}else{
			$html = "";
		}
		
		return $html;
	}
	
	function buildCheckBox(){
		$html = '<input type="hidden" name="order_confirm_module[common_order_confirm_check]" value="0">';
		$html .= '<input type="checkbox" name="order_confirm_module[common_order_confirm_check]" value="1">';
		
		return $html;
	}
}
SOYShopPlugin::extension("soyshop.order.confirm","common_order_confirm_check","CommonOrderConfirmCheck");
?>