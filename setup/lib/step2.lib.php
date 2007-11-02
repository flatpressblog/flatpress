<?php

	function check_step() {
	
		global $lang;
		
		$validate = validate();
		
		if ($validate) {
		
			$vl =& $lang['samplecontent'];
			entry_save(array(
				'subject' => $vl['entry']['subject'],
				'content' => $vl['entry']['content']
			));	
			static_save(array(
				'subject' => $vl['menu']['subject'],
				'content' => $vl['menu']['content']
			), 'menu');
			static_save(array(
				'subject' => $vl['about']['subject'],
				'content' => $vl['about']['content']
			), 'about');
		}
		
		return $validate;
	}

?>