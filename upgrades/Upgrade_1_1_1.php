<?php

namespace Modules\Sirsoft\Ecommerce\Upgrades;

use App\Extension\AbstractUpgradeStep;

/**
 * Ecommerce 모듈 1.1.1 업그레이드 스텝
 *
 * 공개 #94 (배송비 구간 경계·단위) 후속 데이터 정리.
 *
 * 1. 연속형 구간(금액/무게/부피/부피무게)의 시작값을 직전 구간 종료값으로 정규화한다.
 *    구(舊) 규칙(다음 min = max + 1)으로 저장된 정책은 계산에는 영향이 없지만
 *    (사다리 매칭은 시작값을 쓰지 않는다) 관리자 화면 요약이 "10001~30000" 처럼 표시된다.
 * 2. 총 무게/부피가 0 으로 고정 기록된 기존 주문을 주문 옵션 소계 합으로 백필한다.
 *
 * 모든 비즈니스 로직은 data/1.1.1/migrations/ 로 격리(AbstractUpgradeStep 규약).
 *
 * @upgrade-path B
 */
class Upgrade_1_1_1 extends AbstractUpgradeStep {}
