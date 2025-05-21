<?php

class admin_entry_commedit extends AdminPanelActionValidated {

		var $validators = array(
				array('name', 'name', 'notEmpty', false, false, 'trim,stripslashes'),
				array('email', 'email', 'isEmail', true, false, 'trim,stripslashes'),
				array('url', 'url', 'isURL', true, false, 'trim,stripslashes'),
				array('content', 'content', 'notEmpty', false, false, 'stripslashes'),
		); 

		var $events = array('save');

		var $args = array('entry', 'comment');

		var $nosuchcomment = false;

		function setup() {
			$this->nosuchcomment = !comment_exists($_REQUEST['entry'], $_REQUEST['comment']);
			$this->smarty->assign('entryid', $_REQUEST['entry']);
			$this->smarty->assign('id', $_REQUEST['comment']);
		}

		function main() {
			if ($this->nosuchcomment) return PANEL_REDIRECT_DEFAULT;

			$e = entry_parse($_REQUEST['entry']);
			if ($e) {
				$this->smarty->assign('entrysubject', $e['subject']);
			} else return PANEL_REDIRECT_DEFAULT;

			$comment = comment_parse($_REQUEST['entry'], $_REQUEST['comment']);
			if ($comment) {
				$this->smarty->assign('values', $comment);
				$this->smarty->append('values', array('ip_address'=>$comment['ip-address']), true);
			} else return PANEL_REDIRECT_DEFAULT;

			

		}

		function onsave($content) {
			if ($this->nosuchcomment) return PANEL_REDIRECT_DEFAULT;
			
			$comment = comment_parse($_REQUEST['entry'],$_REQUEST['comment']);
			if (isset($comment['loggedin'])) $content['loggedin'] = $comment['loggedin'];
			$content['ip-address']	= $comment['ip-address'];
			$content['date'] = $comment['date'];
			$success = comment_save($_REQUEST['entry'], $content);
			$this->smarty->assign('success', $success? 1 : -1);
			return PANEL_REDIRECT_CURRENT;
		}

}
