<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter Class
 *
 * Allows for methods of controller to be called immediately before or after an action is executed
 *      before_filter executes immediately after controller constructor is called
 *      after_filter executes immediately after the action has executed
 * 
 * Mimics basic functionality of Rails filters.
 *
 *
 * Keep it DRY!
 *
 * @package     CodeIgniter
 * @subpackage  Hooks
 * @category    Hooks
 * @author      Matthew Machuga
 * @copyright   Copyright (c) 2010 Matthew Machuga
 * @version     0.0.1
 */
 
 /**
  * Example usage in Controller for before or after filters:
  * 
  * In variable definition with single action:
  * 
  * var $before_filter = array( 
  *     'action' => 'name_of_method_for_before_filter',
  *     'except' => array('index', 'logout');
  * );
  *
  * - or -
  * 
  * In constructor with several actions:
  * 
  * var $before_filter = array();
  *
  * function __construct() {
  *     parent::Controller();
  *     $this->before_filter[] = array(
  *         'action' => 'name_of_method_for_before_filter',
  *         'only' => array('login');
  *     );
  * }
  *
  * Note: If both 'except' and 'only' are defined on the same action and filter, 'except' will be ignored and an error will be logged
  *
  */
 
 
class Filter {
    
    var $CI, $class, $action;
    
    function __construct() {
        $this->CI =& get_instance();
        $this->class = $this->CI->router->fetch_class();        // Get class
        $this->action = $this->CI->router->fetch_method();      // Get action
    }
    
    public function before_filter() {
        // Verify existence of $before_filter inside the global (defined in Controller)
        if (isset($this->CI->before_filter) && !empty($this->CI->before_filter)) {
            $this->filter($this->CI->before_filter);
        }
    }
    
    public function after_filter() {
        // Verify existence of $after_filter inside the global (defined in Controller)
        if (isset($this->CI->after_filter) && !empty($this->CI->after_filter)) {
            $this->filter($this->CI->after_filter);
        }
    }
    

    private function filter(array &$filter) {
        if ($this->is_assoc($filter)) {
            $this->run_filter($filter);
        } else {
            // Allow multiple actions
            foreach ($filter as $action) {
                $this->run_filter($action);
            }
        }
    }
    
    private function run_filter(array &$filter) {
        // Filter must have valid 'action' key defined
        if ($this->is_valid_action($filter['action'])) {
            $call_action = &$filter['action'];
            
            // Set Flags
            $only = (isset($filter['only']) && !empty($filter['only']));
            $except = (!$only && isset($filter['except']) && !empty($filter['except']));
            
            // For logging purposes - only and except can not exist simultaneously.  Log if they do - except will be ignored.
            if ($only && isset($filter['except']) && !empty($filter['except'])) {
                log_message('error', 'Only and Except are both set in filter with action ' . $filter['action']);
            }
            
            if ($only && in_array($this->action, $filter['only'])) {
                $this->CI->$call_action();
            } elseif ($except && !in_array($this->action, $filter['except'])) {
                $this->CI->$call_action(); 
            } elseif (!$only && !$except) {
                $this->CI->$call_action();
            }
        } else {
            log_message('error', $filter['action'] . ' is not a valid action.  Ignoring in filter.');
        }
    }
    
    private function is_assoc(array $array = array()) {
        return (array_keys($array) !== range(0, count($array) - 1)); 
    }
    
    private function is_valid_action($action_name) {
        return ( $action_name && $action_name != '' && method_exists($this->CI, $action_name) );
    }
    
} // End of Filter class

/* End of file Filter.php */
/* Location: ./application/hooks/Filter.php */
