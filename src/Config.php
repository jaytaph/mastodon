<?php

namespace App;

use Symfony\Component\Yaml\Yaml;

class Config {
    protected array $config;

    /**
     * @param array $config
     */
    public function __construct(string $configPath = "config.yaml")
    {
        try {
            $this->config = Yaml::parse(file_get_contents($configPath));
        } catch (\Exception) {
            throw new \Exception("Cannot parse config file: $configPath");
        }
    }

    public function getUser(): string {
        return $this->config->user;
    }

    public function getPrivKey(): string {
        return $this->config->privKey;
    }

    public function getPubKey(): string {
        return $this->config->pubKey;
    }
}
