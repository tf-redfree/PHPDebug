<?php
/**
 * 
 */

/**
 * This file breaks the "one class per file" constraint that is often enforced,
 * but this is the sanest way to define these exceptions that have no body with lots of requires! 
 */

interface PHPDebugInternalException {}

class PHPDebugSetupRuntimeException extends Exception implements PHPDebugInternalException {}
class PHPDebugInvalidSettingException extends Exception implements PHPDebugInternalException {}
?>
