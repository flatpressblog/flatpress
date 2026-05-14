<?php

	function admin_getpanellink($page, $action, $command='') {

		$link = BLOG_BASEURL . 'admin.php?p=' . $page . '&action=' .$action;
		if ($command) {
			$link .= '&' . $command;
		}

		return wp_specialchars(
				apply_filters('admin_panel_link', 
						$link, 
						$page, 
						$action, 
						$command)
				);
	}

	function admin_addpanel($page) {
		global $fpadminpanels;
		$fpadminpanels [] = $page;
	}

	function admin_getpanels() {
		global $fpadminpanels;

		return $fpadminpanels;
	}


	function admin_addpanelaction($panel, $action, $showpanel = true) {

		global $fpadminpanelactions;

		if (admin_panelexists($panel)) {
			$fpadminpanelactions [$panel] [$action] = $showpanel;
		}

	}

	function admin_getpanelactions($panel) {

		global $fpadminpanelactions;
		if (isset($fpadminpanelactions [$panel])) {
			return $fpadminpanelactions [$panel];
		} else {
			return array();
		}

	}

	function admin_panelexists($panel) {
		global $fpadminpanels;
		return in_array($panel, $fpadminpanels);

	}

	function admin_getpaneldir($id) {
		global $fpadminpanels;
		$id = (string) $id;
		$isPluginPanel = false;
		foreach ((array) $fpadminpanels as $panel) {
			if (is_array($panel) && isset($panel [0]) && (string) $panel [0] === $id && !empty($panel [1])) {
				$isPluginPanel = true;
				break;
			}
		}

		if ($isPluginPanel) {
			return ABS_PATH . plugin_getdir($id);
		} else {
			return ABS_PATH . ADMIN_DIR . $id;
		}
	}

?>
