<?php
/*
 * Created on 2009/06/12
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
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
<textarea style="width:480px;height:53px; overflow: hidden;" onfocus="this.select();">
<meta name="description" b_block:id="entry_description" />
<meta name="keyword" b_block:id="entry_keyword" />
</textarea>
</p>
