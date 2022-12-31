<?php

declare(strict_types=1);

namespace App\Service\Converter;

use App\Entity\Account;
use App\Service\AccountService;
use App\Service\ConfigService;
use App\Service\StatusService;
use Jaytaph\TypeArray\TypeArray;

// Converts elements from internal format to ActivityStream format

class ActivityStreamConverter
{
    protected AccountService $accountService;
    protected StatusService $statusService;
    protected ConfigService $configService;

    public function account(Account $account): TypeArray
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Person',
            'id' => $account->getUri(),
            'following' => $account->getUri() . '/following',
            'followers' => $account->getUri() . '/followers',
            'inbox' => $account->getUri() . '/inbox',
            'outbox' => $account->getUri() . '/outbox',
            'preferredUsername' => $account->getUsername(),
            'name' => $account->getDisplayName(),
            'summary' => $account->getNote(),
            'icon' => [
                $account->getAvatarStatic(),
            ],
        ];

        return new TypeArray($data);
    }


    /**
     * @param mixed[] $elements
     */
    public function collection(array $elements, string $summary = null): TypeArray
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Collection',
            'totalItems' => count($elements),
            'items' => $elements,
        ];

        if ($summary) {
            $data['summary'] = $summary;
        }

        return new TypeArray($data);
    }

    /**
     * @param mixed[] $elements
     */
    public function orderedCollection(array $elements, string $summary = null): TypeArray
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'OrderedCollection',
            'totalItems' => count($elements),
            'orderedItems' => $elements,
        ];

        if ($summary) {
            $data['summary'] = $summary;
        }

        return new TypeArray($data);
    }
}
