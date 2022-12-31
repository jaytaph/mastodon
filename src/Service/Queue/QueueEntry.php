<?php

declare(strict_types=1);

namespace App\Service\Queue;

use Jaytaph\TypeArray\TypeArray;

class QueueEntry
{
    protected string $type;
    protected TypeArray $data;
    protected bool $retry;
    protected int $attempts;
    protected bool $failed;
    protected string $failedReason;
    protected bool $finished;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return TypeArray
     */
    public function getData(): TypeArray
    {
        return $this->data;
    }

    /**
     * @param TypeArray $data
     */
    public function setData(TypeArray $data): void
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isRetry(): bool
    {
        return $this->retry;
    }

    /**
     * @param bool $retry
     */
    public function setRetry(bool $retry): void
    {
        $this->retry = $retry;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     */
    public function setAttempts(int $attempts): void
    {
        $this->attempts = $attempts;
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->failed;
    }

    /**
     * @param bool $failed
     */
    public function setFailed(bool $failed): void
    {
        $this->failed = $failed;
    }

    /**
     * @return string
     */
    public function getFailedReason(): string
    {
        return $this->failedReason;
    }

    /**
     * @param string $failedReason
     */
    public function setFailedReason(string $failedReason): void
    {
        $this->failedReason = $failedReason;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @param bool $finished
     */
    public function setFinished(bool $finished): void
    {
        $this->finished = $finished;
    }
}
