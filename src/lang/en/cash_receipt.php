<?php

return [
    /*
     * 발급 용도
     */
    'type' => [
        'income' => 'Income Deduction',
        'expense' => 'Expenditure Proof',
    ],

    /*
     * 식별번호 종류
     */
    'identifier_type' => [
        'phone' => 'Mobile Number',
        'card' => 'Cash Receipt Card Number',
        'business' => 'Business Registration Number',
    ],

    /*
     * 이력 거래 유형
     */
    'transaction_type' => [
        'issue' => 'Issue',
        'cancel' => 'Cancel',
    ],

    /*
     * 발급 상태
     */
    'issue_status' => [
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ],

    /*
     * 배송비 과세 정책
     */
    'shipping_fee_tax_policy' => [
        'proportional' => 'Proportional (by taxable item ratio)',
        'taxable' => 'Fully Taxable',
        'follow_main_item' => 'Follow Main Item',
    ],

    /*
     * 오류 메시지
     */
    'errors' => [
        'provider_not_configured' => 'No cash receipt provider is configured.',
        'no_provider_handled' => 'No provider handled the cash receipt request.',
        'no_issuable_amount' => 'There is no issuable cash-equivalent amount.',
        'identifier_unavailable' => 'The identifier required for re-issuance could not be decrypted. An administrator must re-enter the identifier to issue the receipt.',
    ],
];
