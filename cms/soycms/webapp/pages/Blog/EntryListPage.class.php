<?php

class EntryListPage extends CMSWebPageBase{

	var $id;
	var $page;
	protected $labelIds;

	function doPost(){
		if(soy2_check_token()){

			switch($_POST['op_code']){
				case 'delete':
					//削除実行
					$result = $this->run("Entry.RemoveAction");
					if($result->success()){
						$this->addMessage("ENTRY_REMOVE_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_REMOVE_FAILED");
					}
					$this->jump("Blog.EntryList.".$this->id.".".implode(".",$this->labelIds));
					break;
				case 'copy':
					//複製実行
					$result = $this->run("Entry.CopyAction");
					if($result->success()){
						$this->addMessage("ENTRY_COPY_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_COPY_FAILED");
					}
					$this->jump("Blog.EntryList.".$this->id.".".implode(".",$this->labelIds));
					break;
				case 'setPublish':
					//公開状態にする
					$result =$this->run("Entry.PublishAction",array('publish'=>true));
					if($result->success()){
						$this->addMessage("ENTRY_PUBLISH_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_PUBLISH_FAILED");
					}
					$this->jump("Blog.EntryList.".$this->id.".".implode(".",$this->labelIds));
					break;
				case 'setnonPublish':
					//非公開状態にする
					$result = $this->run("Entry.PublishAction",array('publish'=>false));
					if($result->success()){
						$this->addMessage("ENTRY_NONPUBLISH_SUCCESS");
					}else{
						$this->addErrorMessage("ENTRY_NONPUBLISH_FAILED");
					}
					$this->jump("Blog.EntryList.".$this->id.".".implode(".",$this->labelIds));
					break;
				case 'update_display':
					//表示順が押された（と判断してるけど）
					$result = $this->run("EntryLabel.UpdateDisplayOrderAction");

					if($result->success()){
						$this->addMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_SUCCESS");
						$this->jump("Blog.EntryList.".$this->id.".".implode(".",$this->labelIds));
					}else{
						$this->addErrorMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_FAILED");
						$this->jump("Blog.EntryList.".$this->id);
					}
					break;
			}
		}else{
			$this->jump("Blog.EntryList.".$this->id);
		}
		exit;

	}


	/**
	 * クッキーに保存
	 */
	private function updateCookie($labelIds){
		//Entry_List
		soy2_setcookie("Entry_List", implode('.',$labelIds));

		//Entry_List_Limit
		if(isset($_GET['limit'])){
			soy2_setcookie("Entry_List_Limit", $_GET['limit']);
		}
	}

	function __construct($arg){

		$offset = isset($_GET['offset'])? (int)$_GET['offset'] : 0 ;
		$limit  = isset($_GET['limit'])? (int)$_GET['limit'] : ( isset($_COOKIE['Entry_List_Limit'])? (int)$_COOKIE['Entry_List_Limit'] : 10 );

		//最初の値はブログページID
		$id = array_shift($arg);
		$this->id = $id;


		$labelIds = isset($arg)? $arg: array();
		$this->labelIds = $labelIds;

		$this->updateCookie($labelIds);

		$result = $this->run("Blog.DetailAction",array("id"=>$id));
		if(!$result->success()){
			$this->addMessage("PAGE_DETAIL_GET_FAILED");
			$this->jump("Page");
			exit;
		}

		$page = $result->getAttribute("Page");
		$labelId = $page->getBlogLabelId();

		$result = $this->run("Entry.EntryListAction",array("id"=>$labelId));
		$entries = $result->getAttribute("Entities");

		//ラベルIDには必ずブログに使用するラベルIDが必要
		if(!in_array($labelId,$this->labelIds)){
			array_unshift($this->labelIds,$labelId);
		}

		parent::__construct();

		//ラベル一覧を取得
		$labelList = $this->getLabelList();

		//自分自身へのリンク
		$currentLink = SOY2PageController::createLink("Blog.EntryList") . "/".$id."/". implode("/",$this->labelIds);

		//無効なラベルIDを除く
		foreach($this->labelIds as $key => $value){
			if(!is_numeric($value))unset($this->labelIds[$key]);
		}

		//記事を取得
		list($entries,$count,$offset) = $this->getEntries($offset,$limit,$this->labelIds);

		include_once(dirname(__FILE__).'/_EntryBlankPage.class.php');
		$this->createAdd("no_entry_message","_EntryBlankPage",array(
			"pageId"=>$this->id,
			"visible"=>(count($entries) == 0)
		));

		if(count($entries) > 0){
			//do nothing
		}else{
			DisplayPlugin::hide("must_exist_entry");
		}

		//記事一覧の表を作成
		$this->createAdd("list","_component.Blog.LabeledEntryListComponent",array(
				"labelIds"	=> $labelIds,
				"labelList"   => $labelList,
				"list"		=> $entries,
				"pageId"	  => $id,
				"labelId"	 => $labelId,
				"page"		=> $page,
		));

		$labelState = array();
		$url = SOY2PageController::createLink("Blog.EntryList.".$id);
		foreach($this->labelIds as $labelId){
			if(!isset($labelList[$labelId]))continue;
			$label = $labelList[$labelId];
			$url .= "/".$label->getId();
			$labelState[] = "<a href=\"$url\">".$label->getDisplayCaption()."</a>";
		}
		$this->addLabel("label_state", array(
			"html" => implode("&nbsp;&gt;&nbsp;",$labelState)
		));


		//子ラベルを取得
		$labels = $this->run("EntryLabel.NarrowLabelListAction",array(
			"labelIds" => $this->labelIds
		))->getAttribute("labels");

		//子ラベルボタンを作成
		$this->createAdd("sublabel_list","_component.Blog.SubLabelListComponent",array(
			"list" => $labels,
			"labelList" => $labelList,
			"currentLink" => $currentLink,
			"pageId"=>$id
		));


		//新規作成リンクを作成
		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Blog.Entry") . "/" . $id
		));


		/**
		 * ページャーを作成
		 */
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($offset, $limit, $count, $currentLink)
		));

		/**
		 * CSS
		 */
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")
		));
		HTMLHead::addLink("blog_entrylist",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/blog/entrylist.css")
		));

		$this->addLink("showCount10", array("link"=> $currentLink ."?limit=10"));
		$this->addLink("showCount50", array("link"=> $currentLink ."?limit=50"));
		$this->addLink("showCount100", array("link"=> $currentLink ."?limit=100"));
		$this->addLink("showCount500", array("link"=> $currentLink ."?limit=500"));

		/**
		 * フォーム
		 */
		$this->addForm("index_form");

		//表示順更新ボタンの追加
		$this->addInput("display_order_submit", array(
			"name" => "display_order_submit",
			"value"=>CMSMessageManager::get("SOYCMS_DISPLAYORDER"),
			"type" => "submit",
			"tabindex" => LabeledEntryListComponent::$tabIndex++
		));

		//削除ボタンの追加
		$this->addInput("remove_submit", array(
			"name" => "remove_submit",
			"value"=>CMSMessageManager::get("SOYCMS_DELETE_ENTRY"),
			"type" => "submit"
		));

		$this->createAdd("BlogMenu","Blog.BlogMenuPage",array(
			"arguments" => array($this->id)
		));

		/**
		 * ツールボックス
		 */
		if($page->isActive() == Page::PAGE_ACTIVE){
			$pageUrl = CMSUtil::getSiteUrl() . ( (strlen($page->getUri()) >0) ? $page->getUri() ."/" : "" ) ;

			//カテゴリー別アーカイブページへのリンク
			$category = @$labelList[array_pop($this->labelIds)];
			if($page->getGenerateCategoryFlag() && isset($category) && in_array($category->getId(), $page->getCategoryLabelList())){
				CMSToolBox::addLink(
					CMSMessageManager::get("SOYCMS_SHOW_CATEGORYARCHIVEPAGE"),
					$pageUrl.$page->getCategoryPageUri()."/".rawurlencode($category->getAlias()),
					false,
					"this.target = '_blank'"
				);
			}
		}
		CMSToolBox::addPageJumpBox();

		//一括ラベル操作のURL
		if(count($labelIds) == 0 || !is_numeric(implode("",$labelIds))){
			$this->addScript("parameters",array(
				"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel").'"'
			));
		}else{
			$this->addScript("parameters",array(
				"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel.".implode('.',$labelIds)).'"'
			));
		}

		//操作用のJavaScript
		$this->addScript("entry_list",array(
			"script"=> file_get_contents(dirname(__FILE__)."/../Entry/script/entry_list.js")
		));

		//トップラベル以外は表示順更新を消す
		if(count($this->labelIds) >= 2){
			DisplayPlugin::hide("no_label");
		}


		if(!UserInfoUtil::hasEntryPublisherRole()){
			DisplayPlugin::hide("publish");
			DisplayPlugin::hide("no_label");
		}

	}

	/**
	 * 複数ラベルを指定して記事を取得
	 * ラベルがnullの時は、すべての記事を表示させる
	 * @param $offset,$limit,$labelId
	 * @return (entry_array,記事の数,大きすぎた場合最終オフセット)
	 */
	private function getEntries($offset,$limit,$labelIds){

		//If array $labelIds is empty
		if(count($labelIds)==0)return array(array(),0,0);


		//ラベルIDに数字以外が含まれていたらアウト
		foreach($labelIds as $labelId){
			if(!is_numeric($labelId))return array(array(),0,0);
		}


		$action = SOY2ActionFactory::createInstance("Entry.EntryListAction",array(
			"ids"=>$labelIds,
			"offset"=>$offset,
			"limit"=>$limit
		));
		$result = $action->run();
		$entities = $result->getAttribute("Entities");
		$totalCount = $result->getAttribute("total");

		return array($entities,$totalCount,min($offset,$totalCount));
	}


	/**
	 * ラベルをすべて取得
	 */
	private function getLabelList(){
		$action = SOY2ActionFactory::createInstance("Label.LabelListAction");
		$result = $action->run();

		if($result->success()){
			$list = $result->getAttribute("list");
			return $list;
		}else{
			return array();
		}
	}
}
