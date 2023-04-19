<?php	//	Last Change 2023-04-19

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

    /**
     * Constructor
     */
    function __construct() {
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
    	$this->Lexer->addSpecialPattern('~~LASTPAGES~~', $mode, 'plugin_lastpages');
        $this->Lexer->addSpecialPattern('~~LAST10~~', $mode, 'plugin_lastpages');
        $this->Lexer->addSpecialPattern('~~LAST5~~', $mode, 'plugin_lastpages');
        $this->Lexer->addSpecialPattern('~~LIST10~~', $mode, 'plugin_lastpages');
        $this->Lexer->addSpecialPattern('~~LIST5~~', $mode, 'plugin_lastpages');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
    	return $match;
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
			case '~~LAST10~~':	$number_of_pages = 10;	$type_of_list = 'inline'; break;
			default:
			case '~~LASTPAGES~~': $number_of_pages = $this->getConf('numpages'); $type_of_list = $this->getConf('displist'); break;
		}

		$separator = $this->getConf('listsep');
		if (empty($separator))
			$separator = '&bull;';

    	$this->_getLatestPages($number_of_pages);			// get list of latest pages
    	$docdata = $this->_formatLatestPages($type_of_list, $separator);	// format list of pages
    	$renderer->doc .= $docdata;							// append data to the output stream
    	return true;
    }

	function _debugArray(&$arr) {
		echo highlight_string(print_r($arr, true));
	}

	function _getLatestPages($number_of_pages) {
		$index_links_file = $this->indexdir.'/page.idx';
		$index_title_file = $this->indexdir.'/title.idx';
		$index_pword_file = $this->indexdir.'/pageword.idx';

		$index_links = file($index_links_file);
		$index_title = file($index_title_file);
		$index_pword = file($index_pword_file);

		if (!is_array($index_links))
			return;
		if (!is_array($index_title))
			return;
		if (!is_array($index_pword))
			return;

		$pages     = array_reverse($index_links);
		$titles    = array_reverse($index_title);
		$pagewords = array_reverse($index_pword);
		$cnt       = 0;

		foreach ($pages as $id => $page) {
			if (strlen($pagewords[$id]) > 2) {
				$key = trim($page);
				$title = trim($titles[$id]);
				if (!empty($title))
					$this->pages[$key] = $title;
				else
					$this->pages[$key] = $key;
				$cnt++;
				if ($cnt == $number_of_pages)
					break;
			}
		}
	}

	function _formatLatestPages($type_of_list, $separator) {
		$ret = '';

		if (is_array($this->pages) && (count($this->pages) > 0)) {
			if ($type_of_list == 'list')
				$ret .= '<ul>';
			foreach ($this->pages as $key => $title) {
				if ($type_of_list == 'list')
					$ret .= '<li>'.html_wikilink($key, $title).'</li>';
				else
					$ret .= $sep.html_wikilink($key, $title);
				$sep = ' '.$separator.' ';
			}
			if ($type_of_list == 'list')
				$ret .= '</ul>';
		}

		return $ret;
	}

} // syntax_plugin_lastpages
