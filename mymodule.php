<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MyModule extends Module
{

    public function __construct()
    {
        $this->name = 'mymodule';
        // $this->tab = 'front_office_features';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'John Smith';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('My module');
        $this->description = $this->l('Description of my module.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install() &&
            $this->registerHook('leftColumn') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            Configuration::updateValue('MYMODULE_NAME', 'my friend');
    }

    public function uninstall()
    {
        return (parent::uninstall()
            && Configuration::deleteByName('MYMODULE_NAME')
            && Configuration::deleteByName('MYMODULE_CONFIG')
        );
    }

    /**
     * This method handles the module's configuration page
     * @return string The page's HTML content 
     */
    public function getContent()
    {
        $output = '';

        // this part is executed only when the form is submitted
        if (Tools::isSubmit('submit' . $this->name)) {
            // retrieve the value set by the user
            $configValue = (string) Tools::getValue('MYMODULE_CONFIG');

            // check that the value is valid
            if (empty($configValue) || !Validate::isGenericName($configValue)) {
                // invalid value, show an error
                $output = $this->displayError($this->l('Invalid Configuration value'));
            } else {
                // value is ok, update it and display a confirmation message
                Configuration::updateValue('MYMODULE_CONFIG', $configValue);
                $output = $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        // display any message, then the form
        return $output . $this->displayForm();
    }

    /**
     * Builds the configuration form
     * @return string HTML code
     */
    public function displayForm()
    {
        // Init Fields form array
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Configuration value'),
                        'name' => 'MYMODULE_CONFIG',
                        'size' => 20,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current value into the form
        $helper->fields_value['MYMODULE_CONFIG'] = Tools::getValue('MYMODULE_CONFIG', Configuration::get('MYMODULE_CONFIG'));

        return $helper->generateForm([$form]);
    }

    public function hookDisplayLeftColumn($params)
    {
        $this->context->smarty->assign(
            [
                'my_module_name' => Configuration::get('MYMODULE_NAME'),
                'my_module_link' => $this->context->link->getModuleLink('mymodule', 'display'),
                'my_module_message' => $this->l('This is a simple text message') // Do not forget to enclose your strings in the l() translation method
            ]
        );
    
        return $this->display(__FILE__, 'mymodule.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'mymodule-style',
            $this->_path . 'views/css/mymodule.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );

        $this->context->controller->registerJavascript(
            'mymodule-javascript',
            $this->_path . 'views/js/mymodule.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
    }
}
