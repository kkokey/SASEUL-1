<?php

/** @see: https://kahlan.github.io/docs/cli-options.html */
$commandLine = $this->commandLine();

// Paths of source directories (array)
$commandLine->option('src', 'default', ['src']);

// Paths of specification directories (array)
$commandLine->option('spec', 'default', ['tests']);

// Generate code coverage report
$commandLine->option('coverage', 'default', 3);

/*
 * The name of the text reporter to use
 * options : `dot`, `bar`, `json`, `tap`, `verbose`
 */
$commandLine->option('reporter', 'default', 'verbose');
