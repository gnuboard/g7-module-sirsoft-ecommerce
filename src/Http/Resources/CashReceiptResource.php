<?php

namespace Modules\Sirsoft\Ecommerce\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;
use Modules\Sirsoft\Ecommerce\Http\Resources\Traits\HasMultiCurrencyPrices;

/**
 * 현금영수증 이력 리소스
 *
 * 식별번호는 마스킹 값만 노출한다 — 원본 평문·암호문은 어떤 응답에도 포함되지 않는다.
 * 프로바이더 원응답(raw_response)도 노출하지 않는다.
 */
class CashReceiptResource extends BaseApiResource
{
    use HasMultiCurrencyPrices;

    /**
     * 리소스를 배열로 변환
     *
     * @param  Request  $request  요청
     * @return array 현금영수증 정보 배열
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'transaction_type' => $this->transaction_type,
            'receipt_type' => $this->receipt_type,
            'receipt_type_label' => $this->receipt_type?->label(),
            'amount' => $this->roundToOrderCurrency($this->amount),
            'amount_formatted' => $this->formatOrderCurrency($this->amount),
            'tax_free_amount' => $this->roundToOrderCurrency($this->tax_free_amount),
            'tax_free_amount_formatted' => $this->formatOrderCurrency($this->tax_free_amount),
            'identifier_masked' => $this->identifier_masked,
            'receipt_url' => $this->receipt_url,
            'issue_number' => $this->issue_number,
            'issue_status' => $this->issue_status,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            // 머신 ISO8601 — 표시는 issued_at_formatted 사용
            'issued_at' => $this->issued_at?->toIso8601String(), // audit:allow datetime-display-user-timezone reason: machine ISO8601, display uses issued_at_formatted sibling
            'issued_at_formatted' => $this->formatDateTimeStringForUser($this->issued_at),
        ];
    }
}
