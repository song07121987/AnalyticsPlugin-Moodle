<?php
namespace Piwik\Custom;

use Piwik\Cache;
use Piwik\Config\ClientConfig;

class CacheHelper
{

    private static $pcache = null;

    private static function getCache () {
        if (self::$pcache == null)
            self::$pcache = Cache::getEagerCache();
        return self::$pcache;
    }

    public static function hasKey ($name) {
        if (ClientConfig::GLOBAL_CACHE  == 'Eager') {
            $cache = self::getCache();
            if ($cache->contains($name))
                return true;
        }
        return false;
    }

    public static function getValue ($name) {
        if (ClientConfig::GLOBAL_CACHE  == 'Eager') {
            $cache = self::getCache();
            if ($cache->contains($name))
                return $cache->fetch($name);
        }
        return null;
    }

    public static function setValue ($name, $value, $persist = true, $lifetime = 0) {
        if (ClientConfig::GLOBAL_CACHE  == 'Eager') {
            $cache = self::getCache();
            if ($cache->contains($name))
                $cache->delete($name);
            $cache->save($name, $value);
            if ($persist)
                $cache->persistCacheIfNeeded($lifetime);
            return true;
        }
        return false;
    }

    public static function deleteValue ($name, $persist = true) {
        if (ClientConfig::GLOBAL_CACHE  == 'Eager') {
            $cache = self::getCache();
            if ($cache->contains($name))
                $cache->delete($name);
            if ($persist)
                $cache->persistCacheIfNeeded(0);
            return true;
        }
        return false;
    }

    public static function flush () {
        if (ClientConfig::GLOBAL_CACHE  == 'Eager') {
            $cache = self::getCache();
            $cache->flushAll();
            return true;
        }
        return false;
    }
}
?>