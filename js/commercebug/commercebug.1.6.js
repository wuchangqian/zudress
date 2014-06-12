/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
jQueryCommercebug.fn.commercebugSearcher = function(one,two,three) {
    var $ = jQueryCommercebug;    
    var searchTimeouts = {};
    $.each(this, function(k, input){
        var table = $(input).closest('table')[0];
        if(!table || !table.id)
        {
            return;
        }
        searchTimeouts[table.id] = false;

        var tableSearch = function(input, table){    
            var theText = $(input).val();
            if(!theText)
            {
                $('#'+table.id+' tbody tr td').closest('tr').show();
                return;
            }

            if(theText.indexOf('-') === 0)
            {
                theText = theText.substr(1);
                $('#'+table.id+' tbody tr td').closest('tr').show();
                $('#'+table.id+' tbody tr td:contains("'+theText+'")').closest('tr').hide();                
                return;
            }
            
            //normal search
            $('#'+table.id+' tbody tr td').closest('tr').hide();
            $('#'+table.id+' tbody tr td:contains("'+theText+'")').closest('tr').show();
        }
        
        $(input).bind('keyup click',function(){
            if(searchTimeouts[table.id])
            {
                clearTimeout(searchTimeouts[table.id]);
            }
            searchTimeout = setTimeout(function(){
                tableSearch(input, table)
            },10);            
        });
    });    
}

//var as_debug;
jQueryCommercebug.fn.commercebug = function(l_commercebug_json) {
    var $ = jQueryCommercebug;
    var l_commercebug_json_parsed = $.parseJSON(l_commercebug_json);
    //var s_prefix = this[0].id.split('-')[0];
    s_prefix = 'ascommercebug';
    function toggle_commercebug(pElement)
    {
        $(pElement).animate({height:'toggle'}, false, false, function(){
            set_default_display($(this).css('display'));
        });     
    }

    function action_lookup()
    {
        var collected_data = l_commercebug_json_parsed;               
        var val    = $('#ascommercebug_text_lookup_uri').val();
        var context= $('#ascommercebug_select_context').val();           
        if(val)
        {
            $('#ascommercebug_lookup_results').html('Loading ...');
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
            $('#ascommercebug_lookup_results').load(collected_data.system_info.ajax_path + '/'+action,params);//?uri=catalog%2Fnavigation');             
        }
    }
    
    function init_controllers(pElement)
    {
        var collected_data = l_commercebug_json_parsed;    
        var labels      = l_commercebug_json_parsed.labels.controllers;
        
        var table = new Pulsestorm_Html_Table();
        table.setId('ascommercebug_controllers_table');
        table.addHeader(labels.label).addHeader(labels.value);
        table.addGlobalClass('table','tablesorter');
        
        var className = ['<span class="classname">',collected_data.controller.className,'</span>'].join('');
        var fileName  = ['<span class="pathinfo">',collected_data.controller.fileName,'</span>'].join('');        
        
        var cell_controller = [className,'<br/>',fileName].join('');        
        table.addRow([labels.controller_class_name  ,cell_controller]);
        table.addRow([labels.full_action_name       ,collected_data.controller.fullActionName]);
        table.addRow([labels.module_name            ,collected_data.request.moduleName]);
        table.addRow([labels.controller_name        ,collected_data.request.controllerName]);
        table.addRow([labels.action_name            ,collected_data.request.actionName]);
        table.addRow([labels.path_info              ,collected_data.request.pathInfo]);
        table.addRow([labels.cms_page_id            ,collected_data.request.pageId]);
        // table.addRow(row);
        
        $('#ascommercebug_controllers').html(table.render());
    }
    
    function init_events(pElement)
    {        
        
        var events      = l_commercebug_json_parsed.events;                       
        events = events ? events : [];
        
        var areas   = l_commercebug_json_parsed.event_areas;                      
        
        var labels      = l_commercebug_json_parsed.labels.events;
        var table       = [];
        
        var table = new Pulsestorm_Html_Table();                
        
        table.setId('ascommercebug_events_table');
        table.addHeader(labels.column_1).addHeader(labels.column_2);
        table.addGlobalClass('table','tablesorter');
        //table.addHeadRow(['','<input id="ascommercebug_search_events" type="text">']);
        table.addPositionedHeadCss(0,'ascommercebug_event_area');
        table.setUseSearch(true);
        var c = 0;
        $.each(events, function(key, value){
            table.addRow([areas[c],value]);
            c++;
        });

        $('#ascommercebug_events').html(table.render());
        if($('#ascommercebug_events_table tr').length > 2)
        {
            $('#ascommercebug_events_table').tablesorter({widgets: ['zebra']});        
        }                
        
        var tableSearch = function(event){
            var theText = $('#ascommercebug_search_events').val();
            if(!theText)
            {
                $('#ascommercebug_events_table tbody tr td').closest('tr').show();
                return;
            }
            $('#ascommercebug_events_table tbody tr td').closest('tr').hide();
            $('#ascommercebug_events_table tbody tr td:contains("'+theText+'")').closest('tr').show();
        }
        
        var searchTimeout=false;
        $("#ascommercebug_search_events").keyup(function(){
            //clear the timeout
            if(searchTimeout)
            {
                clearTimeout(searchTimeout);
            }
            //set new timeout
            searchTimeout = setTimeout(tableSearch,10);
            
        });    
        
        return;                    
    }

    function init_models_other(pElement)
    {
        var files   = parse_files_info_into_pools(l_commercebug_json_parsed.phpFiles);
        var labels  = l_commercebug_json_parsed.labels.other;
        as_debug = files;
        // console.log(files);

        var table = new Pulsestorm_Html_Table();
        table.setId('ascommercebug_objects_other_table');
        table.addHeader(labels.code_pool).addHeader(labels.type).addHeader(labels.className);
        table.addGlobalClass('table','tablesorter');
        table.setUseSearch(true);
        // table.addPositionedCellCss(0,'classname');
        
        var row,cell,model;
        var base_dir = l_commercebug_json_parsed.phpFilesInfo.baseDir;
        var process_file_row = function(value, table, pool)
        {
            var model = get_model_or_block_of_interest(value, l_commercebug_json_parsed);            
            var type  = value.split('/')[2];
            if(model)
            {
                cell = [model,'<br/>','<span class="pathinfo">' + base_dir + '/app/code/' + pool + '/' + value + '<\/span>'].join('');
                row = [pool,type,cell];
                table.addRow(row);
            }        
        }
        
        $.each(files.pool_core, function(key, value){
            process_file_row(value, table, 'core');
        });
        $.each(files.pool_community, function(key, value){
            process_file_row(value, table, 'community');
        });
        $.each(files.pool_local, function(key, value){
            process_file_row(value, table, 'local');
        });
        
        $('#ascommercebug_objects_other').html(table.render());
        $('#ascommercebug_objects_other_table').tablesorter({widgets: ['zebra']});      
            
    }
    
    var get_model_or_block_of_interest = function(path, l_commercebug_json_parsed)
    {
        var parts = path.replace(/\.php/,'').split('/');
        //
        if(parts[1] == 'Commercebug')
        {
            return false;
        }
        
        if(parts[2] != 'Model' && parts[2] != 'Block')
        {
            return false;
        }
        
        if(parts[2] == 'Model' && (parts[3] == 'Resource' || parts[3] == 'Mysql4'))
        {
            return false;
        }
        
        if(path.indexOf('/Abstract') !== -1)
        {
            return false;
        }
        
        var className = path.replace(/\//g,'_').replace(/\.php/,'');
        //remove models common to all requests
        
        $.each(get_standard_models(), function(key, value){
            className = (className !== value) ? className : false;
        });        
        
        $.each(l_commercebug_json_parsed.models, function(key){
            className = (className !== key) ? className : false;            
        });
        
        
        $.each(l_commercebug_json_parsed.blocks, function(key){
            var parts = key.split('::');
            key = parts[0];
            className = (className !== key) ? className : false;
        });     
                
        

        return className;
    }
    
    var get_standard_models = function()
    {
        return l_commercebug_json_parsed.standardModels;
    }
    
    var parse_files_info_into_pools = function(files)
    {    
        var info = {};
        info.pool_core = [];info.pool_community = [];
        info.pool_local = [];info.lib = [];
        info.other = [];
        
        $.each(files, function(key, value){            
            if(value.indexOf('app/code/core') === 0){
                info.pool_core.push(value.substr('app/code/core'.length+1));
            }
            else if(value.indexOf('app/code/community') === 0){
                info.pool_community.push(value.substr('app/code/community'.length+1));
            }
            else if(value.indexOf('app/code/local') === 0){
                info.pool_local.push(value.substr('app/code/local'.length+1));
            }
            else if(value.indexOf('lib') === 0){
                info.lib.push(value.substr('lib'.length+1));
            }            
            else{
                info.other.push(value);
            }
        });    
        
        return info;
    }
    
    function init_models_crud(pElement)
    {
        var models      = l_commercebug_json_parsed.models;           
        var modelFiles  = l_commercebug_json_parsed.modelFiles;
        var labels      = l_commercebug_json_parsed.labels.models;

        var table = new Pulsestorm_Html_Table();
        table.setUseSearch(true);
        table.setId('ascommercebug_models_table');
        table.addHeader(labels.column_1).addHeader(labels.column_2);
        table.addGlobalClass('table','tablesorter');
        table.addPositionedCellCss(0,'classname');
        
        var row,cell_1;
        $.each(models, function(key, value){
            cell_1 = [key,'<br/>','<span class="pathinfo">' + modelFiles[key] + '<\/span>'].join('');
            row = [cell_1,value];
            table.addRow(row);
        });
        
        $('#ascommercebug_models').html(table.render());
        $('#ascommercebug_models_table').tablesorter({widgets: ['zebra']});      
    }
    
    function init_observers(pElement)
    {
        var observers = l_commercebug_json_parsed.observers;
        var labels    = l_commercebug_json_parsed.labels.observers;
        
        var table = new Pulsestorm_Html_Table();
        table.setUseSearch(true);
        table.setId('ascommercebug_observers_table');
        table.addHeader(labels.column_1).addHeader(labels.column_2);
        table.addHeader(labels.column_3).addHeader(labels.column_4);
        table.addHeader(labels.column_5);        
        table.addGlobalClass('table','tablesorter');

        var row, cell_1;
        $.each(observers, function(key, value){
            var name = value.commercebug_name;
            delete value.commercebug_name;
            $.each(value, function(key2, information){
                row = [name,key2,information.type,information.model,information.method];                
                table.addRow(row);
            });
        });
        
        $('#ascommercebug_observers').html(table.render());
        $('#ascommercebug_observers_table').tablesorter({widgets: ['zebra']});      
    }
    
    function init_blocks(pElement)
    {
        var blocks      = l_commercebug_json_parsed.blocks;           
        var blockFiles  = l_commercebug_json_parsed.blockFiles;           
        var labels      = l_commercebug_json_parsed.labels.blocks;

        var table = new Pulsestorm_Html_Table();
        table.setUseSearch(true);
        table.setId('ascommercebug_blocks_table');
        table.addHeader(labels.column_1).addHeader(labels.column_2).addHeader(labels.column_3);
        table.addGlobalClass('table','tablesorter');
        table.addPositionedCellCss(0,'classname').addPositionedCellCss(2,'pathinfo');;
        
        var row,cell_1;
        $.each(blocks, function(key, value){
            // cell_1 = [key,'<br/>','<span class="pathinfo">' + modelFiles[key] + '<\/span>'].join('');
            var block_and_template = key.split('::');
            var cell_1 = [block_and_template[0],'<br /><span class="pathinfo">',blockFiles[key],'<\/span>'].join('');
            row = [cell_1,value,block_and_template[1]];
            table.addRow(row);
        });
        $('#ascommercebug_blocks').html(table.render());             
        $('#ascommercebug_blocks_table').tablesorter({widgets:['zebra']});       
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
        var layout          = l_commercebug_json_parsed.layout;   
        var design_paths    = l_commercebug_json_parsed.design_paths;
        var labels          = l_commercebug_json_parsed.labels.layouts;       
        var results = [];
        //console.log(design_paths);
        
        var table = new Pulsestorm_Html_Table();
        table.setId('ascommercebug_layout_handles');
        table.addHeader(labels.column_1);
        table.addGlobalClass('table','tablesorter');
        table.addPositionedCellCss(0,'classname');
        $.each(layout.handles, function(key, value){
            table.addRow([['&lt;',value,' /&gt;'].join('')]);    
        });             
        
        // table.addFooterRow(['Testse']);
                
        table.addFooterRow(['<span id="ascommercebug_page_layout_label">'+labels.view_page_layout+':<\/span><a href="?showLayout=page&showLayoutFormat=xml" target="_blank">'+labels.xml+'<\/a> | <a href="?showLayout=page&showLayoutFormat=text" target="_blank">'+labels.text+'<\/a>']);
        table.addFooterRow(['<span id="ascommercebug_page_layout_label">'+labels.view_package_layout+': <\/span><a href="?showLayout=package&showLayoutFormat=xml" target="_blank">'+labels.xml+'<\/a> | <a href="?showLayout=package&showLayoutFormat=text" target="_blank">'+labels.text+'<\/a>']);
        table.addFooterRow(['<a href="?showLayoutFiles" target="_blank">View Layout Files <\/a>']);

        results.push('<p><a href="#" id="as_commercebug_display_dot_link"> '+labels.view_graphviz+' </a></p>');
        results.push('<textarea style="display:none;width:500px;height:300px" name="as_commercebug_dot_console" id="as_commercebug_dot_console"></textarea>');
        results.push(table.render());
        
        results.push('<table id="ascommercebug_layout_path_information" class="tablesorter">');          
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
        
        $('#ascommercebug_layout').html(results.join(''));        
        $('#ascommercebug_layout_handles').tablesorter({widgets: ['zebra']});   
        
        $('#as_commercebug_display_dot_link').click(function(){
            var as_commercebug_dot = {'dot':l_commercebug_json_parsed.graphviz.replace(/\\\\n/g,'\\n')};		
            $('#as_commercebug_dot_console').val(as_commercebug_dot.dot);
            $('#as_commercebug_dot_console').toggle();            
        });
    }
    
    function init_event_handlers(pElement)
    {       
        var collected_data = l_commercebug_json_parsed;   
        //if events are already attached then skip
        //         if($(pElement).data('events').tabsshow)
        //         {
        //             return;
        //         }

        $('#ascommercebug_button_json').bind('click', function(){
            sJson = $('#ascommercebug_textarea_json').val();
            if($.parseJSON(sJson))
            {
                $(pElement).commercebug(sJson);
                $('#ascommercebug_results_json').html('Tabs Reloaded');              
            }
            else
            {
                $('#ascommercebug_results_json').html('Contents of field are not a valid JSON string');
            }
        });             
        
        $('#ascommercebug_showhide').bind('click', function(){
            toggle_commercebug(pElement);
        });             

        $('#ascommercebug_but_run_lint').bind('click', function()
        {
            $('#ascommercebug_run_lint_results').html('loading ...');
            $('#ascommercebug_run_lint_results').load('/configlint');            
        });
                
        $(pElement).bind('tabsshow',function(e,ui){
            set_last_tab(ui.tab.href);
        });            
    }
    
    function set_last_tab(tab)
    {
        var cookie_name     = 'last_tab';
        var cookie_options = { path: '/', expires: 7 };             
        $.cookie(cookie_name, tab, cookie_options);     
    }
    
    function get_last_tab()
    {
        if($.cookie('last_tab'))
        {               
            return '#' + $.cookie('last_tab').split('#')[1];                
        }       
        return false;       
    }
    
    function set_default_display(val)
    {
        var cookie_name     = 'default_display';
        var cookie_options = { path: '/', expires: 7 };             
        $.cookie(cookie_name, val, cookie_options);         
    }
    
    function get_default_display()
    {
        if($.cookie('default_display'))
        {
            return $.cookie('default_display');
        }
        return 'none';
    }
    
    
    function switch_to_last_tab(pElement)
    {
        var tab = get_last_tab();           
        $(pElement).tabs('select',tab); 
    }
    
    function init_tabs(pElement)
    {
        $(pElement).tabs();
        switch_to_last_tab(pElement);
    
    }
    
    function init_lookup_tab(pElement)
    {
        var collected_data = l_commercebug_json_parsed;               
        var labels      = l_commercebug_json_parsed.labels.lookup;
        
        var content = '';   
        content += '<div id="ascommercebug_lookup_container">';
        content += '    <p> ' + labels.instructions;
        content += '    (<span class="classname">Mage_Catalog_Model_Product</span>).</p>';
        content += '    <div>' + labels.context;
        content += '    <select id="ascommercebug_select_context">';
        content += '        <option value="all">'+labels.all+'</option>';
        content += '        <option value="model">'+labels.model+'</option>';
        content += '        <option value="block">'+labels.block+'</option>';								
        content += '        <option value="helper">'+labels.helper+'</option>';				
        content += '    </select>, ';
        content += '    <input type="text" id="ascommercebug_text_lookup_uri"/>  ';
        content += '    <button id="ascommercebug_but_lookup" class="fg-button ui-state-default ui-corner-all">';
        content += labels.resolves_to;
        content += '    </button>';
        content += '    </div>';
        content += '    <div id="ascommercebug_lookup_results">';
        content += '    </div>';
        content += '</div>';
        $('#ascommercebug_lookup').append(content);
        $('#ascommercebug_but_lookup').bind('click',action_lookup);
        $('#ascommercebug_text_lookup_uri').bind('keyup',function(e){                
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { //Enter keycode
                action_lookup();             
            }
        });             
        
    
    }
    
    function init_system_tab(pElement)
    {
        var collected_data = l_commercebug_json_parsed;   
        var labels         = l_commercebug_json_parsed.labels.system;        
        var content = '<p>'; 
        content += '<button id="ascommercebug_but_clear_cache" class="fg-button ui-state-default ui-corner-all">'+labels.clear_cache+'</button>'
        content += '<button id="ascommercebug_but_togglehints" class="fg-button ui-state-default ui-corner-all">'+labels.toggle_template+'</button>'
        content += '<button id="ascommercebug_but_toggleblockhints" class="fg-button ui-state-default ui-corner-all">'+labels.toggle_block+'</button>'
        content += '<button id="ascommercebug_but_toggle_magelogging" class="fg-button ui-state-default ui-corner-all">'+labels.toggle_magelogging+'</button>'
        content += '<button id="ascommercebug_but_toggle_cblogging" class="fg-button ui-state-default ui-corner-all">'+labels.toggle_cblogging+'</button>'        
        content += '</p>' + "\n";
        
        content += '<div id="ascommercebug_clear_cache_results"></div>';
        $('#ascommercebug_systemtasks').append(content);
        $('#ascommercebug_but_clear_cache').bind('click', function()
        {
            $('#ascommercebug_clear_cache_results').html('clearing ...');
            $('#ascommercebug_clear_cache_results').load(collected_data.system_info.ajax_path + '/clearcache');
        });        
        
        $('#ascommercebug_but_togglehints').bind('click', function()
        {
            $('#ascommercebug_clear_cache_results').html('toggling ...');
            $('#ascommercebug_clear_cache_results').load(collected_data.system_info.ajax_path + '/togglehints');
        });        

        $('#ascommercebug_but_toggleblockhints').bind('click', function()
        {
            $('#ascommercebug_clear_cache_results').html('toggling ...');
            $('#ascommercebug_clear_cache_results').load(collected_data.system_info.ajax_path + '/toggleblockhints');
        });                

        $('#ascommercebug_but_toggle_magelogging').bind('click', function()
        {
            $('#ascommercebug_clear_cache_results').html('toggling ...');
            $('#ascommercebug_clear_cache_results').load(collected_data.system_info.ajax_path + '/togglemagelogging');
        });            

        $('#ascommercebug_but_toggle_cblogging').bind('click', function()
        {
            $('#ascommercebug_clear_cache_results').html('toggling ...');
            $('#ascommercebug_clear_cache_results').load(collected_data.system_info.ajax_path + '/togglecblogging');
        });            
        
    }
    
    function init_default_display(pElement)
    {
        if(get_default_display() != 'none' &&  $('#ascommercebug-tabs').css('display') != 'block')
        {
            toggle_commercebug(pElement);
        }
    }
    
    function init_collections(pElement)
    {
        var o = l_commercebug_json_parsed;
        var collections         = o.collections;            
        var collectionFiles     = o.collectionFiles;                
        var collectionModels    = o.collectionModels;
        var labels              = l_commercebug_json_parsed.labels.collections;
        
        var table = new Pulsestorm_Html_Table();
        table.setId('ascommercebug_collections_table');
        table.addHeader(labels.collection_name).addHeader(labels.times);
        table.addGlobalClass('table','tablesorter');
        table.setUseSearch(true);
        table.addPositionedCellCss(0,'classname');
        
        var cell_1;
        $.each(collections, function(key, value){
            cell_1 = [key,'<br/>','<span class="pathinfo">']
            cell_1.push('Collects Mage::getModel("'+collectionModels[key]+'"); <br />');
            cell_1.push(collectionFiles[key]);
            cell_1.push('</span>');            
            table.addRow([cell_1.join(''),value]);
        });
        
//         var table  = [];
//         table.push('<p>'+labels.note+'</p>');
//         table.push('<table id="ascommercebug_collections_table" class="tablesorter">');
//         table.push('<thead><tr><th>'+labels.collection_name+'<\/th><!--<th>Model Name<\/th>--><th>'+labels.times+'<\/th><\/tr><\/thead>');
//     
//         table.push('<tbody>');      
//         var c=0;
//         $.each(collections, function(key, value){
//             var odd_or_even = c % 2 ? 'odd' : 'even';
//             table.push('<tr class="'+odd_or_even+'">');
//     
//             table.push('<td class="classname">');
//             table.push(key);
//             table.push('<br />');
//             table.push('<span class="pathinfo">' + 
//             'Collects Mage::getModel("'+collectionModels[key]+'"); <br />' +
//             collectionFiles[key] + 
//             '<\/span>');                                
//             
//             table.push('<\/td>');           
//             table.push('<td>');
//             table.push(value);
//             table.push('<\/td>');
//             table.push('<' + '/tr>');           
//             c++;
//         });
//         table.push('<\/tbody>');                        
//         table.push('<\/table>');
        $('#ascommercebug_collections').html(table.render());
        $('#ascommercebug_collections_table').tablesorter({widgets: ['zebra']});             
    }
    

    init_tabs(this);
    
    init_event_handlers(this);
    init_controllers(this);
    init_models_crud(this);
    init_models_other(this);
    init_collections(this);
    init_blocks(this);      
    init_layout(this);
    init_events(this);
    init_observers(this);
    init_system_tab(this);    
    init_lookup_tab(this);
    
    $.each($.fn.commercebug.callbacks, function(callback, f){
        f(l_commercebug_json);
    });
    
    init_default_display(this); 
    
    $('.pulsestorm_search_table_field').commercebugSearcher();
};

jQueryCommercebug.fn.commercebug.tab_backwards = function(pElement)
{
    var $ = jQueryCommercebug;
    var index       = $(pElement).tabs('option','selected');
    $(pElement).tabs('select',index-1);
    var new_index   = $(pElement).tabs('option','selected');
};  
    
jQueryCommercebug.fn.commercebug.tab_forward = function(pElement)
{
    var $ = jQueryCommercebug;
    var index       = $(pElement).tabs('option','selected');
    $(pElement).tabs('select',index+1);
    var new_index   = $(pElement).tabs('option','selected');
    if(index == new_index)
    {
        $(pElement).tabs('select',0);
    }
};

jQueryCommercebug.fn.commercebug.callbacks = [];
jQueryCommercebug.fn.commercebug.registerCallback = function(f) 
{
    jQueryCommercebug.fn.commercebug.callbacks.push(f);
}