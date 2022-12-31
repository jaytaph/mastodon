<?php

declare(strict_types=1);

namespace App\ActivityStream;

class OrderedCollectionPage
{
    protected OrderedCollection $collection;

    public function __construct(OrderedCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return mixed[]
     */
    public function getPage(int $pageNr, int $pageSize): array
    {
        $items = array_slice($this->collection->getElements(), ($pageNr - 1) * $pageSize, $pageSize);

        $first = 1;
        $last = (int) ceil(count($this->collection->getElements()) / $pageSize);

        $ret = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $this->collection->getId() . '?page=' . $pageNr,
            'type' => 'OrderedCollectionPage',
            'partOf' => $this->collection->getId(),
            'first' => $this->collection->getId() . '?page=' . $first,
            'last' => $this->collection->getId() . '?page=' . $last,
            'current' => $this->collection->getId() . '?page=' . $pageNr,
            'totalItems' => count($this->collection->getElements()),
            'orderedItems' => $items,
        ];

        if ($pageNr < $last) {
            $ret['next'] = $this->collection->getId() . '?page=' . ($pageNr + 1);
        }
        if ($pageNr > 1) {
            $ret['prev'] = $this->collection->getId() . '?page=' . ($pageNr - 1);
        }


        return $ret;
    }
}
