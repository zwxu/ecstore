(function() {
	var storeGcpt;

	var getGcptHTML = function() {
		if (storeGcpt) return storeGcpt;
		var nk = {
			WM: ['#000', '#444', '#666', '#999', '#ccc', '#eee', '#f3f3f3', '#fff'],
			aN: ['#f00', '#f90', '#ff0', '#0f0', '#0ff', '#00f', '#90f', '#f0f'],
			XM: ['#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#cfe2f3', '#d9d2e9', '#ead1dc', '#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#9fc5e8', '#b4a7d6', '#d5a6bd', '#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6fa8dc', '#8e7cc3', '#c27ba0', '#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3d85c6', '#674ea7', '#a64d79', '#990000', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#0b5394', '#351c75', '#741b47', '#660000', '#783f04', '#7f6000', '#274e13', '#0c343d', '#073763', '#20124d', '#4c1130']
		};

		var gps_u = '<div class="goog-palette">' + '<table cellspacing="0" cellpadding="0"  class="goog-palette-table">' + '<tbody class="goog-palette-body">';
		var gps_d = '</tbody></table></div>';

		var gp = '';
		$H(nk).each(function(colors, part) {
			gp += gps_u;
			colors.each(function(color, index) {
				var rgb = color.hexToRgb();
				if (index == 0) {
					gp += '<tr class="goog-palette-row">';
				}
				gp += '<td class="goog-palette-cell">' + '<div title="' + rgb + '" style="background-color: ' + rgb + ';" class="goog-palette-colorswatch"/></td>';
				if ((index + 1) % 8 == 0) {
					if ((index + 1) == colors.length) {
						gp += '</tr>';
					}
					else {
						gp += '</tr><tr class="goog-palette-row">';
					}
				}
			});
			gp += gps_d;
		});
		storeGcpt = gp;
		return gp;
	};

	GoogColorPicker = new Class({
		Implements: [Events, Options],
		options: {
			onSelect: Class.empty,
			onShow: Class.empty,
			offsets: {
				x: 10,
				y: 10
			}
		},
		initialize: function(el, options) {
			this.setOptions(options);
			this.el = $(el);

			this.showing = false;
			var that = this;
			this.el.addEvents({
				'click': function(e) {
					that.showPanel(e);
				},
				'mouseleave': function() {
					that.selecting = false;
					that.blurEl();
				},
				'mouseenter': function() {
					that.selecting = true;
				}
			});

		},
		onSelect: function(rgb, hex) {
			this.fireEvent('select', [hex, rgb, this.el]);
			this.selecting = false;
			this.hidePanel();
		},
		blurEl: function() {
			this.hd = this.hidePanel.delay(850, this);
		},
		position: function(event) {
			var size = window.getSize(),
			scroll = window.getScroll();
			var gcp_panel = {
				x: this.gcp_panel.offsetWidth,
				y: this.gcp_panel.offsetHeight
			};
			var props = {
				x: 'left',
				y: 'top'
			};
			for (var z in props) {
				var pos = event.page[z] + this.options.offsets[z];
				if ((pos + gcp_panel[z] - scroll[z]) > size[z]) pos = event.page[z] - this.options.offsets[z] - gcp_panel[z];
				this.gcp_panel.setStyle(props[z], pos);
			}
		},
		hidePanel: function() {
			if (this.selecting) return;
			if (this.gcp_panel) this.gcp_panel.setStyle('visibility', 'hidden');
		},
		showPanel: function(event) {
			var that = this;

			var el_uid = this.el.uid || Slick.uidOf(this.el);
			if (!$('gcp_panel' + el_uid)) {
				var gcp_panel = this.gcp_panel = $('gcp_panel' + el_uid) || new Element('div', {
					'id': 'gcp_panel' + el_uid,
					'class': 'goog-palette-panel'
				}).setHTML(getGcptHTML()).inject(document.body);

				gcp_panel.addEvents({
					'mousemove': function() {
						that.selecting = true;
					},
					'mouseleave': function() {
						that.selecting = false;
						that.blurEl();
					}
				});

				$ES('.goog-palette-cell', gcp_panel).addEvents({
					'mouseover': function(e) {
						this.addClass('goog-palette-cell-hover');
					},
					'mouseleave': function(e) {
						this.removeClass('goog-palette-cell-hover');
					},
					'click': function(e) {
						var rgbcolor = this.getElement('.goog-palette-colorswatch').get('title');
						var hexclor = rgbcolor.rgbToHex();
						that.onSelect(rgbcolor, hexclor);
					}

				});
			}
			this.position(event);
			this.fireEvent('show', this.el, this);
			$(event.target).store('associate', gcp_panel);
			this.gcp_panel.setStyle('visibility', 'visible');
		}
	});

})();

