<?php
class Zhangguoping_Commercebug_Block_Jquery_Bodyend extends Zhangguoping_Commercebug_Block_Html
{
    protected function _toHtml()
    {
        $data = new stdClass;
        $data->tabIdPairs = $this->getTabIdPairs();
        $json = $this->getShim()->getSingleton('commercebug/jsonbroker')->jsonEncode($data);
        
        ob_start();
        ?>
        <?php $skin_path = $this->getPathSkin(); ?>
        <script type="text/javascript"></script>
        <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery-1.8.1.min.js"></script>        
        <script type="text/javascript" src="<?php echo $skin_path; ?>/plugins/jquery.form.js"></script>
        <script type="text/javascript">jQueryCommercebug.noConflict();</script>
        <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery-ui-1.8.custom.min.js"></script>
        <script type="text/javascript" src="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/js/jquery.tablesorter.min.js"></script>            
        <script type="text/javascript" src="<?php echo $skin_path; ?>/index-of.js"></script>
        <script type="text/javascript" src="<?php echo $skin_path; ?>/Pulsestorm_Html_Table.js"></script>
        <script type="text/javascript" src="<?php echo $skin_path; ?>/commercebug.1.6.js"></script>
        <script type="text/javascript">  
        //<![CDATA[
            jQueryCommercebug(document).ready(function(){
                var $ = jQueryCommercebug;
                
                var html_debug = '<div id="ascommercebug_link_debug">+ <a href="#" id="ascommercebug_showhide">Debug</a></div>';
                <?php echo $this->_renderJsonAssignment('tab_data', $data); ?>
                
                var html_skeleton = [];
                html_skeleton.push('<div id="ascommercebug-tabs" style="display:none;">');
                    html_skeleton.push('<ul>');
                        $.each(tab_data.tabIdPairs, function(key,value){
                            html_skeleton.push('<li><a href="#'+key+'"><span>'+value+'</span></a></li>');
                        });
                    html_skeleton.push('</ul>');
                    
                    $.each(tab_data.tabIdPairs, function(key, value){
                        html_skeleton.push('<div id="'+key+'" class="tab-container"></div>');
                    });
                    
                html_skeleton.push('</div>');                
                $('body').prepend(html_debug + html_skeleton.join(''));                
            });
        //]]>            
        </script>
        <?php
        return ob_get_clean();
    }
}