/*
 * zip2address.js
 * 複数カラムに対応の為、共通のコードはzip2address_common.jsに移設
 *
 * Copyright (c) 2010 Kazuhito Hokamura
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @author   Kazuhito Hokamura (http://webtech-walker.com/)
 * @version  0.0.1
 * @edit Tsuyoshi Saito (https://github.com/inunosinsi)
 *
 * This script inspired by jQuery.zip2addr. (https://github.com/kotarok/jQuery.zip2addr)
 * Thank you for kotarok.
 *
 */
(function(){
	// ##COLUMN_ID##はPHPでこのコードを読み込む際に置換されます
	var columnId = "##COLUMN_ID##";
	if(document.querySelector(".search-btn-"+columnId)){
		document.querySelector(".search-btn-"+columnId).addEventListener("click", function(e){
			click_search_button(columnId);
		});
	//自動検索モード
	}else{
		execute_auto_search(columnId);
	}
}());
