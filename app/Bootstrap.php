<?php

/**
 * BPJS API Bootstrap
 * Central initialization for all entry points
 */

class BPJSBootstrap
{
    private static $initialized = false;
    private static $basePath;
    private static $isDevMode;
    private static $currentDomain;
    private static $apiDomainVersion;

    /**
     * Initialize the application
     */
    public static function init($basePath = null)
    {
        if (self::$initialized) {
            return;
        }

        self::$basePath = $basePath ?? dirname(__DIR__);
        
        // Load environment
        self::loadEnvironment();
        
        // Load LZString library (manual loading)
        self::loadLZString();
        
        // Load helper classes
        self::loadHelpers();
        
        self::$initialized = true;
    }

    /**
     * Get base path
     */
    public static function getBasePath()
    {
        return self::$basePath;
    }

    /**
     * Load environment variables
     */
    private static function loadEnvironment()
    {
        $envPath = self::$basePath . '/.env';
        if (!file_exists($envPath)) {
            die('.env file not found. Please copy .env-demo to .env and configure your credentials.');
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || trim($line) === '') {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }

    /**
     * Load LZString compression library (manual)
     */
    private static function loadLZString()
    {
        $lzPath = self::$basePath . '/library/lz-string/src/LZCompressor';
        foreach (glob($lzPath . '/*.php') as $file) {
            require_once $file;
        }
    }

    /**
     * Load helper classes
     */
    private static function loadHelpers()
    {
        require_once self::$basePath . '/src/Signature.php';
        require_once self::$basePath . '/src/Request.php';
        require_once self::$basePath . '/src/Decrypt.php';
    }

    /**
     * Get credentials from environment
     */
    public static function getCredentials()
    {
        return [
            'cons_id' => $_ENV['BPJS_CONS_ID'] ?? '',
            'secret_key' => $_ENV['BPJS_SECRET_KEY'] ?? '',
            'user_key' => $_ENV['BPJS_USER_KEY'] ?? ''
        ];
    }

    /**
     * Get API domain configuration
     */
    public static function getApiConfig()
    {
        $isDevMode = isset($_GET['dev_mode']) 
            ? ($_GET['dev_mode'] === 'true') 
            : (($_COOKIE['bpjs_dev_mode'] ?? 'false') === 'true');
        
        $apiDomainVersion = isset($_GET['api_version']) 
            ? $_GET['api_version'] 
            : (($_COOKIE['bpjs_api_version'] ?? 'v1'));

        $prodDomainMap = [
            'v1' => 'apijkn.bpjs-kesehatan.go.id', 
            'v2' => 'new-apijkn.bpjs-kesehatan.go.id'
        ];
        
        $currentDomain = $isDevMode 
            ? 'apijkn-dev.bpjs-kesehatan.go.id' 
            : ($prodDomainMap[$apiDomainVersion] ?? $prodDomainMap['v1']);

        // Store in static property for module access
        self::$isDevMode = $isDevMode;
        self::$currentDomain = $currentDomain;
        self::$apiDomainVersion = $apiDomainVersion;

        return [
            'is_dev_mode' => $isDevMode,
            'api_version' => $apiDomainVersion,
            'current_domain' => $currentDomain
        ];
    }

    /**
     * Get current domain (for module files)
     */
    public static function getCurrentDomain()
    {
        return self::$currentDomain;
    }

    /**
     * Get dev mode status (for module files)
     */
    public static function getIsDevMode()
    {
        return self::$isDevMode;
    }

    /**
     * Load all modules
     * Note: This must be called AFTER setModuleVariables() to ensure $currentDomain and $isDevMode are available
     */
    public static function loadModules()
    {
        $modules = [];
        $modulesPath = self::$basePath . '/app/modules/*.php';
        
        // Extract variables into the scope for module files (using exact variable names)
        extract([
            'currentDomain' => self::$currentDomain,
            'isDevMode' => self::$isDevMode
        ]);
        
        foreach (glob($modulesPath) as $file) {
            $moduleKey = basename($file, '.php');
            $moduleData = require_once $file;
            if (is_array($moduleData)) {
                $modules[$moduleKey] = $moduleData;
            }
        }
        
        return $modules;
    }
}

/**
 * Helper function to get base URL for a module
 */
function getBaseUrl($moduleKey, $currentDomain, $isDevMode)
{
    $prodPaths = [
        'vclaim' => '/vclaim-rest', 
        'antrean_rs' => '/antreanrs', 
        'antrean_fktp' => '/antreanfktp',
        'apotek' => '/apotek-rest', 
        'pcare' => '/pcare-rest', 
        'icare' => '/wsihs',
        'ws_rekam_medis' => '/erekammedis', 
        'aplicares' => '/aplicaresws/rest',
    ];

    $devDomains = [
        'vclaim' => 'apijkn-dev.bpjs-kesehatan.go.id/vclaim-rest-dev',
        'antrean_rs' => 'apijkn-dev.bpjs-kesehatan.go.id/antreanrs_dev',
        'antrean_fktp' => 'apijkn-dev.bpjs-kesehatan.go.id/antreanfktp_dev',
        'apotek' => 'apijkn-dev.bpjs-kesehatan.go.id/apotek-rest-dev',
        'pcare' => 'apijkn-dev.bpjs-kesehatan.go.id/pcare-rest-dev',
        'icare' => 'apijkn-dev.bpjs-kesehatan.go.id/ihs_dev',
        'ws_rekam_medis' => 'apijkn-dev.bpjs-kesehatan.go.id/erekammedis_dev',
    ];

    if ($moduleKey === 'aplicares') {
        return $isDevMode
            ? 'https://apijkn.bpjs-kesehatan.go.id/aplicaresws/rest'
            : 'https://' . $currentDomain . ($prodPaths[$moduleKey] ?? '');
    }

    return $isDevMode && isset($devDomains[$moduleKey])
        ? 'https://' . $devDomains[$moduleKey]
        : 'https://' . $currentDomain . ($prodPaths[$moduleKey] ?? '');
}