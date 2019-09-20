<?php

$commandLine = $this->commandLine();
$commandLine->option('src', 'default', ['src']);
$commandLine->option('spec', 'default', ['tests']);
$commandLine->option('coverage', 'default', 3);
$commandLine->option('reporter', 'default', 'verbose');
