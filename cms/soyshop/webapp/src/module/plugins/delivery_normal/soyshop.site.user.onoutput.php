<?php

class DeliveryNormalOnOutput extends SOYShopSiteUserOnOutputAction{

	/**
	 * @return string
	 */
	function onOutput($html){

		//カートページのみ動作します。
		$res = strpos($_SERVER["PATH_INFO"], "/" . soyshop_get_cart_uri());
		if($res === false || $res > 0) return $html;

		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		$config = DeliveryNormalUtil::getDeliveryDateConfig();
		if(!isset($config["use_format_calendar"]) || $config["use_format_calendar"] != 1) return $html;

		//念の為、配送方法のお届け日指定フォームが表示されているか？
		if(!strpos($html, "id=\"jquery-ui-calendar\"")) return $html;

		//jQueryとjQueryUIを読み込んでいるか調べる
		preg_match_all('/<script.*src=\".*\/(.*.*j.*query.*)\.js\"><\/script>/', $html, $out);
		$isReadedJquery = false;
		$isReadedJqueryUi = false;

		if(count($out[1])){
			$isReadedJquery = true;
			foreach($out[1] as $v){
				if(strpos($v, "-ui.")) {
					$isReadedJqueryUi = true;
					break;
				}
			}
		}

		$tags = [];
		if(!$isReadedJquery) $tags[] = "<script src=\"/" . SOYSHOP_ID . "/themes/common/js/jquery.min.js\"></script>";
		if(!$isReadedJqueryUi) {
			$tags[] = "<link href=\"/" . SOYSHOP_ID . "/themes/common/css/jquery-ui.min.css\" rel=\"stylesheet\" type=\"text/css\">";
			$tags[] = "<script src=\"/" . SOYSHOP_ID . "/themes/common/js/jquery-ui.min.js\"></script>";

			//datepicker
			if(!preg_match('/<script.*src=\".*\/(.*.*datepicker.*)\.js\"><\/script>/', $html, $out)){
				$tags[] = "<script src=\"/" . SOYSHOP_ID . "/themes/common/js/datepicker-ja.js\"></script>";
			}
		}

		$tags[] = "<script>\n" . self::buildCalendarScript($config) .  "\n</script>";
		$tags[] = "<style>#date_remove{display:none;}</style>";

		if(count($tags)){
			$html = str_replace("</body>", implode("\n", $tags) . "\n</body>", $html);
		}

		return $html;
	}

	private function buildCalendarScript($config){
		$script = array();
		$script[] = "$(function(){";

		//
		$script[] = "	if($(\"#jquery-ui-calendar\").val().length > 0){";
		$script[] = "		$(\"#date_remove\").css(\"display\", \"inline\");";
		$script[] = "	} else {";
		$script[] = "		$(\"#date_remove\").css(\"display\", \"none\");";
		$script[] = "	}";

		$script[] = "	$(\"#jquery-ui-calendar\").datepicker({";
		$script[] = "		minDate: '+" . ($config["delivery_shortest_date"] + 1) . "d',";
		$script[] = "		maxDate: '+" . ($config["delivery_shortest_date"] + 1 + $config["delivery_date_period"]) . "d',";
		$script[] = "		dateFormat: '" . SOY2Logic::createInstance("module.plugins.delivery_normal.logic.DeliveryDateFormatLogic")->getDateFormat() . "'";
		$script[] = "	});";

		$script[] = "	$(\"#jquery-ui-calendar\").change(function(){";
		$script[] = "		$(\"#date_remove\").css(\"display\", \"inline\");";
		$script[] = "	})";

		$script[] = "	$(\"#date_remove\").click(function(){";
		$script[] = "		$(\"#jquery-ui-calendar\").val(\"\");";
		$script[] = "		$(this).css(\"display\", \"none\");";
		$script[] = "	});";

		$script[] = "});";

		return implode("\n", $script);
	}
}

SOYShopPlugin::extension("soyshop.site.user.onoutput", "delivery_normal", "DeliveryNormalOnOutput");
