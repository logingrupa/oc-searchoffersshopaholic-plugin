<?php namespace LoginGrupa\SearchOffersShopaholic;

use Event;
use System\Classes\PluginBase;

use LoginGrupa\SearchOffersShopaholic\Classes\Event\OfferModelHandler;
use LoginGrupa\SearchOffersShopaholic\Classes\Event\ExtendFieldHandler;

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
