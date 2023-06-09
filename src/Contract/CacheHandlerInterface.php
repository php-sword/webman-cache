<?php

declare (strict_types=1);

namespace sword\Cache\Contract;

use DateTime;

/**
 * 缓存驱动接口
 */
interface CacheHandlerInterface
{
    /**
     * 判断缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has(string $name);

    /**
     * 读取缓存
     * @param string $name    缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * 写入缓存
     * @param string            $name   缓存变量名
     * @param mixed             $value  存储数据
     * @param null|int|DateTime $expire 有效时间（秒）
     * @return bool
     */
    public function set(string $name, $value, $expire = null);

    /**
     * 自增缓存（针对数值缓存）
     * @param string $name 缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function inc(string $name, int $step = 1);

    /**
     * 自减缓存（针对数值缓存）
     * @param string $name 缓存变量名
     * @param int    $step 步长
     * @return false|int
     */
    public function dec(string $name, int $step = 1);

    /**
     * 删除缓存
     * @param string $name 缓存变量名
     * @return bool
     */
    public function delete(string $name);

    /**
     * 清除缓存
     * @return bool
     */
    public function clear();

    /**
     * 删除缓存标签
     * @param array $keys 缓存标识列表
     * @return void
     */
    public function clearTag(array $keys);

}
