<?php
use Mockery as m;

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

$assets = $wp_options = $wp_pages = $new_whitelist_options = $wp_filter = $merged_filters = array();

function clear_all_globals()
{
    global $wp_options, $wp_pages, $new_whitelist_options, $wp_filter, $merged_filters, $assets;

    $assets = $wp_options = $wp_pages = $new_whitelist_options = $wp_filter = $merged_filters = array();

}

function has_asset($src)
{
    global $assets;
    return in_array($src, $assets);
}

function wp_enqueue_script($handle, $src = false, $deps = array(), $ver = false, $in_footer = false)
{
    global $assets;
    $assets[] = $src;
}

function wp_enqueue_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all')
{
    global $assets;
    $assets[] = $src;
}

function clear_options()
{
    global $wp_options;
    $wp_options = array();
}

function get_option($var)
{
    global $wp_options;
    return isset($wp_options[$var]) ? $wp_options[$var] : null;
}

function update_option($var, $val)
{
    global $wp_options;
    $wp_options[$var] = $val;
    return true;
}

function plugin_dir_path()
{
    return dirname(dirname(__FILE__)) . '/';
}

function load_plugin_textdomain()
{
}

function plugin_basename()
{
}

function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
{
    global $wp_filter, $merged_filters;

    $idx = _wp_filter_build_unique_id($tag, $function_to_add, $priority);
    $wp_filter[$tag][$priority][$idx] = array('function' => $function_to_add, 'accepted_args' => $accepted_args);
    unset($merged_filters[$tag]);
    return true;
}

function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
{
    return add_filter($tag, $function_to_add, $priority, $accepted_args);
}

function clear_filters()
{
    global $wp_filter, $merged_filters;
    $wp_filter = $merged_filters = array();
}

function _wp_filter_build_unique_id($tag, $function, $priority)
{
    global $wp_filter;
    static $filter_id_count = 0;

    if (is_string($function)) {
        return $function;
    }

    if (is_object($function)) {
        // Closures are currently implemented as objects
        $function = array($function, '');
    } else {
        $function = (array)$function;
    }

    if (is_object($function[0])) {
        // Object Class Calling
        if (function_exists('spl_object_hash')) {
            return spl_object_hash($function[0]) . $function[1];
        } else {
            $obj_idx = get_class($function[0]) . $function[1];
            if (!isset($function[0]->wp_filter_id)) {
                if (false === $priority) {
                    return false;
                }
                $obj_idx .= isset($wp_filter[$tag][$priority]) ? count((array)$wp_filter[$tag][$priority]) : $filter_id_count;
                $function[0]->wp_filter_id = $filter_id_count;
                ++$filter_id_count;
            } else {
                $obj_idx .= $function[0]->wp_filter_id;
            }

            return $obj_idx;
        }
    } elseif (is_string($function[0])) {
        // Static Calling
        return $function[0] . '::' . $function[1];
    }
}

function has_filter($tag, $function_to_check = false)
{
    {
        // Don't reset the internal array pointer
        $wp_filter = $GLOBALS['wp_filter'];

        $has = !empty($wp_filter[$tag]);

        // Make sure at least one priority has a filter callback
        if ($has) {
            $exists = false;
            foreach ($wp_filter[$tag] as $callbacks) {
                if (!empty($callbacks)) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $has = false;
            }
        }

        if (false === $function_to_check || false === $has) {
            return $has;
        }

        if (!$idx = _wp_filter_build_unique_id($tag, $function_to_check, false)) {
            return false;
        }

        foreach ((array)array_keys($wp_filter[$tag]) as $priority) {
            if (isset($wp_filter[$tag][$priority][$idx])) {
                return $priority;
            }
        }

        return false;
    }
}

function register_setting($option_group, $option_name, $sanitize_callback = '')
{
    global $new_whitelist_options;
    $new_whitelist_options[] = $option_name;
}

function get_registered_settings()
{
    return $GLOBALS['new_whitelist_options'];
}

function clear_settings()
{
    global $new_whitelist_options;
    $new_whitelist_options = array();
}

function add_options_page($page_title, $menu_title, $capability, $menu_slug, $function = '')
{
    global $wp_pages;
    $wp_pages[$menu_slug] = true;
    return true;
}

function add_management_page($page_title, $menu_title, $capability, $menu_slug, $function = '')
{
    return add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
}

function aos_has_page($menu_slug)
{
    global $wp_pages;
    return isset($wp_pages[$menu_slug]);
}

function clear_pages()
{
    global $wp_pages;
    $wp_pages = array();
}

function plugin_dir_url()
{
    return '';
}


class WP_Query
{
    public $found_posts = 0;

    public $is_search = false;

    public $is_main_query = false;

    public $query_vars = array();
    public $query = array();

    public function query()
    {
        return array();
    }

    public function is_search()
    {
        return $this->is_search;
    }

    public function is_main_query()
    {
        return $this->is_main_query;
    }
}

$wp_query = new WP_Query();
$posts = array();

class WP_Post
{

    /**
     * Post ID.
     *
     * @var int
     */
    public $ID;

    /**
     * ID of post author.
     *
     * A numeric string, for compatibility reasons.
     *
     * @var string
     */
    public $post_author = 0;

    /**
     * The post's local publication time.
     *
     * @var string
     */
    public $post_date = '0000-00-00 00:00:00';

    /**
     * The post's GMT publication time.
     *
     * @var string
     */
    public $post_date_gmt = '0000-00-00 00:00:00';

    /**
     * The post's content.
     *
     * @var string
     */
    public $post_content = '';

    /**
     * The post's title.
     *
     * @var string
     */
    public $post_title = '';

    /**
     * The post's excerpt.
     *
     * @var string
     */
    public $post_excerpt = '';

    /**
     * The post's status.
     *
     * @var string
     */
    public $post_status = 'publish';

    /**
     * Whether comments are allowed.
     *
     * @var string
     */
    public $comment_status = 'open';

    /**
     * Whether pings are allowed.
     *
     * @var string
     */
    public $ping_status = 'open';

    /**
     * The post's password in plain text.
     *
     * @var string
     */
    public $post_password = '';

    /**
     * The post's slug.
     *
     * @var string
     */
    public $post_name = '';

    /**
     * URLs queued to be pinged.
     *
     * @var string
     */
    public $to_ping = '';

    /**
     * URLs that have been pinged.
     *
     * @var string
     */
    public $pinged = '';

    /**
     * The post's local modified time.
     *
     * @var string
     */
    public $post_modified = '0000-00-00 00:00:00';

    /**
     * The post's GMT modified time.
     *
     * @var string
     */
    public $post_modified_gmt = '0000-00-00 00:00:00';

    /**
     * A utility DB field for post content.
     *
     *
     * @var string
     */
    public $post_content_filtered = '';

    /**
     * ID of a post's parent post.
     *
     * @var int
     */
    public $post_parent = 0;

    /**
     * The unique identifier for a post, not necessarily a URL, used as the feed GUID.
     *
     * @var string
     */
    public $guid = '';

    /**
     * A field used for ordering posts.
     *
     * @var int
     */
    public $menu_order = 0;

    /**
     * The post's type, like post or page.
     *
     * @var string
     */
    public $post_type = 'post';

    /**
     * An attachment's mime type.
     *
     * @var string
     */
    public $post_mime_type = '';

    /**
     * Cached comment count.
     *
     * A numeric string, for compatibility reasons.
     *
     * @var string
     */
    public $comment_count = 0;

    /**
     * Stores the post object's sanitization level.
     *
     * Does not correspond to a DB field.
     *
     * @var string
     */
    public $filter;
}

function get_post($ID)
{

    global $posts;
    return isset($posts[$ID]) ? $posts[$ID] : null;
}

function add_post($ID, $post)
{
    global $posts;
    $posts[$ID] = $post;
}

function clear_posts()
{
    global $posts;
    $posts = array();
}

function settings_fields()
{
}

define('ALI_OPENSEARCH_PLUGIN_NAME', 'AliYun Open Search Test Kit');

$query_vars = array();
function clear_query_vars()
{
    global $wp_query;

    $wp_query->query_vars = array();
}

function set_query_var($var, $val)
{
    global $wp_query;

    $wp_query->query_vars[$var] = $val;
}


function is_search()
{
    global $wp_query;

    return $wp_query->query_vars['is_search'];
}

function is_admin()
{
    global $wp_query;

    return $wp_query->query_vars['is_admin'];
}

include_once __DIR__ . '/../includes/AliyunOpenSearch.php';
include_once __DIR__ . '/../includes/AliyunOpenSearchAdmin.php';
include_once __DIR__ . '/../includes/AliyunOpenSearchClient.php';
include_once __DIR__ . '/../includes/AliyunOpenSearchFrontend.php';
include_once __DIR__ . '/../includes/AliyunOpenSearchOptions.php';
