<?php
/**
 * FooGallery Migrate Init Class
 * Runs at the startup of the plugin
 * Assumes after all checks have been made, and all is good to go!
 *
 * @package FooPlugins\FooGalleryMigrate
 */

namespace FooPlugins\FooGalleryMigrate;

if ( ! class_exists( 'FooPlugins\FooGalleryMigrate\Init' ) ) {

	/**
	 * Class Init
	 *
	 * @package FooPlugins\FooGalleryMigrate
	 */
	class Init {

		/**
		 * Initialize the plugin
		 */
		public function __construct() {
            add_action( 'foogallery_admin_menu_after', array( $this, 'add_menu' ) );

            // Ajax calls for importing galleries
            add_action( 'wp_ajax_foogallery_migrate', array( $this, 'ajax_start_migration' ) );
            add_action( 'wp_ajax_foogallery_migrate_continue', array( $this, 'ajax_continue_migration' ) );
            add_action( 'wp_ajax_foogallery_migrate_cancel', array( $this, 'ajax_cancel_migration' ) );
            add_action( 'wp_ajax_foogallery_migrate_reset', array( $this, 'ajax_reset_migration' ) );
		}

        /**
         * Add an admin menu
         *
         * @return void
         */
        function add_menu() {
            foogallery_add_submenu_page(
                __( 'Migrate!', 'foogallery' ),
                'manage_options',
                'foogallery-migrate',
                array( $this, 'render_view' )
            );
        }

        /**
         * Render the contents of the page for the menu.
         *
         * @return void
         */
        function render_view() {
            require_once 'view-migrate.php';
        }

        /**
         * Start the migration!
         *
         * @return void
         */
        function ajax_start_migration() {
            if ( check_admin_referer( 'foogallery_migrate', 'foogallery_migrate' ) ) {

                $migrator = foogallery_migrate_migrator_instance();

                if ( array_key_exists( 'gallery-id', $_POST ) ) {

                    $gallery_ids = $_POST['gallery-id'];

                    $migrations = array();

                    foreach ( $gallery_ids as $gallery_id ) {
                        $migrations[$gallery_id] = array(
                            'id' => $gallery_id,
                            'migrated' => false,
                            'current' => false,
                        );
                        if ( array_key_exists( 'foogallery-title-' . $gallery_id, $_POST ) ) {
                            $migrations[$gallery_id]['title'] = stripslashes( $_POST[ 'foogallery-title-' . $gallery_id ] );
                        }
                    }

                    // Queue the galleries for migration.
                    $migrator->queue_galleries_for_migration( $migrations );
                }

                $migrator->render_gallery_form();

                die();
            }
        }

        function ajax_continue_migration() {
            if ( check_admin_referer( 'foogallery_migrate_continue', 'foogallery_migrate_continue' ) ) {

                $migrator = foogallery_migrate_migrator_instance();

                $migrator->migrate();

                $migrator->render_gallery_form();

                die();
            }
        }

        function ajax_cancel_migration() {
            if ( check_admin_referer( 'foogallery_migrate_cancel', 'foogallery_migrate_cancel' ) ) {

                $migrator = foogallery_migrate_migrator_instance();

                $migrator->cancel_migration();

            }
            die();
        }

        function ajax_nextgen_reset_import() {
            if ( check_admin_referer( 'foogallery_nextgen_reset', 'foogallery_nextgen_reset' ) ) {

                $this->nextgen->reset_import();

                $this->nextgen->render_import_form();

            }
            die();
        }
	}
}
