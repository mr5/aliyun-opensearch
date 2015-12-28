<?php

class AliyunOpenSearchClient3
{
    /**
     * @var AliyunOpenSearchClient
     */
    protected static $autoloadInstance;
    /**
     * @var CloudsearchClient
     */
    protected $cloudsearchClient;

    protected $appName;
    protected $key;
    protected $host;
    protected $secret;
    const TYPE_POST = 1;
    const TYPE_PAGE = 2;

    /**
     * Constructor
     *
     * @param string $key AliYun AccessKey
     * @param string $secret AliYun Secret
     * @param string $host AliYun Open Search API endpoint.
     * @param string $appName AliYun Open Search application name.
     * @param string $key_type AliYun key type.
     */
    public function __construct($key, $secret, $host, $appName, $key_type = 'aliyun')
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->host = $host;
        $this->appName = $appName;
        $options = array('host' => $this->host, 'debug' => true);
        $this->cloudsearchClient = new CloudsearchClient(
            $this->key,
            $this->secret,
            $options,
            $key_type
        );
    }


    /**
     * Delete given posts from AliYun Open Search.
     *
     * @param WP_Post[] $posts Posts will be deleted.
     *
     * @return int
     */
    public function deletePosts(array $posts)
    {
        $csDoc = new CloudsearchDoc($this->appName, $this->cloudsearchClient);
        $docs = array();
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            $docs[$post->post_type][] = array(
                'fields' => array(
                    'id' => $post->ID
                ),
                'cmd' => 'DELETE'
            );
        }
        $successCount = 0;
        foreach ($docs as $type => $_docs) {
            $csDoc->remove($docs, 'main');
            $successCount += count($docs);
        }

        return $successCount;
    }

    /**
     * Save posts to AliYun Open Search.
     *
     * @param WP_Post[] $posts WP_Posts that you want to index.
     *
     * @return int
     */
    public function savePosts(array $posts)
    {
        $csDoc = new CloudsearchDoc($this->appName, $this->cloudsearchClient);
        $docs = array();
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            $docs[] = array(
                'fields' => array(
                    'id' => $post->ID,
                    'post_category' => is_int($post->post_category)
                        ? array($post->post_category)
                        : $post->post_category,
                    'post_title' => $post->post_title,
                    'post_excerpt' => $post->post_excerpt,
                    'comment_status' => $post->comment_status,
                    'pinged' => $post->pinged,
                    'post_content_source' => $post->post_content,
                    'type' => $post->post_type,
                    'guid' => $post->guid,
                    'post_author' => $post->post_author,
                    'ping_status' => $post->ping_status,
                    'post_status' => $post->post_status,
                    'post_parent' => $post->post_parent,
                    'menu_order' => $post->menu_order,
                    'post_content_filtered' => $post->post_content_filtered,
                    'post_thumbnail' => get_the_post_thumbnail($post->ID),
                    'post_date_unixtime' => strtotime($post->post_date_gmt),
                    'post_modified_unixtime' => strtotime($post->post_modified_gmt),
                    'comment_count' => $post->comment_count,
                    'tags' => wp_get_post_tags($post->ID)
                ),
                'cmd' => 'UPDATE'
            );
        }
        $csDoc->add($docs, 'main');
        $this->cloudsearchClient->getRequest();
    }

    /**
     * Initialize AliYun Open Search index with `builtin_news` schema template.
     *
     * @return string
     */
    public function initializeIndex()
    {
        $index = new CloudsearchIndex($this->appName, $this->cloudsearchClient);

        $index->createByTemplate(
            file_get_contents(plugin_dir_path(__FILE__) . '/index-template.json'),
            array(
                'desc' => '由阿里云 WordPress 插件创建，请勿手动配置。'
            )
        );
    }

    /**
     * Search by keyword.
     *
     * @param string $query Query string under AliYun Open Search API references.
     *
     * @return array
     */
    public function search($query, $offset = 0, $limit = 10)
    {
        $searcher = new CloudsearchSearch($this->cloudsearchClient);
        $searcher->addIndex($this->appName);
        $searcher->setQueryString($query);
        $searcher->setFormat('json');
        $searcher->setHits($limit);
        $searcher->setStartHit($offset);
        $searcher->addSummary('post_content', 150, 'em', '...', 3);
        $searcher->addSummary('post_title');


        $ret = $searcher->search();
        $ret = json_decode($ret);
        if ($ret && $ret->errors) {
            $messages = '<h4>请求搜索服务器时,发生以下错误,请检查您的配置是否有误:</h4><br>';
            foreach ($ret->errors as $error) {
                $messages .= $error->code . ': ' . $error->message . '<br>';
            }
            wp_die($messages);
        }
        $result = array(
            'total' => 0,
            'posts' => array()
        );
        if ($ret && isset($ret->result) && $ret->result->viewtotal > 0) {
            $posts = array();
            $localOffsetSecs = get_option('gmt_offset') * HOUR_IN_SECONDS;
            foreach ($ret->result->items as $item) {
                $post = new WP_Post($item);
                $post->ID = $item->id;
                $post->post_excerpt = $item->post_content;
                $post->post_date = date(
                    'Y-m-d H:i:s',
                    $item->post_date_unixtime + $localOffsetSecs
                );
                $post->post_date_gmt = date(
                    'Y-m-d H:i:s',
                    $item->post_date_unixtime
                );
                $post->post_modified = date(
                    'Y-m-d H:i:s',
                    $item->post_modified_unixtime + $localOffsetSecs
                );
                $post->post_modified_gmt = date(
                    'Y-m-d H:i:s',
                    $item->post_modified_unixtime
                );
                $posts[] = $post;

                wp_cache_set($post->ID, $post, 'posts');
            }

            $result['posts'] = $posts;
            $result['total'] = $ret->result->total;
        }

        return $result;

    }

    /**
     * Autoload AliYun Open Search options, and craft an AliyunOpenSearchClient.
     *
     * @return AliyunOpenSearchClient
     */
    public static function autoload()
    {
        if (static::$autoloadInstance === null) {
            static::$autoloadInstance = new static(
                AliyunOpenSearchOptions::getAccessKey(),
                AliyunOpenSearchOptions::getSecret(),
                AliyunOpenSearchOptions::getHost(),
                AliyunOpenSearchOptions::getAppName()
            );
        }

        return static::$autoloadInstance;
    }
}