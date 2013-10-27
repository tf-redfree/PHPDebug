<?php
    /**
     * Debugger settings array 
     */
    $_______PHPDebugSETTINGS = array(
        
        /**
         * These variables:
         * Turn on and off functionality of PHPDebug 
         */
        // should PHP Debug handle error types specified in '' (these types of errors are notices etc, not exceptions)
        'error_handler_use' => true,
        // should PHP Debug handle E_FATAL? * - PHP >5.2
        'error_handler_e_fatal_use' => true,
        // should PHP Debug handle uncaught exceptions for you?
        'excep_handler_use' => true,
        
        /**
         * These variables:
         * affect the behaviour which PHPDebug has when executing 
         */
        // Output the information in a command-line friendly way (when false, it will output HTML)
        'PHPDebug_CLI' => true,
        // Which error types should debugger catch? specify this is you want to catch a certain type yourself and define an error handler for it
        'error_handler_types' => E_ALL,             // Note: We do not handle E_FATAL in this part of PHPDebug, see: 'error_handler_e_fatal_use'
        // Store a buffer of errors and print it out last (ignored if 'error_handler_stop_on_error' is true)
        'error_handler_buffer' => true,
        // Stop the php program if any errors (which are designated to PHPDebug, see 'error_handler_types' are on the page)
        'error_handler_stop_on_error' => false,     // Note: Exceptions ALWAYS halt execution
    );
?>
