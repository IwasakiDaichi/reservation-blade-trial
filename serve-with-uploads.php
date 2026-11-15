<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require __DIR__.'/vendor/autoload.php';

$uploadTempDirectory = realpath(sys_get_temp_dir()) ?: sys_get_temp_dir();
if (! is_dir($uploadTempDirectory) || ! is_writable($uploadTempDirectory)) {
    fwrite(STDERR, "PHP's temporary directory is not writable: {$uploadTempDirectory}".PHP_EOL);
    exit(1);
}

$host = getenv('SERVER_HOST') ?: '127.0.0.1';
$port = getenv('SERVER_PORT') ?: '8000';
$router = __DIR__.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';

$server = new Process([
    PHP_BINARY,
    '-d',
    'upload_tmp_dir='.$uploadTempDirectory,
    '-d',
    'upload_max_filesize=12M',
    '-d',
    'post_max_size=48M',
    '-S',
    $host.':'.$port,
    $router,
], __DIR__.'/public');
$server->setTimeout(null);

exit($server->run(static function (string $type, string $buffer): void {
    fwrite($type === Process::ERR ? STDERR : STDOUT, $buffer);
}));
