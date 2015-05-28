<?php
/**
 * Class for iou-web images
 * 
 * This class define the following methods:
 * - __construct: a constructor to create an image
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */

class Image {
	static $id;
	public $name;
	public $info;
	public $content;
	public $folder_id;
	public $map;

    /**
     * Constructor which load an existent image
     * 
     * @param	bool	$get_from_db		true if data are get from the DB, false if data are get from parameter 
     * @param	int		$img_id				the img_id of the image
     * @param	string	$img_name			the name of the image
	 * @param	int		$folder_id			the folder_id of the image
     * @return	void
     */
	public function __construct($get_from_db, $img_id, $img_name, $folder_id) {
		if ($get_from_db) {
			try {
				global $db;
				// Getting data from DB
				$query = 'SELECT img_name, img_info, img_content, folder_id, img_map FROM images WHERE img_id=:img_id;';
				$statement = $db -> prepare($query);
				$statement -> bindParam(':img_id', $img_id, PDO::PARAM_INT);
				$statement -> execute();
				$result = $statement -> fetch();

				// Setting data to this object
				$this -> id = $img_id;
				$this -> name = $result['img_name'];
				$this -> info = $result['img_info'];
				$this -> content = $result['img_content'];
				$this -> folder_id = $result['folder_id'];
				$this -> map = $result['img_map'];
			} catch(PDOException $e) {
				error_log('DB: cannot query the DB with error "'.$e->getMessage().'" (query was "'.$query.'".');
			}
		} else {
			// Use parameters
			$this -> id = $img_id;
			$this -> name = $img_name;
			$this -> folder_id = $folder_id;
		}
	}
}
?>