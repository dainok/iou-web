<?php
include('includes/conf.php');

if (is_admin()) {
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
	} else {
		$action = 'default';
	}

	switch ($action) {
/*************************************************************************
 * Show system tools  (default case)                                     *
 *************************************************************************/
 // TODO add log file tab with tail last lines from php_error.log
		default:
			page_header('Manage');
?>
<script type='text/javascript'><!--
	$(document).ready(
		function() {
			$('#db_wipe').click(
				function() {
					$('#dialog').attr('title', 'Confirm data loss');
					$('#dialog').html('Are you sure you want to delete your database and <strong>loose</strong> all your data?');
					$('#dialog').dialog({
						modal: true, 
						show: 'fade', 
						hide: 'fade',
						resizable: false,
						buttons: {
							Ok: function() {
								$(this).dialog('destroy');
								initDatabase('<?php print BASE_WWW ?>');
							},
							Cancel: function() {
								$(this).dialog('destroy');
							}
						}
					});
				}
			);
			$('#db_optimize').click(
				function() {
					optimizeDatabase('<?php print BASE_WWW ?>');
				}
			);
			$('#license').click(
				function() {
					var url = '<?php print BASE_WWW ?>/ajax_helper.php?action=license_save';
					var license = '<?php print rawurlencode(file_get_contents(BASE_BIN.'/iourc')) ?>';
					$('#dialog').attr('title', 'Manage license');
					$('#dialog').html("<form><textarea type='textarea' class='license_data' name='license_data' rows='6' cols='42'>" + decodeURIComponent(license) + "</textarea></form>");
					$('#dialog').dialog({
						modal: true, 
						resizable: false,
						buttons: {
							'Save': function() {
								$(this).dialog('destroy');
								$.post(url, {
									license_data: $(this).find('.license_data').val()
								});
								$(this).dialog('destroy');
								$('#dialog').attr('title', 'Manage license');
								$('#dialog').html("License saved.");
								$( "#dialog" ).dialog({
									modal: true,
									resizable: false,
									buttons: {
										Ok: function() {
											$(this).dialog('destroy');
											location.reload();
										}
									}
								});
							},
							Cancel: function() {
								$(this).dialog('destroy');
							}
						}
					});
				}
			);
		}
	);
--></script>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Manage <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/manage/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom' id='list'>
			<ul>
				<li><a href='<?php print BASE_WWW ?>/manage.php?action=lab_export'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/lab.png' width='32' height='32' alt='Export labs' title='Export labs' />Export labs</a></li>
				<li><a href='<?php print BASE_WWW ?>/manage.php?action=lab_import'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/lab.png' width='32' height='32' alt='Import labs' title='Import labs' />Import labs</a></li>
				<li><a href='<?php print BASE_WWW ?>/manage.php?action=bin_show'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/binary.png' width='32' height='32' alt='Manage IOSes' title='Manage IOSes' />Manage IOSes</a></li>
				<li><a id='license' href='#'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/license.png' width='32' height='32' alt='Manage license' title='Manage license' />Manage license</a></li>
				<li><a id='db_optimize' href='#'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/database.png' width='32' height='32' alt='Manage license' title='Manage license' />Optimize database</a></li>
				<li><a id='db_wipe' href='#'><img border='0' src='<?php print BASE_WWW ?>/images/buttons/database.png' width='32' height='32' alt='Manage license' title='Manage license' /><font>Wipe database</font></a></li>
			</ul>
		</div>
	</div>
</div>
<?php
			page_footer();
			break;
/*************************************************************************
 * Image add                                                             *
 *************************************************************************/			
		case 'img_add':
			if (isset($_POST['action'])) {			
				// Form completed, updating the DB
				if(isset($_POST['img_name']) && isset($_FILES['img_file']['tmp_name']) &&
					isset($_FILES['img_file']['type']) && $_FILES['img_file']['type'] = 'image/png') {
				
					$img_name = $_POST['img_name'];
					$img_id = $_GET['img_id'];
					$img_info = $_POST['img_info'] ? $_POST['img_info'] : '';

					$hndl = fopen($_FILES['img_file']['tmp_name'], 'r'); 
					$imgdata = ''; 
					$imgdata = fread($hndl, $_FILES['img_file']['size']); 
					img_add($img_name, $img_info, $imgdata, 0);

					header("Cache-Control: no-cache, must-revalidate");     // HTTP/1.1
					header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");       // Date in the past
					header("Location: ".BASE_WWW."/laboratories.php");
					exit();
					// TODO should redirect: http://192.168.211.128/manage.php?action=img_edit&img_id=31
				} else {
					//header('Cache-Control: no-cache, must-revalidate');     // HTTP/1.1
					//header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');       // Date in the past
					//header('HTTP/1.1 403 Forbidden');                                       // Forbidden
				}
			} else {
				// Fill the form
				page_header('Manage');
?>
<script type='text/javascript'><!--
	$.validator.addMethod(
		'regexp',
		function(value, element, regexp) {
			var check = false;
			return this.optional(element) || regexp.test(value);
		},
		'Please check your input and use only allowed characters.'
	);
	$(document).ready(function(){
		$('form').validate({
			rules: {
				img_name: {
					required: true,
				},
				img_file: {
					required: true,
				},
			}
		});
	});
--></script>
<form action='<?php print BASE_WWW ?>/manage.php?action=img_add' method='post' enctype='multipart/form-data'>
	<div class='block'>
		<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
			<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
				<h1 class='bar'>Add an image <a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
			</div>
			<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
				<p>
					<label for='img_name'>Name*:</label>
					<input type='text' id='img_name' name='img_name' size='20' />
					<a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label>File:</label>
					<input type="file" name="img_file"/>
					<a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='img_info'>Additional Info:<br/>
						<a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
					<textarea type='textarea' id='img_info' name='img_info' rows='40' cols='116' ></textarea>
				</p>
				<input type='submit' name='action' value='Save' />
				<input type='reset' />
			</div>
			<div class='clear'></div>
		</div>
	</div>
</form>
<?php
				page_footer();
			}
			break;
/*************************************************************************
 * Add a lab                                                             *
 *************************************************************************/
		case 'lab_add':
			if (isset($_POST['action'])) {
				// Form completed, updating the DB TODO
				if(isset($_POST['lab_time']) && is_numeric($_POST['lab_time']) &&
					isset($_POST['lab_points']) && is_numeric($_POST['lab_points']) &&
					isset($_POST['lab_name']) && preg_match('/^[A-Za-z0-9_+\\s]+$/', $_POST['lab_name']) &&
					isset($_POST['lab_description']) && isset($_POST['lab_netmap'])) {
					
					$_SESSION['current_lab'] = new Lab(false, getLastLabId() + 1, $_POST['lab_name'], $_POST['lab_description'], $_SESSION['current_folder'] -> id);
					$_SESSION['current_lab'] -> netmap = trim($_POST['lab_netmap']);
					$_SESSION['current_lab'] -> diagram = isset($_POST['lab_diagram']) ? true : false;
					$_SESSION['current_lab'] -> time = $_POST['lab_time'];
					$_SESSION['current_lab'] -> points = $_POST['lab_points'];
					$_SESSION['current_lab'] -> save();
					$_SESSION['current_lab'] -> load();
					header("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
					header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");	// Date in the past
					header("Location: ".BASE_WWW."/manage.php?action=lab_edit#devices");
					exit();
				} else {
					header('Cache-Control: no-cache, must-revalidate');	// HTTP/1.1
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');	// Date in the past
					header('HTTP/1.1 403 Forbidden');					// Forbidden
					exit();
				}
				break;
			} else {
				// Fill the form
				page_header('Manage');
?>
<script type='text/javascript'><!--
	$.validator.addMethod(
		'regexp',
		function(value, element, regexp) {
			var check = false;
			return this.optional(element) || regexp.test(value);
		},
		'Please check your input and use only allowed characters.'
	);
	$(document).ready(function(){
		$('form').validate({
			rules: {
				lab_name: {
					required: true,
					regexp: /^[A-Za-z0-9_ +]+$/
				},
				lab_description: {
					required: true,
				},
				lab_netmap: {
					required: true
				},
				lab_time: {
					required: true,
					regexp: /^[0-9]+$/
				},
				lab_points: {
					required: true,
					regexp: /^[0-9]+$/
				}
			}
		});
	});
--></script>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Add new laboratory <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<form action='<?php print BASE_WWW ?>/manage.php?action=lab_add' method='post' enctype='multipart/form-data'>
				<p>
					<label for='lab_name'>Name*:<br/><small>(A-Za-z0-9_ +)</small></label>
					<input type='text' id='lab_name' name='lab_name' size='20' />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='lab_description'>Description*:</label>
					<input type='text' id='lab_description' name='lab_description' size='20' />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='lab_time'>Time (minutes)*:<br/><small>(0-9)</small></label>
					<input type='text' id='lab_time' name='lab_time' size='20' value='0' />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<!-- <p>
					<label for='lab_points'>Points awarded*:<br/><small>(0-9)</small></label>
					<input type='hidden' id='lab_points' name='lab_points' size='20' value='0' />
				</p> -->
				<input type='hidden' id='lab_points' name='lab_points' size='20' value='0' />
				<p>
					<label for='lab_diagram'>Display diagram?</label>
					<input type='checkbox' id='lab_diagram' name='lab_diagram' checked />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='lab_info'>Additional Info:<br/>
						<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
					<textarea type='textarea' id='lab_info' name='lab_info' rows='40' cols='116'></textarea>
				</p>
				<p>
					<label for='lab_netmap'>NETMAP*:<br/>
						<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/the-netmap-file/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
					<textarea type='textarea' id='lab_netmap' name='lab_netmap' rows='35' cols='65'></textarea>
				</p>
				<input type='submit' name='action' value='Add' />
				<input type='reset' />
			</form>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
				page_footer();
			}
			break;
/*************************************************************************
 * Edit an image                                                         *
 *************************************************************************/
 		case 'img_edit':
			if (isset($_POST['action'])) {
				// Form completed, updating the DB
				if(isset($_GET['img_id']) && is_numeric($_GET['img_id']) && isset($_POST['img_name'])) {

					$img_name = $_POST['img_name'];
					$img_id = $_GET['img_id'];
					$img_info = $_POST['img_info'] ? $_POST['img_info'] : '';
					$img_map = $_POST['img_map'] ? $_POST['img_map'] : '';
					
					img_update($img_id, $img_name, $img_info, $img_map);

					header("Cache-Control: no-cache, must-revalidate");     // HTTP/1.1
					header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");       // Date in the past
					header("Location: ".BASE_WWW."/laboratories.php");
					exit();
				} else {
					header('Cache-Control: no-cache, must-revalidate');     // HTTP/1.1
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');       // Date in the past
					header('HTTP/1.1 403 Forbidden');                       // Forbidden
					exit();
				}
			} else {
				// Fill the form
				if(isset($_GET['img_id']) && is_numeric($_GET['img_id'])) {
					$image = new Image(true, $_GET['img_id'], '', '');
					page_header('Manage');
?>
<script type='text/javascript'><!--
	$.validator.addMethod(
		'regexp',
		function(value, element, regexp) {
			var check = false;
			return this.optional(element) || regexp.test(value);
		},
		'Please check your input and use only allowed characters.'
	);
	$(document).ready(function(){
		$('form').validate({
			rules: {
				img_name: {
					required: true,
				},
			}
		});
		$('img').click(function(e){
			var offset = $(this).parent().offset();
			var position = $(this).position();
			var y = (e.pageY - offset.top).toFixed(0);
			var x = (e.pageX - offset.left).toFixed(0);
			$('#img_map').append("&lt;area shape='circle' coords='" + x + "," + y + ",30' href='telnet://{{IP}}:2000'&gt;\n");
		});
	});
--></script>
<form action='<?php print BASE_WWW ?>/manage.php?action=img_edit&img_id=<?php print $image -> id ?>' method='post' enctype='multipart/form-data'>
	<div class='block'>
		<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
			<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
				<h1 class='bar'>Edit an image <a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
			</div>
			<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
				<p>
					<label for='img_name'>Name*:</label>
					<input type='text' id='img_name' name='img_name' size='20' value='<?php print $image -> name ?>'/>
					<a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='img_info'>Additional Info:<br/>
						<a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
					<textarea type='textarea' id='img_info' name='img_info' rows='40' cols='116' ><?php print $image -> info ?></textarea>
				</p>
				<p>
					<label for='img_info'>Map:
						<a href='http://www.routereflector.com/en/cisco/cisco-iou-web-interface/images/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
					<textarea type='textarea' id='img_map' name='img_map' rows='40' cols='116' ><?php print $image -> map ?></textarea>
				</p>
				<input type='submit' name='action' value='Save' />
				<input type='reset' />
				<div><img border='0' src='<?php print BASE_WWW ?>/ajax_helper.php?action=img_show&img_id=<?php print $image -> id ?>' alt='<?php print $image -> id ?>' /></div>
			</div>
			<div class='clear'></div>
		</div>
	</div>
</form>
<?php
					page_footer();
				} else {
					header('Cache-Control: no-cache, must-revalidate');	// HTTP/1.1
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');	// Date in the past
					header('HTTP/1.1 403 Forbidden');					// Forbidden
					exit();
				}
			}
			break;
/*************************************************************************
 * Edit a lab                                                            *
 *************************************************************************/
		case 'lab_edit':
			if (isset($_POST['action'])) {
				// Form completed, validating the input
				if(isset($_GET['lab_id']) && is_numeric($_GET['lab_id']) &&
					isset($_POST['lab_time']) && is_numeric($_POST['lab_time']) &&
					isset($_POST['lab_points']) && is_numeric($_POST['lab_points']) &&
					isset($_POST['lab_name']) && preg_match('/^[A-Za-z0-9_+\\s]+$/', $_POST['lab_name']) &&
					isset($_POST['lab_description']) && isset($_POST['lab_netmap']) && $_SESSION['current_lab'] -> id == $_GET['lab_id']) {
					
					// saving the lab
					$_SESSION['current_lab'] -> name = $_POST['lab_name'];
					$_SESSION['current_lab'] -> description = $_POST['lab_description'];
					$_SESSION['current_lab'] -> diagram = isset($_POST['lab_diagram']) ? true : false;
					$_SESSION['current_lab'] -> netmap = trim($_POST['lab_netmap']);
					$_SESSION['current_lab'] -> time = $_POST['lab_time'];
					$_SESSION['current_lab'] -> points = $_POST['lab_points'];
					$_SESSION['current_lab'] -> info = isset($_POST['lab_info']) ? $_POST['lab_info'] : '';
					
					$finisched = false; // Used during lab creation, see later

					// Now update each device
					foreach ($_SESSION['current_lab'] -> devices as $device) {
						if ($device -> id >= BASE_HUB) {
							// Hubs need top and left only
							$_SESSION['current_lab'] -> devices[$device -> id] -> top = $_POST[$device -> id.':top'];
							$_SESSION['current_lab'] -> devices[$device -> id] -> left = $_POST[$device -> id.':left'];	
							break;
						}
						$finisched = true;
						
						// Config Pack selected?
						if ($_POST['conf_pack'] != '') {
							// Config Pack selected, updating cfg_id and config
							$found = false;
							foreach ($GLOBAL['all_configs'] as $config) {
	
							if ($config -> name == $_POST['conf_pack']) {
									$found = true;
									$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_id = $config -> id;
									$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_name = $config -> name;
									$_SESSION['current_lab'] -> devices[$device -> id] -> config = $config -> config;
							}
							if ($config -> name == $_POST['conf_pack'].' - '.$_POST[$device -> id.':name']) {
									$found = true;
									$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_id = $config -> id;
									$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_name = $config -> name;
									$_SESSION['current_lab'] -> devices[$device -> id] -> config = $config -> config;
								}
							}
							// If config pack is missing for this devices, reset fields
							if (!$found) {
								$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_id = '0';
								$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_name = '';
								$_SESSION['current_lab'] -> devices[$device -> id] -> config = '';
							}
						} else {
							// No Config Pack selected
							if (isset($_POST[$device -> id.':cfg_id'])) {
								$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_id = $_POST[$device -> id.':cfg_id'];
								// Updating cfg_name and config
								foreach ($GLOBAL['all_configs'] as $config) {
									if ($config -> id == $_SESSION['current_lab'] -> devices[$device -> id] -> cfg_id) {
										$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_name = $config -> name;
										$_SESSION['current_lab'] -> devices[$device -> id] -> config = $config -> config;
									}
								}
							} else {
								$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_id = '0';
								$_SESSION['current_lab'] -> devices[$device -> id] -> cfg_name = '';
								$_SESSION['current_lab'] -> devices[$device -> id] -> config = '';
							}
						}
						
						// Check if l2keepalive is set
						if (isset($_POST[$device -> id.':l2keepalive'])) {
							$_SESSION['current_lab'] -> devices[$device -> id] -> l2keepalive = true;
						} else {
							$_SESSION['current_lab'] -> devices[$device -> id] -> l2keepalive = false;
						}
						// Check if watchdog is set
						if (isset($_POST[$device -> id.':watchdog'])) {
							$_SESSION['current_lab'] -> devices[$device -> id] -> watchdog = true;
						} else {
							$_SESSION['current_lab'] -> devices[$device -> id] -> watchdog = false;
						}
						
						$_SESSION['current_lab'] -> devices[$device -> id] -> name = $_POST[$device -> id.':name'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> bin_name = $_POST[$device -> id.':bin_name'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> ram = $_POST[$device -> id.':ram'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> nvram = $_POST[$device -> id.':nvram'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> ethernet = $_POST[$device -> id.':ethernet'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> serial = $_POST[$device -> id.':serial'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> picture = $_POST[$device -> id.':picture'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> delay = $_POST[$device -> id.':delay'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> top = $_POST[$device -> id.':top'];
						$_SESSION['current_lab'] -> devices[$device -> id] -> left = $_POST[$device -> id.':left'];	
						
						// Updating bin_filename
						foreach ($GLOBAL['all_bins'] as $bin) {
							if ($bin -> name == $_SESSION['current_lab'] -> devices[$device -> id] -> bin_name) {
								$_SESSION['current_lab'] -> devices[$device -> id] -> bin_filename = $bin -> filename;
							}
						}
					}

					// Finally update linked images: first remove all images, then select proper images only
					unset($_SESSION['current_lab'] -> images);
					$_SESSION['current_lab'] -> images = array();
					if (isset($_POST["images"])) {
						foreach ($_POST["images"] as $img_id) {
							$_SESSION['current_lab'] -> images[$img_id] = new Image(true, $img_id, '', '');
						}
					}

					// Save lab and devices
					$_SESSION['current_lab'] -> save();
					
					// Invalidate the global cache
					unset($GLOBAL['all_labs']);
					
					// Redirect to?
					if (!$finisched) {
						// Lab just created, next step is create devices
						header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
						header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
						//header("Location: ".BASE_WWW."/manage.php?action=lab_edit&lab_id=".$lab_id."#devices");
						header("Location: ".BASE_WWW."/manage.php?action=lab_edit#devices");
						exit();
					} else {
						// Lab modified, next step is show lab or edit devices position
						header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
						header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
						//header("Location: ".BASE_WWW."/laboratories.php?action=open&lab_id=".$lab_id);
						header("Location: ".BASE_WWW."/laboratories.php?action=open");
						exit();
					}
				} else {
					// Error TODO
					$_SESSION['current_error'] = 'Input validation error';
					header("Location: ".BASE_WWW."/error.php");
					exit();
				}
				break;
			} else {
				// Fill the form
				if(isset($_SESSION['current_lab'])) {
					page_header('Manage');
?>
<script type='text/javascript'><!--
	$.validator.addMethod(
		'regexp',
		function(value, element, regexp) {
			var check = false;
			return this.optional(element) || regexp.test(value);
		},
		'Please check your input and use only allowed characters.'
	);
	$(document).ready(function(){
		$('form').validate({
			rules: {
				lab_name: {
					required: true,
					regexp: /^[A-Za-z0-9_: +]+$/
				},
				lab_description: {
					required: true,
				},
				lab_netmap: {
					required: true
				},
				lab_time: {
					required: true,
					regexp: /^[0-9]+$/
				},
				lab_points: {
					required: true,
					regexp: /^[0-9]+$/
				}
			}
		});
	});
--></script>
<form action='<?php print BASE_WWW ?>/manage.php?action=lab_edit&lab_id=<?php print $_SESSION['current_lab'] -> id ?>' method='post' enctype='multipart/form-data'>
	<div class='block'>
		<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
			<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
				<h1 class='bar'>Edit laboratory</h1>
			</div>
			<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
				<p>
					<label for='lab_name'>Name*:<br/><small>(A-Za-z0-9_ +)</small></label>
					<input type='text' id='lab_name' name='lab_name' value='<?php print $_SESSION['current_lab'] -> name ?>' size='20' />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='lab_description'>Description*:</label>
					<input type='text' id='lab_description' name='lab_description' value='<?php print $_SESSION['current_lab'] -> description ?>' size='20' />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='lab_time'>Time (minutes)*:<br/><small>(0-9)</small></label>
					<input type='text' id='lab_time' name='lab_time' size='20' value='<?php print is_numeric($_SESSION['current_lab'] -> time) ? $_SESSION['current_lab'] -> time : 0 ?>' />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<!-- <p>
					<label for='lab_points'>Points awarded*:<br/><small>(0-9)</small></label>
					<input type='text' id='lab_points' name='lab_points' size='20' value='<?php print is_numeric($_SESSION['current_lab'] -> points) ? $_SESSION['current_lab'] -> points : 0 ?>' />
				</p> -->
				<input type='hidden' id='lab_points' name='lab_points' size='20' value='0' />
				<p>
					<label for='lab_diagram'>Display diagram?</label>
					<input type='checkbox' id='lab_diagram' name='lab_diagram' <?php if ($_SESSION['current_lab'] -> diagram == true) print "checked" ?> />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='lab_info'>Additional Info:</br>
						<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
					<textarea type='textarea' id='lab_info' name='lab_info' rows='40' cols='116'><?php print $_SESSION['current_lab'] -> info ?></textarea>
				</p>
				<p>
					<label for='lab_netmap'>NETMAP*:
						<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/the-netmap-file/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
						<textarea type='textarea' id='lab_netmap' name='lab_netmap' rows='35' cols='65'><?php print $_SESSION['current_lab'] -> netmap ?></textarea></td>
				</p>
				<input type='submit' name='action' value='Save' />
				<input type='reset' />
			</div>
			<div class='clear'></div>
		</div>
	</div>
	<div class='block'>
		<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
			<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
				<h1 class='bar'><a name="devices">Devices</a> <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
			</div>
			<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
				<p>Apply an Initial Config Pack to all device: <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/initial-configurations/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					<select name="conf_pack">
							<option value="">&lt;empty&gt;</OPTION>
<?php
					foreach ($GLOBAL['all_configpacks'] as $configpack) {
?>
						<option value="<?php print $configpack -> name ?>"><?php print $configpack -> name ?></option>
<?php
					}
?>
					</select>
				</p>
				<table>
					<tr>
						<td>ID</td>
						<td>Name <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>IOS <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>RAM <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>NVRAM <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>Eth <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>Ser <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>L2 Keppalive <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>Watchdog <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>Picture <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>Boot delay <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#dev_add' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
						<td>Initial Config <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/initial-configurations/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></td>
					</tr>
<?php
					// Getting all info about devices
					foreach ($_SESSION['current_lab'] -> devices as $device) {
						if ($device -> id >= BASE_HUB) {
							// Hubs need top and left only
							$dev_id = $device -> id;
							$dev_top = $device -> top;
							$dev_left = $device -> left;
?>
					<input type="hidden" name="<?php print $dev_id ?>:top" value="<?php print $dev_top ?>">
					<input type="hidden" name="<?php print $dev_id ?>:left" value="<?php print $dev_left ?>">
<?php
							break;
						}
						$dev_id = $device -> id;
						$dev_name = $device -> name;
						$dev_top = $device -> top;
						$dev_left = $device -> left;
						if ($device -> isCloud()) {
							// a Cloud need ethernet and picture only
							$dev_bin = '';
							$dev_ram = '';
							$dev_nvram = '';
							$dev_ethernet = $device -> ethernet;
							$dev_serial = '';
							$dev_l2keepalive = '';
							$dev_watchdog = '';
							$dev_picture = 'cloud';
							$dev_delay = $device -> delay;
							$cfg_id = '';
						} else {
							// a classic device need all parameters
							$dev_bin = $device -> bin_name;
							$dev_ram = $device -> ram;
							$dev_nvram = $device -> nvram;
							$dev_ethernet = $device -> ethernet;
							$dev_serial = $device -> serial;
							$dev_l2keepalive = $device -> l2keepalive;
							$dev_l2keepalive = isset($device -> l2keepalive) ? $device -> l2keepalive : false;
							$dev_watchdog = isset($device -> watchdog) ? $device -> watchdog : true;
							$dev_picture = $device -> picture;
							$dev_delay = $device -> delay;
							$cfg_id = $device -> cfg_id;
						}
?>
					<tr>
						<td>
							<?php print $dev_id; ?>
							<input type="hidden" name="<?php print $dev_id ?>:top" value="<?php print $dev_top ?>">
							<input type="hidden" name="<?php print $dev_id ?>:left" value="<?php print $dev_left ?>">
						</td>
						<td><input size="10" type="text" name="<?php print $dev_id; ?>:name" value="<?php print $dev_name ?>"></td>
						<td>
							<select name="<?php print $dev_id; ?>:bin_name">
								<option value=''>&lt;empty&gt;</option>
<?php
						foreach ($GLOBAL['all_bins'] as $bin) {
?>
								<option value='<?php print $bin -> name ?>' <?php if($dev_bin == $bin -> name) print 'selected' ?>><?php print $bin -> name ?></option>
<?php
						}
?>						
							</select>
						</td>
						<td><input size="3" type="text" name="<?php print $dev_id; ?>:ram" value="<?php print $dev_ram ?>"></td>
						<td><input size="3" type="text" name="<?php print $dev_id; ?>:nvram" value="<?php print $dev_nvram ?>"></td>
						<td><input size="2" type="text" name="<?php print $dev_id; ?>:ethernet" value="<?php print $dev_ethernet ?>"></td>
						<td><input size="2" type="text" name="<?php print $dev_id; ?>:serial" value="<?php print $dev_serial ?>"></td>
						<td align='center'><input size="2" type="checkbox" name="<?php print $dev_id; ?>:l2keepalive" <?php if ($dev_l2keepalive == true) print "checked" ?> /></td>
						<td align='center'><input size="2" type="checkbox" name="<?php print $dev_id; ?>:watchdog" <?php if (!isset($dev_watchdog) || $dev_watchdog == true) print "checked" ?> /></td>
						<td>
							<select name="<?php print $dev_id; ?>:picture">
								<option value='cloud' <?php if($dev_picture == 'cloud') print 'selected' ?>>Cloud</option>
								<option value='desktop' <?php if($dev_picture == 'desktop') print 'selected' ?>>Desktop</option>
								<option value='framerelay' <?php if($dev_picture == 'framerelay') print 'selected' ?>>Frame Relay Switch</option>
								<option value='l3switch' <?php if($dev_picture == 'l3switch') print 'selected' ?>>L3 Switch</option>
								<option value='mpls' <?php if($dev_picture == 'mpls') print 'selected' ?>>MPLS Router</option>
								<option value='router' <?php if($dev_picture == 'router' || $dev_picture == '') print 'selected' ?>>Router</option>
								<option value='server' <?php if($dev_picture == 'server') print 'selected' ?>>Server</option>
								<option value='switch' <?php if($dev_picture == 'switch') print 'selected' ?>>Switch</option>
							</select>
						</td>
						<td align='center'><input size="3" maxlength="3" type="text" name="<?php print $dev_id; ?>:delay" value="<?php print $dev_delay ?>"></td>
						<td>
							<select name="<?php print $dev_id; ?>:cfg_id" width="150" style="width: 150px">
								<option value=''>&lt;empty&gt;</option>
<?php
						foreach ($GLOBAL['all_configs'] as $config) {
							// Filtering configs by name or generic configs (without ' - ')
							if (preg_match('/ - '.$dev_name.'$/', $config -> name) || !preg_match('/ - /', $config -> name)) {
?>
								<option value="<?php print $config -> id ?>"<?php if($config -> id == $cfg_id) print "selected"; ?>><?php print $config -> name ?></option>
<?php
							}
						}
?>						
							</select>
						</td>
					</tr>
<?php
			}
?>
				</table>
				<input type='submit' name='action' value='Save' />
				<input type='reset' />
			</div>
			<div class='clear'></div>
		</div>
	</div>
	<div class='block'>
		<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
			<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
				<h1 class='bar'><a name="devices">Images</a> <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/laboratories/#lab_images' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
			</div>
			<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
				<p>Select image you want to link:</p>
<?php
			foreach ($GLOBAL['all_images'] as $image) {
?>
				<p>
					<input size="2" type="checkbox" <?php if (isset($_SESSION['current_lab'] -> images[$image -> id])) print "checked" ?> name='images[<?php print $image -> id; ?>]' value="<?php print $image -> id; ?>" />
					<?php print $image -> name; ?>
				</p>
<?php
			}
?>
				<input type='submit' name='action' value='Save' />
				<input type='reset' />
			</div>
		</div>
	</div>
</form>
<?php
					page_footer();
				} else {
					// Error
					$_SESSION['current_error'] = 'Non existent lab.';
					header("Location: ".BASE_WWW."/error.php");
					exit();
				}
			}
			break;
/*************************************************************************
 * Add a config                                                             *
 *************************************************************************/
		case 'cfg_add':
			if (isset($_POST['action'])) {
				// Form completed, updating the DB
				if(isset($_POST['cfg_name']) && preg_match('/^[A-Za-z0-9_+\\s-]+$/', $_POST['cfg_name']) &&
					(isset($_POST['cfg_config']) || $_FILES['cfg_file']['error'] == 0)) {
					
					$cfg_name = $_POST['cfg_name'];
					if ($_FILES['cfg_file']['error'] == 0) {
						// load from file
						$hndl = fopen($_FILES['cfg_file']['tmp_name'], 'r');
						$cfg_config = '';
						$cfg_config = fread($hndl, $_FILES['cfg_file']['size']);
					} else {
						// load from web
						$cfg_config = $_POST['cfg_config'];
					}
					cfg_add($cfg_name, $cfg_config, $_SESSION['current_folder'] -> id);
					
					header("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
					header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");	// Date in the past
					header("Location: ".BASE_WWW."/laboratories.php");
					exit();
				} else {
					header('Cache-Control: no-cache, must-revalidate');	// HTTP/1.1
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');	// Date in the past
					header('HTTP/1.1 403 Forbidden');					// Forbidden
					exit();
				}
				break;
			} else {
				// Fill the form
				page_header('Manage');
?>
<script type='text/javascript'><!--
	$.validator.addMethod(
		'regexp',
		function(value, element, regexp) {
			var check = false;
			return this.optional(element) || regexp.test(value);
		},
		'Please check your input and use only allowed characters.'
	);
	$(document).ready(function(){
		$('form').validate({
			rules: {
				cfg_name: {
					required: true,
					regexp: /^[A-Za-z0-9_ +-]+$/
				}
			}
		});
	});
--></script>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Add new config <a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/initial-configurations/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a></h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<form action='<?php print BASE_WWW ?>/manage.php?action=cfg_add' method='post' enctype='multipart/form-data'>
				<p>
					<label for='cfg_name'>Name*:<br/><small>(A-Za-z0-9_ +-)</small></label>
					<input type='text' id='cfg_name' name='cfg_name' size='20' />
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/initial-configurations/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label>File:</label>
					<input type="file" name="cfg_file"/>
					<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/initial-configurations/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
				</p>
				<p>
					<label for='lab_description'>
						Config*:<br/>
						<a href='http://www.routereflector.com/cisco/cisco-iou-web-interface/initial-configurations/' target='_blank'><img class="help" border='0' src='<?php print BASE_WWW ?>/images/buttons/help_small.png' width='16' height='16' title='Online Help' alt='Online Help' /></a>
					</label>
					<textarea type="textarea" id="cfg_config" name="cfg_config" rows="20" cols="80"></textarea>
				</p>
				<input type='submit' name='action' value='Add' />
				<input type='reset' />
			</form>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
				page_footer();
			}
			break;
/*************************************************************************
 * Edit a Config                                                         *
 *************************************************************************/
		case 'cfg_edit':
			if(isset($_GET['cfg_pack']) && preg_match('/^[A-Za-z0-9_:+\\s]+$/', rawurldecode($_GET['cfg_pack']))) {
				//Check if save button was pressed
				// USARE AJAX $.post
				if(isset($_POST['action']) && isset($_POST['cfg_name']) && preg_match('/^[A-Za-z0-9_:+\\s-]+$/', rawurldecode($_POST['cfg_name'])) && isset($_POST['cfg_config']) && isset($_POST['cfg_id'])) {
					$cfg_id = $_POST['cfg_id'];
					$cfg_name = $_POST['cfg_name'];
					$cfg_config = $_POST['cfg_config'];
					cfg_update($cfg_id, $cfg_name, $cfg_config);
					unset($_SESSION['current_confpack']);
					unset($GLOBAL['all_configpacks']);
					header("Location: ".BASE_WWW."/manage.php?action=cfg_edit&cfg_pack=".$_GET['cfg_pack']);
					exit();
				}
				$cfg_pack = rawurldecode($_GET['cfg_pack']);				
				page_header('Manage');
?>
<script type='text/javascript'><!--
	$(function() {
		$('#tabs').tabs({
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$(anchor.hash).html("Couldn't load this tab. We'll try to fix this as soon as possible.");
				}
			}
		});
		$('.cfg_form').submit(
			function() {
				$.ajax({
					type: 'POST',
					url: '<?php print BASE_WWW ?>/ajax_helper.php?action=cfg_edit',
					data: $(this).serialize(),
					dataType: "xml",
					//success: function(xml) {
						//alert($(xml).find('message').text());
					//},
					//error: function(xml) {
						//alert($(xml).find('message').text());
					//},
				});

			}
		);
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
			<form class='cfg_form' action='#tabs-<?php print $cfg_counter ?>' method='post' enctype='multipart/form-data'>
				<table>
					<tr>
						<td>Name: <input type='text' class='cfg_name' name='cfg_name' value='<?php print $config -> name ?>' size='20' /></td>
					</tr>
					<tr>
						<td><textarea type='textarea' class='cfg_config' name='cfg_config' rows='40' cols='116'><?php print $config -> config ?></textarea></td>
					</tr>
				</table>
				<input type='hidden' class='cfg_id' name='cfg_id' value='<?php print $config -> id ?>'>
				<input type='submit' name='action' value='Save' />
				<input type='reset' />
			</form>
		</div>
<?php
					$cfg_counter++;
				}
?>
	</div>
</div>
<?php
				page_footer();
			}
			break;
/*************************************************************************
 * IOU binaries                                                          *
 *************************************************************************/
	case 'bin_show':
		// List all configured IOSes
		$up_id = uniqid(); 
		page_header('Manage');
		if (isset($_POST['action'])) {
			file_upload($_FILES['ios_file']['tmp_name'], $_POST['ios_name'], $_POST['ios_alias'], $_FILES['ios_file']['error']);
			unset($GLOBAL['all_bins']);
			header("Location: ".BASE_WWW."/manage.php?action=bin_show");
			exit();
		}
?>
<script type='text/javascript'><!--
	$(document).ready(function() {
		$('.file a.wipe').click(function() {
			var file_name = $(this).closest('li').find('input').attr('value');
			$('#dialog').attr('title', 'Confirm file deletion');
			$('#dialog').html('Are you sure you want to delete <strong>' + file_name + '</strong> file?');
			$('#dialog').dialog({
				modal: true, 
				show: 'fade', 
				hide: 'fade',
				resizable: false,
				buttons: {
					Ok: function() {
						$('input[value="' + file_name + '"]').closest('li').fadeOut(1000);
						$(this).dialog('destroy');
						deleteFile('<?php print BASE_WWW ?>', file_name);
					},
					Cancel: function() {
						$(this).dialog('destroy');
					}
				}
			});
		});
	});
--></script>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Upload IOS</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom' id='list'>
			<form action="<?php print BASE_WWW ?>/manage.php?action=bin_show" method="post" enctype="multipart/form-data" name="upload_form" id="upload_form">
				<p>
					<label>Filename*:<br/><small>(A-Za-z0-9_.-)</small></label>
					<input class='ios_name' type='text' name='ios_name' />
				</p>
				<p>
					<label>Alias*:<br/><small>(A-Za-z0-9_.-)</small></label>
					<input class='ios_alias' type='text' name='ios_alias' />
				</p>
				<p>
					<label>Pick a file*:</label>
					<input class='ios_file' type='file' name='ios_file'/>
				</p>
				<input name="action" type="submit" id="submit" value="Upload" />
			</form>
		</div>
	</div>
</div>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Manage IOSes</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom' id='list'>
			<ul>
<?php	
			foreach ($GLOBAL['all_bins'] as $bin) {
?>
				<li class='file'>
					<img border='0' src='<?php print BASE_WWW ?>/images/buttons/binary.png' width='32' height='32' alt='IOS file' title='IOS file' /><strong><?php print $bin -> name ?>:</strong> <?php print BASE_BIN.'/'.$bin -> filename ?>
<?php
				if (!file_exists(BASE_BIN.'/'.$bin -> filename)) {
?>
					<img border='0' src='<?php print BASE_WWW ?>/images/buttons/warning_small.png' width='16' height='16' alt='Warning' title='Warning: file not found, please delete this file' />
<?php
				}
?>
					<a class='wipe' href='#'><img border='0' class='button2' src='<?php print BASE_WWW ?>/images/buttons/wipe.png' width='32' height='32' alt='Delete this file' title='Delete this file' /></a>
					<input type='hidden' class='file_name' value='<?php print $bin -> filename ?>' />
				</li>
<?php
			}
?>
			</ul>
		</div>
	</div>
</div>
<?php
		page_footer();
		break;
/*************************************************************************
 * Labs: export                                                          *
 *************************************************************************/
	case 'lab_export':
		page_header('Manage');
		
		if (isset($_POST['action'])) {
?>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Export</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
<?php
			$file_export = 'iou-web-export-'.date('YmdHis').'.gz';
			$path_export = BASE_DIR.'/data/Export/';
			if (isset($_POST['labs'])) {
				$labs = $_POST['labs'];
			} else {
				$labs = false;
			}
			if (isset($_POST['configs'])) {
				$configs = $_POST['configs'];
			} else {
				$configs = false;
			}
			export($labs, $configs, $path_export.$file_export);
?>
			<p>Your export is ready, proceed with <a href="<?php print BASE_WWW ?>/downloads/Export/<?php print $file_export ?>">download</a>.</p>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
		} else {
?>
<SCRIPT type="text/javascript">
$(document).ready(function()
{
	$("#labs_all").click(function()				
	{
		var checked_status = this.checked;
		$('.labs').each(function()
		{
			this.checked = checked_status;
		});
	});
	$("#configs_all").click(function()				
	{
		var checked_status = this.checked;
		$('.configs').each(function()
		{
			this.checked = checked_status;
		});
	});					
});
</SCRIPT>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Export</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<FORM id="export_form" action="<?php print BASE_WWW ?>/manage.php?action=lab_export" method="post">
				<h2>Select labs you want to export:</h2>
				<TABLE id='templatemo_table'>
					<TR>
						<th colspan='2'>Name</th>
						<th>Description</th>
					</TR>
					<TR>
						<TD><INPUT id='labs_all' type='checkbox'></TD>
						<TD colspan='2'><STRONG>Select all labs</STRONG></TD>
					</TR>
<?php
			foreach ($GLOBAL['all_labs'] as $lab) {
?>
					<TR>
						<TD><INPUT class='labs' type='checkbox' name='labs[<?php print $lab -> id ?>]' value='<?php print $lab -> id ?>'></TD>
						<TD><?php print $lab -> name ?></TD>
						<TD><?php print $lab -> description ?></TD>
					</TR>
<?php
			}
?>
				</TABLE>
				<H2>Select initial config packs you want to export:</H2>
				<TABLE id='templatemo_table'>
					<tr>
						<th colspan='2'>Config Pack</th>
					</tr>
					<TR>
						<TD><INPUT id='configs_all' type='checkbox'></TD>
						<TD><STRONG>Select all initial config packs</STRONG></TD>
					</TR>
<?php
			foreach ($GLOBAL['all_configpacks'] as $configpack) {
?>
					<TR>
						<TD><INPUT class='configs' type='checkbox' name='configs[<?php print $configpack -> name ?>]' value='<?php print $configpack -> name ?>'></TD>
						<TD><?php print $configpack -> name ?></TD>
					</TR>
<?php
			}
?>
				</TABLE>
				<INPUT type="submit" name="action" value="Export">
				<INPUT type="reset">
			</FORM>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
		}
		page_footer();
		break;
/*************************************************************************
 * Labs: import                                                          *
 *************************************************************************/
	case 'lab_import':
		ini_set('max_execution_time', '180');
		ini_set('memory_limit', '64M');
		page_header('Manage');
		if (isset($_POST['action']) && $_POST['action'] == 'Import Labs') {
			// Get last id on labs and configs
			$lab_offset = getLastLabId();
			$cfg_offset = getLastConfigId();
			$img_offset = getLastImageId();

			try {
				global $db;
				$file_tmp = $_POST["file"];
				$query = "ATTACH '".$file_tmp."' AS import_db;";
				$db -> query($query);
				
				// Importing Labs
				if (isset($_POST['labs'])) {
					$db -> beginTransaction();
					foreach ($_POST['labs'] as $lab) {
						$query = "INSERT OR REPLACE INTO main.labs (lab_id, lab_name, lab_description, lab_info, lab_netmap, folder_id, lab_diagram, lab_time, lab_points) SELECT lab_id + ".$lab_offset.", lab_name, lab_description, lab_info, lab_netmap, '".$_POST['labs_folder'][$lab]."', lab_diagram, lab_time, lab_points FROM import_db.labs WHERE lab_id='".$lab."';";;
						$db -> query($query);
						$query = "INSERT OR REPLACE INTO main.devices (dev_id, lab_id, dev_name, bin_name, dev_ram, dev_nvram, dev_ethernet, dev_serial, dev_picture, cfg_id, dev_top, dev_left, dev_l2keepalive, dev_watchdog) SELECT dev_id, lab_id + '".$lab_offset."', dev_name, bin_name, dev_ram, dev_nvram, dev_ethernet, dev_serial, dev_picture, cfg_id + '".$cfg_offset."', dev_top, dev_left, dev_l2keepalive, dev_watchdog FROM import_db.devices WHERE lab_id='".$lab."';";;
						$db -> query($query);
						$query = "INSERT OR REPLACE INTO main.rel_img_lab (img_id, lab_id) SELECT img_id + '".$img_offset."', lab_id + '".$lab_offset."'FROM import_db.rel_img_lab WHERE import_db.rel_img_lab.lab_id=".implode(" OR import_db.rel_img_lab.lab_id=", $_POST['labs']).";";
						$db -> query($query);
						$query = "INSERT OR REPLACE INTO main.images (img_id, img_name, img_info, img_content, folder_id, img_map) SELECT DISTINCT img_id + '".$img_offset."', img_name, img_info, img_content, ".$_POST['imgs_folder'].", img_map FROM import_db.images NATURAL JOIN import_db.rel_img_lab WHERE import_db.rel_img_lab.lab_id=".implode(" OR import_db.rel_img_lab.lab_id=", $_POST['labs']).";";
						$db -> query($query);
					}
					$db -> commit();

				}
				// Importing Configs
				if (isset($_POST['configs'])) {
					$db -> beginTransaction();
					foreach ($_POST['configs'] as $config) {
						$query = "INSERT OR REPLACE INTO main.configs (cfg_id, cfg_name, cfg_config, folder_id) SELECT cfg_id + '".$cfg_offset."', cfg_name, cfg_config, '".$_POST['configs_folder'][$config]."' FROM import_db.configs WHERE cfg_name LIKE '".$config." - %' OR cfg_name = '".$config."';";;
						$db -> query($query);
					}
					$db -> commit();
				}
				$query = "DETACH 'import_db';";
				$db -> query($query);
?>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Import</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<P>Data imported.</P>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
			} catch(PDOException $e) {
				error_log('DB: cannot export data with error "'.$e->getMessage().'" (query was "'.$query.'".');	
?>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Import</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<P>Failed to import data.</P>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
			}
		} else if (isset($_POST['action']) && $_POST['action'] == 'Import') {
?>
<SCRIPT type="text/javascript">
$(document).ready(function()
{
	$("#labs_all").click(function()				
	{
		var checked_status = this.checked;
		$('.labs').each(function()
		{
			this.checked = checked_status;
		});
	});
	$("#configs_all").click(function()				
	{
		var checked_status = this.checked;
		$('.configs').each(function()
		{
			this.checked = checked_status;
		});
	});					
});
</SCRIPT>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Import</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
<?php
			$finfo = new finfo;
			$fileinfo = $finfo->file($_FILES["import_file"]["tmp_name"], FILEINFO_MIME_TYPE);
			if ($fileinfo == "application/x-gzip") {
		
				$buffer_size = 4096; // read 4kb at a time
				$file_tmp = BASE_DIR.'/data/Import/iou-web-import-'.date('YmdHis').'.db';
				if (file_exists($file_tmp)) {
					unlink($file_tmp);
				}

				$sfp = gzopen($_FILES['import_file']['tmp_name'], "rb");
				$fp = fopen($file_tmp, "w");

				while ($string = gzread($sfp, 4096)) {
					fwrite($fp, $string, strlen($string));
				}
				gzclose($sfp);
				fclose($fp);
		
				database_update($file_tmp);
				database_optimize($file_tmp);
				$imported_db = new PDO('sqlite:'.$file_tmp);
				$imported_db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
			<FORM id="import_form" action="<?php print BASE_WWW ?>/manage.php?action=lab_import" method="post">
				<h2>Select labs you want to import:</h2>
				<TABLE id='templatemo_table'>
					<TR>
						<th colspan='2'>Name</th>
						<th>Description</th>
						<th>Folder</th>
					</TR>
					<TR>
						<TD><INPUT id='labs_all' type='checkbox'></TD>
						<TD colspan='3'><STRONG>Select all labs</STRONG></TD>
					</TR>
<?php
				$query = 'SELECT lab_id, lab_name, lab_description FROM labs ORDER BY lab_name COLLATE NOCASE ASC';
				$statement = $imported_db -> prepare($query);
				$statement -> execute();
				
				while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
?>
					<TR>
						<TD><INPUT class='labs' type='checkbox' name='labs[<?php print $result['lab_id'] ?>]' value='<?php print $result['lab_id'] ?>'></TD>
						<TD><?php print $result['lab_name'] ?></TD>
						<TD><?php print $result['lab_description'] ?></TD>
						<td>
							<select name='labs_folder[<?php print $result['lab_id'] ?>]'>
								<option value='0'selected>/</option>
<?php
					foreach ($GLOBAL['all_folders'] as $folder) {
?>
								<option value='<?php print $folder -> id ?>'><?php print $folder -> path ?></option>
<?php
					}
?>
							</select>
						</td>
					</TR>
<?php
				}
?>
				</TABLE>
				<p>Select image folder: <select name='imgs_folder'>
					<option value='0' selected>/</option>
<?php
					foreach ($GLOBAL['all_folders'] as $folder) {
?>
					<option value='<?php print $folder -> id ?>'><?php print $folder -> path ?></option>
<?php
					}
?>
				</select></p>
				<H2>Select initial config packs you want to import:</H2>
				<TABLE id='templatemo_table'>
					<tr>
						<th colspan='2'>Config Pack</th>
						<th>Folder</th>
					</tr>
					<TR>
						<TD><INPUT id='configs_all' type='checkbox'></TD>
						<TD colspan='2'><STRONG>Select all initial config packs</STRONG></TD>
					</TR>
<?php
				$query = 'SELECT DISTINCT RTRIM(REPLACE(cfg_name, LTRIM(cfg_name, \'1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM _+\'), \'\')) cfg_name, folder_id FROM configs ORDER BY cfg_name COLLATE NOCASE ASC';
				$statement = $imported_db -> prepare($query);
				$statement -> execute();
				
				while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
?>
					<TR>
						<TD><INPUT class='configs' type='checkbox' name='configs[<?php print $result['cfg_name'] ?>]' value='<?php print $result['cfg_name'] ?>'></TD>
						<TD><?php print $result['cfg_name'] ?></TD>
						<td>
							<select name='configs_folder[<?php print $result['cfg_name'] ?>]'>
								<option value='0' selected>/</option>
<?php
					foreach ($GLOBAL['all_folders'] as $folder) {
?>
								<option value='<?php print $folder -> id ?>'><?php print $folder -> path ?></option>
<?php
					}
?>
							</select>
						</td>
					</TR>
<?php
				}
?>
				</TABLE>
				<INPUT type="hidden" name="file" value="<?php print $file_tmp ?>">
				<INPUT type="submit" name="action" value="Import Labs">
				<INPUT type="reset">
			</FORM>
<?php
			} else {
?>
			<p>Import error: file must be in gzip format (found: $fileinfo).</p>
<?php
			}
?>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
		} else {
?>
<div class='block'>
	<div class='ui-tabs ui-widget ui-widget-content ui-corner-all'>
		<div class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
			<h1 class='bar'>Import</h1>
		</div>
		<div class='ui-tabs-panel ui-widget-content ui-corner-bottom'>
			<P>Select your previously exported file:</P>
			<FORM action="<?php print BASE_WWW ?>/manage.php?action=lab_import" method="post" enctype="multipart/form-data">
				<INPUT type="file" name="import_file">
				<INPUT type="submit" name="action" value="Import">
			</FORM>
		</div>
		<div class='clear'></div>
	</div>
</div>
<?php
		}
		page_footer();
		break;
	}
} else {
	header('Cache-Control: no-cache, must-revalidate');	// HTTP/1.1
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');	// Date in the past
	header('HTTP/1.1 403 Forbidden');					// Forbidden
	exit();
}
?>
