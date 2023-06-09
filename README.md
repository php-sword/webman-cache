# webman-cache

> 该插件基于`xpzhu/webman-cache`插件修改而来，感谢原作者的付出。

用于PHP缓存管理（PHP 7.4+），支持`PSR-6`及`PSR-16`缓存规范。

主要特性包括：

* 支持多缓存通道设置及切换
* 支持缓存数据递增/递减
* 支持门面调用
* 内置File/Redis/Memcache/Memcached/Wincache
* 支持缓存标签
* 支持闭包数据
* 支持`PSR-6`及`PSR-16`缓存规范

## 安装
```
composer require php-sword/webman-cache
```

## 使用说明：
```php
use sword\Cache\Facade\Cache;

// 设置缓存
Cache::set('key', 'value', 600);
// 判断缓存是否设置
Cache::has('key');
// 获取缓存
Cache::get('key');
// 删除缓存
Cache::delete('key');
// 清除缓存
Cache::clear();
// 读取并删除缓存
Cache::pull('key');
// 不存在则写入
Cache::remember('key', 10);

// 对于数值类型的缓存数据可以使用
// 缓存增+1
Cache::inc('key');
// 缓存增+5
Cache::inc('key',5);
// 缓存减1
Cache::dec('key');
// 缓存减5
Cache::dec('key',5);

// 使用缓存标签
Cache::tag('tag_name')->set('key','value',600);
// 删除某个标签下的缓存数据
Cache::tag('tag_name')->clear();
// 支持指定多个标签
Cache::tag(['tag1','tag2'])->set('key2','value',600);
// 删除多个标签下的缓存数据
Cache::tag(['tag1','tag2'])->clear();

// 使用多种缓存类型
$redis = Cache::store('redis');

$redis->set('var','value',600);
$redis->get('var');
```