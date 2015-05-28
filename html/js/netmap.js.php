<?php
	include('../includes/conf.php');

	if (isset($_SESSION['current_lab'])) {
		$cleaned_netmap = preg_replace('/(#.*)/', '', $_SESSION['current_lab'] -> netmap);              // Remove comments
        $cleaned_netmap = preg_replace('/ [0-9]+\r/', '', $cleaned_netmap);                             // Filtering encapuslation on each line
        $cleaned_netmap = preg_replace('/ [0-9]+\z/', '', $cleaned_netmap);                             // Filtering encapuslation on last line
		$cleaned_netmap = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n\']+/", "\n", $cleaned_netmap);	// Remove empty lines
		$cleaned_netmap = preg_replace("/[\s]+\n/", "\n", $cleaned_netmap);								// Remove trailing spaces (trim lines)
		$cleaned_netmap = trim($cleaned_netmap);														// Remove trailing spaces (trim all)
		$cleaned_netmap = $cleaned_netmap."\n";															// Adding and end of line for ioulive86
		$netmap_array = explode("\n", preg_replace('/ [0-9]+\r/', '', $cleaned_netmap));
		$base_hub = BASE_HUB;
?>
	jsPlumb.bind("ready", function() {
		jsPlumb.importDefaults({
			Anchor : "Continuous",
			//Connector : [ "Bezier", { curviness: 50 } ],
			Connector : [ "Straight" ],
			Endpoint : "Blank",
			PaintStyle : { lineWidth : 2, strokeStyle : "#828282" },
			cssClass:"link",
		});
<?php
		$currr_hub = $base_hub;
		$index = 0;
		// Print connections between devices
		foreach ($netmap_array as $key => $value) {
			$tok = strtok($value, " ");
			$total = 0;
			while ($tok != false) {
				$total++;
				$tok = strtok(" ");
			}
			// Now let's paint
			$tok = strtok($value, ": ");
			if (is_numeric($tok)) {
				// First and third are devices;
				// Second and fourth are interfaces;
				$routerA_ID = $tok;
				$routerA_int = strtok(": ");

				// If host specified, remove it
				if (strrpos($routerA_int, '@') > 0) {
					$routerA_int = substr($routerA_int, 0, strrpos($routerA_int, '@'));
				}
				// Check if serial or ethernet
				if ($_SESSION['current_lab'] -> devices[$routerA_ID] -> isCloud()) {
					$routerA_int = $_SESSION['current_lab'] -> devices[$routerA_ID] -> ethernet;
				} else if ($_SESSION['current_lab'] -> devices[$routerA_ID] -> isEthernet($routerA_int)) {
					$routerA_int = "e".$routerA_int;
				} else {
					$routerA_int = "s".$routerA_int;
				}

				$routerB_ID = strtok(": ");
				$routerB_int = str_replace("\r", "", strtok(": "));
				
				// If host specified, remove it
				if (strrpos($routerB_int, '@') > 0 ) {
					$routerB_int = substr($routerB_int, 0, strrpos($routerB_int, '@'));
				}
				// Check if serial or ethernet
				if ($_SESSION['current_lab'] -> devices[$routerB_ID] -> isCloud()) {
					$routerB_int = $_SESSION['current_lab'] -> devices[$routerB_ID] -> ethernet;
				} else if ($_SESSION['current_lab'] -> devices[$routerB_ID] -> isEthernet($routerB_int)) {
					$routerB_int = "e".$routerB_int;
				} else {
					$routerB_int = "s".$routerB_int;
				}

				$nodes[$index++] = $routerA_ID;
				$nodes[$index++] = $routerB_ID;

				if ($total < 3) {
					// P2P Link
?>
					// P2P Link
					jsPlumb.connect({
						source:"node<?php print $routerA_ID ?>",
						target:"node<?php print $routerB_ID ?>",
<?php
					// If Serial, then paint orange link (cannot use isEthernet because int is s1/0 instead of 1/0
					if (substr($routerA_int, 0, 1) == 's') {
?>
						paintStyle : { lineWidth : 2, strokeStyle : "#ffcc00" },
<?php
					}
?>
						overlays:[
							[ "Label", {label:"<?php print $routerA_int ?>", location:0.15, cssClass:"label"}],
							[ "Label", {label:"<?php print $routerB_int ?>", location:0.85, cssClass:"label"}],
						]
					});
<?php
				} else {
					// Shared Link
?>
					// Shared Link - first device
					jsPlumb.connect({
						source:"node<?php print $routerA_ID ?>",
						target:"node<?php print $currr_hub ?>",
						//connector:"Bezier",
						//cssClass:"link",
						overlays:[
							[ "Label", {label:"<?php print $routerA_int ?>", location:0.15, cssClass:"label"}],
							[ "Label", {label:"", location:0.85, cssClass:"label"}],
						]
					});
					// Shared Link - second device
					jsPlumb.connect({
						source:"node<?php print $routerB_ID ?>",
						target:"node<?php print $currr_hub ?>",
						overlays:[
							[ "Label", {label:"<?php print $routerB_int ?>", location:0.15, cssClass:"label"}],
							[ "Label", {label:"", location:0.85, cssClass:"label"}],
						]
					});
<?php
					// Painting other devices
					$i = 2;
					while($i < $total) {
						$routerX_ID = strtok(": ");
						$routerX_int = str_replace("\r", "", strtok(": "));
						// If host specified, remove it
						if (strrpos($routerX_int, '@') > 0) {
							$routerX_int = substr($routerX_int, 0, strrpos($routerX_int, '@'));
						}
						// Check if serial or ethernet
						if ($_SESSION['current_lab'] -> devices[$routerX_ID] -> isEthernet($routerX_int)) {
							$routerX_int = "e".$routerX_int;
						} else {
							$routerX_int = "s".$routerX_int;
						}
						$nodes[$index++] = $routerX_ID;
?>
						// Shared Link - another device
						jsPlumb.connect({
							source:"node<?php print $currr_hub ?>",
							target:"node<?php print $routerX_ID ?>",
							overlays:[
								[ "Label", {label:"", location:0.15, cssClass:"label"}],
								[ "Label", {label:"<?php print $routerX_int ?>", location:0.85, cssClass:"label"}],
							]
						});
<?php				
						$i++;
					}
					$currr_hub++;
				}
			}
		}
?>
		jsPlumb.draggable(jsPlumb.getSelector(".window"));	
	});
<?php
	}
?>
