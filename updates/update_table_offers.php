<?php namespace Lovata\SearchShopaholic\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class UpdateTableProduct
 * @package Lovata\SearchShopaholic\Updates
 */
class UpdateTableOffersAddSearch extends Migration
{
    /**
     * Apply migration
     */
    public function up()
    {
        if (!Schema::hasTable('lovata_shopaholic_offers') || Schema::hasColumn('lovata_shopaholic_offers', 'search_synonym')) {
            return;
        }

        Schema::table('lovata_shopaholic_offers', function (Blueprint $obTable) {
            $obTable->text('search_synonym')->nullable();
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if (!Schema::hasTable('lovata_shopaholic_offers') || !Schema::hasColumn('lovata_shopaholic_offers', 'search_synonym')) {
            return;
        }

        Schema::table('lovata_shopaholic_offers', function (Blueprint $obTable) {
            $obTable->dropColumn(['search_synonym']);
        });
    }
}
