<?php
/**
 * Class for iou-web devices
 * 
 * This class define the following methods:
 * - __construct: a constructor to create a device
 * - clean: a method to delete all device configurations
 * - export: a method to copy running-config into the database
 * - isCloud: a method to check if a specific device is a cloud
 * - isEthernet: a method to check if a specific interface is Ethernet
 * - isRunning: a method to check if a device is running
 * - reset: a method to reset console
 * - save: a method to save device parameters
 * - snapshot: a method to make a snapshot of the device
 * - start: a method to start a device
 * - stop: a method to stop a device
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */
 
class Device {
	private $lab_id;
	public $folder_id;
	public $lab_name;
	private $files = array();
	static $id;
	public $name;
	public $bin_alias;
	public $bin_name;
	public $bin_filename;
	public $ram;
	public $nvram;
	public $ethernet;
	public $serial;
	public $picture;
	public $delay;
	public $cfg_id;
	public $cfg_name;
	public $config;
	public $top;
	public $left;
	public $l2keepalive;
	public $watchdog;
	public $console;

    /**
     * Constructor which load an existent device
     * 
     * @param	int		$lab_id				the lab_id of the device
     * @param	int		$dev_id				the dev_id of the device
     * @return	void
     */
	public function __construct($lab_id, $dev_id) {
		try {
			global $db;
			// Getting data from DB
			$query = 'SELECT devices.dev_name dev_name, devices.bin_name bin_name, bin_filename, dev_ram, dev_nvram, dev_ethernet, dev_serial, dev_picture, dev_delay, devices.cfg_id cfg_id, cfg_name, cfg_config, dev_top, dev_left, dev_l2keepalive, dev_watchdog FROM devices LEFT JOIN bins ON devices.bin_name=bins.bin_name LEFT JOIN configs ON devices.cfg_id=configs.cfg_id WHERE dev_id=:dev_id AND lab_id=:lab_id;';
			$statement = $db -> prepare($query);
			$statement -> bindParam(':dev_id', $dev_id, PDO::PARAM_INT);
			$statement -> bindParam(':lab_id', $lab_id, PDO::PARAM_INT);
			$statement -> execute();
			$result = $statement -> fetch();

			// Setting data to this object
			$this -> id = $dev_id;
			$this -> lab_id = $lab_id;
			$this -> name = $result['dev_name'];
			$this -> bin_name = $result['bin_name'];
			$this -> bin_filename = $result['bin_filename'];
			$this -> ram = $result['dev_ram'];
			$this -> nvram = $result['dev_nvram'];
			$this -> ethernet = $result['dev_ethernet'] == '' ? 2 : $result['dev_ethernet'];
			$this -> serial = $result['dev_serial'] == '' ? 2 : $result['dev_serial'];
			$this -> picture = $result['dev_picture'];
			$this -> delay = $result['dev_delay'];
			$this -> cfg_id = $result['cfg_id'];
			$this -> cfg_name = $result['cfg_name'];
			$this -> config = $result['cfg_config'];
			$this -> top = $result['dev_top'];
			$this -> left = $result['dev_left'];
			$this -> l2keepalive = $result['dev_l2keepalive'];
			$this -> watchdog = $result['dev_watchdog'];
			$this -> console = BASE_PORT + $this -> id;
			$this -> files = array(
				'startup' => 'config-'.sprintf('%05d', $this -> id),
				'nvram' => 'nvram_'.sprintf('%05d', $this -> id),
				'vlan' => 'vlan.dat-'.sprintf('%05d', $this -> id),
			);
		} catch(PDOException $e) {
			error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		}
	}

	/**
     * Delete all configurations of a device
     * 
     * @return	bool						true if deleted all configurations
     */
	public function clean() {
		// Delete startup, nvram and vlan files from /tmp
		foreach ($this -> files as $file) {
			if ($this -> isRunning()) {
				$this -> stop();
			}
			if (file_exists('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file) && !unlink('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file)) {
				error_log('FILE: cannot delete copy /tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file.'.');
				return false;
			}
		}
		return true;
	}
	
	/**
     * Export a configuration
     * 
     * @return	bool						true if exported
     */
	public function export() {
		if ($this -> isCloud()){
			// Don't need to export if Cloud, skipping
			return true;
		} else {
			//Import running, if doesn't exist import startup and if it doesn't exist impost initial config.
			$running_config = '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/running-config';
			$startup_config = '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'//startup-config';
			//$initial_config = '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/config-'.sprintf("%05d", $this -> id);
			$name = $this -> lab_name.' - '.$this -> name;

			if (file_exists($running_config)) {
				$import_file = $running_config;
			} elseif (file_exists($startup_config)) {
				$import_file = $startup_config;
			} else {
				error_log('FILE: cannot find any config to export.');
				return false;
			}
			
			try {
				$fp = fopen($import_file, 'r');
				$content = fread($fp, filesize($import_file));

				if (cfg_add($name, $content, $this -> folder_id)) {
					return true;
				} else {
					return false;
				}
				
				fclose($fp);
				return true;
			} catch (Exception $e) {
				error_log('FILE: cannot export '.$import_file.' with error "'.$e.'".');
				return false;
			}
		}
	}
	
    /**
     * Check if a device is a cloud
     * 
     * @return	bool						true if a device is a cloud
     */
	public function isCloud() {
		if ($this -> picture == 'cloud') {
			return true;
		} else {
			return false;
		}
	}
	
    /**
     * Check if an interface is Ethernet
     * 
	 * @param	string	$dev_int			the interface of the device (i.e. 0/0)
     * @return	bool						true if a device is a cloud
     */
	public function isEthernet($dev_int) {
        $eth = $this -> ethernet;
        $ser = $this -> serial;

        // if eth is not set, use the default
        if ($eth == '') $eth = 2;

        // if ser is not set, use the default
        if ($ser == '') $ser = 2;

        // get the portgroup number (i.e. 1/2)
        $portgroup = substr($dev_int, 0, strpos($dev_int, '/'));

        if ($portgroup <= $eth - 1) {
                return true;
        } else {
                return false;
        }
	}
	
    /**
     * Check if a device is running
     * 
     * @return	bool						true if is running
     */
	public function isRunning() {
		// Check id ID <= 1024
		if ($this -> id > 1024) {
			return false;
		}
		if($this -> isCloud()) {
			if (!file_exists('/sys/class/net/'.$this -> ethernet.'/operstate')) {
				// Ethernet not found
				error_log('FILE: cannot find '.$this -> ethernet.' ethernet interface.');
				return false;
			}
			$command = 'pgrep -f "ioulive86.*'.$this -> ethernet.'.*'.$this -> id.'"';
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
		} else {
			$command = 'sudo fuser -n tcp '.$this -> console;
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
	}
	
    /**
     * Reset the console
     * 
     * @return	bool						true if reset
     */
	public function reset() {
		if (!$this -> isRunning()) {
			return true;
		} else {
			if (!$this -> isCloud()) {
				// Stop
				$command = 'sudo kill -SIGHUP $(sudo fuser -n tcp '.$this -> console.' 2>&1 | awk \'{print $2}\')';
				try {
					exec($command, $output, $pid);
					return true;
				} catch (Exception $e){
					error_log('EXEC: failed to exec "'.$command.'".');
					return false;
				}
			} else {
				// Cloud device -> skip
				return true;
			}
		}
	}
	
	/**
     * Revert to snapshot
     * 
     * @return	bool						true if reverted to snapshot
     */
	public function revert() {
		// Revert startup, nvram and vlan files
		foreach ($this -> files as $file) {
			if ($this -> isRunning()) {
				$this -> stop();
			}
            if (file_exists('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file)) {
                $command = 'sudo rm -f /tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file;
                try {
                    exec($command, $output, $pid);
                    if ($pid != 0) {
                        error_log('EXEC: cannot remove "/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file.'"');
                        return false;
                    }
                } catch (Exception $e) {
                    error_log('EXEC: failed to exec "'.$command.'".');
                    return false;
                }
            }
			if (file_exists(BASE_DIR.'/labs/lab_'.$this -> lab_id.'/'.$file) && !copy(BASE_DIR.'/labs/lab_'.$this -> lab_id.'/'.$file, '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file)) {
				error_log('FILE: cannot copy '.BASE_DIR.'/labs/lab_'.$this -> lab_id.'/'.$file.' to /tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file.'.');
				return false;
			}
		}
		return true;
	}
	
    /**
     * Set save device
     * 
     * @return	void
     */
	public function save() {  
		try {
			// Saving data to DB
			global $db;
			$query = 'INSERT OR REPLACE INTO devices (dev_id, lab_id, dev_name, bin_name, dev_ram, dev_nvram, dev_ethernet, dev_serial, dev_picture, dev_delay, cfg_id, dev_top, dev_left, dev_l2keepalive, dev_watchdog) VALUES (:dev_id, :lab_id, :dev_name, :bin_name, :dev_ram, :dev_nvram, :dev_ethernet, :dev_serial, :dev_picture, :dev_delay, :cfg_id, :dev_top, :dev_left, :dev_l2keepalive, :dev_watchdog);';
			$statement = $db -> prepare($query);
			$statement -> bindParam(':dev_id', $this -> id, PDO::PARAM_INT);
			$statement -> bindParam(':lab_id', $this -> lab_id, PDO::PARAM_INT);
			$statement -> bindParam(':dev_name', $this -> name, PDO::PARAM_STR);
			$statement -> bindParam(':bin_name', $this -> bin_name, PDO::PARAM_STR);
			$statement -> bindParam(':dev_ram', $this -> ram, PDO::PARAM_STR);			// PARAM_INT can't be null, PARAM_STR can be null
			$statement -> bindParam(':dev_nvram', $this -> nvram, PDO::PARAM_STR);		// PARAM_INT can't be null, PARAM_STR can be null
			$statement -> bindParam(':dev_ethernet', $this -> ethernet, PDO::PARAM_STR);// PARAM_INT can't be null, PARAM_STR can be null
			$statement -> bindParam(':dev_serial', $this -> serial, PDO::PARAM_STR);	// PARAM_INT can't be null, PARAM_STR can be null
			$statement -> bindParam(':dev_picture', $this -> picture, PDO::PARAM_STR);
			$statement -> bindParam(':dev_delay', $this -> delay, PDO::PARAM_STR);
			$statement -> bindParam(':cfg_id', $this -> cfg_id, PDO::PARAM_INT);
			$statement -> bindParam(':dev_top', $this -> top, PDO::PARAM_INT);
			$statement -> bindParam(':dev_left', $this -> left, PDO::PARAM_INT);
			$statement -> bindParam(':dev_l2keepalive', $this -> l2keepalive, PDO::PARAM_BOOL);
			$statement -> bindParam(':dev_watchdog', $this -> watchdog, PDO::PARAM_BOOL);
			$statement -> execute();
		} catch(PDOException $e) {
			error_log('DB: cannot update the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		}
    }

	/**
     * Make a snapshot of the device
     * 
     * @return	bool						true if a snapshot is made
     */
	public function snapshot() {
		// Preparing folders
		if (!file_exists(BASE_DIR.'/labs') && !mkdir(BASE_DIR.'/labs')) {
			error_log('FILE: cannot create dir '.BASE_DIR.'/labs.');
			return false;
		}
		if (!file_exists(BASE_DIR.'/labs/lab_'.$this -> lab_id) && !mkdir(BASE_DIR.'/labs/lab_'.$this -> lab_id)) {
			error_log('FILE: cannot create dir '.BASE_DIR.'/labs/lab_'.$this -> lab_id.'.');
			return false;
		}
		
		// Store startup, nvram and vlan files
		foreach ($this -> files as $file) {
			if (file_exists('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file) && !copy('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file, BASE_DIR.'/labs/lab_'.$this -> lab_id.'/'.$file)) {
				error_log('FILE: cannot copy /tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$file.' to '.BASE_DIR.'/labs/lab_'.$this -> lab_id.'/'.$file.'.');
				return false;
			}
		}
		return true;
	}
	
    /**
     * Start a device
     * 
     * @return	bool						true if a device is started or already started
     */
	public function start() {
		// Check id ID <= 1024
		if ($this -> id > 1024) {
			error_log('EXEC: device id must be less/equal than 1024 ('.$this -> id.' was used)');
			return false;
		}
		// Check if already running
		if ($this -> isRunning()) {
			return true;
		} else {
			// Preparing folders
			if (!file_exists('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id) && !mkdir('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id)) {
				mkdir('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id);
				error_log('FILE: cannot create dir /tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'.');
				return false;
			}
			// Linking NETMAP
			$netmap = '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/NETMAP';
			if (!file_exists($netmap) && !symlink('../NETMAP', $netmap)) {
				error_log('FILE: cannot link ../NETMAP.');
				return false;
			}

			// Now starting
			if ($this -> isCloud()) {
				// Check if device is a Cloud
				if (!file_exists('/sys/class/net/'.$this -> ethernet.'/operstate')) {
					// Ethernet not found
					error_log('FILE: cannot find '.$this -> ethernet.' ethrenet interface.');
					return false;
				}
				$command = 'sudo ifconfig '.$this -> ethernet.' up >> '.BASE_DIR.'/data/Logs/exec.txt 2>&1 &';
				try {
					exec($command, $output, $pid);
					if ($pid != 0) {
						error_log('EXEC: cannot bring ethernet '.$eth.' up');
						return false;
					}
				} catch (Exception $e) {
					error_log('EXEC: failed to exec "'.$command.'".');
					return false;
				}
				$command = 'nohup sudo '.BASE_BIN.'/ioulive86 -i '.$this -> ethernet.' -n /tmp/iou/lab_'.$this -> lab_id.'/NETMAP '.$this -> id.' >> '.BASE_DIR.'/data/Logs/exec.txt 2>&1 &';
				try {
					exec($command, $output, $pid);
					return $this -> isRunning();
				} catch (Exception $e) {
					error_log('EXEC: failed to exec "'.$command.'".');
					return false;
				}
			} else {
				// Linking iourc
				$iourc = '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/iourc';
				if (!file_exists(BASE_BIN.'/iourc')) {
					error_log('FILE: you must fill license file ('.BASE_BIN.'/iourc) before starting devices.');
					return false;
				}
				if (!file_exists($iourc) && !symlink(BASE_BIN.'/iourc', $iourc)) {
					error_log('FILE: cannot link '.BASE_BIN.'/iourc.');
					return false;
				}
				// Linking IOS
				$bin = '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/'.$this -> bin_filename;
				if (!file_exists(BASE_BIN.'/'.$this -> bin_filename)) {
					// Non existent binary file
					error_log('FILE: file '.BASE_BIN.'/'.$this -> bin_filename.' does not exist.');
					return false;
				}			
				if (!file_exists($bin) && !symlink(BASE_BIN.'/'.$this -> bin_filename, $bin)) {
					error_log('FILE: cannot link '.BASE_BIN.'/'.$this -> bin_filename.'.');
					return false;
				}	
				// Store initial config
				$startup_file = '/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id.'/config-'.sprintf('%05d', $this -> id);
				if (file_exists($startup_file) && !unlink($startup_file)) {
					error_log('FILE: cannot delete '.$startup_file.' configuration.');
					return false;
				}
				if ($this -> config != '') {
					$startup = '-c config-'.sprintf('%05d', $this -> id); // Cannot use full path because of overflow in IOU
					try {
						$fp = fopen($startup_file, 'w');
						fwrite($fp, $this -> config);
						fclose($fp);
					} catch (Exception $e) {
						error_log('FILE: cannot write '.$startup_file.' configuration.');
						return false;
					}
				} else {
					$startup = '';
				}
				// Checking parameters
				if(is_numeric($this -> ethernet)) {
					$ethernet = '-e '.$this -> ethernet;
				} else {
					$ethernet = '';
				}
				if(is_numeric($this -> serial)) {
					$serial = '-s '.$this -> serial;
				} else {
					$serial = '';
				}
				if(is_numeric($this -> nvram)) {
					$nvram = '-n '.$this -> nvram;
				} else {
					$nvram = '';
				}
				if(is_numeric($this -> ram)) {
					$ram = '-m '.$this -> ram;
				} else {
					$ram = '';
				}
				if(is_numeric($this -> delay)) {
					$delay = $this -> delay;
				} else {
					$delay = '0';
				}
				if ($this -> l2keepalive) {
					$l2keepalive = '-l';
				} else {
					$l2keepalive = '';
				}
				if ($this -> watchdog) {
					$watchdog = '';
				} else {
					$watchdog = '-W';	// -W disable watchdog
				}
				// Start
				$command = 'nohup sudo -E '.WRAPPER.' -m '.$bin.' -p '.$this -> console.' -d '.$delay.' -t "'.$this -> name.'" -- '.
					$ethernet.' '.$serial.' '.$nvram.' '.$ram.' '.$l2keepalive.' '.$watchdog. ' '.$startup.' '.$this -> id.' >> '.BASE_DIR.'/data/Logs/exec.txt 2>&1 &';
				try {
					chdir('/tmp/iou/lab_'.$this -> lab_id.'/dev_'.$this -> id);
					exec($command, $output, $pid);
					return $this -> isRunning();
				} catch (Exception $e) {
					error_log('EXEC: failed to exec "'.$command.'".');
					return false;
				}
			}
		}
	}
	
    /**
     * Stop a device
     * 
     * @return	bool						true if a device is stopped or already stopped
     */
	public function stop() {
		// Check id ID <= 1024
		if ($this -> id > 1024) {
			return true;
		}
		if (!$this -> isRunning()) {
			return true;
		} else {
			if ($this -> isCloud()) {
				// Check if device is a Cloud
				if (!file_exists('/sys/class/net/'.$this -> ethernet.'/operstate')) {
					// Ethernet not found
					error_log('FILE: cannot find '.$this -> ethernet.' ethrenet interface.');
					return false;
				}
				$command = 'sudo pkill -f "ioulive86.*'.$this -> ethernet.'.*'.$this -> id.'"';
				try {
					exec($command, $output, $pid);
					return !$this -> isRunning();
				} catch (Exception $e){
					error_log('EXEC: failed to exec "'.$command.'".');
					return false;
				}
			} else {
				// Stop
				$command = 'sudo fuser -n tcp -k -TERM '.$this -> console;
				try {
					exec($command, $output, $pid);
					return !$this -> isRunning();
				} catch (Exception $e){
					error_log('EXEC: failed to exec "'.$command.'".');
				}
			}
		}
	}
}
?>
