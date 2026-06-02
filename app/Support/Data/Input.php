<?php

declare(strict_types=1);

namespace App\Support\Data;

/**
 * Type-safe accessors for reading scalar values out of loosely typed
 * arrays such as validated request payloads (`array<string, mixed>`).
 */
final class Input
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function string(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if (!is_scalar($value)) {
            return null;
        }

        $value = (string) $value;

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function integer(array $data, string $key, int $default = 0): int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function boolean(array $data, string $key, bool $default = false): bool
    {
        $value = $data[$key] ?? null;

        return is_scalar($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $default;
    }

    /**
     * Extract a list of strings, discarding any non-string members.
     *
     * @param  array<array-key, mixed>  $data
     * @return list<string>
     */
    public static function stringList(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }
}
