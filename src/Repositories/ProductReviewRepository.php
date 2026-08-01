<?php

namespace Modules\Sirsoft\Ecommerce\Repositories;

use App\Repositories\Concerns\PaginatesWithDeferredJoin;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Sirsoft\Ecommerce\Enums\ReviewStatus;
use Modules\Sirsoft\Ecommerce\Models\OrderOption;
use Modules\Sirsoft\Ecommerce\Models\ProductOption;
use Modules\Sirsoft\Ecommerce\Models\ProductReview;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\ProductReviewRepositoryInterface;

/**
 * 상품 리뷰 Repository 구현체
 */
class ProductReviewRepository implements ProductReviewRepositoryInterface
{
    use PaginatesWithDeferredJoin;

    public function __construct(
        protected ProductReview $model
    ) {}

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?ProductReview
    {
        return $this->model->with(['user', 'product', 'images', 'orderOption.order', 'replyAdmin'])->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getListWithFilters(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        // 관계는 아래 relations: 인자로만 넘긴다 — 여기서 with() 해도 지연 조인 트레이트가
        // 지우므로 실제로 로드되지 않는다(중복 선언은 계약을 오해하게 만든다).
        $query = $this->model->newQuery();

        // 검색 키워드
        if (! empty($filters['search_keyword'])) {
            $keyword = $filters['search_keyword'];
            $field = $filters['search_field'] ?? 'product_name';

            $query->where(function ($q) use ($keyword, $field) {
                if ($field === 'product_name') {
                    $locales = config('app.translatable_locales', ['ko', 'en']);
                    $q->whereHas('product', function ($pq) use ($keyword, $locales) {
                        $pq->where(function ($inner) use ($keyword, $locales) {
                            foreach ($locales as $locale) {
                                $inner->orWhere("name->{$locale}", 'like', "%{$keyword}%");
                            }
                        });
                    });
                } elseif ($field === 'reviewer') {
                    $q->whereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%")
                            ->orWhere('nickname', 'like', "%{$keyword}%");
                    });
                } elseif ($field === 'content') {
                    $q->where('content', 'like', "%{$keyword}%");
                } elseif ($field === 'order_number') {
                    $q->whereHas('orderOption.order', function ($oq) use ($keyword) {
                        $oq->where('order_number', 'like', "%{$keyword}%");
                    });
                } elseif ($field === 'option_name') {
                    // option_snapshot JSON에서 옵션명 검색 (Laravel JSON 문법 사용)
                    $q->where('option_snapshot->option_name', 'like', "%{$keyword}%");
                }
            });
        }

        // 별점 필터
        if (! empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }

        // 답변 상태 필터
        if (isset($filters['reply_status']) && $filters['reply_status'] !== '') {
            if ($filters['reply_status'] === 'replied') {
                $query->whereNotNull('replied_at');
            } elseif ($filters['reply_status'] === 'unreplied') {
                $query->whereNull('replied_at');
            }
        }

        // 포토리뷰 필터
        if (isset($filters['photo']) && $filters['photo'] !== '') {
            if ($filters['photo'] === 'photo') {
                $query->whereHas('images');
            } elseif ($filters['photo'] === 'normal') {
                $query->whereDoesntHave('images');
            }
        }

        // 상태 필터
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 기간 필터 — whereDate 는 컬럼에 DATE() 를 씌워 인덱스를 못 쓰게 만든다.
        // 같은 결과를 내는 범위 조건으로 바꿔 created_at 인덱스를 살린다
        // (종료일은 그날 23:59:59.999999 까지 포함해야 whereDate 와 동일한 경계를 갖는다).
        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }
        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        // 정렬 — 선택지가 닫힌 집합이라 match 로 충분하다
        $sort = match ($filters['sort'] ?? 'created_at_desc') {
            'created_at_asc' => [['column' => 'created_at', 'direction' => 'asc']],
            'rating_desc' => [['column' => 'rating', 'direction' => 'desc']],
            'rating_asc' => [['column' => 'rating', 'direction' => 'asc']],
            default => [['column' => 'created_at', 'direction' => 'desc']],
        };

        return $this->paginateWithDeferredJoin(
            query: $query,
            columns: ['*'],
            sort: $sort,
            perPage: $perPage,
            relations: ['user', 'product', 'images', 'orderOption.order', 'replyAdmin'],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findByProduct(int $productId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        // 관계는 아래 relations: 인자로만 넘긴다 (위 getListWithFilters 와 같은 이유)
        $query = $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('status', ReviewStatus::VISIBLE->value);

        // 포토리뷰만 보기 (문자열 'false' 방어: truthy 값만 적용)
        $photoOnly = $filters['photo_only'] ?? false;
        if ($photoOnly === true || $photoOnly === '1' || $photoOnly === 'true') {
            $query->whereHas('images');
        }

        // 별점 필터
        if (! empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }

        // 옵션 필터 (키-값별 product_option_id IN 방식)
        if (! empty($filters['option_filters']) && is_array($filters['option_filters'])) {
            foreach ($filters['option_filters'] as $key => $value) {
                if ($value === '' || $value === null) {
                    continue;
                }
                $optionIds = DB::table((new ProductOption)->getTable())
                    ->where('product_id', $productId)
                    ->whereRaw(
                        "JSON_CONTAINS(option_values, JSON_OBJECT('key', JSON_OBJECT('ko', ?), 'value', JSON_OBJECT('ko', ?)))",
                        [$key, $value]
                    )
                    ->pluck('id')
                    ->toArray();

                if (empty($optionIds)) {
                    // 일치 옵션 없음 → 빈 결과. 빈 whereIn 은 `0 = 1` 로 컴파일된다
                    $query->whereIn($query->getModel()->getKeyName(), []);
                    break;
                }

                $query->whereHas('orderOption', function ($q) use ($optionIds) {
                    $q->whereIn('product_option_id', $optionIds);
                });
            }
        }

        // 정렬 — 선택지가 닫힌 집합이라 match 로 충분하다
        $sort = match ($filters['sort'] ?? 'created_at_desc') {
            'rating_desc' => [['column' => 'rating', 'direction' => 'desc']],
            'rating_asc' => [['column' => 'rating', 'direction' => 'asc']],
            default => [['column' => 'created_at', 'direction' => 'desc']],
        };

        return $this->paginateWithDeferredJoin(
            query: $query,
            columns: ['*'],
            sort: $sort,
            perPage: $perPage,
            relations: ['user', 'images'],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRatingStats(int $productId): array
    {
        $rows = $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('status', ReviewStatus::VISIBLE->value)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $total = array_sum($rows);
        $stats = [];

        $weightedSum = 0;
        for ($i = 5; $i >= 1; $i--) {
            $count = $rows[$i] ?? 0;
            $weightedSum += $i * $count;
            $stats[(string) $i] = [
                'count' => $count,
                'percent' => $total > 0 ? round($count / $total * 100) : 0,
            ];
        }

        $stats['avg'] = $total > 0 ? round($weightedSum / $total, 1) : 0;

        return $stats;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCount(int $productId): int
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->where('status', ReviewStatus::VISIBLE->value)
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionFilters(int $productId): array
    {
        // 상품의 모든 옵션 조회 (기준: product_options 전체)
        $options = DB::table((new ProductOption)->getTable())
            ->where('product_id', $productId)
            ->orderBy('sort_order')
            ->pluck('option_values', 'id')
            ->toArray();

        if (empty($options)) {
            return [];
        }

        // 옵션별 리뷰 건수 집계 (option_id → review count)
        // 테이블명은 모델에서 얻는다 (문자열 하드코딩 금지)
        $reviewCounts = DB::table((new ProductReview)->getTable().' as r')
            ->join((new OrderOption)->getTable().' as oo', 'r.order_option_id', '=', 'oo.id')
            ->where('r.product_id', $productId)
            ->where('r.status', ReviewStatus::VISIBLE->value)
            ->whereNull('r.deleted_at')
            ->whereIn('oo.product_option_id', array_keys($options))
            ->groupBy('oo.product_option_id')
            ->pluck(DB::raw('COUNT(*)'), 'oo.product_option_id')
            ->toArray();

        // 키별 고유값 + 건수 집계 (기본 옵션 제외)
        // 동일 키+값 조합이 여러 option_id에 중복될 수 있으므로 건수 합산
        $filters = [];
        foreach ($options as $optionId => $optionValuesJson) {
            $optionValues = json_decode($optionValuesJson, true);
            if (! is_array($optionValues)) {
                continue;
            }
            $count = (int) ($reviewCounts[$optionId] ?? 0);
            foreach ($optionValues as $item) {
                $keyKo = $item['key']['ko'] ?? '';
                $valueKo = $item['value']['ko'] ?? '';
                if ($keyKo === '기본' && $valueKo === '기본') {
                    continue;
                }
                if ($keyKo === '' || $valueKo === '') {
                    continue;
                }
                if (! isset($filters[$keyKo])) {
                    $filters[$keyKo] = [];
                }
                if (! isset($filters[$keyKo][$valueKo])) {
                    $filters[$keyKo][$valueKo] = 0;
                }
                $filters[$keyKo][$valueKo] += $count;
            }
        }

        // [['key' => '색상', 'values' => [['value' => '블랙', 'count' => 12], ...]], ...] 형태로 변환
        $result = [];
        foreach ($filters as $key => $valueCounts) {
            $values = [];
            foreach ($valueCounts as $value => $count) {
                $values[] = ['value' => $value, 'count' => $count];
            }
            $result[] = ['key' => $key, 'values' => $values];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function findByOrderOptionId(int $orderOptionId): ?ProductReview
    {
        return $this->model->where('order_option_id', $orderOptionId)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): ProductReview
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(ProductReview $review, array $data): ProductReview
    {
        $review->update($data);

        return $review->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(ProductReview $review): bool
    {
        return (bool) $review->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return $this->model->whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * {@inheritDoc}
     */
    public function getByIdsWithImages(array $ids): Collection
    {
        return $this->model->with('images')->whereIn('id', $ids)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function bulkSoftDeleteByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function transferByOrderOptionId(int $fromOrderOptionId, int $toOrderOptionId): int
    {
        return $this->model->where('order_option_id', $fromOrderOptionId)
            ->update(['order_option_id' => $toOrderOptionId]);
    }

    /**
     * {@inheritDoc}
     */
    public function getRecentAcrossProducts(int $limit): Collection
    {
        return $this->model->newQuery()
            ->with(['product', 'user'])
            ->where('status', ReviewStatus::VISIBLE)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
