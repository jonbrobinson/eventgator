<?php

include_once "vendor/autoload.php";

spl_autoload_register(
    function($className)
    {
        $className = str_replace("_", "\\", $className);
        $className = ltrim($className, '\\');
        $fileName = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (file_exists('src/' . $fileName)) {
            include_once 'src/' . $fileName;
        }
    }
);

if (!function_exists('getPayload')) {
    /**
     * gets payload file and decodes
     *
     * @param bool $assoc
     *
     * @return mixed
     */
    function getPayload($assoc = false)
    {
        $payloadFile = $_SERVER['PAYLOAD_FILE'];
        if (empty($payloadFile)) {
            return false;
        }
        $payload = file_get_contents($payloadFile);

        return json_decode($payload, $assoc);
    }
};