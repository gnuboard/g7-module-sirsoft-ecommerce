<?php

namespace Modules\Sirsoft\Ecommerce\Repositories;

use Modules\Sirsoft\Ecommerce\Enums\CashReceiptIdentifierType;
use Modules\Sirsoft\Ecommerce\Enums\CashReceiptType;
use Modules\Sirsoft\Ecommerce\Models\OrderCashReceipt;
use Modules\Sirsoft\Ecommerce\Models\OrderPayment;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\OrderPaymentRepositoryInterface;

/**
 * 주문 결제 Repository 구현체
 */
class OrderPaymentRepository implements OrderPaymentRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function markCashReceiptIssued(
        OrderPayment $payment,
        OrderCashReceipt $receipt,
        CashReceiptType $type,
        string $identifier,
        string $identifierMasked,
        ?CashReceiptIdentifierType $identifierType,
    ): OrderPayment {
        $attributes = [
            'is_cash_receipt_issued' => true,
            'cash_receipt_type' => $type->value,
            'cash_receipt_identifier' => $identifierMasked,
            'cash_receipt_identifier_type' => $identifierType,
            'cash_receipt_identifier_encrypted' => $identifier,
            'cash_receipt_issued_at' => $receipt->issued_at,
        ];

        if ($receipt->receipt_url !== null) {
            $attributes['receipt_url'] = $receipt->receipt_url;
        }

        $payment->update($attributes);

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function markCashReceiptCancelled(OrderPayment $payment): OrderPayment
    {
        $payment->update([
            'is_cash_receipt_issued' => false,
            'cash_receipt_issued_at' => null,
        ]);

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function purgeCashReceiptIdentifier(OrderPayment $payment): OrderPayment
    {
        if (! $this->hasCashReceiptIdentifier($payment)) {
            return $payment;
        }

        $payment->update(['cash_receipt_identifier_encrypted' => null]);

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveCashReceiptIdentifier(OrderPayment $payment): ?string
    {
        try {
            $identifier = $payment->cash_receipt_identifier_encrypted;
        } catch (\Throwable $e) {
            // APP_KEY 로테이션 등으로 복호화 실패
            return null;
        }

        return is_string($identifier) && $identifier !== '' ? $identifier : null;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCashReceiptIdentifier(OrderPayment $payment): bool
    {
        return $payment->getRawOriginal('cash_receipt_identifier_encrypted') !== null;
    }
}
