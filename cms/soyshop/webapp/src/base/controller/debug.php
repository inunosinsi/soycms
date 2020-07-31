<?php
/**
 * タイマーに記録する（デバッグモードのみ）
 * @param String $label
 */
function count_timer($label){
	if(DEBUG_MODE){
		$GLOBALS["debug_timer"][$label] = microtime(true);
		if(!isset($GLOBALS["debug_timer_start_time"])){
			$GLOBALS["debug_timer_start_time"] = $GLOBALS["debug_timer"][$label];
		}
	}
}

/**
 * デバッグ情報をHTMLの末尾に付け足す（デバッグモードのみ）
 * @param WebPage $webPage
 */
function append_debug_info($webPage){
	if(DEBUG_MODE){
		if(isset($GLOBALS["debug_timer"]) && is_array($GLOBALS["debug_timer"]) && count($GLOBALS["debug_timer"])){
			$debugInfo = "";

			$previous = null;
			foreach($GLOBALS["debug_timer"] as $label => $time){
				if(!$previous){
					$previous = $time;
					continue;
				}
				$debugInfo .= "<p>".$label.": " . ($time - $previous) . " 秒</p>";
				$previous = $time;
			}
			$debugInfo .= "<p><b>Total: " . ($previous - $GLOBALS["debug_timer_start_time"]) . " 秒</b></p>";
			$debugInfo .= "<p>Render: ##########RENDER_TIME######### 秒</p>";

			$ele = $webPage->getBodyElement();
			$ele->appendHTML($debugInfo);
		}
	}
}

/**
 * レンダリング時間を置換する
 * @param String $html (リファレンス渡し)
 */
function replace_render_time(&$html){
	if(DEBUG_MODE){
		if(isset($GLOBALS["debug_timer"]) && is_array($GLOBALS["debug_timer"]) && count($GLOBALS["debug_timer"])){
			$html = str_replace("##########RENDER_TIME#########", $GLOBALS["debug_timer"]["Render"] - $GLOBALS["debug_timer"]["Main"], $html);
		}
	}
}
