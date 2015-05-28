<?php
/** 
 * Configuration file for iou-web
 * 
 * This file include all needed files to run iou-web. Don't edit this file,
 * it will be overwritten when updating. Create a new file named 'custom.php'
 * under /opt/iou/html/includes and set the following parameters:
 *
 * define('ADMIN', true);
 * define('TIMEZONE', 'Europe/Rome');
 * define('BASE_DIR', '/opt/iou');
 * define('BASE_WWW', 'http://192.168.0.130:81/iou');
 * define('BASE_PORT', '2000');
 * define('BCK_RETENTION', '10');
 * define('CHECK_UPDATE', true);
 * define('PROXY', '192.168.0.1:3128');
 * define('UPDATE_INTERVAL', '2');
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */

if (file_exists('includes/custom.php')) {
	require_once('includes/custom.php');
}

if (!defined('ADMIN')) define('ADMIN', true);
if (!defined('TIMEZONE')) define('TIMEZONE', 'Europe/Rome');
if (!defined('BASE_DIR')) define('BASE_DIR', '/opt/iou');
if (!defined('BASE_WWW')) define('BASE_WWW', '');			// Change if you need custom url/port: 'http://192.168.72.130:81/iou'
if (!defined('BASE_PORT')) define('BASE_PORT', '2000');			// Change if you need different TELNET port.
if (!defined('BCK_RETENTION')) define('BCK_RETENTION', '10');
if (!defined('CHECK_UPDATE')) define('CHECK_UPDATE', true);
if (!defined('PROXY')) define('PROXY', '');				// Change to IP:port (i.e. '192.168.0.1:3128')
if (!defined('UPDATE_INTERVAL')) define('UPDATE_INTERVAL', '2');	// Seconds between check device status

define('VERSION', '1.2.2-22');
define('BASE_BIN', BASE_DIR.'/bin');
define('DATABASE', BASE_DIR.'/data/database.sdb');
define('WRAPPER', BASE_DIR.'/bin/wrapper-linux');
define('BASE_HUB', 10001);

date_default_timezone_set(TIMEZONE);

$db = new PDO('sqlite:'.DATABASE);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Include classes
require_once(BASE_DIR."/html/includes/__bin.php");
require_once(BASE_DIR."/html/includes/__config.php");
require_once(BASE_DIR."/html/includes/__configpack.php");
require_once(BASE_DIR."/html/includes/__device.php");
require_once(BASE_DIR."/html/includes/__folder.php");
require_once(BASE_DIR."/html/includes/__image.php");
require_once(BASE_DIR."/html/includes/__lab.php");

// Include functions
require_once(BASE_DIR."/html/includes/functions.php");

// Start a user session
session_start();

// Daily Backup
database_backup();

// First access: check for updates
if (is_admin() && CHECK_UPDATE && !isset($_SESSION['session_time_start'])) {
	$_SESSION['session_time_start'] = time();
	$_SESSION['session_check_update'] = true;
}

// 24h from last check: check for updates
if (is_admin() && CHECK_UPDATE && time() - $_SESSION['session_time_start'] >= 86400) {
	$_SESSION['session_time_start'] = time();
	$_SESSION['session_check_update'] = true;
}

/*************************************************************************
 * Binaries                                                              *
 *************************************************************************/
// Loading all binary (IOS): GLOBAL var is faster with multiuser
if (!isset($GLOBAL['all_bins'])) {
	try {
		global $db;
		$GLOBAL['all_bins'] = array();
		
		// Getting data from DB
		$query = 'SELECT bin_filename, bin_name FROM bins ORDER BY bin_name COLLATE NOCASE ASC;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		
		// Setting data to this object
		while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
			array_push($GLOBAL['all_bins'], new Bin($result['bin_filename'], $result['bin_name']));
		}
	} catch(PDOException $e) {
		error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
	}
}

/*************************************************************************
 * Configs                                                               *
 *************************************************************************/
// Loading all configs (name and id only): GLOBAL var is faster with multiuser
if (!isset($GLOBAL['all_configs'])) {
	try {
		global $db;
		$GLOBAL['all_configs'] = array();
		
		// Getting data from DB
		$query = 'SELECT cfg_id, cfg_name, folder_id FROM configs ORDER BY cfg_name COLLATE NOCASE ASC;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		
		// Setting data to this object
		while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
			array_push($GLOBAL['all_configs'], new Config(false, $result['cfg_id'], $result['cfg_name'], $result['folder_id']));
		}
	} catch(PDOException $e) {
		error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
	}
}

// Loading all config packs: GLOBAL var is faster with multiuser
if (!isset($GLOBAL['all_configpacks'])) {
	try {
		global $db;
		$GLOBAL['all_configpacks'] = array();
		
		// Getting data from DB
		$query = 'SELECT DISTINCT RTRIM(REPLACE(cfg_name, LTRIM(cfg_name, \'1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM _+\'), \'\')) cfg_name, folder_id FROM configs ORDER BY cfg_name COLLATE NOCASE ASC;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		
		// Setting data to this object
		while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
			array_push($GLOBAL['all_configpacks'], new ConfigPack($result['cfg_name'], $result['folder_id']));
		}
	} catch(PDOException $e) {
		error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
	}
}

 // Loading Config if cfg_pack is set TODO: folder_id is not valid
if(isset($_GET['cfg_pack']) && preg_match('/^[A-Za-z0-9_:+\\s]+$/', rawurldecode($_GET['cfg_pack']))) {
	// Check if cfg_pack exists
	$found = false;
	foreach ($GLOBAL['all_configpacks'] as $configpack) {
		if ($configpack -> name == rawurldecode($_GET['cfg_pack'])) {
			$found = true;
		}
	}
	 
	if ($found == true) { 
		// Check if the right configpack is already loaded
		if (!isset($_SESSION['current_confpack']) || $_SESSION['current_confpack'] -> name != rawurldecode($_GET['cfg_pack'])) {
			$_SESSION['current_confpack'] = new ConfigPack(rawurldecode($_GET['cfg_pack']), '');
			$_SESSION['current_confpack'] -> load();
		}
	} else {
		unset($_SESSION['current_confpack']);
		header("Location: ".BASE_WWW."/");
		exit();
	}
}

/*************************************************************************
 * Folders                                                               *
 *************************************************************************/
// Loading all folders: GLOBAL var is faster with multiuser
if (!isset($GLOBAL['all_folders'])) {
	try {
		global $db;
		$GLOBAL['all_folders'] = array();
		
		// Getting data from DB
		$query = 'SELECT folder_id, folder_name, parent_id FROM folders ORDER BY folder_name COLLATE NOCASE ASC;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		
		// Setting data to this object
		while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
			array_push($GLOBAL['all_folders'], new Folder(false, $result['folder_id'], $result['folder_name'], $result['parent_id']));
		}
	} catch(PDOException $e) {
		error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
	}
}

// If folder is selected and valid, save as current_folder
if (isset($_GET['folder_id']) && is_numeric($_GET['folder_id'])) {
	if ($_GET['folder_id'] == 0) {
		$_SESSION['current_folder'] = new Folder(false, 0, '', 0);
	} else {
		// Check if selected folder exists
		foreach ($GLOBAL['all_folders'] as $folder) {
			if ($folder -> id == $_GET['folder_id']) {
				$_SESSION['current_folder'] = new Folder(true, $_GET['folder_id'], '', '');
				break;
			}
		}
	}
}

// If no folder is selected/found, default folder is 0
if (!isset($_SESSION['current_folder'])) {
	$_SESSION['current_folder'] = new Folder(false, 0, '', 0);
}

/*************************************************************************
 * Images                                                                *
 *************************************************************************/
// Loading all images (name and id only): GLOBAL var is faster with multiuser
if (!isset($GLOBAL['all_images'])) {
	try {
		global $db;
		$GLOBAL['all_images'] = array();
		
		// Getting data from DB
		$query = 'SELECT img_id, img_name, folder_id FROM images ORDER BY img_name COLLATE NOCASE ASC;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		
		// Setting data to this object
		while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
			array_push($GLOBAL['all_images'], new Image(false, $result['img_id'], $result['img_name'], $result['folder_id']));
		}
	} catch(PDOException $e) {
		error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
	}
}

// Loading Image if img_id is set
if(isset($_GET['img_id']) && is_numeric($_GET['img_id'])) {
	// Check if img exists
	$found = false;
	foreach ($GLOBAL['all_images'] as $image) {
		if ($image -> id == $_GET['img_id']) {
			$found = true;
		}
	}
	 
	if ($found == true) { 
		// Check if the right image is already loaded
		if (!isset($_SESSION['current_img']) || $_SESSION['current_img'] -> id != $_GET['img_id']) {
			$_SESSION['current_img'] = new Image(true, $_GET['img_id'], '', '');
		}
	} else {
		unset($_SESSION['current_img']);
		header("Location: ".BASE_WWW."/");
		exit();
	}
}

/*************************************************************************
 * Labs                                                                  *
 *************************************************************************/
// Loading all labs (name and id only): GLOBAL var is faster with multiuser
if (!isset($GLOBAL['all_labs'])) {
	try {
		global $db;
		$GLOBAL['all_labs'] = array();
		
		// Getting data from DB
		$query = 'SELECT lab_id, lab_name, lab_description, folder_id FROM labs ORDER BY lab_name COLLATE NOCASE ASC;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		
		// Setting data to this object
		while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
			array_push($GLOBAL['all_labs'], new Lab(false, $result['lab_id'], $result['lab_name'], $result['lab_description'], $result['folder_id']));
		}
	} catch(PDOException $e) {
		error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
	}
}

// Loading Lab if a numeric lab_id is set
if(isset($_GET['lab_id']) && is_numeric($_GET['lab_id'])) {
	// Check if lab exists
	$found = false;
	foreach ($GLOBAL['all_labs'] as $lab) {
		if ($lab -> id == $_GET['lab_id']) {
			$found = true;
		}
	}
	 
	if ($found == true) { 
		// Check if the right lab is already loaded
		if (isset($_SESSION['current_lab']) && $_SESSION['current_lab'] -> id == $_GET['lab_id']) {
			// Current lab already loaded, just reset timer
			$_SESSION['current_lab_start'] = time();
		}
		if (!isset($_SESSION['current_lab']) || $_SESSION['current_lab'] -> id != $_GET['lab_id']) {
			$_SESSION['current_lab'] = new Lab(true, $_GET['lab_id'], '', '', '');
			$_SESSION['current_lab'] -> load();
			$_SESSION['current_lab_start'] = time();
		}
	} else {
		unset($_SESSION['current_lab']);
		unset($_SESSION['current_lab_start']);
		header("Location: ".BASE_WWW."/");
		exit();
	}
}

// Check if current lab has a valid folder (Ubuntu wipe /tmp after reboot)
if (isset($_SESSION['current_lab'] -> id) && !file_exists('/tmp/iou/lab_'.$_SESSION['current_lab'] -> id.'/NETMAP')) {
	unset($_SESSION['current_lab']);
}
?>
