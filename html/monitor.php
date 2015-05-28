<?php
/**
 ** RRD page for iou-web
 **
 ** This file allows to monitor rrd graphs created by rrd.py util
 **
 ** @author Andrea Dainese <andrea.dainese@gmail.com>
 ** @license http://www.gnu.org/licenses/gpl.html
 **/
include('includes/conf.php');
header('Refresh: 10;url='.BASE_WWW.'/monitor.php');
page_header('Monitor');
?>
<div class='block'>
    <div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
        <div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
            <h1 class='bar'>Monitoring <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/graphs/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
        </div>
        <div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
<?php
$previous_host = '';
$current_host = '';
foreach (scandir('/opt/iou/data/Rrd/') as $file) {
    // $lab_id = preg_replace('/[^0-9]/s', '', $name);
    if (preg_match('/\.png$/', $file)) {
        $current_host = preg_replace('/^([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)\.png$/s', '$1.$2.$3.$4', $file);
        if ($current_host != $previous_host) {
?>
            <h2><?php print $current_host ?></h2>
            <img alt="RRD Graph" border='0' src='<?php print BASE_WWW ?>/downloads/Rrd/<?php print $file ?>'/>
<?php
        } else {
?>
            <img alt="RRD Graph" border='0' src='<?php print BASE_WWW ?>/downloads/Rrd/<?php print $file ?>'/>
<?php
        }
    }
    $previous_host = $current_host;
}
?>
        </div>
    </div>
</div>
<?php
page_footer();
?>
