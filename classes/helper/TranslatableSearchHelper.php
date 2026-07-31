<?php namespace Logingrupa\SearchOffersShopaholic\Classes\Helper;

use DB;
use Lovata\SearchShopaholic\Classes\Helper\SearchHelper;

/**
 * Class TranslatableSearchHelper
 *
 * Fixes the translated-field branch of Lovata SearchHelper on non-default locales:
 * 1. JSON_EXTRACT() output is utf8mb4_bin (case + accent sensitive). Force
 *    utf8mb4_unicode_ci so /en and /ru behave like the base table on /lv.
 * 2. User input was spliced raw into whereRaw (SQL injection + 500 on quotes).
 *    All search terms are now bound parameters.
 * 3. RainLab Translate only stores values that differ from the base record.
 *    Models without a stored translation were unfindable. Fall back to the base
 *    table column for models that have no translation of the field.
 * 4. Parent all_words branch was unreachable for translated fields (condition
 *    required type=default) and silently searched the base table instead.
 *
 * Bound to the container in Plugin::boot(), so every App::make(SearchHelper::class)
 * call site (product, offer, category, brand, tag) receives this class.
 *
 * @package Logingrupa\SearchOffersShopaholic\Classes\Helper
 */
class TranslatableSearchHelper extends SearchHelper
{
    const TRANSLATE_TABLE = 'rainlab_translate_attributes';
    const FIELD_NAME_PATTERN = '/^[a-z0-9_]+$/i';

    /**
     * Search element IDs by full search phrase
     * @param string $sSearch
     * @param string $sFieldName
     * @param int    $iMinLength
     * @param int    $iWeight
     */
    protected function search($sSearch, $sFieldName, $iMinLength, $iWeight)
    {
        if (!$this->isTranslatedSearch($sFieldName)) {
            parent::search($sSearch, $sFieldName, $iMinLength, $iWeight);
            return;
        }

        if (empty($sSearch) || mb_strlen($sSearch) < $iMinLength) {
            return;
        }

        $sLike = '%'.$sSearch.'%';

        $arElementIDList = $this->newTranslateQuery($sFieldName)
            ->whereRaw($this->getTranslatedFieldSql($sFieldName).' LIKE ?', [$sLike])
            ->pluck('model_id')
            ->all();

        $sModelName = $this->sModel;
        $obBaseQuery = $sModelName::where($sFieldName, 'like', $sLike);
        $this->applyMissingTranslationFilter($obBaseQuery, $sFieldName);
        $arBaseIDList = $obBaseQuery->toBase()->pluck('id')->all();

        $arElementIDList = array_unique(array_merge($arElementIDList, $arBaseIDList));
        if (empty($arElementIDList)) {
            return;
        }

        $this->addSearchResults($arElementIDList, $iWeight);
    }

    /**
     * Search element IDs by separate words with weight by word position
     * @param string $sFieldName
     * @param int    $iMinLength
     * @param int    $iWeight
     * @param string $sSearchType
     */
    protected function searchByWords($sFieldName, $iMinLength, $iWeight, $sSearchType)
    {
        if (!$this->isTranslatedSearch($sFieldName)) {
            parent::searchByWords($sFieldName, $iMinLength, $iWeight, $sSearchType);
            return;
        }

        $arWordList = $this->filterWordList($iMinLength);
        if (empty($sFieldName) || empty($arWordList)) {
            return;
        }

        $arResult = array_merge(
            $this->getTranslatedWordRows($sFieldName, $arWordList, $sSearchType),
            $this->getBaseFallbackWordRows($sFieldName, $arWordList, $sSearchType)
        );

        if (empty($arResult)) {
            return;
        }

        $this->addSearchResultByWord($arResult, $iWeight, $iMinLength);
    }

    /**
     * Field is searched through the translation table on the active locale
     * @param string $sFieldName
     * @return bool
     */
    protected function isTranslatedSearch($sFieldName): bool
    {
        return !empty(self::$sActiveLang)
            && !empty($sFieldName)
            && in_array($sFieldName, self::$arTranslateField)
            && preg_match(self::FIELD_NAME_PATTERN, $sFieldName) === 1;
    }

    /**
     * Remove words shorter than min length (parent unset() used a wrong key and never removed them)
     * @param int $iMinLength
     * @return array
     */
    protected function filterWordList($iMinLength): array
    {
        $arWordList = [];
        foreach ($this->arWordList as $sWord) {
            if (mb_strlen($sWord) >= $iMinLength) {
                $arWordList[] = $sWord;
            }
        }

        return $arWordList;
    }

    /**
     * Base translate table query for the active locale and searched model
     * @param string $sFieldName
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newTranslateQuery($sFieldName)
    {
        return DB::table(self::TRANSLATE_TABLE)
            ->where('locale', self::$sActiveLang)
            ->where('model_type', $this->sModel);
    }

    /**
     * Case-insensitive SQL expression for a translated field value.
     * $sFieldName is validated against FIELD_NAME_PATTERN before interpolation.
     * @param string $sFieldName
     * @return string
     */
    protected function getTranslatedFieldSql($sFieldName): string
    {
        return "CONVERT(JSON_UNQUOTE(JSON_EXTRACT(`attribute_data`, '$.\"{$sFieldName}\"')) USING utf8mb4) COLLATE utf8mb4_unicode_ci";
    }

    /**
     * Limit a base table query to models WITHOUT a stored non-empty translation of the field
     * @param \October\Rain\Database\Builder $obQuery
     * @param string                         $sFieldName
     * @return void
     */
    protected function applyMissingTranslationFilter($obQuery, $sFieldName): void
    {
        $sModelName = $this->sModel;
        $sTable = (new $sModelName())->getTable();

        $obQuery->whereNotExists(function ($obSubQuery) use ($sTable, $sFieldName) {
            $obSubQuery->selectRaw('1')
                ->from(self::TRANSLATE_TABLE)
                ->where('locale', self::$sActiveLang)
                ->where('model_type', $this->sModel)
                ->whereColumn(self::TRANSLATE_TABLE.'.model_id', $sTable.'.id')
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(`attribute_data`, '$.\"{$sFieldName}\"')) IS NOT NULL")
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(`attribute_data`, '$.\"{$sFieldName}\"')) <> ''");
        });
    }

    /**
     * Word matches from the translation table: [['id' => .., 'field' => ..], ..]
     * @param string $sFieldName
     * @param array  $arWordList
     * @param string $sSearchType
     * @return array
     */
    protected function getTranslatedWordRows($sFieldName, $arWordList, $sSearchType): array
    {
        $sFieldSql = $this->getTranslatedFieldSql($sFieldName);
        $obQuery = $this->newTranslateQuery($sFieldName)
            ->selectRaw('model_id, '.$sFieldSql.' AS field_value');

        if ($sSearchType == self::TYPE_ALL_WORDS) {
            foreach ($arWordList as $sWord) {
                $obQuery->whereRaw($sFieldSql.' LIKE ?', ['%'.$sWord.'%']);
            }
        } else {
            $obQuery->where(function ($obWhere) use ($sFieldSql, $arWordList) {
                foreach ($arWordList as $sWord) {
                    $obWhere->orWhereRaw($sFieldSql.' LIKE ?', ['%'.$sWord.'%']);
                }
            });
        }

        $arResult = [];
        foreach ($obQuery->get() as $obElement) {
            $arResult[] = [
                'id'    => $obElement->model_id,
                'field' => (string) $obElement->field_value,
            ];
        }

        return $arResult;
    }

    /**
     * Word matches from the base table for models without a stored translation
     * @param string $sFieldName
     * @param array  $arWordList
     * @param string $sSearchType
     * @return array
     */
    protected function getBaseFallbackWordRows($sFieldName, $arWordList, $sSearchType): array
    {
        $sModelName = $this->sModel;
        $obQuery = $sModelName::select(['id', $sFieldName]);
        $this->applyMissingTranslationFilter($obQuery, $sFieldName);

        if ($sSearchType == self::TYPE_ALL_WORDS) {
            foreach ($arWordList as $sWord) {
                $obQuery->where($sFieldName, 'like', '%'.$sWord.'%');
            }
        } else {
            $obQuery->where(function ($obWhere) use ($sFieldName, $arWordList) {
                foreach ($arWordList as $sWord) {
                    $obWhere->orWhere($sFieldName, 'like', '%'.$sWord.'%');
                }
            });
        }

        $arResult = [];
        foreach ($obQuery->get() as $obElement) {
            $arResult[] = [
                'id'    => $obElement->id,
                'field' => (string) $obElement->$sFieldName,
            ];
        }

        return $arResult;
    }
}
