<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    '@PSR12:risky' => true,
    '@PhpCsFixer' => true,
    '@PhpCsFixer:risky' => true,
    'array_syntax' => ['syntax' => 'short'],
    'php_unit_test_class_requires_covers' => false,
    'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
    'native_function_invocation' => false,
])
    ->setFinder($finder)
    ;