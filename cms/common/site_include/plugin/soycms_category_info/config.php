<?php
/*
 * Created on 2009/10/30
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>

<p>
ラベルの詳細にあるメモに記載されている文字列をブログページのアーカイブページで表示することが出来ます。<br />
設定方法は下記のソースコードを任意の場所に記載してください。<br /><br />
b_block:id="category_description"は属性値として追加することが可能です。<br />
( このプラグインはブログページのカテゴリページ以外では動作しません )
</p>

<p>
<textarea style="width:390px;height:90px; overflow: hidden;" onfocus="this.select();">
<!-- b_block:id="is_description" -->
<span b_block:id="category_description"></span>
<!-- /b_block:id="is_description" -->
</textarea>
</p>