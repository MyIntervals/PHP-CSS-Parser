<?php

declare(strict_types=1);

function pointless(int $value): void
{
    \assert(\is_int($value));
}
