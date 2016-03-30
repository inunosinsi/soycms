<?php
/*
 */
class DetailCategoryInfoInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			$html = array();
			$html[] = "商品のカスタムフィールドのフィールドIDとカテゴリカスタムフィールドのフィールドIDが被らないようにしてください<br />";
			$html[] = "カテゴリ名は<strong>cms:id=\"category_name\"</strong>で取得します。<br />";
			$html[] = "カテゴリツリーは<strong>cms:id=\"category_tree\"</strong>で取得します。<br />";
			$html[] = "それ以外はカテゴリカスタムフィールドで設定したIDを使用します<br />";
			$html[] = "<strong>&lt;a href=\"/shop/item/list/<!-- cms:id=\"category_alias\" /-->\"&gt;&lt;!-- cms:id=\"category_name\" /--&gt;&lt;/a&gt;</strong>で一覧ページへのリンクを生成します";
			return implode("\r\n",$html);
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info","detail_category_info","DetailCategoryInfoInfo");
?>