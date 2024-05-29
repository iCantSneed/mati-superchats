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
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder)
    ->setIndent("  ")
;
