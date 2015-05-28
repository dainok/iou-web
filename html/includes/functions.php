<?php
/**
 * Various functions for iou-web
 * 
 * This file defines the following functions:
 * - database_backup: used to backup the database
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */
/**
 * Function to insert an initial configuration
 * 
 * @param	string	$cfg_name			Name of the initial config
 * @param	string	$cfg_config			Initial config
 * @return	bool						true if successfully backed up
 */
function cfg_add($cfg_name, $cfg_config, $folder_id) {
	try {
		global $db;
		// Before DELETE because cfg_id is unknown
		$query = 'DELETE FROM configs WHERE cfg_name=:cfg_name;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':cfg_name', $cfg_name, PDO::PARAM_STR);
		$statement -> execute();
		// Then INSERT
		$query = 'INSERT INTO configs (cfg_name, cfg_config, folder_id) VALUES (:cfg_name, :cfg_config, :folder_id);';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':cfg_name', $cfg_name, PDO::PARAM_STR);
		$statement -> bindParam(':cfg_config', $cfg_config, PDO::PARAM_LOB);
		$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
		$statement -> execute();
		return true;
	} catch(PDOException $e) {
		error_log('DB: cannot update the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to delete a config/config pack
 * 
 * @param	string	$cfg_name			Name of the initial config
 * @return	bool						True if successfully deleted
 */
function cfg_delete($cfg_name) {
	try {
		global $db;
		$query = 'DELETE FROM configs WHERE cfg_name LIKE "'.$cfg_name.' - %" OR cfg_name = "'.$cfg_name.'";';
		$statement = $db -> prepare($query);
		$statement -> execute();
		return true;
	} catch(PDOException $e) {
		error_log('DB: cannot update the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to update an initial configuration
 *
 * @param	int		$cfg_id				Id of the initial config to update
 * @param	string	$cfg_name			Name of the initial config
 * @param	string	$cfg_config			Initial config
 */
function cfg_update($cfg_id, $cfg_name, $cfg_config) {
	try {
		global $db;
		$statement = $db -> prepare("UPDATE configs SET cfg_name=:cfg_name, cfg_config=:cfg_config WHERE cfg_id=:cfg_id;");
		$statement -> bindParam(':cfg_id', $cfg_id, PDO::PARAM_INT);
		$statement -> bindParam(':cfg_name', $cfg_name, PDO::PARAM_STR);
		$statement -> bindParam(':cfg_config', $cfg_config, PDO::PARAM_LOB);
		$statement -> execute();
	} catch(PDOException $e) {
		msg_error("Cannot query the DB: ".$e->getMessage());
		return;
	}
}
/**
 * Function to daily backup the database
 * 
 * @return	bool						true if successfully backed up
 */
function database_backup() {
	// Daily backup
	$today = date("Ymd");
	if (!file_exists(DATABASE."-$today")) {
		if (copy(DATABASE, DATABASE."-$today")) {
			return true;
		} else {
			error_log('DB: failed to backup the database.');
			return false;
		}
	}
	
	// Count how many backup files
	$dir = BASE_DIR."/data/";
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if (strstr($filename, 'database.sdb-')) {
			$files[] = $filename;
		}
	}
	rsort($files);
	
	// Delete older backup file
	$counter = count($files)-1;
	while ($counter >= BCK_RETENTION) {
		if (unlink(BASE_DIR."/data/$files[$counter]")) {
			return true;
		} else {
			error_log('DB: failed to delete old backup file '.BASE_DIR.'/data/'.$files[$counter]);
			return false;
		}
		$counter--;
	}
}
/**
 * Function to re-init the database
 *
 * @return	bool						true if database successfully init
 */
function database_init($database) {
	// Remove the database file
	if(file_exists($database)) {
		unlink($database);
	}

	// Create a new database
	try {
		// if previous database deleted, I need to recreate a new connection
		$db = new PDO('sqlite:'.$database);
		$db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = 'CREATE TABLE bins (bin_filename TEXT PRIMARY KEY, bin_name TEXT);';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$query = 'CREATE TABLE configs (cfg_id INTEGER PRIMARY KEY, cfg_name TEXT, cfg_config, folder_id INTEGER);';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$query = 'CREATE TABLE devices (dev_id INTEGER, lab_id INTEGER, dev_name TEXT, bin_name TEXT, dev_ram INTEGER, dev_nvram INTEGER, dev_ethernet INTEGER, dev_serial INTEGER, dev_picture TEXT, dev_delay INTEGER DEFAULT 0, cfg_id INTEGER, dev_top INTEGER, dev_left INTEGER, dev_l2keepalive BOOL DEFAULT 0, dev_watchdog BOOL DEFAULT 1, PRIMARY KEY (dev_id, lab_id));';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$query = 'CREATE TABLE folders (folder_id INTEGER PRIMARY KEY, folder_name TEXT, parent_id INTEGER);';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$query = 'CREATE TABLE images (img_id INTEGER PRIMARY KEY, img_name TEXT, img_info TEXT, img_content BLOB, folder_id INTEGER, img_map TEXT);';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$query = 'CREATE TABLE labs (lab_id INTEGER PRIMARY KEY, lab_name TEXT, lab_description TEXT, lab_info TEXT, lab_netmap TEXT, folder_id INTEGER, lab_diagram BOOL DEFAULT 1, lab_time INTEGER DEFAULT 0, lab_points INTEGER DEFAULT 0);';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$query = 'CREATE TABLE rel_img_lab (img_id INTEGER, lab_id INTEGER, PRIMARY KEY (img_id, lab_id));';
		$statement = $db -> prepare($query);
		$statement -> execute();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot create the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to optimizie the database
 * 
 * @return	bool						true if successfully optimized
 */
function database_optimize($database) {
	$db = new PDO('sqlite:'.$database);
	$db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	try {
		// Compressing lab_ids
		$new_lab_id = 1;
		$query = 'SELECT lab_id FROM labs ORDER BY lab_id ASC;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		try {
			while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
				$old_lab_id = $result['lab_id'];
				if ($old_lab_id > $new_lab_id) {
					// There are unused lab_ids, must update: rel_img_lab, devices, labs
					$db -> beginTransaction();
					$query_nested = 'UPDATE rel_img_lab SET lab_id=:new_lab_id WHERE lab_id=:old_lab_id;';
					$statement_nested = $db -> prepare($query_nested);
					$statement_nested -> bindParam(':new_lab_id', $new_lab_id, PDO::PARAM_INT);
					$statement_nested -> bindParam(':old_lab_id', $old_lab_id, PDO::PARAM_INT);
					$statement_nested -> execute();
					$query_nested = 'UPDATE devices SET lab_id=:new_lab_id WHERE lab_id=:old_lab_id;';
					$statement_nested = $db -> prepare($query_nested);
					$statement_nested -> bindParam(':new_lab_id', $new_lab_id, PDO::PARAM_INT);
					$statement_nested -> bindParam(':old_lab_id', $old_lab_id, PDO::PARAM_INT);
					$statement_nested -> execute();				
					$query_nested = 'UPDATE labs SET lab_id=:new_lab_id WHERE lab_id=:old_lab_id;';
					$statement_nested = $db -> prepare($query_nested);
					$statement_nested -> bindParam(':new_lab_id', $new_lab_id, PDO::PARAM_INT);
					$statement_nested -> bindParam(':old_lab_id', $old_lab_id, PDO::PARAM_INT);
					$statement_nested -> execute();
					$db -> commit();
				}
				$new_lab_id++;
			}
		} catch (PDOException $e) {
			error_log('DB: cannot optimize the DB with error "'.$e->getMessage().'" (query was "'.$query_nested.'".');
			return false;
		}
	} catch (PDOException $e) {
		error_log('DB: cannot optimize the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
	try {
		// Compressing cfg_ids
		$new_cfg_id = 1;
		$query = 'SELECT cfg_id FROM configs ORDER BY cfg_id ASC';
		$statement = $db -> prepare($query);
		$statement -> execute();
		try {
			while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
				$old_cfg_id = $result['cfg_id'];
				if ($old_cfg_id > $new_cfg_id) {
					// There are unused cfg_ids, must update: devices, configs
					$db -> beginTransaction();
					$query_nested = 'UPDATE devices SET cfg_id=:new_cfg_id WHERE cfg_id=:old_cfg_id;';
					$statement_nested = $db -> prepare($query_nested);
					$statement_nested -> bindParam(':new_cfg_id', $new_cfg_id, PDO::PARAM_INT);
					$statement_nested -> bindParam(':old_cfg_id', $old_cfg_id, PDO::PARAM_INT);
					$statement_nested -> execute();
					$query_nested = 'UPDATE configs SET cfg_id=:new_cfg_id WHERE cfg_id=:old_cfg_id;';
					$statement_nested = $db -> prepare($query_nested);
					$statement_nested -> bindParam(':new_cfg_id', $new_cfg_id, PDO::PARAM_INT);
					$statement_nested -> bindParam(':old_cfg_id', $old_cfg_id, PDO::PARAM_INT);
					$statement_nested -> execute();
					$db -> commit();
				}
				$new_cfg_id++;
			}
		} catch (PDOException $e) {
			error_log('DB: cannot optimize the DB with error "'.$e->getMessage().'" (query was "'.$query_nested.'".');
			return false;
		}
	} catch (PDOException $e) {
		error_log('DB: cannot optimize the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
	try {
		// Compressing images_ids
		$new_img_id = 1;
		$query = 'SELECT img_id FROM images ORDER BY img_id ASC';
		$statement = $db -> prepare($query);
		$statement -> execute();
		try {
			while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
				$old_img_id = $result['img_id'];
				if ($old_img_id > $new_img_id) {
					// There are unused img_ids, must update: rel_img_lab, images
					$db -> beginTransaction();
					$query_nested = 'UPDATE rel_img_lab SET img_id=:new_img_id WHERE img_id=:old_img_id;';
					$statement_nested = $db -> prepare($query_nested);
					$statement_nested -> bindParam(':new_img_id', $new_img_id, PDO::PARAM_INT);
					$statement_nested -> bindParam(':old_img_id', $old_img_id, PDO::PARAM_INT);
					$statement_nested -> execute();
					$query_nested = 'UPDATE images SET img_id=:new_img_id WHERE img_id=:old_img_id;';
					$statement_nested = $db -> prepare($query_nested);
					$statement_nested -> bindParam(':new_img_id', $new_img_id, PDO::PARAM_INT);
					$statement_nested -> bindParam(':old_img_id', $old_img_id, PDO::PARAM_INT);
					$statement_nested -> execute();
					$db -> commit();
				}
				$new_img_id++;
			}
		} catch (PDOException $e) {
			error_log('DB: cannot optimize the DB with error "'.$e->getMessage().'" (query was "'.$query_nested.'".');
			return false;
		}
	} catch (PDOException $e) {
		error_log('DB: cannot optimize the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
	try {
		// Compressing database
		$query = 'VACUUM;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot compress the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to update a database
 *
 * @return	bool						true if database successfully updated
 */
function database_update($database) {
	$db = new PDO('sqlite:'.$database);
	$db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	try {
		$query = 'ALTER TABLE devices ADD COLUMN dev_delay INTEGER DEFAULT 0;';
		$statement = $db -> prepare($query);
		$statement -> execute();
	} catch (Exception $e) {
		// Do nothing
	}
	try {
		$query = 'ALTER TABLE images ADD COLUMN img_map TEXT;';
		$statement = $db -> prepare($query);
		$statement -> execute();
	} catch (Exception $e) {
		// Do nothing
	}
	try {
		$query = 'ALTER TABLE labs ADD COLUMN lab_diagram BOOL DEFAULT 1;';
		$statement = $db -> prepare($query);
		$statement -> execute();
	} catch (Exception $e) {
		// Do nothing
	}
	try {
		$query = 'ALTER TABLE labs ADD COLUMN lab_points INTEGER DEFAULT 0;';
		$statement = $db -> prepare($query);
		$statement -> execute();
	} catch (Exception $e) {
		// Do nothing
	}
	try {
		$query = 'ALTER TABLE labs ADD COLUMN lab_time INTEGER DEFAULT 0;';
		$statement = $db -> prepare($query);
		$statement -> execute();
	} catch (Exception $e) {
		// Do nothing
	}
}
/**
 * Delete a file
 *
 * @param	string		$file_name	Filename of the IOS to be deleted
 * @return	bool					True if successfully deleted
 */
function file_delete($file_name) {
	// Delete the file if exist
	if (file_exists(BASE_BIN.'/'.$file_name) && !unlink(BASE_BIN.'/'.$file_name)) {
		 error_log('FILE: cannot delete the file'.BASE_BIN."/".$file_name.'.');
		 return false;
	}
	try {
		global $db;
		$query = "DELETE FROM bins WHERE bin_filename=:file_name;";
		$statement = $db -> prepare($query);
		$statement -> bindParam(':file_name', $file_name, PDO::PARAM_STR);
		$statement -> execute();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot delete file with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to upload file to bin directory
 *
 * @param	int		$ios_file			Filename of the uploaded IOS
 * @param	string	$ios_name			Filename stored under bin
 * @param	string	$ios_alias			Alias of the uploaded IOS (used in labs)
 * @param	string	$ios_error			Alias of the uploaded IOS (used in labs)
 * @return	bool						true if successfully uploaded
 */
function file_upload($ios_file, $ios_name, $ios_alias, $ios_error) {
	// Check the uploaded file
	if (!isset($ios_file) || $ios_file == '') {
		$upload_errors = array( 
			UPLOAD_ERR_OK => 'No errors', 
			UPLOAD_ERR_INI_SIZE => 'Larger than upload_max_filesize', 
			UPLOAD_ERR_FORM_SIZE => 'Larger than form MAX_FILE_SIZE', 
			UPLOAD_ERR_PARTIAL => 'Partial upload', 
			UPLOAD_ERR_NO_FILE => 'No file', 
			UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory', 
			UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk', 
			UPLOAD_ERR_EXTENSION => 'File upload stopped by extension', 
			UPLOAD_ERR_EMPTY => 'File is empty.' // add this to avoid an offset 
		);
		error_log('FILE: upload failed with error '.$upload_errors[$ios_error].'.');
		return false;
	}
	
	// Check if a file already exists
	if (file_exists(BASE_BIN.'/'.$ios_name)) {
		error_log('FILE: '.BASE_BIN.'/'.$ios_name.' already exists, deleting it.');
		try {
			unlink(BASE_BIN.'/'.$ios_name);
		} catch (Exception $e) {
			error_log('FILE: cannot delete '.BASE_BIN.'/'.$ios_name.' with error "'.$e->getMessage().'".');
			return false;
		}
	}
	// Try to store under bin
	if (!rename($ios_file, BASE_BIN.'/'.$ios_name)) {
		error_log('FILE: cannt move '.$ios_file.' to '.BASE_BIN.'/'.$ios_name.' Check directory permission.');
		return false;
	}
	
	// Adjust permissions
	if (!chmod(BASE_BIN.'/'.$ios_name, 0755)) {
		error_log('FILE: cannot change '.BASE_BIN.'/'.$ios_name.' permission.');
		return false;
	}
	
	// Updating the DB
	try {
		global $db;
		$statement = $db -> prepare('INSERT OR REPLACE INTO bins (bin_filename, bin_name) VALUES (:name, :alias);');
		$statement -> bindParam(':name', $ios_name, PDO::PARAM_STR);
		$statement -> bindParam(':alias', $ios_alias, PDO::PARAM_STR);
		$statement -> execute();
		return true;
	} catch(PDOException $e) {
		error_log('DB: cannot update the DB with error "'.$e->getMessage().'".');
		unlink(BASE_BIN."/$ios_name");
		return;
	}
}
/**
 * Function to add a folder
 *
 * @param	string	$folder_name		name of the folder
 * @param	int		$parent_id			ID of the parent folder
 * @return	bool						True if successfully moved
 */
function folder_add($folder_name, $parent_id) {
	try {
		global $db;
		$statement = $db -> prepare("INSERT INTO folders (folder_name, parent_id) VALUES (:folder_name, :parent_id)");
		$statement -> bindParam(':folder_name', $folder_name, PDO::PARAM_STR);
		$statement -> bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
		$statement -> execute();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot add folder with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
 /**
 * Function to delete a folder and move the content into the parent folder TODO
 *
 * @param	int		$id					ID of the folder
 * @return	bool						True if successfully deleted
 */
function folder_delete($folder_id, $parent_id) {
	try {
		global $db;
		$db -> beginTransaction();
		//Move folder
		$query = 'UPDATE folders SET parent_id=:parent_id WHERE parent_id=:folder_id';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
		$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
		$statement -> execute();
		//Move Labs
		$query = 'UPDATE labs SET folder_id=:parent_id WHERE folder_id=:folder_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
		$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
		$statement -> execute();
		//Move Configs
		$query = 'UPDATE configs SET folder_id=:parent_id WHERE folder_id=:folder_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
		$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
		$statement -> execute();
		//Delete the folder
		$query = 'DELETE from folders WHERE folder_id=:folder_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
		$statement -> execute();
		$db -> commit();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot delete folder with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to move an object (folder, cfg, lab, img) inside a folder
 *
 * @param	string	$name				name of the object
 * @param	string	$dst				destination folder in the form folderX, where X is the destination folder_id
 * @return	bool						True if successfully moved
 */
function folder_move($name, $dst) {
	$dst_folder_id = preg_replace('/[^0-9]/s', '', $dst);
	global $db;
	
	if (substr($name, 0, 3) == 'cfg') {
		//Move Config
		$cfg_pack = substr($name, 3);
		try {
			$query = "UPDATE configs SET folder_id=:folder_id WHERE cfg_name=:cfg_pack OR cfg_name LIKE '".$cfg_pack." - %';";
			$statement = $db -> prepare($query);
			$statement -> bindParam(':folder_id', $dst_folder_id, PDO::PARAM_INT);
			$statement -> bindParam(':cfg_pack', $cfg_pack, PDO::PARAM_STR);
			$statement -> execute();
			return true;
		} catch (PDOException $e) {
			error_log('DB: cannot move config to folder with error "'.$e->getMessage().'" (query was "'.$query.'".');
			return false;
		}
	} else {
		if(strstr($name, 'folder')) {
			//Move folder
			$folder_id = preg_replace('/[^0-9]/s', '', $name);
			try {
				$query = 'UPDATE folders SET parent_id=:parent_id WHERE folder_id=:folder_id;';
				$statement = $db -> prepare($query);
				$statement -> bindParam(':parent_id', $dst_folder_id, PDO::PARAM_INT);
				$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
				$statement -> execute();
				return true;
			} catch (PDOException $e) {
				error_log('DB: cannot move folder to folder with error "'.$e->getMessage().'" (query was "'.$query.'".');
				return false;
			}
		} else if(strstr($name, 'lab')) {
			//Move Labs
			$lab_id = preg_replace('/[^0-9]/s', '', $name);
			try {
				$query = 'UPDATE labs SET folder_id=:folder_id WHERE lab_id=:lab_id;';
				$statement = $db -> prepare($query);
				$statement -> bindParam(':folder_id', $dst_folder_id, PDO::PARAM_INT);
				$statement -> bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
				$statement -> execute();
				return true;
			} catch (PDOException $e) {
				error_log('DB: cannot move lab to folder with error "'.$e->getMessage().'" (query was "'.$query.'".');
				return false;
			}
		} else if(strstr($name, 'img')) {
			//Move Images
			$img_id = preg_replace('/[^0-9]/s', '', $name);
			try {
				$query = 'UPDATE images SET folder_id=:folder_id WHERE img_id=:img_id;';
				$statement = $db -> prepare($query);
				$statement -> bindParam(':folder_id', $dst_folder_id, PDO::PARAM_INT);
				$statement -> bindParam(':img_id', $img_id, PDO::PARAM_INT);
				$statement -> execute();
				return true;
			} catch (PDOException $e) {
				error_log('DB: cannot move image to folder with error "'.$e->getMessage().'" (query was "'.$query.'".');
				return false;
			}
		} else {
			//Should not be here
			error_log('DB: cannot move unknown object.');
			return false;
		}
	}
}
/**
 * Function to return last cfg_id used
 *
 * @return	int							Last cfg_id found
 */
function getLastConfigId() {
	try {
		global $db;
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = 'SELECT MAX(cfg_id) AS last FROM configs;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$result = $statement -> fetch();
		if (is_numeric($result['last'])) {
			return $result['last'];
		} else {
			// No config found, return 0
			return 0;
		}
		return $result['last'];
	} catch (PDOException $e) {
		error_log('DB: cannot select last cfg_id with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to return last img_id used
 *
 * @return	int							Last img_id found
 */
function getLastImageId() {
	try {
		global $db;
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = 'SELECT MAX(img_id) AS last FROM images;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$result = $statement -> fetch();
		if (is_numeric($result['last'])) {
			return $result['last'];
		} else {
			// No image found, return 0
			return 0;
		}
	} catch (PDOException $e) {
		error_log('DB: cannot select last img_id with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to return last lab_id used
 *
 * @return	int							Last lab_id found
 */
function getLastLabId() {
	try {
		global $db;
		$db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = 'SELECT MAX(lab_id) AS last FROM labs;';
		$statement = $db -> prepare($query);
		$statement -> execute();
		$result = $statement -> fetch();
		if (is_numeric($result['last'])) {
			return $result['last'];
		} else {
			// No lab found, return 0
			return 0;
		}
	} catch (PDOException $e) {
		error_log('DB: cannot select last lab_id with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Export labs, configs and devices
 *
 * @param	array	$labs			List of labs to be exported
 * @param	array	$configs		List of configs to be exported
 * @param	string	$file_export	File where export data
 * @return	bool					True if successfully exported
 */
function export($labs, $configs, $file_export) {
	$file_tmp = '/tmp/iou-web-export.db';
	// Delete destination file if exists
	if (file_exists($file_export) && !unlink($file_export)) {
		error_log('DB: cannot delete the database file'.$file_export.'.');
		return false;
	}
	// Create a database
	if (!database_init($file_tmp)) {
		error_log('DB: cannot reinitializate database file'.$file_tmp.'.');
		return false;
	}
		
	try {
		global $db;
		// Attach the temporary database
		$query = "ATTACH '".$file_tmp."' AS export_db;";
		$statement = $db -> prepare($query);
		$statement -> execute();
		
		$db -> beginTransaction();
		// Current version is stored
		$query = "CREATE TABLE export_db.data (data_name TEXT, data_value TEXT)";
		$db -> query($query);
		$query = "INSERT INTO export_db.data (data_name, data_value) VALUES ('db_version', '".VERSION."')";
		$db -> query($query);
		
		// Export data
		if ($configs != false) {
			$query = "INSERT INTO export_db.configs (cfg_id, cfg_name, cfg_config) SELECT cfg_id, cfg_name, cfg_config FROM main.configs WHERE cfg_name LIKE '".implode(" - %' OR cfg_name LIKE '", $configs)." - %' ORDER BY cfg_id";
			$db -> query($query);
		}
		if ($labs != false) {
			$query = "INSERT INTO export_db.devices (dev_id, lab_id, dev_name, bin_name, dev_ram, dev_nvram, dev_ethernet, dev_serial, dev_picture, cfg_id, dev_top, dev_left, dev_l2keepalive, dev_watchdog) SELECT dev_id, lab_id, dev_name, bin_name, dev_ram, dev_nvram, dev_ethernet, dev_serial, dev_picture, cfg_id, dev_top, dev_left, dev_l2keepalive, dev_watchdog FROM devices WHERE lab_id=".implode(" OR lab_id=", $labs)." ORDER BY lab_id";
			$db -> query($query);
			$query = "INSERT INTO export_db.labs (lab_id, lab_name, lab_description, lab_info, lab_netmap, lab_diagram, lab_time, lab_points) SELECT lab_id, lab_name, lab_description, lab_info, lab_netmap, lab_diagram, lab_time, lab_points FROM labs WHERE lab_id=".implode(" OR lab_id=", $labs)." ORDER BY lab_id";
			$db -> query($query);
			$query = "INSERT INTO export_db.rel_img_lab (img_id, lab_id) SELECT img_id, lab_id FROM main.rel_img_lab WHERE main.rel_img_lab.lab_id=".implode(" OR main.rel_img_lab.lab_id=", $labs).";";
			$db -> query($query);
			// INSERT OR REPLACE used because multiple labs can be linked to the same image (Integrity constraint violation: 19 PRIMARY KEY must be unique")
			$query = "INSERT OR REPLACE INTO export_db.images (img_id, img_name, img_info, img_content, img_map) SELECT DISTINCT img_id, img_name, img_info, img_content, img_map FROM main.images NATURAL JOIN main.rel_img_lab WHERE main.rel_img_lab.lab_id=".implode(" OR main.rel_img_lab.lab_id=", $labs).";";
			$db -> query($query);
		}
		$db -> commit();
		
		$query = "DETACH 'export_db';";	
		$statement = $db -> prepare($query);
		$statement -> execute();
	} catch (PDOException $e) {
		error_log('DB: cannot export data with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
	
	// Now optimize the DB

	if (!database_optimize($file_tmp)) {
		return false;
	}
	
	$exported_db = new PDO('sqlite:'.$file_tmp);
	$exported_db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	try {
		$fp = gzopen($file_export, 'w9');
		gzwrite($fp, file_get_contents($file_tmp));
		gzclose($fp);
		return true;
	} catch (Exception  $e){
		error_log('DB: cannot compress the database with error "'.$e->getMessage().'".');
		return false;
	}
}
/**
 * Function to add an image
 *
 * @param	int			$img_id		Id of the img to delete
 * @return	bool					True if successfully deleted
 */
function img_add($img_name, $img_info, $img_content, $folder_id) {
	try {
		global $db;
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = 'INSERT INTO images (img_name, img_info, img_content, folder_id) VALUES (:img_name, :img_info, :img_content, :folder_id);';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':img_name', $img_name, PDO::PARAM_STR);
		$statement -> bindParam(':img_info', $img_info, PDO::PARAM_STR);
		$statement -> bindParam(':img_content', $img_content);
		$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
		$statement -> execute();
		return true;
	} catch(PDOException $e) {
		error_log('DB: cannot update the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to delete an image
 *
 * @param	int		$img_id			Id of the img to delete
 * @return	bool					True if successfully deleted
 */
function img_delete($img_id) {
	try {
		global $db;
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// Delete the image
		$query = 'DELETE FROM images WHERE img_id=:img_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':img_id', $img_id, PDO::PARAM_INT);
		$statement -> execute();
		// Delete the lab - img relationship
		$query = 'DELETE FROM rel_img_lab WHERE img_id=:img_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':img_id', $img_id, PDO::PARAM_INT);
		$statement -> execute();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot delete image with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to update an image
 *
 * @param	int		$img_id			Id of the img to delete
 * @param	string	$img_name		File where export data
 * @param	string	$img_info		File where export data
 * @param	string	$img_map		File where export data
 * @return	bool					True if successfully deleted
 */
function img_update($img_id, $img_name, $img_info, $img_map) {
	try {
		global $db;
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = 'UPDATE images SET img_name=:img_name, img_info=:img_info, img_map=:img_map WHERE img_id=:img_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':img_id', $img_id, PDO::PARAM_INT);
		$statement -> bindParam(':img_name', $img_name, PDO::PARAM_STR);
		$statement -> bindParam(':img_info', $img_info, PDO::PARAM_STR);
		$statement -> bindParam(':img_map', $img_map, PDO::PARAM_STR);
		$statement -> execute();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot delete image with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to print an image
 *
 * @param	int		$img_id			Id of the img to delete
 * @return	string					The image
 */
function img_print($img_id) {
	try {
		global $db;
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// Print the image
		$query = 'SELECT img_content FROM images WHERE img_id=:img_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':img_id', $img_id, PDO::PARAM_INT);
		$statement -> execute();
		$result = $statement -> fetch();
		return $result['img_content'];
	} catch (PDOException $e) {
		error_log('DB: cannot delete image with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
 /**
 * Function to check if current user is an admin. Under this version all users
 * are admins.
 *
 * @return	bool					True if current user is an admin
 */
function is_admin() {
        return ADMIN;
}
/**
 * Function to add a lab
 *
 * @param	string	$name			Name of the lab
 * @param	string	$description	Description of the lab
 * @param	string	$info			Additional info of the lab
 * @param	string	$netmap			NETMAP of the lab
 * @return	int						The ID of the last inserted lab
 */
function lab_add($name, $description, $info, $netmap) {
	try {
		global $db;
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$statement = $db -> prepare("INSERT INTO labs (name, description, info, netmap) VALUES (:name, :description, :info, :netmap);");
		$statement -> bindParam(':name', $name, PDO::PARAM_STR);
		$statement -> bindParam(':description', $description, PDO::PARAM_STR);
		$statement -> bindParam(':info', $info, PDO::PARAM_STR);
		$statement -> bindParam(':netmap', $netmap, PDO::PARAM_STR);
		$statement -> execute();
		return $db -> lastInsertId();
	
	} catch(PDOException $e) {
		msg_error("Cannot query the DB: ".$e->getMessage());
		return;
	}
}
/**
 * Function to delete a lab
 *
 * @param	int		$lab_id			Id of the lab to delete
 * @return	bool					True if successfully deleted
 */
function lab_delete($lab_id) {
	try {
		global $db;
		// Delete the lab
		$query = 'DELETE FROM labs WHERE lab_id=:lab_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
		$statement -> execute();
		// Delete the devices
		$query = 'DELETE FROM devices WHERE lab_id=:lab_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
		$statement -> execute();
		// Delete image references
		$query = 'DELETE FROM rel_img_lab WHERE lab_id=:lab_id;';
		$statement = $db -> prepare($query);
		$statement -> bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
		$statement -> execute();
		return true;
	} catch (PDOException $e) {
		error_log('DB: cannot delete lab with error "'.$e->getMessage().'" (query was "'.$query.'".');
		return false;
	}
}
/**
 * Function to print html footer
 *
 * @return	void
 */
function page_footer() {
?>
<div class='block'>
	<div align='center' id='footer' class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>iou-web-<?php print VERSION ?><?php if (CHECK_UPDATE == true) { ?> - latest official is <iframe id="current" src="http://public.routereflector.com/iou-web/latest"></iframe><?php } ?></div>
</div>
</body>
</html>
<?php
}
/**
 * Function to print html header
 *
 * @param	string		$title		Title of the page/tab
 * @return	void
 */
function page_header($title) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>
<head>
	<title>IOU Web Interface - <?php print $title ?></title>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<meta name='keywords' content='IOU, IOL, Web Interface, CCIE' />
	<meta name='description' content='IOU Web Interface gives you the flexibility you need to use Cisco IOU.' />
	<meta http-equiv='Content-Language' content='en' />
	<script type='text/javascript'>
		_editor_url  = '<?php print BASE_WWW ?>/xinha/'   // (preferably absolute) URL (including trailing slash) where Xinha is installed
		_editor_lang = 'en';       // And the language we need to use in the editor.
		_editor_skin = 'silva';    // If you want use a skin, add the name (of the folder) here
		_editor_icons = 'Classic'; // If you want to use a different iconset, add the name (of the folder, under the `iconsets` folder) here
	</script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/xinha/XinhaCore.js'></script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/xinha/XinhaConfig.js'></script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/js/jquery-1.8.2.min.js'></script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/js/jquery-ui-1.8.24.custom.min.js'></script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/js/jquery.contextmenu.js'></script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/js/jquery.validate.js'></script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/js/iou-web.js'></script>
	<script type='text/javascript' src='<?php print BASE_WWW ?>/js/jquery.countdown.js'></script>
	<link rel='stylesheet' type='text/css' href='<?php print BASE_WWW ?>/css/black-tie/jquery-ui-1.8.24.custom.css' />
	<link rel='stylesheet' type='text/css' href='<?php print BASE_WWW ?>/css/jquery.contextmenu.css' />
	<link rel='stylesheet' type='text/css' href='<?php print BASE_WWW ?>/css/jquery.countdown.css' />
	<link rel='stylesheet' type='text/css' href='<?php print BASE_WWW ?>/css/iou-web.css' />
</head>
<body>
<div id="dialog"></div>
<?php
	if ($_SESSION['session_check_update']) {
		$_SESSION['session_check_update'] = false;
?>
<script type='text/javascript'><!--
	$(document).ready(function() {
		checkUpdate('<?php print VERSION ?>');
	});
--></script>
<?php
	}
?>
<div id='menu' class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
	<ul>
		<li class="<?php if($title == 'Laboratories') print " selected" ?>">
			<a href='<?php print BASE_WWW ?>/laboratories.php'>Laboratories</a>
		</li>
		<li class="<?php if($title == 'Manage') print " selected" ?>">
			<a href='<?php print BASE_WWW ?>/manage.php'>Manage</a>
		</li>
<!-- TODO
		<li class="<?php if($title == 'Monitor') print " selected" ?>">
			<a href='<?php print BASE_WWW ?>/monitor.php'>Monitor</a>
		</li>
-->
		<li class="<?php if($title == 'Downloads') print " selected" ?>">
			<a href='<?php print BASE_WWW ?>/downloads/'>Downloads</a>
		</li>
	</ul>
</div>
<?php
}
/**
 * Function to print an error msg using a xml page
 *
 * @param	string	$status				Type of the error (Informational, Error)
 * @param	string	$message			String to print
 * @return	void
 */
function xml_message($prefix, $status, $message) {
	header('Cache-Control: no-cache, must-revalidate');	// HTTP/1.1
	header('Expires: Thu, 12 Oct 1979 15:10:00 CET');	// Date in the past
	header('HTTP/1.1 200 OK');							// OK
	print "<?xml version='1.0' encoding='UTF-8'?>\n";
?>
<msg>
	<status><?php print $status ?></status>
	<message><?php print $message ?></message>
</msg>
<?php
	if ($status != 'Informational') {
		// If error print into the error_log also
		error_log('AJAX: '.$status.' - '.$message);
	}
}
?>
