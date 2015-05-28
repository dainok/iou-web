<?php
/**
 * Ajax Helper for iou-web
 * 
 * This file answers to Ajax/jQuery requests responding with a XML
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */
include('includes/conf.php');

if (isset($_GET['action'])) {
	$action = $_GET['action'];
} else {
	$action = 'default';
}

switch ($action) {
/*************************************************************************
 * Default case is Forbidden                                             *
 *************************************************************************/
	default:
		$status = 'Error';
		$message = 'Function '.$action.' not available.';
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Clean a config                                                        *
 *************************************************************************/
	case 'cfg_clean':
		// A lab must be selected, dev_id must be set and must be numeric or 'all'
		if (isset($_SESSION['current_lab']) && isset($_GET['dev_id']) && (is_numeric($_GET['dev_id']) || $_GET['dev_id'] == 'all')) {
			$dev_id = $_GET['dev_id'];
			switch ($dev_id) {
				case 'all':
					// Snaphost of all devices
					$rc_all = true;
					foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
						$rc = $_SESSION['current_lab'] -> devices[$netmap_id] -> clean();
						$rc_all = $rc_all && $rc ? true : false;
					}
					if ($rc_all) {
						// All devices wiped
						$status = 'Informational';
						$message = 'Deleted all configurations.';
					} else {
						// At least one error
						$status = 'Error';
						$message = 'Failed to delete at least one configuration. Check logs (under Downloads page) for additional informations.';
					}
					break;
				default:
					if ($_SESSION['current_lab'] -> devices[$dev_id] -> clean()) {
						// Device wiped
						$status = 'Informational';
						$message = 'Deleted all configurations of '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' device.';
					} else {
						// Cannot wipe
						$status = 'Error';
						$message = 'Cannot delete configurations of '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' device. Check logs (under Downloads page) for additional informations.';
					}
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab or device selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Delete a config/config pack                                           *
 *************************************************************************/
	case 'cfg_delete':
		// Must be Admin and cfg_name numeric
		if(is_admin() && isset($_GET['cfg_name']) && preg_match('/^[A-Za-z0-9_:+\\s-]+$/', $_GET['cfg_name'])) {
			$cfg_name = rawurldecode($_GET['cfg_name']);
			if (cfg_delete($cfg_name)) {
				$status = 'Informational';
				$message = 'Config '.$cfg_name.' deleted.';
				unset($GLOBAL['all_configpacks']);
				unset($_SESSION['current_confpack']);
			} else {
				$status = 'Error';
				$message = 'Cannot delete config '.$cfg_name.'. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required, or no config selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Edit a config                                                         *
 *************************************************************************/
	case 'cfg_edit':
		// Must be Admin, cfg_id numeric, cfg_name contains "A-Z, a-z, 0-9, _, :, +, - , ' '", and cfg_config setted.
		if(is_admin() && isset($_POST['cfg_id']) && is_numeric($_POST['cfg_id']) &&
			isset($_POST['cfg_name']) && preg_match('/^[A-Za-z0-9_:+\\s-]+$/', $_POST['cfg_name']) &&
			isset($_POST['cfg_config'])) {
			$cfg_id = $_POST['cfg_id'];
			$cfg_name = $_POST['cfg_name'];
			$cfg_config = $_POST['cfg_config'];
			if (cfg_update($cfg_id, $cfg_name, $cfg_config)) {
				$status = 'Informational';
				$message = 'Config '.$cfg_name.' updated.';
				unset($GLOBAL['all_configpacks']);
				unset($_SESSION['current_confpack']);
			} else {
				$status = 'Error';
				$message = 'Cannot update config '.$cfg_name.'. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required, or no config selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Export config                                                         *
 *************************************************************************/
	case 'cfg_export':
		// A lab must be selected, dev_id must be set and must be numeric or 'all'
		if (isset($_SESSION['current_lab']) && isset($_GET['dev_id']) && (is_numeric($_GET['dev_id']) || $_GET['dev_id'] == 'all')) {
			$dev_id = $_GET['dev_id'];
			switch ($dev_id) {
				case 'all':
					// Exports all configs
					$rc_all = true;
					foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
						$rc = $_SESSION['current_lab'] -> devices[$netmap_id] -> export();
						$rc_all = $rc_all && $rc ? true : false;
					}
					if ($rc_all) {
						// All config exported
						$status = 'Informational';
						$message = 'All config exported to snapshot.';
						unset($GLOBAL['all_configs']);
						unset($GLOBAL['all_configpacks']);
						unset($_SESSION['current_confpack']);
						$_SESSION['current_lab'] -> load();
					} else {
						// At least one error
						$status = 'Error';
						$message = 'At least one config failed to export. Check logs (under Downloads page) for additional informations.';
					}
					break;
				default:
					if ($_SESSION['current_lab'] -> devices[$dev_id] -> export()) {
						// Config exported
						$status = 'Informational';
						$message = 'Config of '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' exported.';
						unset($GLOBAL['all_configs']);
						unset($GLOBAL['all_configpacks']);
						unset($_SESSION['current_confpack']);
						$_SESSION['current_lab'] -> load();
					} else {
						// Cannot revert
						$status = 'Error';
						$message = 'Cannot export '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' config. Check logs (under Downloads page) for additional informations.';
					}
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab or device selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Revert to snapshot                                                    *
 *************************************************************************/
	case 'cfg_revert':
		// A lab must be selected, dev_id must be set and must be numeric or 'all'
		if (isset($_SESSION['current_lab']) && isset($_GET['dev_id']) && (is_numeric($_GET['dev_id']) || $_GET['dev_id'] == 'all')) {
			$dev_id = $_GET['dev_id'];
			switch ($dev_id) {
				case 'all':
					// Snaphost of all devices
					$rc_all = true;
					foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
						$rc = $_SESSION['current_lab'] -> devices[$netmap_id] -> revert();
						$rc_all = $rc_all && $rc ? true : false;
					}
					if ($rc_all) {
						// All devices reverted
						$status = 'Informational';
						$message = 'All devices reverted to snapshot.';
					} else {
						// At least one error
						$status = 'Error';
						$message = 'At least one device failed to revert to the snapshot. Check logs (under Downloads page) for additional informations.';
					}
					break;
				default:
					if ($_SESSION['current_lab'] -> devices[$dev_id] -> revert()) {
						// Device reverted
						$status = 'Informational';
						$message = 'Device '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' reverted to snapshot.';
					} else {
						// Cannot revert
						$status = 'Error';
						$message = 'Cannot revert to snapshot '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' device. Check logs (under Downloads page) for additional informations.';
					}
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab or device selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Make a snapshot                                                       *
 *************************************************************************/
	case 'cfg_snapshot':
		// A lab must be selected, dev_id must be set and must be numeric or 'all'
		if (isset($_SESSION['current_lab']) && isset($_GET['dev_id']) && (is_numeric($_GET['dev_id']) || $_GET['dev_id'] == 'all')) {
			$dev_id = $_GET['dev_id'];
			switch ($dev_id) {
				case 'all':
					// Snaphost of all devices
					$rc_all = true;
					foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
						$rc = $_SESSION['current_lab'] -> devices[$netmap_id] -> snapshot();
						$rc_all = $rc_all && $rc ? true : false;
					}
					if ($rc_all) {
						// All devices snapshotted
						$status = 'Informational';
						$message = 'Created snapshot of all devices.';
					} else {
						// At least one error
						$status = 'Error';
						$message = 'At least one device failed to make a snapshot. Check logs (under Downloads page) for additional informations.';
					}
					break;
				default:
					if ($_SESSION['current_lab'] -> devices[$dev_id] -> snapshot()) {
						// Device snapshotted
						$status = 'Informational';
						$message = 'Created snapshot of device '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.'.';
					} else {
						// Cannot make the snapshot
						$status = 'Error';
						$message = 'Cannot make a snapshot of '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' device. Check logs (under Downloads page) for additional informations.';
					}
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab or device selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Check Update                                                          *
 *************************************************************************/ 
	case 'check_update':
		$version_url = 'http://public.routereflector.com/iou-web/version';
		$whatsnew_url = 'http://public.routereflector.com/iou-web/whatsnew';
		if (PROXY != '') {
			$aContext = array('http' => array('proxy' => 'tcp://'.PROXY, 'request_fulluri' => true));
			$cxContext = stream_context_create($aContext);
		}
		if (isset($cxContext)) {
			// Using a Proxy
			try {
				$version = @file_get_contents($version_url, false, $cxContext);
				$whatsnew = @file_get_contents($whatsnew_url, false, $cxContext);
				$checked = true;
			} catch(Exception $e) {
				$checked = false;
			}
		} else {
			// Direct Connection
			try {
				$version = trim(@file_get_contents($version_url));
				$whatsnew = trim(@file_get_contents($whatsnew_url));
				$checked = true;
			} catch(Exception $e) {
				$checked = false;
			}
		}
		if ($checked) {
			header("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");	// Date in the past
			header('HTTP/1.1 200 OK');							// OK
			print "<?xml version='1.0' encoding='UTF-8'?>\n";
?>
<msg>
	<status>0</status>
<?php
			// Check if current version is the newest (for beta-tester)
			$official_major = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$1', $version);
			$official_medium = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$2', $version);
			$official_minor = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$3', $version);
			$official_release = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$4', $version);
			$this_major = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$1', VERSION);
			$this_medium = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$2', VERSION);
			$this_minor = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$3', VERSION);
			$this_release = preg_replace('/^([0-9]+)\.([0-9]+)\.([0-9]+)-([0-9]+)$/', '$4', VERSION);
			
			if ($this_major < $official_major || $this_medium < $official_medium || $this_minor < $official_minor || $this_release < $official_release) {
?>
	<message><?php print $version ?></message>
	<whatsnew><?php print $whatsnew ?></whatsnew>
<?php
			} else {
?>
	<message><?php print VERSION ?></message>
	<whatsnew></whatsnew>
<?php
			}
?>
</msg>
<?php
		} else {
			$status = 'Error';
			$message = 'Cannot check updates.';
			xml_message($status, $message);
		}
		break;
/*************************************************************************
 * Database: optimize                                                    *
 *************************************************************************/
	case 'db_optimize':
		if (is_admin()) {
			if (database_optimize(DATABASE)) {
				$status = 'Informational';
				$message = 'Database optimized.';
			} else {
				$status = 'Error';
				$message = 'Cannot optimize the database. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Database: wipe                                                        *
 *************************************************************************/
	case 'db_wipe':
		if (is_admin()) {
			if (database_init(DATABASE)) {
				$status = 'Informational';
				$message = 'Database reinitializated.';
				session_unset();
				unset($GLOBAL['all_configs']);
				unset($GLOBAL['all_configpack']);
				unset($GLOBAL['all_bins']);
				unset($GLOBAL['all_images']);
				unset($GLOBAL['all_folders']);
				unset($GLOBAL['all_labs']);
			} else {
				$status = 'Error';
				$message = 'Cannot reinitializated the database. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Print XML for device status                                           *
 *************************************************************************/
	case 'dev_status':
		// Current lab must be selected and loaded
		if (isset($_SESSION['current_lab']) && isset($_SESSION['current_lab'] -> devices)) {
			print "<?xml version='1.0' encoding='UTF-8'?>\n";
?>
<lab id='<?php print $_SESSION['current_lab'] -> id ?>'>
<?php
			foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
?>
	<device id='<?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> id ?>'>
		<name><?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> name ?></name>
		<type><?php print $_SESSION['current_lab'] -> devices[$netmap_id] -> picture ?></type>
		<status><?php print ($_SESSION['current_lab'] -> devices[$netmap_id] -> isRunning()) ? '1' : '0' ?></status>
	</device>
<?php
			}
			foreach ($_SESSION['current_lab'] -> netmap_hubs as $netmap_hub) {
?>
	<device id='<?php print $_SESSION['current_lab'] -> devices[$netmap_hub] -> id ?>'>
		<name>Hub</name>
		<type>hub</type>
		<status>1</status>
	</device>
<?php
			}
?>
	<sniffer>
		<status><?php print $_SESSION['current_lab'] -> isSnifferRunning() ? 1 : 0 ?></status>
	</sniffer>
</lab>
<?php
		} else {
			$status = 'Error';
			$message = 'No lab selected.';
			xml_message('AJAX '.$action, $status, $message);
		}
		break;
/*************************************************************************
 * Start a device                                                        *
 *************************************************************************/
	case 'dev_start':
		// A lab must be selected, dev_id must be set and must be numeric or 'all'
		if (isset($_SESSION['current_lab']) && isset($_GET['dev_id']) && (is_numeric($_GET['dev_id']) || $_GET['dev_id'] == 'all')) {
			$dev_id = $_GET['dev_id'];
			switch ($dev_id) {
				case 'all':
					// Starting all devices
					$rc_all = true;
					foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
						$rc = $_SESSION['current_lab'] -> devices[$netmap_id] -> start();
						$rc_all = $rc_all && $rc ? true : false;
					}
					if ($rc_all) {
						// All devices stared
						$status = 'Informational';
						$message = 'All devices started.';
					} else {
						// At least one error
						$status = 'Informational';
						$message = 'Starting all devices. Check logs (under Downloads page) for additional informations.';
					}
					break;
				default:
					if ($_SESSION['current_lab'] -> devices[$dev_id] -> start()) {
						// Device started
						$status = 'Informational';
						$message = 'Device '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' started.';
					} else {
						// Cannot start device
						$status = 'Informational';
						$message = 'Startin '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' device. Check logs (under Downloads page) for additional informations.';
					}
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab or device selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Stop a device                                                         *
 *************************************************************************/
	case 'dev_stop':
		// A lab must be selected, dev_id must be set and must be numeric or 'all'
		if (isset($_SESSION['current_lab']) && isset($_GET['dev_id']) && (is_numeric($_GET['dev_id']) || $_GET['dev_id'] == 'all' || $_GET['dev_id'] == 'global')) {
			$dev_id = $_GET['dev_id'];
			switch ($dev_id) {
				case 'all':
					// Stopping all devices
					$rc_all = true;
					foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
						$rc = $_SESSION['current_lab'] -> devices[$netmap_id] -> stop();
						$rc_all = $rc_all && $rc ? true : false;
					}
					if ($rc_all) {
						// All devices stopped
						$status = 'Informational';
						$message = 'All devices stopped.';
					} else {
						// At least one error
						$status = 'Informational';
						$message = 'Stopping all devices. Check logs (under Downloads page) for additional informations.';
					}
					break;
				case 'global':
                                        // Stopping Everything
                                        $command = 'sudo pkill -f /tmp/iou/';
                                        exec($command, $output, $pid);
                                        $command = 'sudo pkill -f ioulive86';
                                        exec($command, $output, $pid);
                                        $status = 'Informational';
                                        $message = 'All devices and Clouds stopped.';
					break;
				default:
					if ($_SESSION['current_lab'] -> devices[$dev_id] -> stop()) {
						// Device started
						$status = 'Informational';
						$message = 'Device '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' stopped.';
					} else {
						// Cannot stop device
						$status = 'Informational';
						$message = 'Stopping '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' device. Check logs (under Downloads page) for additional informations.';
					}
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab or device selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Reset a device console                                                *
 *************************************************************************/
	case 'dev_reset':
		// A lab must be selected, dev_id must be set and must be numeric or 'all'
		if (isset($_SESSION['current_lab']) && isset($_GET['dev_id']) && (is_numeric($_GET['dev_id']) || $_GET['dev_id'] == 'all')) {
			$dev_id = $_GET['dev_id'];
			switch ($dev_id) {
				case 'all':
					// Stopping all devices
					$rc_all = true;
					foreach ($_SESSION['current_lab'] -> netmap_ids as $netmap_id) {
						$rc = $_SESSION['current_lab'] -> devices[$netmap_id] -> reset();
						$rc_all = $rc_all && $rc ? true : false;
					}
					if ($rc_all) {
						// All devices stopped
						$status = 'Informational';
						$message = 'All console resetted.';
					} else {
						// At least one error
						$status = 'Error';
						$message = 'At least one console failed to reset. Check logs (under Downloads page) for additional informations.';
					}
					break;
				default:
					if ($_SESSION['current_lab'] -> devices[$dev_id] -> reset()) {
						// Device started
						$status = 'Informational';
						$message = $_SESSION['current_lab'] -> devices[$dev_id] -> name.' console resetted.';
					} else {
						// Cannot stop device
						$status = 'Error';
						$message = 'Cannot reset '.$_SESSION['current_lab'] -> devices[$dev_id] -> name.' console. Check logs (under Downloads page) for additional informations.';
					}
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab or device selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Delete a file                                                         *
 *************************************************************************/
	case 'file_delete':
		// Must be Admin and file_name set
		if(is_admin() && isset($_GET['file_name'])) {
			$file_name = rawurldecode($_GET['file_name']);
			if (file_delete($file_name)) {
				$status = 'Informational';
				$message = 'File '.$file_name.' deleted.';
				unset($GLOBAL['all_bins']);
			} else {
				$status = 'Error';
				$message = 'Cannot delete '.$file_name.'. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required, or no file selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Add a folder                                                          *
 *************************************************************************/
	case 'folder_add':
		// Must be Admin, folder_name contains only 'A-Z', 'a-z', '0-9', '-', '_', ' ' and folder_parent numeric
		if(is_admin() && isset($_POST['folder_parent']) && is_numeric($_POST['folder_parent']) &&
			isset($_POST['folder_name']) && preg_match('/^[A-Za-z0-9_-\\s]+$/', rawurldecode($_POST['folder_name']))) {
			if (folder_add(rawurldecode($_POST['folder_name']), $_POST['folder_parent'])) {
				$status = 'Informational';
				$message = 'Folder '.rawurldecode($_POST['folder_name']).' created.';
				unset($GLOBAL['all_folders']);
			} else {
				$status = 'Error';
				$message = 'Cannot create folder '.rawurldecode($_POST['folder_name']).'. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			if (isset($_POST['folder_name'])) {
				$status = 'Error';
				$message = 'Admin privileges required, or invalid folder name ('.rawurldecode($_POST['folder_name']).').';
			} else {
				$status = 'Error';
				$message = 'Admin privileges required, or invalid folder name (null).';
			}
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Delete a folder                                                       *
 *************************************************************************/
	case 'folder_delete':
		// Must be Admin and folder_id numeric
		if(is_admin() && isset($_GET['folder_id']) && is_numeric($_GET['folder_id'])) {
			foreach ($GLOBAL['all_folders'] as $folder) {
				if ($folder -> id == $_GET['folder_id']) {
					$parent_id = $folder -> parent_id;
				}
			}
			if (!isset($parent_id)) {
				$status = 'Informational';
				$message = 'Cannot find parent of '.$_GET['folder_id'].'.';
			} else if (folder_delete($_GET['folder_id'], $parent_id)) {
				$status = 'Informational';
				$message = 'Folder '.$_GET['folder_id'].' deleted.';
				unset($GLOBAL['all_folders']);
				unset($_SESSION['current_folder']);
			} else {
				$status = 'Error';
				$message = 'Cannot delete folder '.$_GET['folder_id'].'. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required, or no folder selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Move into a folder                                                    *
 *************************************************************************/
	case 'folder_move':
		// Must be Admin, src_obj contains only 'A-Z', 'a-z', '0-9', '_', ' ', '-' and folder_dst must be folderX (X is 0-9).
		if(is_admin() && isset($_GET['src_obj']) && preg_match('/^[A-Za-z0-9 _+\\s]+$/', rawurldecode($_GET['src_obj'])) && isset($_GET['folder_dst']) && preg_match('/^folder[0-9]*$/', $_GET['folder_dst'])) {
			$src_obj = rawurldecode($_GET['src_obj']);
			$folder_dst = $_GET['folder_dst'];
			folder_move($src_obj, $folder_dst);
			if (folder_move(rawurldecode($src_obj), $folder_dst)) {
				$status = 'Informational';
				$message = 'Object '.$src_obj.' moved.';
				unset($GLOBAL['all_folders']);
			} else {
				$status = 'Error';
				$message = 'Cannot move object '.$src_obj.'. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required, or invalid folder name.';
		}
		xml_message('AJAX '.$action, $status, $message);
                break;
/*************************************************************************
 * Clone a folder and its content                                        *
 *************************************************************************/
    case 'clone_folder':
    // Must be Admin, folder_name contains only 'A-Z', 'a-z', '0-9', '-', '_', ' ' and folder_parent numeric
    if(is_admin() && isset($_POST['folder_id']) && is_numeric($_POST['folder_id']) &&
        isset($_POST['new_folder_name']) && preg_match('/^[A-Za-z0-9_-\\s]+$/', rawurldecode($_POST['new_folder_name'])) &&
        isset($_POST['destination_folder_id'])  && is_numeric($_POST['destination_folder_id']) ) {
        if (folder_clone($_POST['folder_id'], rawurldecode($_POST['new_folder_name']), $_POST['destination_folder_id'] )) {
            $status = 'Informational';
            $message = 'Folder '.rawurldecode($_POST['new_folder_name']).' cloned.';
            unset($GLOBAL['all_folders']);
        } else {
            $status = 'Error';
            $message = 'Cannot create folder '.rawurldecode($_POST['new_folder_name']).'. Check logs (under Downloads page) for additional informations.';
        }
    } else {
        if (isset($_POST['new_folder_name'])) {
            $status = 'Error';
            $message = 'Admin privileges required, or invalid folder name ('.rawurldecode($_POST['new_folder_name']).').';
        } else {
            $status = 'Error';
            $message = 'Admin privileges required, or invalid folder name (null).';
        }
    }
    xml_message('AJAX '.$action, $status, $message);
    break;
/*************************************************************************
 * Delete an image                                                       *
 *************************************************************************/
	case 'img_delete':
		// Must be Admin and img_id numeric
		if(is_admin() && isset($_GET['img_id']) && is_numeric($_GET['img_id'])) {
			if (img_delete($_GET['img_id'])) {
				$status = 'Informational';
				$message = 'Image deleted.';
				if (isset($_SESSION['current_img']) && $_SESSION['current_img'] -> id == $_GET['lab_id']) {
					unset($_SESSION['current_img']);
				}
				unset($GLOBAL['all_images']);
			} else {
				$status = 'Error';
				$message = 'Cannot delete image. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required, or no image selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Show an Image                                                         *
 *************************************************************************/
	case 'img_show':
		// img_id must be numeric, a lab must be selcted and image must be present
		if(isset($_GET['img_id']) && is_numeric($_GET['img_id']) && isset($_SESSION['current_lab']) && isset($_SESSION['current_lab'] -> images[$_GET['img_id']])) {
			header("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");	// Date in the past
			header('HTTP/1.1 200 OK');							// OK
			header('Content-type: image/png');
			print $_SESSION['current_lab'] -> images[$_GET['img_id']] -> content;
		} else if (isset($_GET['img_id']) && is_numeric($_GET['img_id'])) {
			// Used if single image is selected (outside a lab)
			header("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");	// Date in the past
			header('HTTP/1.1 200 OK');							// OK
			header('Content-type: image/png');
			print img_print($_GET['img_id']);				
		} else {
			$status = 'Error';
			$message = 'Cannot print image.';
			xml_message('AJAX '.$action, $status, $message);
		}
		break;
/*************************************************************************
 * Delete a lab                                                          *
 *************************************************************************/
	case 'lab_delete':
		// Must be admin and lab_id must be numeric
		if(is_admin() && isset($_GET['lab_id']) && is_numeric($_GET['lab_id'])) {
			if (lab_delete($_GET['lab_id'])) {
				$status = 'Informational';
				$message = 'Lab deleted.';
				unset($GLOBAL['all_labs']);
				if (isset($_SESSION['current_lab']) && $_SESSION['current_lab'] -> id == $_GET['lab_id']) {
					unset($_SESSION['current_lab']);
				}
			} else {
				$status = 'Error';
				$message = 'Cannot delete lab. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			$status = 'Error';
			$message = 'Admin privileges required, or no lab selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * License                                                               *
 *************************************************************************/
	case 'license_save':
		// Must be admin and license must be set
		if (is_admin() && isset($_POST['license_data'])) {
			$license_data = $_POST['license_data'];
			file_put_contents (BASE_BIN.'/iourc', $license_data);
			$status = 'Informational';
			$message = 'License saved.';
		} else {
			$status = 'Error';
			$message = 'Admin privileges needed.';
		}
		xml_message($status, $message);
		break;
/*************************************************************************
 * Start sniffing                                                        *
 *************************************************************************/
	case 'sniffer_start':
		// A lab must be selected
		if (isset($_SESSION['current_lab'])) {
			if ($_SESSION['current_lab'] -> snifferStart()) {
				// Sniffer started
				$status = 'Informational';
				$message = 'Sniffer started.';
			} else {
				// Cannot start sniffer
				$status = 'Informational';
				$message = 'Starting the sniffer. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Stop sniffing                                                        *
 *************************************************************************/
	case 'sniffer_stop':
		// A lab must be selected
		if (isset($_SESSION['current_lab'])) {
			if ($_SESSION['current_lab'] -> snifferStop()) {
				// Sniffer stopped
				$status = 'Informational';
				$message = 'Sniffer stopped.';
			} else {
				// Cannot stop sniffer
				$status = 'Informational';
				$message = 'Stopping the sniffer. Check logs (under Downloads page) for additional informations.';
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No lab selected.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
/*************************************************************************
 * Clear                                                                 *
 *************************************************************************/
	case 'clear':
		// Must be admin and files 'sniffer' or 'all'
		if (is_admin() && isset($_GET['files']) &&($_GET['files'] == 'sniffer' || $_GET['files'] == 'all')) {
			switch ($_GET['files']) {
				case 'all':
					$status = 'Informational';
					$message = 'Deleting all cache files.';
					$command = 'sudo rm -rf /tmp/netio* /tmp/iou* /var/lib/php/session/* '.BASE_DIR.'/labs/* '.BASE_DIR.'/data/Export/* '.BASE_DIR.'/data/Import/* '.BASE_DIR.'/data/Logs/*';
					exec($command, $output, $pid);
					$command = 'sudo apachectl graceful';
					exec($command, $output, $pid);
					$command = 'sudo pkill iousniff';
					exec($command, $output, $pid);
					$command = 'sudo rm -f '.BASE_DIR.'/data/Sniffer/*';
					exec($command, $output, $pid);
					$command = 'sudo /sbin/reboot';
					exec($command, $output, $pid);
					break;
				case 'sniffer':
					$status = 'Informational';
					$message = 'Deleting sniffer files only ("'.BASE_DIR.'/data/Sniffer/*").';
					$command = 'sudo pkill iousniff';
					exec($command, $output, $pid);
					$command = 'sudo rm -f '.BASE_DIR.'/data/Sniffer/*';
					exec($command, $output, $pid);
					break;
			}
		} else {
			// Error parsing options
			$status = 'Error';
			$message = 'No acion not valid.';
		}
		xml_message('AJAX '.$action, $status, $message);
		break;
}
?>
