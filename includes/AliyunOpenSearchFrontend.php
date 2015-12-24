<?php

class AliyunOpenSearchFrontend
{

    /**
     * Plugin name.
     *
     * @var string $plugin_name
     */
    protected $pluginName;

    /**
     * The version of plugin.
     *
     * @var string $version
     */
    protected $version;

    /**
     * Constructor
     *
     * @param string $pluginName plugin name
     * @param string $version plugin version
     */
    public function __construct($pluginName, $version)
    {

        $this->pluginName = $pluginName;
        $this->version = $version;
    }

    public function run()
    {
        add_action('template_redirect', array($this, 'searchIntercept'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    public function searchIntercept()
    {
        /** @var WP_Query $wp_the_query */
        global $wp_the_query;
        if ($wp_the_query->is_search()
            && !$wp_the_query->is_admin
            && $wp_the_query->is_main_query()
        ) {
            $aosClient = AliyunOpenSearchClient::autoload();
            $keyword = $wp_the_query->query['s'];
            $limit = isset($wp_the_query->query_vars['posts_per_page'])
                ? $wp_the_query->query_vars['posts_per_page'] : 10;
            $page = 1;
            if (isset($wp_the_query->query_vars['paged'])) {
                $page = intval($wp_the_query->query_vars['paged']);
                $page = $page > 0 ? $page : 1;
            }
            $offset = ($page - 1) * $limit;
            $ret = $aosClient->search(
                "default:'{$keyword}' AND post_status:'publish'",
                $offset,
                $limit
            );
            $wp_the_query->posts = $ret['posts'];
            $wp_the_query->post_count = count($ret['posts']);
            $wp_the_query->found_posts = $ret['total'];

            return false;
        }
    }

    /**
     * Register the stylesheets.
     *
     * @return void
     */
    public function enqueueStyles()
    {
        wp_enqueue_style(
            $this->pluginName,
            plugin_dir_url(__DIR__) . 'frontend/css/opensearch.css', array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @return void
     */
    public function enqueueScripts()
    {
        wp_enqueue_script(
            $this->pluginName,
            plugin_dir_url(__DIR__) . 'frontend/js/opensearch.js',
            array('jquery'),
            $this->version,
            false
        );
    }
}