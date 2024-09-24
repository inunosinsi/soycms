<?php
/**
 * @return string
 */
function checker_fn_get_blog_template_mode(){
	$args = SOY2PageController::getArguments();
	return  (isset($args[1])) ? $args[1] : "top";
}
/**
 * @param Page|BlogPage
 * @return string
 */
function checker_fn_get_template(Page $page){
	if($page instanceof BlogPage){
		switch(checker_fn_get_blog_template_mode()){
			case "top":
				return $page->getTopTemplate();
			case "archive":
				return $page->getArchiveTemplate();
			case "entry":
				return $page->getEntryTemplate();
		}
	}else if($page instanceof Page){
		return $page->getTemplate();
	}

	return null;
}

/**
 * @param int
 * @return string
 */
function checker_fn_build_cache_file_path(int $pageId, string $dirName){
	$dir = UserInfoUtil::getSiteDirectory() . ".cache/".$dirName."/";
	if(!file_exists($dir)) mkdir($dir);

	$path = $dir.(string)$pageId;
	$mode = checker_fn_get_blog_template_mode();
	if(strlen($mode)) $path .= "_".$mode;
	return $path.".txt";
}

/**
	 * コメントのある行のみを集める
	 * @param string
	 * @return string
	 */
function checker_fn_shape_template(string $temp){
	$h = array();
	$lines = explode("\n", $temp);
	$n = count($lines);
	for($i = 0; $i < $n; $i++){
		$line = $lines[$i];
		preg_match('/<!--.*?/', $line, $tmp);
		if(!count($tmp)) continue;
		
		// 閉じコメントがない場合は次の行を連結する
		if(is_bool(strpos($line, "-->"))){
			for(;;){
				if($i > $n) return "";	// 最後の行までコメントの閉じがない場合は強制終了
				$i++;
				$line .= " ".$lines[$i];
				if(is_numeric(strpos($line, "-->"))) break;
			}
		}

		//コメントが複数行あるかもしれないので、その対応
		$line = str_replace("<!--", "\n<!--", $line);
		$arr = explode("\n", $line);
		foreach($arr as $v){
			$v = trim($v);
			if(!strlen($v)) continue;
			$h[] = $v;
		}
	}
	$c = count($h);
	if($c === 0) return "";

	// cms:ignoreをcms:ignore="dummy"に変換して、cms:ignoreも調査対象にする
	for($i = 0; $i < $c; $i++){
		if(is_numeric(strpos($h[$i], "cms:ignore")) && is_bool(strpos($h[$i], "cms:ignore=\"dummy\""))){
			$h[$i] = str_replace("cms:ignore", "cms:ignore=\"dummy\"", $h[$i]);
		}
	}

	return implode("\n", $h);
}

/**
 * @param array
 */
function checker_fn_debug_dump_array(array $a){
	$arr = array();
	if(count($a)){
		foreach($a as $t){
			$arr[] = htmlspecialchars($t, ENT_QUOTES, "UTF-8");
		}
	}
	var_dump($arr);
}

/**
 * @param string
 */
function checker_fn_debug_dump_string(string $s){
	var_dump(htmlspecialchars($s, ENT_QUOTES, "UTF-8"));
}