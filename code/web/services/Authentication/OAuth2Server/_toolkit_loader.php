<?php

// OAuth2 Server Autoloader for The PHP League's OAuth2 Server with Dependencies
// https://github.com/thephpleague/oauth2-server

// Define the base directories
$oauth2ServerBaseDir = __DIR__ . '/lib/';
$dependenciesDir = __DIR__ . '/dependencies/';

// Create dependencies directory if it doesn't exist
if (!is_dir($dependenciesDir)) {
	mkdir($dependenciesDir, 0755, true);
}

// Unregister any existing OAuth2 autoloaders to avoid conflicts
$registeredAutoloaders = spl_autoload_functions();
foreach ($registeredAutoloaders as $autoloader) {
	if (is_array($autoloader) && isset($autoloader[1]) && strpos($autoloader[1], 'OAuth2') !== false) {
		spl_autoload_unregister($autoloader);
	}
}

/**
 * PSR-4 Autoloader for OAuth2 Server and Dependencies
 */
spl_autoload_register(function ($className) use ($oauth2ServerBaseDir, $dependenciesDir) {
	// Define namespace mappings
	$namespaceMappings = [
		'League\\OAuth2\\Server\\' => $oauth2ServerBaseDir,
		'League\\Event\\' => $dependenciesDir . 'league/event/src/',
		'Lcobucci\\JWT\\' => $dependenciesDir . 'lcobucci/jwt/src/',
		'Lcobucci\\Clock\\' => $dependenciesDir . 'lcobucci/clock/src/',
		'Defuse\\Crypto\\' => $dependenciesDir . 'defuse/php-encryption/src/',
		'ParagonIE\\ConstantTime\\' => $dependenciesDir . 'paragonie/constant_time_encoding/src/',
		'Psr\\Http\\Message\\' => $dependenciesDir . 'psr/http-message/src/',
		'Psr\\Clock\\' => $dependenciesDir . 'psr/clock/src/',
		'Psr\\EventDispatcher\\' => $dependenciesDir . 'psr/event-dispatcher/src/',
		'Laminas\\Diactoros\\' => $dependenciesDir . 'laminas/diactoros/src/',
	];

	foreach ($namespaceMappings as $prefix => $baseDir) {
		// Check if the class uses this namespace prefix
		if (strncmp($prefix, $className, strlen($prefix)) === 0) {
			// Remove the namespace prefix from the class name
			$relativeClassName = substr($className, strlen($prefix));

			// Convert namespace separators to directory separators
			$relativeClassPath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClassName);

			// Build the full file path
			$filePath = $baseDir . $relativeClassPath . '.php';

			// If the file exists and class isn't already loaded, require it
			if (file_exists($filePath) && !class_exists($className, false) && !interface_exists($className, false)) {
				require_once $filePath;
				return true;
			}
		}
	}

	// Special handling for OAuth2 server subdirectories
	if (strpos($className, 'League\\OAuth2\\Server\\') === 0) {
		$relativeClassName = substr($className, strlen('League\\OAuth2\\Server\\'));
		$pathParts = explode('\\', $relativeClassName);
		$fileName = end($pathParts);

		// Look for the file in common subdirectories
		$searchPaths = [
			$oauth2ServerBaseDir . 'Entities/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'Entities/Traits/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'Exception/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'Grant/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'Repositories/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'ResponseTypes/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'RequestTypes/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'Middleware/' . $fileName . '.php',
			$oauth2ServerBaseDir . 'EventEmitting/' . $fileName . '.php',
		];

		foreach ($searchPaths as $searchPath) {
			if (file_exists($searchPath) && !class_exists($className, false) && !interface_exists($className, false)) {
				require_once $searchPath;
				return true;
			}
		}
	}

	return false;
}, true, true); // Prepend to autoloader queue for high priority

/**
 * Create the League Event classes that OAuth2 server needs
 */
function createLeagueEventClasses($dependenciesDir) {
	$leagueEventDir = $dependenciesDir . 'league/event/src/';
	$psrEventDir = $dependenciesDir . 'psr/event-dispatcher/src/';

	// Create directories
	if (!is_dir($leagueEventDir)) {
		mkdir($leagueEventDir, 0755, true);
	}
	if (!is_dir($psrEventDir)) {
		mkdir($psrEventDir, 0755, true);
	}

	// Only create files if they don't exist or classes aren't loaded
	if (!class_exists('Psr\\EventDispatcher\\EventDispatcherInterface', false)) {
		// Create PSR Event Dispatcher Interface
		$psrEventDispatcherContent = '<?php

namespace Psr\\EventDispatcher;

interface EventDispatcherInterface
{
    public function dispatch(object $event);
}

interface ListenerProviderInterface
{
    public function getListenersForEvent(object $event): iterable;
}

interface StoppableEventInterface
{
    public function isPropagationStopped(): bool;
}
';

		file_put_contents($psrEventDir . 'EventDispatcherInterface.php', $psrEventDispatcherContent);
	}

	// Only create League EventDispatcher if it doesn't exist
	if (!class_exists('League\\Event\\EventDispatcher', false)) {
		// Create League Event Dispatcher WITHOUT problematic class_alias
		$leagueEventDispatcherContent = '<?php

namespace League\\Event;

use Psr\\EventDispatcher\\EventDispatcherInterface;
use Psr\\EventDispatcher\\ListenerProviderInterface;
use Psr\\EventDispatcher\\StoppableEventInterface;

class EventDispatcher implements EventDispatcherInterface
{
    protected array $listeners = [];

    public function dispatch(object $event)
    {
        foreach ($this->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
            
            $listener($event);
        }
        
        return $event;
    }
    
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);
        return $this->listeners[$eventClass] ?? [];
    }
    
    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }
}
';

		file_put_contents($leagueEventDir . 'EventDispatcher.php', $leagueEventDispatcherContent);
	}

	// Only create ListenerProvider if it doesn't exist
	if (!class_exists('League\\Event\\ListenerProvider', false)) {
		// Create a simple ListenerProvider
		$listenerProviderContent = '<?php

namespace League\\Event;

use Psr\\EventDispatcher\\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    protected array $listeners = [];

    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);
        return $this->listeners[$eventClass] ?? [];
    }
    
    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }
}
';

		file_put_contents($leagueEventDir . 'ListenerProvider.php', $listenerProviderContent);
	}

	// Only create HasEventName if it doesn't exist
	if (!interface_exists('League\\Event\\HasEventName', false)) {
		// Create League Event HasEventName Interface
		$hasEventNameContent = '<?php

namespace League\\Event;

interface HasEventName
{
    public function eventName(): string;
}
';

		file_put_contents($leagueEventDir . 'HasEventName.php', $hasEventNameContent);
	}
}


/**
 * Load critical dependency files immediately
 */
function loadCriticalDependencyFiles($dependenciesDir) {
	// Create League Event classes if they don't exist
	createLeagueEventClasses($dependenciesDir);

	// Load PSR interfaces first (only if not already loaded)
	$psrFiles = [
		'psr/http-message/src/MessageInterface.php',
		'psr/http-message/src/RequestInterface.php',
		'psr/http-message/src/ResponseInterface.php',
		'psr/http-message/src/ServerRequestInterface.php',
		'psr/http-message/src/StreamInterface.php',
		'psr/http-message/src/UriInterface.php',
		'psr/http-message/src/UploadedFileInterface.php',
		'psr/clock/src/ClockInterface.php',
		'psr/event-dispatcher/src/EventDispatcherInterface.php',
	];

	foreach ($psrFiles as $file) {
		$filePath = $dependenciesDir . $file;
		if (file_exists($filePath)) {
			require_once $filePath;
		}
	}

	// Load League Event classes (only if not already loaded)
	$leagueEventFiles = [
		'league/event/src/EventDispatcher.php',
		'league/event/src/ListenerProvider.php',
		'league/event/src/HasEventName.php',
	];

	foreach ($leagueEventFiles as $file) {
		$filePath = $dependenciesDir . $file;
		if (file_exists($filePath)) {
			// Extract class name to check if it's already loaded
			$className = '';
			if (strpos($file, 'EventDispatcher.php') !== false) {
				$className = 'League\\Event\\EventDispatcher';
			} elseif (strpos($file, 'ListenerProvider.php') !== false) {
				$className = 'League\\Event\\ListenerProvider';
			}

			// Only require if class doesn't exist
			if (empty($className) || !class_exists($className, false)) {
				require_once $filePath;
			}
		}
	}
}

/**
 * Immediate loading of critical OAuth2 files
 */
function loadCriticalOAuth2Files($directory) {
	if (!is_dir($directory)) {
		return;
	}

	// Critical files that should be loaded immediately
	$criticalFiles = [
		'CryptKeyInterface.php',
		'CryptKey.php',
		'CryptTrait.php',
		'Entities/ClientEntityInterface.php',
		'Entities/AccessTokenEntityInterface.php',
		'Entities/AuthCodeEntityInterface.php',
		'Entities/RefreshTokenEntityInterface.php',
		'Entities/ScopeEntityInterface.php',
		'Entities/UserEntityInterface.php',
		'Entities/TokenInterface.php',
		'Entities/Traits/EntityTrait.php',
		'Entities/Traits/ClientTrait.php',
		'Entities/Traits/AccessTokenTrait.php',
		'Entities/Traits/AuthCodeTrait.php',
		'Entities/Traits/RefreshTokenTrait.php',
		'Entities/Traits/ScopeTrait.php',
		'Entities/Traits/TokenEntityTrait.php',
		'Repositories/RepositoryInterface.php',
		'Repositories/ClientRepositoryInterface.php',
		'Repositories/AccessTokenRepositoryInterface.php',
		'Repositories/AuthCodeRepositoryInterface.php',
		'Repositories/RefreshTokenRepositoryInterface.php',
		'Repositories/ScopeRepositoryInterface.php',
		'Repositories/UserRepositoryInterface.php'
	];

	foreach ($criticalFiles as $file) {
		$filePath = $directory . $file;
		if (file_exists($filePath)) {
			require_once $filePath;
		}
	}
}

// Create dependency directories
$dependencyDirs = [
	'lcobucci/jwt/src',
	'lcobucci/clock/src',
	'defuse/php-encryption/src',
	'paragonie/constant_time_encoding/src',
	'psr/http-message/src',
	'psr/clock/src',
	'psr/event-dispatcher/src',
	'league/event/src',
	'laminas/diactoros/src'
];

foreach ($dependencyDirs as $dir) {
	$fullPath = $dependenciesDir . $dir;
	if (!is_dir($fullPath)) {
		mkdir($fullPath, 0755, true);
	}
}

// Load critical dependency files
loadCriticalDependencyFiles($dependenciesDir);

// Load critical OAuth2 files immediately
loadCriticalOAuth2Files($oauth2ServerBaseDir);

// Also manually load core files from the root lib directory
$coreFiles = [
	'CryptKeyInterface.php',
	'CryptKey.php',
	'CryptTrait.php',
	'AuthorizationServer.php',
	'ResourceServer.php'
];

foreach ($coreFiles as $coreFile) {
	$filePath = $oauth2ServerBaseDir . $coreFile;
	if (file_exists($filePath)) {
		require_once $filePath;
	}
}

// Register a fallback autoloader for any missed classes
spl_autoload_register(function ($className) use ($oauth2ServerBaseDir, $dependenciesDir) {
	// Handle OAuth2 server classes
	if (strpos($className, 'League\\OAuth2\\Server\\') === 0) {
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($oauth2ServerBaseDir, RecursiveDirectoryIterator::SKIP_DOTS));

		$classBaseName = substr($className, strrpos($className, '\\') + 1);

		foreach ($iterator as $file) {
			if ($file->isFile() && $file->getExtension() === 'php') {
				if ($file->getBasename('.php') === $classBaseName) {
					if (!class_exists($className, false) && !interface_exists($className, false)) {
						require_once $file->getRealPath();
						return true;
					}
				}
			}
		}
	}

	// Handle League Event classes
	if (strpos($className, 'League\\Event\\') === 0) {
		$classBaseName = substr($className, strrpos($className, '\\') + 1);
		$eventFile = $dependenciesDir . 'league/event/src/' . $classBaseName . '.php';
		if (file_exists($eventFile) && !class_exists($className, false)) {
			require_once $eventFile;
			return true;
		}
	}

	// Handle dependency classes
	if (is_dir($dependenciesDir)) {
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dependenciesDir, RecursiveDirectoryIterator::SKIP_DOTS));

		$classBaseName = substr($className, strrpos($className, '\\') + 1);

		foreach ($iterator as $file) {
			if ($file->isFile() && $file->getExtension() === 'php') {
				if ($file->getBasename('.php') === $classBaseName) {
					if (!class_exists($className, false) && !interface_exists($className, false)) {
						require_once $file->getRealPath();
						return true;
					}
				}
			}
		}
	}

	return false;
});

// Clear any existing problematic EventDispatcher file and recreate it properly
$problematicEventDispatcherFile = $dependenciesDir . 'league/event/src/EventDispatcher.php';
if (file_exists($problematicEventDispatcherFile)) {
	unlink($problematicEventDispatcherFile);
	createLeagueEventClasses($dependenciesDir);
}
