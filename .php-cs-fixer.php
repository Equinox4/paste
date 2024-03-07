<?php

$finder = (new PhpCsFixer\Finder())
	->in(__DIR__)
;

return (new PhpCsFixer\Config())
	->setRules([
		'@PSR12'                                  => true,
		'array_syntax'                            => ['syntax' => 'short'],
		'control_structure_continuation_position' => ['position' => 'next_line'],
		'declare_strict_types'                    => true,
		'strict_comparison'                       => true,
		'strict_param'                            => true,
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setCacheFile(__DIR__ . '/tools/php-cs-fixer/.php-cs-fixer.cache')
	->setFinder($finder)
;
