<?php

class AliyunOpenSearchOptions
{

    const OPTION_NAME_ACCESS_KEY = 'aliyun_open_search_access_key';
    const OPTION_NAME_ACCESS_SECRET = 'aliyun_open_search_access_secret';
    const OPTION_NAME_HOST = 'aliyun_open_search_access_host';
    const OPTION_NAME_APP_NAME = 'aliyun_open_search_app_name';


    public static function setAccessKey($accessKey)
    {
        update_option(static::OPTION_NAME_ACCESS_KEY, $accessKey);
    }

    public static function getAccessKey()
    {
        return get_option(static::OPTION_NAME_ACCESS_KEY);
    }

    public static function setSecret($secret)
    {
        update_option(static::OPTION_NAME_ACCESS_SECRET, $secret);
    }

    public static function getSecret()
    {
        return get_option(static::OPTION_NAME_ACCESS_SECRET);
    }

    public static function setHost($host)
    {
        update_option(static::OPTION_NAME_HOST, $host);
    }

    public static function getHost()
    {
        return get_option(static::OPTION_NAME_HOST);
    }

    public static function setAppName($appName)
    {
        update_option(static::OPTION_NAME_APP_NAME, $appName);
    }

    public static function getAppName()
    {
        return get_option(static::OPTION_NAME_APP_NAME);
    }

    public static function getAllSettingKeys()
    {
        return array(
            static::OPTION_NAME_ACCESS_KEY,
            static::OPTION_NAME_ACCESS_SECRET,
            static::OPTION_NAME_APP_NAME,
            static::OPTION_NAME_HOST
        );
    }
}