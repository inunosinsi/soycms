(function() {
	tinymce.create('tinymce.plugins.InnerLinkPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceInsertLink', function() {
				ed.windowManager.open({
					file : InsertLinkAddress ,
					width : 320,
					height : 240,
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('innerlink', {
				title : 'リンクを挿入', 
				cmd : 'mceInsertLink',
				image : url + '/img/icon.gif'
				});
		},

		getInfo : function() {
			return {
				longname : 'Insert Link',
				author : 'Nippon Institute of Agroinformatics Ltd.',
				authorurl : 'http://www.n-i-agroinformatics.com/',
				version : "0.0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('innerlink', tinymce.plugins.InnerLinkPlugin);
})();