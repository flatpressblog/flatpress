<?php

/**
 * Static list panel for FlatPress admin
 *
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 * @property \Smarty $smarty
 */

	class admin_static_list extends AdminPanelActionValidated {


		var $actionname = 'list';

		var $events = array(
			'save'
		);

		function main() {
			// Returns an int value of 0... What for?
			// parent::main(); 
			$this->smarty->assign('statics', $assign = static_getlist());
			return 0;
		}

		function onsave() {
			global $fp_config;

			$fp_config ['staticlist'] = array(
				'naturalsort' => isset($_POST ['naturalsort'])
			);

			$fp_config ['staticlist'] ['naturalsort'] = isset($fp_config ['staticlist'] ['naturalsort']) ? $fp_config ['staticlist'] ['naturalsort']
						: false;

			$success = config_save() ? 1 : -1;

			$this->smarty->assign('fp_config', $fp_config);

			return 1;

		}

		function onsubmit($data = null) {
			parent::onsubmit($data);
			return $this->main();
		}

		function onfilter() {
			return $this->main();
		}

		function onerror() {
			return $this->main();
			return 0;
		}

	}

?>
