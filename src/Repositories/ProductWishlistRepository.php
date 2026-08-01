<?php

namespace Modules\Sirsoft\Ecommerce\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Sirsoft\Ecommerce\Models\ProductWishlist;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\ProductWishlistRepositoryInterface;

/**
 * 상품 찜 Repository 구현체
 */
class ProductWishlistRepository implements ProductWishlistRepositoryInterface
{
    public function __construct(
        protected ProductWishlist $model
    ) {}

    /**
     * {@inheritDoc}
     */
    public function toggle(int $userId, int $productId): array
    {
        $existing = $this->model
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();

            return ['added' => false, 'wishlist' => null];
        }

        $wishlist = $this->model->create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return ['added' => true, 'wishlist' => $wishlist];
    }

    /**
     * {@inheritDoc}
     */
    public function isWishlisted(int $userId, int $productId): bool
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function getByUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            // 상품이 소프트 삭제되면 eager load 결과가 null 이 되어 카드가 렌더 실패한다.
            // 목록에서 아예 제외해 "빈 셀 0" 을 성립시킨다 (찜 행 자체는 보존).
            ->whereHas('product')
            ->with(['product.brand', 'product.categories', 'product.activeLabelAssignments.label'])
            ->orderByDesc('created_at')
            // 전순서 보장 — 한 번에 여러 건을 담았을 때의 created_at 동률 대비
            ->orderByDesc('id')
            // audit:allow repository-paginate-column-pruning reason: 사용자 1명에 종속된 찜 목록 —
            // where(user_id) 로 이미 좁혀지고, 피벗 성격의 테이블이라 넓은 컬럼이 없다
            ->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        return (bool) $this->model
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function getWishlistedProductIds(int $userId, array $productIds): array
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->all();
    }
}
