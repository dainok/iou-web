<?php
/**
 * Class for iou-web folder
 * 
 * This class define the following methods:
 * - __construct: a constructor to create a folder
 * - getPath: a method to the full path of the folder
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */

class Folder {
	static $id;
	public $parent_id;
	public $name;
	public $path;
	
    /**
     * Constructor which load an existent folder
     * 
     * @param	bool	$get_from_db		true if data are get from the DB, false if data are get from parameter 
     * @param	int		$folder_id			the folder_id of the folder
     * @param	string	$folder_name		the folder_name of the folder
     * @param	int		$parent_id			the parent_id of the folder
     * @return	void
     */
	 public function __construct($get_from_db, $folder_id, $folder_name, $parent_id) {
		if ($folder_id == 0) {
			// If root folder, do not query the DB
			$this -> id = 0;
			$this -> parent_id = 0;
			$this -> name = 'Root';
		} else {
			if ($get_from_db) {
				try {
					global $db;
					// Getting data from DB
					$query = 'SELECT folder_name, parent_id FROM folders WHERE folder_id=:folder_id;';
					$statement = $db -> prepare($query);
					$statement -> bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
					$statement -> execute();
					$result = $statement -> fetch();

					// Setting data to this object
					$this -> id = $folder_id;
					$this -> parent_id = $result['parent_id'];
					$this -> name = $result['folder_name'];
				} catch(PDOException $e) {
					error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
				}
			} else {
				// Use parameters
				$this -> id = $folder_id;
				$this -> parent_id = $parent_id;
				$this -> name = $folder_name;
			}
		}
		$this -> path = $this -> getPath();
	}
	
    /**
     * A method to get the full path of the folder
     * 
     * @return	string						the path of the folder
     */
	function getPath() {
		if ($this -> id == 0) {
			return '/';
		} else {
			$parent_folder = New Folder(true, $this -> parent_id, '', '');
			return $parent_folder -> path.$this -> name.'/';
		}
	}
}
?>