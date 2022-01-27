<?php
/**
 * 2006-2022 THECON SRL
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * YOU ARE NOT ALLOWED TO REDISTRIBUTE OR RESELL THIS FILE OR ANY OTHER FILE
 * USED BY THIS MODULE.
 *
 * @author    THECON SRL <contact@thecon.ro>
 * @copyright 2006-2022 THECON SRL
 * @license   Commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Thprodupdmaincat extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'thprodupdmaincat';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Presta Maniacs';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Automatically update Products Default Category');
        $this->description = $this->l('Automatically changes the default category to the product category with the highest depth level.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHooks()) {
            return false;
        }

        return true;
    }

    public function registerHooks()
    {
        if (!$this->registerHook('actionAdminControllerSetMedia')) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $message = '';
        if (((bool)Tools::isSubmit('submit_th_reindex')) == true) {
            $this->reindexProductMainCategory(Tools::getValue('THPRODUPDMAINCAT_CATEGORY'));
            $message = $this->displayConfirmation($this->l('Successfully reindexed!'));
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $maniacs = $this->context->smarty->fetch($this->local_path.'views/templates/admin/maniacs.tpl');

        return $message.$maniacs.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitThprodupdmaincatModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Category:'),
                        'name' => 'THPRODUPDMAINCAT_CATEGORY',
                        'options' => array(
                            'query' => Category::getSimpleCategories($this->context->language->id),
                            'id' => 'id_category',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'th_html',
                        'html_content' => $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/admin/reindex_hint.tpl'),
                        'name' => ''
                    ),
                    array(
                        'type' => 'th_reindexing_product_categories',
                        'name' => '',
                        'th_ps_version' => $this->getPsVersion(),
                        'th_ps_sub_version' => $this->getPsSubVersion(),
                        'th_icon_path' => $this->context->shop->getBaseURL(true, true).'modules/'.$this->name.'/views/img/reload-icon.png'
                    )
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'THPRODUPDMAINCAT_CATEGORY' => Tools::getValue('THPRODUPDMAINCAT_CATEGORY', Configuration::get('THPRODUPDMAINCAT_CATEGORY'))
        );
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    private function reindexProductMainCategory($THPRODUPDMAINCAT_CATEGORY)
    {
        $category_obj = new Category($THPRODUPDMAINCAT_CATEGORY);
        $products = $category_obj->getProducts($this->context->language->id, 1, 25000, null, null, false, true, false, 1, false);

        foreach ($products as $product) {
            $categories = Product::getProductCategories($product['id_product']);
            if ($category_max_depth = $this->getCategoryMaxDepth(implode(', ', array_map('intval', $categories)))) {
                $product_obj = new Product($product['id_product']);
                $product_obj->id_category_default = $category_max_depth;
                $product_obj->save();
            }
        }
    }

    private function getCategoryMaxDepth($categories)
    {
        if ($categories) {
            $sql = 'SELECT `id_category` FROM `'._DB_PREFIX_.'category` WHERE `id_category` IN ('.pSQL($categories).') ORDER BY `level_depth` DESC';
            return Db::getInstance()->getValue($sql);
        }

        return false;
    }

    public function getPsVersion()
    {
        $full_version = _PS_VERSION_;
        return explode(".", $full_version)[1];
    }

    public function getPsSubVersion()
    {
        $full_version = _PS_VERSION_;
        return explode(".", $full_version)[2];
    }
}
