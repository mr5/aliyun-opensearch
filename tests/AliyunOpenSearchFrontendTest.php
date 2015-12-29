<?php

use \Mockery as m;

/**
 * Frontend logic of AliYun Open Search WordPress plugin.
 *
 */
class AliyunOpenSearchFrontendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AliyunOpenSearchFrontend
     */
    protected $frontend;
    /**
     *
     * @var string
     */
    protected $pluginName = ALI_OPENSEARCH_PLUGIN_NAME;

    protected $pluginVersion = '0.1.0';
    /**
     * @var m\MockInterface
     */
    protected $aliyunOpenSearchClient = null;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        require_once dirname(__FILE__) . '/../includes/AliyunOpenSearchFrontend.php';
    }

    public function setUp()
    {
        $this->aliyunOpenSearchClient = m::mock('AliyunOpenSearchClient');

        $this->frontend = new AliyunOpenSearchFrontend(
            $this->pluginName,
            $this->pluginVersion,
            $this->aliyunOpenSearchClient
        );

    }


    public function testRun()
    {

        $this->frontend->run();
        $this->assertTrue(has_filter('pre_get_posts', array($this->frontend, 'preGetPosts')) > 0);
        $this->assertTrue(has_filter('the_posts', array($this->frontend, 'thePosts')) > 0);
        $this->assertTrue(has_filter('wp_enqueue_scripts', array($this->frontend, 'enqueueStyles')) > 0);
        $this->assertTrue(has_filter('wp_enqueue_scripts', array($this->frontend, 'enqueueScripts')) > 0);
    }


    public function testThePosts()
    {
        global $wp_query;
        $this->assertNull($this->frontend->thePosts(null));

        set_query_var('is_search', false);
        $posts = array();
        $posts[] = new WP_Post();
        $posts[] = new WP_Post();
        $this->assertEquals($this->frontend->thePosts($posts), $posts);
        set_query_var('is_search', true);
        set_query_var('is_admin', true);
        $this->assertEquals($this->frontend->thePosts($posts), $posts);
        set_query_var('is_search', true);
        set_query_var('is_admin', false);
        $this->assertNotEquals($this->frontend->thePosts($posts), $posts);
        $post = new WP_Post();
        $post->ID = 3;
        $posts = array($post);
        set_query_var('is_main_query', true);
        $wp_query->is_main_query = true;
        $wp_query->is_search = true;
        $wp_query->is_admin = false;
        $wp_query->query['s'] = 'aliyun';
        $this->aliyunOpenSearchClient->shouldReceive('search')->andReturn(array(
            'posts' => $posts,
            'total' => 1
        ))->once();
        $this->frontend->preGetPosts($wp_query);
        $this->assertEquals($this->frontend->thePosts($posts), $posts);
    }

    /**
     * @dataProvider queryBuildingData
     */
    public function testPreGetPostsQueryBuilding($s, $post_per_page, $paged, $limit, $offset, $queryString)
    {
        $wp_query = new WP_Query();

        $searchClient = m::mock('AliYunSearch' . mt_rand(10000, 99999));
        $this->frontend->setOpenSearchClient($searchClient);
        $wp_query->is_search = true;
        $wp_query->is_admin = false;
        $wp_query->is_main_query = true;
        $wp_query->query['s'] = $s;
        $wp_query->query_vars['posts_per_page'] = $post_per_page;
        $wp_query->query_vars['paged'] = $paged;
        $searchClient->shouldReceive('search')->with($queryString,
            $offset,
            $limit
        )->once()->andReturn(array(
            'posts' => array(),
            'total' => 0
        ));
        $this->frontend->preGetPosts($wp_query);
    }

    public function queryBuildingData()
    {
        return array(
            array(
                'aliyun',   // s
                10,         // post_per_page
                1,          // paged
                10,         // limit
                0,          // offset
                "default:'aliyun' AND post_status:'publish'"
            ),
            array(
                'aliyun',
                5,
                2,
                5,
                5,
                "default:'aliyun' AND post_status:'publish'"
            ),
            array(
                'aliyun2',
                5,
                2,
                5,
                5,
                "default:'aliyun2' AND post_status:'publish'"
            ),
            array(
                'aliyun',   // s
                10,         // post_per_page
                0,          // paged
                10,         // limit
                0,          // offset
                "default:'aliyun' AND post_status:'publish'"
            ),
            array(
                'aliyun',   // s
                10,         // post_per_page
                null,          // paged
                10,         // limit
                0,          // offset
                "default:'aliyun' AND post_status:'publish'"
            ),
            array(
                'aliyun',   // s
                10,         // post_per_page
                -1,          // paged
                10,         // limit
                0,          // offset
                "default:'aliyun' AND post_status:'publish'"
            ),
        );
    }

    public function testPreGetPosts()
    {
        $wp_query = new WP_Query();
        $wp_query->is_main_query = true;
        $query_vars = $wp_query->query_vars;
        $this->assertEquals($this->frontend->preGetPosts($wp_query)->query_vars, $query_vars);
        $wp_query->is_admin = true;
        $query_vars = $wp_query->query_vars;
        $this->assertEquals($this->frontend->preGetPosts($wp_query)->query_vars, $query_vars);
        $wp_query->is_main_query = false;
        $query_vars = $wp_query->query_vars;
        $this->assertEquals($this->frontend->preGetPosts($wp_query)->query_vars, $query_vars);
        $wp_query->is_search = true;
        $wp_query->is_admin = false;
        $wp_query->is_main_query = true;
        $wp_query->query['s'] = 'aliyun';
        $wp_query->posts = array(new WP_Post());
        $post = new WP_Post();
        $post->ID = 3;
        $this->aliyunOpenSearchClient->shouldReceive('search')->andReturn(array('posts' => array($post), 'total' => 1));
        $query_vars = $wp_query->query_vars;
        $this->assertNotEquals($this->frontend->preGetPosts($wp_query)->query_vars, $query_vars);
    }

    /**
     * @expectedException WPDieException
     */
    public function testPreGetPostsDie()
    {
        $wp_query = new WP_Query();

        $wp_query->is_search = true;
        $wp_query->is_admin = false;
        $wp_query->is_main_query = true;
        $wp_query->query['s'] = 'aliyun';
        $wp_query->posts = array(new WP_Post());
        $this->aliyunOpenSearchClient->shouldReceive('search')->andThrow('AliyunOpenSearchException');
        $this->frontend->preGetPosts($wp_query);
    }

    public function testEnqueueStyles()
    {
        $this->frontend->enqueueStyles();
        $this->assertTrue(has_asset(plugin_dir_url(dirname(__FILE__)) . 'frontend/css/opensearch.css'));

    }

    public function testEnqueueScripts()
    {
        $this->frontend->enqueueScripts();

        $this->assertTrue(has_asset(plugin_dir_url(dirname(__FILE__)) . 'frontend/js/opensearch.js'));
    }
}