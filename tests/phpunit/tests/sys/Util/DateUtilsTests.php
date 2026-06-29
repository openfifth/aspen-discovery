<?php
namespace sys\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateUtilsTests extends TestCase {
	private ?string $originalTimezone = null;
	private $originalActiveLanguage = null;

	public function __construct(string $name) {
		parent::__construct($name);
		require_once __DIR__ . '/../../../../../code/web/sys/Utils/DateUtils.php';
	}

	protected function setUp(): void {
		$this->originalTimezone = date_default_timezone_get();
		date_default_timezone_set('UTC');

		global $activeLanguage;
		$this->originalActiveLanguage = $activeLanguage;
		$activeLanguage = (object)['locale' => 'en_US'];
	}

	protected function tearDown(): void {
		if ($this->originalTimezone !== null) {
			date_default_timezone_set($this->originalTimezone);
		}
		global $activeLanguage;
		$activeLanguage = $this->originalActiveLanguage;
	}

	public static function emptyDateProvider(): array {
		return [
			'empty string'        => [''],
			'null'                => [null],
			'zero date'           => ['0000-00-00'],
			'zero datetime'       => ['0000-00-00 00:00:00'],
			'unparseable string'  => ['not a date'],
		];
	}

	#[DataProvider('emptyDateProvider')]
	public function testFormatDateLocaleReturnsEmptyForInvalidInput($input): void {
		$this->assertSame('', \DateUtils::formatDateLocale($input));
	}

	public static function patternProvider(): array {
		return [
			'iso date'        => ['2025-03-15', 'medium', 'none', 'yyyy-MM-dd', '2025-03-15'],
			'iso datetime'    => ['2025-03-15 14:30:00', 'medium', 'short', 'yyyy-MM-dd HH:mm', '2025-03-15 14:30'],
			'month and year'  => ['2025-03-15', 'medium', 'none', 'MMMM yyyy', 'March 2025'],
		];
	}

	#[DataProvider('patternProvider')]
	public function testFormatDateLocaleHonoursPattern($input, $dateStyle, $timeStyle, $pattern, $expected): void {
		$this->assertSame($expected, \DateUtils::formatDateLocale($input, $dateStyle, $timeStyle, $pattern));
	}

	public function testFormatDateLocaleAcceptsDateTimeObject(): void {
		$date = new \DateTime('2025-03-15 00:00:00', new \DateTimeZone('UTC'));
		$this->assertSame('2025-03-15', \DateUtils::formatDateLocale($date, 'medium', 'none', 'yyyy-MM-dd'));
	}

	public function testFormatDateLocaleAcceptsNumericTimestamp(): void {
		$timestamp = strtotime('2025-03-15 00:00:00');
		$this->assertSame('2025-03-15', \DateUtils::formatDateLocale($timestamp, 'medium', 'none', 'yyyy-MM-dd'));
	}

	public static function skeletonProvider(): array {
		return [
			'month and year (en_US)' => ['en_US', '2025-03-15', 'yMMM', 'Mar 2025'],
			'month and year (en_GB)' => ['en_GB', '2025-03-15', 'yMMM', 'Mar 2025'],
			'month and year - short (en_GB)' => ['en_GB', '2025-03-15', 'yMM', '03/2025'],
			'month and year - short (en_US)' => ['en_US', '2025-03-15', 'yMM', '03/2025'],
		];
	}

	#[DataProvider('skeletonProvider')]
	public function testFormatDateLocaleDerivesPatternFromSkeleton($locale, $input, $skeleton, $expected): void {
		global $activeLanguage;
		$activeLanguage->locale = $locale;
		$this->assertSame($expected, \DateUtils::formatDateLocale($input, 'medium', 'none', null, $skeleton));
	}

	public function testFormatDateLocaleSkeletonRespectsLocaleOrdering(): void {
		global $activeLanguage;
		$activeLanguage->locale = 'ja_JP';
		$result = \DateUtils::formatDateLocale('2025-03-15', 'medium', 'none', null, 'yMMM');
		$this->assertStringStartsWith('2025', $result);
	}

	public function testFormatDateLocaleSkeletonOverridesPattern(): void {
		$this->assertSame('Mar 2025', \DateUtils::formatDateLocale('2025-03-15', 'medium', 'none', 'yyyy-MM-dd', 'yMMM'));
	}
}
