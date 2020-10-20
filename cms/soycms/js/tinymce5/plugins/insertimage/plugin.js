(function() {
	tinymce.create('tinymce.plugins.InsertImagePlugin', {
		init : function(ed, url) {
			// Register commands
			/*
			ed.addCommand('mceInsertImage', function() {
				ed.windowManager.open({
					file : InsertImagePage ,
					width : 320,
					height : 240,
					inline : 1
				}, {
					plugin_url : url
				});
			});
			*/

			ed.addCommand("mceInsertImage", function() {
				ed.windowManager.open({
					title : "イメージを挿入",
					width : 320,
					height : 240,
					url : InsertImagePage,
					resizable : true
				}, {
					width : 320,
					height : 240,
					plugin_url : url
				});
			});

				
			// Register buttons
			ed.addButton('insertimage', {
				title : 'イメージを挿入', 
				cmd : 'mceInsertImage',
				image : url + '/img/icon.gif'
			});
		},

		getInfo : function() {
			return {
				longname : 'Insert Image',
				author : 'Nippon Institute of Agroinformatics Ltd.',
				authorurl : 'http://www.n-i-agroinformatics.com/',
				version : "0.0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('insertimage', tinymce.plugins.InsertImagePlugin);
})();