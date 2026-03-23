<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class React_Shortcodes_Plugin
{

    private $plugin_path;
    private $plugin_url;

    /**
     * Normalize a shortcode attribute value to a "true"/"false" string.
     */
    private function parse_bool($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        $lower = strtolower(trim((string) $value));
        return in_array($lower, ['true', '1', 'yes', 'on'], true) ? 'true' : 'false';
    }

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url  = plugin_dir_url(__FILE__);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_react_assets']);
        add_shortcode('react_sgbm_starting_date', [$this, 'react_sgbm_starting_date']);
        add_shortcode('react_sgbm_starting_gift', [$this, 'react_sgbm_starting_gift']);
        add_shortcode('react_sgbm_starting_category', [$this, 'react_sgbm_starting_category']);
        add_shortcode('react_sgbm_starting_service', [$this, 'react_sgbm_starting_service']);
        add_shortcode('react_sgbm_redeem_gift', [$this, 'react_sgbm_redeem_gift']);
    }

    /**
     * Enqueue JS & CSS from Vite build
     */
    public function enqueue_react_assets()
    {
        $react_dir = plugin_dir_path(__FILE__) . '../react-frontend-v2/';
        $react_url = plugin_dir_url(__FILE__) . '../react-frontend-v2/';

        $manifest_path = $react_dir . '.vite/manifest.json';

        // Default fallback paths
        $js_file  = $react_url . 'assets/index.js';
        $css_file = $react_url . 'assets/index.css';

        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);
            $entry = $manifest['index.html'] ?? null;

            if ($entry && isset($entry['file'])) {
                $js_file = $react_url .  $entry['file'];
            }
            if (!empty($entry['css'][0])) {
                $css_file = $react_url .  $entry['css'][0];
            }
        }

        wp_enqueue_script('vite-react-js', $js_file, [], null, true);
        wp_enqueue_style('vite-react-css', $css_file, [], null);

        add_filter('script_loader_tag', function ($tag, $handle) {
            if ($handle === 'vite-react-js') {
                return str_replace('<script ', '<script type="module" ', $tag);
            }
            return $tag;
        }, 10, 2);
    }

    /**
     * Shortcode 1
     */
    public function react_sgbm_starting_date($atts)
    {
        $atts = shortcode_atts([
            'title'          => '',
            'topbar'         => 'true',
            'rightbar'       => 'false',
            'bottombar'      => 'false',
            'mobile-heading' => 'false',
        ], $atts, 'react_sgbm_starting_date');

        $title          = esc_attr($atts['title']);
        $topbar         = esc_attr($this->parse_bool($atts['topbar']));
        $rightbar       = esc_attr($this->parse_bool($atts['rightbar']));
        $bottombar      = esc_attr($this->parse_bool($atts['bottombar']));
        $mobile_heading = esc_attr($this->parse_bool($atts['mobile-heading']));
        return '<div id="react_sgbm_starting_date" data-title="' . $title . '" data-topbar="' . $topbar . '" data-rightbar="' . $rightbar . '" data-bottombar="' . $bottombar . '" data-mobile-heading="' . $mobile_heading . '"></div>';
    }

    /**
     * Shortcode 2
     */
    public function react_sgbm_starting_gift($atts)
    {
        $atts = shortcode_atts([
            'title'          => '',
            'topbar'         => 'true',
            'rightbar'       => 'false',
            'bottombar'      => 'false',
            'mobile-heading' => 'false',
        ], $atts, 'react_sgbm_starting_gift');

        $title          = esc_attr($atts['title']);
        $topbar         = esc_attr($this->parse_bool($atts['topbar']));
        $rightbar       = esc_attr($this->parse_bool($atts['rightbar']));
        $bottombar      = esc_attr($this->parse_bool($atts['bottombar']));
        $mobile_heading = esc_attr($this->parse_bool($atts['mobile-heading']));
        return '<div id="react_sgbm_starting_gift" data-title="' . $title . '" data-topbar="' . $topbar . '" data-rightbar="' . $rightbar . '" data-bottombar="' . $bottombar . '" data-mobile-heading="' . $mobile_heading . '"></div>';
    }
    /**
     * Shortcode 3
     */
    public function react_sgbm_starting_category($atts)
    {
        $atts = shortcode_atts([
            'title'          => '',
            'topbar'         => 'true',
            'rightbar'       => 'false',
            'bottombar'      => 'false',
            'mobile-heading' => 'false',
        ], $atts, 'react_sgbm_starting_category');

        $title          = esc_attr($atts['title']);
        $topbar         = esc_attr($this->parse_bool($atts['topbar']));
        $rightbar       = esc_attr($this->parse_bool($atts['rightbar']));
        $bottombar      = esc_attr($this->parse_bool($atts['bottombar']));
        $mobile_heading = esc_attr($this->parse_bool($atts['mobile-heading']));
        return '<div id="react_sgbm_starting_category" data-title="' . $title . '" data-topbar="' . $topbar . '" data-rightbar="' . $rightbar . '" data-bottombar="' . $bottombar . '" data-mobile-heading="' . $mobile_heading . '"></div>';
    }

    /**
     * Shortcode 4
     */
    public function react_sgbm_starting_service($atts)
    {
        $atts = shortcode_atts([
            'title'          => '',
            'topbar'         => 'true',
            'rightbar'       => 'false',
            'bottombar'      => 'false',
            'mobile-heading' => 'false',
        ], $atts, 'react_sgbm_starting_service');

        $title          = esc_attr($atts['title']);
        $topbar         = esc_attr($this->parse_bool($atts['topbar']));
        $rightbar       = esc_attr($this->parse_bool($atts['rightbar']));
        $bottombar      = esc_attr($this->parse_bool($atts['bottombar']));
        $mobile_heading = esc_attr($this->parse_bool($atts['mobile-heading']));
        return '<div id="react_sgbm_starting_service" data-title="' . $title . '" data-topbar="' . $topbar . '" data-rightbar="' . $rightbar . '" data-bottombar="' . $bottombar . '" data-mobile-heading="' . $mobile_heading . '"></div>';
    }

    /**
     * Shortcode 5
     */
    public function react_sgbm_redeem_gift($atts)
    {
        $atts = shortcode_atts([
            'title'          => '',
            'topbar'         => 'true',
            'rightbar'       => 'false',
            'bottombar'      => 'false',
            'mobile-heading' => 'false',
        ], $atts, 'react_sgbm_redeem_gift');

        $title          = esc_attr($atts['title']);
        $topbar         = esc_attr($this->parse_bool($atts['topbar']));
        $rightbar       = esc_attr($this->parse_bool($atts['rightbar']));
        $bottombar      = esc_attr($this->parse_bool($atts['bottombar']));
        $mobile_heading = esc_attr($this->parse_bool($atts['mobile-heading']));
        return '<div id="react_sgbm_redeem_gift" data-title="' . $title . '" data-topbar="' . $topbar . '" data-rightbar="' . $rightbar . '" data-bottombar="' . $bottombar . '" data-mobile-heading="' . $mobile_heading . '"></div>';
    }
}
