<?php

declare(strict_types=1);

namespace App;

class ActivityStream
{
    public const DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    public const DATETIME_FORMAT_GMT = \DateTimeInterface::RFC7231;
}
