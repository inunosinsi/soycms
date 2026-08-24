<?php

class LabelAccordionUtil {

	const LABEL_ACCORDION_CONFIG_DIR = ".label_accordion";
	const LABEL_ACCORDION_CONFIG_FILE_NAKE = "labels";

	public static function read(){
		$f = self::configDirectory().self::LABEL_ACCORDION_CONFIG_FILE_NAKE;
		if(!file_exists($f)) return array();

		$_arr = explode(",", file_get_contents($f));
		$labels = array();
		foreach($_arr as $_lbl){
			$_lbl = trim($_lbl);
			if(!strlen($_lbl)) continue;
			$labels[] = self::t($_lbl);
		}

		return $labels;
	}

	/**
	 *
	 */
	public static function save(string $label, bool $isChecked){
		$label = self::t($label);
		$labels = self::read();
		if($isChecked){	// チェックを入れた時
			if(!is_numeric(array_search($label, $labels))){
				$labels[] = $label;
			}
		}else{	//チェックを外した時
			$_tmp = array();
			if(count($labels)){
				foreach($labels as $lbl){
					if($label == $lbl) continue;
					$_tmp[] = $lbl;
				}
			}
			$labels = $_tmp;
		}

		file_put_contents(self::configDirectory().self::LABEL_ACCORDION_CONFIG_FILE_NAKE, implode(",", $labels));
	}

	private static function t(string $str){
		return trim(htmlspecialchars(strip_tags($str), ENT_QUOTES, "UTF-8"));
	}

	private static function configDirectory(){
		$dir = UserInfoUtil::getSiteDirectory().self::LABEL_ACCORDION_CONFIG_DIR."/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}
}
