<?php

namespace Modules\Sirsoft\Ecommerce\Tests\Feature;

// 테스트 베이스 클래스 수동 require (autoload 전에 로드 필요)
require_once __DIR__.'/../ModuleTestCase.php';

use Modules\Sirsoft\Ecommerce\Models\Category;
use Modules\Sirsoft\Ecommerce\Tests\ModuleTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * 통합 조회의 단건 분류 응답 계약 테스트 (#519 F4)
 *
 * 분류 화면은 종전에 분류·분류트리·상품목록으로 요청을 세 번 냈다. 이를 통합 조회 하나로
 * 합치면서 단건 분류를 응답에 얹었다. 옮긴 것은 조립 위치뿐이므로, **개별 엔드포인트와
 * 같은 리소스·같은 키 집합**이어야 한다. 리소스가 어긋나면 화면 표현식이 조용히 빈 값을
 * 읽어 제목·설명·하위분류·이미지가 예외 없이 사라진다.
 *
 * @scenario case=storefront_category_contract
 *
 * @effects storefront_category_matches_single_endpoint,
 *          storefront_without_slug_omits_category,
 *          storefront_unknown_slug_returns_not_found
 */
class StorefrontCategoryContractTest extends ModuleTestCase
{
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => ['ko' => '통합조회 분류'],
            'slug' => 'storefront-contract',
            'depth' => 0,
            'sort_order' => 1,
            'is_active' => true,
            'path' => '',
        ]);

        $this->category->update(['path' => (string) $this->category->id]);
    }

    /**
     * 통합 조회의 분류가 단건 엔드포인트와 같은 키 집합이어야 함
     *
     * @effects storefront_category_matches_single_endpoint
     */
    #[Test]
    public function storefront_category_matches_single_endpoint(): void
    {
        $single = $this->getJson('/api/modules/sirsoft-ecommerce/categories/'.$this->category->slug);
        $single->assertOk();

        $combined = $this->getJson(
            '/api/modules/sirsoft-ecommerce/storefront?category_slug='.$this->category->slug
        );
        $combined->assertOk();

        $expected = $single->json('data');
        $actual = $combined->json('data.category');

        $this->assertIsArray($actual, '통합 조회 응답에 분류가 없다 — 분류 화면이 제목조차 못 그린다.');

        $missing = array_diff(array_keys($expected), array_keys($actual));
        $this->assertSame(
            [],
            $missing,
            '단건 엔드포인트에는 있고 통합 조회에는 없는 필드: '.implode(', ', $missing)
        );

        $this->assertSame($expected, $actual, '같은 분류인데 두 경로의 응답이 다르다.');
    }

    /**
     * 분류를 지정하지 않으면 분류 키가 비어야 함 (쇼핑 첫 화면 경로)
     *
     * @effects storefront_without_slug_omits_category
     */
    #[Test]
    public function storefront_without_slug_omits_category(): void
    {
        $response = $this->getJson('/api/modules/sirsoft-ecommerce/storefront');

        $response->assertOk();
        $this->assertNull($response->json('data.category'));
    }

    /**
     * 없는 분류를 가리키면 404 여야 함
     *
     * 200 으로 빈 화면을 내보내면 검색엔진이 존재하지 않는 분류 주소를 정상 페이지로 수집한다.
     *
     * @effects storefront_unknown_slug_returns_not_found
     */
    #[Test]
    public function storefront_unknown_slug_returns_not_found(): void
    {
        $response = $this->getJson(
            '/api/modules/sirsoft-ecommerce/storefront?category_slug=no-such-category'
        );

        $response->assertNotFound();
    }
}
