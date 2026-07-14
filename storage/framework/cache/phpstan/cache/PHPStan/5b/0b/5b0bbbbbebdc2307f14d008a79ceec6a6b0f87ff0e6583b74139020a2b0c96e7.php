<?php declare(strict_types = 1);

// odsl-/home/fikri/Projects/aish_agentic_ai/app/Support/Health/ReadinessProbe.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Support\Health\ReadinessProbe
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.4.1-92a92ad2319897e1a37bb1ddaea9d8b2d88da0ce5665a4703e4d4a483706d45d',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Support\\Health\\ReadinessProbe',
        'filename' => '/home/fikri/Projects/aish_agentic_ai/app/Support/Health/ReadinessProbe.php',
      ),
    ),
    'namespace' => 'App\\Support\\Health',
    'name' => 'App\\Support\\Health\\ReadinessProbe',
    'shortName' => 'ReadinessProbe',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Aggregates the configured readiness checks into a single verdict.
 *
 * The probe is truthful (rule 10, Master Source §53): readiness is reported
 * only when every mandatory dependency check passes. A single failing check
 * makes the whole probe not-ready.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 14,
    'endLine' => 40,
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
      'checks' => 
      array (
        'declaringClassName' => 'App\\Support\\Health\\ReadinessProbe',
        'implementingClassName' => 'App\\Support\\Health\\ReadinessProbe',
        'name' => 'checks',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'iterable',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 19,
        'endLine' => 19,
        'startColumn' => 33,
        'endColumn' => 65,
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
          'checks' => 
          array (
            'name' => 'checks',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'iterable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 19,
            'endLine' => 19,
            'startColumn' => 33,
            'endColumn' => 65,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  iterable<HealthCheck>  $checks
 */',
        'startLine' => 19,
        'endLine' => 19,
        'startColumn' => 5,
        'endColumn' => 69,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Support\\Health',
        'declaringClassName' => 'App\\Support\\Health\\ReadinessProbe',
        'implementingClassName' => 'App\\Support\\Health\\ReadinessProbe',
        'currentClassName' => 'App\\Support\\Health\\ReadinessProbe',
        'aliasName' => NULL,
      ),
      'evaluate' => 
      array (
        'name' => 'evaluate',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return array{0: bool, 1: array<int, HealthResult>}
 */',
        'startLine' => 24,
        'endLine' => 39,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Support\\Health',
        'declaringClassName' => 'App\\Support\\Health\\ReadinessProbe',
        'implementingClassName' => 'App\\Support\\Health\\ReadinessProbe',
        'currentClassName' => 'App\\Support\\Health\\ReadinessProbe',
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