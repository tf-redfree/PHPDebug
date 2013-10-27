<?php
/**
 * 
 */

/**
 * 
 */

if(isset($_______PHPDebugSETTINGS) === true) {
    die("Cannot setup PHPDebug for you, as a variable called \$_______PHPDebugSETTINGS is already set. are you using include/require? use require_once instead");
}
if(isset($_______PHPDebug) === true) {
    die("Cannot setup PHPDebug for you, as a variable called \$_______PHPDebug is already set. are you using include/require? use require_once instead");
}
require_once('PHPDebug.conf.php');
require_once('PHPDebug.internalexceptions.php');
/**
 * 
 */
class PHPDebug {
    /**
     *   List of variables we expect as input
     */
    protected $expectedVariables = array(
        0 => array('varname' => 'error_handler_use', 'stopifmissing' => false, 'default' => true),
        1 => array('varname' => 'error_handler_e_fatal_use', 'stopifmissing' => false, 'default' => true),
        2 => array('varname' => 'excep_handler_use', 'stopifmissing' => false, 'default' => true),
        3 => array('varname' => 'error_handler_types', 'stopifmissing' => false, 'default' => E_ALL),
        4 => array('varname' => 'error_handler_buffer', 'stopifmissing' => false, 'default' => true),
        5 => array('varname' => 'error_handler_stop_on_error', 'stopifmissing' => false, 'default' => false),
    );
    
    /**
     * List of variables we read in from settings
     */
    protected $error_handler_use = null;
    protected $error_handler_e_fatal_use = null;
    protected $excep_handler_use = null;
    protected $error_handler_types = null;
    protected $error_handler_buffer = null;
    protected $error_handler_stop_on_error = null;
    
    protected $error_buffer = array(
        'error_count' => 0,
        'error_worst' => 0,
        'error_buffer'
    );
    /**
     * PHPDebug Constructor
     * @param array $settings
     * @throws PHPDebugSetupRuntimeException 
     * TODO: Might be helpful if we cleanse the input? are we expecting a boolean etc.
     */
    public function __construct(array $settings) {
        
        // Loop through expected variables and check we have them in the input
        foreach($this->expectedVariables as $variable) {
            if(isset($settings[$variable['varname']]) === false) {
                // Variable is not specified in input, check if we must halt on missing (Y / N) and either throw a RTExc or set to default value
                if($variable['stopifmissing'] === true) {
                    throw new PHPDebugSetupRuntimeException("PHPDebug:  ERROR, Missing input variable: '{$variable['varname']}'.".PHP_EOL);
                } else {
                    $this->$variable['varname'] = $variable['default'];
                    trigger_error("PHPDebug: WARNING, Missing input variable: '{$variable['varname']}', using default value.".PHP_EOL);
                }
            } else {
                // Was specified in input
                $this->$variable['varname'] = $settings[$variable['varname']];
            }
        }
        
        set_error_handler(array($this, 'error_handler'), E_ALL);
    }
    
    
    public function error_handler($error_severity, $error_message, $err_file, $err_line, array $err_contextInformation) {
        $this->error_appendBuffer($error_severity, $error_message, $err_file, $err_line, $err_contextInformation);
        if($this->error_handler_stop_on_error === true) {
            
        } else {
            throw new PHPDebugInvalidSettingException("PHPDebug: ERROR, Invalid Setting specified in 'error_handler_stop_on_error'.".PHP_EOL);
        }
    }
    
    protected function error_appendBuffer($error_severity, $error_message, $err_file, $err_line, array $err_contextInformation) {
        
    }
    
    
    
}

$_______PHPDebug = new PHPDebug($_______PHPDebugSETTINGS);
unset($_______PHPDebugSETTINGS);
?>
