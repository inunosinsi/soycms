<?php
EntryPreviewPlugin::register();

class EntryPreviewPlugin{

	const PLUGIN_ID = "soycms_entry_preview";

	private $postfix = "test";


	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"ブログ記事プレビュープラグイン",
			"type" => Plugin::TYPE_ENTRY,
			"description"=>"",
			"author"=>"齋藤毅",
			"url"=>"https://saitodev.co/article/4610",
			"mail"=>"tsuyoshi@saitodev.co",
			"version"=>"0.8"
		));

		CMSPlugin::addPluginConfigPage($this->getId(),array(
			$this,"config_page"
		));

		if(CMSPlugin::activeCheck($this->getId())){
			SOY2::import("site_include.plugin.soycms_entry_preview.util.EntryPreviewUtil");

			if(defined("_SITE_ROOT_")){
                CMSPlugin::setEvent('onEntryGet',$this->getId(),array($this,"onEntryGet"));
            }else{
				CMSPlugin::setEvent('onEntryStateMessage', $this->getId(), array($this, "onEntryStateMessage"));
				CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
				CMSPlugin::addCustomFieldFunction($this->getId(), "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction($this->getId(), "Blog.Entry", array($this, "onCallCustomField_inBlog"));
            }
		}
	}

    function onEntryGet($args){
		$blogLabelId = &$args["blogLabelId"];
		$alias = &$args["alias"];

		preg_match('/^(\d*)_/', $alias, $tmp);
		if(!isset($tmp[1]) || !is_numeric($tmp[1]) || !EntryPreviewUtil::checkPreviewMode($tmp[1])) return null;;

		$entryId = (int)$tmp[1];
		$postfix = EntryPreviewUtil::getPreviewPostfix($entryId);
		if(!strlen($postfix)) $postfix = $this->postfix;

		if($alias != $entryId . "_" . $postfix) return null;

		/** 取得した記事がblogLabelIdと一致しているか？を調べる */
		try{
			$res = soycms_get_hash_table_dao("entry_label")->getByEntryIdLabelId($entryId, $blogLabelId);
		}catch(Exception $e){
			return null;
		}

		if(!is_numeric($res->getEntryId()) || !is_numeric($res->getLabelId())) return null;

		header("HTTP/1.1 404 Not Found");
		define("PLUGIN_PREVIEW_MODE", true);
		SOY2::import("logic.site.Entry.class.new.LabeledEntryDAO");
		return SOY2::cast("LabeledEntry", soycms_get_entry_object($entryId));
    }

	function onEntryStateMessage($arg){
		$entryId = &$arg["entryId"];
		if(EntryPreviewUtil::checkPreviewMode($entryId)) return "非公開(preview)";
		return null;
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		if(isset($_POST["PreviewConfig"])){
			$entry = $arg["entry"];

			$on = (isset($_POST["PreviewConfig"]["on"]) && (int)$_POST["PreviewConfig"]["on"] === 1);
			EntryPreviewUtil::savePreviewMode($entry->getId(), $on);

			EntryPreviewUtil::savePreviewPostfix($entry->getId(), $_POST["PreviewConfig"]["postfix"]);
		}
	}

	function onCallCustomField(){$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		$pageId = EntryPreviewUtil::getPageIdOnEntryCreatePage($entryId);
		
		return ($pageId > 0) ? self::_buildForm($entryId, $pageId) : self::_buildNoPreviewForm();
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$pageId = (isset($arg[0])) ? (int)$arg[0] : 0;
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;

		return self::_buildForm($entryId, $pageId);
	}

	/**
	 * @param int, int
	 * @return html
	 */
	private function _buildForm(int $entryId, int $pageId){
		$on = EntryPreviewUtil::checkPreviewMode($entryId);

		$html = array();
		$html[] = "<div class=\"alert alert-success\">記事プレビュー</div>";

		if(soycms_get_entry_object($entryId)->getIsPublished() < Entry::ENTRY_ACTIVE){
			$html[] = "<div class=\"form-group\">";
			$html[] = "	<label>記事プレビューを使用する</label>";
			if($on){
				$html[] = "	<input type=\"checkbox\" name=\"PreviewConfig[on]\" value=\"1\" checked=\"checked\" id=\"soycms_preview_check\">";
			}else{
				$html[] = "	<input type=\"checkbox\" name=\"PreviewConfig[on]\" value=\"1\" id=\"soycms_preview_check\">";
			}
			$html[] = "</div>";

			$postfix = EntryPreviewUtil::getPreviewPostfix($entryId);
			$html[] = "<div class=\"form-group\" id=\"soycms_preview_url_postfix\">";
			$html[] = "<label>プレビューURLの接尾語</label><br>";
			$html[] = "<div class=\"form-inline\">";
			$html[] = "<input type=\"text\" name=\"PreviewConfig[postfix]\" class=\"form-control\" value=\"" . $postfix . "\" placeholder=\"" . $this->postfix . "\">";
			$html[] = "</div>";
			$html[] = "</div>";

			// プレビューのURL
			if(!strlen($postfix)) $postfix = $this->postfix;
			$url = EntryPreviewUtil::buildPreviewPageUrl($pageId) . $entryId . "_" . $postfix;
			$html[] = "<div class=\"form-group\" id=\"soycms_preview_url_area\">";
			$html[] = "<label>プレビューURL</label>";
			if($entryId > 0){
				$html[] = $url;
				if($on){
					$html[] = "<a href=\"" . $url . "\" class=\"btn btn-info\" target=\"_blank\" rel=\"noopener\">確認</a>";
				}
			}else{
				$html[] = "---";
			}
			
			$html[] = "</div>";

			$html[] = "<script>" . file_get_contents(dirname(__FILE__) . "/js/preview.js") . "</script>";
		}else{
			$html[] = "記事の公開設定が公開中の時はプレビューの設定を行うことはできません。<br><br>";
		}

		return implode("\n", $html);
	}

	private function _buildNoPreviewForm(){
		$html = array();
		$html[] = "<div class=\"alert alert-success\">記事プレビュー</div>";
		$html[] = "<strong>記事プレビューを使用する手順</strong>";
		$html[] = "<ul style=\"list-style-type:decimal;\">";
		$html[] = "<li>ブログで設定しているラベルを選択した上で（記事の）作成ボタンを押します。公開設定は下書きのままでOKです。</li>";
		$html[] = "<li><strong>記事プレビューを使用する</strong>にチェックをつけます。</li>";
		$html[] = "<li><strong>プレビューURLの接尾語</strong>を必要であれば変更してから（記事の）更新ボタンを押します。</li>";
		$html[] = "<li><strong>プレビューURL</strong>に続いて確認ボタンが表示されるので押します。</li>";
		$html[] = "</ul>";
		$html[] = "<br>";
		return implode("\n", $html);
	}

	/**
	 * @TODO ヘルプを表示
	 */
	function config_page(){
		SOY2::import("site_include.plugin.soycms_entry_preview.config.EntryPreviewConfigPage");
		$form = SOY2HTMLFactory::createInstance("EntryPreviewConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	function getPostfix(){
		return $this->postfix;
	}

	function setPostfix($postfix){
		$this->postfix = $postfix;
	}

	/**
	 * プラグインの登録
	 */
	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new EntryPreviewPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID,array($obj,"init"));
	}
}
