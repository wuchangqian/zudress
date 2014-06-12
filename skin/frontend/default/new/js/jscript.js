
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

