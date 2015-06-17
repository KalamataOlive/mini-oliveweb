<?php
    //START MINI-OLIVEWEB
    /*
     * Mini-OliveWeb 1.0.0
     * A Single-page OliveWeb Solution
     * OliveTech, June 2015
     * A Luke Bullard Project
     */
    //the INPROCESS constant is checked by other scripts
    //to verify that they are not being accessed directly
    define("INPROCESS", TRUE);
    
    //execution state constants
    define("EXESTATE_ERROR", -1);
    define("EXESTATE_NOT_RAN", 0);
    define("EXESTATE_INIT", 1);
    define("EXESTATE_RUN", 2);
    define("EXESTATE_DONE", 3);
    
    //olive core super static class
    class Olive
    {
        //private constructor and clone method to disable instantiation
        private function __construct(){}
        private function __clone(){}
        
        //execution state. based on the EXESTATE_* constants
        private static $m_exeState = EXESTATE_NOT_RAN;
        
        //base URL
        private static $m_baseURL;
        
        //request parameters (from URL)
        private static $m_requestParams;
        
        //return the base URL of the site
        public static function baseURL()
        {
            return self::$m_baseURL;
        }
        
        //return the URL request param at the given index
        public static function requestParam($a_index)
        {
            //make sure
            // 1. the request params are set
            // 2. the request params variable is an array
            // 3. the index exists (the count is greater than the index, since index '0' == count '1')
            if (!isset(self::$m_requestParams) ||
                !is_array(self::$m_requestParams) ||
                !(count(self::$m_requestParams) > $a_index))
            {
                //one or more of the conditions isn't met. return an empty string
                return "";
            }
            
            //looking good. return the request param
            return self::$m_requestParams[$a_index];
        }
        
        //'main' execution static function
        public static function initialize()
        {
            //make sure we haven't already ran...
            if (self::$m_exeState != EXESTATE_NOT_RAN)
            {
                //this function shouldn't run more than once.
                //abandon function
                return;
            }
            
            //set the state to initializing
            self::$m_exeState = EXESTATE_INIT;
            
            //set up the base url constant:
            //1. remove any trailing forward slashes from the end of the
            //   current script's directory name
            //2. append just one forward slash to this new string
            self::$m_baseURL = rtrim(str_ireplace("\\","/",dirname($_SERVER['SCRIPT_NAME'])),"/") . "/";
            
            //set up the route request
            $t_routeRequest = (isset($_GET['p']) ? rtrim($_GET['p']," /") : "");
            
            //split the request params up into an array, then store them
            //in a private static member variable of this class
            self::$m_requestParams = explode("/",$t_routeRequest);
            
            //set the state to running
            self::$m_exeState = EXESTATE_RUN;
        }
    }
    
    //Modules registry defined error codes
    define("ERR_MOD_EXISTS",-1); //the module to be loaded already exists
    define("ERR_MOD_NOTFOUND",-2); //the module to be loaded was not found
    define("ERR_MOD_NOINTERFACE",-3); //the class for the module was not found in it's source
    
    //Modules singleton registry class
    class Modules implements ArrayAccess
    {
        //static instance for singleton
        private static $m_instance = null;
        
        //the actual modules registry array
        private $m_registry = array();
        
        //private constructor and clone method to disable outside instantiation
        private function __construct(){}
        private function __clone(){}
        
        //static function to get the instance of the Modules class
        public static function getInstance()
        {
            //if the instance doesn't exist, make it
            if (self::$m_instance === null)
            {
                self::$m_instance = new Modules();
            }
            
            return self::$m_instance;
        }
        
        //load a new module instance
        public function load($a_key)
        {
            //strtolower the module key, so that the keys are case-insensitive
            $t_cleanKey = strtolower($a_key);
            
            //make sure something for the key doesn't already exist
            if (isset($this->m_registry[$t_cleanKey]))
            {
                //something already exists. return the error code
                return ERR_MOD_EXISTS;
            }
            
            //filesystem path to module's main file
            $t_modulePath = "modules/" . $t_cleanKey . "/" . $t_cleanKey . ".mod.php";
            
            //confirm the module exists in the modules directory
            if (!file_exists($t_modulePath))
            {
                return ERR_MOD_NOTFOUND;
            }
            
            //include_once the module file
            include_once($t_modulePath);
            
            //class name for the module
            $t_moduleClass = "MOD_" . $t_cleanKey;
            
            //make sure the module's class exists
            if (!class_exists($t_moduleClass))
            {
                return ERR_MOD_NOINTERFACE;
            }
            
            //instantiate the module, then store the instance in the registry
            $this->m_registry[$t_cleanKey] = new $t_moduleClass();
            return true;
        }
        
        //get a module instance from the registry
        public function get($a_key)
        {
            //strtolower the module key, so that the keys are case-insensitive
            $t_cleanKey = strtolower($a_key);
            
            //make sure the key exists
            if (!isset($this->m_registry[$t_cleanKey]))
            {
                //it doesn't exist. try to load it
                if (!$this->load($t_cleanKey))
                {
                    //no module able to load with that name either.
                    //return false signifying error
                    return false;
                }
            }
            
            return $this->m_registry[$t_cleanKey];
        }
        
        //ArrayAccess methods
        public function offsetExists($a_key)
        {
            //strtolower the module key
            $t_cleanKey = strtolower($a_key);
            
            //return whether or not the key exists at all
            return isset($this->m_registry[$t_cleanKey]);
        }
        
        public function offsetGet($a_key)
        {
            //strtolower the module key
            $t_cleanKey = strtolower($a_key);
            
            //return the module if it was sent from the get() method,
            //return null otherwise (ie. when it returned false)
            $t_possibleModule = $this->get($a_key);
            if ($t_possibleModule !== false)
            {
                return $t_possibleModule;
            }
            return null;
        }
        
        //no offsetSet or offsetUnset, since those should be kept
        //safe for the sake of all the modules lifespans
        public function offsetSet($a_key,$a_value){}
        public function offsetUnset($a_key){}
    }
    //END MINI-OLIVEWEB
?>