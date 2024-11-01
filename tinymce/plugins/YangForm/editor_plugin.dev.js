
/* Ê¾ÀýJS */

(function() {
	tinymce.PluginManager.requireLangPack('YangForm');
	tinymce.create('tinymce.plugins.YangFormPlugin', {
		init : function(ed, url) {
			ed.addCommand('mceFormInsert', function() {
				ed.execCommand('mceInsertContent', 0, insertform('visual', ''));
			});
			ed.addButton('YangForm', {
				title : 'YangForm.insert_form',
				cmd : 'mceFormInsert',
				image : url + '/img/yang_form-32.png'
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('YangForm', n.nodeName == 'IMG');
			});
		},

		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
				longname : 'yang-files',
				author : 'yang',
				authorurl : 'http://yangjunwei.com',
				infourl : 'http://yangjunwei.com/a/1000.html',
				version : "1.00"
			};
		}
	});
	tinymce.PluginManager.add('YangForm', tinymce.plugins.YangFormPlugin);
})();