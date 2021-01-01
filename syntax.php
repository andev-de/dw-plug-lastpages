<?php
//	Last Change 2021-01-01

if(!defined('DOKU_INC')) {
    define ('DOKU_INC', realpath(dirname(__FILE__).'/../../').'/');
}
if(!defined('DOKU_PLUGIN')) {
    define ('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');
}

require_once (DOKU_PLUGIN.'syntax.php');
require_once (DOKU_INC.'inc/search.php');
require_once (DOKU_INC.'inc/pageutils.php');

/**
 * The main LASTPAGES plugin class...
 */
class syntax_plugin_lastpages extends DokuWiki_Syntax_Plugin {
	var $indexdir = '';
	var $pages = array();
	var $titles = array();

    /**
     * Constructor
     */
    function syntax_plugin_lastpages() {
    	global $conf;

		$this->indexdir = $conf['indexdir'];
    }

    /**
     * return some info
     */
    function getInfo() {
    }

    /**
     * Type of syntax plugin
     */
    function getType() {
        return "substition";
    }

    /**
     * Just before build in links
     */
    function getSort() {
        return 299;
    }

    /**
     * Register supported keywords
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~LAST10~~', $mode, 'plugin_lastpages');
        $this->Lexer->addSpecialPattern('~~LAST5~~', $mode, 'plugin_lastpages');
        $this->Lexer->addSpecialPattern('~~LIST10~~', $mode, 'plugin_lastpages');
        $this->Lexer->addSpecialPattern('~~LIST5~~', $mode, 'plugin_lastpages');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
    	return preg_replace("%~~LAST10~~%", "\\2", $match);
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data) {
		// set options
		switch ($data) {
			case '~~LIST5~~':	$number_of_pages = 5;	$type_of_list = 'list'; break;
			case '~~LIST10~~':	$number_of_pages = 10;	$type_of_list = 'list'; break;
			case '~~LAST5~~':	$number_of_pages = 5;	$type_of_list = 'inline'; break;
			default:
			case '~~LAST10~~':	$number_of_pages = 10;	$type_of_list = 'inline'; break;
		}

    	$this->_getLatestPages($number_of_pages);			// get list of latest pages
    	$data = $this->_formatLatestPages($type_of_list);	// format list of pages
    	$renderer->doc .= $data;							// append data to the output stream
    	return true;
    }

	function _debugArray(&$arr) {
		echo highlight_string(print_r($arr, true));
	}

	function _getLatestPages($number_of_pages) {
		$index_links_file = $this->indexdir.'/page.idx';
		$index_title_file = $this->indexdir.'/title.idx';

		$index_links = file($index_links_file);
		$index_title = file($index_title_file);

		$this->pages  = array_reverse(array_slice($index_links, ($number_of_pages * -1), $number_of_pages));
		$this->titles = array_reverse(array_slice($index_title, ($number_of_pages * -1), $number_of_pages));
	}

	function _formatLatestPages($type_of_list) {
		$ret = '';

		if (is_array($this->pages) && (count($this->pages) > 0)) {
			if ($type_of_list == 'list')
				$ret .= '<ul>';
			foreach ($this->pages as $id => $page) {
				$title = $this->titles[$id];
				if ($type_of_list == 'list')
					$ret .= '<li>'.html_wikilink(trim($page), $title).'</li>';
				else
					$ret .= $sep.html_wikilink(trim($page), $title);
				$sep = ' &bull; ';
			}
			if ($type_of_list == 'list')
				$ret .= '</ul>';
		}

		return $ret;
	}

} // syntax_plugin_lastpages
