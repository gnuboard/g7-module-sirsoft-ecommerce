<?php

return [
    /*
     * 발급 용도
     */
    'type' => [
        'income' => '소득공제용',
        'expense' => '지출증빙용',
    ],

    /*
     * 식별번호 종류
     */
    'identifier_type' => [
        'phone' => '휴대폰번호',
        'card' => '현금영수증카드번호',
        'business' => '사업자등록번호',
    ],

    /*
     * 이력 거래 유형
     */
    'transaction_type' => [
        'issue' => '발급',
        'cancel' => '취소',
    ],

    /*
     * 발급 상태
     */
    'issue_status' => [
        'in_progress' => '처리 중',
        'completed' => '발급 완료',
        'failed' => '발급 실패',
    ],

    /*
     * 배송비 과세 정책
     */
    'shipping_fee_tax_policy' => [
        'proportional' => '안분 (과세상품 비율만큼 과세)',
        'taxable' => '전액 과세',
        'follow_main_item' => '주된 재화를 따름',
    ],

    /*
     * 오류 메시지
     */
    'errors' => [
        'provider_not_configured' => '현금영수증 발급 프로바이더가 설정되지 않았습니다.',
        'no_provider_handled' => '현금영수증 발급 요청을 처리한 프로바이더가 없습니다.',
        'no_issuable_amount' => '발급 가능한 현금성 금액이 없습니다.',
        'identifier_unavailable' => '재발급에 필요한 식별번호를 복호화할 수 없습니다. 관리자가 식별번호를 다시 입력해 발급해야 합니다.',
    ],
];
