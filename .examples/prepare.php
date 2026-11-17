<?php

$stages = json_decode(file_get_contents(__DIR__ . '/stages.json'), true, flags: JSON_THROW_ON_ERROR);
$slug = $argv[1] ?? '';
$target = $argv[2] ?? '';
if (!in_array($slug, $stages, true) || $target === '') {
    fwrite(STDERR, "Usage: php .examples/prepare.php <lesson-slug> <new-directory>\n");
    exit(2);
}
if (file_exists($target)) {
    fwrite(STDERR, "The target must not exist. Existing files are preserved.\n");
    exit(2);
}
if (!mkdir($target, 0755, true)) {
    throw new RuntimeException('Cannot create target');
}
if (str_starts_with(realpath($target), __DIR__ . DIRECTORY_SEPARATOR)) {
    rmdir($target);
    fwrite(STDERR, "Choose a destination outside .examples.\n");
    exit(2);
}
function overlay(string $source, string $target): void
{
    if (!is_dir($source)) {
        return;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $source,
        FilesystemIterator::SKIP_DOTS,
    )) as $file) {
        $relative = substr($file->getPathname(), strlen($source) + 1);
        $destination = $target . '/' . $relative;
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }
        copy($file->getPathname(), $destination);
    }
}

overlay(__DIR__ . '/base', $target);
foreach ($stages as $stage) {
    overlay(__DIR__ . '/steps/' . $stage, $target);
    if ($stage === $slug) {
        break;
    }
}
fwrite(STDOUT, 'Prepared ' . $slug . ' in ' . $target . "\nNext: composer install\n");
