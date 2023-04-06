<?php
CmsTagChecker::registerPlugin();

class CmsTagChecker{

	const PLUGIN_ID = "cms_tag_checker";
	const DIRECTORY_NAME = "cmstag_checker";

	private $tags = array(
		"soy:id",
		"b_block:id",
		"m_block;id",
		"block:id",
		"cms:id",
		"app:id",
		"csf:id",
		"cms:module",
		"soy:ignore",
		"cms:ignore"
	);

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=> "CMSタグチェックプラグイン",
			"type" => Plugin::TYPE_PAGE,
			"description"=> "CMSタグの閉じ忘れ等を調べます",
			"author"=> "齋藤毅",
			"url" => "https://saitodev.co/article/5056",
			"mail" => "tsuyoshi@saitodev.co",
			"version"=>"0.1"
		));

		if(!defined("_SITE_ROOT_")){
			CMSPlugin::setEvent('onPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
			CMSPlugin::setEvent('onBlogPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));

			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Page.Notice", array($this, "onPageNotice"));
			CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Notice", array($this, "onBlogNotice"));
		}
	}

	function onPageUpdate($args){
		$template = null;
		$mode = "";
		$page = &$args["new_page"];		
		if($page instanceof BlogPage){
			$mode = self::_blogTemplateMode();
			switch($mode){
				case "top":
					$template = $page->getTopTemplate();
					break;
				case "archive":
					$template = $page->getArchiveTemplate();
					break;
				case "entry":
					$template = $page->getEntryTemplate();
					break;
			}
		}else if($page instanceof Page){
			$template = $page->getTemplate();
		}

		// キャッシュの削除
		$cacheFilePath = self::_buildCacheFilePath((int)$page->getId(), $mode);
		if(file_exists($cacheFilePath)) unlink($cacheFilePath);

		if(is_null($template)) return;

		$template = self::_shapeTemplate($template);
		if(!strlen($template)) return;

		
		$errs = array();

		foreach($this->tags as $tagType){
			preg_match_all('/<!--.*'.$tagType.'=\"(.*?)\".*-->/', $template, $tmps);
			
			if(isset($tmps[1]) && count($tmps[1])){
				$tmps[1] = array_unique($tmps[1]);
				
				foreach($tmps[1] as $tagName){
					// 二段階チェック
					preg_match_all('/<!-- .*'.$tagType.'.*-->/', $template, $soytags);
					
					$cnt = count($soytags[0]);
					if($cnt === 0) continue;

					// cms:idタグを分割する 1行に二個のcms:idタグがある場合がある
					$tagArr = self::_shapeTagList($tagType, $tagName, $soytags[0]);
					$tagCnt = count($tagArr);
					
					$shortTagCnt = 0;
					$pairCnt = 0;	// 開始タグと終了タグのペア数
					$isFirst = true;	// 開始タグを探す
					for($i = 0; $i < $tagCnt; $i++){
						$t = $tagArr[$i];

						// 記述に誤りがないか？を調べる
						preg_match('/<!--.*'.$tagType.'.* -->/', $t, $tmp);
						if(!isset($tmp[0])) preg_match('/<!-- .*'.$tagType.'.* \/-->/', $t, $tmp);	// 省略形の方も調べておく
						if(!isset($tmp[0])) preg_match('/<!-- .*'.$tagType.'=\"'.$tagName.'\" .*-->/', $t, $tmp);	// ブログのテンプレートにあるようなタグ内でコメントがある場合
						if(!isset($tmp[0])) {
							$errs[] = self::_buildErrorMsg($tagType, $tagName, "記述に誤りがあります");
						}else{
							// 最初のタグは開始タグでなければならない　省略形の場合はスルー
							if($isFirst){
								if(is_numeric(strpos($t, "/".$tagType))){	// 左の書き方で終了タグを意味する
									$errs[] = self::_buildErrorMsg($tagType, $tagName, "開始タグがありません");
								}else{	// 開始タグか省略タグが入る
									if(is_numeric(strpos($t, "/-->"))){
										continue;	//省略形の場合はスルー
									}else{
										$isFirst = false;
										if($i >= ($tagCnt-1)){
											$errs[] = self::_buildErrorMsg($tagType, $tagName, "終了タグがありません");
										}
									}
								}
							}else{	// isFirstがfalseの場合は必ず終了タグが来なければならない
								if(is_bool(strpos($t, "/".$tagType))){
									$errs[] = self::_buildErrorMsg($tagType, $tagName, "終了タグがありません");
								}else{
									$pairCnt++;
									$isFirst = true;	// 再び開始タグを探索するように戻す
								}
							}
						}
					}

					// 開始タグ + 終了タグ以外のタグはすべて省略タグでなければならない
					if(($tagCnt - $pairCnt*2) !== $shortTagCnt) self::_buildErrorMsg($tagType, $tagName, "タグの個数に誤りがあります");

					// チェックが終わったら、チェックしたタグを消しておく b_block:idの後にblock:idをチェックする為
					foreach($tagArr as $tt){
						$template = trim(str_replace($tt, "", $template));
					}
				}
			}
		}
		
		if(count($errs)) file_put_contents($cacheFilePath, implode("\n", $errs));
	}

	/**
	 * @return string
	 */
	private function _blogTemplateMode(){
		$args = SOY2PageController::getArguments();
		return  (isset($args[1])) ? $args[1] : "top";
	}

	/**
	 * コメントのある行のみを集める
	 * @param string
	 * @return string
	 */
	private function _shapeTemplate(string $temp){
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
	 * 1行中に<!-- cms:id="create_date" -->hoge<!-- /cms:id="create_date" -->のような書き方があるので分割する
	 * @param string, string, array
	 * @return array
	 */
	private function _shapeTagList(string $tagType, string $tagName, array $tags){
		$arr = array();
		foreach($tags as $tag){
			$tag = trim(str_replace("<!--", "\n<!--", $tag));
			$div = explode("\n", $tag);
			foreach($div as $t){
				// 下記の正規表現でコメントのみにすることと<!-- soy:id="aaa" --><!-- soy:id="bbb" /--><!-- /soy:id="aaa" -->の問題を回避する
				preg_match('/<!--.*'.$tagType.'=\"'.$tagName.'\".*?-->/', $t, $tmp);
				if(!isset($tmp[0])) continue;
				$arr[] = trim($tmp[0]);
			}
		}
		return $arr;
	}

	/**
	 * @param int, string
	 * @return string
	 */
	private function _buildCacheFilePath(int $pageId, string $mode=""){
		$dir = UserInfoUtil::getSiteDirectory() . ".cache/".self::DIRECTORY_NAME."/";
		if(!file_exists($dir)) mkdir($dir);

		$path = $dir.(string)$pageId;
		if(strlen($mode)) $path .= "_".$mode;
		return $path.".txt";
	}

	/**
	 * @param string, string, string
	 * @return string
	 */
	private function _buildErrorMsg(string $tagType, string $tagName, string $content){
		switch($tagType){
			case "cms:ignore":
				return $tagType.":".$content;
			default:
				return $tagType."=\"".$tagName."\":".$content;
		}
	}

	function onPageNotice(){
		$args = SOY2PageController::getArguments();
		if(!isset($args[0]) || !is_numeric($args[0])) return "";
		
		$cacheFilePath = self::_buildCacheFilePath((int)$args[0]);
		if(!file_exists($cacheFilePath)) return "";

		return self::_buildNotice($cacheFilePath);
	}

	function onBlogNotice(){
		$args = SOY2PageController::getArguments();
		if(!isset($args[0]) || !is_numeric($args[0])) return "";
		if(!isset($args[1]) || !is_string($args[1])) return "";
		
		$cacheFilePath = self::_buildCacheFilePath((int)$args[0], $args[1]);
		if(!file_exists($cacheFilePath)) return "";

		return self::_buildNotice($cacheFilePath);
	}

	/**
	 * @param string
	 * @return string
	 */
	private function _buildNotice(string $path){
		$h = array();
		$h[] = "<div class=\"alert alert-danger\">";
		$h[] = "<p>CMSタグの記述に誤りがあります</p>";

		$errs = explode("\n", file_get_contents($path));
		$h[] = "<ul>";
		foreach($errs as $err){
			$h[] = "<li>".$err."</li>";
		}
		$h[] = "</ul>";

		$h[] = "</div>";
		return implode("\n", $h);
	}

	/**
	 * debug
	 */
	private function d(array $a){
		$arr = array();
		if(count($a)){
			foreach($a as $t){
				$arr[] = htmlspecialchars($t, ENT_QUOTES, "UTF-8");
			}
		}
		var_dump($arr);
	}

	/**
	 * debug
	 */
	private function h(string $str){
		var_dump(htmlspecialchars($str, ENT_QUOTES, "UTF-8"));
	}

	/**
	 * プラグインの登録
	 */
	public static function registerPlugin(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new CmsTagChecker();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
