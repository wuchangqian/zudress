<?php
class Zhangguoping_Commercebug_Block_Jquery_Head extends Zhangguoping_Commercebug_Block_Html
{
    protected function _toHtml()
    {
        ob_start();
        ?>
        <?php $skin_path = $this->getPathSkin(); ?>
        <link type="text/css" href="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/css/tablesorter/blue/style.css" rel="stylesheet" />
        <link type="text/css" href="<?php echo $skin_path; ?>/jquery-ui-1.8.custom/css/overcast/jquery-ui-1.8.custom.css" rel="stylesheet" />
        <link type="text/css" href="<?php echo $skin_path; ?>/commercebug.css" rel="stylesheet" />            
        <?php        
        return ob_get_clean();
    }
}