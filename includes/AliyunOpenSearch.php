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
     * @param string $version    Plugin version
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
     * Load dependent libraries.
     *
     * @return void
     */
    protected function loadLibraries()
    {
        include_once plugin_dir_path(dirname(__FILE__))
            . 'includes/AliyunOpenSearchOptions.php';
        include_once plugin_dir_path(dirname(__FILE__))
            . 'includes/AliyunOpenSearchClient.php';
        include_once plugin_dir_path(dirname(__FILE__))
            . 'admin/AliyunOpenSearchAdmin.php';
        include_once plugin_dir_path(dirname(__FILE__))
            . 'frontend/AliyunOpenSearchFrontend.php';

    }

    /**
     * Load AliYun Open Search SDK.
     *
     * @return void
     */
    protected function loadSDK()
    {
        include_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchClient.php';
        include_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchDoc.php';
        include_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchIndex.php';
        include_once plugin_dir_path(dirname(__FILE__))
            . 'sdk/CloudsearchSearch.php';
        include_once plugin_dir_path(dirname(__FILE__))
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
    public function registerI18N()
    {
        load_plugin_textdomain(
            $this->getPluginName(),
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress action
     *                              that is being registered.
     * @param object $component     A reference to the instance of the object on
     *                              which the action is defined.
     * @param string $callback      The name of the function definition
     *                              on the $component.
     * @param int    $priority      Optional. he priority at which the function
     *                              should be fired. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments that should
     *                              be passed to the $callback.Default is 1.
     *
     * @return void
     */
    public function addAction(
        $hook,
        $component,
        $callback,
        $priority = 10,
        $accepted_args = 1
    ) {
        $this->actions = $this->add(
            $this->actions, $hook, $component, $callback, $priority, $accepted_args
        );
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param string $hook          The name of the WordPress filter
     *                              that is being registered.
     * @param object $component     A reference to the instance of
     *                              the object on which the filter is defined.
     * @param string $callback      The name of the function definition
     *                              on the $component.
     * @param int    $priority      Optional. he priority at which the function
     *                              should be fired. Default is 10.
     * @param int    $accepted_args Optional . The number of arguments that should
     *                              be passed to the $callback. Default is 1
     *
     * @return void
     */
    public function addFilter(
        $hook,
        $component,
        $callback,
        $priority = 10,
        $accepted_args = 1
    ) {
        $this->filters = $this->add(
            $this->filters, $hook, $component, $callback, $priority, $accepted_args
        );
    }

    /**
     * A utility function that is used to register the actions
     * and hooks into a single collection.
     *
     * @param array  $hooks         The collection of hooks that is being registered
     *                              (that is, actions or filters).
     * @param string $hook          The name of the WordPress filter that is
     *                              being registered.
     * @param object $component     A reference to the instance of the object
     *                              on which the filter is defined.
     * @param string $callback      The name of the function definition
     *                              on the $component.
     * @param int    $priority      The priority at which the function
     *                              should be fired.
     * @param int    $accepted_args The number of arguments that should
     *                              be passed to the $callback.
     *
     * @return array The collection of actions and filters registered with WordPress.
     */
    protected function add(
        $hooks,
        $hook,
        $component,
        $callback,
        $priority,
        $accepted_args
    ) {

        $hooks[] = array(
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;

    }

    /**
     * Register filters and actions to WordPress.
     *
     * @return void
     */
    public function run()
    {
        $this->loadSDK();
        $this->loadLibraries();
        $this->registerI18N();
        $this->registerAdminHooks();

        $frontend = new AliyunOpenSearchFrontend(
            $this->getPluginName(),
            $this->getVersion()
        );
        $frontend->run();
        $admin = new AliyunOpenSearchAdmin(
            $this->getPluginName(), $this->getVersion()
        );
        $admin->run();

        if (!empty($this->filters)) {
            foreach ($this->filters as $hook) {
                add_filter(
                    $hook['hook'],
                    array($hook['component'], $hook['callback']),
                    $hook['priority'],
                    $hook['accepted_args']
                );
            }
        }

        if (!empty($this->actions)) {
            foreach ($this->actions as $hook) {
                add_action(
                    $hook['hook'],
                    array($hook['component'], $hook['callback']),
                    $hook['priority'],
                    $hook['accepted_args']
                );
            }
        }

    }
}