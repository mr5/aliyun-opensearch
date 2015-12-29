<?php
use \Mockery as m;

class AliyunOpenSearchClientTest extends \PHPUnit_Framework_TestCase
{
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        require_once dirname(__FILE__) . '/../includes/AliyunOpenSearchClient.php';
        require_once dirname(__FILE__) . '/../includes/AliyunOpenSearchException.php';

        require_once dirname(__FILE__) . '/../sdk/CloudsearchClient.php';
        require_once dirname(__FILE__) . '/../sdk/CloudsearchDoc.php';
        require_once dirname(__FILE__) . '/../sdk/CloudsearchIndex.php';
        require_once dirname(__FILE__) . '/../sdk/CloudsearchSearch.php';
    }

    protected function generateSearchClient()
    {
        return new AliyunOpenSearchClient('test_key', 'test_secret', 'test_host', 'test_appName');
    }

    public function testGetSDKClients()
    {
        // Testing for no setter called.
        $client = $this->generateSearchClient();
        $this->assertInstanceOf('CloudsearchClient', $client->getCloudSearchClient());
        $this->assertInstanceOf('CloudsearchSearch', $client->getCloudsearchSearch());
        $this->assertInstanceOf('CloudsearchDoc', $client->getCloudSearchDoc());

        // Testing for pass sdk clients via setter.
        $client = $this->generateSearchClient();
        $cloudsearchClient = m::mock('CloudsearchClient');
        $client->setCloudSearchClient($cloudsearchClient);
        $cloudsearchSearch = m::mock('CloudsearchSearch');
        $client->setCloudsearchSearch($cloudsearchSearch);
        $cloudsearchDoc = m::mock('CloudsearchDoc');
        $client->setCloudSearchDoc($cloudsearchDoc);
        $this->assertEquals($cloudsearchClient, $client->getCloudSearchClient());
        $this->assertEquals($cloudsearchSearch, $client->getCloudsearchSearch());
        $this->assertEquals($cloudsearchDoc, $client->getCloudSearchDoc());
    }


    /**
     * Delete given posts from AliYun Open Search.
     *
     * @param WP_Post[] $posts Posts will be deleted.
     *
     * @return int
     */
    public function testDeletePosts()
    {
        $client = $this->generateSearchClient();
        $cloudsearchDoc = m::mock('CloudsearchDoc');
        $client->setCloudSearchDoc($cloudsearchDoc);
        $cloudsearchDoc->shouldReceive('remove')->never();
        $client->deletePosts(array());


        $client = $this->generateSearchClient();
        $cloudsearchDoc = m::mock('CloudsearchDoc');
        $client->setCloudSearchDoc($cloudsearchDoc);
        $cloudsearchDoc->shouldReceive('remove')->once();
        $client->deletePosts(array(
            new WP_Post()
        ));
    }

    /**
     * Save posts to AliYun Open Search.
     *
     * @param WP_Post[] $posts WP_Posts that you want to index.
     *
     * @return int
     */
    public function testSavePosts()
    {
        $client = $this->generateSearchClient();
        $cloudsearchDoc = m::mock('CloudsearchDoc');
        $client->setCloudSearchDoc($cloudsearchDoc);
        $cloudsearchDoc->shouldReceive('add')->never();
        $client->savePosts(array());


        $client = $this->generateSearchClient();
        $cloudsearchDoc = m::mock('CloudsearchDoc');
        $client->setCloudSearchDoc($cloudsearchDoc);
        $cloudsearchDoc->shouldReceive('add')->once();
        $client->savePosts(array(
            new WP_Post()
        ));

        return;
    }

    /**
     * @expectedException AliyunOpenSearchException
     */
    public function testAPIResultProcess()
    {
        $client = $this->generateSearchClient();
        $cloudsearchDoc = m::mock('CloudsearchDoc');
        $client->setCloudSearchDoc($cloudsearchDoc);
        $cloudsearchDoc->shouldReceive('add')->once()->andReturn('{"status":"FAIL","errors":[{"code":5001,"message":"User doesnot exist"}],"request_id":"1451395397046965800818613"}');
        $client->savePosts(array(
            new WP_Post()
        ));

    }


    public function testSearch()
    {
        $client = $this->generateSearchClient();
        $cloudsearchSearch = m::mock('CloudsearchSearch');
        $client->setCloudsearchSearch($cloudsearchSearch);
        //         // addIndex, setQueryString, setFormat, setHits, setStartHit, addSummary

        $cloudsearchSearch->shouldReceive('addIndex')->once();
        $cloudsearchSearch->shouldReceive('setQueryString')->once();
        $cloudsearchSearch->shouldReceive('setFormat')->once();
        $cloudsearchSearch->shouldReceive('setHits')->once();
        $cloudsearchSearch->shouldReceive('setStartHit')->once();
        $cloudsearchSearch->shouldReceive('addSummary')->twice();
        $cloudsearchSearch->shouldReceive('search')->once();

        $this->assertEquals(
            array(
                'total' => 0,
                'posts' => array()
            ),
            $client->search("default:'aliyun'")
        );

        return;
    }

    /**
     * @expectedException AliyunOpenSearchException
     */
    public function testSearchException()
    {
        $client = $this->generateSearchClient();
        $cloudsearchSearch = m::mock('CloudsearchSearch');
        $client->setCloudsearchSearch($cloudsearchSearch);
        //         // addIndex, setQueryString, setFormat, setHits, setStartHit, addSummary

        $cloudsearchSearch->shouldReceive('addIndex')->once();
        $cloudsearchSearch->shouldReceive('setQueryString')->once();
        $cloudsearchSearch->shouldReceive('setFormat')->once();
        $cloudsearchSearch->shouldReceive('setHits')->once();
        $cloudsearchSearch->shouldReceive('setStartHit')->once();
        $cloudsearchSearch->shouldReceive('addSummary')->twice();
        $cloudsearchSearch->shouldReceive('search')->once()->andReturn('{"status":"FAIL","request_id":"145140093617790786264045","result":{"searchtime":0.012463,"total":0,"num":0,"viewtotal":0,"items":[],"facet":[]},"errors":[{"code":5001,"message":"User doesnot exist"}],"tracer":""}');
        $client->search("default:'aliyun'");
    }

    public function testSearchHaveResults()
    {
        $client = $this->generateSearchClient();
        $cloudsearchSearch = m::mock('CloudsearchSearch');
        $client->setCloudsearchSearch($cloudsearchSearch);
        //         // addIndex, setQueryString, setFormat, setHits, setStartHit, addSummary

        $cloudsearchSearch->shouldReceive('addIndex')->once();
        $cloudsearchSearch->shouldReceive('setQueryString')->once();
        $cloudsearchSearch->shouldReceive('setFormat')->once();
        $cloudsearchSearch->shouldReceive('setHits')->once();
        $cloudsearchSearch->shouldReceive('setStartHit')->once();
        $cloudsearchSearch->shouldReceive('addSummary')->twice();
        $cloudsearchSearch->shouldReceive('search')->once()->andReturn('
{"status":"OK","request_id":"145140143817790786557088","result":{"searchtime":0.007401,"total":6,"num":6,"viewtotal":6,"items":[{"id":"7","post_author":"1","post_date_unixtime":"1450365535","post_title":"中电信2016年规划70亿元激励终端 新增1亿4G用户","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","pinged":"","post_modified_unixtime":"1450366406","post_content":"在今日下午召开的中国电信(微博 终端产业2016合作战略发布会上，中国电信表示明年将拿出70亿元激励基金，通过定制和流量合作激励终端发展，并制定了新增1亿4G...","post_content_filtered":"","post_parent":"0","guid":"http://wp.local/?p=7","menu_order":"0","tags":"{\"term_taxonomy_id\":11,\"count\":1,\"description\":\"\",\"name\":\"中国电信\",\"term_id\":11,\"parent\":0,\"slug\":\"%e4%b8%ad%e5%9b%bd%e7%94%b5%e4%bf%a1\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":10,\"count\":1,\"description\":\"\",\"name\":\"电信\",\"term_id\":10,\"parent\":0,\"slug\":\"%e7%94%b5%e4%bf%a1\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}","comment_count":"0","post_category":"2","post_thumbnail":"","index_name":"aos_wp_plugin_test"},{"id":"34","post_author":"1","post_date_unixtime":"1450367025","post_title":"阿里云筹划明年进入海外市场","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","pinged":"","post_modified_unixtime":"1451397276","post_content":"阿里云计划在海外设立云数据中心，向部署海外业务的中国企业和海外本土企业输出云计算服务能力。据悉，该计划将于明年3月对外正式公布。目前，阿里云计算经历了5...","post_content_filtered":"","post_parent":"0","guid":"http://wp.local/?p=34","menu_order":"0","tags":"{\"term_taxonomy_id\":13,\"count\":10,\"description\":\"\",\"name\":\"阿里云\",\"term_id\":13,\"parent\":0,\"slug\":\"%e9%98%bf%e9%87%8c%e4%ba%91\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}","comment_count":"0","post_category":"12","post_thumbnail":"","index_name":"aos_wp_plugin_test"},{"id":"19","post_author":"1","post_date_unixtime":"1450365791","post_title":"阿里云万网正式合并 万网品牌继续保留","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","pinged":"","post_modified_unixtime":"1450366405","post_content":"万网是中国最大的域名注册服务商，拥有中国最大的虚拟主机市场份额。此次阿里云与万网的品牌融合，是阿里巴巴集团继续在云计算市场深耕的标志之一，也表明了阿里...","post_content_filtered":"","post_parent":"0","guid":"http://wp.local/?p=19","menu_order":"0","tags":"{\"term_taxonomy_id\":20,\"count\":1,\"description\":\"\",\"name\":\"万网\",\"term_id\":20,\"parent\":0,\"slug\":\"%e4%b8%87%e7%bd%91\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":21,\"count\":1,\"description\":\"\",\"name\":\"并购\",\"term_id\":21,\"parent\":0,\"slug\":\"%e5%b9%b6%e8%b4%ad\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":13,\"count\":10,\"description\":\"\",\"name\":\"阿里云\",\"term_id\":13,\"parent\":0,\"slug\":\"%e9%98%bf%e9%87%8c%e4%ba%91\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}","comment_count":"0","post_category":"2\t7\t19","post_thumbnail":"","index_name":"aos_wp_plugin_test"},{"id":"26","post_author":"1","post_date_unixtime":"1450366634","post_title":"阿里云牵手全球知名游戏商 加速商业化","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","pinged":"","post_modified_unixtime":"1450366634","post_content":"同时，梦宝谷游戏中心还将网罗日本、美国、中国乃至全球开发者提供的优质手机游戏。通过双方的合作，将为用户提供更好的游戏体验。而针对国内游戏开发者，双方也...","post_content_filtered":"","post_parent":"0","guid":"http://wp.local/?p=26","menu_order":"0","tags":"{\"term_taxonomy_id\":13,\"count\":10,\"description\":\"\",\"name\":\"阿里云\",\"term_id\":13,\"parent\":0,\"slug\":\"%e9%98%bf%e9%87%8c%e4%ba%91\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":14,\"count\":2,\"description\":\"\",\"name\":\"阿里巴巴\",\"term_id\":14,\"parent\":0,\"slug\":\"%e9%98%bf%e9%87%8c%e5%b7%b4%e5%b7%b4\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}","comment_count":"0","post_category":"2\t12","post_thumbnail":"","index_name":"aos_wp_plugin_test"},{"id":"4","post_author":"1","post_date_unixtime":"1450364592","post_title":"人民币汇率进入“灰色地带”？","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","pinged":"","post_modified_unixtime":"1450366406","post_content":"中国外汇交易中心上周公布了人民币汇率指数，央行官网也转载中国货币网评论员文章称，人民币新汇率指数将有助于引导市场改变过去主要关注人民币对美元双边汇率的...","post_content_filtered":"","post_parent":"0","guid":"http://wp.local/?p=4","menu_order":"0","tags":"{\"term_taxonomy_id\":4,\"count\":1,\"description\":\"\",\"name\":\"SDR\",\"term_id\":4,\"parent\":0,\"slug\":\"sdr\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":5,\"count\":1,\"description\":\"\",\"name\":\"中国央行\",\"term_id\":5,\"parent\":0,\"slug\":\"%e4%b8%ad%e5%9b%bd%e5%a4%ae%e8%a1%8c\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":3,\"count\":1,\"description\":\"\",\"name\":\"人民币\",\"term_id\":3,\"parent\":0,\"slug\":\"%e4%ba%ba%e6%b0%91%e5%b8%81\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":6,\"count\":1,\"description\":\"\",\"name\":\"外汇\",\"term_id\":6,\"parent\":0,\"slug\":\"%e5%a4%96%e6%b1%87\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}","comment_count":"0","post_category":"2","post_thumbnail":"","index_name":"aos_wp_plugin_test"},{"id":"30","post_author":"1","post_date_unixtime":"1450366872","post_title":"浅谈阿里云手机系统及移动未来","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","pinged":"","post_modified_unixtime":"1450366872","post_content":"之所以有许多人认为阿里云OS是忽悠，部分原因是道听途说，另一部分原因是不相信中国有企业去做底层的云计算。当然，不可否认的是阿里云OS目前来看做的还算不上...","post_content_filtered":"","post_parent":"0","guid":"http://wp.local/?p=30","menu_order":"0","tags":"{\"term_taxonomy_id\":25,\"count\":1,\"description\":\"\",\"name\":\"4G\",\"term_id\":25,\"parent\":0,\"slug\":\"4g\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":13,\"count\":10,\"description\":\"\",\"name\":\"阿里云\",\"term_id\":13,\"parent\":0,\"slug\":\"%e9%98%bf%e9%87%8c%e4%ba%91\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}\t{\"term_taxonomy_id\":16,\"count\":3,\"description\":\"\",\"name\":\"阿里云OS\",\"term_id\":16,\"parent\":0,\"slug\":\"%e9%98%bf%e9%87%8c%e4%ba%91os\",\"taxonomy\":\"post_tag\",\"term_group\":0,\"filter\":\"raw\"}","comment_count":"0","post_category":"2\t7\t24","post_thumbnail":"","index_name":"aos_wp_plugin_test"}],"facet":[]},"errors":[],"tracer":""}
'
        );
        $ret = $client->search("default:'aliyun'");
        $this->assertEquals(6, $ret['total']);
        $this->assertEquals(6, count($ret['posts']));

    }


    public function testAutoload()
    {
        $client1 = AliyunOpenSearchClient::autoload();
        $client2 = AliyunOpenSearchClient::autoload();

        $this->assertEquals($client1, $client2);
    }

    public function tearDown()
    {
        clear_options();
    }

}