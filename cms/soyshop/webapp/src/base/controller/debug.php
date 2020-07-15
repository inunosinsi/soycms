<?php

/**
 * タイマーに記録する（デバッグモードのみ）
 * @param String $label
 */
function count_timer($label){
	if(DEBUG_MODE){
		$this->timer[$label] = microtime(true);
		if(!$this->startTime){
			$this->startTime = $this->timer[$label];
		}
	}
}

/**
 * デバッグ情報をHTMLの末尾に付け足す（デバッグモードのみ）
 * @param WebPage $webPage
 */
function append_debug_info($webPage){
	if(DEBUG_MODE){
		$debugInfo = "";

		$previous = null;
		foreach($this->timer as $label => $time){
			if(!$previous){
				$previous = $time;
				continue;
			}
			$debugInfo .= "<p>".$label.": " . ($time - $previous) . " 秒</p>";
			$previous = $time;
		}
		$debugInfo .= "<p><b>Total: " . ($previous - $this->startTime) . " 秒</b></p>";
		$debugInfo .= "<p>Render: ##########RENDER_TIME######### 秒</p>";

		$ele = $webPage->getBodyElement();
		$ele->appendHTML($debugInfo);
	}
}

/**
 * レンダリング時間を置換する
 * @param String $html (リファレンス渡し)
 */
function replace_render_time(&$html){
	if(DEBUG_MODE){
		$html = str_replace("##########RENDER_TIME#########", $this->timer["Render"] - $this->timer["Main"], $html);
	}
}
