<?php

namespace services\API;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../code/web/services/API/AbstractAPI.php';
require_once __DIR__ . '/../../../../../code/web/sys/Translation/Language.php';

class PaginationHarnessAPI extends \AbstractAPI {
	function launch(): void {
	}

	function getBreadcrumbs(): array {
		return [];
	}

	public function paginationParams(int $defaultPageSize = 100, int $maxPageSize = 100): array {
		return $this->getPaginationParams($defaultPageSize, $maxPageSize);
	}

	public function paginate(\DataObject $dataObject, string $orderBy, callable $formatRow, int $defaultPageSize = 100, int $maxPageSize = 100): array {
		return $this->paginateQuery($dataObject, $orderBy, $formatRow, $defaultPageSize, $maxPageSize);
	}
}

/**
 * Tests for the generic pagination helpers on AbstractAPI
 * (getPaginationParams and paginateQuery).
 */
class AbstractAPIPaginationTests extends TestCase {

	private PaginationHarnessAPI $api;

	protected function setUp(): void {
		parent::setUp();
		$this->api = new PaginationHarnessAPI();

		foreach ([
			'zt1',
			'zt2',
			'zt3',
			'zt4',
			'zt5',
		] as $index => $code) {
			$language = new \Language();
			$language->code = $code;
			$language->displayName = 'Pagination Test ' . $code;
			$language->displayNameEnglish = 'Pagination Test ' . $code;
			$language->facetValue = 'Pagination Test ' . $code;
			$language->weight = $index + 10;
			$this->assertNotFalse($language->insert());
		}
	}

	protected function tearDown(): void {
		global $aspen_db;

		$aspen_db->exec("DELETE FROM languages WHERE code LIKE 'zt%'");
		foreach ([
			'page',
			'pageSize',
		] as $param) {
			unset($_REQUEST[$param]);
		}

		parent::tearDown();
	}

	private function languagesQuery(): \Language {
		$language = new \Language();
		$language->whereAdd("code LIKE 'zt%'");
		return $language;
	}

	public function testParamsDefaults(): void {
		$params = $this->api->paginationParams(20, 100);

		$this->assertSame([
			'page' => 1,
			'pageSize' => 20,
			'offset' => 0,
		], $params);
	}

	public function testParamsFromRequest(): void {
		$_REQUEST['page'] = 3;
		$_REQUEST['pageSize'] = 15;

		$params = $this->api->paginationParams(20, 100);

		$this->assertSame([
			'page' => 3,
			'pageSize' => 15,
			'offset' => 30,
		], $params);
	}

	public function testParamsCapsPageSize(): void {
		$_REQUEST['pageSize'] = 5000;

		$params = $this->api->paginationParams(20, 100);

		$this->assertSame(100, $params['pageSize']);
	}

	public function testParamsCapsDefaultPageSize(): void {
		$params = $this->api->paginationParams(200, 100);

		$this->assertSame(100, $params['pageSize']);
	}

	public function testParamsClampsInvalidValues(): void {
		$_REQUEST['page'] = -4;
		$_REQUEST['pageSize'] = 0;

		$params = $this->api->paginationParams(20, 100);

		$this->assertSame(1, $params['page']);
		$this->assertSame(1, $params['pageSize']);
		$this->assertSame(0, $params['offset']);
	}

	public function testParamsNonNumericValues(): void {
		$_REQUEST['page'] = 'abc';
		$_REQUEST['pageSize'] = 'xyz';

		$params = $this->api->paginationParams(20, 100);

		$this->assertSame(1, $params['page']);
		$this->assertSame(1, $params['pageSize']);
	}

	public function testPaginateEnvelopeAndMath(): void {
		$_REQUEST['pageSize'] = 2;
		$response = $this->api->paginate($this->languagesQuery(), 'code ASC', function ($row) {
			return ['code' => $row->code];
		});

		$this->assertEqualsCanonicalizing([
			'success',
			'totalResults',
			'page',
			'pageSize',
			'totalPages',
			'items',
		], array_keys($response));
		$this->assertTrue($response['success']);
		$this->assertSame(5, $response['totalResults']);
		$this->assertSame(1, $response['page']);
		$this->assertSame(2, $response['pageSize']);
		$this->assertSame(3, $response['totalPages']);
		$this->assertSame([
			['code' => 'zt1'],
			['code' => 'zt2'],
		], $response['items']);
	}

	public function testPaginateSecondPage(): void {
		$_REQUEST['page'] = 2;
		$_REQUEST['pageSize'] = 2;

		$response = $this->api->paginate($this->languagesQuery(), 'code ASC', function ($row) {
			return ['code' => $row->code];
		});

		$this->assertSame([
			['code' => 'zt3'],
			['code' => 'zt4'],
		], $response['items']);
	}

	public function testPaginateRespectsOrderBy(): void {
		$_REQUEST['pageSize'] = 2;

		$response = $this->api->paginate($this->languagesQuery(), 'code DESC', function ($row) {
			return $row->code;
		});

		$this->assertSame([
			'zt5',
			'zt4',
		], $response['items']);
	}

	public function testPaginatePageBeyondRange(): void {
		$_REQUEST['page'] = 99;

		$response = $this->api->paginate($this->languagesQuery(), 'code ASC', function ($row) {
			return $row->code;
		});

		$this->assertTrue($response['success']);
		$this->assertSame(5, $response['totalResults']);
		$this->assertSame(99, $response['page']);
		$this->assertSame([], $response['items']);
	}

	public function testPaginateSkipsRowsFormattedAsNull(): void {
		$response = $this->api->paginate($this->languagesQuery(), 'code ASC', function ($row) {
			return $row->code === 'zt3' ? null : $row->code;
		});

		$this->assertSame(5, $response['totalResults']);
		$this->assertSame([
			'zt1',
			'zt2',
			'zt4',
			'zt5',
		], $response['items']);
	}

	public function testPaginateEmptyResultSet(): void {
		$language = new \Language();
		$language->whereAdd("code = 'zzz'");

		$response = $this->api->paginate($language, 'code ASC', function ($row) {
			return $row->code;
		});

		$this->assertTrue($response['success']);
		$this->assertSame(0, $response['totalResults']);
		$this->assertSame(0, $response['totalPages']);
		$this->assertSame([], $response['items']);
	}
}
