
// Glider 
Glider = Class.create();
Object.extend(Object.extend(Glider.prototype, Abstract.prototype), {
	initialize: function(wrapper, options){
        this.handStopped = false;
	    this.scrolling  = false;
	    this.wrapper    = $(wrapper);
	    this.scroller   = this.wrapper.down('div.scroller');
	    this.sections   = this.wrapper.getElementsBySelector('div.sectionslide');
	    this.options    = Object.extend({ duration: 1.0, frequency: 3 }, options || {});

	    this.sections.each( function(section, index) {
	      section._index = index;
	    });    

	    this.events = {
	      click: this.click.bind(this),
          mouseover: this.pause.bind(this),
          mouseout: this.resume.bind(this)
	    };

	    this.addObservers();
        if(this.options.initialSection) 
            this.moveTo(this.options.initialSection, this.scroller, { duration:this.options.duration });  // initialSection should be the id of the section you want to show up on load
        if(this.options.autoGlide) 
            this.start();
	  },
	
  addObservers: function() {
    this.wrapper.observe('mouseover', this.events.mouseover);
    this.wrapper.observe('mouseout', this.events.mouseout);
    
    var descriptions = this.wrapper.getElementsBySelector('div.sliderdescription');
    descriptions.invoke('observe', 'mouseover', this.makeActive);
    descriptions.invoke('observe', 'mouseout', this.makeInactive);
    
    var controls = this.wrapper.getElementsBySelector('div.slidercontrol a');
    controls.invoke('observe', 'mouseover', this.events.click);

  },

  click: function(event) {
    var element = Event.findElement(event, 'a');
    
    if (this.scrolling) this.scrolling.cancel();
    this.moveTo(element.href.split("#")[1], this.scroller, { duration:this.options.duration });  
     if (!this.handStopped) {
        clearTimeout(this.timer);
        this.timer = null;
      }
    Event.stop(event);
  },

  moveTo: function(element, container, options) {
    this.current = $(element);
    Position.prepare();
    var containerOffset = Position.cumulativeOffset(container);
    var elementOffset = Position.cumulativeOffset(this.current);

    this.scrolling = new Effect.SmoothScroll(container, {
      duration:options.duration, 
      x:(elementOffset[0]-containerOffset[0]), 
      y:(elementOffset[1]-containerOffset[1])
    });
    
    if (typeof element == 'object')
        element = element.id;
        
    this.toggleControl($$('a[href="#'+element+'"]')[0]);
    
    return false;
  },
        
  next: function(){
    if (this.current) {
      var currentIndex = this.current._index;
      var nextIndex = (this.sections.length - 1 == currentIndex) ? 0 : currentIndex + 1;      
    } else var nextIndex = 1;

    this.moveTo(this.sections[nextIndex], this.scroller, { 
      duration: this.options.duration
    });

  },
	
  previous: function(){
    if (this.current) {
      var currentIndex = this.current._index;
      var prevIndex = (currentIndex == 0) ? this.sections.length - 1 : 
       currentIndex - 1;
    } else var prevIndex = this.sections.length - 1;
    
    this.moveTo(this.sections[prevIndex], this.scroller, { 
      duration: this.options.duration
    });
  },
  
  makeActive: function(event)
  {
    var element = Event.findElement(event, 'div');
    element.addClassName('active');
  },
  
  makeInactive: function(event)
  {
    var element = Event.findElement(event, 'div');
    element.removeClassName('active');
  },
  
  toggleControl: function(el)
  {
    $$('.slidercontrol a').invoke('removeClassName', 'active');
    el.addClassName('active');
  },

	stop: function()
	{
        this.handStopped = true;
		clearTimeout(this.timer);
	},
	
	start: function()
	{
        this.handStopped = false;
		this.periodicallyUpdate();
	},
    
    pause: function()
    {
      if (!this.handStopped) {
        clearTimeout(this.timer);
        this.timer = null;
      }
    },
    
    resume: function()
    {
      if (!this.handStopped)
        this.periodicallyUpdate();
    },
		
	periodicallyUpdate: function()
	{ 
		if (this.timer != null) {
			clearTimeout(this.timer);
			this.next();
		}
		this.timer = setTimeout(this.periodicallyUpdate.bind(this), this.options.frequency*1000);
	}

});

Effect.SmoothScroll = Class.create();
Object.extend(Object.extend(Effect.SmoothScroll.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    var options = Object.extend({
      x:    0,
      y:    0,
      mode: 'absolute'
    } , arguments[1] || {}  );
    this.start(options);
  },
  setup: function() {
    if (this.options.continuous && !this.element._ext ) {
      this.element.cleanWhitespace();
      this.element._ext=true;
      this.element.appendChild(this.element.firstChild);
    }
   
    this.originalLeft=this.element.scrollLeft;
    this.originalTop=this.element.scrollTop;
   
    if(this.options.mode == 'absolute') {
      this.options.x -= this.originalLeft;
      this.options.y -= this.originalTop;
    } 
  },
  update: function(position) {   
    this.element.scrollLeft = this.options.x * position + this.originalLeft;
    this.element.scrollTop  = this.options.y * position + this.originalTop;
  }
});

// End of Glider




/*  Home Page New Product Slider */
var Slider = Class.create();
Slider.prototype = {
    options: {
        shift: 715
    },
    
    initialize: function(container, controlLeft, controlRight){
        this.animating = false;
        this.containerSize = {
            width: $(container).offsetWidth,
            height: $(container).offsetHeight
        },
        this.content = $(container).down();
        this.controlLeft = $(controlLeft);
        this.controlRight = $(controlRight);
        
        this.initControls();
    },
    
   initControls: function(){

        var lastItemLeft = this.content.childElements().last().positionedOffset()[0];
        var lastItemWidth = this.content.childElements().last().getWidth();
        var contentWidth = lastItemLeft + lastItemWidth + 8;

		if ((contentWidth) > 723){
       this.controlLeft.href = this.controlRight.href = 'javascript:void(0)';
        Event.observe(this.controlLeft,  'click', this.shiftLeft.bind(this));
        Event.observe(this.controlRight, 'click', this.shiftRight.bind(this));
        this.updateControls(1, 0);
		}else{this.updateControls(0, 0);}
    },
    
    shiftRight: function(){
        if (this.animating)
            return;
        
        var left = isNaN(parseInt(this.content.style.left)) ? 0 : parseInt(this.content.style.left);
        
        if ((left + this.options.shift) < 0) {
            var shift = this.options.shift;
            this.updateControls(1, 1);
        } else {
            var shift = Math.abs(left);
            this.updateControls(1, 0);
        }
        this.moveTo(shift);
    },
    
    shiftLeft: function(){
        if (this.animating)
            return;
        
        var left = isNaN(parseInt(this.content.style.left)) ? 0 : parseInt(this.content.style.left);
        
        var lastItemLeft = this.content.childElements().last().positionedOffset()[0];
        var lastItemWidth = this.content.childElements().last().getWidth();
        var contentWidth = lastItemLeft + lastItemWidth + 8;
        
        if ((contentWidth + left - this.options.shift) > this.containerSize.width) {
            var shift = this.options.shift;
            this.updateControls(1, 1);
        } else {
            var shift = contentWidth + left - this.containerSize.width;
            this.updateControls(0, 1);
        } 
        this.moveTo(-shift);
    },
    
    moveTo: function(shift){
        var scope = this;
                     
        this.animating = true;
        
        new Effect.Move(this.content, {
            x: shift,
            duration: 0.4,
            delay: 0,
            afterFinish: function(){
                scope.animating = false;
            }
        });
    },
    
    updateControls: function(left, right){
        if (!left)
            this.controlLeft.addClassName('disabled');
        else
            this.controlLeft.removeClassName('disabled');
        
        if (!right)
            this.controlRight.addClassName('disabled');
        else
            this.controlRight.removeClassName('disabled');
    }
}

/*  End of Home Page New Product Slider */

/* Home page Tab */
var TabBuilder = Class.create();
TabBuilder.prototype = {
    config:
    {
        effect: 'none',
        duration: 300,
        tabContainer: '.tab-container',
        tab: '.tab'
    },
    
    initialize: function(settings)
    {
        Object.extend(this.config, settings);
        $$(this.config.tabContainer).each(function(el){
            this.buildTabs(el)
                .setActiveTab(el, 0)
                .addObservers(el);
        }.bind(this))
    },
    
    buildTabs: function(container)
    {
        var tabs = new Element('ol')
            .addClassName('tabs');
            
        container.insert({'bottom': tabs});
        
        var tabsContent = new Element('div')
            .addClassName('content')
        
        tabs.insert({'after': tabsContent});
        
        $(container).select(this.config.tab).each(function(el) {
            var tab = $(el).select('.head')[0].wrap('li');
            tabs.insert({'bottom': tab});
            
            var tabContent = $(el).select('.content')[0];
            tabContent.removeClassName('content').addClassName('tab');
            tabsContent.insert({'bottom': tabContent});
            $(el).remove();
        })
        $$('.tabs li:first-child')[0].addClassName('first');
        $$('.tabs li:last-child')[0].addClassName('last');
        return this;
    },
    
    setActiveTab: function(container, index)
    {
        this._switchTabDisplay(container, index);
        return this;
    },
    
    addObservers: function(container)
    {
        var that = this;
        $(container).select('.tabs li').each(function(el, index) {
            el.observe('mouseover', function() {
                that.setActiveTab(container, index)
            });
            el.observe('mouseover', function(el) {
                $(this).addClassName('over');
            })
            el.observe('mouseout', function(el) {
                $(this).removeClassName('over');
            })
        })
        return this;
    },
    
    _switchTabDisplay: function(container, index)
    {
        $(container).select('.tabs li, .content .tab').invoke('removeClassName', 'active');
        $(container).select('.tabs li')[index].addClassName('active');
        $(container).select('.content .tab')[index].addClassName('active');
        $(container).select('.content .tab').invoke('setStyle', {'display': 'none'});
        $(container).select('.content .tab')[index].setStyle({'display': 'block'});
    }
    
}

jQuery(function () {
	jQuery(".top-myaccount").hover(function () {
	jQuery(this).addClass("over"); jQuery(this).children(".drop-menu").show();
	}, function () {
	jQuery(this).removeClass("over"); jQuery(this).children(".drop-menu").hide();
	});
	
	jQuery(".block-login").hover(function () {
	jQuery(this).addClass("over");
	jQuery(this).children(".drop-menu").show();
	}, function () {
	jQuery(this).removeClass("over"); 
	jQuery(this).children(".drop-menu").hide();
	});
}); 
/*
 * Lazy Load - jQuery plugin for lazy loading images
 *
 * Copyright (c) 2007-2012 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   http://www.appelsiini.net/projects/lazyload
 *
 * Version:  1.7.2
 *
 */
(function(a,b){$window=a(b),a.fn.lazyload=function(c){function f(){var b=0;d.each(function(){var c=a(this);if(e.skip_invisible&&!c.is(":visible"))return;if(!a.abovethetop(this,e)&&!a.leftofbegin(this,e))if(!a.belowthefold(this,e)&&!a.rightoffold(this,e))c.trigger("appear");else if(++b>e.failure_limit)return!1})}var d=this,e={threshold:0,failure_limit:0,event:"scroll",effect:"show",container:b,data_attribute:"original",skip_invisible:!0,appear:null,load:null};return c&&(undefined!==c.failurelimit&&(c.failure_limit=c.failurelimit,delete c.failurelimit),undefined!==c.effectspeed&&(c.effect_speed=c.effectspeed,delete c.effectspeed),a.extend(e,c)),$container=e.container===undefined||e.container===b?$window:a(e.container),0===e.event.indexOf("scroll")&&$container.bind(e.event,function(a){return f()}),this.each(function(){var b=this,c=a(b);b.loaded=!1,c.one("appear",function(){if(!this.loaded){if(e.appear){var f=d.length;e.appear.call(b,f,e)}a("<img />").bind("load",function(){c.hide().attr("src",c.data(e.data_attribute))[e.effect](e.effect_speed),b.loaded=!0;var f=a.grep(d,function(a){return!a.loaded});d=a(f);if(e.load){var g=d.length;e.load.call(b,g,e)}}).attr("src",c.data(e.data_attribute))}}),0!==e.event.indexOf("scroll")&&c.bind(e.event,function(a){b.loaded||c.trigger("appear")})}),$window.bind("resize",function(a){f()}),f(),this},a.belowthefold=function(c,d){var e;return d.container===undefined||d.container===b?e=$window.height()+$window.scrollTop():e=$container.offset().top+$container.height(),e<=a(c).offset().top-d.threshold},a.rightoffold=function(c,d){var e;return d.container===undefined||d.container===b?e=$window.width()+$window.scrollLeft():e=$container.offset().left+$container.width(),e<=a(c).offset().left-d.threshold},a.abovethetop=function(c,d){var e;return d.container===undefined||d.container===b?e=$window.scrollTop():e=$container.offset().top,e>=a(c).offset().top+d.threshold+a(c).height()},a.leftofbegin=function(c,d){var e;return d.container===undefined||d.container===b?e=$window.scrollLeft():e=$container.offset().left,e>=a(c).offset().left+d.threshold+a(c).width()},a.inviewport=function(b,c){return!a.rightofscreen(b,c)&&!a.leftofscreen(b,c)&&!a.belowthefold(b,c)&&!a.abovethetop(b,c)},a.extend(a.expr[":"],{"below-the-fold":function(c){return a.belowthefold(c,{threshold:0,container:b})},"above-the-top":function(c){return!a.belowthefold(c,{threshold:0,container:b})},"right-of-screen":function(c){return a.rightoffold(c,{threshold:0,container:b})},"left-of-screen":function(c){return!a.rightoffold(c,{threshold:0,container:b})},"in-viewport":function(c){return!a.inviewport(c,{threshold:0,container:b})},"above-the-fold":function(c){return!a.belowthefold(c,{threshold:0,container:b})},"right-of-fold":function(c){return a.rightoffold(c,{threshold:0,container:b})},"left-of-fold":function(c){return!a.rightoffold(c,{threshold:0,container:b})}})})(jQuery,window)function wppShowMenuPopup(objMenu, popupId)
{
    objMenu = $(objMenu.id); var popup = $(popupId); if (!popup) return;
    popup.style.display = 'block';
    objMenu.addClassName('active');
}

function wppPopupPos(objMenu, w)
{
    var pos = objMenu.cumulativeOffset();
    var wraper = $('custommenu');
    var posWraper = wraper.cumulativeOffset();
    var wWraper = wraper.getWidth() - CUSTOMMENU_POPUP_RIGHT_OFFSET_MIN;
    var xTop = pos.top - posWraper.top + CUSTOMMENU_POPUP_TOP_OFFSET;
    var xLeft = pos.left - posWraper.left;
    if ((xLeft + w) > wWraper) xLeft = wWraper - w;
    return {'top': xTop, 'left': xLeft};
}

function wppHideMenuPopup(element, event, popupId, menuId)
{
    element = $(element.id); var popup = $(popupId); if (!popup) return;
    var current_mouse_target = null;
    if (event.toElement)
    {
        current_mouse_target = event.toElement;
    }
    else if (event.relatedTarget)
    {
        current_mouse_target = event.relatedTarget;
    }
    if (!wppIsChildOf(element, current_mouse_target) && element != current_mouse_target)
    {
        if (!wppIsChildOf(popup, current_mouse_target) && popup != current_mouse_target)
        {
            popup.style.display = 'none';
            $(menuId).removeClassName('active');
        }
    }
}

function wppIsChildOf(parent, child)
{
    if (child != null)
    {
        while (child.parentNode)
        {
            if ((child = child.parentNode) == parent)
            {
                return true;
            }
        }
    }
    return false;
}
/**
 * @author feiwen
 */
(function($){
	$.fn.textSlider = function(settings){    
        settings = jQuery.extend({
        	speed : "normal",
			line : 2,
			timer : 1000
    	}, settings);
		return this.each(function() {
			$.fn.textSlider.scllor( $( this ), settings );
    	});
    }; 
	$.fn.textSlider.scllor = function($this, settings){
		//alert($this.html());
		var ul = $( "ul:eq(0)", $this );
		var timerID;
		var li = ul.children();
		var _btnUp=$(".up:eq(0)", $this)
		var _btnDown=$(".down:eq(0)", $this)
		var liHight=$(li[0]).height();
		var upHeight=0-settings.line*liHight;//滚动的高度；
		var scrollUp=function(){
			_btnUp.unbind("click",scrollUp);
			ul.animate({marginTop:upHeight},settings.speed,function(){
				for(i=0;i<settings.line;i++){
                	 //$(li[i]).appendTo(ul);
					 ul.find("li:first").appendTo(ul);
					// alert(ul.html());
                }
               	ul.css({marginTop:0});
                _btnUp.bind("click",scrollUp); //Shawphy:绑定向上按钮的点击事件
			});	
		};
		var scrollDown=function(){
			_btnDown.unbind("click",scrollDown);
			ul.css({marginTop:upHeight});
			for(i=0;i<settings.line;i++){
				ul.find("li:last").prependTo(ul);
            }
			ul.animate({marginTop:0},settings.speed,function(){
                _btnDown.bind("click",scrollDown); //Shawphy:绑定向上按钮的点击事件
			});	
		};
		var autoPlay=function(){
			timerID = window.setInterval(scrollUp,settings.timer);
			//alert(settings.timer);
		};
		var autoStop = function(){
            window.clearInterval(timerID);
        };
		//事件绑定
		ul.hover(autoStop,autoPlay).mouseout();
		_btnUp.css("cursor","pointer").click( scrollUp );
		_btnUp.hover(autoStop,autoPlay);
		_btnDown.css("cursor","pointer").click( scrollDown );
		_btnDown.hover(autoStop,autoPlay)
	};
})(jQuery);
jQuery(function () 
{
var scrtime;
jQuery("#reviewed_box").hover(function () 
{
clearInterval(scrtime);
}, 
function ()
 {
 scrtime = setInterval(function () 
 {
 var $ul = jQuery("#reviewed_box .items-content"), 
 liHeight = $ul.find("li:last").height();
 $ul.animate({marginTop: liHeight + 1 + "px"}, 500, function () {
 $ul.find("li:last").prependTo($ul);$ul.find("li:first").hide();
 $ul.css({marginTop: 0});$ul.find("li:first").fadeIn(1000);});}, 3000);}).trigger("mouseleave");
 
var scrtimes;
jQuery("#neworder_box").hover(function () 
{
clearInterval(scrtimes);
}, 
function ()
 {
 scrtimes = setInterval(function () 
 {
 var $uls = jQuery("#neworder_box .items-content"), 
 liHeights = $uls.find("li:last").height();
 $uls.animate({marginTop: liHeights + 1 + "px"}, 500, function () {
 $uls.find("li:last").prependTo($uls);$uls.find("li:first").hide();
 $uls.css({marginTop: 0});$uls.find("li:first").fadeIn(1000);});}, 3000);}).trigger("mouseleave");

 });
 
 
 

 // Glider 
Glider = Class.create();
Object.extend(Object.extend(Glider.prototype, Abstract.prototype), {
	initialize: function(wrapper, options){
        this.handStopped = false;
	    this.scrolling  = false;
	    this.wrapper    = $(wrapper);
	    this.scroller   = this.wrapper.down('div.scroller');
	    this.sections   = this.wrapper.getElementsBySelector('div.sectionslide');
	    this.options    = Object.extend({ duration: 1.0, frequency: 3 }, options || {});

	    this.sections.each( function(section, index) {
	      section._index = index;
	    });    

	    this.events = {
	      click: this.click.bind(this),
          mouseover: this.pause.bind(this),
          mouseout: this.resume.bind(this)
	    };

	    this.addObservers();
        if(this.options.initialSection) 
            this.moveTo(this.options.initialSection, this.scroller, { duration:this.options.duration });  // initialSection should be the id of the section you want to show up on load
        if(this.options.autoGlide) 
            this.start();
	  },
	
  addObservers: function() {
    this.wrapper.observe('mouseover', this.events.mouseover);
    this.wrapper.observe('mouseout', this.events.mouseout);
    
    var descriptions = this.wrapper.getElementsBySelector('div.sliderdescription');
    descriptions.invoke('observe', 'mouseover', this.makeActive);
    descriptions.invoke('observe', 'mouseout', this.makeInactive);
    
    var controls = this.wrapper.getElementsBySelector('div.slidercontrol a');
    controls.invoke('observe', 'mouseover', this.events.click);

  },

  click: function(event) {
    var element = Event.findElement(event, 'a');
    
    if (this.scrolling) this.scrolling.cancel();
    this.moveTo(element.href.split("#")[1], this.scroller, { duration:this.options.duration });  
     if (!this.handStopped) {
        clearTimeout(this.timer);
        this.timer = null;
      }
    Event.stop(event);
  },

  moveTo: function(element, container, options) {
    this.current = $(element);
    Position.prepare();
    var containerOffset = Position.cumulativeOffset(container);
    var elementOffset = Position.cumulativeOffset(this.current);

    this.scrolling = new Effect.SmoothScroll(container, {
      duration:options.duration, 
      x:(elementOffset[0]-containerOffset[0]), 
      y:(elementOffset[1]-containerOffset[1])
    });
    
    if (typeof element == 'object')
        element = element.id;
        
    this.toggleControl($$('a[href="#'+element+'"]')[0]);
    
    return false;
  },
        
  next: function(){
    if (this.current) {
      var currentIndex = this.current._index;
      var nextIndex = (this.sections.length - 1 == currentIndex) ? 0 : currentIndex + 1;      
    } else var nextIndex = 1;

    this.moveTo(this.sections[nextIndex], this.scroller, { 
      duration: this.options.duration
    });

  },
	
  previous: function(){
    if (this.current) {
      var currentIndex = this.current._index;
      var prevIndex = (currentIndex == 0) ? this.sections.length - 1 : 
       currentIndex - 1;
    } else var prevIndex = this.sections.length - 1;
    
    this.moveTo(this.sections[prevIndex], this.scroller, { 
      duration: this.options.duration
    });
  },
  
  makeActive: function(event)
  {
    var element = Event.findElement(event, 'div');
    element.addClassName('active');
  },
  
  makeInactive: function(event)
  {
    var element = Event.findElement(event, 'div');
    element.removeClassName('active');
  },
  
  toggleControl: function(el)
  {
    $$('.slidercontrol a').invoke('removeClassName', 'active');
    el.addClassName('active');
  },

	stop: function()
	{
        this.handStopped = true;
		clearTimeout(this.timer);
	},
	
	start: function()
	{
        this.handStopped = false;
		this.periodicallyUpdate();
	},
    
    pause: function()
    {
      if (!this.handStopped) {
        clearTimeout(this.timer);
        this.timer = null;
      }
    },
    
    resume: function()
    {
      if (!this.handStopped)
        this.periodicallyUpdate();
    },
		
	periodicallyUpdate: function()
	{ 
		if (this.timer != null) {
			clearTimeout(this.timer);
			this.next();
		}
		this.timer = setTimeout(this.periodicallyUpdate.bind(this), this.options.frequency*1000);
	}

});

Effect.SmoothScroll = Class.create();
Object.extend(Object.extend(Effect.SmoothScroll.prototype, Effect.Base.prototype), {
  initialize: function(element) {
    this.element = $(element);
    var options = Object.extend({
      x:    0,
      y:    0,
      mode: 'absolute'
    } , arguments[1] || {}  );
    this.start(options);
  },
  setup: function() {
    if (this.options.continuous && !this.element._ext ) {
      this.element.cleanWhitespace();
      this.element._ext=true;
      this.element.appendChild(this.element.firstChild);
    }
   
    this.originalLeft=this.element.scrollLeft;
    this.originalTop=this.element.scrollTop;
   
    if(this.options.mode == 'absolute') {
      this.options.x -= this.originalLeft;
      this.options.y -= this.originalTop;
    } 
  },
  update: function(position) {   
    this.element.scrollLeft = this.options.x * position + this.originalLeft;
    this.element.scrollTop  = this.options.y * position + this.originalTop;
  }
});

// End of Glider
/*!
 * jCarousel - Riding carousels with jQuery
 *   http://sorgalla.com/jcarousel/
 *
 * Copyright (c) 2006 Jan Sorgalla (http://sorgalla.com)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Built on top of the jQuery library
 *   http://jquery.com
 *
 * Inspired by the "Carousel Component" by Bill Scott
 *   http://billwscott.com/carousel/
 */

(function(g){var q={vertical:!1,rtl:!1,start:1,offset:1,size:null,scroll:3,visible:null,animation:"normal",easing:"swing",auto:0,wrap:null,initCallback:null,setupCallback:null,reloadCallback:null,itemLoadCallback:null,itemFirstInCallback:null,itemFirstOutCallback:null,itemLastInCallback:null,itemLastOutCallback:null,itemVisibleInCallback:null,itemVisibleOutCallback:null,animationStepCallback:null,buttonNextHTML:"<div></div>",buttonPrevHTML:"<div></div>",buttonNextEvent:"click",buttonPrevEvent:"click", buttonNextCallback:null,buttonPrevCallback:null,itemFallbackDimension:null},m=!1;g(window).bind("load.jcarousel",function(){m=!0});g.jcarousel=function(a,c){this.options=g.extend({},q,c||{});this.autoStopped=this.locked=!1;this.buttonPrevState=this.buttonNextState=this.buttonPrev=this.buttonNext=this.list=this.clip=this.container=null;if(!c||c.rtl===void 0)this.options.rtl=(g(a).attr("dir")||g("html").attr("dir")||"").toLowerCase()=="rtl";this.wh=!this.options.vertical?"width":"height";this.lt=!this.options.vertical? this.options.rtl?"right":"left":"top";for(var b="",d=a.className.split(" "),f=0;f<d.length;f++)if(d[f].indexOf("jcarousel-skin")!=-1){g(a).removeClass(d[f]);b=d[f];break}a.nodeName.toUpperCase()=="UL"||a.nodeName.toUpperCase()=="OL"?(this.list=g(a),this.clip=this.list.parents(".jcarousel-clip"),this.container=this.list.parents(".jcarousel-container")):(this.container=g(a),this.list=this.container.find("ul,ol").eq(0),this.clip=this.container.find(".jcarousel-clip"));if(this.clip.size()===0)this.clip= this.list.wrap("<div></div>").parent();if(this.container.size()===0)this.container=this.clip.wrap("<div></div>").parent();b!==""&&this.container.parent()[0].className.indexOf("jcarousel-skin")==-1&&this.container.wrap('<div class=" '+b+'"></div>');this.buttonPrev=g(".jcarousel-prev",this.container);if(this.buttonPrev.size()===0&&this.options.buttonPrevHTML!==null)this.buttonPrev=g(this.options.buttonPrevHTML).appendTo(this.container);this.buttonPrev.addClass(this.className("jcarousel-prev"));this.buttonNext= g(".jcarousel-next",this.container);if(this.buttonNext.size()===0&&this.options.buttonNextHTML!==null)this.buttonNext=g(this.options.buttonNextHTML).appendTo(this.container);this.buttonNext.addClass(this.className("jcarousel-next"));this.clip.addClass(this.className("jcarousel-clip")).css({position:"relative"});this.list.addClass(this.className("jcarousel-list")).css({overflow:"hidden",position:"relative",top:0,margin:0,padding:0}).css(this.options.rtl?"right":"left",0);this.container.addClass(this.className("jcarousel-container")).css({position:"relative"}); !this.options.vertical&&this.options.rtl&&this.container.addClass("jcarousel-direction-rtl").attr("dir","rtl");var j=this.options.visible!==null?Math.ceil(this.clipping()/this.options.visible):null,b=this.list.children("li"),e=this;if(b.size()>0){var h=0,i=this.options.offset;b.each(function(){e.format(this,i++);h+=e.dimension(this,j)});this.list.css(this.wh,h+100+"px");if(!c||c.size===void 0)this.options.size=b.size()}this.container.css("display","block");this.buttonNext.css("display","block");this.buttonPrev.css("display", "block");this.funcNext=function(){e.next()};this.funcPrev=function(){e.prev()};this.funcResize=function(){e.resizeTimer&&clearTimeout(e.resizeTimer);e.resizeTimer=setTimeout(function(){e.reload()},100)};this.options.initCallback!==null&&this.options.initCallback(this,"init");!m&&g.browser.safari?(this.buttons(!1,!1),g(window).bind("load.jcarousel",function(){e.setup()})):this.setup()};var f=g.jcarousel;f.fn=f.prototype={jcarousel:"0.2.8"};f.fn.extend=f.extend=g.extend;f.fn.extend({setup:function(){this.prevLast= this.prevFirst=this.last=this.first=null;this.animating=!1;this.tail=this.resizeTimer=this.timer=null;this.inTail=!1;if(!this.locked){this.list.css(this.lt,this.pos(this.options.offset)+"px");var a=this.pos(this.options.start,!0);this.prevFirst=this.prevLast=null;this.animate(a,!1);g(window).unbind("resize.jcarousel",this.funcResize).bind("resize.jcarousel",this.funcResize);this.options.setupCallback!==null&&this.options.setupCallback(this)}},reset:function(){this.list.empty();this.list.css(this.lt, "0px");this.list.css(this.wh,"10px");this.options.initCallback!==null&&this.options.initCallback(this,"reset");this.setup()},reload:function(){this.tail!==null&&this.inTail&&this.list.css(this.lt,f.intval(this.list.css(this.lt))+this.tail);this.tail=null;this.inTail=!1;this.options.reloadCallback!==null&&this.options.reloadCallback(this);if(this.options.visible!==null){var a=this,c=Math.ceil(this.clipping()/this.options.visible),b=0,d=0;this.list.children("li").each(function(f){b+=a.dimension(this, c);f+1<a.first&&(d=b)});this.list.css(this.wh,b+"px");this.list.css(this.lt,-d+"px")}this.scroll(this.first,!1)},lock:function(){this.locked=!0;this.buttons()},unlock:function(){this.locked=!1;this.buttons()},size:function(a){if(a!==void 0)this.options.size=a,this.locked||this.buttons();return this.options.size},has:function(a,c){if(c===void 0||!c)c=a;if(this.options.size!==null&&c>this.options.size)c=this.options.size;for(var b=a;b<=c;b++){var d=this.get(b);if(!d.length||d.hasClass("jcarousel-item-placeholder"))return!1}return!0}, get:function(a){return g(">.jcarousel-item-"+a,this.list)},add:function(a,c){var b=this.get(a),d=0,p=g(c);if(b.length===0)for(var j,e=f.intval(a),b=this.create(a);;){if(j=this.get(--e),e<=0||j.length){e<=0?this.list.prepend(b):j.after(b);break}}else d=this.dimension(b);p.get(0).nodeName.toUpperCase()=="LI"?(b.replaceWith(p),b=p):b.empty().append(c);this.format(b.removeClass(this.className("jcarousel-item-placeholder")),a);p=this.options.visible!==null?Math.ceil(this.clipping()/this.options.visible): null;d=this.dimension(b,p)-d;a>0&&a<this.first&&this.list.css(this.lt,f.intval(this.list.css(this.lt))-d+"px");this.list.css(this.wh,f.intval(this.list.css(this.wh))+d+"px");return b},remove:function(a){var c=this.get(a);if(c.length&&!(a>=this.first&&a<=this.last)){var b=this.dimension(c);a<this.first&&this.list.css(this.lt,f.intval(this.list.css(this.lt))+b+"px");c.remove();this.list.css(this.wh,f.intval(this.list.css(this.wh))-b+"px")}},next:function(){this.tail!==null&&!this.inTail?this.scrollTail(!1): this.scroll((this.options.wrap=="both"||this.options.wrap=="last")&&this.options.size!==null&&this.last==this.options.size?1:this.first+this.options.scroll)},prev:function(){this.tail!==null&&this.inTail?this.scrollTail(!0):this.scroll((this.options.wrap=="both"||this.options.wrap=="first")&&this.options.size!==null&&this.first==1?this.options.size:this.first-this.options.scroll)},scrollTail:function(a){if(!this.locked&&!this.animating&&this.tail){this.pauseAuto();var c=f.intval(this.list.css(this.lt)), c=!a?c-this.tail:c+this.tail;this.inTail=!a;this.prevFirst=this.first;this.prevLast=this.last;this.animate(c)}},scroll:function(a,c){!this.locked&&!this.animating&&(this.pauseAuto(),this.animate(this.pos(a),c))},pos:function(a,c){var b=f.intval(this.list.css(this.lt));if(this.locked||this.animating)return b;this.options.wrap!="circular"&&(a=a<1?1:this.options.size&&a>this.options.size?this.options.size:a);for(var d=this.first>a,g=this.options.wrap!="circular"&&this.first<=1?1:this.first,j=d?this.get(g): this.get(this.last),e=d?g:g-1,h=null,i=0,k=!1,l=0;d?--e>=a:++e<a;){h=this.get(e);k=!h.length;if(h.length===0&&(h=this.create(e).addClass(this.className("jcarousel-item-placeholder")),j[d?"before":"after"](h),this.first!==null&&this.options.wrap=="circular"&&this.options.size!==null&&(e<=0||e>this.options.size)))j=this.get(this.index(e)),j.length&&(h=this.add(e,j.clone(!0)));j=h;l=this.dimension(h);k&&(i+=l);if(this.first!==null&&(this.options.wrap=="circular"||e>=1&&(this.options.size===null||e<= this.options.size)))b=d?b+l:b-l}for(var g=this.clipping(),m=[],o=0,n=0,j=this.get(a-1),e=a;++o;){h=this.get(e);k=!h.length;if(h.length===0){h=this.create(e).addClass(this.className("jcarousel-item-placeholder"));if(j.length===0)this.list.prepend(h);else j[d?"before":"after"](h);if(this.first!==null&&this.options.wrap=="circular"&&this.options.size!==null&&(e<=0||e>this.options.size))j=this.get(this.index(e)),j.length&&(h=this.add(e,j.clone(!0)))}j=h;l=this.dimension(h);if(l===0)throw Error("jCarousel: No width/height set for items. This will cause an infinite loop. Aborting..."); this.options.wrap!="circular"&&this.options.size!==null&&e>this.options.size?m.push(h):k&&(i+=l);n+=l;if(n>=g)break;e++}for(h=0;h<m.length;h++)m[h].remove();i>0&&(this.list.css(this.wh,this.dimension(this.list)+i+"px"),d&&(b-=i,this.list.css(this.lt,f.intval(this.list.css(this.lt))-i+"px")));i=a+o-1;if(this.options.wrap!="circular"&&this.options.size&&i>this.options.size)i=this.options.size;if(e>i){o=0;e=i;for(n=0;++o;){h=this.get(e--);if(!h.length)break;n+=this.dimension(h);if(n>=g)break}}e=i-o+ 1;this.options.wrap!="circular"&&e<1&&(e=1);if(this.inTail&&d)b+=this.tail,this.inTail=!1;this.tail=null;if(this.options.wrap!="circular"&&i==this.options.size&&i-o+1>=1&&(d=f.intval(this.get(i).css(!this.options.vertical?"marginRight":"marginBottom")),n-d>g))this.tail=n-g-d;if(c&&a===this.options.size&&this.tail)b-=this.tail,this.inTail=!0;for(;a-- >e;)b+=this.dimension(this.get(a));this.prevFirst=this.first;this.prevLast=this.last;this.first=e;this.last=i;return b},animate:function(a,c){if(!this.locked&& !this.animating){this.animating=!0;var b=this,d=function(){b.animating=!1;a===0&&b.list.css(b.lt,0);!b.autoStopped&&(b.options.wrap=="circular"||b.options.wrap=="both"||b.options.wrap=="last"||b.options.size===null||b.last<b.options.size||b.last==b.options.size&&b.tail!==null&&!b.inTail)&&b.startAuto();b.buttons();b.notify("onAfterAnimation");if(b.options.wrap=="circular"&&b.options.size!==null)for(var c=b.prevFirst;c<=b.prevLast;c++)c!==null&&!(c>=b.first&&c<=b.last)&&(c<1||c>b.options.size)&&b.remove(c)}; this.notify("onBeforeAnimation");if(!this.options.animation||c===!1)this.list.css(this.lt,a+"px"),d();else{var f=!this.options.vertical?this.options.rtl?{right:a}:{left:a}:{top:a},d={duration:this.options.animation,easing:this.options.easing,complete:d};if(g.isFunction(this.options.animationStepCallback))d.step=this.options.animationStepCallback;this.list.animate(f,d)}}},startAuto:function(a){if(a!==void 0)this.options.auto=a;if(this.options.auto===0)return this.stopAuto();if(this.timer===null){this.autoStopped= !1;var c=this;this.timer=window.setTimeout(function(){c.next()},this.options.auto*1E3)}},stopAuto:function(){this.pauseAuto();this.autoStopped=!0},pauseAuto:function(){if(this.timer!==null)window.clearTimeout(this.timer),this.timer=null},buttons:function(a,c){if(a==null&&(a=!this.locked&&this.options.size!==0&&(this.options.wrap&&this.options.wrap!="first"||this.options.size===null||this.last<this.options.size),!this.locked&&(!this.options.wrap||this.options.wrap=="first")&&this.options.size!==null&& this.last>=this.options.size))a=this.tail!==null&&!this.inTail;if(c==null&&(c=!this.locked&&this.options.size!==0&&(this.options.wrap&&this.options.wrap!="last"||this.first>1),!this.locked&&(!this.options.wrap||this.options.wrap=="last")&&this.options.size!==null&&this.first==1))c=this.tail!==null&&this.inTail;var b=this;this.buttonNext.size()>0?(this.buttonNext.unbind(this.options.buttonNextEvent+".jcarousel",this.funcNext),a&&this.buttonNext.bind(this.options.buttonNextEvent+".jcarousel",this.funcNext), this.buttonNext[a?"removeClass":"addClass"](this.className("jcarousel-next-disabled")).attr("disabled",a?!1:!0),this.options.buttonNextCallback!==null&&this.buttonNext.data("jcarouselstate")!=a&&this.buttonNext.each(function(){b.options.buttonNextCallback(b,this,a)}).data("jcarouselstate",a)):this.options.buttonNextCallback!==null&&this.buttonNextState!=a&&this.options.buttonNextCallback(b,null,a);this.buttonPrev.size()>0?(this.buttonPrev.unbind(this.options.buttonPrevEvent+".jcarousel",this.funcPrev), c&&this.buttonPrev.bind(this.options.buttonPrevEvent+".jcarousel",this.funcPrev),this.buttonPrev[c?"removeClass":"addClass"](this.className("jcarousel-prev-disabled")).attr("disabled",c?!1:!0),this.options.buttonPrevCallback!==null&&this.buttonPrev.data("jcarouselstate")!=c&&this.buttonPrev.each(function(){b.options.buttonPrevCallback(b,this,c)}).data("jcarouselstate",c)):this.options.buttonPrevCallback!==null&&this.buttonPrevState!=c&&this.options.buttonPrevCallback(b,null,c);this.buttonNextState= a;this.buttonPrevState=c},notify:function(a){var c=this.prevFirst===null?"init":this.prevFirst<this.first?"next":"prev";this.callback("itemLoadCallback",a,c);this.prevFirst!==this.first&&(this.callback("itemFirstInCallback",a,c,this.first),this.callback("itemFirstOutCallback",a,c,this.prevFirst));this.prevLast!==this.last&&(this.callback("itemLastInCallback",a,c,this.last),this.callback("itemLastOutCallback",a,c,this.prevLast));this.callback("itemVisibleInCallback",a,c,this.first,this.last,this.prevFirst, this.prevLast);this.callback("itemVisibleOutCallback",a,c,this.prevFirst,this.prevLast,this.first,this.last)},callback:function(a,c,b,d,f,j,e){if(!(this.options[a]==null||typeof this.options[a]!="object"&&c!="onAfterAnimation")){var h=typeof this.options[a]=="object"?this.options[a][c]:this.options[a];if(g.isFunction(h)){var i=this;if(d===void 0)h(i,b,c);else if(f===void 0)this.get(d).each(function(){h(i,this,d,b,c)});else for(var a=function(a){i.get(a).each(function(){h(i,this,a,b,c)})},k=d;k<=f;k++)k!== null&&!(k>=j&&k<=e)&&a(k)}}},create:function(a){return this.format("<li></li>",a)},format:function(a,c){for(var a=g(a),b=a.get(0).className.split(" "),d=0;d<b.length;d++)b[d].indexOf("jcarousel-")!=-1&&a.removeClass(b[d]);a.addClass(this.className("jcarousel-item")).addClass(this.className("jcarousel-item-"+c)).css({"float":this.options.rtl?"right":"left","list-style":"none"}).attr("jcarouselindex",c);return a},className:function(a){return a+" "+a+(!this.options.vertical?"-horizontal":"-vertical")}, dimension:function(a,c){var b=g(a);if(c==null)return!this.options.vertical?b.outerWidth(!0)||f.intval(this.options.itemFallbackDimension):b.outerHeight(!0)||f.intval(this.options.itemFallbackDimension);else{var d=!this.options.vertical?c-f.intval(b.css("marginLeft"))-f.intval(b.css("marginRight")):c-f.intval(b.css("marginTop"))-f.intval(b.css("marginBottom"));g(b).css(this.wh,d+"px");return this.dimension(b)}},clipping:function(){return!this.options.vertical?this.clip[0].offsetWidth-f.intval(this.clip.css("borderLeftWidth"))- f.intval(this.clip.css("borderRightWidth")):this.clip[0].offsetHeight-f.intval(this.clip.css("borderTopWidth"))-f.intval(this.clip.css("borderBottomWidth"))},index:function(a,c){if(c==null)c=this.options.size;return Math.round(((a-1)/c-Math.floor((a-1)/c))*c)+1}});f.extend({defaults:function(a){return g.extend(q,a||{})},intval:function(a){a=parseInt(a,10);return isNaN(a)?0:a},windowLoaded:function(){m=!0}});g.fn.jcarousel=function(a){if(typeof a=="string"){var c=g(this).data("jcarousel"),b=Array.prototype.slice.call(arguments, 1);return c[a].apply(c,b)}else return this.each(function(){var b=g(this).data("jcarousel");b?(a&&g.extend(b.options,a),b.reload()):g(this).data("jcarousel",new f(this,a))})}})(jQuery);
 /**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file PRICE COUNTDOWN-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.x and 1.5.x COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.x and 1.5.x COMMUNITY edition.
 * =================================================================
 */

 var j =1;
        if (typeof(BackColor)=="undefined")
            BackColor = "white";
        if (typeof(ForeColor)=="undefined")
            ForeColor= "black";
        if (typeof(DisplayFormat)=="undefined")
            DisplayFormat = "<div id='leftTime' class='cf50 db fleft dailydeals_date'>&nbsp;<em>%%D%%</em>&nbsp;D&nbsp;<em>%%H%%</em>&nbsp;H&nbsp;<em>%%M%%</em>&nbsp;M&nbsp;<em>%%S%%</em>&nbsp;S&nbsp;</div>";
        if (typeof(CountActive)=="undefined")
            CountActive = true;
        if (typeof(FinishMessage)=="undefined")
            FinishMessage = "";
        if (typeof(CountStepper)!="number")
            CountStepper = -1;
        if (typeof(LeadingZero)=="undefined")
            LeadingZero = true;
        CountStepper = Math.ceil(CountStepper);
        if (CountStepper == 0)
            CountActive = false;
        var SetTimeOutPeriod = (Math.abs(CountStepper)-1)*1000 + 990;
        function calcage(secs, num1, num2) {
            s = ((Math.floor(secs/num1)%num2)).toString();
            if (LeadingZero && s.length < 2)
                s = "0" + s;
            return "<b>" + s + "</b>";
        }
        function CountBack(secs,iid,j) {
            if (secs < 0) {
                document.getElementById(iid).innerHTML = FinishMessage;
                document.getElementById('caption'+j).style.display = "none";
                document.getElementById('heading'+j).style.display = "none";
                return;
            }
            DisplayStr = DisplayFormat.replace(/%%D%%/g, calcage(secs,86400,100000));
            DisplayStr = DisplayStr.replace(/%%H%%/g, calcage(secs,3600,24));
            DisplayStr = DisplayStr.replace(/%%M%%/g, calcage(secs,60,60));
            DisplayStr = DisplayStr.replace(/%%S%%/g, calcage(secs,1,60));
            document.getElementById(iid).innerHTML = DisplayStr;
            if (CountActive)
                setTimeout(function(){CountBack((secs+CountStepper),iid,j)}, SetTimeOutPeriod);
        }
