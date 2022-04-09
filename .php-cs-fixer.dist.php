<?php

$config = new TheTribe\CodingStandard();

return $config
    ->setRules(\array_merge($config->getRules(), [
        'declare_strict_types' => false,
        'doctrine_annotation_braces' => \array_merge($config->getRules()['doctrine_annotation_braces'], [
            'ignored_tags' => \array_merge($config->getRules()['doctrine_annotation_braces']['ignored_tags'], [
                'extends',
                'implements',
                'template',
            ]),
        ]),
        'ordered_imports' => [
            'imports_order' => ['class', 'const', 'function'],
        ],
        'single_line_throw' => false,
    ]))
    ->setFinder((new PhpCsFixer\Finder())
        ->exclude('node_modules')
        ->exclude('var/cache')
        ->exclude('var/logs')
        ->in(__DIR__)
    )
    ;
