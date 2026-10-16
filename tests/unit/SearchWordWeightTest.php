<?php

use Logingrupa\SearchOffersShopaholic\Classes\Helper\TranslatableSearchHelper;
use Lovata\SearchShopaholic\Classes\Helper\SearchHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Search words are weighed as a regex against every word of a found name. A typed
 * regex character must neither fail the search nor act as a pattern, and the case of
 * a non-ASCII letter must not change the weight.
 */
class SearchWordWeightTest extends TestCase
{
    const VALUE_WORD_LIST = ['Gel', 'polish', '10+', '100', 'a.b', 'axb', '(5ml)', '#5'];

    /** Laravel turns a preg_match() compile warning into an exception, as production does */
    protected function setUp(): void
    {
        parent::setUp();
        set_error_handler(function (int $iLevel, string $sMessage, string $sFile, int $iLine) {
            throw new ErrorException($sMessage, 0, $iLevel, $sFile, $iLine);
        });
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        parent::tearDown();
    }

    #[DataProvider('specialWordProvider')]
    public function testTypedRegexCharacterKeepsTheSearchWorking(string $sSpecialWord)
    {
        $obHelper = $this->makeHelper(TranslatableSearchHelper::class, ['gel', $sSpecialWord]);

        $this->assertGreaterThan(0, $this->weigh($obHelper, ['Gel', 'polish']));
    }

    public static function specialWordProvider(): array
    {
        $arWordList = ['*', '+', '?', '[', ']', '{2}', '\\', '%', '(', ')', '\\(', '^', '$', '|', '.*', '/', "\xff\xfe"];

        return array_combine(
            array_map('bin2hex', $arWordList),
            array_map(fn ($sWord) => [$sWord], $arWordList)
        );
    }

    #[DataProvider('plainSearchProvider')]
    public function testAsciiWordsWeighAsTheParentDoes(array $arSearchWordList, int $iMinLength)
    {
        $obHelper = $this->makeHelper(TranslatableSearchHelper::class, $arSearchWordList);
        $obParentHelper = $this->makeHelper(SearchHelper::class, $arSearchWordList);

        $this->assertSame(
            $this->weigh($obParentHelper, self::VALUE_WORD_LIST, $iMinLength),
            $this->weigh($obHelper, self::VALUE_WORD_LIST, $iMinLength)
        );
    }

    public static function plainSearchProvider(): array
    {
        return [
            'one word'                  => [['gel'], 1],
            'two words'                 => [['gel', 'polish'], 1],
            'two words, min 3'          => [['gel', 'polish'], 3],
            'parenthesis word'          => [['(5ml)'], 3],
            'no match'                  => [['laka'], 1],
            'short word, min 3'         => [['gel', 'po'], 3],
            'short regex word, min 3'   => [['gel', '5.'], 3],
            'short hash word, min 3'    => [['gel', '#5'], 3],
        ];
    }

    public function testRegexCharacterMatchesOnlyItself()
    {
        $obHelper = $this->makeHelper(TranslatableSearchHelper::class, ['10+']);
        $this->assertGreaterThan(0, $this->weigh($obHelper, ['10+']));
        $this->assertSame(0, $this->weigh($obHelper, ['100']));

        $obHelper = $this->makeHelper(TranslatableSearchHelper::class, ['a.b']);
        $this->assertSame(0, $this->weigh($obHelper, ['axb']));
    }

    #[DataProvider('caseProvider')]
    public function testLetterCaseDoesNotChangeTheWeight(string $sTypedWord, string $sStoredWord)
    {
        $obHelper = $this->makeHelper(TranslatableSearchHelper::class, [$sTypedWord, 'lak']);

        $this->assertSame(
            $this->weigh($obHelper, [$sTypedWord]),
            $this->weigh($obHelper, [$sStoredWord])
        );
        $this->assertGreaterThan(0, $this->weigh($obHelper, [$sStoredWord]));
    }

    public static function caseProvider(): array
    {
        return [
            'russian'  => ['гель', 'Гель'],
            'latvian'  => ['līme', 'LĪME'],
            'ascii'    => ['gel', 'GEL'],
        ];
    }

    /**
     * The constructor reads the model and the active language, weighing needs neither
     * @param string $sClass
     * @param array  $arSearchWordList
     * @return SearchHelper
     */
    protected function makeHelper(string $sClass, array $arSearchWordList): SearchHelper
    {
        $obHelper = (new ReflectionClass($sClass))->newInstanceWithoutConstructor();
        (new ReflectionProperty(SearchHelper::class, 'arWordList'))->setValue($obHelper, $arSearchWordList);

        return $obHelper;
    }

    /**
     * @param SearchHelper $obHelper
     * @param array        $arValueWordList
     * @param int          $iMinLength
     * @return int
     */
    protected function weigh(SearchHelper $obHelper, array $arValueWordList, int $iMinLength = 1): int
    {
        $obMethod = new ReflectionMethod($obHelper, 'calculateWeight');

        return $obMethod->invoke($obHelper, $arValueWordList, count($arValueWordList), $iMinLength);
    }
}
