<?php

declare(strict_types=1);

namespace App\ActivityStream;

class OrderedCollection extends Collection
{
    public function toArray(): array
    {
        $ret = parent::toArray();

        $ret['orderedItems'] = $ret['items'];
        unset($ret['items']);
        $ret['type'] = 'OrderedCollection';

        return $ret;
    }
}
