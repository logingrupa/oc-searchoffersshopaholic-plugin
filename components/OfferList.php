<?php namespace Logingrupa\SearchOffersShopaholic\Components;

use Lang;
use System\Classes\PluginManager;

use Lovata\Toolbox\Classes\Component\SortingElementList;
use Lovata\Shopaholic\Classes\Collection\OfferCollection;
use Lovata\Shopaholic\Classes\Store\OfferListStore;

/**
 * Class OfferList
 * @package Lovata\Shopaholic\Components
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * Compare for Shopaholic
 * @method array onAddToCompare()
 * @method array onRemoveFromCompare()
 * @method void onClearCompareList()
 *
 * Viewed offers for Shopaholic
 * @method array onRemoveFromViewedOfferList()
 * @method void onClearViewedOfferList()
 *
 * Wish list for Shopaholic
 * @method array onAddToWishList()
 * @method array onRemoveFromWishList()
 * @method void onClearWishList()
 */
class OfferList extends SortingElementList
{
    /** @var array */
    protected $arPropertyList = [];

    /**
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name' => 'lovata.shopaholic::lang.component.offer_list_name',
            'description' => 'lovata.shopaholic::lang.component.offer_list_description',
        ];
    }

    /**
     * @return array
     */
    public function defineProperties()
    {
        $this->arPropertyList = [
            'sorting' => [
                'title' => 'lovata.shopaholic::lang.component.offer_list_sorting',
                'type' => 'dropdown',
                'default' => OfferListStore::SORT_NO,
                'options' => [
                    OfferListStore::SORT_NO => Lang::get('lovata.shopaholic::lang.component.sorting_no'),
                    OfferListStore::SORT_PRICE_ASC => Lang::get('lovata.shopaholic::lang.component.sorting_price_asc'),
                    OfferListStore::SORT_PRICE_DESC => Lang::get('lovata.shopaholic::lang.component.sorting_price_desc'),
                    OfferListStore::SORT_NEW => Lang::get('lovata.shopaholic::lang.component.sorting_new'),
                ],
            ],
        ];
        // TODO: Impliment fuller support - also filters for Offers
        
        // if (PluginManager::instance()->hasPlugin('Lovata.PopularityShopaholic')) {
        //     $this->arPropertyList['sorting']['options'][OfferListStore::SORT_POPULARITY_DESC] =
        //         Lang::get('lovata.shopaholic::lang.component.sorting_popularity_desc');
        // }

        // if (PluginManager::instance()->hasPlugin('Lovata.ReviewsShopaholic')) {
        //     $this->arPropertyList['sorting']['options'][OfferListStore::SORT_RATING_DESC] =
        //         Lang::get('lovata.shopaholic::lang.component.sorting_rating_desc');
        //     $this->arPropertyList['sorting']['options'][OfferListStore::SORT_RATING_ASC] =
        //         Lang::get('lovata.shopaholic::lang.component.sorting_rating_asc');
        // }

        return $this->arPropertyList;
    }

    /**
     * Make element collection
     * @param array $arElementIDList
     *
     * @return OfferCollection
     */
    public function make($arElementIDList = null)
    {
        return OfferCollection::make($arElementIDList);
    }

    /**
     * Method for ajax request with empty response
     * @return bool
     * @deprecated
     */
    public function onAjaxRequest()
    {
        return true;
    }
}
