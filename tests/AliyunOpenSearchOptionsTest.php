<?php

class AliyunOpenSearchOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        require_once dirname(__FILE__) . '/../includes/AliyunOpenSearchOptions.php';
    }

    public function testSetGetAccessKey()
    {

        AliyunOpenSearchOptions::setAccessKey('access_key');
        $this->assertEquals(AliyunOpenSearchOptions::getAccessKey(), 'access_key');
    }

    public function testSetGetSecret()
    {
        AliyunOpenSearchOptions::setSecret('access_secret');
        $this->assertEquals(AliyunOpenSearchOptions::getSecret(), 'access_secret');
    }

    public function testSetGetHost()
    {
        AliyunOpenSearchOptions::setHost('test_host');
        $this->assertEquals(AliyunOpenSearchOptions::getHost(), 'test_host');
    }

    public function testSetGetAppName()
    {
        AliyunOpenSearchOptions::setAppName('test_app_name');
        $this->assertEquals(AliyunOpenSearchOptions::getAppName(), 'test_app_name');
    }


    public function testGetAllSettingKeys()
    {
        $this->assertEquals(array(
            AliyunOpenSearchOptions::OPTION_NAME_ACCESS_KEY,
            AliyunOpenSearchOptions::OPTION_NAME_ACCESS_SECRET,
            AliyunOpenSearchOptions::OPTION_NAME_APP_NAME,
            AliyunOpenSearchOptions::OPTION_NAME_HOST
        ), AliyunOpenSearchOptions::getAllSettingKeys());
    }
}