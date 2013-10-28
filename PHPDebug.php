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
     *  
     */
    protected $error_lookup = array(
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE', /*should never be seen*/
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED',
        E_ALL => 'E_ALL'
    );
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
        6 => array('varname' => 'PHPDebug_CLI', 'stopifmissing' => true, 'default' => null),
        7 => array('varname' => 'HTMLo_font_color_error', 'stopifmissing' => false, 'default' => '#ff0000'),
        8 => array('varname' => 'HTMLo_font_color_normal', 'stopifmissing' => false, 'default' => 'inherit')
    );
    /**
     *  Error Buffer Container
     */
    protected $error_buffer = array(
        'error_count' => 0,
        'error_worst' => 0,
        'error_buffer' => ''
    );
    
    
    /**
     * List of variables we read in from settings
     */
    protected $error_handler_use = null;
    protected $error_handler_e_fatal_use = null;
    protected $excep_handler_use = null;
    protected $PHPDebug_CLI = null;
    protected $error_handler_types = null;
    protected $error_handler_buffer = null;
    protected $error_handler_stop_on_error = null;
    
    // ------------------- Setup Handler ------------------- //
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
        set_exception_handler(array($this, 'excep_handler'));
        register_shutdown_function(array($this, 'phpdebug_shutdown_function'));
    }
    
    // ------------------- Error Handler ------------------- //
    /**
     * Error handler for PHPDebug
     * @param var $error_severity
     * @param var $error_message
     * @param var $err_file
     * @param var $err_line
     * @param array $err_contextInformation
     * @throws PHPDebugInvalidSettingException 
     */
    public function error_handler($error_severity, $error_message, $err_file, $err_line, array $err_contextInformation) {
        $this->error_appendBuffer($error_severity, $error_message, $err_file, $err_line, $err_contextInformation);
        if($this->error_handler_stop_on_error === true) {
            // Stop Execution now
            die("Execution stopped on request (`error_handler_stop_on_error` === true)".PHP_EOL);
        } elseif($this->error_handler_stop_on_error === false) { 
            // Nothing
        } else {
            throw new PHPDebugInvalidSettingException("PHPDebug: ERROR, Invalid Setting specified in 'error_handler_stop_on_error'.".PHP_EOL);
        }
    }
    
    /**
     * Append given error to the error output buffer
     * @param var $error_severity
     * @param var $error_message
     * @param var $err_file
     * @param var $err_line
     * @param array $err_contextInformation 
     */
    protected function error_appendBuffer($error_severity, $error_message, $err_file, $err_line, array $err_contextInformation) {
        $this->error_buffer['error_count']++;
        //$this->error_buffer['error_worst'] = ?? //TODO: Implement Later
        $errorList = $this->error_geterrors($error_severity);
        
        if($this->PHPDebug_CLI === true) {
            $this->error_buffer['error_buffer'] .= "|-- Error Number\t: {$this->error_buffer['error_count']}".PHP_EOL;
            $this->error_buffer['error_buffer'] .= "|-- File:Line\t\t: {$err_file}:{$err_line}".PHP_EOL;
            $this->error_buffer['error_buffer'] .= "|-- Flags\t\t: {$errorList}".PHP_EOL;
            $this->error_buffer['error_buffer'] .= "|-- Message\t\t: {$error_message}".PHP_EOL;
            $this->error_buffer['error_buffer'] .= PHP_EOL;
            unset($err_contextInformation['_______PHPDebug']);
            //$this->error_buffer['error_buffer'] .= "|-- Context\t\t\t: ".print_r($err_contextInformation, true).PHP_EOL;
        } elseif($this->PHPDebug_CLI === false) {
$this->error_buffer['error_buffer'] .= <<<EOB
        <tr>
            <td style="width: 10%; font-weight: bold; color: {$this->HTMLo_font_color_normal}">{$this->error_buffer['error_count']}</td>
            <td style="width: 40%; font-weight: bold; color: {$this->HTMLo_font_color_normal}">{$err_file}:<i>{$err_line}</i></td>
            <td style="width: 10%; font-style: italic; color: {$this->HTMLo_font_color_normal}">{$errorList}</td>
            <td style="width: 40%; color: {$this->HTMLo_font_color_normal}"><i>{$error_message}</i></td>
        </tr>
EOB;
        } else {
            throw new PHPDebugInvalidSettingException("PHPDebug: ERROR, Invalid Setting specified in 'PHPDebug_CLI'.".PHP_EOL);
        }
    }
    
    /**
     * Convert the errors into a string for display
     * @param type $error_severity
     * @return string 
     */
    protected function error_geterrors($error_severity) {
        $str = "";
        foreach($this->error_lookup as $key => $value) {
            if($key === E_ALL) { continue; }
            if(($key & $error_severity) == $key) {
                $str .= $value."({$key}); ";
            }
        }
        return $str;
    }
    
    // ------------------- Excep Handler ------------------- //
    public function excep_handler(Exception $exception) {
        $eol = '';
        //$eol = PHP_EOL;
        //print_r($exception);
        echo <<<EOF
PHPDebug: Execution was halted due to Uncaught Exception{$eol}
|-- File:line\t\t: {$exception->getFile()}:{$exception->getLine()}{$eol}
|-- Message\t\t: {$exception->getMessage()}{$eol}
{$eol} 
EOF;
    }
    
    // ------------------- Outpt Handler ------------------- //
    /**
     * PHPDebug Shutdown Function
     */
    public function phpdebug_shutdown_function() {
        echo "\n\nList of detected errors\n";
        echo $this->error_buffer['error_buffer'];
    }
    
}

$_______PHPDebug = new PHPDebug($_______PHPDebugSETTINGS);
unset($_______PHPDebugSETTINGS);
?>
