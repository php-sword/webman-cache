<?php declare (strict_types=1);

namespace sword\Cache;

use Closure;
use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use sword\Cache\Contract\CacheHandlerInterface;
use Throwable;

/**
 * 缓存基础类
 */
abstract class Driver implements CacheInterface, CacheHandlerInterface
{
    /**
     * 驱动句柄
     * @var object
     */
    protected $handler = null;

    /**
     * 缓存读取次数
     * @var integer
     */
    protected int $readTimes = 0;

    /**
     * 缓存写入次数
     * @var integer
     */
    protected int $writeTimes = 0;

    /**
     * 缓存参数
     * @var array
     */
    protected array $options = [];

    /**
     * 缓存标签
     * @var array
     */
    protected array $tag = [];

    /**
     * 获取有效期
     * @param int|DateTimeInterface|DateInterval $expire 有效期
     * @return int
     */
    protected function getExpireTime($expire): int
    {
        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->getTimestamp() - time();
        } elseif ($expire instanceof DateInterval) {
            $expire = DateTime::createFromFormat('U', (string)time())
                    ->add($expire)
                    ->format('U') - time();
        }

        return (int)$expire;
    }

    /**
     * 获取实际的缓存标识
     * @param string $name 缓存名
     * @return string
     */
    public function getCacheKey(string $name): string
    {
        return $this->options['prefix'] . $name;
    }

    /**
     * 读取缓存并删除
     * @param string $name 缓存变量名
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function pull(string $name)
    {
        $result = $this->get($name, false);

        if ($result) {
            $this->delete($name);
        }
        return $result;
    }

    /**
     * 追加（数组）缓存
     * @param string $name  缓存变量名
     * @param mixed  $value 存储数据
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function push(string $name, $value): void
    {
        $item = $this->get($name, []);

        if (!is_array($item)) {
            throw new InvalidArgumentException('only array cache can be push');
        }

        $item[] = $value;

        if (count($item) > 1000) {
            array_shift($item);
        }

        $item = array_unique($item);

        $this->set($name, $item);
    }

    /**
     * 追加TagSet数据
     * @param string $name  缓存变量名
     * @param mixed  $value 存储数据
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function append(string $name, $value): void
    {
        $this->push($name, $value);
    }

    /**
     * 如果不存在则写入缓存
     * @param string   $name   缓存变量名
     * @param mixed    $value  存储数据
     * @param int|null $expire 有效时间 0为永久
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws Throwable
     */
    public function remember(string $name, $value, $expire = null)
    {
        if ($this->has($name)) {
            if (($hit = $this->get($name)) !== null) {
                return $hit;
            }
        }

        $time = time();

        while ($time + 5 > time() && $this->has($name . '_lock')) {
            // 存在锁定则等待
            usleep(200000);
        }

        try {
            // 锁定
            $this->set($name . '_lock', true);

            if ($value instanceof Closure) {
                // 获取缓存数据
                $value = Container::getInstance()->invokeFunction($value);
            }

            // 缓存数据
            $this->set($name, $value, $expire);

            // 解锁
            $this->delete($name . '_lock');
        } catch (Exception | Throwable $e) {
            $this->delete($name . '_lock');
            throw $e;
        }

        return $value;
    }

    /**
     * 缓存标签
     * @param string|array $name 标签名
     * @return TagSet
     */
    public function tag($name): TagSet
    {
        $name = (array)$name;
        $key  = implode('-', $name);

        if (!isset($this->tag[$key])) {
            $this->tag[$key] = new TagSet($name, $this);
        }

        return $this->tag[$key];
    }

    /**
     * 获取标签包含的缓存标识
     * @param string $tag 标签标识
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getTagItems(string $tag): array
    {
        $name = $this->getTagKey($tag);
        return $this->get($name, []);
    }

    /**
     * 获取实际标签名
     * @param string $tag 标签名
     * @return string
     */
    public function getTagKey(string $tag): string
    {
        return $this->options['tag_prefix'] . md5($tag);
    }

    /**
     * 序列化数据
     * @param mixed $data 缓存数据
     * @return string
     */
    protected function serialize($data): string
    {
        if (is_numeric($data)) {
            return (string)$data;
        }

        $serialize = $this->options['serialize'][0] ?? "serialize";

        return $serialize($data);
    }

    /**
     * 反序列化数据
     * @param string $data 缓存数据
     * @return mixed
     */
    protected function unserialize(string $data)
    {
        if (is_numeric($data)) {
            return $data;
        }

        $unserialize = $this->options['serialize'][1] ?? "unserialize";

        return $unserialize($data);
    }

    /**
     * 返回句柄对象，可执行其它高级方法
     * @return object
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * 返回缓存读取次数
     * @return int
     */
    public function getReadTimes(): int
    {
        return $this->readTimes;
    }

    /**
     * 返回缓存写入次数
     * @return int
     */
    public function getWriteTimes(): int
    {
        return $this->writeTimes;
    }

    /**
     * 读取缓存
     * @param iterable $keys    缓存变量名
     * @param mixed    $default 默认值
     * @return iterable
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * 写入缓存
     * @param iterable              $values 缓存数据
     * @param null|int|DateInterval $ttl    有效时间 0为永久
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool
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
     * 删除缓存
     * @param iterable $keys 缓存变量名
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $result = $this->delete($key);

            if (false === $result) {
                return false;
            }
        }

        return true;
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->handler, $method], $args);
    }
}
