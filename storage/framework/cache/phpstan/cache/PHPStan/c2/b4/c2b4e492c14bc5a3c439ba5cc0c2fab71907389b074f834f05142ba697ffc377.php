<?php declare(strict_types = 1);

// odsl-/home/fikri/Projects/aish_agentic_ai/app/Support/Runtime/Jobs/RuntimeSmokeJob.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Support\Runtime\Jobs\RuntimeSmokeJob
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.4.1-b53eb2d43b81c136c2c31d93cb72a67ab62831e3473acce3e1228cee93012da3',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'filename' => '/home/fikri/Projects/aish_agentic_ai/app/Support/Runtime/Jobs/RuntimeSmokeJob.php',
      ),
    ),
    'namespace' => 'App\\Support\\Runtime\\Jobs',
    'name' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
    'shortName' => 'RuntimeSmokeJob',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Foundation-only smoke job: proves the queue can dispatch AND a worker can
 * process a job end-to-end (Step 5 acceptance). It writes a controlled cache
 * marker and has NO business side effect. It is NOT a product feature and MUST
 * NOT be extended into agent/business work here (rule 05, rule 02).
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 20,
    'endLine' => 35,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'Illuminate\\Contracts\\Queue\\ShouldQueue',
    ),
    'traitClassNames' => 
    array (
      0 => 'Illuminate\\Foundation\\Bus\\Dispatchable',
      1 => 'Illuminate\\Queue\\InteractsWithQueue',
      2 => 'Illuminate\\Bus\\Queueable',
      3 => 'Illuminate\\Queue\\SerializesModels',
    ),
    'immediateConstants' => 
    array (
      'MARKER_PREFIX' => 
      array (
        'declaringClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'implementingClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'name' => 'MARKER_PREFIX',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'aish:queue-smoke:\'',
          'attributes' => 
          array (
            'startLine' => 27,
            'endLine' => 27,
            'startTokenPos' => 87,
            'startFilePos' => 801,
            'endTokenPos' => 87,
            'endFilePos' => 819,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 27,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 53,
      ),
    ),
    'immediateProperties' => 
    array (
      'token' => 
      array (
        'declaringClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'implementingClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'name' => 'token',
        'modifiers' => 2177,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 29,
        'endLine' => 29,
        'startColumn' => 33,
        'endColumn' => 61,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'token' => 
          array (
            'name' => 'token',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 29,
            'endLine' => 29,
            'startColumn' => 33,
            'endColumn' => 61,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 29,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 65,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Support\\Runtime\\Jobs',
        'declaringClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'implementingClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'currentClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'aliasName' => NULL,
      ),
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
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 31,
        'endLine' => 34,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Support\\Runtime\\Jobs',
        'declaringClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'implementingClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
        'currentClassName' => 'App\\Support\\Runtime\\Jobs\\RuntimeSmokeJob',
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