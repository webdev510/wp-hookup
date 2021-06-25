<?php

require_once( __DIR__. '/services/triggers-service/triggers-service.class.php' );
require_once( __DIR__. '/services/page-visits-service/page-visits-service.class.php' );
require_once( __DIR__. '/services/analytics-service/analytics-service.class.php' );
require_once( __DIR__. '/services/ajax-triggers-service/ajax-triggers-service.class.php' );
require_once(IFSO_PLUGIN_BASE_DIR . 'services/plugin-settings-service/plugin-settings-service.class.php');

use IfSo\PublicFace\Services\TriggersService;
use IfSo\PublicFace\Services\PageVisitsService;
use IfSo\PublicFace\Services\AjaxTriggersService;
use IfSo\Services\PluginSettingsService;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://if-so.com
 * @since      1.0.0
 * @package    IfSo
 * @subpackage IfSo/public
 * @author     Matan Green
 * @author     Nick Martianov
 */
class If_So_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/*
	 *	Create shortcode
	 */
    public function add_if_so_shortcode( $atts ) {
        $ret = null;
        $render_via_ajax_option_value = \IfSo\Services\PluginSettingsService\PluginSettingsService::get_instance()->renderTriggersViaAjax->get();
        $load_later_param = isset($atts['ajax']) ? $atts['ajax'] : '';
        if(!is_admin() && ($render_via_ajax_option_value || $load_later_param === 'yes') && $load_later_param !== 'no')
            $ret =  AjaxTriggersService\AjaxTriggersService::get_instance()->handle($atts);
        else
            $ret =  TriggersService\TriggersService::get_instance()->handle($atts);

        return apply_filters('ifso_shortcode_content',$ret);
    }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/if-so-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        /**
         * This method is hooked into wordpress in the main if-so class(class-if-so.php) via if-so loader
         * Enqueues public js files as well as providing the required global JS variables
         */

		$ajax_nonce = wp_create_nonce( "ifso-nonce" );
		echo "<script>var nonce = '".$ajax_nonce."';</script>";
		$ajax_url = admin_url('admin-ajax.php');
		echo "<script>var ajaxurl = '".$ajax_url."';</script>";
		$page_url = $this->get_current_page_url();
		echo "<script>var ifso_page_url = '".$page_url."';</script>";
        $isAnalyticsOn = (IfSo\PublicFace\Services\AnalyticsService\AnalyticsService::get_instance()->isOn) ? 'true' : 'false';
        echo "<script> var isAnalyticsOn = {$isAnalyticsOn};</script>";
        $isPagesVisitedOn = (\IfSo\Services\PluginSettingsService\PluginSettingsService::get_instance()->removePageVisitsCookie->get()) ? 'false' : 'true';
        echo "<script> var isPageVisitedOn = {$isPagesVisitedOn};</script>";
        $referrerAtPageload = isset($_SERVER['HTTP_REFERER']) ? esc_js( wp_strip_all_tags( $_SERVER['HTTP_REFERER'] ) ) : '';
        echo "<script> var referrer_for_pageload = '{$referrerAtPageload}';</script>";

        //wp_deregister_script( 'jquery');

        //wp_enqueue_script( 'jquery', plugin_dir_url( __FILE__ ) . 'js/jquery-3.4.1.min.js', array( ),'3.4.1' , false );     //Enqueue a newer version of jquery

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/if-so-public.js', array( 'jquery' ), $this->version, false );

	}

	private function get_current_page_url() {
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            $url = "https://";
        else
            $url = "http://";

        $url.= $_SERVER['HTTP_HOST'];
        $url.= $_SERVER['REQUEST_URI'];

        return esc_js( wp_strip_all_tags($url));
    }

	public function wp_ajax_ifso_add_page_visit_handler() {
		check_ajax_referer( 'ifso-nonce', 'nonce' );

		$page_url = $_POST['page_url'];
		PageVisitsService\PageVisitsService::get_instance()->save_page($page_url);

		wp_die(); // indicate end of stream
	}

	public function start_sesh(){
	    if(!is_admin() && !isset($_SESSION)){    //Prevent using session_start on admin pages to fix theme/plugin editor
            if(PluginSettingsService\PluginSettingsService::get_instance()->preventNocacheHeaders->get())
                session_cache_limiter('');	//Prevent no-cache headers being sent when using session

            if(!PluginSettingsService\PluginSettingsService::get_instance()->disableSessions->get())
                session_start(['read_and_close'=>true]);
        }
    }

    public function set_ifso_group_cookie(){
        if(isset($_REQUEST['ifsoGroup']) && !empty($_REQUEST['ifsoGroup'])){
            $grp = $_REQUEST['ifsoGroup'];
            setcookie('ifsoGroup',$grp,time()+60*60*24*365*3,'/');  //Set a cookie to identify a member of a group(3 years)
            $_COOKIE['ifsoGroup'] = $grp;
        }
    }

    public function update_visit_count(){
	    if(is_admin())
	        return false;

        $cookie_name = 'ifso_visit_counts';

        // TODO move to another service
        $is_new_user = isset( $_COOKIE[$cookie_name] ) && $_COOKIE[$cookie_name] == '';

        $num_of_visits = 0;
        if ( !$is_new_user ) {
            if ( isset( $_COOKIE[$cookie_name] ) )
                $num_of_visits = $_COOKIE[$cookie_name]; // TODO move to another service

            $num_of_visits = $num_of_visits + 1;
        }

        setcookie($cookie_name, $num_of_visits, time() + (86400 * 30 * 12), "/"); // 86400 = 1 day
    }


}
