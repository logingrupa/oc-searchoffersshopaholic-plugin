<?php namespace LoginGrupa\SearchOffersShopaholic;

use Event;
use System\Classes\PluginBase;

use LoginGrupa\SearchOffersShopaholic\Classes\Event\OfferModelHandler;
use LoginGrupa\SearchOffersShopaholic\Classes\Event\ExtendFieldHandler;
use Logingrupa\SearchOffersShopaholic\Classes\Helper\TranslatableSearchHelper;
use Lovata\SearchShopaholic\Classes\Helper\SearchHelper;

/**
 * Class Plugin
 * @package LoginGrupa\SearchOffersShopaholic
 */
class Plugin extends PluginBase
{
    /** @var array Plugin dependencies */
    public $require = ['Lovata.Shopaholic', 'Lovata.Toolbox', 'Lovata.OrdersShopaholic', 'Lovata.SearchShopaholic',];

    /**
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Logingrupa\SearchOffersShopaholic\Components\OfferList' => 'OfferList',
        ];
    }

    /**
     * Plugin boot method
     */
    public function boot()
    {
        //Every search call site resolves SearchHelper from the container:
        //locale-aware subclass fixes case sensitivity + missing translation fallback
        $this->app->bind(SearchHelper::class, TranslatableSearchHelper::class);

        $this->addEventListener();
    }

    /**
     * Add event listeners
     */
    protected function addEventListener()
    {
        Event::subscribe(OfferModelHandler::class);
        Event::subscribe(ExtendFieldHandler::class);
    }


}
