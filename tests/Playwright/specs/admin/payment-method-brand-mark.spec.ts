/**
 * 주문설정 결제수단 — PG 플러그인 브랜드 마크 주입 회귀.
 *
 * 배경: 관리자 레이아웃은 아이콘 열을 `Icon name={{$method._cached_icon}}` 하나로만 그리고
 * `_cached_brand_mark` 를 읽는 노드가 없다. 각 PG 플러그인이 자기 결제수단 행의 아이콘을
 * 브랜드 배지로 치환하는 관리자 전용 injector 를 직접 싣는 구조다.
 *
 * 이 spec 이 고정하는 두 가지:
 *  1. 설치된 PG 플러그인의 간편결제 행에 브랜드 배지가 주입된다 (플러그인별 data 속성으로 판정).
 *  2. **다른 탭에 갔다 돌아와도** 배지가 재주입된다. 탭 전환은 pushState 가 아니라
 *     replaceState 로 URL 만 바꾸므로, pushState/popstate 만 후킹하던 구현은 복귀 후
 *     배지가 사라진 채로 남았다(행은 정상 렌더되어 화면상 아이콘만 회색으로 되돌아감).
 *
 * 측정 규율:
 *  - 결제수단 ID·PG명을 하드코딩하지 않는다. 설치 구성은 환경마다 다르므로 브랜드 배지
 *    data 속성을 **런타임 카운트**로 판정하고, 배지가 0인 환경(간편결제 플러그인 미설치)은
 *    skip 이 아니라 "행이 렌더됐는지" 를 먼저 확인해 거짓 통과를 만들지 않는다.
 *
 * @scenario extension_payment_method method_kind=extension × capability=brand_mark
 * @effects admin_shows_brand_mark, admin_rebinds_brand_mark_after_tab_return
 */
import { test, expect, authenticatePage } from '../../fixtures/ecommerce-auth';
import type { Page } from '@playwright/test';

const ORDER_SETTINGS_URL = '/admin/ecommerce/settings?tab=order_settings';

/** 설치된 모든 PG 플러그인의 브랜드 배지 총합 + 렌더된 결제수단 행 수. */
async function counts(page: Page): Promise<{ badges: number; rows: number }> {
  return page.evaluate(() => ({
    badges: document.querySelectorAll('[data-testid], span').length
      && document.querySelectorAll('span[class*="rounded-lg"][aria-hidden="true"]').length,
    rows: document.querySelectorAll('[data-testid^="pg-locked-badge-"]').length,
  }));
}

/** 플러그인별 브랜드 배지 카운트 (data 속성 접미사로 집계 — 플러그인명 하드코딩 회피). */
async function badgeCountsByPlugin(page: Page): Promise<Record<string, number>> {
  return page.evaluate(() => {
    const out: Record<string, number> = {};
    for (const el of document.querySelectorAll<HTMLElement>('span[aria-hidden="true"]')) {
      for (const name of Object.keys(el.dataset)) {
        if (!/AdminPaymentBrandMark$/.test(name)) continue;
        out[name] = (out[name] ?? 0) + 1;
      }
    }
    return out;
  });
}

/** 설정 탭을 라벨 텍스트로 클릭한다 (탭은 role/accessible-name 이 라벨과 일치하지 않는다). */
async function clickTab(page: Page, label: string): Promise<void> {
  const clicked = await page.evaluate((text) => {
    const el = [...document.querySelectorAll('button, a')].find((e) => e.textContent?.trim() === text);
    if (!el) return false;
    (el as HTMLElement).click();
    return true;
  }, label);
  if (!clicked) throw new Error(`탭을 찾지 못했다: ${label}`);
}

async function gotoOrderSettings(page: Page, token: string): Promise<void> {
  await authenticatePage(page, token);
  await page.setViewportSize({ width: 1440, height: 1000 });
  await page.goto(ORDER_SETTINGS_URL);
  await page.waitForLoadState('domcontentloaded', { timeout: 30_000 });
  await expect.poll(async () => (await counts(page)).rows, { timeout: 30_000 }).toBeGreaterThan(0);
}

test.describe('@sirsoft-ecommerce 주문설정 결제수단 — 브랜드 마크', () => {
  test('PG 플러그인의 간편결제 행에 브랜드 배지가 주입된다', async ({ page, settingsToken }) => {
    await gotoOrderSettings(page, settingsToken);

    await expect
      .poll(async () => Object.values(await badgeCountsByPlugin(page)).reduce((a, b) => a + b, 0), { timeout: 20_000 })
      .toBeGreaterThan(0);

    const byPlugin = await badgeCountsByPlugin(page);
    // 간편결제를 제공하는 PG 플러그인이 하나라도 설치돼 있으면 그 플러그인 배지가 잡혀야 한다.
    expect(Object.keys(byPlugin).length, `브랜드 배지를 주입한 플러그인이 없다: ${JSON.stringify(byPlugin)}`)
      .toBeGreaterThan(0);
  });

  test('다른 탭에 갔다 돌아와도 브랜드 배지가 재주입된다 (replaceState 회귀)', async ({ page, settingsToken }) => {
    await gotoOrderSettings(page, settingsToken);

    await expect
      .poll(async () => Object.values(await badgeCountsByPlugin(page)).reduce((a, b) => a + b, 0), { timeout: 20_000 })
      .toBeGreaterThan(0);
    const before = await badgeCountsByPlugin(page);

    // 탭 전환은 replaceState 로 URL 만 바꾼다 — 화면 전환을 실제 클릭으로 재현한다.
    // 탭은 접근성 이름이 라벨과 정확히 일치하지 않으므로(내부 텍스트 노드 구성) 텍스트로 찾는다.
    await clickTab(page, '배송설정');
    await expect.poll(async () => (await counts(page)).rows, { timeout: 20_000 }).toBe(0);

    await clickTab(page, '주문설정');
    await expect.poll(async () => (await counts(page)).rows, { timeout: 20_000 }).toBeGreaterThan(0);

    // 복귀 후 배지가 수정 전처럼 0 으로 남지 않아야 한다.
    await expect
      .poll(async () => Object.values(await badgeCountsByPlugin(page)).reduce((a, b) => a + b, 0), { timeout: 20_000 })
      .toBeGreaterThan(0);

    const after = await badgeCountsByPlugin(page);
    expect(after, `탭 복귀 후 배지가 복원되지 않았다 (before=${JSON.stringify(before)} after=${JSON.stringify(after)})`)
      .toEqual(before);
  });
});
