<?php declare(strict_types = 1);

// odsl-/home/fikri/Projects/aish_agentic_ai/app/Http/Controllers/Health/ReadinessController.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Http\Controllers\Health\ReadinessController
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.4.1-0c5302d7be7019882c1d574b57cb25e171aa82a3b646bce4fcc404b10397cda0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Http\\Controllers\\Health\\ReadinessController',
        'filename' => '/home/fikri/Projects/aish_agentic_ai/app/Http/Controllers/Health/ReadinessController.php',
      ),
    ),
    'namespace' => 'App\\Http\\Controllers\\Health',
    'name' => 'App\\Http\\Controllers\\Health\\ReadinessController',
    'shortName' => 'ReadinessController',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Readiness probe: are all mandatory dependencies ready to serve traffic?
 *
 * Returns HTTP 200 only when every configured check passes; otherwise HTTP 503
 * with a truthful, non-sensitive per-check breakdown. The check list comes from
 * config(\'health.readiness\') so it is environment- and test-overridable.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 18,
    'endLine' => 34,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      '__invoke' => 
      array (
        'name' => '__invoke',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Http\\JsonResponse',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 20,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Http\\Controllers\\Health',
        'declaringClassName' => 'App\\Http\\Controllers\\Health\\ReadinessController',
        'implementingClassName' => 'App\\Http\\Controllers\\Health\\ReadinessController',
        'currentClassName' => 'App\\Http\\Controllers\\Health\\ReadinessController',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));