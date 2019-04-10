<?php
declare(strict_types=1);

namespace battlecook\Config;

use battlecook\DataStore\IDataStore;

final class Memcache
{
    const DEFAULT_EXPIRE_TIME = 60 * 60 * 7;

    private $store;
    private $servers;
    private $expireTime;

    /**
     * Memcache constructor.
     * @param IDataStore|null $store
     * @param \battlecook\Config\Server\Memcache[] $servers
     * @param float|int $expireTime
     */
    public function __construct(IDataStore $store = null, ?array $servers = null, $expireTime = self::DEFAULT_EXPIRE_TIME)
    {
        $this->store = $store;
        if($servers === null) {
            $this->servers[] = new \battlecook\Config\Server\Memcache();
        } else {
            $this->servers = $servers;
        }
        $this->expireTime = $expireTime;
    }

    public function getStore(): ?IDataStore
    {
        return $this->store;
    }

    /**
     * @return \battlecook\Config\Server\Memcache[]
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }
}