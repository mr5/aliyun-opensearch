<?php

class AliyunOpenSearchClient
{
    /**
     * @var AliyunOpenSearchClient
     */
    protected static $autoloadInstance;
    /**
     * @var CloudsearchClient
     */
    protected $cloudsearchClient;
    /**
     * Application name of AliYun Open Search.
     *
     * @var string
     */
    protected $appName;
    /**
     * Access Key of AliYun Open Search.
     *
     * @var string
     */
    protected $key;
    /**
     * Access secret of AliYun Open Search.
     *
     * @var string
     */
    protected $secret;
    /**
     * Endpoint of AliYun Open Search API.
     *
     * @var string
     */
    protected $host;

    /**
     * @var CloudsearchDoc
     */
    protected $cloudSearchDoc = null;

    protected $options = array();

    protected $keyType = 'aliyun';

    protected $cloudsearchSearch = null;

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
        $this->keyType = $key_type;
        $this->options = array('host' => $this->host, 'debug' => true);

    }

    /**
     * Set CloudsearchDoc to prevent default `CloudsearchDoc` generation.
     * Warning: You must call it at once this class instanced,
     * otherwise something strange will be happened.
     *
     * @param $cloudSearchDoc
     */
    public function setCloudSearchDoc($cloudSearchDoc)
    {
        $this->cloudSearchDoc = $cloudSearchDoc;
    }

    /**
     * Get `CloudsearchDoc`, default object will be instanced
     * on `setCloudSearchDoc` has never been called before.
     *
     * @return CloudsearchDoc
     */
    public function getCloudSearchDoc()
    {
        if ($this->cloudSearchDoc == null) {
            $this->cloudSearchDoc = new CloudsearchDoc($this->appName, $this->getCloudSearchClient());
        }
        return $this->cloudSearchDoc;
    }

    /**
     * Set CloudsearchSearch to prevent default `CloudsearchSearch` generation.
     * Warning: You must call it at once this class instanced,
     * otherwise something strange will be happened.
     *
     * @param CloudsearchSearch $cloudsearchSearch
     */
    public function setCloudsearchSearch(CloudsearchSearch $cloudsearchSearch)
    {
        $this->cloudsearchSearch = $cloudsearchSearch;
    }

    /**
     * Get `CloudsearchSearch`, default object will be instanced
     * on `setCloudsearchSearch` has never been called before.
     *
     * @return CloudsearchSearch
     */
    public function getCloudsearchSearch()
    {
        if ($this->cloudsearchSearch == null) {
            $this->cloudsearchSearch = new CloudsearchSearch($this->getCloudSearchClient());
        }

        return $this->cloudsearchSearch;
    }

    /**
     * Set `CloudsearchClient` to prevent default `CloudsearchClient` generation.
     * Warning: You must call it at once this class instanced,
     * otherwise something strange will be happened.
     *
     * @param CloudsearchClient $cloudSearchClient
     */
    public function setCloudSearchClient(CloudsearchClient $cloudSearchClient)
    {
        $this->cloudsearchClient = $cloudSearchClient;
    }

    /**
     * Get `CloudsearchClient`, default object will be instanced
     * on `setCloudSearchClient` has never been called before.
     *
     * @return CloudsearchClient
     */
    public function getCloudSearchClient()
    {
        if ($this->cloudsearchClient == null) {
            $this->cloudsearchClient = new CloudsearchClient(
                $this->key,
                $this->secret,
                $this->options,
                $this->keyType
            );
        }

        return $this->cloudsearchClient;
    }

    /**
     * Delete given posts from AliYun Open Search.
     *
     * @param WP_Post[] $posts Posts will be deleted.
     * @throws AliyunOpenSearchException
     */
    public function deletePosts(array $posts)
    {
        if (empty($posts)) {
            return;
        }
        $csDoc = $this->getCloudSearchDoc();
        $docs = array();
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            $docs[] = array(
                'fields' => array(
                    'id' => $post->ID
                ),
                'cmd' => 'DELETE'
            );
        }
        $this->APIResultProcess($csDoc->remove($docs, 'main'));
    }

    /**
     * Save posts to AliYun Open Search.
     *
     * @param WP_Post[] $posts WP_Posts that you want to index.
     *
     * @throws AliyunOpenSearchException
     */
    public function savePosts(array $posts)
    {
        if (empty($posts)) {
            return;
        }
        $csDoc = $this->getCloudSearchDoc();
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
        $ret = $csDoc->add($docs, 'main');
        $this->APIResultProcess($ret);

    }

    private function APIResultProcess($ret)
    {
        $ret = json_decode($ret, true);
        // {"status":"FAIL","errors":[{"code":5001,"message":"User doesnot exist"}],"request_id":"1451395397046965800818613"}
        // {"status":"OK","request_id":"1451395489084060700489306"}
        if ($ret['status'] == 'FAIL') {
            $messages = '';
            foreach ($ret['errors'] as $error) {
                $messages .= sprintf("%s:%s\n", $error['code'], $error['message']);
            }
            throw new AliyunOpenSearchException($messages);
        }
    }

    /**
     * Search by keyword.
     *
     * @param string $query Query string under AliYun Open Search API references.
     *
     * @return array
     * @throws AliyunOpenSearchException
     */
    public function search($query, $offset = 0, $limit = 10)
    {
        $searcher = $this->getCloudsearchSearch();
        $searcher->addIndex($this->appName);
        $searcher->setQueryString($query);
        $searcher->setFormat('json');
        $searcher->setHits($limit);
        $searcher->setStartHit($offset);
        $searcher->addSummary('post_content', 150, 'em', '...', 3);
        $searcher->addSummary('post_title');


        $ret = $searcher->search();
        $this->APIResultProcess($ret);
        $ret = json_decode($ret);
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
