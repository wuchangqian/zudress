var MegaMenu = {
    
    mobileMenuThreshold: 960,bar: jQuery('#nav')
    ,panels: null
    ,mobnavTriggerWrapper: null
    ,itemSelector: 'li'
    ,panelSelector: '.nav-panel'
    ,openerSelector: '.opener'
    ,isTouchDevice: ('ontouchstart' in window) || (navigator.msMaxTouchPoints > 0)
    ,ddDelayIn: 100
    ,ddDelayOut: 200
    ,ddAnimationDurationIn: 100
    ,ddAnimationDurationOut: 300
    
    ,init: function(){
        MegaMenu.panels = MegaMenu.bar.find(MegaMenu.panelSelector);
        MegaMenu.mobnavTriggerWrapper = jQuery('.mobnav-trigger-wrapper');
    }
    
    ,initDualMode: function(){
        MegaMenu.init();
        MegaMenu.bar.accordion(MegaMenu.panelSelector, MegaMenu.openerSelector, MegaMenu.itemSelector);
        if (jQuery(window).width() >= MegaMenu.mobileMenuThreshold) {
            MegaMenu.cleanUpAfterMobileMenu(); //Required for IE8
        }
        
        enquire.register('screen and (max-width: ' + (MegaMenu.mobileMenuThreshold - 1) + 'px)', {
            match: MegaMenu.activateMobileMenu,
            unmatch: MegaMenu.cleanUpAfterMobileMenu
        }).register('screen and (min-width: ' + MegaMenu.mobileMenuThreshold + 'px)', {
            deferSetup: true,
            setup: MegaMenu.cleanUpAfterMobileMenu,
            match: MegaMenu.activateRegularMenu,
            unmatch: MegaMenu.prepareMobileMenu
        });
    }
    
    ,initMobileMode: function(){
        MegaMenu.init();
        MegaMenu.bar.accordion(MegaMenu.panelSelector, MegaMenu.openerSelector, MegaMenu.itemSelector);
        MegaMenu.activateMobileMenu();
    }
    
    ,activateMobileMenu: function(){
        MegaMenu.mobnavTriggerWrapper.show();
        MegaMenu.bar.addClass('nav-mobile acco').removeClass('nav-regular');
    }
    
    ,activateRegularMenu: function(){
        MegaMenu.bar.addClass('nav-regular').removeClass('nav-mobile acco');
        MegaMenu.mobnavTriggerWrapper.hide();
    }
    
    ,cleanUpAfterMobileMenu: function(){
        MegaMenu.panels.css('display', '');
    }
    
    ,prepareMobileMenu: function(){
        MegaMenu.panels.hide();
        
        MegaMenu.bar.find('.item-active').each(function() {
            jQuery(this).children('.nav-panel').show();
        });
    }

}; //end: MegaMenu

MegaMenu.initDualMode();

//Toggle mobile menu
jQuery('a.mobnav-trigger').on('click', function(e) {
    return;
    e.preventDefault();
    if (jQuery(this).hasClass('active')){
        MegaMenu.bar.removeClass('show');
        jQuery(this).removeClass('active');
    }else{
        MegaMenu.bar.addClass('show');
        jQuery(this).addClass('active');
    }
});

jQuery(function($) {
    var menubar = MegaMenu.bar;
    
    menubar.on('click', '.no-click', function(e) {
        e.preventDefault();
    });
    
    menubar.on('mouseenter', 'li.parent.level0', function() {
        
        if (false === menubar.hasClass('nav-mobile')){
            var item = $(this);
            var dd = item.children('.nav-panel');
            
            var itemPos = item.position();
            var ddPos = {left: itemPos.left,top: itemPos.top + item.height()};
            if (dd.hasClass('full-width')) {
                ddPos.left = 0;
            }
            
            dd.removeClass('tmp-full-width');
            
            var ddConOffset = menubar.offset().left;
            var outermostCon = $(window);            
            var outermostContainerWidth = outermostCon.width();
            var ddOffset = ddConOffset + ddPos.left;
            var ddWidth = dd.outerWidth();
            
            if ((ddOffset + ddWidth) > outermostContainerWidth){
                var diff = (ddOffset + ddWidth) - outermostContainerWidth;
                var ddPosLeft_NEW = ddPos.left - diff;
                
                var ddOffset_NEW = ddOffset - diff;
                
                if (ddOffset_NEW < 0){
                    dd.addClass('tmp-full-width');
                    ddPos.left = 0;
                }else{
                    ddPos.left = ddPosLeft_NEW;
                }
            }
            
            dd.css({
                'left': ddPos.left + 'px',
                'top': ddPos.top + 'px'
            })
            .stop(true, true).delay(MegaMenu.ddDelayIn).fadeIn(MegaMenu.ddAnimationDurationIn, "easeOutCubic");
        }
    
    }).on('mouseleave', 'li.parent.level0', function() {
        
        if (false === menubar.hasClass('nav-mobile')){
            $(this).children(".nav-panel")
            .stop(true, true).delay(MegaMenu.ddDelayOut).fadeOut(MegaMenu.ddAnimationDurationOut, "easeInCubic");
        }
    
    }); //end: menu top-level dropdowns

}); //end: on document ready

jQuery(window).on("load", function() {
    return;
    var menubar = MegaMenu.bar;
    
    if (MegaMenu.isTouchDevice){
        menubar.on('click', 'a', function(e) {
            
            link = jQuery(this);
            if (!menubar.hasClass('nav-mobile') && link.parent().hasClass('nav-item--parent')){
                if (!link.hasClass('ready')){
                    e.preventDefault();
                    menubar.find('.ready').removeClass('ready');
                    link.parents('li').children('a').addClass('ready');
                }
            }
        
        }); //end: on click
    } //end: if isTouchDevice

}); //end: on load



var SmartHeader = {
    mobileHeaderThreshold: 770
    ,rootContainer: jQuery('.header-container')
    
    ,init: function() {
        enquire.register('(max-width: ' + (SmartHeader.mobileHeaderThreshold - 1) + 'px)', {
            match: SmartHeader.moveElementsToMobilePosition,
            unmatch: SmartHeader.moveElementsToRegularPosition
        });
    }
    ,activateMobileHeader: function() {
        SmartHeader.rootContainer.addClass('header-mobile').removeClass('header-regular');
    }
    
    ,activateRegularHeader: function() {
        SmartHeader.rootContainer.addClass('header-regular').removeClass('header-mobile');
    }
    
    ,moveElementsToMobilePosition: function() {
        SmartHeader.activateMobileHeader();
        jQuery('#mini-cart-wrapper-mobile').prepend(jQuery('#mini-cart'));
        jQuery('.skip-active').removeClass('skip-active');
        //Disable dropdowns
        jQuery('#mini-cart').removeClass('dropdown');
        jQuery('#mini-compare').removeClass('dropdown');
        //Clean up after dropdowns: reset the "display" property
        jQuery('#header-cart').css('display', '');
        jQuery('#header-compare').css('display', '');
    }
    
    ,moveElementsToRegularPosition: function() {
        SmartHeader.activateRegularHeader();
        jQuery('#mini-cart-wrapper-regular').prepend(jQuery('#mini-cart'));
        jQuery('.skip-active').removeClass('skip-active');
        //Enable dropdowns
        jQuery('#mini-cart').addClass('dropdown');
        jQuery('#mini-compare').addClass('dropdown');
    }

}; //end: SmartHeader

SmartHeader.init();
jQuery(function($) {
    //Skip Links
    var skipContents = $('.skip-content');
    var skipLinks = $('.skip-link');
    
    skipLinks.on('click', function(e) {
        e.preventDefault();
        
        var self = $(this);
        var target = self.attr('href');
        //Get target element
        var elem = $(target);
        //Check if stub is open
        var isSkipContentOpen = elem.hasClass('skip-active') ? 1 : 0;
        //Hide all stubs
        skipLinks.removeClass('skip-active');
        skipContents.removeClass('skip-active');
        //Toggle stubs
        if (isSkipContentOpen) {
            self.removeClass('skip-active');
        } else {
            self.addClass('skip-active');
            elem.addClass('skip-active');
        }
    });

}); //end: on document ready

jQuery(function($) {
    var StickyHeader = {
        
        stickyThreshold: 960
        ,isSticky: false
        ,isSuspended: false
        ,headerContainer: $('.header-container')
        ,stickyContainer: $('.sticky-container') //.nav-container
        ,stickyContainerOffsetTop: 55
        
        ,init: function() {
            StickyHeader.stickyContainerOffsetTop = 
            StickyHeader.stickyContainer.offset().top + StickyHeader.stickyContainer.outerHeight();
            
            StickyHeader.applySticky();
            StickyHeader.hookToScroll();
            
            if (StickyHeader.stickyThreshold > 0) {
                enquire.register('(max-width: ' + (StickyHeader.stickyThreshold - 1) + 'px)', {
                    match: StickyHeader.suspendSticky,
                    unmatch: StickyHeader.unsuspendSticky
                });
            }
        }
        
        ,applySticky: function() {
            if (StickyHeader.isSuspended)
                return;
            
            var viewportOffsetTop = $(window).scrollTop();
            if (viewportOffsetTop > StickyHeader.stickyContainerOffsetTop) {
                if (!StickyHeader.isSticky) {
                    StickyHeader.activateSticky();
                }
            } else {
                if (StickyHeader.isSticky) {
                    StickyHeader.deactivateSticky();
                }
            }
        }
        
        ,activateSticky: function() {
            var height = StickyHeader.stickyContainer.outerHeight();
            StickyHeader.headerContainer.css('padding-bottom', height); //Fill in the space of the removed container
            //$('.page').css('padding-top', height); //Fill in the space of the removed container
            StickyHeader.headerContainer.addClass('sticky-header');
            StickyHeader.stickyContainer.css('margin-top', '-' + height + 'px').animate({'margin-top': '0'}, 200, 'easeOutCubic');
            //StickyHeader.stickyContainer.css('opacity', '0').animate({'opacity': '1'}, 300, 'easeOutCubic');
            StickyHeader.isSticky = true;
        }
        
        ,deactivateSticky: function() {
            StickyHeader.headerContainer.css('padding-bottom', '');
            //$('.page').css('padding-top', '');
            
            StickyHeader.headerContainer.removeClass('sticky-header');
            StickyHeader.isSticky = false;
        }
        
        ,suspendSticky: function() {
            StickyHeader.isSuspended = true;
            StickyHeader.deactivateSticky();
        }
        
        ,unsuspendSticky: function() {
            StickyHeader.isSuspended = false;
            StickyHeader.applySticky();
        }
        
        ,hookToScroll: function() {
            $(window).on("scroll", StickyHeader.applySticky);
        }
        
        ,hookToScrollDeferred: function() {
            var windowScrollTimeout;
            $(window).on("scroll", function() {
                clearTimeout(windowScrollTimeout);
                windowScrollTimeout = setTimeout(function() {
                    StickyHeader.applySticky();
                }, 50);
            });
        }
    
    }; //end: StickyHeader
    
    StickyHeader.init();

}); //end: on document ready