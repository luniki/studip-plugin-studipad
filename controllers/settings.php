<?php

use EtherpadPlugin\Foo;

/**
 * @property \RangeConfig $config
 * @property array<string,string> $settings
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class SettingsController extends StudipController
{
    /** @var \EtherpadPlugin */
    public $plugin;

    public function __construct(Trails_Dispatcher $dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    /**
     * @param string   $action
     * @param string[] $args
     *
     * @return void|bool
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!\Context::getId()) {
            throw new \AccessDeniedException();
        }

        $this->set_layout($GLOBALS['template_factory']->open(\Request::isXhr() ? 'layouts/dialog' : 'layouts/base'));
        $this->setPageTitle();
        PageLayout::setHelpKeyword('Basis.EtherpadPlugin');
        PageLayout::addStylesheet($this->plugin->getPluginURL() . '/stylesheets/studipad.css');
        \PageLayout::setBodyElementId('etherpad-plugin');

        $this->config = \CourseConfig::get(\Context::getId());
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function index_action()
    {
        $this->requireTutor();

        if (\Navigation::hasItem('/course/studipad/settings')) {
            \Navigation::activateItem('/course/studipad/settings');
        }

        $this->settings = [
            'statusgruppen_admin_permission' => $this->config->getValue('STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION'),
        ];
    }

    /**
     * @return void
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function store_action()
    {
        $this->requireTutor();
        \CSRFProtection::verifyUnsafeRequest();

        if (\Navigation::hasItem('/course/studipad/settings')) {
            \Navigation::activateItem('/course/studipad/settings');
        }

        $settings = \Request::getArray('settings');

        if (!in_array($settings['statusgruppen_admin_permission'], ['tutor', 'autor'])) {
            throw new \Trails_Exception(400);
        }

        $this->config->store('STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION', $settings['statusgruppen_admin_permission']);

        \PageLayout::postSuccess(
            dgettext(
                'studipad',
                'Die veranstaltungsbezogene Berechtigung zur Verwaltung von Gruppen-Pads wurde geändert!'
            )
        );
        $this->redirect('settings');
    }

    // #########################################################################

    /**
     * @param string $to
     * @return string
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function url_for($to = '')
    {
        $args = func_get_args();

        // find params
        $params = [];
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        // urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return \PluginEngine::getURL($this->dispatcher->plugin, $params, implode('/', $args));
    }

    /**
     * @return void
     */
    private function setPageTitle()
    {
        $title = dgettext('studipad', '%1$s - Etherpad-Einstellungen');

        PageLayout::setTitle(sprintf($title, \Context::getHeaderLine()));
    }

    /**
     * @throws \AccessDeniedException
     *
     * @return void
     */
    private function requireTutor()
    {
        $cid = \Context::getId();
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $cid)) {
            throw new \AccessDeniedException();
        }
    }
}
