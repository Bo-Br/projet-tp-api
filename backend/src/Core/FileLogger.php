<?php

declare(strict_types=1);

namespace App\Core;

use Psr\Log\AbstractLogger;
use Stringable;
use Throwable;

/**
 * Petit logger PSR-3 de secours utilisé quand Monolog n'est pas présent dans
 * le dossier vendor fourni avec le projet de départ.
 */
final class FileLogger extends AbstractLogger
{
    public function __construct(private readonly string $file)
    {
        $directory = dirname($this->file);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Impossible de créer le dossier de logs : ' . $directory);
        }
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $messageText = $this->interpolate((string) $message, $context);
        $safeContext = $this->normalizeContext($context);
        $line = json_encode([
            'timestamp' => date(DATE_ATOM),
            'level' => strtoupper((string) $level),
            'message' => $messageText,
            'context' => $safeContext,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($line === false || file_put_contents($this->file, $line . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            error_log('[DevCoffee] ' . strtoupper((string) $level) . ': ' . $messageText);
        }
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null || $value instanceof Stringable) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }
        return strtr($message, $replace);
    }

    private function normalizeContext(array $context): array
    {
        $result = [];
        foreach ($context as $key => $value) {
            if ($value instanceof Throwable) {
                $result[$key] = [
                    'class' => $value::class,
                    'message' => $value->getMessage(),
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'trace' => $value->getTraceAsString(),
                ];
            } elseif (is_scalar($value) || $value === null) {
                $result[$key] = $value;
            } elseif ($value instanceof Stringable) {
                $result[$key] = (string) $value;
            } elseif (is_object($value)) {
                $result[$key] = ['class' => $value::class];
            } elseif (is_array($value)) {
                $result[$key] = $this->normalizeContext($value);
            } else {
                $result[$key] = gettype($value);
            }
        }
        return $result;
    }
}
