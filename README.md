php-command
===========

php-command provides a simple object oriented interface to execute shell commands.

## Features

 * Catches `stdOut`, `stdErr` and `exitCode`
 * Handle argument escaping
 * Pass environment vars and other options to `proc_open()`

## Examples

### Basic Example

```php
<?php
use mikehaertl\shellcommand\Command;

// Basic example
$command = new Command('/usr/local/bin/mycommand -a -b');
if ($command->execute()) {
    echo $command->getOutput();
} else {
    echo $command->getError();
    $exitCode = $command->getExitCode();
}
```

### Advanced Features

```php
// Create command with options array
$command = new Command(array(
    'command' => '/usr/local/bin/mycommand',

    // Will be passed as environment variables to the command
    'procEnv' => array(
        'DEMOVAR' => 'demovalue'
    ),

    // Will be passed as options to proc_open()
    'procOptions' => array(
        'bypass_shell' => true,
    ),
));

// Add arguments with correct escaping:
// results in --name='d'\''Artagnan'
$command->addArg('--name=', "d'Artagnan");

// Add argument with several values
// results in --keys key1 key2
$command->addArg('--keys', array('key1','key2')
```
