<?php namespace Logingrupa\SearchOffersShopaholic\Classes\Event;

use App;
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
            /** @var Offer $obModel */
            $obModel->fillable[] = 'search_synonym';
            $obModel->fillable[] = 'search_content';
        });
        OfferCollection::extend(function ($obCollection) {
            /** @var OfferCollection $obCollection */
            $obCollection->addDynamicMethod('search', function ($sSearch) use ($obCollection) {

                /** @var array $arSettings */
                $arSettings = Settings::getValue('offer_search_by');

                /** @var SearchHelper $obSearchHelper */
                $obSearchHelper = App::make(SearchHelper::class, [
                    'sModel' => Offer::class
                ]);
                $arElementIDList = $obSearchHelper->result($sSearch,$arSettings);

                return $obCollection->applySorting($arElementIDList);
            });
        });
    }
}
