<?php

declare(strict_types=1);

namespace Sabberworm\CSS\Tests\Unit\Position\Fixtures;

use Sabberworm\CSS\Position\Position;
use Sabberworm\CSS\Position\Positionable;

final class ConcretePosition implements Positionable
{
    use Position;
}
