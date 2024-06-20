<?php

namespace Sabberworm\CSS;

use Psr\Log\AbstractLogger;

/**
 * This class provides a simple logging facility.
 */
class SimpleLogger extends AbstractLogger
{
    public function log($level, $message, array $context = []): void
    {
        echo self::interpolate($message, $context) . PHP_EOL;
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    private function interpolate(string $message, array $context = []): string
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
