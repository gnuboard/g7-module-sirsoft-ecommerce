<?php

namespace Modules\Sirsoft\Ecommerce\Upgrades;

use App\Extension\AbstractUpgradeStep;

/**
 * Ecommerce 모듈 1.0.5 업그레이드 스텝
 *
 * 1. 카테고리 계층의 순환 참조를 절단하고 path 를 전면 재구축한다.
 *    순환이 남아 있으면 카테고리 수정·순서 변경 요청이 path 재계산 재귀에서 종료되지 않아
 *    실패하고, 해당 노드가 트리에서 사라져 관리자 화면으로 복구할 수 없다.
 * 2. 주문 부가세(total_vat_amount)를 백필한다.
 *    쓰기 지점이 부분취소 재계산 1곳뿐이라 부분취소를 겪은 주문만 값이 정상화되고
 *    나머지는 영구 0 이었다. 0 인 주문만 채우고 이미 정상화된 값은 건드리지 않는다.
 *
 * 함께 목록 조회 성능용 인덱스를 기존 사이트에 반영한다 — 상품 리뷰 정렬 인덱스(02)와
 * 주문 목록 "발송일" 정렬용 복합 인덱스(04). 마이그레이션만 추가하면 신규 설치에만
 * 반영되므로 업그레이드 시점에 동일 인덱스를 적용한다. 데이터가 많은 쇼핑몰에서는
 * ALTER TABLE 이 수 분 걸리며 그동안 해당 테이블 쓰기가 대기한다.
 *
 * 모든 비즈니스 로직은 data/1.0.5/migrations/ 로 격리(AbstractUpgradeStep 규약).
 *
 * @upgrade-path A
 */
class Upgrade_1_0_5 extends AbstractUpgradeStep {}
