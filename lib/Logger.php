<?php
/**
 * Logger Class
 * 
 * A simple PSR-3 compatible logger implementation
 */

namespace Bingetv\Lib;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use DateTime;
use DateTimeZone;

class Logger extends AbstractLogger {
    /**
     * @var array Log levels with their severity
     */
    private $levels = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7,
    ];
    
    /**
     * @var string The minimum logging level
     */
    private $minLevel;
    
    /**
     * @var string The log file path
     */
    private $logFile;
    
    /**
     * @var resource The file handle
     */
    private $fileHandle;
    
    /**
     * @var array Log entries in memory (for testing)
     */
    private $logEntries = [];
    
    /**
     * @var bool Whether to log to memory (for testing)
     */
    private $logToMemory = false;
    
    /**
     * @var bool Whether to output logs to the browser (for debugging)
     */
    private $outputToBrowser = false;
    
    /**
     * @var Logger Singleton instance
     */
    private static $instance = null;
    
    /**
     * Get the singleton instance
     * 
     * @param string $logFile Path to the log file
     * @param string $minLevel Minimum log level
     * @return Logger
     */
    public static function getInstance($logFile = null, $minLevel = LogLevel::DEBUG) {
        if (self::$instance === null) {
            self::$instance = new self($logFile, $minLevel);
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     * 
     * @param string $logFile Path to the log file
     * @param string $minLevel Minimum log level
     */
    private function __construct($logFile = null, $minLevel = LogLevel::DEBUG) {
        $this->minLevel = $minLevel;
        $this->logFile = $logFile ?: (__DIR__ . '/../../logs/app_' . date('Y-m-d') . '.log');
        
        // Create the log directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // In development, output logs to the browser
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $this->outputToBrowser = true;
        }
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of the instance
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
    
    /**
     * Set the log file path
     * 
     * @param string $logFile Path to the log file
     * @return self
     */
    public function setLogFile($logFile) {
        $this->logFile = $logFile;
        return $this;
    }
    
    /**
     * Set the minimum log level
     * 
     * @param string $level The minimum log level
     * @return self
     */
    public function setMinLevel($level) {
        if (!isset($this->levels[$level])) {
            throw new \InvalidArgumentException("Invalid log level: $level");
        }
        $this->minLevel = $level;
        return $this;
    }
    
    /**
     * Enable or disable logging to memory
     * 
     * @param bool $enabled Whether to log to memory
     * @return self
     */
    public function logToMemory($enabled = true) {
        $this->logToMemory = $enabled;
        return $this;
    }
    
    /**
     * Enable or disable output to browser
     * 
     * @param bool $enabled Whether to output to browser
     * @return self
     */
    public function outputToBrowser($enabled = true) {
        $this->outputToBrowser = $enabled;
        return $this;
    }
    
    /**
     * Get all log entries (only when logging to memory)
     * 
     * @return array Array of log entries
     */
    public function getLogEntries() {
        return $this->logEntries;
    }
    
    /**
     * Clear all log entries (only when logging to memory)
     * 
     * @return self
     */
    public function clearLogEntries() {
        $this->logEntries = [];
        return $this;
    }
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level   The log level
     * @param string $message The log message
     * @param array  $context The log context
     * @return void
     */
    public function log($level, $message, array $context = []) {
        // Check if the log level is enabled
        if (!$this->isLevelEnabled($level)) {
            return;
        }
        
        // Format the message with context
        $formattedMessage = $this->formatMessage($level, $message, $context);
        
        // Log to file
        if (!$this->logToMemory) {
            $this->writeToFile($formattedMessage);
        }
        
        // Log to memory
        if ($this->logToMemory) {
            $this->logEntries[] = [
                'timestamp' => $this->getTimestamp(),
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'formatted' => $formattedMessage
            ];
        }
        
        // Output to browser (for debugging)
        if ($this->outputToBrowser && !headers_sent()) {
            echo '<pre>' . htmlspecialchars($formattedMessage) . '</pre>';
        }
    }
    
    /**
     * Check if the given log level is enabled
     * 
     * @param string $level The log level to check
     * @return bool True if enabled, false otherwise
     */
    public function isLevelEnabled($level) {
        if (!isset($this->levels[$level])) {
            throw new \InvalidArgumentException("Invalid log level: $level");
        }
        
        return $this->levels[$level] <= $this->levels[$this->minLevel];
    }
    
    /**
     * Format the log message with context
     * 
     * @param string $level The log level
     * @param string $message The log message
     * @param array $context The log context
     * @return string The formatted log message
     */
    protected function formatMessage($level, $message, array $context = []) {
        $timestamp = $this->getTimestamp();
        $level = strtoupper($level);
        
        // Interpolate context values into the message
        $message = $this->interpolate($message, $context);
        
        // Add the context as JSON if not empty
        $contextStr = '';
        if (!empty($context)) {
            $contextStr = ' ' . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        
        return sprintf(
            "[%s] %s: %s%s\n",
            $timestamp,
            str_pad($level, 9), // Pad to align log levels
            $message,
            $contextStr
        );
    }
    
    /**
     * Interpolates context values into the message placeholders
     * 
     * @param string $message The message with placeholders
     * @param array $context The context values
     * @return string The interpolated message
     */
    protected function interpolate($message, array $context = []) {
        if (strpos($message, '{') === false) {
            return $message;
        }
        
        $replace = [];
        foreach ($context as $key => $val) {
            // Check if the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        
        return strtr($message, $replace);
    }
    
    /**
     * Get the current timestamp in the application timezone
     * 
     * @return string The formatted timestamp
     */
    protected function getTimestamp() {
        $dateTime = new DateTime('now', new DateTimeZone('UTC'));
        return $dateTime->format('Y-m-d H:i:s.u P');
    }
    
    /**
     * Write a message to the log file
     * 
     * @param string $message The message to write
     * @return void
     */
    protected function writeToFile($message) {
        if ($this->fileHandle === null) {
            $this->fileHandle = @fopen($this->logFile, 'a');
            if ($this->fileHandle === false) {
                throw new \RuntimeException("Unable to open log file: {$this->logFile}");
            }
        }
        
        if (fwrite($this->fileHandle, $message) === false) {
            throw new \RuntimeException("Unable to write to log file: {$this->logFile}");
        }
        
        fflush($this->fileHandle);
    }
    
    /**
     * Close the log file handle
     * 
     * @return void
     */
    public function close() {
        if ($this->fileHandle !== null) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
    
    /**
     * Log an emergency message
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function emergency($message, array $context = []) {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    
    /**
     * Log an alert message
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function alert($message, array $context = []) {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    
    /**
     * Log a critical message
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function critical($message, array $context = []) {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function error($message, array $context = []) {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function warning($message, array $context = []) {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    
    /**
     * Log a notice
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function notice($message, array $context = []) {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    
    /**
     * Log an info message
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function info($message, array $context = []) {
        $this->log(LogLevel::INFO, $message, $context);
    }
    
    /**
     * Log a debug message
     * 
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    public function debug($message, array $context = []) {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
    
    /**
     * Log an exception
     * 
     * @param \Throwable $exception The exception to log
     * @param array $context Additional context
     * @return void
     */
    public function exception(\Throwable $exception, array $context = []) {
        $message = sprintf(
            '%s: %s in %s:%d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];
        
        $this->error($message, $context);
    }
}

// Global helper function
if (!function_exists('logger')) {
    /**
     * Get the logger instance
     * 
     * @return \Bingetv\Lib\Logger
     */
    function logger() {
        return \Bingetv\Lib\Logger::getInstance();
    }
}
