<?php

declare (strict_types=1);

namespace sword\Cache\Facade;

use DateInterval;
use DateTime;
use sword\Cache\Driver;
use sword\Cache\TagSet;

/**
 * @see \sword\Cache\Cache
 * @method static string|null getDefaultDriver() 默认驱动
 * @method static mixed getConfig(null|string $name = null, mixed $default = null) 获取缓存配置
 * @method static array getStoreConfig(string $store, string $name = null, null $default = null) 获取驱动配置
 * @method static Driver store(string $name = null) 连接或者切换缓存
 * @method static bool clear() 清空缓冲池
 * @method static mixed get(string $key, mixed $default = null) 读取缓存
 * @method static bool set(string $key, mixed $value, null|int|DateTime $ttl = null) 写入缓存
 * @method static bool delete(string $key) 删除缓存
 * @method static iterable getMultiple(iterable $keys, mixed $default = null) 读取缓存
 * @method static bool setMultiple(iterable $values, null|int|DateInterval $ttl = null) 写入缓存
 * @method static bool deleteMultiple(iterable $keys) 删除缓存
 * @method static bool has(string $key) 判断缓存是否存在
 * @method static TagSet tag(string|array $name) 缓存标签
 * @method static TagSet remember(string $name, $value, ?int $expire) 如果不存在则写入缓存
 */
class Cache
{
    protected static ?object $_instance;

    public static function instance()
    {
        if (!isset(static::$_instance)) {
            static::$_instance = new \sword\Cache\Cache();
        }
        return static::$_instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(... $arguments);
    }

}
