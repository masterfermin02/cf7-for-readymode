<?php
/**
 * CFTZ_Module_Zapier
 *
 * @package         Cf7_To_ReadyMode
 * @subpackage      CFTZ_Module_Zapier
 * @since           1.0.0
 *
 */

// If this file is called directly, call the cops.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! class_exists( 'CFTZ_Module_Zapier' ) ) {
    class CFTZ_Module_Ready_Mode {

        /**
         * The Core object
         *
         * @since    1.0.0
         * @var      Cf7_To_ReadyMode    $core   The core class
         */
        private $core;

        /**
         * The Module Indentify
         *
         * @since    1.0.0
         */
        const MODULE_SLUG = 'zapier';

        /**
         * Define the core functionalities into plugin.
         *
         * @since    1.0.0
         * @param    Cf7_To_ReadyMode      $core   The Core object
         */
        public function __construct( Cf7_To_ReadyMode $core ) {
            $this->core = $core;
        }

        /**
         * Register all the hooks for this module
         *
         * @since    1.0.0
         * @access   private
         */
        private function define_hooks() {
            $this->core->add_action( 'ctz_trigger_webhook', array( $this, 'pull_the_trigger' ), 10, 5 );
        }

        /**
         * Send data to Zapier
         *
         * @since    1.0.0
         * @access   private
         */
        public function pull_the_trigger( array $data, $hook_url, $properties, $contact_form ) {
            /**
             * Filter: ctz_ignore_default_webhook
             *
             * The 'ctz_ignore_default_webhook' filter can be used to ignore
             * core request, if you want to trigger your own request.
             *
             * add_filter( 'ctz_ignore_default_webhook', '__return_true' );
             *
             * @since    2.3.0
             */
            if (apply_filters( 'ctz_ignore_default_webhook', false )) {
                return;
            }

            $postdata = http_build_query(array(
                'lead' => [
                    $data
                ]
            ));

            $opts = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => $postdata
                )
            );

            $context  = stream_context_create($opts);

            /**
             * Filter: ctz_hook_url
             *
             * The 'ctz_hook_url' filter webhook URL so developers can use form
             * data or other information to change webhook URL.
             *
             * @since    2.1.4
             */
            $hook_url = apply_filters( 'ctz_hook_url', $hook_url, $data );

            /**
             * Filter: ctz_post_request_args
             *
             * The 'ctz_post_request_args' filter POST args so developers
             * can modify the request args if any service demands a particular header or body.
             * @example: url 'https://blineaccess.readymode.com/lead-api/wxtApCgBzoCy'
             * @since    1.1.0
             */
            $result = file_get_contents($hook_url, false, $context);

            /**
             * Action: ctz_post_request_result
             *
             * You can perform a action with the result of the request.
             * By default we do nothing but you can throw a Exception in webhook errors.
             *
             * @since    1.4.0
             */
            do_action( 'ctz_post_request_result', $result, $hook_url );
        }

        /**
         * Run the module.
         *
         * @since    1.0.0
         */
        public function run() {
            $this->define_hooks();
        }

        /**
         * Get headers to request.
         *
         * @since    2.3.0
         */
        public function create_headers($custom) {
            $headers = array( 'Content-Type'  => 'application/x-www-form-urlencoded\r\n' );
            $blog_charset = get_option( 'blog_charset' );
            if ( ! empty( $blog_charset ) ) {
                $headers['Content-Type'] .= '; charset=' . get_option( 'blog_charset' );
            }

            $custom = explode("\n", $custom);
            foreach ($custom as $header) {
                $header = explode(':', $header, 2);
                $header = array_map('trim', $header);

                if (count($header) === 2) {
                    $headers[ $header[0] ] = $header[1];
                }
            }

            return $headers;
        }
    }
}
