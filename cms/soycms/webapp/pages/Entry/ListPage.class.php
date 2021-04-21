<?php

class ListPage extends CMSUpdatePageBase{

	protected $labelIds;
	protected $isShowDisplayOrder = true;

	private $offset;
	private $limit;

	function doPost(){

		$query_str = "?offset=".$this->offset."&limit=".$this->limit;

		switch($_POST['op_code']){
			case 'delete':
				//削除実行
				$result = $this->run("Entry.RemoveAction");
				if($result->success()){
					$this->addMessage("ENTRY_REMOVE_SUCCESS");
				}else{
					$this->addErrorMessage("ENTRY_REMOVE_FAILED");
				}
				$this->jump("Entry.List.".implode(".",$this->labelIds).$query_str);
				break;
			case 'copy':
				//複製実行
				$result = $this->run("Entry.CopyAction");
				if($result->success()){
					$this->addMessage("ENTRY_COPY_SUCCESS");
				}else{
					$this->addErrorMessage("ENTRY_COPY_FAILED");
				}
				$this->jump("Entry.List.".implode(".",$this->labelIds).$query_str);
				break;
			case 'setPublish':
				//公開状態にする
				$result =$this->run("Entry.PublishAction",array('publish'=>true));
				if($result->success()){
					$this->addMessage("ENTRY_PUBLISH_SUCCESS");
				}else{
					$this->addErrorMessage("ENTRY_PUBLISH_FAILED");
				}
				$this->jump("Entry.List.".implode(".",$this->labelIds).$query_str);
				break;
			case 'setnonPublish':
				//非公開状態にする
				$result = $this->run("Entry.PublishAction",array('publish'=>false));
				if($result->success()){
					$this->addMessage("ENTRY_NONPUBLISH_SUCCESS");
				}else{
					$this->addErrorMessage("ENTRY_NONPUBLISH_FAILED");
				}
				$this->jump("Entry.List.".implode(".",$this->labelIds).$query_str);
				break;
			case 'update_display':
				//表示順が押された（と判断してるけど）
				$result = $this->run("EntryLabel.UpdateDisplayOrderAction");

				if($result->success()){
					$this->addMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_SUCCESS");
					$this->jump("Entry.List.".implode(".",$this->labelIds).$query_str);
				}else{
					$this->addErrorMessage("ENTRYLABEL_DISPLAYORDER_MODIFY_FAILED");
					$this->jump("Entry");
				}
				break;
		}
		exit;

	}

	/**
	 * クッキーに保存
	 */
	function updateCookie($labelIds){
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

		$this->offset = $offset;
		$this->limit  = $limit;

		$labelIds = isset($arg) ? $arg : array();
		$this->labelIds = $labelIds;

		$this->updateCookie($labelIds);

		parent::__construct();

		//IDが0だった場合はIndexへ
		if(count($this->labelIds)<1){
			$this->jump("Entry"."?offset=".$this->offset."&limit=".$this->limit);
		}

		//ラベル一覧を取得
		$labelList = self::getLabelList();

		//自分自身へのリンク
		$currentLink = SOY2PageController::createLink("Entry.List") . "/". implode("/",$labelIds);

		//無効なラベルIDを除く
		foreach($this->labelIds as $key => $value){
			if(!is_numeric($value))unset($this->labelIds[$key]);
		}

		//記事を取得
		list($entries, $count, $offset) = self::getEntries($offset,$limit,$this->labelIds);

		//include_once(dirname(__FILE__).'/_EntryBlankPage.class.php');
		$this->createAdd("no_entry_message","Entry._EntryBlankPage",array(
			"visible"=>(count($entries) == 0),
			"labelIds" => $this->labelIds
		));

		if(count($entries) > 0){
			//do nothing
		}else{
			DisplayPlugin::hide("must_exist_entry");
		}


		//記事一覧の表を作成
		$this->createAdd("list","_component.Entry.LabeledEntryListComponent",array(
				"labelIds"  => $this->labelIds,
				"labelList" => $labelList,
				"list"	  => $entries,
		));

		$labelState = array();
		$url = SOY2PageController::createLink("Entry.List");
		foreach($this->labelIds as $labelId){
			if(!isset($labelList[$labelId]))continue;
			$label = $labelList[$labelId];
			$url .= "/".$label->getId();
			$url = htmlspecialchars($url, ENT_QUOTES, "UTF-8");
			$caption = $label->getDisplayCaption();
			$labelState[] = "<a href=\"{$url}\">{$caption}</a>";
		}
		$this->addLabel("label_state", array(
			"html" => implode(" &gt; ",$labelState)
		));


		//子ラベルを取得
		$labels = $this->getNarrowLabels();

		//子ラベル表示領域のキャプション
		$this->addModel("sublabel_list_wrapper", array(
			"visible" => count($labels)
		));

		//子ラベルボタンを作成
		$this->createAdd("sublabel_list","_component.Entry.SubLabelListComponent",array(
			"list" => $labels,
			"labelList" => $labelList,
			"currentLink" => $currentLink
		));


		//戻るリンクを作成
		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Entry.List") . "/" .implode("/",array_slice($labelIds,0,count($labelIds)-1))
		));

		//新規作成リンクを作成（公開記事一覧などでは表示しない）
		$this->addModel("create_link_box", array(
			"visible" => isset($labelIds[0]) AND is_numeric($labelIds[0])
		));
		$this->addLink("create_link", array(
			"link" => SOY2PageController::createLink("Entry.Create") . "/" . implode("/",$labelIds),
		));


		//ページャーを作成
		$this->createAdd("topPager","EntryPagerComponent",array(
			"arguments"=> array($offset, $limit, $count, $currentLink)
		));

		//記事テーブルのCSS
		HTMLHead::addLink("entrytree",array(
			"rel" => "stylesheet",
			"type" => "text/css",
			"href" => SOY2PageController::createRelativeLink("./css/entry/entry.css")
		));

		$this->addLink("showCount10" , array("link"=> $currentLink ."?limit=10"));
		$this->addLink("showCount20" , array("link"=> $currentLink ."?limit=20"));
		$this->addLink("showCount50" , array("link"=> $currentLink ."?limit=50"));
		$this->addLink("showCount100", array("link"=> $currentLink ."?limit=100"));
		$this->addLink("showCount500", array("link"=> $currentLink ."?limit=500"));

		//フォーム
		$this->addForm("index_form",array(
			"action" => $currentLink."?limit=".$this->limit."&offset=".$this->offset
		));

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

		if(count($this->labelIds) == 0 || !is_numeric(implode("",$this->labelIds))){
			$this->addScript("parameters",array(
				"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel").'"'
			));
			$this->addLink("search_link",array(
				"link"=>SOY2PageController::createLink("Entry.Search")
			));
		}else{
			$this->addScript("parameters",array(
				"script"=>'var listPanelURI = "'.SOY2PageController::createLink("Entry.ListPanel.".implode('.',$this->labelIds)).'"'
			));
			$this->addLink("search_link",array(
				"link"=>SOY2PageController::createLink("Entry.Search.".implode('.',$this->labelIds))
			));
		}

		//操作用のJavaScript
		$this->addScript("entry_list",array(
			"script"=> file_get_contents(dirname(__FILE__)."/script/entry_list.js")
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
		if(strpos($_SERVER["PATH_INFO"], "/Closed")){
			$result = $this->run("Entry.ClosedEntryListAction",array(
	    		"offset" => $offset,
	    		"limit" => $limit
	    	));

	    	$entities = $result->getAttribute("Entities");
	    	$totalCount = $result->getAttribute("total");

	    	return array($entities,$totalCount,min($offset,$totalCount));
		}else if(strpos($_SERVER["PATH_INFO"], "/OutOfDate")){
			$result = $this->run("Entry.OutOfDateEntryListActoin",array(
	    		"offset" => $offset,
	    		"limit" => $limit
	    	));

	    	$entities = $result->getAttribute("Entities");
	    	$totalCount = $result->getAttribute("total");

	    	return array($entities,$totalCount,min($offset,$totalCount));
		}else if(strpos($_SERVER["PATH_INFO"], "/NoLabel")){
			$result = $this->run("Entry.NoLabelEntryListAction",array(
	    		"offset" => $offset,
	    		"limit" => $limit
	    	));

	    	$entities = $result->getAttribute("Entities");
	    	$totalCount = $result->getAttribute("total");
	    	return array($entities,$totalCount,min($offset,$totalCount));
		}else{
			//ラベルIDに数字以外が含まれていたらアウト
			foreach($labelIds as $labelId){
				if(!is_numeric($labelId))return array(array(),0,0);
			}

			$action = SOY2ActionFactory::createInstance("Entry.EntryListAction", array(
				"ids" => $labelIds,
				"offset" => $offset,
				"limit" => $limit
			));
			$result = $action->run();
			$entities = $result->getAttribute("Entities");
			$totalCount = $result->getAttribute("total");

			return array($entities,$totalCount,min($offset,$totalCount));
		}
	}


	/**
	 * ラベルをすべて取得
	 * 記事管理者は制限有り
	 */
	private function getLabelList(){
		$action = SOY2ActionFactory::createInstance("Label.LabelListAction");
		$result = $action->run();

		if($result->success()){
			return $result->getAttribute("list");
		}else{
			return array();
		}
	}

	/**
	 * 子ラベルを取得
	 */
	private function getNarrowLabels(){
		return $this->run("EntryLabel.NarrowLabelListAction",array(
			"labelIds" => $this->labelIds
		))->getAttribute("labels");
	}
}
