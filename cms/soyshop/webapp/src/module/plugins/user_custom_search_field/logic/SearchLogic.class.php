<?php

class SearchLogic extends SOY2LogicBase{

    private $where = array();
    private $binds = array();
    private $usereDao;

    function __construct(){
        SOY2::import("module.plugins.user_custom_search_field.util.UserCustomSearchFieldUtil");
        $this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
    }

    /**
     * @params int current:現在のページ, int limit:一ページで表示する商品
     * @return array<SOYShop_User>
     */
    function search($mypageId=null, $current, $limit){
        self::setCondition();

        $sql = "SELECT DISTINCT s.user_id, s.*, u.* " .
                "FROM soyshop_user u ".
                "INNER JOIN soyshop_user_custom_search s ".
                "ON u.id = s.user_id ";
        $sql .= self::buildWhere();    //カウントの時と共通の処理は切り分ける
        //$sort = self::buildOrderBySQLOnSearchPage($mypageId);
        if(isset($sort)) $sql .= $sort;

        //表示件数
        $sql .= " LIMIT " . (int)$limit;

        //OFFSET
        $offset = $limit * ($current - 1);
        if($offset > 0) $sql .= " OFFSET " . $offset;
		
        try{
            $res = $this->userDao->executeQuery($sql, $this->binds);
        }catch(Exception $e){
			return array();
        }

        if(!count($res)) return array();

        $users = array();
        foreach($res as $v){
            $users[] = $this->userDao->getObject($v);
        }

        return $users;
    }

    function getTotal(){
        self::setCondition();

        $sql = "SELECT COUNT(id) AS total " .
                "FROM soyshop_user u ".
                "INNER JOIN soyshop_user_custom_search s ".
                "ON u.id = s.user_id ";
        $sql .= self::buildWhere();    //カウントの時と共通の処理は切り分ける

        try{
            $res = $this->userDao->executeQuery($sql, $this->binds);
        }catch(Exception $e){
            return 0;
        }

        return (isset($res[0]["total"])) ? (int)$res[0]["total"] : 0;
    }

    private function buildWhere(){
        $config = UserCustomSearchFieldUtil::getSearchConfig();

        $where = "WHERE u.is_disabled != 1 ";

        foreach($this->where as $key => $w){
            if(!strlen($w)) continue;
            $where .= "AND " . $w . " ";
        }
        return $where;
    }

    private function setCondition(){
        if(!count($this->where)){
            /** @ToDo 年齢検索 **/

			//顧客の基本情報周り
			foreach(array("name", "reading", "mail_address") as $key){
				if(isset($_GET["u_search"][$key]) && strlen($_GET["u_search"][$key])) {
	                $this->where[$key] = "u." . $key . " LIKE :" . $key . " ";
	                $this->binds[":" . $key] = "%" . trim($_GET["u_search"][$key]) . "%";

					//メールアドレス検索の時、ダミーのメールアドレスを登録している顧客は除く
					if($key == "mail_address"){
						$this->where["dummy"] = "u.mail_address NOT LIKE '%" . DUMMY_MAIL_ADDRESS_DOMAIN . "' ";
					}
	            }
			}

            foreach(UserCustomSearchFieldUtil::getConfig() as $key => $field){

                //まずは各タイプのfield SQLでkeyを指定する場合、s.を付けること。soyshop_user_custom_searchのaliasがs
                switch($field["type"]){
                    //文字列の場合
                    case UserCustomSearchFieldUtil::TYPE_STRING:
                    case UserCustomSearchFieldUtil::TYPE_TEXTAREA:
                    case UserCustomSearchFieldUtil::TYPE_RICHTEXT:
					    if(isset($_GET["u_search"][$key]) && strlen($_GET["u_search"][$key])){
                            $this->where[$key] = "s." . $key . " LIKE :" . $key;
                            $this->binds[":" . $key] = "%" . trim($_GET["u_search"][$key]) . "%";
                        }
                        break;

                    //範囲の場合
                    case UserCustomSearchFieldUtil::TYPE_RANGE:
                        $ws = "";$we = "";    //whereのスタートとエンド
                        if(isset($_GET["u_search"][$key . "_start"]) && strlen($_GET["u_search"][$key . "_start"]) && is_numeric($_GET["u_search"][$key . "_start"])){
                            $ws = "s." . $key . " >= :" . $key . "_start";
                            $this->binds[":" . $key . "_start"] = (int)$_GET["u_search"][$key . "_start"];
                        }
                        if(isset($_GET["u_search"][$key . "_end"]) && strlen($_GET["u_search"][$key . "_end"]) && is_numeric($_GET["u_search"][$key . "_end"])){
                            $we = "s." . $key .  " <= :" . $key . "_end";
                            $this->binds[":" . $key . "_end"] = (int)$_GET["u_search"][$key . "_end"];
                        }
                        if(strlen($ws) && strlen($we)){
                            $this->where[$key] = "(" . $ws . " AND " . $we . ")";
                        }else if(strlen($ws) || strlen($we)){
                            $this->where[$key] = $ws . $we;
                        }
                        break;

                    //チェックボックスの場合
                    case UserCustomSearchFieldUtil::TYPE_CHECKBOX:
                        if(isset($_GET["u_search"][$key]) && count($_GET["u_search"][$key])){
                            $w = array();
                            foreach($_GET["u_search"][$key] as $i => $v){
                                if(!strlen($v)) continue;
                                $w[] = "s." . $key . " LIKE :" . $key . $i;
                                $this->binds[":" . $key . $i] = "%" . trim($v) . "%";
                            }
                            if(count($w)) $this->where[$key] = "(" . implode(" OR ", $w) . ")";
                        }
                        break;

                    //数字、ラジオボタン、セレクトボックス
                    default:
                        if(isset($_GET["u_search"][$key]) && strlen($_GET["u_search"][$key])){
                            $this->where[$key] = "s." . $key . " = :" . $key;
                            $this->binds[":" . $key] = $_GET["u_search"][$key];
                        }
                }
            }

			//ここからはグループ
			SOY2::import("util.SOYShopPluginUtil");
		    if(SOYShopPluginUtil::checkIsActive("user_group")){
				$gwhere = array();

				foreach(array("name", "code") as $key){
					if(isset($_GET["g_search"][$key]) && strlen($_GET["g_search"][$key])){
						$gwhere["g" . $key] = "g." . $key . " LIKE :g" . $key;
						$this->binds[":g" . $key] = "%" . trim($_GET["g_search"][$key]) . "%";
					}
				}

				if(count($gwhere)){
					$gSubquery = "select gi.user_id from soyshop_user_grouping gi ".
									"INNER JOIN soyshop_user_group g ".
									"ON gi.group_id = g.id ".
									"WHERE " . implode(" AND ", $gwhere);
					$this->where["gsubquery"] = "u.id IN (" . $gSubquery .")";
				}
			}
        }
    }

    private function buildOrderBySQLOnSearchPage($pageId){
        return self::buildOrderBySQLCommon($pageId);
    }

    private function buildOrderBySQLCommon($pageId){
        $session = SOY2ActionSession::getUserSession();
        if(isset($_GET["sort"]) || isset($_GET["csort"])){
            $custom_search_sort = null;
        }else{
            $custom_search_sort = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_user_custom_search" . $pageId);
        }

        //カスタムソート
        if(isset($_GET["custom_search_sort"])){
            $custom_search_sort = ($_GET["custom_search_sort"] != "reset") ? htmlspecialchars($_GET["custom_search_sort"], ENT_QUOTES, "UTF-8") : null;
            //存在するフィールドか調べる
            $dao = new SOY2DAO();
            try{
                $dao->executeQuery("SELECT user_id FROM soyshop_user_custom_search WHERE " . $custom_search_sort . "= '' LIMIT 1");
            }catch(Exception $e){
                $custom_search_sort = null;
            }
            $session->setAttribute("soyshop_" . SOYSHOP_ID . "_user_custom_search" . $pageId, $custom_search_sort);
        }

        if(isset($custom_search_sort)){
            $suffix = $session->getAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId);
            if(isset($_GET["r"])){
                $suffix = ($_GET["r"] == 1) ? " DESC" : " ASC";
                $session->setAttribute("soyshop_" . SOYSHOP_ID . "_suffix" . $pageId, $suffix);
            }

            return " ORDER BY s." . $custom_search_sort . " IS NULL ASC, s." . $custom_search_sort . $suffix;
        }

        return null;
    }
}
