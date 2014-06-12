/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
jQueryCommercebug.fn.commercebug = function(l_commercebug_json) {
	s_prefix = this[0].id.split('-')[0];
	function toggle_commercebug(pElement)
	{
		jQueryCommercebug(pElement).animate({height:'toggle'}, false, false, function(){
			set_default_display(jQueryCommercebug(this).css('display'));
		});		
	}
	
	function action_lookup()
	{
		var collected_data = jQueryCommercebug.parseJSON(l_commercebug_json);				
		var val	   = jQueryCommercebug('#'+s_prefix+'_text_lookup_uri').val();
		var context= jQueryCommercebug('#'+s_prefix+'_select_context').val();			
		if(val)
		{
			jQueryCommercebug('#'+s_prefix+'_lookup_results').html('Loading ...');
			var params = {
				uri:val,
				'context':context
			};
			var action = 'lookupUri';
			if(val.indexOf('/') == -1)
			{
				params = {'class':val};
				action = 'lookupClass';
			}				
			jQueryCommercebug('#'+s_prefix+'_lookup_results').load(collected_data.system_info.ajax_path + '/'+action,params);//?uri=catalog%2Fnavigation');				
		}
	}
	
	function init_controllers(pElement)
	{
		var collected_data = jQueryCommercebug.parseJSON(l_commercebug_json);			
		if(collected_data.controller)
		{
			jQueryCommercebug('#'+s_prefix+'_controllers_classname').html(collected_data.controller.className);
			jQueryCommercebug('#'+s_prefix+'_controllers_full_actionname').html(collected_data.controller.fullActionName);
			jQueryCommercebug('#'+s_prefix+'_controllers_filename').html(collected_data.controller.fileName);
			jQueryCommercebug("#"+s_prefix+"_controllers_modulename").html(collected_data.request.moduleName);
			jQueryCommercebug("#"+s_prefix+"_controllers_controllername").html(collected_data.request.controllerName);
			jQueryCommercebug("#"+s_prefix+"_controllers_actionname").html(collected_data.request.actionName);			
			jQueryCommercebug("#"+s_prefix+"_controllers_pathinfo").html(collected_data.request.pathInfo);
			jQueryCommercebug("#"+s_prefix+"_controllers_page_id").html(collected_data.request.pageId);
		}			
	}
	
	function init_models(pElement)
	{
		var models 		= jQueryCommercebug.parseJSON(l_commercebug_json).models;			
		var modelFiles 	= jQueryCommercebug.parseJSON(l_commercebug_json).modelFiles;
		var labels		= jQueryCommercebug.parseJSON(l_commercebug_json).labels.models;
		var table  = [];
		table.push('<table id="'+s_prefix+'_models_results_table" class="tablesorter">');
		table.push('<thead><tr><th>'+labels.column_1+'<\/th><th>'+labels.column_2+'<\/th><\/tr><\/thead>');
	
		table.push('<tbody>');		
		var c=0;
		jQueryCommercebug.each(models, function(key, value){
			var odd_or_even = c % 2 ? 'odd' : 'even';
			table.push('<tr class="'+odd_or_even+'">');
	
			table.push('<td class="classname">');
			table.push(key);
			table.push('<br />');
			table.push('<span class="pathinfo">' + modelFiles[key] + '<\/span>');								
			table.push('<\/td>');
			table.push('<td>');
			table.push(value);
			table.push('<\/td>');
			table.push('<' + '/tr>');			
			c++;
		});
		table.push('<\/tbody>');						
		table.push('<\/table>');
		jQueryCommercebug('#'+s_prefix+'_models_results').html(table.join(''));
		jQueryCommercebug('#'+s_prefix+'_models_results_table').tablesorter({widgets: ['zebra']});			
	}
	
	function init_blocks(pElement)
	{
		var blocks 		= jQueryCommercebug.parseJSON(l_commercebug_json).blocks;			
		var blockFiles 	= jQueryCommercebug.parseJSON(l_commercebug_json).blockFiles;			
		var labels		= jQueryCommercebug.parseJSON(l_commercebug_json).labels.blocks;

		var table  = [];
		table.push('<table id="'+s_prefix+'_blocks_results_table" class="tablesorter">');
		table.push('<thead><tr><th>'+labels.column_1+'<\/th><th>'+labels.column_2+'<\/th><th>'+labels.column_3+'<\/th><\/tr><\/thead>');			
	
		table.push('<tbody>');					
		var c=0;
		jQueryCommercebug.each(blocks, function(key, value){
			var odd_or_even = c % 2 ? 'odd' : 'even';
			table.push('<tr class="'+odd_or_even+'">');
			var block_and_template = key.split('::');
			table.push('<td class="classname">');
			table.push(block_and_template[0]);
			table.push('<br /><span class="pathinfo">');table.push(blockFiles[key]);table.push('<\/span>');								
			table.push('<\/td>');
			table.push('<td>');
			table.push(value);
			table.push('<\/td>');
			table.push('<td class="pathinfo">');table.push(block_and_template[1]);table.push('<\/td>');
			table.push('<' + '/tr>');		
			c++;
		});	
		table.push('<\/tbody>');									
		table.push('<\/table>');			
		jQueryCommercebug('#'+s_prefix+'_blocks_results').html(table.join(''));				
		jQueryCommercebug('#'+s_prefix+'_blocks_results_table').tablesorter({widgets:['zebra']});			
	}
	
	function push_cellpair(arr, key, value)
	{
		arr.push('<tr>');					
		arr.push('<td><strong>'+key+'</strong></td>');							
		arr.push('<td>'+value+'</td>');							
		arr.push('</tr>');	
	
		return arr;
	}
	
	function init_layout(pElement)
	{
		var layout 			= jQueryCommercebug.parseJSON(l_commercebug_json).layout;	
		var design_paths 	= jQueryCommercebug.parseJSON(l_commercebug_json).design_paths;
		var labels = jQueryCommercebug.parseJSON(l_commercebug_json).labels.layouts;		
		var results = [];
		//console.log(design_paths);
		results.push('<table class="tablesorter" style="width:400px;float:left;margin-right:30px;">');			
		var c=0;
		results.push('<thead><tr><th>'+labels.column_1+'<\/th><\/tr><\/thead>');			
		results.push('<tbody>');
		jQueryCommercebug.each(layout.handles, function(key, value){
			var odd_or_even = c % 2 ? 'odd' : 'even' ;
			results.push('<tr class="'+odd_or_even+'"><td class="classname">&lt;');results.push(value);results.push(' /&gt;<\/td><\/tr>');
			c++;
		});		
		
		var odd_or_even = c % 2 ? 'odd' : 'even' ;
		results.push('<tfoot>');
		results.push('<tr class="'+odd_or_even+'"><td>');
		results.push('<span id="'+s_prefix+'_page_layout_label">'+labels.view_page_layout+':<\/span><a href="?showLayout=page&showLayoutFormat=xml" target="_blank">'+labels.xml+'<\/a> | <a href="?showLayout=page&showLayoutFormat=text" target="_blank">'+labels.text+'<\/a>');
		results.push('<\/td><\/tr>');
		c++;
		var odd_or_even = c % 2 ? 'odd' : 'even' ;
		results.push('<tr class="'+odd_or_even+'"><td>');
		results.push('<span id="'+s_prefix+'_page_layout_label">'+labels.view_package_layout+': <\/span><a href="?showLayout=package&showLayoutFormat=xml" target="_blank">'+labels.xml+'<\/a> | <a href="?showLayout=package&showLayoutFormat=text" target="_blank">'+labels.text+'<\/a>');
		results.push('<\/td>');
		results.push('<\/tfoot>');			
		results.push('<\/tbody>');			
		results.push('<\/table>');			
		
		results.push('<table class="tablesorter" style="width:480px;float:left;">');			
		results.push('<thead>');
		results.push('<tr>');
		results.push('<th colspan="2">Path Information</th>');
		results.push('</tr>');
		results.push('</thead>');
		results.push('<tbody>');					


		results = push_cellpair(results,'Package',design_paths.package);
		results = push_cellpair(results,'Default Theme',design_paths.default_theme);
		results = push_cellpair(results,'Custom Package',design_paths.custom_package);
		results = push_cellpair(results,'Custom Theme',design_paths.custom_theme);		
		results = push_cellpair(results,'Templates',design_paths.templates);
		results = push_cellpair(results,'Layout',design_paths.layout);		
		results = push_cellpair(results,'Translations',design_paths.translations);
		results = push_cellpair(results,'Skin',design_paths.skin);
		
		results.push('<tfoot>');
		results.push('<tr><td colspan="2">');
		results.push('<em>See Blocks tab for specific template locations</em>');
		results.push('<\/td><\/tr>');
		
		results.push('</tbody>');							
		results.push('<\/table>');			
		results.push('<div style="clear:left;"></div>');	
		jQueryCommercebug('#'+s_prefix+'_layout_results').html(results.join(''));
	}
	
	function init_event_handlers(pElement)
	{
		var collected_data = jQueryCommercebug.parseJSON(l_commercebug_json);	
		//if events are already attached then skip
		if(jQueryCommercebug(pElement).data('events').tabsshow)
		{
			return;
		}

		jQueryCommercebug('#'+s_prefix+'_button_json').bind('click', function(){
			sJson = jQueryCommercebug('#'+s_prefix+'_textarea_json').val();
			if(jQueryCommercebug.parseJSON(sJson))
			{
				jQueryCommercebug(pElement).commercebug(sJson);
				jQueryCommercebug('#'+s_prefix+'_results_json').html('Tabs Reloaded');				
			}
			else
			{
				jQueryCommercebug('#'+s_prefix+'_results_json').html('Contents of field are not a valid JSON string');
			}
		});				
		
		jQueryCommercebug('#'+s_prefix+'_showhide').bind('click', function(){
			toggle_commercebug(pElement);
		});				
		jQueryCommercebug('#'+s_prefix+'_but_clear_cache').bind('click', function()
		{
			jQueryCommercebug('#'+s_prefix+'_clear_cache_results').html('clearing ...');
			jQueryCommercebug('#'+s_prefix+'_clear_cache_results').load(collected_data.system_info.ajax_path + '/clearcache');
		});
		
		jQueryCommercebug('#'+s_prefix+'_but_run_lint').bind('click', function()
		{
			jQueryCommercebug('#'+s_prefix+'_run_lint_results').html('loading ...');
			jQueryCommercebug('#'+s_prefix+'_run_lint_results').load('/configlint');			
		});
		
		jQueryCommercebug('#'+s_prefix+'_but_lookup').bind('click',action_lookup);
		jQueryCommercebug('#'+s_prefix+'_text_lookup_uri').bind('keyup',function(e){				
			var code = (e.keyCode ? e.keyCode : e.which);
			if(code == 13) { //Enter keycode
				action_lookup();			 
			}
		});				
		
		jQueryCommercebug(pElement).bind('tabsshow',function(e,ui){
			set_last_tab(ui.tab.href);
		});            
	}
	
	function set_last_tab(tab)
	{
		var cookie_name 	= 'last_tab';
		var cookie_options = { path: '/', expires: 7 };				
		jQueryCommercebug.cookie(cookie_name, tab, cookie_options);		
	}
	
	function get_last_tab()
	{
		if(jQueryCommercebug.cookie('last_tab'))
		{				
			return '#' + jQueryCommercebug.cookie('last_tab').split('#')[1];				
		}		
		return false;		
	}
	
	function set_default_display(val)
	{
		var cookie_name 	= 'default_display';
		var cookie_options = { path: '/', expires: 7 };				
		jQueryCommercebug.cookie(cookie_name, val, cookie_options);			
	}
	
	function get_default_display()
	{
		if(jQueryCommercebug.cookie('default_display'))
		{
			return jQueryCommercebug.cookie('default_display');
		}
		return 'none';
	}
	
	
	function switch_to_last_tab(pElement)
	{
		var tab = get_last_tab();			
		jQueryCommercebug(pElement).tabs('select',tab);	
	}
	
	function init_tabs(pElement)
	{
		jQueryCommercebug(pElement).tabs();
		switch_to_last_tab(pElement);
	
	}
	function init_default_display(pElement)
	{
		if(get_default_display() != 'none' &&  jQueryCommercebug('#ascommercebug-tabs').css('display') != 'block')
		{
			toggle_commercebug(pElement);
		}
	}
	
	init_tabs(this);
	init_event_handlers(this);
	init_controllers(this);
	init_models(this);
	init_blocks(this);		
	init_layout(this);
	jQueryCommercebug.each(jQueryCommercebug.fn.commercebug.callbacks, function(callback, f){
		f(l_commercebug_json);
	});
	
	init_default_display(this);	
};

jQueryCommercebug.fn.commercebug.tab_backwards = function(pElement)
{
	var index 		= jQueryCommercebug(pElement).tabs('option','selected');
	jQueryCommercebug(pElement).tabs('select',index-1);
	var new_index 	= jQueryCommercebug(pElement).tabs('option','selected');
};	
	
jQueryCommercebug.fn.commercebug.tab_forward = function(pElement)
{
	var index 		= jQueryCommercebug(pElement).tabs('option','selected');
	jQueryCommercebug(pElement).tabs('select',index+1);
	var new_index 	= jQueryCommercebug(pElement).tabs('option','selected');
	if(index == new_index)
	{
		jQueryCommercebug(pElement).tabs('select',0);
	}
};

jQueryCommercebug.fn.commercebug.callbacks = [];
jQueryCommercebug.fn.commercebug.registerCallback = function(f) 
{
	jQueryCommercebug.fn.commercebug.callbacks.push(f);
}