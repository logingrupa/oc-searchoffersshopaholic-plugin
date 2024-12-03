<?php namespace Logingrupa\SearchOffersShopaholic\Classes\Event;

use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Classes\Collection\OfferCollection;
use Lovata\SearchShopaholic\Classes\Helper\SearchHelper;
use Lovata\Shopaholic\Models\Settings;

class OfferModelHandler
{
    /**
     * Add listeners
     */
    public function subscribe()
    {

        Offer::extend(function ($obModel) {
            /** @var Brand $obModel */

        });
        OfferCollection::extend(function ($obCollection) {
            /** @var OfferCollection $obCollection */
            $obCollection->addDynamicMethod('search', function ($sSearch) use ($obCollection) {

                /** @var array $arSettings */
                $arSettings = Settings::getValue('offer_search_by');

                /** @var SearchHelper $obSearchHelper */
                $obSearchHelper = app(SearchHelper::class,[Offer::class]);
                $arElementIDList = $obSearchHelper->result($sSearch,$arSettings);

                return $obCollection->applySorting($arElementIDList);
            });
        });
    }
}
