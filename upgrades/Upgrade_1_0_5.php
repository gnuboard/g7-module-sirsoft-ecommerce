<?php

namespace Modules\Sirsoft\Ecommerce\Upgrades;

use App\Extension\AbstractUpgradeStep;

/**
 * Ecommerce 모듈 1.0.5 업그레이드 스텝
 *
 * 카테고리 계층의 순환 참조를 절단하고 path 를 전면 재구축한다.
 *
 * 순환이 남아 있으면 카테고리 수정·순서 변경 요청이 path 재계산 재귀에서 종료되지 않아
 * 실패하고, 해당 노드가 트리에서 사라져 관리자 화면으로 복구할 수 없다.
 *
 * 모든 비즈니스 로직은 data/1.0.5/migrations/ 로 격리(AbstractUpgradeStep 규약).
 *
 * @upgrade-path A
 */
class Upgrade_1_0_5 extends AbstractUpgradeStep {}
