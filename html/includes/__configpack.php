<?php
/**
 * Class for iou-web config packs
 * 
 * This class define the following methods:
 * - __construct: a constructor to create a config pack
 * - load: a method to load all configs
 * - save: a method to save all configs
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */

class ConfigPack {
	static $name;
	public $folder_id;
	public $configs = array();

    /**
     * Constructor which create a config pack
     * 
     * @param	string	$cfg_name				the name of the config pack
     * @param	int		$folder_id			the fodler id where the config pack is placed
     * @return	void
     */
	public function __construct($cfg_name, $folder_id) {
		$this -> name = $cfg_name;
		$this -> folder_id = $folder_id;
	}

    /**
     * Load all configs
     * 
     * @return	void
     */
	public function load() {
		try {
			global $db;

			// Getting configs data from DB
			$cfg_like = $this -> name.' - %';
			$query = 'SELECT cfg_id FROM configs WHERE cfg_name LIKE :cfg_like OR cfg_name=:cfg_name ORDER BY cfg_name COLLATE NOCASE ASC;';
			$statement = $db -> prepare($query);
			$statement -> bindParam(':cfg_like', $cfg_like, PDO::PARAM_STR);
			$statement -> bindParam(':cfg_name', $this -> name, PDO::PARAM_STR);
			$statement -> execute();

			// Putting configs into an array
			while ($result = $statement -> fetch(PDO::FETCH_ASSOC)) {
				array_push($this -> configs, new Config(true, $result['cfg_id'], '', ''));
			}
			
			// Loading all config also
			foreach ($this -> configs as $config) {
				$config -> load();
			}
		} catch(PDOException $e) {
			error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
		}
    }
}
?>