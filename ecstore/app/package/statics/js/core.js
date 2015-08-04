window.addEvent('domready', function() {
	GiftObj = {
		init: function() {
			var self = this,
			addgift, floatbox = document.getElement('.floatbox');
			$$('.gift-single').each(function(el, index) {
				if ((addgift = el.getElement('.addgift'))) addgift.addEvent('click', self.getObj.bind(self, el, null, null, null));
			});

			document.getElement('.giftGroup').addEvent('click', function(e) {
				if ($(e.target).hasClass('remove')) self.defGift($(e.target));
			});

			$$('.buy').addEvent('click', function(e) {
				// if ($$('.giftGroup li.selected').length != $$('.giftGroup li.gift-null').length) {
				// 	var floatbox = document.getElement('.floatbox');
				// 	floatbox['removeClass']('floatSelected');
				// 	floatbox.setStyles({
				// 		'visibility': 'visible',
				// 		'top': document.getElement('.GiftBorder').getSize().y / 2 - 22,
				// 		'opacity': 1
				// 	});
				// 	floatbox.fade.delay(1000, floatbox, 'out');
				// } else {
					document.getElement('.package-form input[name^=checkout]').disabled = ! e.target.hasClass('addcart') ? false: true;
					this.getParent('form').submit();
				// }
			});
		},
		getObj: function(el, spec, pid, giftPrice) {
			var src = el.getElement('.gift-image img').src,
				title = el.getElement('.gift-dec').get('text'),
				active = document.getElement('#GiftHead .active'),
				i = active ? active.get('data-i') - 1 : null,
				goods_id = el.getElement('input[name=goods_id]').value,
				price = giftPrice ? giftPrice: el.getElement('input[name=price]').value,
				obj = {
					img: '<img src=' + src + '/>',
					giftTitle: title,
					goods_id: goods_id,
					i: i,
					product_id: pid || 'null',
					price: '<span>' + price + '</span>',
					giftSpec: spec || ''
				};

			this.addGift(obj, el, i);
		},
		addGift: function(obj, el, i) {
			if ($$('.giftGroup li.selected').length === $$('.giftGroup li.gift-null').length) {
				var floatbox = document.getElement('.floatbox');
				floatbox['addClass']('floatSelected');
				floatbox.setStyles({
					'visibility': 'visible',
					'top': document.getElement('.GiftBorder').getSize().y / 2 - 22,
					'opacity': 1
				});
				floatbox.fade.delay(1000, floatbox, 'out');
				return;
			}

			var selectEl = el.getElement('.addinfo'),
			have_select = el.getElement('.have_select').addClass('selectedGift'),
			box = $$('.giftGroup .gift-null')[i];

			if (!box) {
				box = $$('.giftGroup .gift-null').filter(function(ell) {
					if (!ell.hasClass('selected')) return ell;
				})[0];
				obj['i'] = box.get('data-i').toInt() - 1;

				if (document.getElement('.GiftRepeat') && ! el.getElement('.nodisplay')) {return MessageBox.error('该捆绑销售客户必须挑选不同商品');}
			}
			var str = document.getElement('.gift-has').innerHTML.substitute(obj);

			if (!this.check(box)) {
				box.addClass('selected').store('giftnull', box.get('html')).store('selectedGift', el).innerHTML = str;
				selectEl.removeClass('add_over');
				this.updateInfo();

				(function() {
					have_select.removeClass('nodisplay').getParent('.gift-single');
					selectEl.addClass('add_over');
				}).delay(1000);
			}
		},
		defGift: function(el) {
			var box = el.getParent('.gift-null'),
			giftEl = box.retrieve('selectedGift'),
			have_select = giftEl.removeClass('selectedGift').getElement('.have_select');
			box.removeClass('selected').innerHTML = box.retrieve('giftnull');
			var flag = 0,
			val = have_select.getParent('.gift-image').get('data-id');
			box.getParent('.giftGroup').getElements('.goodsId').get('value').each(function(v) {
				if (v === val) flag++;
			});
			if (flag === 0)(function() {
				have_select.addClass('nodisplay');
			}).delay(1000);
			this.updateInfo();
		},
		updateInfo: function() {
			var count = $$('.giftGroup .gift-null').length,
			selectednum = $$('.giftGroup .selected') ? $$('.giftGroup .selected').length: 0,
			num = count - selectednum,
			selectedNum = document.getElement('.selectedNum'),
			info = document.getElement('.GiftBuy');
			if (!selectednum) return info.className = 'GiftBuy';
			info.getElement('.selectnum').set('text', selectednum);
			info.getElement('.num').set('text', num);
			if (!num) return info.className = 'GiftBuy GiftSuccess';
			info.className = 'GiftBuy GiftInfo';
		},
		check: function(box) {
			if (box && ! box.hasClass('selected')) return false;
			return MessageBox.error('已经选择,请先移除再选择');
		}
	};
	GiftObj.init();

});


Element.implement({
	toQueryString: function(filterEl, abs) {
		var queryString = [];
		this.getElements('input').each(function(el) {
			var type = el.type;
			if (!el.name || el.disabled || type == 'submit' || type == 'reset' || type == 'file' || type == 'image') return;
			if (filterEl) {
				if (!filterEl(el)) return;
			}
			var value = (el.get('tag') == 'select') ? el.getSelected().map(function(opt) {
                //IE->document.id
				return document.id(opt).value;
			}) : ((type == 'radio' || type == 'checkbox') && ! el.checked) ? null : el.get('value');

			if (el.getAttribute('filterhidden')) {
				el = $(el);
				var filterBox = el.getParent('.filter_panel').getElement('.filter_box');
				value = filterBox.toQueryString();
			}
			if (!value && abs) return;
			Array.from(value).each(function(val) {
				if (typeof val != 'undefined') queryString.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(val));
			});
		});
		return queryString.join('&');
	}
});

