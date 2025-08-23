<?php

namespace Phiki\Tests\Fixtures;

use Psr\SimpleCache\CacheInterface;

class FakeCache implements CacheInterface
{
    private array $store = [];

    public function get($key, $default = null): mixed
    {
        return $this->store[$key] ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->store[$key] = $value;
        return true;
    }

    public function delete($key): bool
    {
        unset($this->store[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->store = [];
        return true;
    }

    public function getMultiple($keys, $default = null): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->store);
    }
}
