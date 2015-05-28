<?php
/**
 * Laboratories page for iou-web
 * 
 * This file allows to navigate between folders, select a lab and start devices
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */
include('includes/conf.php');
page_header('Laboratories');

if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = 'default';
}

switch ($action) {
/*************************************************************************
 * List all laboratories (default case)                                  *
 *************************************************************************/
	default:
?>
<script type='text/javascript'><!--
	$(document).ready(function() {
<?php
		if(is_admin()) {
?>
		$('.folder_draggable,.img,.lab,.cfg').draggable();
		$('.folder_droppable,.parent_folder').droppable({
			drop: function(event, ui) {
				if ($(ui.draggable).attr('id') == undefined) {
					var name = encodeURIComponent('cfg' + $(ui.draggable).closest('li').find('.cfg_pack').val());
				} else {
					var name = $(ui.draggable).attr('id');
				}
				var folder_dst = $(this).attr('id');
				var url = '<?php print BASE_WWW ?>/ajax_helper.php?action=folder_move&src_obj=' + name + '&folder_dst=' + folder_dst;
				$.get(url);
				$(ui.draggable).fadeOut(1000);
			}
		});
		$('.folder a.wipe').click(function() {
			var folder_id = $(this).closest('li').attr('id').replace(/folder/g, '');
			var folder_name = $(this).closest('li').find('a').text();
			var folder_path = $(this).closest('li').find('.path').val();
			var delete_url = '<?php print BASE_WWW ?>/ajax_helper.php?action=folder_delete&folder_id=' + folder_id;
			$('#dialog').attr('title', 'Confirm folder deletion');
			$('#dialog').html('Are you sure you want to delete <strong>' + folder_name + '</strong> folder?');
			$('#dialog').dialog({
				modal: true, 
				show: 'fade', 
				hide: 'fade',
				resizable: false,
				buttons: {
					Ok: function() {
						$.get(delete_url);
						setTimeout(function(){
							$(this).dialog('destroy');
							location.reload();
						}, 100);
					},
					Cancel: function() {
						$(this).dialog('destroy');
					}
				}
			});
		});
		$('.lab a.wipe').click(function() {
			var lab_id = $(this).closest('li').attr('id').replace(/lab/g, '');
			var lab_name = $(this).closest('li').find('a').text();
			var delete_url = '<?php print BASE_WWW ?>/ajax_helper.php?action=lab_delete&lab_id=' + lab_id;
			$('#dialog').attr('title', 'Confirm lab deletion');
			$('#dialog').html('Are you sure you want to delete <strong>' + lab_name + '</strong> lab?');
			$('#dialog').dialog({
				modal: true, 
				show: 'fade', 
				hide: 'fade',
				resizable: false,
				buttons: {
					Ok: function() {
						$.get(delete_url);
						setTimeout(function(){
							$(this).dialog('destroy');
							location.reload();
						}, 100);
					},
					Cancel: function() {
						$(this).dialog('destroy');
					}
				}
			});
		});
		$('.img a.wipe').click(function() {
			var img_id = $(this).closest('li').attr('id').replace(/img/g, '');
			var img_name = $(this).closest('li').find('a').text();
			var delete_url = '<?php print BASE_WWW ?>/ajax_helper.php?action=img_delete&img_id=' + img_id;
			$('#dialog').attr('title', 'Confirm image deletion');
			$('#dialog').html('Are you sure you want to delete <strong>' + img_name + '</strong> image?');
			$('#dialog').dialog({
				modal: true, 
				show: 'fade', 
				hide: 'fade',
				resizable: false,
				buttons: {
					Ok: function() {
						$.get(delete_url);
						$('#img' + img_id).fadeOut(1000);
						setTimeout(function(){
							$(this).dialog('destroy');
							location.reload();
						}, 100);
					},
					Cancel: function() {
						$(this).dialog('destroy');
					}
				}
			});
		});
		$('.cfg a.wipe').click(function() {
			var cfg_name = $(this).closest('li').find('a').text();
			var delete_url = '<?php print BASE_WWW ?>/ajax_helper.php?action=cfg_delete&cfg_name=' + encodeURIComponent(cfg_name);
			$('#dialog').attr('title', 'Confirm config deletion');
			$('#dialog').html('Are you sure you want to delete <strong>' + cfg_name + '</strong> config pack?');
			$('#dialog').dialog({
				modal: true, 
				show: 'fade', 
				hide: 'fade',
				resizable: false,
				buttons: {
					Ok: function() {
						$.get(delete_url);
						setTimeout(function(){
							$(this).dialog('destroy');
							location.reload();
						}, 100);
					},
					Cancel: function() {
						$(this).dialog('destroy');
					}
				}
			});
		});
		$('#addFolder').click(function() {
			var folder_parent = '<?php print $_SESSION['current_folder'] -> id ?>';
			var add_url = '<?php print BASE_WWW ?>/ajax_helper.php?action=folder_add';
			$('#dialog').attr('title', 'Add folder <a href="http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#folder_add" target="_blank"><img class="help" border="0" src="<?php print BASE_WWW ?>/images/buttons/help_small.png" width="16" height="16" title="Online Help" alt="Online Help" /></a>');
			$('#dialog').html("<script type='text/javascript'>$.validator.addMethod('regexp',function(value, element, regexp) {var check = false;return this.optional(element) || regexp.test(value);},'Please check your input and use only allowed characters.');$(document).ready(function(){$('form').validate({rules: {folder_name: {required: true,regexp: /^[A-Za-z0-9_ -]+$/}}});});</script><form><p><label for='folder_name'>Folder name*:<br/><small>(A-Za-z0-9_ -)</small></label><input id='folder_name' type='text' name='folder_name' /></p></form>");
			$('#dialog').dialog({
				modal: true,
				show: 'fade',
				hide: 'fade',
				resizable: false,
				buttons: {
					'Add': function() {
						folder_name = $(this).find('#folder_name').val();
						addFolder('<?php print BASE_WWW ?>', folder_name, folder_parent);
					},
					Cancel: function() {
						$(this).dialog('destroy');
					}
				}
			});
		});
<?php
		}
?>
	});
--></script>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Laboratories <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
<?php
		if (is_admin()) {
?>
			<div align='right' id='buttons'>
				<a href='#'><img border='0' id='addFolder' src='<?php print BASE_WWW ?>/images/buttons/addfolder.png' width='32' height='32' title='Add Folder' alt='Add Folder' /></a>
				<a href='<?php print BASE_WWW ?>/manage.php?action=lab_add'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/addlab.png' width='32' height='32' title='Add new lab' alt='Add new lab' /></a>
				<a href='<?php print BASE_WWW ?>/manage.php?action=cfg_add'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/addconfig.png' width='32' height='32' title='Add new config' alt='Add new config' /></a>
				<a href='<?php print BASE_WWW ?>/manage.php?action=img_add'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/addimg.png' width='32' height='32' title='Add new image' alt='Add new image' /></a>
			</div>
<?php
		}
		if (isset($_SESSION['current_lab'])) {
?>
			<p>Last opened lab:  <a class='open' href='<?php print BASE_WWW ?>/laboratories.php?action=lab_open&lab_id=<?php print $_SESSION['current_lab'] -> id ?>#tabs-3'><img border='0' class='inline' src='<?php print BASE_WWW ?>/images/buttons/lab.png' width='16' height='16' alt='Open last lab' title='Open last lab' /><?php print $_SESSION['current_lab'] -> name ?>: <?php print $_SESSION['current_lab'] -> description ?></a></p>
<?php
		}
?>
			<p>Listing content of: <?php print $_SESSION['current_folder'] -> path ?></p>
			<div id='list'>
				<ul>
<?php
		// If not root folder, then print ..
		if ($_SESSION['current_folder'] -> id != 0) {
?>
					<li class='folder folder_droppable' id='folder<?php print $_SESSION['current_folder'] -> parent_id ?>'>
						<a class='open' href='<?php print BASE_WWW ?>/laboratories.php?folder_id=<?php print $_SESSION['current_folder'] -> parent_id ?>'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/folder.png' width='32' height='32' alt='Up one level' title='Up one level' />..</a>
					</li>
<?php
		}
		// Printing current child folders
		foreach ($GLOBAL['all_folders'] as $folder) {
			if ($folder -> parent_id == $_SESSION['current_folder'] -> id) {
?>
					<li class='folder folder_draggable folder_droppable' id='folder<?php print $folder -> id ?>'>
						<a class='open' href='<?php print BASE_WWW ?>/laboratories.php?folder_id=<?php print $folder-> id ?>'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/folder.png' width='32' height='32' alt='Open this folder' title='Open this folder' /><?php print $folder -> name ?></a>
<?php
				if(is_admin()) {
?>
						<a class='edit' href='<?php print BASE_WWW ?>/manage.php?action=edit_folder?folder_id=<?php print $folder -> id ?>'><img border='0' class='button1' src='<?php print BASE_WWW ?>/images/buttons/edit.png' width='32' height='32' alt='Edit this folder' title='Edit this folder' /></a>
						<a class='wipe' href='#'><img border='0' class='button2' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' width='32' height='32' alt='Delete this folder' title='Delete this folder' /></a>
<?php
				}
?>
					</li>
<?php			
			}
		}
		// Printing current child labs
		foreach ($GLOBAL['all_labs'] as $lab) {
			if ($lab -> folder_id == $_SESSION['current_folder'] -> id) {
?>
					<li class='lab' id='lab<?php print $lab -> id ?>'>
						<a class='open' href='<?php print BASE_WWW ?>/laboratories.php?action=lab_open&amp;lab_id=<?php print $lab -> id ?>#tabs-3'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/lab.png' width='32' height='32' alt='Open this lab' title='Open this lab' /><?php print $lab -> name ?>: <?php print $lab -> description ?></a>
<?php
			if(is_admin()) {
?>
						<a class='edit' href='<?php print BASE_WWW ?>/manage.php?action=lab_edit&amp;lab_id=<?php print $lab -> id ?>'><img border='0' class='button1' src='<?php print BASE_WWW ?>/images/buttons/edit.png' width='32' height='32' alt='Edit this lab' title='Edit this lab' /></a>
						<a class='wipe' href='#'><img border='0' class='button2' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' width='32' height='32' alt='Delete this lab' title='Delete this lab' /></a>
<?php
			}
?>
					</li>
<?php			
			}
		}
		// Printing current child images
		foreach ($GLOBAL['all_images'] as $image) {
			if ($image -> folder_id == $_SESSION['current_folder'] -> id) {
?>
					<li class='img' id='img<?php print $image -> id ?>'>
						<a class='open' href='<?php print BASE_WWW ?>/laboratories.php?action=img_open&amp;img_id=<?php print $image -> id ?>'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/img.png' width='32' height='32' alt='Open this image' title='Open this image' /><?php print htmlentities($image -> name) ?></a>
<?php
				if(is_admin()) {
?>
						<a class='edit' href='<?php print BASE_WWW ?>/manage.php?action=img_edit&amp;img_id=<?php print $image -> id ?>'><img border='0' class='button1' src='<?php print BASE_WWW ?>/images/buttons/edit.png' width='32' height='32' alt='Edit this image' title='Edit this image' /></a>
						<a class='wipe' href='#'><img border='0' class='button2' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' width='32' height='32' alt='Delete this image' title='Delete this image' /></a>
<?php
				}
?>
					</li>
<?php			
			}
		}
		// Printing current child config packs
		foreach ($GLOBAL['all_configpacks'] as $configpack) {
			if ($configpack -> folder_id == $_SESSION['current_folder'] -> id) {
?>
					<li class='cfg'>
						<a class='open' href='<?php print BASE_WWW ?>/laboratories.php?action=cfg_open&amp;cfg_pack=<?php print $configpack -> name ?>'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/config.png' width='32' height='32' alt='Open this config' title='Open this config' /><?php print $configpack -> name ?></a>
<?php
				if(is_admin()) {
?>
						<a class='edit' href='<?php print BASE_WWW ?>/manage.php?action=cfg_edit&amp;cfg_pack=<?php print rawurlencode($configpack -> name) ?>'><img border='0' class='button1' src='<?php print BASE_WWW ?>/images/buttons/edit.png' width='32' height='32' alt='Edit this config' title='Edit this config' /></a>
						<a class='wipe' href='#'><img border='0' class='button2' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' width='32' height='32' alt='Delete this config' title='Delete this config' /></a>
<?php
				}
?>
						<input type='hidden' class='cfg_pack' value='<?php print rawurlencode($configpack -> name) ?>' />
					</li>
<?php
			}
		}
?>
				</ul>
			</div>
		</div>
	</div>
</div>
<?php
		break;
/*************************************************************************
 * Open an image                                                         *
 *************************************************************************/
	case 'img_open': 
?>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'><?php print $_SESSION['current_img'] -> name ?></h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom' id='list'>
			<div><img border='0' src='<?php print BASE_WWW ?>/ajax_helper.php?action=img_show&amp;img_id=<?php print $_SESSION['current_img'] -> id ?>' alt='<?php print $_SESSION['current_img'] -> name ?>' /></div>
			<div><?php print $_SESSION['current_img'] -> info ?></div>
		</div>
	</div>
</div>
<?php
		break;
 /*************************************************************************
 * Open a lab                                                            *
 *************************************************************************/
	case 'lab_open':
		if(is_admin() && isset($_POST['action']) && $_POST['action'] == 'Save positions') {
			foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
				$_SESSION['current_lab'] -> devices[$netmap_id] -> top = $_POST[$netmap_id.'top'];
				$_SESSION['current_lab'] -> devices[$netmap_id] -> left = $_POST[$netmap_id.'left'];
			}
			foreach ($_SESSION['current_lab'] -> netmap_hubs as $netmap_hub) {
				$_SESSION['current_lab'] -> devices[$netmap_hub] -> top = $_POST[$netmap_hub.'top'];
				$_SESSION['current_lab'] -> devices[$netmap_hub] -> left = $_POST[$netmap_hub.'left'];
			}			
			$_SESSION['current_lab'] -> save();
		}
?>
<script type='text/javascript'><!--
	$(document).ready(function() {
		// Scrolling to the top
		$('html, body').animate({scrollTop:0}, 'slow');
		$('.netmap').height($(window).height() - 170);
		
		setInterval("updateDeviceStatus('<?php print BASE_WWW ?>', '<?php print $_SESSION['current_lab'] -> id ?>')", <?php print UPDATE_INTERVAL*1000 ?>);
		$('.solutions').hide();
		$('.help').click(function() {
			$(this).next('.solutions').slideToggle();
		});
		$('.action').click(function() {
			var base_www = '<?php print BASE_WWW ?>';
			var lab_id = '<?php print $_SESSION['current_lab'] -> id ?>';
			var action = $(this).attr('id').split('_')[0] + '_' + $(this).attr('id').split('_')[1];
			var dev_id = $(this).attr('id').split('_')[2];
			var img_original = $(this).attr("src");
			var img_wait = '<?php print BASE_WWW ?>/images/buttons/wait.gif';
			switch (action) {
				case 'dev_start':
					startDevice(base_www, dev_id);
					break;
				case 'dev_stop':
					stopDevice(base_www, dev_id);
					break;
				case 'dev_reset':
					resetDevice(base_www, dev_id);
					break;
				case 'cfg_export':
					exportConfig(base_www, dev_id);
					break;
				case 'cfg_snapshot':
					deviceSnapshot(base_www, dev_id);
					break;
				case 'cfg_revert':
					deviceRevert(base_www, dev_id);
					break;
				case 'cfg_clean':
					deviceClean(base_www, dev_id);
					break;
				case 'sniffer_start':
					sniffer(base_www, 'sniffer_start');
					break;
				case 'sniffer_stop':
					sniffer(base_www, 'sniffer_stop');
					break;
			}
			$(this).attr('src', img_wait).delay(2000).fadeOut(0, function() {
				$(this).attr('src', img_original).fadeIn(0);
			});
		});
		$('#tabs').tabs({
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$(anchor.hash).html("Couldn't load this tab. We'll try to fix this as soon as possible.");
				}
			}
		});
	});
--></script>
<?php
		$lab_remaing_time = $_SESSION['current_lab'] -> time * 60 - (time() - $_SESSION['current_lab_start']);
		if ($lab_remaing_time > 0) {
?>
<script type="text/javascript"><!--
$(function () {
	$('#countdown').countdown({until: '+<?php print $lab_remaing_time ?>s', compact: true});
});
--></script>
<div id="countdown" class='ui-corner-all'></div>
<?php
		}
?>
<div class='block'>
	<div id='tabs' class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<ul class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<li><a href='#tabs-1'>Devices</a> <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_devices' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></li>
			<li><a href='#tabs-2'>Description</a></li>
<?php
		if ($_SESSION['current_lab'] -> diagram) {
?>
			<li><a href='#tabs-3'>Diagram</a> <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_diagram' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></li>
<?php
		}
		$tab = $_SESSION['current_lab'] -> diagram == true ? 4 : 3;
		foreach ($_SESSION['current_lab'] -> images as $image) {
?>
			<li><a href='#tabs-<?php print $tab ?>'><?php print $image -> name ?></a></li>
<?php
			$tab++;
		}
?>
		</ul>
<?php
/*************************************************************************
 * Tab #1: Devices                                                       *
 *************************************************************************/
 ?>
		<div id='tabs-1' class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<h1><?php print $_SESSION['current_lab'] -> name ?></h1>
			<h2><?php print $_SESSION['current_lab'] -> description ?></h2>
			<table width='100%'>
				<tr>
					<th colspan='2'>Name</th>
					<th>IOS</th>
					<th>RAM/NVRAM</th>
					<th>Interfaces</th>									
					<th>L2 Keepalive</th>									
					<th>Watchdog</th>									
					<th>Actions</th>
				</tr>
				<tr>
					<td align='center'><img border='0' id='dev_small_status_sniffer' class='sniffer_status' src='<?php print BASE_WWW ?>/images/devices/nam_small.png' alt="Sniffer status" title="Sniffer status" /></td>
					<td><strong>All Devices</strong></td>
					<td align='center'>-</td>
					<td align='center'>-</td>
					<td align='center'>-</td>
					<td align='center'>-</td>
					<td align='center'>-</td>
					<td align='center'>
						<a href='#'><img border='0' class='action' id='dev_start_all' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/play.png' title='Start all devices' alt='Start all devices' /></a>
						<a href='#'><img border='0' class='action' id='dev_stop_all' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/stop.png' title='Stop all devices' alt='Stop all devices' /></a>
<?php
		if(is_admin()) {
?>
						<a href='#'><img border='0' class='action' id='cfg_export_all' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/export.png' title='Copy all unix://running-config files to database' alt='Copy all unix://running-config files to database' /></a>
<?php
		}
?>
						<a href='#'><img border='0' class='action' id='cfg_snapshot_all' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/snapshot.png' title='Make a snapshot of all devices' alt='Make a snapshot of all devices' /></a>
						<a href='#'><img border='0' class='action' id='cfg_revert_all' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/revert.png' title='Stop and revert to snapsot all devices' alt='Stop and revert to snapsot all devices' /></a>
						<a href='#'><img border='0' class='action' id='cfg_clean_all' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' title='Stop all devices and wipe all configurations' alt='Stop all devices and wipe all configurations' /></a>
						<a href='#'><img border='0' class='action' id='sniffer_start' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/wireshark_start.png' title='Start sniffer' alt='Start sniffer' /></a>
						<a href='#'><img border='0' class='action' id='sniffer_stop' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/wireshark_stop.png' title='Stop sniffer' alt='Stop sniffer' /></a>
                                                <a href='#'><img border='0' class='action' id='dev_reset_all' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/reset.png' title='Reset all consoles' alt='Reset all consoles' /></a>
<?php
                if(is_admin()) {
?>
                                                <a href='#'><img border='0' class='action' id='dev_stop_global' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/bomb.png' title='Stop everything' alt='Stop everything' /></a>
<?php
                }
?>
					</td>
				</tr>
<?php
		foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
?>
					<tr>
						<td align='center'><img border='0' id='dev_small_status_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' class='dev_status' src='<?php print BASE_WWW ?>/images/devices/<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> picture ?>_small.png' /></td>
						<td><?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name.' ('.$_SESSION['current_lab'] -> devices[$netmap_id] -> id.')' ?></td>
<?php
			if($_SESSION['current_lab'] -> devices[$netmap_id] -> isCloud()) {
				$ios = '-';
				$nv_ram = '-';
				$int = $_SESSION['current_lab'] -> devices[$netmap_id] -> ethernet;
				$l2keepalive = '-';
				$watchdog = '-';
			} else {
				$ios = $_SESSION['current_lab'] -> devices[$netmap_id] -> bin_name;
				$nv_ram = $_SESSION['current_lab'] -> devices[$netmap_id] -> ram.'MB/'.$_SESSION['current_lab'] -> devices[$netmap_id] -> nvram.'KB';
				$int = (4*$_SESSION['current_lab'] -> devices[$netmap_id] -> ethernet).'e/'.(4*$_SESSION['current_lab'] -> devices[$netmap_id] -> serial).'s';
				if($_SESSION['current_lab'] -> devices[$netmap_id] -> l2keepalive) {
					$l2keepalive = '<input type=\'checkbox\' checked disabled />';
				} else {
					$l2keepalive = '<input type=\'checkbox\' disabled />';
				}
				if($_SESSION['current_lab'] -> devices[$netmap_id] -> watchdog) {
					$watchdog = '<input type=\'checkbox\' checked disabled />';
				} else {
					$watchdog = '<input type=\'checkbox\' disabled />';
				}
			}
?>
						<td align='center'><?php print $ios ?></td>
						<td align='center'><?php print $nv_ram ?></td>
						<td align='center'><?php print $int ?></td>
						<td align='center'><?php print $l2keepalive ?></td>
						<td align='center'><?php print $watchdog ?></td>
						<td align='center'>
							<a href='#'><img border='0' class='action' id='dev_start_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/play.png' title='Start <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?>'></a>
							<a href='#'><img border='0' class='action' id='dev_stop_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/stop.png' title='Stop <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?>'></a>
<?php
			if($_SESSION['current_lab'] -> devices[$netmap_id] -> isCloud()) {
                                if(is_admin()) {
?>
                                                        <img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
<?php
                                }
?>
							<img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
							<img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
							<img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
							<img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
							<img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
							<img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
<?php
                                if(is_admin()) {
?>
                                                        <img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
<?php
                                }
?>
<?php 
			} else {
				if(is_admin()) {
?>
										
							<a href='#'><img border='0' class='action' id='cfg_export_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/export.png' title='Copy unix://running-config file to database'></a>
<?php
                                }
?>
							<a href='#'><img border='0' class='action' id='cfg_snapshot_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/snapshot.png' title='Make a snapshot'></a>
							<a href='#'><img border='0' class='action' id='cfg_revert_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/revert.png' title='Stop <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?> and revert to snapshot'></a>
							<a href='#'><img border='0' class='action' id='cfg_clean_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' title='Stop <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?> and wipe all configurations'></a>
							<a href='<?php print 'telnet://'.$_SERVER['HTTP_HOST'].':'.$_SESSION['current_lab'] -> devices[$netmap_id] -> console ?>'><img border='0' class='action' id='console_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/console.png' title='Open <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?> console'></a>
							<a href='<?php print BASE_WWW ?>/cgi-bin/console?<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> console?>' target='_blank'><img border='0' class='action' id='console_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/shellinabox.png' title='Open <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?> console via browser'></a>
                                                        <a href='#'><img border='0' class='action' id='dev_reset_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' width='20' height='20' src='<?php print BASE_WWW ?>/images/buttons/reset.png' title='Reset <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?> console'></a>
<?php
                                if(is_admin()) {
?>
                                                        <img border='0' src='<?php print BASE_WWW ?>/images/buttons/spacer_20.png' width='20' height='1' title='Spacer'>
<?php
                                }
?>
						</td>
<?php
			}
?>
					</tr>
<?php
		}
?>
			</table>
		</div>
<?php
/*************************************************************************
 * Tab #2: Description                                                   *
 *************************************************************************/
 ?>
		<div id='tabs-2' class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<h1><?php print $_SESSION['current_lab'] -> name ?></h1>
			<h2><?php print $_SESSION['current_lab'] -> description ?></h2>
			<?php print $_SESSION['current_lab'] -> info ?>
		</div>
<?php
/*************************************************************************
 * Tab #3: Diagram                                                       *
 *************************************************************************/
		if ($_SESSION['current_lab'] -> diagram) {
 ?>
		<div id='tabs-3' class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
<?php
			if(is_admin()) {
?>
<form class='netmap_form' action='#tabs-3' method='post'>
	<input type='submit' name='action' value='Save positions'>
<?php
				foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
?>
	<input id='<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>top' type='hidden' name='<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>top' value=''>
	<input id='<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>left' type='hidden' name='<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>left' value=''>
<?php
				}
				foreach ($_SESSION['current_lab'] -> netmap_hubs as $netmap_hub) {
?>
	<input id='<?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> id ?>top' type='hidden' name='<?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> id ?>top' value=''>
	<input id='<?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> id ?>left' type='hidden' name='<?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> id ?>left' value=''>
<?php
				}
?>
	<input type='hidden' name='lab_id' value='<?php print $_SESSION['current_lab'] -> id ?>'>
</form>
<?php
			}
?>
<div class='netmap'>
<?php			
			
			// Print all nodes
			foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
?>
	<div class='window' id='node<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' style='top: <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> top == 0 || $_SESSION['current_lab'] -> devices[$netmap_id] -> top >= 100 ? rand(30, 70) : $_SESSION['current_lab'] -> devices[$netmap_id] -> top ?>%; left: <?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> left == 0 || $_SESSION['current_lab'] -> devices[$netmap_id] -> left >= 100 ? rand(30, 70) : $_SESSION['current_lab'] -> devices[$netmap_id] -> left ?>%;'>
		<a href='telnet://<?php print $_SERVER['HTTP_HOST'].':'.(BASE_PORT+$_SESSION['current_lab'] -> devices[$netmap_id] -> id) ?>'>
			<div class="dev_menu">
                <img border='0' id='dev_status_<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' class='dev_status' src='<?php print BASE_WWW ?>/images/devices/<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> picture ?>.png' />
            </div>
		</a>
		<div class='name'><?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?></div>
		<input type='hidden' class='dev_id' value='<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>' />
	</div>
<?php
			}
			// Print all hubs
			$total_hubs = 0;
			foreach ($_SESSION['current_lab'] -> netmap_hubs as $netmap_hub) {
				$total_hubs++;
?>
	<div class='window' id='node<?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> id ?>' style='top: <?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> top == 0 || $_SESSION['current_lab'] -> devices[$netmap_hub] -> top >= 100 ? rand(30, 70) : $_SESSION['current_lab'] -> devices[$netmap_hub] -> top ?>%; left: <?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> left == 0 || $_SESSION['current_lab'] -> devices[$netmap_hub] -> left >= 100 ? rand(30, 70) : $_SESSION['current_lab'] -> devices[$netmap_hub] -> left ?>%;'>
		<img border='0' id='dev_status_<?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> id ?>' src='<?php print BASE_WWW ?>/images/devices/hub.png' alt='Hub' title='Hub' />
	</div>
<?php
			}
?>
</div>
<script type='text/javascript' src='<?php print BASE_WWW ?>/js/jquery.jsPlumb-1.3.14-all-min.js'></script>
<script type='text/javascript' src='<?php print BASE_WWW ?>/js/netmap.js.php'></script>
<script type='text/javascript'><!--
	var base_www = '<?php print BASE_WWW ?>';
	var lab_id = '<?php print $_SESSION['current_lab'] -> id ?>';
	var menu = [
		$.contextMenu.separator,
		{ 'Start': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					startDevice(base_www, dev_id);
				},
				icon:'/images/buttons/play_small.png'
		} },
		{ 'Start All': {
				onclick:function(){
					startDevice(base_www, 'all');
				},
				icon:'/images/buttons/play_small.png'
		} },
		$.contextMenu.separator,
		{ 'Stop': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					stopDevice(base_www, dev_id);
				},
				icon:'/images/buttons/stop_small.png'
		} },
		{ 'Stop All': {
				onclick:function(){
					stopDevice(base_www, 'all');
				},
				icon:'/images/buttons/stop_small.png'
		} },
		{ 'Stop Everything': {
				onclick:function(){
					stopDevice(base_www, 'global');
				},
				icon:'/images/buttons/bomb_small.png'
		} },
		$.contextMenu.separator,
		{ 'Copy unix://running-config file to database': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					exportConfig(base_www, dev_id);
				},
				icon:'/images/buttons/export_small.png'
		} },
		{ 'Copy all unix://running-config files to database': {
				onclick:function(){
					exportConfig(base_www, 'all');
				},
				icon:'/images/buttons/export_small.png'
		} },
		$.contextMenu.separator,
		{ 'Make a snapshot': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					deviceSnapshot(base_www, dev_id);
				},
				icon:'/images/buttons/snapshot_small.png'
		} },
		{ 'Make a snapshot of all devices': {
				onclick:function(){
					deviceSnapshot(base_www, 'all');
				},
				icon:'/images/buttons/snapshot_small.png'
		} },
		$.contextMenu.separator,
		{ 'Stop and and revert to snapshot': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					deviceRevert(base_www, dev_id);
				},
				icon:'/images/buttons/revert_small.png'
		} },
		{ 'Stop and and revert to snapshot all devices': {
				onclick:function(){
					deviceRevert(base_www, 'all');
				},
				icon:'/images/buttons/revert_small.png'
		} },
		$.contextMenu.separator,
		{ 'Stop and wipe all configurations': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					deviceClean(base_www, dev_id);
				},
				icon:'/images/buttons/wipe_small.png'
		} },
		{ 'Stop and wipe all device configurations': {
				onclick:function(){
					deviceClean(base_www, 'all');
				},
				icon:'/images/buttons/wipe_small.png'
		} },
		$.contextMenu.separator,
		{ 'Open console': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					var telnet_port = (parseInt(dev_id) + <?php print BASE_PORT ?>);
					document.location.href = 'telnet://<?php print $_SERVER['HTTP_HOST'] ?>:' + telnet_port;
				},
				icon:'/images/buttons/console_small.png'
		} },
		$.contextMenu.separator,
		{ 'Open console via browser': {
				onclick:function(){
					var dev_id = $(this).closest('.window').find('.dev_id').val();
					var telnet_port = (parseInt(dev_id) + <?php print BASE_PORT ?>);
					window.open('<?php print BASE_WWW ?>/cgi-bin/console?' + telnet_port);
				},
				icon:'/images/buttons/shellinabox_small.png'
		} },
		$.contextMenu.separator,
		{ 'Reset console': {
                onclick:function(){
                    var dev_id = $(this).closest('.window').find('.dev_id').val();
                    resetDevice(base_www, dev_id);
                },
                icon:'/images/buttons/reset_small.png'
        } },
        { 'Reset all consoles': {
                onclick:function(){
                    resetDevice(base_www, 'all');
                },
                icon:'/images/buttons/reset_small.png'
        } },
		$.contextMenu.separator,
		{ 'Start sniffer': {
				onclick:function(){
					sniffer(base_www, 'sniffer_start');
				},
				icon:'/images/buttons/wireshark_start_small.png'
		} },
		{ 'Stop sniffer': {
				onclick:function(){
					sniffer(base_www, 'sniffer_stop');
				},
				icon:'/images/buttons/wireshark_stop_small.png'
		} }
	];
	$(document).ready(function() {
		$('.dev_menu').contextMenu(menu,{theme:'vista'});
	});

--></script>
</div>
<?php
		}
/*************************************************************************
 * Tab #4: Image                                                         *
 *************************************************************************/
		$tab = $_SESSION['current_lab'] -> diagram == true ? 4 : 3;
		foreach ($_SESSION['current_lab'] -> images as $image) {
?>
		<div id='tabs-<?php print $tab ?>' class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<h1><?php print $image -> name ?></h1>
			<div><img border='0' src='<?php print BASE_WWW ?>/ajax_helper.php?action=img_show&img_id=<?php print $image -> id ?>' alt='<?php print $image -> name ?>' usemap='#map<?php print $tab ?>' /></div>
			<div><?php print $image -> info ?></div>
		</div>
		<map name='map<?php print $tab ?>'>
			<?php print preg_replace('/{{IP}}/', $_SERVER['HTTP_HOST'], $image -> map) ?>
		</map>
<?php
			$tab++;
		}
?>
</div>
<?php
		break;
/*************************************************************************
 * Open a config pack                                                    *
 *************************************************************************/
	case 'cfg_open':
?>
<script type='text/javascript'><!--
	$(document).ready(function() {
		$('#tabs').tabs({
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$(anchor.hash).html("Couldn't load this tab. We'll try to fix this as soon as possible.");
				}
			}
		});
<?php
		if(is_admin()) {
?>

		$('a.wipe').click(function() {
			var cfg_name = $(this).closest('a').find('input').val();
			var delete_url = '<?php print BASE_WWW ?>/ajax_helper.php?action=cfg_delete&cfg_name=' + encodeURIComponent(cfg_name);
			$('#dialog').attr('title', 'Confirm config deletion');
			$('#dialog').html('Are you sure you want to delete <strong>' + cfg_name + '</strong> config pack?');
			$('#dialog').dialog({
				modal: true, 
				show: 'fade', 
				hide: 'fade',
				resizable: false,
				buttons: {
					Ok: function() {
						$.get(delete_url);
						setTimeout(function(){
							$(this).dialog('destroy');
							location.reload();
						}, 100);
					},
					Cancel: function() {
						$(this).dialog('destroy');
					}
				}
			});
		});
<?php
		}
?>
	});
--></script>
<div class='block'>
	<div id='tabs' class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<ul class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
<?php
		$cfg_counter = 1;
		foreach ($_SESSION['current_confpack'] -> configs as $config) {
			// Strip all before ' - ', if exists
			if (strstr($config -> name, ' - ')) {
					$cfg_short_name = substr($config -> name, strpos($config -> name, ' - ') + 3);
			} else {
					$cfg_short_name = $config -> name;
			}
?>
			<li><a href='#tabs-<?php print $cfg_counter ?>'><?php print $cfg_short_name ?></a></li>
<?php
				$cfg_counter++;
			}
?>
		</ul>
<?php
/*************************************************************************
 * Tabs                                                                  *
 *************************************************************************/
			$cfg_counter = 1;
 			foreach ($_SESSION['current_confpack'] -> configs as $config) {
?>
		<div id='tabs-<?php print $cfg_counter ?>' class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<a class='wipe' href='#'>
				<img border='0' align='right' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' width='32' height='32' title='Delete "<?php print $config -> name ?>" config' alt='Delete "<?php print $config -> name ?>" config' />
				<input type='hidden' class='cfg_name' value='<?php print $config -> name ?>' />
			</a>
			<h1><?php print $config -> name ?></h1>
			<pre><?php print $config -> config ?></pre>
		</div>
<?php
				$cfg_counter++;
			}
?>
</div>
<?php	
		break;
}
page_footer();
?>
