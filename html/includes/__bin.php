<?php
/**
 * Class for iou-web binary files
 * 
 * This class define the following methods:
 * - __construct: a constructor to create a bin
 * 
 * @author Andrea Dainese <andrea.dainese@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 */

class Bin {
	static $filename;
	public $name;
	
    /**
     * Constructor which create a bin
     * 
     * @param	string	$bin_filename			the filename of the binary file
     * @param	string	$bin_name				the name (alias) of the binary file
     * @return	void
     */
	public function __construct($bin_filename, $bin_name) {
		$this -> filename = $bin_filename;
		$this -> name = $bin_name;
	}
}
?>