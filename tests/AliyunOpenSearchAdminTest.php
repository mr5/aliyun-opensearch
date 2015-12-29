<?php

use \Mockery as m;

/**
 * Administrative logic of AliYun Open Search WordPress plugin.
 */
class AliyunOpenSearchAdminTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AliyunOpenSearchAdmin
     */
    protected $admin;
    /**
     *
     * @var string
     */
    protected $pluginName = ALI_OPENSEARCH_PLUGIN_NAME;

    protected $pluginVersion = '0.1.0';

    protected $aliyunOpenSearchClient = null;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        require_once dirname(__FILE__) . '/../includes/AliyunOpenSearchAdmin.php';
    }

    public function setUp()
    {
        $this->aliyunOpenSearchClient = m::mock('AliyunOpenSearchClient');
        $this->admin = new AliyunOpenSearchAdmin($this->pluginName, $this->pluginVersion,
            $this->aliyunOpenSearchClient);

    }

    public function testRun()
    {

        $this->admin->run();
        $this->assertTrue(has_filter('admin_init', array($this->admin, 'registerSettings')) > 0);
        $this->assertTrue(has_filter('admin_menu', array($this->admin, 'addOptionsPage')) > 0);
        $this->assertTrue(has_filter('admin_enqueue_scripts', array($this->admin, 'enqueueStyles')) > 0);
        $this->assertTrue(has_filter('admin_enqueue_scripts', array($this->admin, 'enqueueScripts')) > 0);
        $this->assertTrue(has_filter('save_post', array($this->admin, 'afterSavePost')) > 0);
        $this->assertTrue(has_filter('delete_post', array($this->admin, 'afterDeletePost')) > 0);
    }


    public function testRegisterSettings()
    {
        require_once dirname(__FILE__) . '/../includes/AliyunOpenSearchOptions.php';
        $this->admin->registerSettings();
        $this->assertEquals(AliyunOpenSearchOptions::getAllSettingKeys(), get_registered_settings());
    }

    public function testAddOptionsPage()
    {
        $this->admin->addOptionsPage();
        $this->assertTrue(aos_has_page($this->pluginName . '-reindex'));
        $this->assertTrue(aos_has_page($this->pluginName . '-options'));
    }

    /**
     * Create index for all posts.
     *
     * @return void
     */
    public function testIndexPosts()
    {
        global $_REQUEST;
        $_REQUEST['paged'] = 0;
        $searchClientPagedZero = m::mock('AliyunOpenSearchClient_');
        $searchClientPagedZero->shouldReceive('savePosts')->never();
        $this->admin->setOpenSearchClient($searchClientPagedZero);
        $this->admin->indexPosts();

        $_REQUEST['paged'] = 1;
        $searchClientPagedOne = m::mock('AliyunOpenSearchClient_');
        $searchClientPagedOne->shouldReceive('savePosts')->once();
        $this->admin->setOpenSearchClient($searchClientPagedOne);
        $this->admin->indexPosts();
        $_REQUEST['paged'] = 1;

        $this->assertTrue(
            in_array(
                plugin_dir_path(dirname(__FILE__)) . 'admin/views/indexPosts.php',
                get_included_files()
            )
        );
    }

    /**
     * @expectedException WPDieException
     */
    public function testIndexPostsDie()
    {
        $_REQUEST['paged'] = 1;
        $searchClient = m::mock('AliyunOpenSearchClient');
        $searchClient->shouldReceive('savePosts')->once()->andThrow('AliyunOpenSearchException');
        $this->admin->setOpenSearchClient($searchClient);
        $this->admin->indexPosts();
    }

    /**
     * @dataProvider afterSavePostProvider
     */
    public function testAfterSavePost($id, $post, $times)
    {
        if ($post != null) {
            add_post($id, $post);
        }
        $searchClient = m::mock('AliyunOpenSearchClient');
        $this->admin->setOpenSearchClient($searchClient);
        $searchClient->shouldReceive('savePosts')->times($times);
        $this->admin->afterSavePost($id);
    }

    /**
     * @expectedException WPDieException
     */
    public function testAfterSavePostDie()
    {
        add_post(1, new WP_Post());
        $searchClient = m::mock('AliyunOpenSearchClient');
        $this->admin->setOpenSearchClient($searchClient);
        $searchClient->shouldReceive('savePosts')->once()->andThrow('AliyunOpenSearchException');
        $this->admin->afterSavePost(1);
    }

    public function testDraftPost()
    {
        $post = new WP_Post();
        $post->post_status = 'auto-draft';
        add_post(4, $post);
        $searchClient = m::mock('AliyunOpenSearchClient');
        $this->admin->setOpenSearchClient($searchClient);
        $searchClient->shouldReceive('savePosts')->never();
        $this->admin->afterSavePost(4);
    }

    /**
     * @dataProvider afterSavePostProvider
     */
    public function testAfterDeletePost($id, $post, $times)
    {
        if ($post != null) {
            add_post($id, $post);
        }
        $searchClient = m::mock('AliyunOpenSearchClient');
        $this->admin->setOpenSearchClient($searchClient);
        $searchClient->shouldReceive('deletePosts')->times($times);
        $this->admin->afterDeletePost($id);
    }

    /**
     * @expectedException WPDieException
     */
    public function testAfterDeletePostDie()
    {
        add_post(1, new WP_Post());
        $searchClient = m::mock('AliyunOpenSearchClient');
        $this->admin->setOpenSearchClient($searchClient);
        $searchClient->shouldReceive('deletePosts')->once()->andThrow('AliyunOpenSearchException');
        $this->admin->afterDeletePost(1);
    }

    public function afterSavePostProvider()
    {
        $post = new WP_Post();
        $post->ID = 1;
        $post->post_type = 'post';

        $page = new WP_Post();
        $post->ID = 2;
        $post->post_type = 'page';

        $other = new WP_Post();
        $other->ID = 3;
        $other->post_type = 'other';
        $nullPost = null;
        return array(
            array(
                $post->ID,
                $post,
                1
            ),
            array(
                $page->ID,
                $page,
                1
            ),
            array(
                $other->ID,
                $other,
                0
            ),
            array(
                100000,
                null,
                0
            )
        );
    }

    public function testDisplayOptionsPage()
    {
        $this->admin->displayOptionsPage();
        $this->assertTrue(in_array(plugin_dir_path(__DIR__) . 'admin/views/options.php', get_included_files()));
    }

    public function testEnqueueStyles()
    {
        $this->admin->enqueueStyles();
        $this->assertTrue(has_asset(plugin_dir_url(__DIR__) . 'admin/css/opensearch.css'));

    }

    public function testEnqueueScripts()
    {
        $this->admin->enqueueScripts();

        $this->assertTrue(has_asset(plugin_dir_url(__DIR__) . 'admin/js/opensearch.js'));
    }

    public function tearDown()
    {
        clear_all_globals();
        $this->aliyunOpenSearchClient = null;
    }
}