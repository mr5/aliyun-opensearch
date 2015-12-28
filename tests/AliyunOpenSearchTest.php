<?php

use \Mockery as m;

class AliyunOpenSearchTest extends \PHPUnit_Framework_TestCase
{
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        require_once dirname(__FILE__) . '/../includes/AliyunOpenSearch.php';
    }

    /**
     * @dataProvider nameAndVersionProvider
     */
    public function testGetPluginNameAndVersion($name, $version)
    {
        $aliyunOpenSearch = new AliyunOpenSearch($name, $version);
        $this->assertEquals($aliyunOpenSearch->getPluginName(), $name);
        $this->assertEquals($aliyunOpenSearch->getVersion(), $version);

    }

    public function nameAndVersionProvider()
    {
        return array(
            array(
                'test1',
                '0.1.0'
            ),
            array(
                'test2',
                '0.1.2'
            ),
            array(
                'test3',
                '0.1.3'
            )
        );
    }

    /**
     * Plugin initialization, prepare something dependent.
     */
    public function testInitialize()
    {
        $aliyunOpenSearch = new AliyunOpenSearch('AliYun Open Search test kit', '0.1.0');

        $aliyunOpenSearch->initialize();

        $this->assertTrue(class_exists('AliyunOpenSearchAdmin'));
        $this->assertTrue(class_exists('AliyunOpenSearchFrontend'));
        $this->assertTrue(class_exists('AliyunOpenSearchClient'));
        $this->assertTrue(class_exists('AliyunOpenSearchOptions'));

    }


    public function testRun()
    {
        $aliyunOpenSearch = new AliyunOpenSearch('AliYun Open Search test kit', '0.1.0');
        $admin = m::mock('AliyunOpenSearchAdmin');
        $admin->shouldReceive('run')->once();
        $frontend = m::mock('AliyunOpenSearchFrontend');
        $frontend->shouldReceive('run')->once();
        $aliyunOpenSearch->run($admin, $frontend);
    }

    public function tearDown()
    {
        m::close();
    }
}