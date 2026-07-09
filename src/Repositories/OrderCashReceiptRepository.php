<?php

namespace Modules\Sirsoft\Ecommerce\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Sirsoft\Ecommerce\Enums\CashReceiptIssueStatus;
use Modules\Sirsoft\Ecommerce\Enums\CashReceiptTransactionType;
use Modules\Sirsoft\Ecommerce\Models\Order;
use Modules\Sirsoft\Ecommerce\Models\OrderCashReceipt;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\OrderCashReceiptRepositoryInterface;

/**
 * 주문 현금영수증 이력 Repository 구현체
 */
class OrderCashReceiptRepository implements OrderCashReceiptRepositoryInterface
{
    public function __construct(
        protected OrderCashReceipt $model
    ) {}

    /**
     * {@inheritDoc}
     */
    public function create(array $data): OrderCashReceipt
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function findByOrder(Order $order): Collection
    {
        return $this->model->newQuery()
            ->where('order_id', $order->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * {@inheritDoc}
     */
    public function findActiveReceipt(Order $order): ?OrderCashReceipt
    {
        return $this->findActiveReceipts($order)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function findActiveReceipts(Order $order): Collection
    {
        $rows = $this->model->newQuery()
            ->where('order_id', $order->id)
            ->whereIn('transaction_type', [
                CashReceiptTransactionType::ISSUE->value,
                CashReceiptTransactionType::CANCEL->value,
            ])
            ->where('issue_status', CashReceiptIssueStatus::COMPLETED->value)
            ->orderByDesc('id')
            ->get();

        // 취소 완료된 receipt_key 집합 — 같은 키의 발급 이력은 더 이상 활성이 아니다.
        $cancelledKeys = $rows
            ->filter(fn (OrderCashReceipt $row) => $row->transaction_type === CashReceiptTransactionType::CANCEL)
            ->pluck('receipt_key')
            ->filter()
            ->unique()
            ->all();

        $active = $rows->filter(function (OrderCashReceipt $row) use ($cancelledKeys) {
            if ($row->transaction_type !== CashReceiptTransactionType::ISSUE) {
                return false;
            }

            // receipt_key 가 없는 발급 완료 건은 취소 대상을 특정할 수 없다 — 활성으로 보지 않는다.
            if ($row->receipt_key === null || $row->receipt_key === '') {
                return false;
            }

            return ! in_array($row->receipt_key, $cancelledKeys, true);
        });

        return $this->model->newCollection($active->values()->all());
    }

    /**
     * {@inheritDoc}
     */
    public function findByReceiptKey(string $receiptKey): ?OrderCashReceipt
    {
        return $this->model->newQuery()
            ->where('receipt_key', $receiptKey)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * {@inheritDoc}
     */
    public function countIssues(Order $order): int
    {
        return $this->model->newQuery()
            ->where('order_id', $order->id)
            ->where('transaction_type', CashReceiptTransactionType::ISSUE->value)
            ->count();
    }
}
