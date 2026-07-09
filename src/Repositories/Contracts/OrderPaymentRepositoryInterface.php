<?php

namespace Modules\Sirsoft\Ecommerce\Repositories\Contracts;

use Modules\Sirsoft\Ecommerce\Enums\CashReceiptIdentifierType;
use Modules\Sirsoft\Ecommerce\Enums\CashReceiptType;
use Modules\Sirsoft\Ecommerce\Models\OrderCashReceipt;
use Modules\Sirsoft\Ecommerce\Models\OrderPayment;

/**
 * 주문 결제 Repository 인터페이스
 */
interface OrderPaymentRepositoryInterface
{
    /**
     * 현금영수증 발급 성공 시 결제의 요약 컬럼을 갱신합니다.
     *
     * 원본 식별번호는 encrypted cast 로 암호화되어 저장되고, 표시용으로는 마스킹 값만 남는다.
     *
     * @param  OrderPayment  $payment  결제 모델
     * @param  OrderCashReceipt  $receipt  발급 이력
     * @param  CashReceiptType  $type  발급 용도
     * @param  string  $identifier  식별번호 원본
     * @param  string  $identifierMasked  마스킹된 식별번호
     * @param  CashReceiptIdentifierType|null  $identifierType  식별번호 종류
     * @return OrderPayment 갱신된 결제 모델
     */
    public function markCashReceiptIssued(
        OrderPayment $payment,
        OrderCashReceipt $receipt,
        CashReceiptType $type,
        string $identifier,
        string $identifierMasked,
        ?CashReceiptIdentifierType $identifierType,
    ): OrderPayment;

    /**
     * 현금영수증 전액취소 성공 시 결제의 요약 컬럼을 초기화합니다.
     *
     * @param  OrderPayment  $payment  결제 모델
     * @return OrderPayment 갱신된 결제 모델
     */
    public function markCashReceiptCancelled(OrderPayment $payment): OrderPayment;

    /**
     * 재발급용 식별번호 암호문을 폐기합니다. (구매확정 시점)
     *
     * 이력·영수증 URL·마스킹 값은 유지된다.
     *
     * @param  OrderPayment  $payment  결제 모델
     * @return OrderPayment 갱신된 결제 모델
     */
    public function purgeCashReceiptIdentifier(OrderPayment $payment): OrderPayment;

    /**
     * 암호화된 식별번호 원본을 복호화해 반환합니다.
     *
     * APP_KEY 로테이션 등으로 복호화가 실패하면 null 을 반환한다.
     *
     * @param  OrderPayment  $payment  결제 모델
     * @return string|null 식별번호 원본 (부재/복호화 실패 시 null)
     */
    public function resolveCashReceiptIdentifier(OrderPayment $payment): ?string;

    /**
     * 식별번호 암호문이 저장되어 있는지 확인합니다.
     *
     * @param  OrderPayment  $payment  결제 모델
     * @return bool 암호문 존재 여부
     */
    public function hasCashReceiptIdentifier(OrderPayment $payment): bool;
}
