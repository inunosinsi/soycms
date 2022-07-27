<?php

class ThumbnailLogic extends SOY2LogicBase {

    /**
     * @param array, int, string
     * @return string
     */
    function buildPathListTable(array $items, int $formId, string $columnId){
        if(!count($items)) return "";
		$dir = self::getThumbnailDirectory($formId, $columnId);
		$srcDir = str_replace($_SERVER["DOCUMENT_ROOT"], "", $dir);

		$html = array();
		$html[] = "<table class=\"table\">";
		$html[] = "<thead>";
		$html[] = "<tr>";
		$html[] = "<th>項目名</th>";
		$html[] = "<th style=\"width:90%;\">画像ファイルのパス</th>";
		$html[] = "</tr>";
		$html[] = "</thead>";
		$html[] = "<tbody>";
		foreach($items as $idx => $item){
			$item = trim($item);
			$filename = self::_createHash($idx, $item);
			$html[] = "<tr>";
			$html[] = "<td nowrap>" . htmlspecialchars($item, ENT_QUOTES, "UTF-8") . "</td>";
			$html[] = "<td>";
			$html[] = $dir . "<strong>" . $filename . "</strong>.jpg";
			if(file_exists($dir . $filename . ".jpg")){
				$html[] = "<a href=\"" . $srcDir . $filename . ".jpg\" class=\"btn btn-primary btn-xs\" target=\"_blank\" rel=\"noopener\">アップロード済み</a>";
			}
			$html[] = "</td>";
			$html[] = "</tr>";
		}
		$html[] = "</tbody>";
		$html[] = "</table>";
		return implode("\n", $html);
    }

	/**
     * @param int, string
     * @return string
     */
    function getThumbnailDirectory(int $formId, string $columnId){
        return self::_thumbDir($formId, $columnId);
    }

	/**
	 * @param int, string, int, string
	 * @return string
	 */
	function getThumbnailFilePath(int $formId, string $columnId, int $idx, string $itemname){
		return self::getThumbnailDirectory($formId, $columnId) . self::_createHash($idx, $itemname) . ".jpg";
	}

    /**
     * @param int, string
     * @return string
     */
    private function _thumbDir(int $formId, string $columnId){
        $dir = rtrim($_SERVER["DOCUMENT_ROOT"], "/") . "/inquiry_images/";
		if(!file_exists($dir)) mkdir($dir);
		if(!file_exists($dir . ".gitignore")) file_put_contents($dir . ".gitignore", "*");
		
		// 下記は必ず取得できなければならない
		$dir .= SOY2DAOFactory::create("SOYInquiry_FormDAO")->getById($formId)->getFormId() . "/";
		if(!file_exists($dir)) mkdir($dir);

		$dir .= $columnId . "/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
    }

	/**
	 * @param int, string
	 * @return string
	 */
    private function _createHash(int $idx, string $item){
		return md5((string)$idx . $item);
    }
}