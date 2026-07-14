<?php declare(strict_types = 1);

// odsl-/home/fikri/Projects/aish_agentic_ai/app/Console/Commands/QueueSmokeCommand.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Console\Commands\QueueSmokeCommand
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.4.1-856d005f558f4063ccc4ccb4bb67ef8e544143b9631665613428581587850a10',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Console\\Commands\\QueueSmokeCommand',
        'filename' => '/home/fikri/Projects/aish_agentic_ai/app/Console/Commands/QueueSmokeCommand.php',
      ),
    ),
    'namespace' => 'App\\Console\\Commands',
    'name' => 'App\\Console\\Commands\\QueueSmokeCommand',
    'shortName' => 'QueueSmokeCommand',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * `php artisan aish:queue-smoke --token=X`  -> dispatch a smoke job.
 * `php artisan aish:queue-smoke --token=X --check` -> verify the worker
 * processed it (exit 0 = processed, 1 = not processed).
 *
 * Used by scripts/verify-runtime.sh to prove real dispatch + worker processing
 * against the configured (Redis) queue. Foundation only — not a product feature.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 19,
    'endLine' => 46,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Console\\Command',
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
      'signature' => 
      array (
        'declaringClassName' => 'App\\Console\\Commands\\QueueSmokeCommand',
        'implementingClassName' => 'App\\Console\\Commands\\QueueSmokeCommand',
        'name' => 'signature',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'aish:queue-smoke {--token= : Unique smoke token} {--check : Verify the job was processed}\'',
          'attributes' => 
          array (
            'startLine' => 21,
            'endLine' => 21,
            'startTokenPos' => 50,
            'startFilePos' => 633,
            'endTokenPos' => 50,
            'endFilePos' => 723,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 21,
        'endLine' => 21,
        'startColumn' => 5,
        'endColumn' => 119,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'description' => 
      array (
        'declaringClassName' => 'App\\Console\\Commands\\QueueSmokeCommand',
        'implementingClassName' => 'App\\Console\\Commands\\QueueSmokeCommand',
        'name' => 'description',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'Dispatch (or verify) a runtime queue smoke job (foundation only; no business effect).\'',
          'attributes' => 
          array (
            'startLine' => 23,
            'endLine' => 23,
            'startTokenPos' => 59,
            'startFilePos' => 756,
            'endTokenPos' => 59,
            'endFilePos' => 842,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 23,
        'endLine' => 23,
        'startColumn' => 5,
        'endColumn' => 117,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      'handle' => 
      array (
        'name' => 'handle',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 25,
        'endLine' => 45,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Console\\Commands',
        'declaringClassName' => 'App\\Console\\Commands\\QueueSmokeCommand',
        'implementingClassName' => 'App\\Console\\Commands\\QueueSmokeCommand',
        'currentClassName' => 'App\\Console\\Commands\\QueueSmokeCommand',
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