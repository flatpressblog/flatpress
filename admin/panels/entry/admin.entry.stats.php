<?php

/**
 * edit entry panel
 *
 * Type:     
 * Name:     
 * Date:     
 * Purpose:  
 * Input:
 *         
 * @author NoWhereMan <real_nowhereman at users dot sf dot com>
 *
 */
	
	class admin_entry_stats extends AdminPanelAction {
	
		function format_number($num, $sep) {
			$ss = $sep*$sep;
			$i = 0;
			while ( $num > $ss ) {
				$num = (float) $num / $sep;
				$i++;
			}
			
			return array(number_format((int)$num), $i);
			
		}

		function setup() {
			global $lang;
			$lang['admin']['entry']['stats'] = array();
			$this->smarty->assign('warnings', '[Dev Notice] Panel lang strings are currently hardcoded.');
		}
		
		function main() {
		
			global $fpdb;
		
			$fpdb->query(array(
				'count' => -1, // show all
				'fullparse' => true
			));
			
			$q = $fpdb->getQuery();
			
			$comments = 
			$entries = array(
				'count'	=> 0,
				'words'		=> 0,
				'chars'		=> 0,
				'size'		=> 0,
				'topten'	=> array()
			);
			
			$entries['comments'] = 0;
			
			$toplist = array();
			
			while ($q->hasMore()) {
			
				list($id, $e) = $q->getEntry();
				
				$entries['count']++;
				$entries['words'] +=  	str_word_count($e['subject']) + 
										str_word_count($e['content']);
										
				$entries['chars'] +=	strlen($e['subject']) + 
										strlen($e['content']);
								
				$entries['size'] += filesize(entry_exists($id));
							
				$cc = $q->hasComments();
				$entries['comments'] += $cc;
				$toplist[$id] = $cc;
				$toplistsubj[$id] = $e['subject']; 
				
				$comments['count']+= $cc;
				
				while ($q->comments->hasMore()) {
					list($cid, $c) = $q->comments->getComment();
					$comments['words'] += str_word_count($c['content']);
					$comments['chars'] += strlen($c['content']);
					$comments['size']  += filesize(comment_exists($id, $cid));
				}
				
			}
			
			arsort($toplist);
			
			$i = 0;
			foreach($toplist as $k=>$v) {
				if ($i>=10 || $v < 1)
					break;
					
				$entries['topten'][$k] = array(
					'subject' => $toplistsubj[$k], 
					'comments' => $v
				);
				$i++;
			}
			
			$decunit = array('', 'Thousand', 'Million', 'Billion', 'Trillion', 'Zillion', 'Gazillion');
			$binunit = array('Bytes', 'KiloBytes', 'MegaBytes', 'GigaBytes', 'TeraBytes', 'Many', 'ManyBytes');
			
			
			list($count, $approx) = $this->format_number($entries['count'], 1000);
			$entries['count'] = $count .' '. $decunit[$approx];
			
			list($count, $approx) = $this->format_number($entries['words'], 1000);
			$entries['words'] = $count .' '. $decunit[$approx];
			
			list($count, $approx) = $this->format_number($entries['chars'], 1000);
			$entries['chars'] = $count .' '. $decunit[$approx];
			
			list($count, $approx) = $this->format_number($entries['comments'], 1000);
			$entries['comments'] = $count .' '. $decunit[$approx];
			
			list($count, $approx) = $this->format_number($entries['size'], 1024);
			$entries['size'] = $count .' '. $binunit[$approx];
			
			
			$this->smarty->assign('entries', $entries);
			
			
			
			list($count, $approx) = $this->format_number($comments['count'], 1000);
			$comments['count'] = $count .' '. $decunit[$approx];
			
			list($count, $approx) = $this->format_number($comments['words'], 1000);
			$comments['words'] = $count .' '. $decunit[$approx];
			
			list($count, $approx) = $this->format_number($comments['chars'], 1000);
			$comments['chars'] = $count .' '. $decunit[$approx];
			
			list($count, $approx) = $this->format_number($comments['size'], 1024);
			$comments['size'] = $count .' '. $binunit[$approx];
			
			
			$this->smarty->assign('comments', $comments);
		
		}
			
	}
