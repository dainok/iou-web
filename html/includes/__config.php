<?php
/**
 * Class for iou-web configs
 * 
 * This class define the following methods:
 * - __construct: a constructor to create a config
 * - load: a method to load initial config also
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */

class Config {
	static $id;
	public $config;
	public $name;
	public $pack;
	public $folder_id;
	
    /**
     * Constructor which load an existent lab (devices are not loaded)
     * 
     * @param	bool	$get_from_db		true if data are get from the DB, false if data are get from parameter 
     * @param	int		$cfg_id				the cfg_id of the config
     * @param	string	$cfg_name			the name of the config
	 * @param	int		$folder_id			the folder_id of the config
     * @return	void
     */
	public function __construct($get_from_db, $cfg_id, $cfg_name, $folder_id) {
		if ($get_from_db) {
			try {
				global $db;
				// Getting data from DB
				$query = 'SELECT cfg_name, folder_id FROM configs WHERE cfg_id=:cfg_id;';
				$statement = $db -> prepare($query);
				$statement -> bindParam(':cfg_id', $cfg_id, PDO::PARAM_INT);
				$statement -> execute();
				$result = $statement -> fetch();

				// Setting data to this object
				$this -> id = $cfg_id;
				$this -> name = $result['cfg_name'];
				$this -> folder = $result['folder_id'];
			} catch(PDOException $e) {
				error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
			}
		} else {
			// Use parameters
			$this -> id = $cfg_id;
			$this -> name = $cfg_name;
			$this -> folder_id = $folder_id;
		}

		// Storing config pack
		if (strstr($this -> name, ' - ')) {
			//If it is part of a config pack strip " - *$"
			$this -> pack = substr($this -> name, 0, strpos($this -> name, ' - '));
		} else {
			//If it is a stand-alone config leave it as it is
			$this -> pack = $this -> name;
		}
	}

    /**
     * Load config also
     * 
     * @return	void
     */
	public function load() {
		try {
			global $db;
			// Getting data from DB
			$query = 'SELECT cfg_config FROM configs WHERE cfg_id=:cfg_id;';
			$statement = $db -> prepare($query);
			$statement -> bindParam(':cfg_id', $this -> id, PDO::PARAM_INT);
			$statement -> execute();
			$result = $statement -> fetch();

			// Setting data to this object
			$this -> config = $result['cfg_config'];
		} catch(PDOException $e) {
			error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		}
    }
}
?>