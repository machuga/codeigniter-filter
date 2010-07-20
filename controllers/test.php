<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * Test functionality of Filter class
 *
 */

class Test extends Controller {
    
    var $before_filter = array();
    
    var $after_filter = array();
        
    function __construct() {
        parent::Controller();
        $this->before_filter[] = array(
            'action' => 'before_filter_run',
            'except' => array('home')
        );
        $this->after_filter[] = array(
            'action' => 'after_filter_run',
            'only' => array('sent_away')
        );
    }
    
    function index() {
        echo 'You made it to the index';
    }
    
    function home() {
        echo 'This is home<br/>';
        echo 'You may try going to the '.anchor('/test/index/', 'index'). ' of this controller.<br/>';
        echo 'The before_filter will execute the before_filter_run method before the controller action is executed.<br/>';
        echo 'It is demonstrated by having before_filter_run redirect to sent_away if the action is "index" <br/>';
    }
    
    function sent_away() {
        echo "You've been sent here by the before_filter_run action, called by the before_filter!<br/>";
        echo anchor('/test/home/', 'Click here to return to the test controller home');
    }
    
    function before_filter_run() {
        $filter = array('index');
        if ( in_array($this->router->fetch_method(), $filter) ) {
            redirect('/test/sent_away/');
        } else {
            return true;
        }
    }
    
    function after_filter_run() {
		echo '<br/>This text is generated from the after_filter_run method';
		echo '<br/>Note: To prevent direct access to methods used solely as filters, include the following lines:<br/>';
		echo '<code>if ($this->router->fetch_method() == \'after_filter_run\') {<br/>';
		echo "\t".'show_404();<br/>';
		echo '}<br/>';
    }
}

/* End of file test.php */
/* Location: ./application/controllers/test.php */
