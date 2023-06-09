<?php

declare (strict_types=1);

namespace sword\Cache;

use DateInterval;
use Psr\SimpleCache\InvalidArgumentException;
use throwable;

/**
 * 标签集合
 */
class TagSet
{
    /**
     * 标签的缓存Key
     * @var array
     */
    protected array $tag;

    /**
     * 缓存句柄
     * @var Driver
     */
    protected Driver $handler;

    /**
     * 架构函数
     * @param array  $tag   缓存标签
     * @param Driver $cache 缓存对象
     */
    public function __construct(array $tag, Driver $cache)
    {
        $this->tag     = $tag;
        $this->handler = $cache;
    }

    /**
     * 写入缓存
     * @access public
     * @param string                $name   缓存变量名
     * @param mixed                 $value  存储数据
     * @param null|int|DateInterval $expire 有效时间（秒）
     * @return bool
     * @throws InvalidArgumentException
     */
    public function set(string $name, $value, $expire = null): bool
    {
        $this->handler->set($name, $value, $expire);

        $this->append($name);

        return true;
    }

    /**
     * 追加缓存标识到标签
     * @access public
     * @param string $name 缓存变量名
     * @return void
     * @throws InvalidArgumentException
     */
    public function append(string $name): void
    {
        $name = $this->handler->getCacheKey($name);

        foreach ($this->tag as $tag) {
            $key = $this->handler->getTagKey($tag);
            $this->handler->append($key, $name);
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param iterable              $values 缓存数据
     * @param DateInterval|null|int $ttl    有效时间 0为永久
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple(iterable $values, $ttl = null): bool
    {
        foreach ($values as $key => $val) {
            $result = $this->set($key, $val, $ttl);

            if (false === $result) {
                return false;
            }
        }

        return true;
    }

    /**
     * 如果不存在则写入缓存
     * @access public
     * @param string   $name   缓存变量名
     * @param mixed    $value  存储数据
     * @param int|null $expire 有效时间 0为永久
     * @return mixed
     * @throws InvalidArgumentException
     * @throws throwable
     */
    public function remember(string $name, $value, $expire = null)
    {
        $result = $this->handler->remember($name, $value, $expire);

        $this->append($name);

        return $result;
    }

    /**
     * 清除缓存
     * @access public
     * @return bool
     * @throws InvalidArgumentException
     */
    public function clear(): bool
    {
        // 指定标签清除
        foreach ($this->tag as $tag) {
            $names = $this->handler->getTagItems($tag);
            $this->handler->clearTag($names);

            $key = $this->handler->getTagKey($tag);
            $this->handler->delete($key);
        }

        return true;
    }
}
