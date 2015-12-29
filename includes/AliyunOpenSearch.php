<?php

class AliyunOpenSearch
{
    /**
     * Actions will be added to WordPress.
     *
     * @var array
     */
    protected $actions;
    /**
     * Filters will be added to WordPress.
     *
     * @var array
     */
    protected $filters;
    /**
     * Plugin name.
     *
     * @var string
     */
    protected $pluginName;
    /**
     * Plugin version
     *
     * @var string
     */
    protected $version;

    /**
     * Constructor
     *
     * @param string $pluginName Plugin name
     * @param string $version Plugin version
     */
    public function __construct($pluginName, $version)
    {
        $this->pluginName = $pluginName;
        $this->version = $version;

    }

    /**
     * Get plugin name.
     *
     * @return string
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Plugin initialization, prepare something dependent.
     */
    public function initialize()
    {
        $this->loadSDK();
        $this->loadLibraries();
        $this->registerI18N();
    }

    /**
     * Load dependent libraries.
     *
     * @return void
     */
    protected function loadLibraries()
    {
        require_once plugin_dir_path(dirname(__FILE__))
            . 'includes/AliyunOpenSearchOptions.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'includes/AliyunOpenSearchClient.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'includes/AliyunOpenSearchAdmin.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'includes/AliyunOpenSearchFrontend.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'includes/AliyunOpenSearchException.php';

    }

    /**
     * Load AliYun Open Search SDK.
     *
     * @return void
     */
    protected function loadSDK()
    {
        require_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchClient.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchDoc.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchIndex.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchSearch.php';
        require_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchSuggest.php';
    }


    /**
     * Register the internationalization functionality.
     *
     * Loads and defines the internationalization files for open search plugin
     * so that it is ready for translation.
     *
     * @return void
     */
    protected function registerI18N()
    {
        load_plugin_textdomain(
            $this->getPluginName(),
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }


    /**
     * Register filters and actions to WordPress.
     *
     * @return void
     */
    public function run(AliyunOpenSearchAdmin $admin, AliyunOpenSearchFrontend $frontend)
    {
        $frontend->run();
        $admin->run();
    }

    /**
     * Get error code references, useful when some API call errors occurred for users.
     *
     * @return string
     */
    public static function getErrorCodeReferencesUrl()
    {
        return 'https://help.aliyun.com/document_detail/opensearch/api-reference/call-method/errormsg.html';
    }
}
