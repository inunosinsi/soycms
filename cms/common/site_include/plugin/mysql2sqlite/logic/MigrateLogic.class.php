<?php

class MigrateLogic extends SOY2LogicBase {

	private $sqlitePath;
	private $backupDir;
	private $pdo;

	function __construct(){
		$this->sqlitePath = UserInfoUtil::getSiteDirectory() . ".db/sqlite.db";
		$this->backupDir = UserInfoUtil::getSiteDirectory() . ".db/backup/";
	}

	function migrate(){
		set_time_limit(0);

		//既にSQLiteのデータベースがあれば、バックアップをとってから削除
		if(file_exists($this->sqlitePath)){
			if(!file_exists($this->backupDir)){
				mkdir($this->backupDir);
			}
			$i = 0;
			for(;;){
				if(!file_exists($this->backupDir . "sqlite.db.backup" . $i)){
					rename($this->sqlitePath, $this->backupDir . "sqlite.db.backup" . $i);
					break;
				}
				$i++;
			}
		}
		touch($this->sqlitePath);
		$this->pdo = new PDO("sqlite:" . $this->sqlitePath);

		$sqls = file_get_contents(SOY2::rootDir() . "sql/init_site_sqlite.sql");
		if(preg_match_all('/CREATE.*?;/mis', $sqls, $tmp)){
            if(count($tmp[0])){
                foreach($tmp[0] as $sql){
					$this->pdo->query($sql);

					//データを挿入
					if(strpos($sql, "create table") !== false){
						preg_match('/create table (.*)\(/', $sql, $tmp);
						if(isset($tmp[1])){
							$tableName = trim($tmp[1]);
							switch(trim($tmp[1])){
								case "Entry":
									self::registerEntry($sql);
									break;
								case "EntryHistory":
									//移行しない
									break;
								case "EntryComment":
									self::registerEntryComment($sql);
									break;
								case "EntryTrackback":
									self::registerEntryTrackback($sql);
									break;
								case "EntryAttribute":
									self::registerEntryAttribute($sql);
									break;
								case "Label":
									self::registerLabel($sql);
									break;
								case "EntryLabel":
									self::registerEntryLabel($sql);
									break;
								case "Template":
									//移行しない
									break;
								case "Page":
									self::registerPage($sql);
									break;
								case "TemplateHistory":
									//移行しない
									break;
								case "Block":
									self::registerBlock($sql);
									break;
								case "SiteConfig":
									self::registerSiteConfig($sql);
									break;
								case "soycms_data_sets":
									//移行しない
									break;
							}
						}
					}
                }
            }

			//カスタムフィールド
			if(file_exists(UserInfoUtil::getSiteDirectory() . "/.plugin/CustomField.active")){
				self::registerCustomField();
			}

			//ブログ記事SEOプラグイン
			if(file_exists(UserInfoUtil::getSiteDirectory() . "/.plugin/soycms_entry_info.active")){
				self::registerEntryInfo();
			}

			/** @ToDo gravatar **/
			/** @ToDo read_entry_count **/
			/** @ToDo record_dead_link **/
			/** @ToDo soycms_like_button **/
			/** @ToDo url_shortener **/

			//最後にadmin.Siteの方のdata_source_nameの値を変更
			$siteId = UserInfoUtil::getSiteId();

			$old = CMSUtil::switchDsn();
			$siteDao = SOY2DAOFactory::create("admin.SiteDAO");
			$site = $siteDao->getById($siteId);
			$site->setDataSourceName("sqlite:" . $this->sqlitePath);
			$siteDao->update($site);

			CMSUtil::resetDsn($old);
        }
	}

	private function registerEntry($sql){
		try{
			$entries = SOY2DAOFactory::create("cms.EntryDAO")->get();
			if(!count($entries)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO Entry (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($entries as $entry){
			$stmt->execute(array(
				":id" => $entry->getId(),
				":title" => $entry->getTitle(),
				":alias" => $entry->getAlias(),
				":content" => $entry->getContent(),
				":more" => $entry->getMore(),
				":cdate" => $entry->getCdate(),
				":udate" => $entry->getUdate(),
				":description" => $entry->getDescription(),
				":openPeriodStart" => $entry->getOpenPeriodStart(),
				":openPeriodEnd" => $entry->getOpenPeriodEnd(),
				":isPublished" => $entry->getIsPublished(),
				":style" => $entry->getStyle(),
				":author" => $entry->getAuthor()
			));
		}
	}

	private function registerEntryComment($sql){
		try{
			$comments = SOY2DAOFactory::create("cms.EntryCommentDAO")->get();
			if(!count($comments)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO EntryComment (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($comments as $comment){
			$stmt->execute(array(
				":id" => $comment->getId(),
				":entry_id" => $comment->getEntryId(),
				":title" => $comment->getTitle(),
				":author" => $comment->getAuthor(),
				":body" => $comment->getBody(),
				":is_approved" => $comment->getIsApproved(),
				":mail_address" => $comment->getMailAddress(),
				":url" => $comment->getUrl(),
				":submitdate" => $comment->getSubmitDate()
			));
		}
	}

	private function registerEntryTrackback($sql){
		try{
			$trackbacks = SOY2DAOFactory::create("cms.EntryTrackbackDAO")->get();
			if(!count($trackbacks)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO EntryTrackback (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($trackbacks as $trackback){
			$stmt->execute(array(
				":id" => $trackback->getId(),
				":entry_id" => $trackback->getEntryId(),
				":title" => $trackback->getTitle(),
				":url" => $trackback->getUrl(),
				":blog_name" => $trackback->getBlogName(),
				":excerpt" => $trackback->getExcerpt(),
				":submitdate" => $trackback->getSubmitDate(),
				":certification" => $trackback->getCertification()
			));
		}
	}

	private function registerEntryAttribute($sql){
		try{
			$attrs = SOY2DAOFactory::create("cms.EntryAttributeDAO")->getAll();
			if(!count($attrs)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO EntryAttribute (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($attrs as $attr){
			$stmt->execute(array(
				":entry_id" => $attr->getEntryId(),
				":entry_field_id" => $attr->getFieldId(),
				":entry_value" => $attr->getValue(),
				":entry_extra_values" => $attr->getExtraValues()
			));
		}
	}

	private function registerLabel($sql){
		try{
			$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
			if(!count($labels)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO Label (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($labels as $label){
			$stmt->execute(array(
				":id" => $label->getId(),
				":caption" => $label->getCaption(),
				":description" => $label->getDescription(),
				":alias" => $label->getAlias(),
				":icon" => $label->getIcon(),
				":display_order" => $label->getDisplayOrder(),
				":color" => $label->getColor(),
				":background_color" => $label->getBackgroundColor()
			));
		}
	}

	private function registerEntryLabel($sql){
		try{
			$entrylabels = SOY2DAOFactory::create("cms.EntryLabelDAO")->get();
			if(!count($entrylabels)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO EntryLabel (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($entrylabels as $entrylabel){
			$stmt->execute(array(
				":entry_id" => $entrylabel->getEntryId(),
				":label_id" => $entrylabel->getLabelId(),
				":display_order" => (!is_null($entrylabel->getDisplayOrder())) ? $entrylabel->getDisplayOrder() : 10000000
			));
		}
	}

	private function registerPage($sql){
		try{
			$pages = SOY2DAOFactory::create("cms.PageDAO")->get();
			if(!count($pages)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO Page (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($pages as $page){
			$stmt->execute(array(
				":id" => $page->getId(),
				":title" => $page->getTitle(),
				":template" => $page->getTemplate(),
				":uri" => $page->getUri(),
				":page_type" => $page->getPageType(),
				":page_config" => $page->getPageConfig(),
				":openPeriodStart" => $page->getOpenPeriodStart(),
				":openPeriodEnd" => $page->getOpenPeriodEnd(),
				":isPublished" => $page->getIsPublished(),
				":isTrash" => $page->getIsTrash(),
				":parent_page_id" => $page->getParentPageId(),
				":udate" => $page->getUdate(),
				":icon" => $page->getIcon()
			));
		}
	}

	private function registerBlock($sql){
		try{
			$blocks = SOY2DAOFactory::create("cms.BlockDAO")->get();
			if(!count($blocks)) return;
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO Block (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		foreach($blocks as $block){
			$stmt->execute(array(
				":id" => $block->getId(),
				":soy_id" => $block->getSoyId(),
				":page_id" => $block->getPageId(),
				":class" => $block->getClass(),
				":object" => $block->getObject(),
			));
		}
	}

	private function registerSiteConfig($sql){
		try{
			$config = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();
		}catch(Exception $e){
			return;
		}

		$columns = self::splitColumns($sql);
		$stmt = $this->pdo->prepare("INSERT INTO SiteConfig (" . implode(",", $columns) . ") VALUES (:" . implode(",:", $columns) . ");");
		$stmt->execute(array(
			":name" => $config->getName(),
			":description" => $config->getDescription(),
			":siteConfig" => $config->getSiteConfig(),
			":charset" => $config->getCharset()
		));
	}

	private function registerCustomField(){
		$this->pdo->query("ALTER TABLE Entry ADD COLUMN custom_field TEXT");

		$dao = new SOY2DAO();
		$res = $dao->executeQuery("SELECT id, custom_field FROM Entry WHERE LENGTH(custom_field) > 0");
		if(!count($res)) return;

		$stmt = $this->pdo->prepare("UPDATE Entry SET custom_field = :custom WHERE id = :id");
		foreach($res as $v){
			$stmt->execute(array(
				":custom" => $v["custom_field"],
				":id" => $v["id"]
			));
		}
	}

	private function registerEntryInfo(){
		$this->pdo->query("ALTER TABLE Entry ADD COLUMN keyword VARCHAR");

		$dao = new SOY2DAO();
		$res = $dao->executeQuery("SELECT id, keyword FROM Entry WHERE LENGTH(keyword) > 0");
		if(!count($res)) return;

		$stmt = $this->pdo->prepare("UPDATE Entry SET keyword = :keyword WHERE id = :id");
		foreach($res as $v){
			$stmt->execute(array(
				":keyword" => $v["keyword"],
				":id" => $v["id"]
			));
		}
	}

	private function splitColumns($sql){
		$rows = explode("\n", $sql);

		$columns = array();
		foreach($rows as $row){
			$row = trim($row);
			if(strpos($row, "create table") === 0 || strpos($row, ")") === 0 || strpos($row, "unique") === 0) continue;
			$split = explode(" ", $row);
			$columns[] = trim($split[0]);
		}

		return $columns;
	}
}
