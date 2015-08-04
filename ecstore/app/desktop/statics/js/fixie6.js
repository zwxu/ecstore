/*此文件将只会在7以下版本的IE浏览器加载运行

  在core/admin/view/index.html中引入

*/

if (window.ie6) {
	(function() {
		var _fixSelectFrame = function(panel) {
			return $(panel).retrieve('iframe', new Element('iframe', {
				src: 'javascript:void(0);',
				styles: {
					position: 'absolute',
					zIndex: - 1,
					border: 'none',
					'filter': 'alpha(opacity=0)'
				}
			}).inject(panel)).setStyles({
				'top': 0,
				'left': 0,
				width: panel.offsetWidth,
				height: panel.offsetHeight
			});
		};

		Dialog = Class.refactor(Dialog || $empty, {

			showDialog: function() {
				var dialog = this.dialog;
				var FixSelectFrame = _fixSelectFrame(dialog);
				dialog.getElement('.dialog-head .btn-close').set('html', '×');
				dialog.addEvent('resize', function() {
					FixSelectFrame.setStyles({
						'top': 0,
						'left': 0,
						width: dialog.offsetWidth,
						height: dialog.offsetHeight
					});
				});
				this.previous.apply(this, arguments);
			}

		});

		Ex_Loader("picker",function(){
			try {
				GoogColorPicker = Class.refactor(GoogColorPicker, {

					initialize: function(el, options) {
						this.addEvent('show', function() {
							_fixSelectFrame(this.gcp_panel);
						});

						this.previous(el, options);
					}

				});

				DatePickers = Class.refactor(DatePickers, {
					initialize: function(els, options) {
						this.addEvent('show', _fixSelectFrame);
						this.previous(els, options);
					}
				});
			} catch(e) {}
		});

		Tips = Class.refactor(Tips, {
			initialize: function(els, options) {
				this.addEvent('show', _fixSelectFrame);
				this.previous(els, options);
			}
		});
	}) ();
}

var OverlayFix = new Class({
	initialize: function(el) {
		if (Browser.Engine.trident) {
			this.element = $(el);
			this.relative = this.element.getOffsetParent();
			this.fix = new Element('iframe', {
				'frameborder': '0',
				'scrolling': 'no',
				'src': 'javascript:false;',
				'styles': {
					'position': 'absolute',
					'border': 'none',
					'display': 'none',
					'filter': 'progid:DXImageTransform.Microsoft.Alpha(opacity=0)'
				}
			}).inject(this.element, 'after');
		}
	},

	show: function() {
		if (this.fix) {
			var coords = this.element.getCoordinates(this.relative);
			delete coords.right;
			delete coords.bottom;
			this.fix.setStyles($extend(coords, {
				'display': '',
				'zIndex': (this.element.getStyle('zIndex') || 1) - 1
			}));
		}
		return this;
	},

	hide: function() {
		if (this.fix) this.fix.setStyle('display', 'none');
		return this;
	},

	destroy: function() {
		if (this.fix) this.fix = this.fix.destroy();
	}

});
