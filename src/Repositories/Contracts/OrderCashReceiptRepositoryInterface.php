<?php

namespace Modules\Sirsoft\Ecommerce\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Sirsoft\Ecommerce\Models\Order;
use Modules\Sirsoft\Ecommerce\Models\OrderCashReceipt;

/**
 * 주문 현금영수증 이력 Repository 인터페이스
 */
interface OrderCashReceiptRepositoryInterface
{
    /**
     * 이력을 생성합니다.
     *
     * @param  array<string, mixed>  $data  이력 데이터
     * @return OrderCashReceipt 생성된 이력
     */
    public function create(array $data): OrderCashReceipt;

    /**
     * 주문의 모든 이력을 최신순으로 조회합니다.
     *
     * @param  Order  $order  주문 모델
     * @return Collection<int, OrderCashReceipt> 이력 컬렉션
     */
    public function findByOrder(Order $order): Collection;

    /**
     * 주문의 활성 영수증(가장 최근 발급 완료 건 중 이후 취소되지 않은 것)을 조회합니다.
     *
     * @param  Order  $order  주문 모델
     * @return OrderCashReceipt|null 활성 영수증 (없으면 null)
     */
    public function findActiveReceipt(Order $order): ?OrderCashReceipt;

    /**
     * 주문의 활성 영수증 전체를 조회합니다.
     *
     * 정상 흐름에서는 항상 0~1건이지만, 발급 실패·중복 발급 등 이상 상태에서 2건 이상이
     * 남을 수 있으므로 전액취소 시에는 이 목록 전체를 대상으로 한다.
     *
     * @param  Order  $order  주문 모델
     * @return Collection<int, OrderCashReceipt> 활성 영수증 컬렉션
     */
    public function findActiveReceipts(Order $order): Collection;

    /**
     * 영수증 키로 이력을 조회합니다.
     *
     * @param  string  $receiptKey  프로바이더 영수증 키
     * @return OrderCashReceipt|null 이력 (없으면 null)
     */
    public function findByReceiptKey(string $receiptKey): ?OrderCashReceipt;
}
