<?php

/**
 * Frontend logic of AliYun Open Search WordPress plugin.
 *
 */
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
     * The ids that got from AliYun Open Search.
     *
     * @var array
     */
    protected $ids = array();
    /**
     * The current page number.
     *
     * @var int
     */
    protected $paged = 1;
    /**
     * Total page count.
     *
     * @var int
     */
    protected $pageCount = 0;
    /**
     * Total post count that will get from AliYun Open Search.
     *
     * @var int
     */
    protected $postCount = 0;
    /**
     * Posts got currently.
     *
     * @var WP_Post[]
     */
    protected $posts = array();
    /**
     * Post count limit per page.
     *
     * @var int
     */
    protected $limit = 10;
    /**
     * Current search keyword.
     *
     * @var string
     */
    protected $keyword = '';

    /**
     * @var AliyunOpenSearchClient
     */
    protected $aliyunOpenSearchClient;

    /**
     * Constructor
     *
     * @param string $pluginName plugin name
     * @param string $version plugin version
     */
    public function __construct($pluginName, $version, $aliyunOpenSearchClient)
    {

        $this->pluginName = $pluginName;
        $this->version = $version;

        $this->setOpenSearchClient($aliyunOpenSearchClient);
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
    protected function getOpenSearchClient()
    {
        return $this->aliyunOpenSearchClient;

    }

    /**
     * Run AliYun Open Search frontend.
     */
    public function run()
    {
        add_action('pre_get_posts', array($this, 'preGetPosts'));
        add_filter('the_posts', array($this, 'thePosts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
    }

    /**
     * Filter `the_posts`.
     *
     * @param WP_Post[] $posts
     * @return WP_Post[]
     */
    public function thePosts($posts)
    {
        if (!$posts || !is_search() || is_admin()) {
            return $posts;
        }
        global $wp_query;
        set_query_var('paged', $this->paged);
        set_query_var('posts_per_page', $this->limit);
        set_query_var('s', $this->keyword);
        $wp_query->max_num_pages = $this->pageCount;
        $wp_query->found_posts = $this->postCount;
        $lookupTable = array();
        foreach ($posts as $post) {
            $lookupTable[$post->ID] = $post;
        }
        $orderedPosts = array();
        foreach ($this->ids as $id) {
            if (isset($lookupTable[$id])) {
                $orderedPosts[] = $lookupTable[$id];
            }
        }
        return $orderedPosts;
    }

    /**
     * Filter `pre_get_posts`.
     *
     * @param WP_Query $wp_the_query
     * @return WP_Query
     */
    public function preGetPosts($wp_the_query)
    {
        /** @var WP_Query $wp_the_query */
        if ($wp_the_query->is_search()
            && !$wp_the_query->is_admin
            && $wp_the_query->is_main_query()
        ) {
            $this->keyword = $wp_the_query->query['s'];
            $this->limit = isset($wp_the_query->query_vars['posts_per_page'])
                ? $wp_the_query->query_vars['posts_per_page'] : 10;
            if (isset($wp_the_query->query_vars['paged'])) {
                $this->paged = intval($wp_the_query->query_vars['paged']);
                $this->paged = $this->paged > 0 ? $this->paged : 1;
            }
            $offset = ($this->paged - 1) * $this->limit;
            try {

                $ret = $this->getOpenSearchClient()->search(
                    "default:'{$this->keyword}' AND post_status:'publish'",
                    $offset,
                    $this->limit
                );
            } catch (AliyunOpenSearchException $e) {
                wp_die(
                    sprintf(
                        '搜索文章时发生错误：%s，请检查您的配置是否有误。<a href="%s" target="_blank">查看错误码说明</a>',
                        $e->getMessage(),
                        AliyunOpenSearch::getErrorCodeReferencesUrl()
                    )
                );
            }

            $this->posts = $ret['posts'];
            $this->postCount = $ret['total'];
            $this->pageCount = ceil($this->postCount / $this->limit);
            foreach ($this->posts as $post) {
                $this->ids[] = $post->ID;
            }
            $wp_the_query->query = array();
            $wp_the_query->query_vars['post_in'] = $this->ids;
            $wp_the_query->query_vars['post_type'] = null;
            $wp_the_query->query_vars['s'] = null;
            $wp_the_query->query_vars['paged'] = null;

//            set_query_var('post__in', $this->ids);
//            set_query_var('post_type', null);
//            set_query_var('s', null);
//            set_query_var('paged', null);

        }

        return $wp_the_query;
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
            plugin_dir_url(dirname(__FILE__)) . 'frontend/css/opensearch.css',
            array(),
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
            plugin_dir_url(dirname(__FILE__)) . 'frontend/js/opensearch.js',
            array('jquery'),
            $this->version,
            false
        );
    }
}
