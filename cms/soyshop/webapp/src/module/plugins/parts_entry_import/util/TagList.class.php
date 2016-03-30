<?php

class TagList{
	
	public static function getTagList(){
		return array(
			array("entry_id", "全て","対象記事のIDを出力します。"),
			array("title", "全て", "対象記事のタイトルを記事毎ページへのリンク付きで出力します。"),
			array("title_plain", "全て", "対象記事のタイトルのみを出力します。"),
			array("content", "全て", "対象記事の本文を出力します。"),
			array("more", "全て", "対象記事の追記を出力します。"),
			array("create_date", "全て", "対象記事の作成日付を出力します。"),
			array("create_time", "全て", "対象記事の作成時刻を出力します。"),
			array("entry_link", "A", "対象記事の記事毎ページへのリンクを生成します。"),
			array("more_link", "A", "対象記事の追記の表示された記事毎ページへのリンクを生成します。")
		);
	}
}
?>