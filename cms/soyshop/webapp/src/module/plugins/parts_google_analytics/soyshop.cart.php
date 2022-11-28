<?php
class GoogleAnalyticsCart extends SOYShopCartBase{

	function displayPage01(CartLogic $cart){

	}

	function displayPage02(CartLogic $cart){
		return $this->buildScript("step1");
	}

	function displayPage03(CartLogic $cart){
		return $this->buildScript("step2");
	}

	function displayPage04(CartLogic $cart){
		return $this->buildScript("step3-confirm");
	}

	function displayPage05(CartLogic $cart){
		return $this->buildScript("step3-credit");
	}

	function displayCompletePage(CartLogic $cart){
		return $this->buildScript("complete");
	}

	function buildScript($step){
		$html = array();

		$url = soyshop_get_cart_url() . "/" . $step."/";

		$html[] = "<script type=\"text/javascript\">";
		$html[] = "if(_gaq){";
		$html[] = "_gaq.push(['_trackPageview', '".htmlspecialchars($url, ENT_QUOTES, "UTF-8")."']);";
		$html[] = "}else if(pageTracker){";
		$html[] = "try { pageTracker._trackPageview('".htmlspecialchars($url, ENT_QUOTES, "UTF-8")."'); } catch(err) {}";
		$html[] = "}";
		$html[] = "</script>";

		return implode("\n", $html);
	}
}

SOYShopPlugin::extension("soyshop.cart","parts_google_analytics","GoogleAnalyticsCart");