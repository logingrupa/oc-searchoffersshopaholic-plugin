<?php namespace Logingrupa\SearchOffersShopaholic\Classes\Event;

use Lang;
use Illuminate\Support\Arr;
use System\Classes\PluginManager;

use Illuminate\Support\Facades\DB;
use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Settings;
use Illuminate\Support\Facades\Schema;
use Lovata\Shopaholic\Controllers\Offers;
use Lovata\SearchShopaholic\Classes\Helper\SearchHelper;

/**
 * Class ExtendCategoryModel
 * @package Lovata\SearchShopaholic\Classes\Event
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ExtendFieldHandler
{
    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        $obEvent->listen('backend.form.extendFields', function ($obWidget) {
            $this->extendSettingsFields($obWidget);
            $this->extendOfferFields($obWidget);
        });
    }

    /**
     * Extend settings fields
     * @param \Backend\Widgets\Form $obWidget
     */
    private function extendSettingsFields($obWidget)
    {
        // Only for the Settings controller
        if (!$obWidget->getController() instanceof \System\Controllers\Settings || $obWidget->isNested || empty($obWidget->context)) {
            return;
        }

        // Only for the Settings model
        if (!$obWidget->model instanceof Settings) {
            return;
        }

        $this->addOfferSearchSettings($obWidget);
    }

    /**
     * Extend offer fields
     * @param \Backend\Widgets\Form $obWidget
     */
    private function extendOfferFields($obWidget)
    {
        // Only for the Offers controller
        if (!$obWidget->getController() instanceof Offers || $obWidget->isNested || empty($obWidget->context)) {
            return;
        }

        // Only for the Offer model
        if (!$obWidget->model instanceof Offer) {
            return;
        }

        $this->addSearchField($obWidget);
    }

    /**
     * Acdd searh settings for Offer model
     * @param \Backend\Widgets\Form $obWidget
     */
    private function addOfferSearchSettings($obWidget)
    {

        $arOffersTableColumns = DB::getSchemaBuilder()->getColumnListing('lovata_shopaholic_offers');

        foreach($arOffersTableColumns as $key => $value ) {
           $lists[$value] = str_replace("_"," ", ucfirst($value));
           
        }


        $arLabelData = [
            'model' => Lang::get('lovata.shopaholic::lang.offer.name'),
        ];

        $arFieldList = [
            'field' => [
                'label'   => 'lovata.searchshopaholic::lang.field.search_field',
                'span'    => 'full',
                'type'    => 'dropdown',
                'options' => $lists, 
            ],
        ];

        $arFieldList = array_merge($arFieldList, $this->getDefaultConfigArray());

        $obWidget->addTabFields([
            'offer_search_by' => [
                'tab'   => 'lovata.searchshopaholic::lang.tab.search_settings',
                'label' => Lang::get('lovata.searchshopaholic::lang.field.search_by', $arLabelData),
                'span'  => 'left',
                'type'  => 'repeater',
                'form'  => [
                    'fields' => $arFieldList,
                ],
            ],
        ]);
    }

    /**
     * Get default config for field
     * @return array
     */
    private function getDefaultConfigArray()
    {
        $arResult = [
            'min'         => [
                'label'       => 'lovata.searchshopaholic::lang.field.search_min_length',
                'span'        => 'left',
                'type'        => 'number',
                'placeholder' => 3,
            ],
            'weight'      => [
                'label'       => 'lovata.searchshopaholic::lang.field.search_weight',
                'span'        => 'right',
                'type'        => 'number',
                'placeholder' => 100,
            ],
            'type'        => [
                'label'   => 'lovata.toolbox::lang.field.type',
                'span'    => 'left',
                'type'    => 'dropdown',
                'options' => [
                    SearchHelper::TYPE_DEFAULT   => Lang::get('lovata.searchshopaholic::lang.field.search_type_'.SearchHelper::TYPE_DEFAULT),
                    SearchHelper::TYPE_FULL      => Lang::get('lovata.searchshopaholic::lang.field.search_type_'.SearchHelper::TYPE_FULL),
                    SearchHelper::TYPE_ALL_WORDS => Lang::get('lovata.searchshopaholic::lang.field.search_type_'.SearchHelper::TYPE_ALL_WORDS),
                ],
            ],
            'word_weight' => [
                'label'       => 'lovata.searchshopaholic::lang.field.search_word_weight',
                'span'        => 'right',
                'type'        => 'number',
                'placeholder' => 1,
            ],
        ];

        return $arResult;
    }

    /**
     * Add search_synonym field
     * @param \Backend\Widgets\Form $obWidget
     */
    private function addSearchField($obWidget)
    {
        $obWidget->addTabFields([
            'search_synonym' => [
                'label' => 'lovata.searchshopaholic::lang.field.search_synonym',
                'tab'   => 'lovata.searchshopaholic::lang.tab.search_content',
                'span'  => 'full',
                'type'  => 'textarea',
            ],
        ]);
    }
}