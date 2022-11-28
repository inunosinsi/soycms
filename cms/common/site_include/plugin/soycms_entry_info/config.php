<?php
/*
 * Created on 2009/06/12
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if(isset($_POST["reacquire"])){
	$this->mode = (int)$_POST["reacquire"];
	CMSPlugin::savePluginConfig(self::PLUGIN_ID,$this);
	CMSPlugin::redirectConfigPage();
}
?>
<p>
ブログページの記事毎ページに表示されるキーワードと概要を記事投稿時に設定することが出来ます。
</p>
<p>
ブログページの記事毎ページテンプレートのヘッダ部分にある<br />
メタキーワードタグとメタディスクリプションタグに下記のタグを上書きしてください。<br />
(ただし、記事毎ページ以外のページでは、このプラグインは動作しません)
</p>

<p>
<form method="POST">
<input type="hidden" name="reacquire" value="0">
<label><input type="checkbox" name="reacquire" value="1" <?php if($this->mode == self::MODE_REACQUIRE){echo "checked=\"checked\"";} ?>> 記事投稿時にメタ情報の記述がない場合はトップページのメタ情報を取得して出力する</label>&nbsp;
<input type="submit" class="btn btn-primary" value="更新">
</form>
</p>

<p>
<textarea style="width:480px;height:180px; overflow: hidden;" onfocus="this.select();">
<!-- b_block:id="is_entry_description" -->
<meta name="description" b_block:id="entry_description">
<!-- /b_block:id="is_entry_description" -->

<!-- b_block:id="is_entry_keyword" -->
<meta name="keyword" b_block:id="entry_keyword">
<!-- /b_block:id="is_entry_keyword" -->
</textarea>
</p>
