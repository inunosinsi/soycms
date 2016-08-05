(function() {
	//mceSOYCMSEmojiURLが定義されているかどうかで判断
	try{
		if(mceSOYCMSEmojiURL != undefined){
			tinymce.create('tinymce.plugins.soycms_emoji_plugin', {
				init : function(ed, url) {
					
		
								
					// Register commands
					ed.addCommand('mceSOYCMSEmoji', function() {
						ed.windowManager.open({
							file : mceSOYCMSEmojiURL ,
							width : 320,
							height : 310,
							inline : 1
						}, {
							plugin_url : url
						});
					});
		
					// Register buttons
					ed.addButton('soycms_emoji', {
						title : '絵文字を挿入', 
						cmd : 'mceSOYCMSEmoji',
						image : url + '/icon.gif'
					});
				},
		
				getInfo : function() {
					return {
						longname : 'SOYCMS emoji Plugin',
						author : 'Nippon Institute of Agroinformatics Ltd.',
						authorurl : 'http://www.n-i-agroinformatics.com/',
						version : "1.0.0"
					};
				}
			});
		
			// Register plugin
			tinymce.PluginManager.add('soycms_emoji', tinymce.plugins.soycms_emoji_plugin);
		}
	}catch(e){
		//
	}
})();