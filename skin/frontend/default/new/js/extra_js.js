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
