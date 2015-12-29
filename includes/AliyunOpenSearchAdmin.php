<?php

/**
 * Administrative logic of AliYun Open Search WordPress plugin.
 */
class AliyunOpenSearchAdmin
{

    /**
     * The name of this plugin.
     *
     * @var string $pluginName
     */
    protected $pluginName;

    /**
     * The version of this plugin.
     *
     * @var string $version
     */
    protected $version;

    /**
     * @var AliyunOpenSearchClient
     */
    protected $aliyunOpenSearchClient;

    /**
     * Post types sync to aliyun accepted.
     *
     * @var array
     */
    protected $acceptedSyncTypes = array('page', 'post');

    /**
     * Constructor
     *
     * @param string $pluginName The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($pluginName, $version, AliyunOpenSearchClient $aliyunOpenSearchClient)
    {

        $this->pluginName = $pluginName;
        $this->version = $version;

        $this->aliyunOpenSearchClient = $aliyunOpenSearchClient;

    }

    /**
     * @param $aliyunOpenSearchClient
     */
    public function setOpenSearchClient($aliyunOpenSearchClient)
    {
        $this->aliyunOpenSearchClient = $aliyunOpenSearchClient;

    }

    /**
     * @return AliyunOpenSearchClient
     */
    private function getOpenSearchClient()
    {
        return $this->aliyunOpenSearchClient;
    }

    /**
     * Run AliYun Open Search admin.
     *
     * @return void
     */
    public function run()
    {

        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_menu', array($this, 'addOptionsPage'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        $this->interceptPostRelatedActions();
    }

    /**
     * Register options settings.
     *
     * @return void
     */
    public function registerSettings()
    {
        foreach (AliyunOpenSearchOptions::getAllSettingKeys() as $optionKey) {
            register_setting($this->pluginName, $optionKey);
        }
    }

    /**
     * Register an options page.
     *
     * @return void
     */
    public function addOptionsPage()
    {
        add_options_page(
            '阿里云搜索 - 配置',
            '阿里云搜索',
            'manage_options',
            $this->pluginName . '-options',
            array($this, 'displayOptionsPage')
        );

        add_management_page(
            '阿里云搜索 - 索引所有文章到阿里云',
            '索引所有文章到阿里云',
            'manage_options',
            $this->pluginName . '-reindex',
            array($this, 'indexPosts')
        );
    }

    /**
     * Create index for all posts.
     *
     * @return void
     */
    public function indexPosts()
    {
        $query = new WP_Query();

        $posts_per_page = 10;
        $paged = isset($_REQUEST['paged']) ? intval($_REQUEST['paged']) : 0;
        $hasMore = false;
        $currentProcessing = 0;
        if ($paged > 0) {
            $posts = $query->query(
                array(
                    'posts_per_page' => $posts_per_page,
                    'paged' => $paged
                )
            );
            $currentProcessing = count($posts) + (($paged - 1) * $posts_per_page);
            $hasMore = $paged * $posts_per_page < $query->found_posts;
            try {
                $this->getOpenSearchClient()->savePosts($posts);
            } catch (AliyunOpenSearchException $e) {
                wp_die(
                    sprintf(
                        '保存文章到阿里云时发生错误:%s, 请检查您的配置是否有误. <a href="%s" target="_blank">查看错误码说明</a>',
                        $e->getMessage(),
                        AliyunOpenSearch::getErrorCodeReferencesUrl()
                    )
                );
            }
        }

        include plugin_dir_path(dirname(__FILE__)) . 'admin/views/indexPosts.php';
    }

    /**
     * Intercept post related actions.
     *
     * @return void
     */
    private function interceptPostRelatedActions()
    {
        add_action('save_post', array($this, 'afterSavePost'));
        add_action('delete_post', array($this, 'afterDeletePost'));
    }

    /**
     * Sync post to aliyun after post saved.
     *
     * @param int $postId The ID of saved post
     *
     * @return void
     */
    public function afterSavePost($postId)
    {
        $post = get_post($postId);
        if (!$post
            || !in_array($post->post_type, $this->acceptedSyncTypes)
            || $post->post_parent != 0
        ) {
            return;
        }
        // discard auto-drafts
        if ($post->post_status == 'auto-draft') {
            return;
        }
        try {
            $this->getOpenSearchClient()->savePosts(array($post));
        } catch (AliyunOpenSearchException $e) {
            wp_die(
                sprintf(
                    '保存文章到阿里云时发生错误:%s, 请检查您的配置是否有误. <a href="%s" target="_blank">查看错误码说明</a>',
                    $e->getMessage(),
                    AliyunOpenSearch::getErrorCodeReferencesUrl()
                )
            );
        }
    }

    /**
     * Delete post from aliyun after posts deleted.
     *
     * @param int $postId ID of the post which has been deleted.
     *
     * @return void
     */
    public function afterDeletePost($postId)
    {
        $post = get_post($postId);
        if (!$post
            || !in_array($post->post_type, $this->acceptedSyncTypes)
            || $post->post_parent != 0
        ) {
            return;
        }
        try {
            $this->getOpenSearchClient()->deletePosts(array($post));
        } catch (AliyunOpenSearchException $e) {
            wp_die(
                sprintf(
                    '从阿里云删除文章时发生错误：%s，请检查您的配置是否有误。<a href="%s" target="_blank">查看错误码说明</a>',
                    $e->getMessage(),
                    AliyunOpenSearch::getErrorCodeReferencesUrl()
                )
            );
        }
    }

    /**
     * Display AliYun Open Search options page.
     *
     * @return void
     */
    public function displayOptionsPage()
    {
        include plugin_dir_path(dirname(__FILE__)) . 'admin/views/options.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @return void
     */
    public function enqueueStyles()
    {
        wp_enqueue_style(
            $this->pluginName,
            plugin_dir_url(dirname(__FILE__)) . 'admin/css/opensearch.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @return void
     */
    public function enqueueScripts()
    {
        wp_enqueue_script(
            $this->pluginName,
            plugin_dir_url(dirname(__FILE__)) . 'admin/js/opensearch.js',
            array('jquery'),
            $this->version,
            false
        );
    }
}
