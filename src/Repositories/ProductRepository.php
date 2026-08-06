<?php

namespace Modules\Sirsoft\Ecommerce\Repositories;

use App\Helpers\PermissionHelper;
use App\Models\ActivityLog;
use App\Repositories\Concerns\PaginatesWithDeferredJoin;
use App\Repositories\Concerns\ResolvesSortSpec;
use App\Search\Engines\DatabaseFulltextEngine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Sirsoft\Ecommerce\Enums\ProductDisplayStatus;
use Modules\Sirsoft\Ecommerce\Models\Category;
use Modules\Sirsoft\Ecommerce\Models\OrderOption;
use Modules\Sirsoft\Ecommerce\Models\Product;
use Modules\Sirsoft\Ecommerce\Models\ProductOption;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\ProductRepositoryInterface;

/**
 * 상품 Repository 구현체
 */
class ProductRepository implements ProductRepositoryInterface
{
    use PaginatesWithDeferredJoin;
    use ResolvesSortSpec;

    /** 허용 정렬 컬럼 (ProductListRequest 와 동일 집합) */
    private const ADMIN_SORTABLE_COLUMNS = ['created_at', 'updated_at', 'selling_price', 'stock_quantity', 'name'];

    public function __construct(
        protected Product $model
    ) {}

    /**
     * {@inheritDoc}
     */
    public function existsAny(): bool
    {
        // 소프트삭제 포함 — 삭제된 상품도 과거 base 로 생성된 이력이라 base 잠금 유지가 안전(A2)
        return $this->model->newQuery()->withTrashed()->exists();
    }

    /**
     * Sitemap 용으로 전시 중인 상품을 스트리밍 조회합니다.
     *
     * lazyById 는 id 기준 키셋 페이징으로 청크를 순차 조회하므로,
     * 결과셋 전체가 메모리(및 DB 드라이버 버퍼)에 적재되지 않습니다.
     *
     * @param  int  $chunkSize  청크 크기
     * @return iterable<Product> 전시 중인 상품 순회자 (id, updated_at 만 조회)
     */
    public function streamVisibleForSitemap(int $chunkSize = 500): iterable
    {
        return $this->model->newQuery()
            ->where('display_status', ProductDisplayStatus::VISIBLE->value)
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->lazyById($chunkSize);
    }

    /**
     * {@inheritDoc}
     */
    public function findWithOptions(int $id, bool $includeInactive = false): ?Product
    {
        $optionRelation = $includeInactive ? 'options' : 'activeOptions';

        return $this->model
            ->with([$optionRelation, 'additionalOptions.values', 'categories', 'images', 'notice', 'activeLabelAssignments.label'])
            ->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getListWithFilters(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 권한 스코프 필터링
        PermissionHelper::applyPermissionScope($query, 'sirsoft-ecommerce.products.read');

        // 지연 조인 outer 전용 관계 (inner 는 id 만 훑으므로 여기서 with() 를 걸지 않는다)
        //
        // 옵션은 기본적으로 로드하지 않는다. 목록 한 페이지(최대 100행)를 여는 것만으로 그 페이지
        // 전 상품의 옵션 행이 모두 적재·직렬화되기 때문이다. 목록이 실제로 쓰는 것은 개수와 재고
        // 합계뿐이라 아래 DB 집계로 대체하고, 옵션 상세는 행을 펼칠 때 배치 엔드포인트
        // (`GET admin/products/options`)로 가져간다. `?with_options=1` 은 종전 동작을 위한 opt-in.
        $listRelations = ['categories', 'images', 'brand', 'shippingPolicy'];

        if (! empty($filters['with_options'])) {
            $listRelations[] = 'options';
        }

        // 옵션 집계 — PHP 컬렉션 연산 대신 DB 집계로 계산한다.
        //   options_count       : 활성 옵션 수 (화면 표시용)
        //   options_total_count : 비활성 포함 전체 옵션 수. 일괄 변경 확인 모달이 서버의
        //                         적용 모집단(ProductOptionRepository::getIdsByProductIds — is_active
        //                         무필터)과 같은 수를 세려면 이쪽이어야 한다
        // 별칭을 생략하면 두 카운트의 기본 별칭이 `options_count` 로 같아져 뒤엣것이 앞엣것을
        // 조용히 덮으므로 `as` 로 반드시 구분한다.
        $listWithCount = [
            'options as options_count' => fn ($q) => $q->where('is_active', true),
            'options as options_total_count',
        ];

        // withSum 은 트레이트 인자가 없으므로 outer 전용 클로저로 붙인다. inner 에 붙으면
        // 건너뛸 행 전체에 대해 실행된다.
        $listOuterUsing = fn (Builder $outer) => $outer->withSum(
            ['options as option_stock_sum' => fn ($q) => $q->where('is_active', true)],
            'stock_quantity'
        );

        // 문자열 검색
        if (! empty($filters['search_keyword'])) {
            $keyword = $filters['search_keyword'];
            $field = $filters['search_field'] ?? 'all';

            // FULLTEXT 대상 필드(all/name/description)는 Scout 검색 ID 와 보조필드 LIKE 매칭 ID 의
            // 합집합(union)을 미리 산출한 뒤 plain Eloquent whereIn 으로 조회한다.
            // (total 계산 경로와 결과 조회 경로가 동일한 검색 조건을 보장 — Scout queryCallback
            //  total 재계산 시 보조필드 OR 가 MATCH 절 없이 재적용되어 total=0 이 되는 결함 회피)
            if (in_array($field, ['all', 'name', 'description'])) {
                // FULLTEXT 매칭 ID (Scout 엔진 순수 검색 — queryCallback 없음)
                $ftIds = Product::search($keyword)->keys()->all();

                // 보조필드(id/product_code/sku/barcode) LIKE 매칭 ID (all 일 때만)
                $auxIds = [];
                if ($field === 'all') {
                    $auxIds = $this->model->newQuery()
                        ->where(function ($q) use ($keyword) {
                            $q->where('product_code', 'like', "%{$keyword}%")
                                ->orWhere('sku', 'like', "%{$keyword}%")
                                ->orWhere('barcode', 'like', "%{$keyword}%");

                            // 숫자 키워드는 상품 ID 정확 매칭도 포함 (예: "99" → id=99)
                            if (ctype_digit($keyword)) {
                                $q->orWhere('id', (int) $keyword);
                            }
                        })
                        ->pluck('id')
                        ->all();
                }

                $matchedIds = array_values(array_unique([...$ftIds, ...$auxIds]));

                // 매칭 ID 로 조건 한정 (빈 매칭은 존재하지 않는 ID 로 빈 결과 → total=0 이 정상)
                $query->whereIn('id', $matchedIds ?: [0]);

                // 필터 적용
                $this->applyAdminFilters($query, $filters);

                return $this->paginateWithDeferredJoin(
                    query: $query,
                    columns: ['*'],
                    sort: $this->resolveAdminSortSpec($filters),
                    perPage: $perPage,
                    relations: $listRelations,
                    withCount: $listWithCount,
                    outerUsing: $listOuterUsing,
                );
            }

            // FULLTEXT 미대상 필드 (product_code, sku, barcode) → LIKE 직접
            $query->where(function ($q) use ($keyword, $field) {
                if ($field === 'product_code') {
                    $q->where('product_code', 'like', "%{$keyword}%");
                }
                if ($field === 'sku') {
                    $q->where('sku', 'like', "%{$keyword}%");
                }
                if ($field === 'barcode') {
                    $q->where('barcode', 'like', "%{$keyword}%");
                }
            });
        }

        // 필터 적용
        $this->applyAdminFilters($query, $filters);

        return $this->paginateWithDeferredJoin(
            query: $query,
            columns: ['*'],
            sort: $this->resolveAdminSortSpec($filters),
            perPage: $perPage,
            relations: $listRelations,
            withCount: $listWithCount,
            outerUsing: $listOuterUsing,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsGroupedByProductIds(array $productIds): array
    {
        $requested = array_values(array_unique(array_map('intval', $productIds)));

        if ($requested === []) {
            return ['product_ids' => [], 'options' => []];
        }

        // ① 권한 스코프를 적용한 상품 쿼리로 허용 ID 를 먼저 확정한다. 옵션 테이블을 바로
        //    조회하면 scope=self 사용자가 남의 상품 ID 를 실어보내는 것만으로 옵션을 읽을 수 있다.
        $scoped = $this->model->newQuery();
        PermissionHelper::applyPermissionScope($scoped, 'sirsoft-ecommerce.products.read');

        $allowedIds = $scoped->whereIn('id', $requested)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($allowedIds === []) {
            return ['product_ids' => [], 'options' => []];
        }

        // 요청 순서를 유지한다 (프론트가 요청 배열과 그대로 대조할 수 있도록)
        $allowedSet = array_flip($allowedIds);
        $allowedIds = array_values(array_filter($requested, fn ($id) => isset($allowedSet[$id])));

        // ② 옵션은 상품 수와 무관하게 쿼리 1개. 비활성 옵션도 포함한다 — 인라인 편집기가
        //    `is_active` 토글을 다루므로 활성만 내려주면 비활성 옵션을 편집할 수 없게 된다.
        //    정렬은 상품폼/activeOptions 와 같은 `sort_order` 기준으로 통일한다.
        //
        //    `product` 를 함께 로드하는 이유: 옵션 리소스의 가격 필드는 상품 정가/판매가에
        //    조정액을 더해 만든다(ProductOption::getListPrice/getFinalPrice). 부모를 로드하지
        //    않으면 옵션 1건마다 상품 조회가 한 번씩 나가 옵션 수만큼 N+1 이 된다.
        $options = ProductOption::with('product')
            ->whereIn('product_id', $allowedIds)
            ->orderBy('product_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('product_id');

        return ['product_ids' => $allowedIds, 'options' => $options];
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function forceDelete(Product $product): bool
    {
        return (bool) $product->forceDelete();
    }

    /**
     * {@inheritDoc}
     */
    public function getSnapshotsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->model->whereIn('id', $ids)->get()->keyBy('id')->map->toArray()->all();
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateStatus(array $ids, string $field, string $value): int
    {
        return $this->model
            ->whereIn('id', $ids)
            ->update([
                $field => $value,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdatePrice(array $ids, string $method, float $value, string $unit): int
    {
        $updatedCount = 0;

        DB::transaction(function () use ($ids, $method, $value, $unit, &$updatedCount) {
            $products = $this->model->whereIn('id', $ids)->get();

            foreach ($products as $product) {
                $currentPrice = $product->selling_price;
                $newPrice = $this->calculateNewPrice($currentPrice, $method, $value, $unit);

                $product->update([
                    'selling_price' => max(0, $newPrice),
                    'updated_by' => auth()->id(),
                ]);
                $updatedCount++;
            }
        });

        return $updatedCount;
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateStock(array $ids, string $method, int $value): int
    {
        $updatedCount = 0;

        DB::transaction(function () use ($ids, $method, $value, &$updatedCount) {
            $products = $this->model->whereIn('id', $ids)->get();

            foreach ($products as $product) {
                $currentStock = $product->stock_quantity;
                $newStock = match ($method) {
                    'increase' => $currentStock + $value,
                    'decrease' => $currentStock - $value,
                    'set' => $value,
                    default => $currentStock,
                };

                $product->update([
                    'stock_quantity' => max(0, $newStock),
                    'updated_by' => auth()->id(),
                ]);
                $updatedCount++;
            }
        });

        return $updatedCount;
    }

    /**
     * {@inheritDoc}
     */
    public function existsByProductCode(string $productCode, ?int $excludeId = null): bool
    {
        $query = $this->model->where('product_code', $productCode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatistics(): array
    {
        $total = $this->model->count();

        // 판매상태별 통계
        $salesStatusCounts = $this->model
            ->selectRaw('sales_status, COUNT(*) as count')
            ->groupBy('sales_status')
            ->pluck('count', 'sales_status')
            ->toArray();

        // 전시상태별 통계
        $displayStatusCounts = $this->model
            ->selectRaw('display_status, COUNT(*) as count')
            ->groupBy('display_status')
            ->pluck('count', 'display_status')
            ->toArray();

        // 재고 부족 상품 수 (안전재고 이하)
        $lowStockCount = $this->model
            ->whereColumn('stock_quantity', '<=', 'safe_stock_quantity')
            ->count();

        // 품절 상품 수
        $outOfStockCount = $this->model
            ->where('stock_quantity', '<=', 0)
            ->count();

        return [
            'total' => $total,
            'sales_status' => $salesStatusCounts,
            'display_status' => $displayStatusCounts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function findByProductCode(string $productCode): ?Product
    {
        return $this->model
            ->with(['activeOptions', 'categories', 'images'])
            ->where('product_code', $productCode)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findWithAllRelations(int $id): ?Product
    {
        return $this->model
            ->with([
                'activeOptions',
                'options',
                'categories',
                'images',
                'brand',
                'additionalOptions',
                'additionalOptions.values',
                'labelAssignments',
                'labelAssignments.label',
                'notice',
                'shippingPolicy',
                'shippingPolicy.countrySettings',
            ])
            ->find($id);
    }

    /**
     * 새 가격 계산
     *
     * @param  float  $currentPrice  현재 가격
     * @param  string  $method  변경 방식
     * @param  float  $value  변경 값
     * @param  string  $unit  단위
     */
    protected function calculateNewPrice(float $currentPrice, string $method, float $value, string $unit): float
    {
        // 소수 통화 대응: 절사 대신 소수 2자리 반올림 보존
        $adjustAmount = $unit === 'percent'
            ? round($currentPrice * $value / 100, 2)
            : $value;

        return match ($method) {
            'increase' => $currentPrice + $adjustAmount,
            'decrease' => $currentPrice - $adjustAmount,
            'set' => $value,
            default => $currentPrice,
        };
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Product
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateFields(array $ids, array $fields): int
    {
        if (empty($ids) || empty($fields)) {
            return 0;
        }

        // 업데이트 시간과 수정자 정보 추가
        $fields['updated_at'] = now();
        $fields['updated_by'] = auth()->id();

        return $this->model
            ->whereIn('id', $ids)
            ->update($fields);
    }

    /**
     * {@inheritDoc}
     */
    public function getPublicList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->withCount('visibleReviews as review_count')
            ->withAvg('visibleReviews as rating_avg', 'rating')
            ->where('display_status', 'visible');

        // 지연 조인 outer 전용 관계 (inner 는 id 만 훑으므로 with() 를 걸지 않는다)
        $listRelations = ['images', 'categories', 'brand', 'activeLabelAssignments.label'];

        // 카테고리 필터 (ID) — 선택 카테고리 + 모든 하위 카테고리 포함
        if (! empty($filters['category_id'])) {
            $categoryIds = Category::selfAndDescendantIds((int) $filters['category_id']);
            $query->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('ecommerce_product_categories.category_id', $categoryIds);
            });
        }

        // 카테고리 필터 (slug) — 선택 카테고리 + 모든 하위 카테고리 포함
        if (! empty($filters['category_slug'])) {
            $root = Category::where('slug', $filters['category_slug'])->first(['id']);
            if ($root) {
                $categoryIds = Category::selfAndDescendantIds((int) $root->id);
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('ecommerce_product_categories.category_id', $categoryIds);
                });
            } else {
                // 존재하지 않는 slug → 빈 결과 (500 아님). 빈 whereIn 은 `0 = 1` 로 컴파일된다
                $query->whereIn($query->getModel()->getKeyName(), []);
            }
        }

        // 키워드 검색 (FULLTEXT)
        if (! empty($filters['search'])) {
            $keyword = $filters['search'];
            DatabaseFulltextEngine::whereFulltext($query, 'name', $keyword);
        }

        // 가격 범위 필터 (소수 통화 대응 — float 비교)
        // 0 은 유효한 경계값이다(무료 상품만 보기). empty() 로 거르면 필터가 무시된다.
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->where('selling_price', '>=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->where('selling_price', '<=', (float) $filters['max_price']);
        }

        // 브랜드 필터
        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // 정렬
        $sort = $filters['sort'] ?? 'latest';

        // 판매량 정렬은 집계 서브쿼리(total_sold) 를 select 에 올려 두고 그 별칭으로 정렬한다.
        // 지연 조인의 inner 는 select 를 id 하나로 교체하므로 별칭이 사라져 성립하지 않는다.
        if ($sort === 'sales') {
            $query
                ->addSelect(['total_sold' => OrderOption::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('product_id', 'ecommerce_products.id'),
                ])
                ->orderByDesc('total_sold')
                ->with($listRelations);

            // audit:allow repository-paginate-column-pruning reason: 판매량 정렬은 집계 서브쿼리
            // 별칭 기준이라 지연 조인 inner(=id 단일 select)로 옮길 수 없다. 나머지 정렬은 지연 조인 적용
            return $query->paginate($perPage);
        }

        $sortSpec = match ($sort) {
            'price_asc' => [['column' => 'selling_price', 'direction' => 'asc']],
            'price_desc' => [['column' => 'selling_price', 'direction' => 'desc']],
            default => [['column' => 'created_at', 'direction' => 'desc']], // latest
        };

        return $this->paginateWithDeferredJoin(
            query: $query,
            columns: ['*'],
            sort: $sortSpec,
            perPage: $perPage,
            relations: $listRelations,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getPopularProducts(int $limit = 10): Collection
    {
        $thirtyDaysAgo = now()->subDays(30);

        return $this->model->newQuery()
            ->with(['images', 'categories', 'activeLabelAssignments.label'])
            ->withCount('visibleReviews as review_count')
            ->withAvg('visibleReviews as rating_avg', 'rating')
            ->where('display_status', 'visible')
            ->addSelect(['recent_sold' => OrderOption::selectRaw('COALESCE(SUM(quantity), 0)')
                ->whereColumn('product_id', 'ecommerce_products.id')
                ->where('created_at', '>=', $thirtyDaysAgo),
            ])
            ->orderByDesc('recent_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getNewProducts(int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->with(['images', 'categories', 'activeLabelAssignments.label'])
            ->withCount('visibleReviews as review_count')
            ->withAvg('visibleReviews as rating_avg', 'rating')
            ->where('display_status', 'visible')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return $this->model->newCollection();
        }

        $products = $this->model->newQuery()
            ->with(['images', 'categories', 'activeLabelAssignments.label'])
            ->where('display_status', 'visible')
            ->whereIn('id', $ids)
            ->get();

        // 클라이언트 요청 순서 유지 (DB 독립적 정렬)
        $idOrder = array_flip($ids);

        return $products->sortBy(fn ($product) => $idOrder[$product->id] ?? PHP_INT_MAX)->values();
    }

    /**
     * {@inheritDoc}
     */
    public function searchByKeyword(string $keyword, string $orderBy = 'created_at', string $direction = 'desc', ?int $categoryId = null, int $offset = 0, int $limit = 10): array
    {
        $page = (int) floor($offset / $limit) + 1;

        // FULLTEXT(name/description) 매칭 + 보조필드(product_code/id) 매칭의 합집합.
        // 통합검색도 관리자 검색과 동일하게 상품코드/ID 로 상품을 찾을 수 있어야 한다.
        $matchedIds = $this->resolveKeywordMatchedProductIds($keyword);

        $paginator = $this->model->newQuery()
            ->whereIn('id', $matchedIds ?: [0])
            ->where('display_status', ProductDisplayStatus::VISIBLE->value)
            // audit:allow list-repository-eager-load-vs-resource reason: 통합검색 결과는 Resource 가
            // 아니라 SearchProductsListener 가 직렬화한다 — 그 안에서 primaryCategory 를 행마다
            // 읽으므로(`$product->primaryCategory->first()`) 여기서 미리 로드하지 않으면 N+1 이 된다
            ->with(['images', 'primaryCategory', 'brand', 'activeLabelAssignments.label'])
            ->withCount('visibleReviews as review_count')
            ->withAvg('visibleReviews as rating_avg', 'rating')
            ->when($categoryId !== null, fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('ecommerce_categories.id', $categoryId)))
            ->orderBy($orderBy, $direction)
            // 전순서 보장 — 정렬 컬럼(등록일/판매량 등)이 비고유라 페이지 경계가 흔들릴 수 있다
            ->orderBy('id', $direction === 'asc' ? 'asc' : 'desc')
            // audit:allow repository-paginate-column-pruning reason: 통합검색 —
            // whereIn(id, $matchedIds) 로 FULLTEXT 매칭 ID 집합에 먼저 한정되므로 스캔 대상이
            // 상품 전체가 아니라 매칭 건수로 묶인다(OFFSET 이 훑는 범위도 그 안). 지연 조인의
            // inner 가 하는 일을 매칭 ID 산출이 이미 수행하는 구조다
            ->paginate($limit, ['*'], 'page', $page);

        return ['total' => $paginator->total(), 'items' => $paginator->getCollection()];
    }

    /**
     * 키워드로 매칭되는 상품 ID 집합을 반환합니다.
     *
     * FULLTEXT(name/description) Scout 매칭과 보조필드(product_code / 숫자 ID) 매칭의
     * 합집합. 통합검색의 결과 조회와 total 계산이 동일한 조건을 쓰도록 단일 경로로 산출한다.
     *
     * @param  string  $keyword  검색 키워드
     * @return array<int> 매칭된 상품 ID 배열
     */
    private function resolveKeywordMatchedProductIds(string $keyword): array
    {
        $ftIds = Product::search($keyword)->keys()->all();

        $auxIds = $this->model->newQuery()
            ->where(function ($q) use ($keyword) {
                $q->where('product_code', 'like', "%{$keyword}%");

                // 숫자 키워드는 상품 ID 정확 매칭도 포함
                if (ctype_digit($keyword)) {
                    $q->orWhere('id', (int) $keyword);
                }
            })
            ->pluck('id')
            ->all();

        return array_values(array_unique([...$ftIds, ...$auxIds]));
    }

    /**
     * {@inheritDoc}
     */
    public function countByKeyword(string $keyword, ?int $categoryId = null): int
    {
        // searchByKeyword 와 동일한 매칭 조건(FULLTEXT ∪ 보조필드)으로 total 산출.
        $matchedIds = $this->resolveKeywordMatchedProductIds($keyword);

        return $this->model->newQuery()
            ->whereIn('id', $matchedIds ?: [0])
            ->where('display_status', ProductDisplayStatus::VISIBLE->value)
            ->when($categoryId !== null, fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('ecommerce_categories.id', $categoryId)))
            ->count();
    }

    /**
     * 관리자 상품 목록 필터를 쿼리에 적용합니다.
     *
     * @param  Builder  $query  Eloquent 쿼리 빌더
     * @param  array  $filters  필터 배열
     */
    private function applyAdminFilters($query, array $filters): void
    {
        // 카테고리 필터 (다대다 관계)
        if (! empty($filters['category_id'])) {
            $categoryId = $filters['category_id'];
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('ecommerce_product_categories.category_id', $categoryId);
            });
        }

        // 카테고리 미부여 상품
        if (! empty($filters['no_category']) && $filters['no_category'] === true) {
            $query->whereDoesntHave('categories');
        }

        // 날짜 필터
        if (! empty($filters['date_type']) && (! empty($filters['start_date']) || ! empty($filters['end_date']))) {
            $dateField = $filters['date_type'];

            if (! empty($filters['start_date'])) {
                $query->whereDate($dateField, '>=', $filters['start_date']);
            }
            if (! empty($filters['end_date'])) {
                $query->whereDate($dateField, '<=', $filters['end_date']);
            }
        }

        // 판매상태 필터 (다중 선택 가능)
        if (! empty($filters['sales_status'])) {
            $statuses = is_array($filters['sales_status'])
                ? $filters['sales_status']
                : [$filters['sales_status']];
            $query->whereIn('sales_status', $statuses);
        }

        // 전시상태 필터
        if (! empty($filters['display_status'])) {
            $query->where('display_status', $filters['display_status']);
        }

        // 브랜드 필터
        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // 브랜드 미부여 상품
        if (! empty($filters['no_brand']) && $filters['no_brand'] === true) {
            $query->whereNull('brand_id');
        }

        // 과세여부 필터
        if (! empty($filters['tax_status'])) {
            $query->where('tax_status', $filters['tax_status']);
        }

        // 가격 범위 필터
        if (! empty($filters['price_type'])) {
            $priceField = $filters['price_type'];

            // 0 은 유효한 경계값 — empty() 판정 금지 (min_stock/max_stock 과 동일 규칙)
            if (isset($filters['min_price']) && $filters['min_price'] !== '') {
                $query->where($priceField, '>=', (float) $filters['min_price']);
            }
            if (isset($filters['max_price']) && $filters['max_price'] !== '') {
                $query->where($priceField, '<=', (float) $filters['max_price']);
            }
        }

        // 재고 범위 필터
        if (isset($filters['min_stock']) && $filters['min_stock'] !== '') {
            $query->where('stock_quantity', '>=', (int) $filters['min_stock']);
        }
        if (isset($filters['max_stock']) && $filters['max_stock'] !== '') {
            $query->where('stock_quantity', '<=', (int) $filters['max_stock']);
        }

        // 배송정책 필터
        if (! empty($filters['shipping_policy_id'])) {
            $query->where('shipping_policy_id', $filters['shipping_policy_id']);
        }
    }

    /**
     * 관리자 상품 목록 정렬 스펙을 해석합니다.
     *
     * 지연 조인은 정렬을 inner/outer 양쪽에 동일하게 적용해야 하므로, 쿼리에 직접 orderBy 를
     * 거는 대신 스펙 배열을 돌려준다.
     *
     * @param  array  $filters  필터 배열
     * @return array<int, array{column: string, direction: string}> 정렬 스펙
     */
    private function resolveAdminSortSpec(array $filters): array
    {
        // 허용 컬럼 화이트리스트로 해석 (없는 컬럼 정렬은 기본값으로 흡수)
        $sort = $this->resolveSortSpec($filters, self::ADMIN_SORTABLE_COLUMNS, 'created_at')[0];

        // 다국어 이름 정렬 처리
        if ($sort['column'] === 'name') {
            $sort['column'] = 'name->'.app()->getLocale();
        }

        return [$sort];
    }

    /**
     * {@inheritDoc}
     */
    public function syncStockFromOptions(int $productId): bool
    {
        $product = $this->model->find($productId);

        if (! $product) {
            return false;
        }

        // 옵션이 없는 상품은 동기화 불필요
        if (! $product->has_options) {
            return true;
        }

        // 활성 옵션의 재고 합계 계산
        $totalStock = $product->options()
            ->where('is_active', true)
            ->sum('stock_quantity');

        $product->stock_quantity = $totalStock;
        $product->save();

        return true;
    }

    /**
     * ID 목록으로 상품을 조회하고 ID 키 맵으로 반환합니다 (bulk activity log lookup).
     *
     * @param  array<int, int>  $ids  상품 ID 목록
     * @return Collection ID 를 키로 하는 상품 컬렉션
     */
    public function findByIdsKeyed(array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection;
        }

        return $this->model->whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * 상품의 stock_quantity 컬럼만 갱신합니다 (옵션 재고 합계 동기화 전용).
     *
     * @param  int  $productId  상품 ID
     * @param  int  $stock  새 재고 수량
     * @return int 업데이트된 행 수
     */
    public function updateStockQuantity(int $productId, int $stock): int
    {
        return $this->model->where('id', $productId)->update(['stock_quantity' => $stock]);
    }

    /**
     * {@inheritDoc}
     */
    public function getActivityLogsForProduct(Product $product, array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $optionIds = $product->options()->pluck('id')->toArray();

        $query = ActivityLog::where(function (Builder $q) use ($product, $optionIds) {
            // 상품 자체 로그
            $q->where(function (Builder $sub) use ($product) {
                $sub->where('loggable_type', $product->getMorphClass())
                    ->where('loggable_id', $product->getKey());
            });

            // 해당 상품의 옵션 로그
            if (! empty($optionIds)) {
                $q->orWhere(function (Builder $sub) use ($optionIds) {
                    $sub->where('loggable_type', (new ProductOption)->getMorphClass())
                        ->whereIn('loggable_id', $optionIds);
                });
            }
        });

        // 활동 로그는 변경 내역(mediumText)을 리소스가 그대로 노출하므로 컬럼은 좁히지 않고
        // 지연 조인으로 넓은 컬럼을 읽는 행 수만 이번 페이지 분량으로 고정한다.
        return $this->paginateWithDeferredJoin(
            query: $query,
            columns: ['*'],
            sort: [['column' => 'created_at', 'direction' => $sortOrder]],
            perPage: $perPage,
        );
    }
}
