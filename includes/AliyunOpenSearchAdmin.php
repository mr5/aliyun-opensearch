<?php

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
     * Constructor
     *
     * @param string $pluginName The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($pluginName, $version)
    {

        $this->pluginName = $pluginName;
        $this->version = $version;

    }

    /**
     * Run AliYun Open Search admin.
     *
     * @return void
     */
    public function run()
    {
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_init', function () {
            add_dashboard_page('哈哈', '哈哈', 'manage_options', 'hahah', function () {
                echo 'hello world';
            });
        });
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
//            $this->pluginName,
//            $this->pluginName,
            '阿里云搜索',
            '阿里云搜索',
            'manage_options',
            $this->pluginName . '-options',
            array($this, 'displayOptionsPage')
        );
    }


    /**
     * Intercept post related actions.
     *
     * @return void
     */
    protected function interceptPostRelatedActions()
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
            || !in_array($post->post_type, ['page', 'post'])
            || $post->post_parent != 0
        ) {
            return;
        }
        // discard auto-drafts
        if ($post->post_status == 'auto-draft') {
            return;
        }
        $post = get_post($postId);
        AliyunOpenSearchClient::autoload()->savePosts([$post]);
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
        AliyunOpenSearchClient::autoload()->deletePosts(array(get_post($postId)));
    }

    /**
     * Display AliYun Open Search options page.
     *
     * @return void
     */
    public function displayOptionsPage()
    {
        include plugin_dir_path(__DIR__) . 'admin/views/options.php';
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
            plugin_dir_url(__DIR__) . 'admin/css/opensearch.css', array(),
            $this->version, 'all'
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
            plugin_dir_url(__DIR__) . 'admin/js/opensearch.js',
            array('jquery'),
            $this->version, false
        );
    }
}