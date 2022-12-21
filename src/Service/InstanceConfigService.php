<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Config;
use App\Repository\ConfigRepository;
use Doctrine\ORM\EntityNotFoundException;

class InstanceConfigService
{
    protected ConfigRepository $repository;

    public function __construct(ConfigRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getConfig(): Config
    {
        $config = $this->repository->findOneBy([]);
        if ($config === null) {
            throw new EntityNotFoundException('No config found');
        }

        return $config;
    }

    public function hasConfig(): bool
    {
        try {
            $this->getConfig();
            return true;
        } catch (EntityNotFoundException) {
            // No config found
        }

        return false;
    }

    public function createDefaultConfig()
    {
        $config = new Config();

        $config->setInstanceDomain('localhost');
        $config->setInstanceTitle('DonkeyHead Mastodon Instance');
        $config->setInstanceShortDescription('A Mastodon instance');
        $config->setInstanceDescription('A Mastodon instance');
        $config->setInstanceEmail('admin@localhost');
        $config->setApprovalRequired(false);
        $config->setInviteEnabled(false);
        $config->setRegistrationAllowed(true);
        $config->setLanguages(['en']);
        $config->setThumbnailUrl('');
        $config->setAdminAccount('');
        $config->setStatusLength(500);
        $config->setMediaAttachments(4);
        $config->setCharactersPerUrl(23);
        $config->setAccountTags(4);
        $config->setOptionsPerPoll(4);
        $config->setCharacersPerOption(25);
        $config->setMinimumPollExpiration(300);
        $config->setMaximumPollExpiration(604800);

        $this->saveConfig($config);
    }

    public function saveConfig(Config $config)
    {
        $this->repository->save($config, true);
    }
}
