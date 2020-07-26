<?php

class IconFieldComponent {

	public static function buildForm($entryId, $iconDir, $label, $labels=array()){
		$icons = self::_get($entryId);

		$files = @scandir(UserInfoUtil::getSiteDirectory() . $iconDir);
		if(!$files) $files = array();

		$html = array();

		$html[] = '<div class="form-group" id="custom_icon_field_area">';
		$html[] = '<label>' . htmlspecialchars($label) . '</label>';

		$icons_array = explode(",", $icons);
		$html []= '<div id="custom_icon_field_current">';
		foreach($icons_array as $str){
			$str = str_replace(CMSUtil::getSiteUrl(), "", $str);
			if(strlen($str)){
				$tmpStr = str_replace($iconDir, "", $str);
				$html[] = '<img id="custom_icon_field_hidden_' . str_replace(".", "_", substr($tmpStr, strrpos("/", $tmpStr) + 1)) . '" src="' . htmlspecialchars(UserInfoUtil::getSiteURL() . $str, ENT_QUOTES) . '" />';
			}
		}
		$html[] = '</div>';

		$html[] = '<input type="hidden" name="custom_icon_field" id="custom_icon_field_hidden" value="' . htmlspecialchars($icons, ENT_QUOTES) . '">';
		$html[] = '<div id="custom_icon_field_icon_list" style="">';
		foreach($files as $file){
			if($file[0] == ".") continue;
			$html[] = '<img onclick="add_custom_icon_field(this.src);" src="' . htmlspecialchars(UserInfoUtil::getSiteURL() . $iconDir . "/" . $file, ENT_QUOTES) . '" />';
		}
		$html[] = '</div>';
		$html[] = '</div>';

		//ここからJS
		$html[] = '<script type="text/javascript">';

		//ラベルの設定がある場合は一旦非表示にする
		if(count($labels)){
			$html[] = "$(\"#custom_icon_field_area\").css(\"display\", \"none\");";
			$html[] = "var iconfield_labels = [" . implode(",", $labels) . "];";
		}else{
			$html[] = "var iconfield_labels = [];";
		}


		$html[] = str_replace("@@SITE_URL@@", UserInfoUtil::getSiteURL(), file_get_contents(dirname(dirname(__FILE__)) . "/js/script.js"));
		$html[] = '</script>';

		return implode("\n", $html);
	}

	private static function _get($entryId){
		$dao = new SOY2DAO();

		try{
			$result = $dao->executeQuery("select custom_icon_field from Entry where id = :id", array(":id" => $entryId));
		}catch(Exception $e){
			$result = array();
		}

		return (isset($result[0]["custom_icon_field"])) ? $result[0]["custom_icon_field"] : null;
	}
}
