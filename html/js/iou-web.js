/*************************************************************************
 * Add folder                                                            *
 *************************************************************************/
function addFolder(base_www, folder_name, folder_parent) {
	$.ajax({
		type: 'POST',
		url: base_www + '/ajax_helper.php?action=folder_add',
		data: {
			folder_name: encodeURIComponent(folder_name),
			folder_parent: folder_parent,
		},
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
				location.reload();
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Check for software updates                                            *
 *************************************************************************/
function checkUpdate(local_version) {
	$.ajax({
		type: 'GET',
		url: '/ajax_helper.php?action=check_update',
		dataType: 'xml',
		success: function(xml) {
			var current_version = $(xml).find('message').text();
			var current_whatsnew = $(xml).find('whatsnew').text();
			var dialog_title = 'Update available';
			var dialog_message = 'A new version of iou-web is available, you should update to <strong>' + current_version +'</strong>.<br/>' + current_whatsnew;
			if (current_version != local_version) {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Update available';
			var dialog_message = 'Cannot check for software update. Be sure your Virtual Machine is directly connected to Internet.';
			dialogAutoclose(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Delete file                                                           *
 *************************************************************************/
function deleteFile(base_www, file_name) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=file_delete&file_name=' + encodeURIComponent(file_name),
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Clear device                                                          *
 *************************************************************************/
function deviceClean(base_www, dev_id) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=cfg_clean&dev_id=' + dev_id,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Revert                                                                *
 *************************************************************************/
function deviceRevert(base_www, dev_id) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=cfg_revert&dev_id=' + dev_id,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Snapshot                                                              *
 *************************************************************************/
function deviceSnapshot(base_www, dev_id) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=cfg_snapshot&dev_id=' + dev_id,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Create an auto-close window message                                   *
 *************************************************************************/
function dialogAutoclose(dialog_title, dialog_message) {
	$('#dialog').attr('title', dialog_title);
	$('#dialog').html(dialog_message);
	$('#dialog').dialog({
		modal: true,
		show: 'fade',
		hide: 'fade',
		resizable: false,
	}, setTimeout(function(){$("#dialog").dialog("destroy");},2000));
}
/*************************************************************************
 * Create an informational window message (OK only)                      *
 *************************************************************************/
function dialogMessage(dialog_title, dialog_message) {
	$('#dialog').attr('title', dialog_title);
	$('#dialog').html(dialog_message);
	$('#dialog').dialog({
		modal: true,
		show: 'fade',
		hide: 'fade',
		resizable: false,
		buttons: {
			Ok: function() {
				$(this).dialog('destroy');
			},
		}
	});
}
/*************************************************************************
 * Export device config                                                  *
 *************************************************************************/
function exportConfig(base_www, dev_id) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=cfg_export&dev_id=' + dev_id,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			dialogAutoclose(dialog_title, dialog_message);
		},
		error: function() {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Init Database                                                     *
 *************************************************************************/
function initDatabase(base_www) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=db_wipe',
		dataType: 'xml',
		timeout: '15000',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Optimize Database                                                     *
 *************************************************************************/
function optimizeDatabase(base_www) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=db_optimize',
		dataType: 'xml',
		timeout: '15000',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Reset device                                                          *
 *************************************************************************/
function resetDevice(base_www, dev_id) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=dev_reset&dev_id=' + dev_id,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Sniffer: start/stop action                                            *
 *************************************************************************/
function sniffer(base_www, action) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=' + action,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Start device                                                          *
 *************************************************************************/
function startDevice(base_www, dev_id) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=dev_start&dev_id=' + dev_id,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Stop device                                                           *
 *************************************************************************/
function stopDevice(base_www, dev_id) {
	$.ajax({
		type: 'GET',
		url: base_www + '/ajax_helper.php?action=dev_stop&dev_id=' + dev_id,
		dataType: 'xml',
		success: function(xml) {
			var dialog_title = $(xml).find('status').text();
			var dialog_message = $(xml).find('message').text();
			if (dialog_title == 'Informational') {
				dialogAutoclose(dialog_title, dialog_message);
			} else {
				dialogMessage(dialog_title, dialog_message);
			}
		},
		error: function() {
			var dialog_title = 'Error';
			var dialog_message = 'Error opening <strong>' + base_www + url + '</strong> page.';
			dialogMessage(dialog_title, dialog_message);
		},
	});
}
/*************************************************************************
 * Update device status and positions                                    *
 *************************************************************************/
function updateDeviceStatus(base_www, lab_id) {
    $.ajax({
        type: 'GET',
        url: base_www + '/ajax_helper.php?action=dev_status&lab_id=' + lab_id,
        dataType: 'xml',
        success: function(xml) {
            // Sniffer
            $(xml).find('sniffer').each(function(){
                if ($(this).find('status').text() == 1) {
                    sniffer_status = 'running';
                } else {
                    sniffer_status = 'stopped';
                }
                $('#dev_small_status_sniffer').attr('src', base_www + '/images/devices/nam_small_' + sniffer_status + '.png');
            });
            // Devices
            $(xml).find('device').each(function(){
                var dev_id = $(this).attr('id');
                var dev_type = $(this).find('type').text();
                if ($(this).find('status').text() == 1) {
                    dev_status = 'running';
                } else {
                    dev_status = 'stopped';
                }
                $('#dev_small_status_' + dev_id).attr('src', base_www + '/images/devices/' + dev_type + '_small_' + dev_status + '.png');
                    
                if ($('#dev_status_' + dev_id).length > 0) {
                    // If diagram tab is loaded, then update big icons and position
                    $('#dev_status_' + dev_id).attr('src', base_www + '/images/devices/' + dev_type + '_' + dev_status + '.png');
                    
                    //Update device position also
                    var p = $("#node" + dev_id);
                    var offset = p.offset();
                    $('#' + dev_id + 'left').val((100 * offset.left / $(window).width()).toFixed(0));
                    $('#' + dev_id + 'top').val((100 * offset.top / $(window).height()).toFixed(0));
                }
            });
        }
    });
}
/*************************************************************************
 * Clone a Folder and its content                                        *
 *************************************************************************/
function clone_folder(base_www, folder_id, new_folder_name, destination_folder_id) {
    $.ajax({
        type: 'POST',
        url: base_www + '/ajax_helper.php?action=clone_folder',
        data: {
            new_folder_name: encodeURIComponent(new_folder_name),
            folder_id: folder_id,
            destination_folder_id: destination_folder_id,
        },
        dataType: 'xml',
        success: function(xml) {
            var dialog_title = $(xml).find('status').text();
            var dialog_message = $(xml).find('message').text();
            dialogAutoclose(dialog_title, dialog_message);
            location.reload();
        },
        error: function() {
            var dialog_title = 'Error';
            var dialog_message = 'Error <strong>' + url + '</strong> page.';
            dialogMessage(dialog_title, dialog_message);
        },
    });
}
