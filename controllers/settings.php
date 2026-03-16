<?php

/**
 * @property EtherpadPlugin $plugin
 * @property RangeConfig $config
 * @property array<string,string> $settings
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class SettingsController extends PluginController
{
    /**
     * @param string   $action
     * @param string[] $args
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Context::getId()) {
            throw new AccessDeniedException();
        }

        $this->set_layout($GLOBALS['template_factory']->open(Request::isXhr() ? 'layouts/dialog' : 'layouts/base'));
        $this->setPageTitle();
        PageLayout::setHelpKeyword('Basis.EtherpadPlugin');
        PageLayout::setBodyElementId('etherpad-plugin');

        $this->config = Context::get()->getConfiguration();
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function index_action(): void
    {
        $this->requireTutor();

        if (Navigation::hasItem('/course/studipad/settings')) {
            Navigation::activateItem('/course/studipad/settings');
        }

        $this->settings = [
            'statusgruppen_admin_permission' => $this->config->getValue('STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION'),
        ];
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function store_action(): void
    {
        $this->requireTutor();
        CSRFProtection::verifyUnsafeRequest();

        if (Navigation::hasItem('/course/studipad/settings')) {
            Navigation::activateItem('/course/studipad/settings');
        }

        $settings = Request::getArray('settings');

        if (!in_array($settings['statusgruppen_admin_permission'], ['tutor', 'autor'])) {
            throw new Trails_Exception(400);
        }

        $this->config->store('STUDIPAD_STATUSGRUPPEN_ADMIN_PERMISSION', $settings['statusgruppen_admin_permission']);

        PageLayout::postSuccess(
            $this->_(
                'Die veranstaltungsbezogene Berechtigung zur Verwaltung von Gruppen-Pads wurde geändert!'
            )
        );
        $this->redirect('settings');
    }

    // #########################################################################

    private function setPageTitle(): void
    {
        $title = $this->_('%1$s - Etherpad-Einstellungen');

        PageLayout::setTitle(sprintf($title, Context::getHeaderLine()));
    }

    /**
     * @throws AccessDeniedException
     */
    private function requireTutor(): void
    {
        $cid = Context::getId();
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $cid)) {
            throw new AccessDeniedException();
        }
    }
}
