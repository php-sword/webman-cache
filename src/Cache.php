<?php declare (strict_types=1);

namespace sword\Cache;

use DateInterval;
use DateTime;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use sword\Cache\Exception\CacheException;
use Throwable;

/**
 * 缓存管理类
 * Class Cache
 * @package \sword\Cache
 */
class Cache extends Manager implements CacheInterface
{

    protected ?string $namespace = '\\sword\Cache\\Driver\\';

    /**
     * 默认驱动
     * @return string|null
     */
    public function getDefaultDriver(): ?string
    {
        return $this->getConfig('default');
    }

    /**
     * 获取缓存配置
     * @param null|string $name    名称
     * @param mixed       $default 默认值
     * @return mixed
     */
    public function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return config('plugin.sword.cache.app.' . $name, $default);
        }

        return config('plugin.sword.cache.app');
    }

    /**
     * 获取驱动配置
     * @param string      $store
     * @param string|null $name
     * @param null        $default
     * @return mixed
     */
    public function getStoreConfig(string $store, ?string $name = null, $default = null)
    {
        if ($config = $this->getConfig("stores.$store")) {
            return Helper::get($config, $name, $default);
        }

        throw new \InvalidArgumentException("Store [$store] not found.");
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function resolveType(string $name)
    {
        return $this->getStoreConfig($name, 'type', 'file');
    }

    protected function resolveConfig(string $name): array
    {
        return $this->getStoreConfig($name);
    }

    /**
     * 连接或者切换缓存
     * @param null|string $name 连接配置名
     * @return Driver
     */
    public function store(string $name = null): Driver
    {
        return $this->driver($name);
    }

    /**
     * 清空缓冲池
     * @return bool
     */
    public function clear(): bool
    {
        return $this->store()->clear();
    }

    /**
     * 读取缓存
     * @param string $key     缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        return $this->store()->get($key, $default);
    }

    /**
     * 写入缓存
     * @param string            $key   缓存变量名
     * @param mixed             $value 存储数据
     * @param null|int|DateTime $ttl   有效时间 0为永久
     * @return bool
     * @throws InvalidArgumentException
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->store()->set($key, $value, $ttl);
    }

    /**
     * 删除缓存
     * @param string $key 缓存变量名
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete($key): bool
    {
        return $this->store()->delete($key);
    }

    /**
     * 读取缓存
     * @param iterable $keys    缓存变量名
     * @param mixed    $default 默认值
     * @return iterable
     * @throws InvalidArgumentException
     */
    public function getMultiple($keys, $default = null): iterable
    {
        return $this->store()->getMultiple($keys, $default);
    }

    /**
     * 写入缓存
     * @param iterable              $values 缓存数据
     * @param null|int|DateInterval $ttl    有效时间 0为永久
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return $this->store()->setMultiple($values, $ttl);
    }

    /**
     * 删除缓存
     * @param iterable $keys 缓存变量名
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        return $this->store()->deleteMultiple($keys);
    }

    /**
     * 判断缓存是否存在
     * @param string $key 缓存变量名
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has($key): bool
    {
        return $this->store()->has($key);
    }

    /**
     * 缓存标签
     * @param string|array $name 标签名
     * @return TagSet
     */
    public function tag($name): TagSet
    {
        return $this->store()->tag($name);
    }

    /**
     * 如果不存在则写入缓存
     * @param string $name
     * @param $value
     * @param int|null $expire
     * @return mixed
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function remember(string $name, $value, ?int $expire = null)
    {
        try{
            return $this->store()->remember($name, $value, $expire);
        }catch (Throwable $e){
            throw new CacheException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 读取缓存并删除
     * @param string $name
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function pull(string $name)
    {
        return $this->store()->pull($name);
    }
}
