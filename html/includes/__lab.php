<?php
/**
 * Class for iou-web labs
 * 
 * This class define the following methods:
 * - __construct: a constructor to create a lab
 * - isSnifferRunning: a method to check if the sniffer is running
 * - load: a method to load all devices in a lab
 * - netmapDump: dump netmap to a file
 * - netmapSet: set one array with unique device ids and another one with unique hub ids
 * - save: a method to save lab, devices and image
 * - snifferStart: a method to start the sniffer
 * - snifferStop: a method to stop the sniffer
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */
 
class Lab {
	static $id;
	public $folder_id;
	public $netmap_ids = array();
	public $netmap_hubs = array();
	public $name;
	public $description;
	public $info;
	public $netmap;
	public $time;
	public $points;
	public $devices = array();
	public $images = array();
	public $diagram;

    /**
     * Constructor which load an existent lab (devices are not loaded)
     * 
     * @param	bool	$get_from_db		true if data are get from the DB, false if data are get from parameter 
     * @param	int		$lab_id				the lab_id of the lab
     * @param	string	$lab_name			the name of the lab
     * @param	string	$lab_description	the description of the lab
     * @param	int		$folder_id			the folder_id of the lab
     * @return	void
     */
	public function __construct($get_from_db, $lab_id, $lab_name, $lab_description, $folder_id) {
		if ($get_from_db) {
			try {
				global $db;
				// Getting data from DB
				$query = 'SELECT lab_name, lab_description, lab_info, lab_netmap, folder_id, lab_diagram, lab_time, lab_points FROM labs WHERE lab_id=:lab_id;';
				$statement = $db -> prepare($query);
				$statement -> bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
				$statement -> execute();
				$result = $statement -> fetch();

				// Setting data to this object
				$this -> id = $lab_id;
				$this -> name = $result['lab_name'];
				$this -> description = $result['lab_description'];
				$this -> info = $result['lab_info'];
				$this -> netmap = $result['lab_netmap'];
				$this -> folder_id = $result['folder_id'];
				$this -> diagram = $result['lab_diagram'];
				$this -> time = $result['lab_time'];
				$this -> points = $result['lab_points'];				
			} catch(PDOException $e) {
				error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
			}
		} else {
			$this -> id = $lab_id;
			$this -> name = $lab_name;
			$this -> description = $lab_description;
			$this -> folder_id = $folder_id;
		}
	}
    /**
     * Check if the sniffer is running
     * 
     * @return	bool											true if is running
     */
	public function isSnifferRunning() {
		$command = 'pgrep iousniff';
		try {
			exec($command, $output, $pid);
			if ($pid == 0) {
				return true;
			} else {
				return false;
			}
		} catch (Exception $e) {
			error_log('EXEC: failed to exec "'.$command.'".');
			return false;
		}
	}
    /**
     * Load all devices in a lab
     * 
     * @return	void
     */
	public function load() {
		// Before loading, need to set/dump NETMAP
		$this -> netmapSet();
		$this -> netmapDump();
		
		// Unset device array, if set
		$this -> devices = array();
		
		try {
			global $db;
			// Getting devices from NETMAP
			foreach ($this -> netmap_ids as $netmap_id) {
				// Putting devices into an array
				$this -> devices[$netmap_id] = new Device($this -> id, $netmap_id);
				$this -> devices[$netmap_id] -> lab_name = $this -> name;   // Needed in export config
				$this -> devices[$netmap_id] -> folder_id = $this -> folder_id; // Needed in export config
			}
			foreach ($this -> netmap_hubs as $netmap_id) {
				// Putting hubs into an array
				$this -> devices[$netmap_id] = new Device($this -> id, $netmap_id);
			}

			// Getting images data from DB
			$query = 'SELECT img_id FROM rel_img_lab WHERE lab_id=:lab_id;';
			$statement = $db -> prepare($query);
			$statement -> bindParam(':lab_id', $this -> id, PDO::PARAM_INT);
			$statement -> execute();
			
			// Putting images into an array
			while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
				$this -> images[$result['img_id']] = new Image(true, $result['img_id'], '', '');
			}
		} catch(PDOException $e) {
			error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		}
    }
	
    /**
     * Dump NETMAP to a file under /tmp/iou/lab_id
     * 
	 * @return	bool					True if successfully created NETMAP file
     */
	public function netmapDump() {	
			// Preparing folders
			if (!file_exists('/tmp/iou') && !mkdir('/tmp/iou')) {
				error_log('FILE: cannot create dir /tmp/iou.');
				return false;
			}
			chmod('/tmp/iou', 17901);
			//error_log(fileperms('/tmp/iou'));
			if (!file_exists('/tmp/iou/lab_'.$this -> id) && !mkdir('/tmp/iou/lab_'.$this -> id)) {
				error_log('FILE: cannot create dir /tmp/iou/lab_'.$this -> id.'.');
				return false;
			}
			// Remove old NETMAP, if exists
			$netmap_file = '/tmp/iou/lab_'.$this -> id.'/NETMAP';
			if(file_exists($netmap_file) && !unlink($netmap_file)) {
				error_log('FILE: cannot delete the file'.$netmap_file.'.');
				return false;
			}
			// Exporting to a file
			try {
				//See netmap.js.php also
				$cleaned_netmap = preg_replace('/(#.*)/', '', $this -> netmap);									// Remove comments
				$cleaned_netmap = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n\']+/", "\n", $cleaned_netmap);	// Remove empty lines
				$cleaned_netmap = preg_replace("/[\s]+\n/", "\n", $cleaned_netmap);								// Remove trailing spaces (trim lines)
				$cleaned_netmap = trim($cleaned_netmap);														// Remove trailing spaces (trim all)
				$cleaned_netmap = $cleaned_netmap."\n";															// Adding and end of line for ioulive86
				$fp = fopen($netmap_file, 'w');
				fwrite($fp, $cleaned_netmap);
				fclose($fp);
				// dos2unix needed for iousniff
				$command = 'dos2unix '.$netmap_file;
				exec($command, $output, $pid);
				return true;
			} catch (Exception  $e){
				error_log('FILE: cannot create NETMAP ('.$netmap_file.') with error "'.$e->getMessage().'".');
				return false;
			}
	 }
	 
    /**
     * Set an array with all device ids and another one with all hub ids.
     * 
     * @return	void
     */
	public function netmapSet() {
		// putting all device IDs in an array
        $tmp_netmap = preg_replace('/ [0-9]+\r/', '', $this -> netmap); // Filtering encapuslation on each line
        $tmp_netmap = preg_replace('/ [0-9]+\z/', '', $tmp_netmap);     // Filtering encapuslation on last line
		$tok = strtok($tmp_netmap, " :\n");
		$netmap_ids_index = 0;
		while ($tok !== false) {
			if (is_numeric($tok)) {
				$this -> netmap_ids[$netmap_ids_index++] = $tok;
			}
			$tok = strtok(": \n");
		}
		sort($this -> netmap_ids);
		$this -> netmap_ids = array_unique($this -> netmap_ids);
		
		// putting all hub IDs in an array
		$netmap_array = explode("\n", $tmp_netmap);
		$netmap_hubs_index=0;
		$base_hub = BASE_HUB;
		
		// Per row analysis
		foreach ($netmap_array as $key => $value) {
				// How many devices?
				$tok = strtok($value, " ");
				$total = 0;
				while ($tok != false) {
						// Exclude line which doesn't starts with 0-9 (e.g comments)
						if (is_numeric(substr($tok, 0, 1))) {
								$total++;
						}
						$tok = strtok(" ");
				}
				if ($total > 2) {
						$this -> netmap_hubs[$netmap_hubs_index] = $base_hub;
						$base_hub++;
						$netmap_hubs_index++;
				}
		}
	}

    /**
     * Save lab, devices and images
     * 
     * @return	void
     */
	public function save() {
		try {
			global $db;
			// Saving lab to the DB
			$query = 'INSERT OR REPLACE INTO labs (lab_id, lab_name, lab_description, lab_info, lab_netmap, folder_id, lab_diagram, lab_time, lab_points) VALUES (:lab_id, :lab_name, :lab_description, :lab_info, :lab_netmap, :folder_id, :lab_diagram, :lab_time, :lab_points);';
			$statement = $db -> prepare($query);
			$statement -> bindParam(':lab_id', $this -> id, PDO::PARAM_INT);
			$statement -> bindParam(':lab_name', $this -> name, PDO::PARAM_STR);
			$statement -> bindParam(':lab_description', $this -> description, PDO::PARAM_STR);
			$statement -> bindParam(':lab_info', $this -> info, PDO::PARAM_STR);
			$statement -> bindParam(':lab_netmap', $this -> netmap, PDO::PARAM_STR);
			$statement -> bindParam(':folder_id', $this -> folder_id, PDO::PARAM_INT);
			$statement -> bindParam(':lab_diagram', $this -> diagram, PDO::PARAM_BOOL);
			$statement -> bindParam(':lab_time', $this -> time, PDO::PARAM_BOOL);
			$statement -> bindParam(':lab_points', $this -> points, PDO::PARAM_BOOL);
			$statement -> execute();
			
			// Saving devices
			foreach ($this -> devices as $device) {
				$device -> save();
			}
			
			// Saving images: unlink all images
			$query = 'DELETE FROM rel_img_lab WHERE lab_id=:lab_id;';
			$statement = $db -> prepare($query);
			$statement -> bindParam(':lab_id', $this -> id, PDO::PARAM_INT);
			$statement -> execute();
			// Saving images: link proper images only
			foreach ($this -> images as $image) {
				$query = 'INSERT OR REPLACE INTO rel_img_lab (img_id, lab_id) VALUES (:img_id, :lab_id);';
				$statement = $db -> prepare($query);
				$statement -> bindParam(':img_id', $image -> id, PDO::PARAM_INT);
				$statement -> bindParam(':lab_id', $this -> id, PDO::PARAM_INT);
				$statement -> execute();
			}
			
			// After saving, need to reload (if there are added or removed devices)
			$this -> load();
		} catch(PDOException $e) {
			error_log('DB: cannot update the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		}
	 }
    /**
     * Start the sniffer
     * 
     * @return	void
     */
	 public function  snifferStart() {
	 	if ($this -> isSnifferRunning()) {
			return true;
		} else {
			$command = 'nohup sudo '.BASE_BIN.'/iousniff -n /tmp/iou/lab_'.$this -> id.'/NETMAP -s /opt/iou/data/Sniffer/ >> '.BASE_DIR.'/data/Logs/exec.txt 2>&1 &';
			try {
				exec($command, $output, $pid);
				return $this -> isSnifferRunning();
			} catch (Exception $e) {
				error_log('EXEC: failed to exec "'.$command.'".');
				return false;
			}
		}
	 }
    /**
     * Stop the sniffer
     * 
     * @return	void
     */
	 public function  snifferStop() {
	 	if (!$this -> isSnifferRunning()) {
			return true;
		} else {
			$command = 'sudo pkill iousniff';
			try {
				exec($command, $output, $pid);
				return !$this -> isSnifferRunning();
			} catch (Exception $e) {
				error_log('EXEC: failed to exec "'.$command.'".');
				return false;
			}
		}
	 }
}
?>
