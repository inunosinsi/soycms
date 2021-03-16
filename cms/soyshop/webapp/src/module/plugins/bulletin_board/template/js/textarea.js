var advanced_textarea = function (a) {
	this.$ = a;
	var b = a.get(0);
	b.inst = this;
	this.insertHTML = function (a) {
		if (null != document.selection) {
			b.focus(), b.selection = document.selection.createRange(), b.selection.text = a;
		} else {
			var c = b.selectionStart,
				d = b.selectionEnd,
				e = b.value.substring(0, c),
				d = b.value.substring(d),
				f = b.scrollTop,
				g = b.scrollLeft;
			b.value = e + a + d;
			b.scrollTop = f;
			b.scrollLeft = g;
			b.setSelectionRange(c, c + a.length);
			b.focus()
		}
	};
	this.insertTab = function (a) {
		if (null != document.selection) {
			b.selection = document.selection.createRange();
			var c = b.selection.text;
			0 == b.selection.compareEndPoints("StartToEnd", b.selection) ? b.selection.text = String.fromCharCode(9) : (a.shiftKey ? (c = c.replace(/\n\t/g, "\n"), "\t" == c.substr(0, 1) && (c = c.substr(1, c.length - 1) + "\n")) : (c = c.replace(/\n/g, "\n\t"), c = "\t" + c + "\n"), b.selection.text = c)
		} else {
			var d = b.selectionStart;
			c = b.selectionEnd;
			var e = b.scrollTop,
				f = b.value.substring(0, d),
				g = b.value.substring(c);
			d == c ? (b.value = f + "\t" + g, b.scrollTop = e, b.setSelectionRange(d + 1, d + 1)) : (c = b.value.substring(d, c), a.shiftKey ? (c = c.replace(/\n\t/g, "\n"), "\t" == c.substr(0, 1) && (c = c.substr(1, c.length - 1))) : (c = c.replace(/\n/g, "\n\t"), c = "\t" == c.substr(c.length - 1, 1) ? "\t" + c.substr(0, c.length - 2) + "\n" : "\t" + c), b.value = f + c + g, b.scrollTop = e, b.setSelectionRange(d, d + c.length))
		}
	};
};

//拡張
$.extend(
	$.fn,{
	textarea : function(cond){
		return new advanced_textarea($(this));
	}
});

//初期化
$(".editor").each(function(){
	$(this).keydown(function(e){
		if(e.keyCode == 9){
			$(this).textarea().insertTab(e);
			return false;
		}
		return true;
	});
});

//インサートボタン
function bulletin_board_app_insert(mode){
	var editor = document.getElementById("editor");
	var sentence = editor.value;

	var pos = editor.selectionStart;
	var end = editor.selectionEnd;
	if(pos == end){
		var tag, add;
		switch(mode){
			case "b":
				tag = "<b></b>";
				break;
			case "quote":
				tag = "<quote></quote>";
				break;
			case "code":
			default:
				tag = "<code></code>";
				break;
		}

		var len = sentence.length;

		var before = sentence.substr(0, pos);
		var after = sentence.substr(pos, len);

		editor.value = before + tag + after;

		var add = mode.length + 2;

		editor.focus();
		editor.setSelectionRange(pos + add, pos + add);
	}else{	//範囲選択をしている場合
		var text = sentence.substr(pos, end);
		text = "<" + mode + ">" + text + "</" + mode + ">";
		var before = sentence.substr(0, pos);
		var after = sentence.substr(end);

		editor.value = before + text + after;
	}
}

//ボタンの登録
(function(){
	var modes = ["code", "quote", "b"];
	for(var i = 0; i < modes.length; i++){
		var mode = modes[i];
		var btn = document.getElementById("insert_" + mode);
		if(btn){
			btn.addEventListener("click", function(){
				bulletin_board_app_insert(this.id.replace("insert_", ""));
			});
		}
	}
})();
