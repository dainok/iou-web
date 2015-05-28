<?php
$db = new PDO('sqlite:/opt/iou/data/database.sdb');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (php_sapi_name() == 'cli') {
	// Convert to 1.2.2 database version
	try {
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$statement = $db -> prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='data';");
		$statement -> execute();
		$result = $statement -> fetch();		
		
		if ($result['name'] != 'data') {
			print "Need to update database...";
			$db -> beginTransaction();
			// Dropping tables
			$statement = $db -> prepare("DROP TABLE IF EXISTS configs_new;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE IF EXISTS devices_new;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE IF EXISTS labs_new;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE IF EXISTS bins_new;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE IF EXISTS folders_new;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE IF EXISTS images_new;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE IF EXISTS rel_img_lab_new;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE IF EXISTS data_new;");
			$statement -> execute();
			
			// Creating new tables
			$statement = $db -> prepare("CREATE TABLE configs_new (cfg_id INTEGER PRIMARY KEY, cfg_name TEXT, cfg_config, folder_id INTEGER);");
			$statement -> execute();
			$statement = $db -> prepare("CREATE TABLE devices_new (dev_id INTEGER, lab_id INTEGER, dev_name TEXT, bin_name TEXT, dev_ram INTEGER, dev_nvram INTEGER, dev_ethernet INTEGER, dev_serial INTEGER, dev_picture TEXT, dev_delay INTEGER DEFAULT 0, cfg_id INTEGER, dev_top INTEGER, dev_left INTEGER, dev_l2keepalive BOOL DEFAULT 0, dev_watchdog BOOL DEFAULT 1, PRIMARY KEY (dev_id, lab_id));");
			$statement -> execute();
			$statement = $db -> prepare("CREATE TABLE labs_new (lab_id INTEGER PRIMARY KEY, lab_name TEXT, lab_description TEXT, lab_info TEXT, lab_netmap TEXT, folder_id INTEGER, lab_diagram BOOL DEFAULT 1, lab_time INTEGER, lab_points INTEGER);");
			$statement -> execute();
			$statement = $db -> prepare("CREATE TABLE bins_new (bin_filename TEXT PRIMARY KEY, bin_name TEXT);");
			$statement -> execute();
			$statement = $db -> prepare("CREATE TABLE folders_new (folder_id INTEGER PRIMARY KEY, folder_name TEXT, parent_id INTEGER);");
			$statement -> execute();
			$statement = $db -> prepare("CREATE TABLE images_new (img_id INTEGER PRIMARY KEY, img_name TEXT, img_info TEXT, img_content BLOB, folder_id INTEGER, img_map TEXT);");
			$statement -> execute();
			$statement = $db -> prepare("CREATE TABLE rel_img_lab_new (img_id INTEGER, lab_id INTEGER, PRIMARY KEY (img_id, lab_id));");
			$statement -> execute();
			$statement = $db -> prepare("CREATE TABLE data_new (data_name TEXT, data_value TEXT);");
			$statement -> execute();
			
			// Migrating data
			$statement = $db -> prepare("INSERT INTO configs_new (cfg_id, cfg_name, cfg_config, folder_id) SELECT id, name, config, folder FROM configs;");
			$statement -> execute();
			$statement = $db -> prepare("INSERT INTO devices_new (dev_id, lab_id, dev_name, bin_name, dev_ram, dev_nvram, dev_ethernet, dev_serial, dev_picture, cfg_id, dev_top, dev_left, dev_l2keepalive, dev_watchdog) SELECT id, lab_id, name, ios, ram, nvram, ethernet, serial, picture, conf_id, top, left, l2keepalive, watchdog FROM devices;");
			$statement -> execute();
			$statement = $db -> prepare("INSERT INTO labs_new (lab_id, lab_name, lab_description, lab_info, lab_netmap, folder_id) SELECT id, name, description, info, netmap, folder FROM labs;");
			$statement -> execute();
			$statement = $db -> prepare("INSERT INTO bins_new (bin_filename, bin_name) SELECT name, alias FROM bins;");
			$statement -> execute();
			$statement = $db -> prepare("INSERT INTO folders_new (folder_id, folder_name, parent_id) SELECT id, name, parent_id FROM folders;");
			$statement -> execute();
			$statement = $db -> prepare("INSERT INTO images_new (img_id, img_name, img_info, img_content, folder_id) SELECT img_id, img_name, img_info, img_content, folder_id FROM images;");
			$statement -> execute();
			$statement = $db -> prepare("INSERT INTO rel_img_lab_new (img_id, lab_id) SELECT img_id, lab_id FROM rel_img_lab;");
			$statement -> execute();
			$statement = $db -> prepare("INSERT INTO data_new (data_name, data_value) VALUES ('db_version', '1.2.2-1');");
			$statement -> execute();
			
			// Renaming tables		
			$statement = $db -> prepare("DROP TABLE configs;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE configs_new RENAME TO configs;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE devices;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE devices_new RENAME TO devices;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE labs;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE labs_new RENAME TO labs;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE bins;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE bins_new RENAME TO bins;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE folders;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE folders_new RENAME TO folders;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE images;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE images_new RENAME TO images;");
			$statement -> execute();
			$statement = $db -> prepare("DROP TABLE rel_img_lab;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE rel_img_lab_new RENAME TO rel_img_lab;");
			$statement -> execute();
			$statement = $db -> prepare("ALTER TABLE data_new RENAME TO data;");
			$statement -> execute();
			$db -> commit();
			print " updated to version 1.2.2\n";
		}
	} catch(PDOException $e) {
		print " DB Error ".$e->getMessage();
		return;
	}	
} else {
	header('Location: /');
	exit();
}	
?>
