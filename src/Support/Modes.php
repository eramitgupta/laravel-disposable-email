<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

use InvalidArgumentException;

class Modes
{
    private const ALLOWED = ['rfc', 'strict', 'dns', 'spoof', 'filter', 'filter_unicode'];

    /**
     * @param  array<int, string>  $parameters
     * @return array<int, string>
     */
    public static function parse(array $parameters): array
    {
        $modes = [];

        foreach ($parameters as $parameter) {
            foreach (explode(',', $parameter) as $mode) {
                $mode = strtolower(trim($mode));

                if ($mode === '') {
                    continue;
                }

                if (! in_array($mode, self::ALLOWED, true)) {
                    throw new InvalidArgumentException("Unsupported disposable email validation mode [{$mode}].");
                }

                $modes[$mode] = true;
            }
        }

        return array_keys($modes);
    }
}
