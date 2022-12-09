var ImageSelect = {

	id: null,
	image_info: null,
	showInfo: true,

	popup: function(id) {

		this.id = id;

		$("#imageSelectModal").modal();

		//if onPopup
		// if (window.onPopup) {
		// 	window.onPopup();
		// }
		//
		// $("#upload_form_el").trigger("popup", {
		// 	width: 640,
		// 	height: 480
		// });
	},


	clear: function(id) {

		this.id = id;

		var input = $("#" + this.id);
		var image = $("#image_select_preview_" + this.id);

		$(input).val("");
		$(image).hide();

	},


	notifyUpload: function(url) {

		var image = document.createElement("img");
		image.src = url;

		this.loadImage(url, function() {

			var size = ImageSelect.adjustSize(url);
			$(image).css(size);
			$(image).addClass("append_new");

			$("#select_image_list").prepend(image);

			$(image).css("opacity", "0.5");

			$(image).fadeTo("slow", 1.0, function() {
				$(image).removeClass("append_new");
			});

			$(image).bind("click", function() {
				ImageSelect.selectImage($(this));
			});
			$(image).bind("mouseover", function(event) {
				ImageSelect.showImageInfo(event, $(this));
			});
			$(image).bind("mouseout", function(event) {
				ImageSelect.clearImageInfo(event, $(this));
			});
		}, function() {
			alert("failed to upload! \n url=" + url);
		});
	},

	adjustAll: function() {

		//list
		$("#select_image_list img").each(function() {
			var size = ImageSelect.adjustSize($(this).attr("src"));
			$(this).css(size);

			$(this).bind("mouseover", function(event) {
				ImageSelect.showImageInfo(event, $(this));
			});

			$(this).bind("mouseout", function(event) {
				ImageSelect.clearImageInfo(event, $(this));
			});

			$(this).bind("click", function() {
				ImageSelect.selectImage($(this));
			});
		});

		//preview
		$(".image_select_preview").each(function() {
			var size = ImageSelect.adjustSize($(this).attr("src"));
			$(this).css(size);

			$(this).bind("mouseover", function(event) {
				ImageSelect.showImageInfo(event, $(this));
			});

			$(this).bind("mouseout", function(event) {
				ImageSelect.clearImageInfo(event, $(this));
			});
		});

		//mousemove
		$(window).bind("mousemove", function(event) {
			if (ImageSelect.image_info) {
				$(ImageSelect.image_info).css({
					"top": event.pageY + 10,
					"left": event.pageX + 10
				});
			}
		});
	},

	adjustSize: function(url) {
		var image = new Image;
		image.src = url;

		var width = 100;
		var height = image.height * 100 / image.width;

		return {
			"width": width,
			"height": height
		};
	},

	showImageInfo: function(event, ele) {

		if (!ImageSelect.showInfo) {
			return;
		}

		if (!this.image_info) {
			var div = document.createElement("div");
			$(div).attr("id", "select_image_info");
			this.image_info = div;

			$(document.body).append(div);
		}

		$(this.image_info).css({
			"top": event.pageY,
			"left": event.pageX
		});

		var url = $(ele).attr("src");
		var image = new Image;
		image.src = url;
		html = "Name : " + url.substr(url.lastIndexOf("/") + 1);
		html += "<br />";
		html += "size : " + image.width + " x " + image.height;

		$(this.image_info).html(html);

		$(this.image_info).show();
	},

	clearImageInfo: function() {
		if (this.image_info) {
			$(this.image_info).hide();
		}
	},


	selectImage: function(ele) {

		var input = $("#" + this.id);
		var image = $("#image_select_preview_" + this.id);
		var preview_link = $("#image_select_preview_link_" + this.id);

		var url = ele.attr("src");
		var size = this.adjustSize(url);

		input.val(url);
		image.attr("src", url);
		image.show();

		//link
		preview_link.attr("href", url);

		$(image).css(size);

		$("#imageSelectModalClose").click();
		//$("#upload_form_el").trigger("close");
	},

	loadImage: function(abspath, onLoad, onError, delay, timeout) {

		var img = new Image(),
			tick = 0;

		// ここから下を差し替え
		img.finish = false;
		img.onabort = img.onerror = function() {
			if (img.finish) {
				return;
			}
			img.finish = true;
			onError(abspath);
		};
		img.onload = function() {
			img.finish = true;
			if (window.opera && !img.complete) {
				onError(abspath);
				return;
			}
			onLoad(abspath);
		};
		img.src = abspath;
		if (!img.finish && timeout) {
			setTimeout(function() {
				if (img.finish) {
					return;
				}
				if (img.complete) {
					img.finish = true;
					if (img.width) {
						return;
					}
					onError(abspath);
					return;
				}
				if ((tick += delay) > timeout) {
					img.finish = true;
					onError(abspath);
					return;
				}
				setTimeout(arguments.callee, delay);
			}, 0);
		}
	}

};

$(function() {
	ImageSelect.adjustAll();
});


var doFileUpload = function(file, ele) {
	var url = location.href;
	var file = $("#" + file);
	var ele = $("#" + ele);

	file.upload(url, function(res) {
		alert(res.message);

		if (res.result < 0) {
			return;
		}

		ele.val(res.url).show();


		file.parent().html(file.parent().html());

	}, 'json');
};
/*
 * jQuery.upload v1.0.9
 *
 * Copyright (c) 2013 Bass Jobsen
 * http://www.w3masters.nl/
 * Dual licensed under the MIT and GPL licenses.
 *
 * Original written by Lagos (http://lagoscript.org/)
 *
 * Contributors:
 * Mr Rogers http://rcode5.wordpress.com/
 */

(function($) {

	var uuid = 0;

	$.fn.upload = function(url, data, callback, type) {
		var self = this,
			inputs, checkbox, checked,
			iframeName = 'jquery_upload' + ++uuid,
			iframe = $('<iframe name="' + iframeName + '" style="position:absolute;top:-9999px" />').appendTo('body'),
			form = '<form target="' + iframeName + '" method="post" enctype="multipart/form-data" />';

		if ($.isFunction(data)) {
			type = callback;
			callback = data;
			data = {};
		}

		checkbox = $('input:checkbox', this);
		checked = $('input:checked', this);
		form = self.wrapAll(form).parent('form').attr('action', url);

		// Make sure radios and checkboxes keep original values
		// (IE resets checkd attributes when appending)
		checkbox.removeAttr('checked');
		checked.attr('checked', true);

		inputs = createInputs(data);
		inputs = inputs ? $(inputs).appendTo(form) : null;

		form.submit(function() {
			iframe.on('load', function() {
				var data = handleData(this, type),
					checked = $('input:checked', self);

				form.after(self).remove();
				checkbox.removeAttr('checked');
				checked.attr('checked', true);
				if (inputs) {
					inputs.remove();
				}

				setTimeout(function() {
					iframe.remove();
					if (type === 'script') {
						$.globalEval(data);
					}
					if (callback) {
						callback.call(self, data);
					}
				}, 0);
			});
		}).submit();

		return this;
	};

	function createInputs(data) {
		return $.map(param(data), function(param) {
			var e = $(document.createElement('input'));
			e.attr('type', 'hidden');
			e.attr('name', param.name);
			e.attr('value', param.value);
			return e;
		});
	}

	function param(data) {
		if ($.isArray(data)) {
			return data;
		}
		var params = [];

		function add(name, value) {
			params.push({
				name: name,
				value: value
			});
		}

		if (typeof data === 'object') {
			$.each(data, function(name) {
				if ($.isArray(this)) {
					$.each(this, function() {
						add(name, this);
					});
				} else {
					add(name, $.isFunction(this) ? this() : this);
				}
			});
		} else if (typeof data === 'string') {
			$.each(data.split('&'), function() {
				var param = $.map(this.split('='), function(v) {
					return decodeURIComponent(v.replace(/\+/g, ' '));
				});

				add(param[0], param[1]);
			});
		}

		return params;
	}

	function handleData(iframe, type) {
		var data, contents = $(iframe).contents().get(0);

		if ($.isXMLDoc(contents) || contents.XMLDocument) {
			return contents.XMLDocument || contents;
		}
		data = $(contents).find('body').text();
		switch (type) {
			case 'xml':
				data = parseXml(data);
				break;
			case 'json':
				data = $.parseJSON(data);
				break;
		}
		return data;
	}

	function parseXml(text) {
		if (window.DOMParser) {
			return new DOMParser().parseFromString(text, 'application/xml');
		} else {
			var xml = new ActiveXObject('Microsoft.XMLDOM');
			xml.async = false;
			xml.loadXML(text);
			return xml;
		}
	}

})(jQuery);
