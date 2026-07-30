<?php

namespace Modules\Sirsoft\Ecommerce\Repositories;

use App\Repositories\Concerns\PaginatesWithDeferredJoin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Modules\Sirsoft\Ecommerce\Models\ProductInquiry;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\ProductInquiryRepositoryInterface;

/**
 * 상품 1:1 문의 Repository 구현체
 */
class ProductInquiryRepository implements ProductInquiryRepositoryInterface
{
    use PaginatesWithDeferredJoin;

    public function __construct(
        protected ProductInquiry $model
    ) {}

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?ProductInquiry
    {
        return $this->model->with(['product', 'user'])->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findByProductId(int $productId): Collection
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function paginateByProductId(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            // audit:allow repository-paginate-column-pruning reason: 상품 1건에 종속된 문의 목록 —
            // where(product_id) 로 이미 좁혀져 OFFSET 이 깊어질 수 없고, 목록이 본문을 그대로 쓴다
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function findByInquirable(string $inquirableType, int $inquirableId): ?ProductInquiry
    {
        return $this->model->newQuery()
            ->where('inquirable_type', $inquirableType)
            ->where('inquirable_id', $inquirableId)
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findByUserId(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // 답변 여부 필터
        if (isset($filters['is_answered']) && $filters['is_answered'] !== '') {
            $query->where('is_answered', (bool) $filters['is_answered']);
        }

        // 상품명 검색 (product_name_snapshot JSON 컬럼)
        if (! empty($filters['search'])) {
            $keyword = $filters['search'];
            $locales = config('app.translatable_locales', ['ko', 'en']);
            $query->where(function ($q) use ($keyword, $locales) {
                foreach ($locales as $locale) {
                    $q->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(product_name_snapshot, '$.{$locale}')) LIKE ?",
                        ["%{$keyword}%"]
                    );
                }
            });
        }

        // audit:allow repository-paginate-column-pruning reason: 사용자 1명에 종속된 내 문의 목록 —
        // where(user_id) 로 이미 좁혀져 OFFSET 이 깊어질 수 없다
        return $query->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getListWithFilters(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // 상품명 검색
        if (! empty($filters['search_keyword'])) {
            $keyword = $filters['search_keyword'];
            $locales = config('app.translatable_locales', ['ko', 'en']);
            $query->whereHas('product', function ($q) use ($keyword, $locales) {
                $q->where(function ($inner) use ($keyword, $locales) {
                    foreach ($locales as $locale) {
                        $inner->orWhere("name->{$locale}", 'like', "%{$keyword}%");
                    }
                });
            });
        }

        // 답변 여부 필터
        if (isset($filters['is_answered']) && $filters['is_answered'] !== '') {
            $query->where('is_answered', (bool) $filters['is_answered']);
        }

        // 기간 필터 — whereDate 는 컬럼에 함수를 씌워 인덱스를 무력화하므로 범위 조건으로 준다
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        // 지연 조인: 전역 문의 목록은 무제한 성장하고 content(text)가 있어
        // OFFSET 이 훑는 구간에서 넓은 컬럼까지 읽힌다. inner 는 id 만 훑는다.
        return $this->paginateWithDeferredJoin(
            query: $query,
            columns: ['*'],
            sort: [['column' => 'created_at', 'direction' => 'desc']],
            perPage: $perPage,
            relations: ['product', 'user'],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): ProductInquiry
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function markAsAnswered(ProductInquiry $inquiry): ProductInquiry
    {
        $inquiry->update([
            'is_answered' => true,
            'answered_at' => now(),
        ]);

        return $inquiry->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function unmarkAnswered(ProductInquiry $inquiry): ProductInquiry
    {
        $inquiry->update([
            'is_answered' => false,
            'answered_at' => null,
        ]);

        return $inquiry->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteById(int $id): bool
    {
        return $this->model->newQuery()->where('id', $id)->delete() > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByInquirableIds(string $inquirableType, array $inquirableIds): int
    {
        return $this->model->newQuery()
            ->where('inquirable_type', $inquirableType)
            ->whereIn('inquirable_id', $inquirableIds)
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getPendingRecent(int $limit): Collection
    {
        return $this->model->newQuery()
            ->with(['product', 'user'])
            ->where('is_answered', false)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function countPending(): int
    {
        return $this->model->newQuery()
            ->where('is_answered', false)
            ->count();
    }
}
