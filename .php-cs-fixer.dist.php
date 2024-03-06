<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHP80Migration:risky' => true,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_to_comment' => ['ignored_tags' => ['psalm-suppress']]
    ])
    ->setFinder($finder)
    ->setIndent("  ")
;
