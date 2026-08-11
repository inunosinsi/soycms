var advanced_textarea = function(jquery){
	this.$ = jquery;
	var textarea = jquery.get(0);

	textarea.inst = this;

	this.insertHTML = function(html){

		if (document.selection != null){
			textarea.focus();
			textarea.selection = document.selection.createRange();
			textarea.selection.text = html;
		}else{
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;

			var beforeString = textarea.value.substring(0,start);
			var afterString = textarea.value.substring(end);

			var scroll = textarea.scrollTop;
			var scrollLeft = textarea.scrollLeft;

			textarea.value = beforeString + html + afterString;

			textarea.scrollTop = scroll;
			textarea.scrollLeft = scrollLeft;

			textarea.setSelectionRange(start,start + html.length);

			textarea.focus();
		}
	};

	//タブの挿入
	this.insertTab = function(e){

		if (document.selection != null){
			textarea.selection = document.selection.createRange();

			var value = textarea.selection.text;

			if(textarea.selection.compareEndPoints('StartToEnd',textarea.selection) == 0){
				textarea.selection.text = String.fromCharCode(9);
			}else{
				if(e.shiftKey){
					value = value.replace( /\n\t/g, "\n" );
					if(value.substr( 0, 1 ) == "\t"){
						value = value.substr( 1, value.length-1 ) + "\n";
					}
				}else{
					value = value.replace( /\n/g, "\n\t" );
					value = "\t" + value + "\n";
				}

				textarea.selection.text = value;
			}
			return;
		}else{
			var start = textarea.selectionStart;
			var end = textarea.selectionEnd;

			var scroll = textarea.scrollTop;

			var beforeString = textarea.value.substring(0,start);
			var afterString = textarea.value.substring(end);

			if(start == end){
				textarea.value = beforeString + "\t" + afterString;
				textarea.scrollTop = scroll;
				textarea.setSelectionRange(start + 1,start + 1);
			}else{
				var value = textarea.value.substring(start,end);
				if(e.shiftKey){
					value = value.replace( /\n\t/g, "\n" );
					if(value.substr( 0, 1 ) == "\t"){
						value = value.substr( 1, value.length-1 );
					}
				}else{
					value = value.replace( /\n/g, "\n\t" );
					if(value.substr(value.length-1, 1) == "\t") {
						value = "\t" + value.substr( 0, value.length-2 ) + "\n";
					}else{
						value = "\t" + value;
					}
				}

				textarea.value = beforeString + value + afterString;
				textarea.scrollTop = scroll;
				textarea.setSelectionRange(start,start + value.length);
			}

			return;
		}
	};
};