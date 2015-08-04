function getContent(el){
  return $(el).getParent('.dialogContent')||$(el).getParent('.content-main')||document.body;
}

var DatePickers = new Class({

    Implements: [Events, Options],

    options: {
        onShow: function(datepicker){
            datepicker.setStyle('visibility', 'visible');
        },
        onHide: function(datepicker){
            datepicker.setStyle('visibility', 'hidden').setStyle('left',-300);
        },
        showDelay: 100,
        hideDelay: 100,
        className: 'x-calendar',
        offsets: {x: 0, y: 20},

        dateformat: '-',

        days: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FIR', 'STA'], // days of the week starting at sunday
        months:LANG_Datepicker['months'],
        weekFirstDay : 1 // first day of the week: 0 = sunday, 1 = monday, etc..
    },

    initialize: function(){
        var params = Array.link(arguments, {options: Object.type, elements: $defined});
        this.setOptions(params.options || null);
        this.lock = false;

        this.datepicker = new Element('div').addEvents({
            'mouseover': function(){
                this.lock = true;
            }.bind(this),
            'mouseout': function(){
                this.lock = false;
            }.bind(this)
        }).inject(document.body);//for shopadmin

        if (this.options.className) this.datepicker.addClass(this.options.className);

        var top = new Element('div', {'class': 'x-datepicker-top'}).inject(this.datepicker);
        this.container = new Element('div', {'class': 'x-datepicker'}).inject(this.datepicker)
        .addEvent('click',function(e){e.stopPropagation();});
        var bottom = new Element('div', {'class': 'x-datepicker-bottom'}).inject(this.datepicker);

        this.datepicker.setStyles({position: 'absolute', top: 0, left: 0, visibility: 'hidden',zIndex:65535});

        if (params.elements){
            params.elements.each(function(el){
              el.store('bindColorPicker',true);

            });
            this.attach(params.elements);
        }
    },

    attach: function(elements){
        $$(elements).each(function(element){
            var dateformat = element.retrieve('datepicker:dateformat', element.get('accept'));
            if(!dateformat) {
                dateformat = this.options.dateformat;
                element.store('datepicker:dateformat', dateformat);
            }
            var datevalue = element.retrieve('datepicker:datevalue', element.get('value'));
            if(!datevalue) {
                datevalue = this.format(new Date(), dateformat);
                element.store('datepicker:datevalue', datevalue);
            }
            element.store('datepicker:current', this.unformat(datevalue,dateformat));

            var inputFocus = element.retrieve('datepicker:focus', this.elementFocus.bindWithEvent(this, element));
            var inputBlur = element.retrieve('datepicker:blur', this.elementBlur.bindWithEvent(this, element));
            element.addEvents({focus: inputFocus, blur: inputBlur});

            element.store('datepicker:native', element.get('accept'));
            element.erase('dateformat');
        }, this);
        return this;
    },

    detach: function(elements){
        $$(elements).each(function(element){
            element.removeEvent('onfocus', element.retrieve('datepicker:focus') || $empty);
            element.removeEvent('onblur', element.retrieve('datepicker:blur') || $empty);
            element.eliminate('datepicker:focus').eliminate('datepicker:blur');
            var original = element.retrieve('datepicker:native');
            if (original) element.set('dateformat', original);
        });
        return this;
    },

    elementFocus: function(event, element){
        if(!this.datepicker.retrieve('injected')){
           this.datepicker.inject(document.body);
           this.datepicker.store('injected',true);
        }
        this.el = element;

        var current = element.retrieve('datepicker:current');
        this.curFullYear = current[0];
        this.curMonth = current[1];
        this.curDate = current[2];

        this.build();

        this.timer = $clear(this.timer);
        this.timer = this.show.delay(this.options.showDelay, this);

        this.position({page: element.getPosition()});
    },

    elementChange: function() {

        if(this.el.get('real')){
            var curDateObj = new Date(this.curFullYear, this.curMonth, this.curDate);
            var now = new Date();

            if(curDateObj<new Date(now.getFullYear(),now.getMonth(),now.getDate())){
               return alert(LANG_Datepicker['dateerror']);
            }
        }


        this.el.store('datepicker:current', Array(this.curFullYear,this.curMonth,this.curDate));
        this.el.set('value',this.format(new Date(this.curFullYear, this.curMonth, this.curDate),this.el.retrieve('datepicker:dateformat')));

        $clear(this.timer);
        this.timer = this.hide.delay(this.options.hideDelay, this);
    },

    elementBlur: function(event){
        if(!this.lock) {
            $clear(this.timer);
            this.timer = this.hide.delay(this.options.hideDelay, this);
        }
    },

    position: function(event){
        var size = window.getSize(), scroll = window.getScroll();
        var datepicker = {x: this.datepicker.offsetWidth, y: this.datepicker.offsetHeight};
        var props = {x: 'left', y: 'top'};
        for (var z in props){
            var pos = event.page[z] + this.options.offsets[z];
            if ((pos + datepicker[z] - scroll[z]) > size[z]) pos = event.page[z] - this.options.offsets[z] - datepicker[z];
            this.datepicker.setStyle(props[z], pos);
        }
        if(window.ie){
    //       this.datepicker.setStyle('top',this.datepicker.getStyle('top').toInt()+getContent(this.el).getScrollTop());
        }
    },

    show: function(){
        this.fireEvent('show', this.datepicker);
    },

    hide: function(){
        this.fireEvent('hide', this.datepicker);
		if(this.el.retrieve('afterHide')){
			var chide=this.el.retrieve('afterHide');
			chide.call(this,this.el);
		}
    },

    build: function() {
        $A(this.container.childNodes).each(Element.dispose);

        var table = new Element('table').set({cellpadding:'0',cellspacing:'0'}).inject(this.container);
        var caption = this.caption().inject(table);
        var thead = this.thead().inject(table);
        var tbody = this.tbody().inject(table);
    },

    // navigate: calendar navigation
    // @param type (str) m or y for month or year
    // @param d (int) + or - for next or prev
    navigate: function(type, d) {
        switch (type) {
            case 'm': // month
                var i = this.curMonth + d;

                if (i < 0 || i == 12) {
                    this.curMonth = (i < 0) ? 11 : 0;
                    this.navigate('y', d);
                }
                else
                    this.curMonth = i;

                break;
            case 'y': // year
                this.curFullYear += d;

                break;
        }

        this.el.store('datepicker:current', Array(this.curFullYear,this.curMonth,this.curDate));

        this.el.focus(); // keep focus and do automatique rebuild ;)
    },

    // caption: returns the caption element with header and navigation
    // @returns caption (element)
    caption: function() {
        // start by assuming navigation is allowed
        var navigation = {
            prev: { 'month': true, 'year': true },
            next: { 'month': true, 'year': true }
        };

        var caption = new Element('caption');

        var prev = new Element('a').addClass('prev').appendText('\x3c'); // <
        var next = new Element('a').addClass('next').appendText('\x3e'); // >



        var year = new Element('span').addClass('year').inject(caption);
        if (navigation.prev.year) { prev.clone().addEvent('click', function() { this.navigate('y', -1); }.bind(this)).inject(year); }
        new Element('span').set('text', this.curFullYear).addEvent('mousewheel', function(e){ e.stop();this.navigate('y', (e.wheel < 0 ? -1 : 1));this.build();}.bind(this)).inject(year);
        if (navigation.next.year) { next.clone().addEvent('click', function() { this.navigate('y', 1); }.bind(this)).inject(year); }
        var month = new Element('span').addClass('month').inject(caption);
        if (navigation.prev.month) { prev.clone().addEvent('click', function() { this.navigate('m', -1); }.bind(this)).inject(month); }
        new Element('span').set('text', this.options.months[this.curMonth]).addEvent('mousewheel', function(e){ e.stop();this.navigate('m', (e.wheel < 0 ? -1 : 1));this.build();}.bind(this)).inject(month);
        if (navigation.next.month) { next.clone().addEvent('click', function() { this.navigate('m', 1); }.bind(this)).inject(month); }
        return caption;
    },

    // thead: returns the thead element with day names
    // @returns thead (element)
    thead: function() {
        var thead = new Element('thead');
        var tr = new Element('tr').inject(thead);
        for (i = 0; i < 7; i++) {
            new Element('th').set('text', this.options.days[(this.options.weekFirstDay + i)%7].substr(0, 3)).inject(tr);
        }

        return thead;
    },

    // tbody: returns the tbody element with day numbers
    // @returns tbody (element)
    tbody: function() {
        var d = new Date(this.curFullYear, this.curMonth, 1);

        var offset = ((d.getDay() - this.options.weekFirstDay) + 7) % 7; // day of the week (offset)
        var last = new Date(this.curFullYear, this.curMonth + 1, 0).getDate(); // last day of this month
        var prev = new Date(this.curFullYear, this.curMonth, 0).getDate(); // last day of previous month

        var v = (this.el.get('value')) ? this.unformat(this.el.get('value'),this.el.retrieve('datepicker:dateformat')) : false;
        var current = new Date(v[0], v[1], v[2]).getTime();
        var d = new Date();
        var today = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); // today obv

        var tbody = new Element('tbody');

        tbody.addEvent('mousewheel', function(e){
            e.stop(); // prevent the mousewheel from scrolling the page.
            this.navigate('m', (e.wheel < 0 ? -1 : 1));
            this.build();
        }.bind(this));

        for (var i = 1; i < 43; i++) { // 1 to 42 (6 x 7 or 6 weeks)
            if ((i - 1) % 7 == 0) { tr = new Element('tr').inject(tbody); } // each week is it's own table row

            var td = new Element('td').inject(tr);
            var day = i - offset;
            var date = new Date(this.curFullYear, this.curMonth, day);

            if (day < 1) { // last days of prev month
                day = prev + day;
                td.addClass('inactive');
            }
            else if (day > last) { // first days of next month
                day = day - last;
                td.addClass('inactive');
            }
            else {
                if(date.getTime() == current)  { td.addClass('hilite');  }
                else if (date.getTime() == today) { td.addClass('today');  } // add class for today

                td.addEvents({
                    'click': function(day) {
                        this.curDate = day;
                        this.elementChange();
                    }.bind(this, day),
                    'mouseover': function(td) {
                        td.addClass('hilite');
                    }.bind(this, td),
                    'mouseout': function(td, date) {
                        if(date.getTime() != current)
                            td.removeClass('hilite');
                    }.bind(this, [td, date])
                }).addClass('active');
            }

            td.set('text',day);
        }
        return tbody;
    },
    unformat: function(val, f) {
       var _dates=val.split(f);
       var dates=new Array(3);
       if(_dates.length<3||!_dates[0]||!_dates[1]||!_dates[2])
       return [new Date().getFullYear(),new Date().getMonth(),new Date().getDate()];
        dates[0] = _dates[0].toInt();
        dates[1] =_dates[1].toInt()-1;
        dates[2] =_dates[2].toInt();
       return dates;

    },
    format: function(date, format) {
        if (date) {
                var j = date.getDate(); // 1 - 31
                var w = date.getDay(); // 0 - 6
                var l = this.options.days[w]; // Sunday - Saturday
                var n = date.getMonth() + 1; // 1 - 12
                var f = this.options.months[n - 1]; // January - December
                var y = date.getFullYear() + ''; // 19xx - 20xx

                return [y,n,j].join(format);
            }else{
               return '';
            }
    }

});


Element.extend({
        makeCalable:function(options){
        if(this.retrieve('bindColorPicker'))return;
           return new DatePickers([this],options);
        }
});
