<?php

declare(strict_types=1);

$environment = $argv[1] ?? '';
if (!in_array($environment, ['dev', 'test'], true)) {
    fwrite(STDERR, "Usage: php bin/reset.php dev|test\n");
    exit(2);
}
putenv('APP_ENV=' . $environment);
$_SERVER['APP_ENV'] = $environment;
$_ENV['APP_ENV'] = $environment;
$project = dirname(__DIR__);
require $project . '/vendor/autoload.php';
new Symfony\Component\Dotenv\Dotenv()->bootEnv($project . '/.env', $environment);
$expected = 'sqlite:///%kernel.project_dir%/var/agenda' . ($environment === 'test' ? '_test' : '') . '.db';
if (($_SERVER['DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? '') !== $expected) {
    fwrite(STDERR, "Refusing to reset an external database.\n");
    exit(2);
}
$file = $project . '/var/agenda' . ($environment === 'test' ? '_test' : '') . '.db';
if (is_file($file) && !unlink($file)) {
    throw new RuntimeException('Cannot remove local database');
}
foreach ([
    ['doctrine:migrations:migrate', '--no-interaction'],
    ['doctrine:fixtures:load',      '--no-interaction'],
] as $arguments) {
    $process = new Symfony\Component\Process\Process(
        [PHP_BINARY, 'bin/console', ...$arguments, '--env=' . $environment],
        $project,
        ['APP_ENV' => $environment],
    );
    $process->mustRun(fn(string $type, string $buffer) => print $buffer);
}
