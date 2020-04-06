<?php

namespace Module\MenuLink;

use Pimple\Container;
use PPVersionNotices\Module\AdInterface;
use PPVersionNotices\Module\TopNotice\Module;
use PPVersionNotices\ServicesProvider;
use PPVersionNotices\Template\TemplateInvalidArgumentsException;

class ModuleTest extends \Codeception\TestCase\WPTestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    /**
     * @var AdInterface
     */
    private $module;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        $container = new Container();
        $container->register(new ServicesProvider());

        $this->module = $container['module_menu_link'];
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    // Tests
    public function test_module_enqueue_admin_assets_on_the_admin()
    {
        set_current_screen('edit-post');

        $wp_styles        = wp_styles();
        $wp_styles->queue = [];

        do_action('admin_enqueue_scripts');

        $this->assertContains(\PPVersionNotices\Module\MenuLink\Module::STYLE_HANDLE, $wp_styles->queue);
    }

    public function test_module_dont_enqueue_admin_assets_on_the_frontend()
    {
        set_current_screen('edit-post');

        $wp_styles        = wp_styles();
        $wp_styles->queue = [];

        do_action('enqueue_scripts');

        $this->assertNotContains(\PPVersionNotices\Module\MenuLink\Module::STYLE_HANDLE, $wp_styles->queue);
    }

    public function test_module_add_admin_menu()
    {
        global $submenu, $_parent_pages;

        $currentUser = get_current_user_id();

        wp_set_current_user(
            self::factory()->user->create(
                [
                    'role' => 'administrator',
                ]
            )
        );

        set_current_screen('edit.php');

        do_action('init');
        do_action('admin_menu');

        // Main menu of the Dumb Plugin One
        $expected = $_ENV['TEST_SITE_WP_URL'] . '/wp-admin/admin.php?page=dummy-plugin-one-page';
        $current  = menu_page_url('dummy-plugin-one-page');

        $this->assertEquals($expected, $current);

        // Submenu for the Upgrade link
        $expected = $_ENV['TEST_SITE_WP_URL'] . '/wp-admin/admin.php?page=dummy-plugin-one-page' . \PPVersionNotices\Module\MenuLink\Module::MENU_SLUG_SUFFIX;
        $current  = menu_page_url('dummy-plugin-one-page' . \PPVersionNotices\Module\MenuLink\Module::MENU_SLUG_SUFFIX);

        $this->assertEquals($expected, $current);

        wp_set_current_user($currentUser);
    }
}
