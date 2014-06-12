/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

var Pulsestorm_Html_Table = function()
{
    this.id                 = false;
    this.headers            = [];
    this.rows               = [];
    this.rowsHead           = [];
    this.rowsFooter         = [];
    this.globalClasses      = [];
    this.positionedCellCss  = {};
    this.positionedHeadCss  = {};    
    this.rowCount           = 0;
    this.useOddEven         = true;
    this.oddEven            = {
        'odd':'even',
        'even':'odd'
    };
    this.useSearch          = false;
    var that = this;
    
    this.setUseSearch        = function(value)
    {
        that.useSearch        = value;
    }
    
    this.addPositionedHeadCss = function(position,className)
    {
        that.positionedHeadCss['pos'+position] = that.positionedHeadCss['pos'+position] ? that.positionedHeadCss['pos'+position] : [];
        that.positionedHeadCss['pos'+position].push(className);
        return that;
    };
    
    this.addPositionedCellCss = function(position,className)
    {
        that.positionedCellCss['pos'+position] = that.positionedCellCss['pos'+position] ? that.positionedCellCss['pos'+position] : [];
        that.positionedCellCss['pos'+position].push(className);
        return that;
    };
    
    this.setId     = function(v)
    {
        that.id = v;
        return that;
    };
    
    this.setUseOddEven = function(v)
    {
        that.useOddEven = v;
        return that;
    };
    
    this.addHeader = function(v)
    {
        that.headers.push(v);
        return that;
    };

    this.addGlobalClass = function(tag,className)
    {
        that.globalClasses[tag] = that.globalClasses[tag] ? that.globalClasses[tag] : [];
        that.globalClasses[tag].push(className);
        return that;
    };
    
    this.removeGlobalClass = function(tag,className)
    {
        var a = [];
        jQueryCommercebug.each(that.globalClasses[tag],function(k,v){
            if(v == className)
            {
                return;
            }
            a.push(v);
        });
        that.globalClasses[tag] = a;
        return that;
    };
    
    this.addFooterRow = function(a)
    {
        if(a.length != that.headers.length)
        {
            throw "Column Count doesn't match header count.";
        }
        that.rowsFooter.push(a);
        return that;    
    }
    
    this.addHeadRow = function(a)
    {
        if(a.length != that.headers.length)
        {
            throw "Column Count doesn't match header count.";
        }
        that.rowsHead.push(a);
        return that;    
    }
    
    this.addRow = function(a)
    {
        if(a.length != that.headers.length)
        {
            throw "Column Count doesn't match header count.";
        }
        that.rows.push(a);
        return that;
    };
    
    this.render = function()
    {               
        //set odd/even global classes
        if(that.useOddEven)
        {
            that.globalClasses['tr'] = that.globalClasses['tr'] ? that.globalClasses['tr'] : [];
            that.addGlobalClass('tr-odd',that.globalClasses['tr'].concat([that.oddEven['odd']]).join(' '));
            that.addGlobalClass('tr-even',that.globalClasses['tr'].concat([that.oddEven['even']]).join(' '));
        }
        var output = [];
        
        output.push('<','table');
        if(that.id)
        {
            output.push(' id="',that.id,'"');
        }
        if(that.globalClasses['table'])
        {
            output.push(' ',"class","=",'"',that.globalClasses['table'].join(''),'"');
        }
        output.push('>');
        
        var stringHead = renderRowFromArrayTh(that.headers);
        
        if(that.useSearch)
        {
            stringHead += '<tr class="odd"><td colspan="'+that.headers.length+'"><input type="search" class="pulsestorm_search_table_field"></div></td></tr>';
        }
        
        if(that.rowsHead.length > 0)
        {
            var originalPositionedCellCss = that.positionedCellCss; 
            that.positionedCellCss = [];
            stringHead += jQueryCommercebug.map(that.rowsHead,renderRowFromArrayTd).join('');                        
            that.positionedCellCss = originalPositionedCellCss;
        }
        
        var thead = wrapStringInTag(stringHead,'thead');
        output.push(thead);     
        
        stringBody = jQueryCommercebug.map(that.rows,renderRowFromArrayTd).join('');
        var tbody = wrapStringInTag(stringBody,'tbody');                
        
        output.push(tbody); 
        
        if(that.rowsFooter.length > 0)
        {
            var originalPositionedCellCss = that.positionedCellCss; 
            that.positionedCellCss = [];
            var tfoot = wrapStringInTag(jQueryCommercebug.map(that.rowsFooter,renderRowFromArrayTd).join(''),'tfoot');
            output.push(tfoot);
            that.positionedCellCss = originalPositionedCellCss;
        }
        
        output.push('</table>');
        
        return output.join('');
    };
    
    var wrapStringInTag = function(inside,out)
    {
        var chunk = ['<',out];
        if(that.globalClasses[out])
        {
            chunk = chunk.concat([' class="',that.globalClasses[out].join(' '),'"']);
        }       
        chunk = chunk.concat(['>',inside,'</',out,'>']);
        return chunk.join('');
    };
    
    var renderRowFromArrayTd = function(a)
    {
        return renderRowFromArray(a,'td');
    };

    var renderRowFromArrayTh = function(a)
    {
        return renderRowFromArray(a,'th');
    };
        
    var renderRowFromArray = function(a,tag)
    {
        var row = [];
        row.push('<tr');        
        
        var key = that.rowCount % 2 == 0 ? 'tr-even' : 'tr-odd';
        if(that.globalClasses[key]){
            row.push(' ','class','=','"',that.globalClasses[key].join(' '),'"');
        };
        row.push('>');
        
        row.push(that.renderLineOfCells(tag,a));
        
        row.push('</tr>');
        
        that.rowCount++;
        return row.join('');
    };
    
    this.renderLineOfCells = function(tag, a)
    {
        var row = [];
        
        jQueryCommercebug.each(a,function(k,v){         
            var classNames = [];
            row.push('<',tag);
            if(that.globalClasses[tag]){
                classNames = classNames.concat(that.globalClasses[tag]);
            };      
            
            if(tag == 'td')
            {
                var key = 'pos'+k;
                if(that.positionedCellCss[key])
                {
                    classNames = classNames.concat(that.positionedCellCss[key]);
                }
            }

            if(tag == 'th')
            {
                var key = 'pos'+k;
                if(that.positionedHeadCss[key])
                {
                    classNames = classNames.concat(that.positionedHeadCss[key]);
                }
            }            
            if(classNames.length > 0)
            {
                row.push(' ','class','=','"',classNames.join(' '),'"');
            }

            row.push('>',v,'</',tag,'>');       
        });
        
        return row.join('');
    };
}