<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter-Filter
 *
 * This controller allows for methods to be called immediately before or after
 * an action is executed.
 *
 * @package     CodeIgniter-Filter
 * @version     0.0.2
 * @author      Matthew Machuga
 * @license     MIT License
 * @copyright   2011 Matthew Machuga
 * @link        http://github.com/machuga/codeigniter-filter/
 *
 */

class MY_Controller extends CI_Controller {

    protected $before_filter   = array(
        // Example
        // 'action'    => 'redirect_if_not_logged_in',
    );

    protected $after_filter    = array();

    // Utilize _remap to call the filters at respective times
    public function _remap($method, $params = array())
    {
        if (!$this->before_filter())
        {
            log_message('debug', "before_filter failed, returning early");
        	return false;
        }

        log_message('debug', "before_filter succeeded, continuing");
        
        $RTR =& load_class('Router', 'core');
        $class = get_class($this);
        $controller = NULL;
        
        if (method_exists($this, $method))
        {
        	$controller = $this;
        }
		// Check and see if we are using a 404 override and use it.
        else if (!empty($RTR->routes['404_override']))
		{
			$x = explode('/', $RTR->routes['404_override']);
			$class = $x[0];
			$method = (isset($x[1]) ? $x[1] : 'index');
			
			if (class_exists($class))
			{
				$controller = new $class();
			}
			else if (file_exists(APPPATH.'controllers/'.$class.'.php'))
			{
				include_once(APPPATH.'controllers/'.$class.'.php');
				$controller = new $class();
			}
		}

		if ($controller)
		{
			empty($params) ? $controller->{$method}() :
				call_user_func_array(array($controller, $method), $params);
		}
		else
		{
			show_404("{$class}/{$method}");
		}

        $this->after_filter();
    }

    // Allows for before_filter and after_filter to be called without aliases
    public function __call($method, $args)
    {
        if (in_array($method, array('before_filter', 'after_filter')))
        {
            if (isset($this->{$method}) && ! empty($this->{$method}))
            {
                $result = $this->filter($method, isset($args[0]) ? $args[0] : $args);
	            log_message('debug', get_called_class()."::{$method}".
	            	" result = $result");
                return $result;
            }
            else
            {
            	// If no filters are configured, treat it as success.
            	return true;
            }
        }
        else
        {
            log_message('error', "Call to nonexistent method ".get_called_class()."::{$method}");
            return false;
        }
    }

    // Begins processing filters
    protected function filter($filter_type, $params)
    {
        $called_action = $this->router->fetch_method();

        if ($this->multiple_filter_actions($filter_type))
        {
            foreach ($this->{$filter_type} as $filter)
            {
                if (!$this->run_filter($filter, $called_action, $params))
                {
                	return false;
                }
            }
        }
        else
        {
            if (!$this->run_filter($this->{$filter_type}, $called_action, $params))
            {
            	return false;
            }
        }
        
        return true;
    }

    // Determines if the filter method can be called and calls the requested 
    // action if so, otherwise returns false
    protected function run_filter(array &$filter, $called_action, $params)
    {
        if (method_exists($this, $filter['action']))
        {
            // Set flags
            $only = isset($filter['only']);
            $except = isset($filter['except']);

            if ($only && $except) 
            {
                log_message('error', "Only and Except are not allowed to be set simultaneously for action ({$filter['action']} on ".$this->router->fetch_method().".)");
                return false;
            }
            elseif ($only && in_array($called_action, $filter['only'])) 
            {
                $result = empty($params) ? $this->{$filter['action']}() : $this->{$filter['action']}($params);
            }
            elseif ($except && ! in_array($called_action, $filter['except'])) 
            {
                $result = empty($params) ? $this->{$filter['action']}() : $this->{$filter['action']}($params);
            }
            elseif ( ! $only && ! $except) 
            {
                $result = empty($params) ? $this->{$filter['action']}() : $this->{$filter['action']}($params);
            }

            return $result;
        }
        else
        {
            log_message('error', "Invalid action {$filter['action']} given to filter system in controller ".get_called_class());
            return false;
        }
    }

    protected function multiple_filter_actions($filter_type) 
    {
        return ! empty($this->{$filter_type}) && array_keys($this->{$filter_type}) === range(0, count($this->{$filter_type}) - 1);
    }

    /*
     *
     * Example callbacks for filters
     * Callbacks can optionally have one parameter consisting of the
     * parameters passed to the called action.
     *
     */

    protected function redirect_if_logged_in()
    {
        $this->load->library('Authentic');
        if ($this->authentic->logged_in())
        {
            redirect(base_url());
        }
    }

    protected function redirect_if_not_logged_in()
    {
        $this->load->library('Authentic');
        if ( ! $this->authentic->logged_in())
        {
            redirect(site_url('login'));
        }
    }
}
