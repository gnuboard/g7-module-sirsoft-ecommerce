<?php

namespace Modules\Sirsoft\Ecommerce\Http\Controllers\Public;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Api\Base\PublicBaseController;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Sirsoft\Ecommerce\Http\Requests\Public\PublicStorefrontRequest;
use Modules\Sirsoft\Ecommerce\Http\Resources\ProductCollection;
use Modules\Sirsoft\Ecommerce\Http\Resources\ProductListResource;
use Modules\Sirsoft\Ecommerce\Http\Resources\PublicCategoryDetailResource;
use Modules\Sirsoft\Ecommerce\Http\Resources\PublicCategoryResource;
use Modules\Sirsoft\Ecommerce\Services\CategoryService;
use Modules\Sirsoft\Ecommerce\Services\ProductService;

/**
 * 쇼핑 첫 화면 통합 조회 컨트롤러
 *
 * 쇼핑 첫 화면은 분류·상품 목록·최근 본·인기·신상품 다섯 묶음을 한 화면에 함께 그린다.
 * 이를 각각의 엔드포인트로 부르면 화면 하나에 요청이 다섯 번 나가고, 그 다섯 번이 전부
 * 같은 부팅(라우팅·미들웨어·권한 판정·설정 적재)을 되풀이한다.
 *
 * 각 묶음의 응답 형태(리소스 계약)는 개별 엔드포인트와 완전히 같다 — 옮긴 것은 조립
 * 위치뿐이므로, 개별 엔드포인트도 그대로 남아 다른 화면에서 계속 쓰인다.
 */
class StorefrontController extends PublicBaseController
{
    public function __construct(
        private ProductService $productService,
        private CategoryService $categoryService
    ) {}

    /**
     * 쇼핑 첫 화면에 필요한 데이터를 한 번에 조회합니다.
     *
     * @param  PublicStorefrontRequest  $request  요청 데이터
     * @return JsonResponse 분류·상품 목록·최근 본·인기·신상품을 담은 JSON 응답
     */
    public function index(PublicStorefrontRequest $request): JsonResponse
    {
        try {
            $this->logApiUsage('storefront.index');

            $validated = $request->validated();

            $category = $this->resolveFocusedCategory($validated);

            if ($category === false) {
                return ResponseHelper::notFound(
                    'messages.categories.not_found',
                    [],
                    'sirsoft-ecommerce'
                );
            }

            return ResponseHelper::moduleSuccess(
                'sirsoft-ecommerce',
                'messages.products.fetch_success',
                [
                    'category' => $category,
                    'categories' => PublicCategoryResource::collection(
                        $this->categoryService->getPublicCategoryTree()
                    )->resolve(),
                    'products' => $this->resolveProductList($request, $validated),
                    'recent_products' => $this->resolveRecentProducts($validated),
                    'popular_products' => $this->resolveDisplayGroup(
                        (int) ($validated['popular_limit'] ?? 8),
                        fn (int $limit) => $this->productService->getPopularProducts($limit)
                    ),
                    'new_products' => $this->resolveDisplayGroup(
                        (int) ($validated['new_limit'] ?? 8),
                        fn (int $limit) => $this->productService->getNewProducts($limit)
                    ),
                ]
            );
        } catch (Exception $e) {
            return ResponseHelper::moduleError(
                'sirsoft-ecommerce',
                'messages.products.fetch_failed',
                500
            );
        }
    }

    /**
     * 상품 목록 묶음을 조립합니다.
     *
     * 개별 상품 목록 엔드포인트와 같은 컬렉션을 쓰므로 페이지네이션 메타까지 형태가 같습니다.
     *
     * @param  PublicStorefrontRequest  $request  요청 객체
     * @param  array<string, mixed>  $validated  검증된 입력
     * @return array<string, mixed> 상품 목록 응답 데이터
     */
    private function resolveProductList(PublicStorefrontRequest $request, array $validated): array
    {
        $listKeys = ['category_id', 'category_slug', 'brand_id', 'search', 'sort', 'min_price', 'max_price'];
        $filters = array_filter(
            $validated,
            fn ($value, $key) => in_array($key, $listKeys, true) && $value !== null && $value !== '',
            ARRAY_FILTER_USE_BOTH
        );

        $perPage = (int) ($validated['per_page'] ?? 20);
        $collection = new ProductCollection($this->productService->getPublicList($filters, $perPage));

        return $collection->toArray($request);
    }

    /**
     * 특정 분류 화면이 필요로 하는 단건 분류를 조립합니다.
     *
     * 분류 화면은 "지금 보고 있는 분류" 하나와 전체 분류 트리, 그 분류의 상품 목록을 함께
     * 그린다. 단건 분류를 여기서 함께 돌려주면 그 화면도 요청 한 번으로 끝난다.
     * `category_slug` 가 없으면(쇼핑 첫 화면) 조회하지 않는다.
     *
     * 응답 형태는 단건 분류 엔드포인트와 같은 리소스를 써서 완전히 일치시킨다 —
     * 옮긴 것은 조립 위치뿐이므로 화면 바인딩이 그대로 동작해야 한다.
     *
     * 반환값이 false 면 "분류를 지정했는데 그런 분류가 없다" 는 뜻이다. 이 경우 호출자가
     * 404 로 끊는다 — 200 으로 빈 화면을 내보내면 검색엔진이 존재하지 않는 분류 주소를
     * 정상 페이지로 수집한다.
     *
     * @param  array<string, mixed>  $validated  검증된 입력
     * @return array<string, mixed>|null|false 분류 응답 데이터 / 미지정 시 null / 대상 없음 시 false
     */
    private function resolveFocusedCategory(array $validated): array|null|false
    {
        $slug = trim((string) ($validated['category_slug'] ?? ''));

        if ($slug === '') {
            return null;
        }

        $category = $this->categoryService->getPublicCategoryBySlug($slug);

        return $category ? (new PublicCategoryDetailResource($category))->resolve() : false;
    }

    /**
     * 최근 본 상품 묶음을 조립합니다.
     *
     * 최근 본 상품은 클라이언트가 보관한 ID 목록으로만 정해지므로, 비어 있으면 조회하지 않습니다.
     *
     * @param  array<string, mixed>  $validated  검증된 입력
     * @return array<int, mixed> 상품 목록 (없으면 빈 배열)
     */
    private function resolveRecentProducts(array $validated): array
    {
        $ids = array_values(array_filter(array_map(
            'intval',
            explode(',', (string) ($validated['recent_ids'] ?? ''))
        )));

        if (empty($ids)) {
            return [];
        }

        return ProductListResource::collection(
            $this->productService->getProductsByIds($ids)
        )->resolve();
    }

    /**
     * 진열 묶음(인기·신상품)을 조립합니다.
     *
     * 개수가 0 이면 그 묶음을 쓰지 않겠다는 뜻이므로 조회하지 않습니다.
     *
     * @param  int  $limit  조회 개수
     * @param  callable  $resolver  상품 조회 클로저
     * @return array<int, mixed> 상품 목록 (없으면 빈 배열)
     */
    private function resolveDisplayGroup(int $limit, callable $resolver): array
    {
        if ($limit <= 0) {
            return [];
        }

        return ProductListResource::collection($resolver($limit))->resolve();
    }
}
