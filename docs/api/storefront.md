# Storefront API 레퍼런스

> **소유**: module `sirsoft-ecommerce` · 쇼핑 첫 화면이 필요한 데이터를 한 번에 돌려주는 통합 엔드포인트입니다.

---

## TL;DR (5초 요약)

```text
1. GET /api/modules/sirsoft-ecommerce/storefront — 분류·상품목록·최근본·인기·신상품 한 번에 (`category_slug` 지정 시 그 분류의 상세까지)
2. 각 묶음의 응답 형태는 개별 엔드포인트와 완전히 동일 (조립 위치만 서버로 이동)
3. 개별 엔드포인트도 그대로 남아 다른 화면에서 계속 사용
4. products 묶음은 총 건수 정확도 메타(total_relation / total_is_exact / result_cap) 포함
5. 진열 묶음 개수를 0 으로 주면 그 묶음은 조회하지 않음
```

---

## 왜 통합 엔드포인트인가

쇼핑 첫 화면은 분류·상품 목록·최근 본·인기·신상품 다섯 묶음을 한 화면에 함께 그립니다.
이를 각각의 엔드포인트로 부르면 화면 하나에 요청이 다섯 번 나가고, 그 다섯 번이 전부 같은
부팅(라우팅·미들웨어·권한 판정·설정 적재)을 되풀이합니다.

각 묶음의 응답 형태(리소스 계약)는 개별 엔드포인트와 같습니다 — 옮긴 것은 조립 위치뿐이므로,
개별 엔드포인트를 쓰는 다른 화면은 영향을 받지 않습니다.

분류 화면도 같은 이유로 이 엔드포인트를 씁니다. 종전에는 분류 상세·분류 트리·상품 목록으로
요청이 세 번 나갔고, 그중 분류 트리는 그 화면의 어떤 표현식도 읽지 않는 낭비였습니다.
`category_slug` 를 주면 `category` 묶음에 그 분류의 상세가 함께 담겨 요청 한 번으로 끝납니다.

---

### GET /api/modules/sirsoft-ecommerce/storefront

- **라우트명**: `api.modules.sirsoft-ecommerce.storefront.index`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\StorefrontController@index`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| category_id | query | integer | 아니오 | — | 상품 목록 묶음의 카테고리 필터 (category 식별자) |
| category_slug | query | string | 아니오 | max 100 | 상품 목록 묶음의 카테고리 필터 (URL 친화 식별자). **지정하면 `category` 묶음에 그 분류의 상세가 함께 담깁니다** — 없는 분류면 404 |
| brand_id | query | integer | 아니오 | — | 상품 목록 묶음의 브랜드 필터 |
| search | query | string | 아니오 | max 200 | 상품 목록 묶음의 검색어 |
| sort | query | string | 아니오 | `latest`, `sales`, `price_asc`, `price_desc` | 상품 목록 묶음의 정렬 기준 |
| min_price | query | integer | 아니오 | min 0 | 판매가 하한 |
| max_price | query | integer | 아니오 | min 0 | 판매가 상한 |
| per_page | query | integer | 아니오 | min 1, max 100 | 상품 목록 묶음의 페이지당 항목 수 |
| page | query | integer | 아니오 | min 1 | 상품 목록 묶음의 페이지 번호 |
| popular_limit | query | integer | 아니오 | min 0, max 50 | 인기 상품 개수 (0 이면 조회하지 않음, 미지정 시 8) |
| new_limit | query | integer | 아니오 | min 0, max 50 | 신상품 개수 (0 이면 조회하지 않음, 미지정 시 8) |
| recent_ids | query | string | 아니오 | max 500 | 최근 본 상품 ID 목록 (쉼표 구분, 비면 조회하지 않음) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.public_storefront_validation_rules`, `sirsoft-ecommerce.product.public_storefront_validation_messages`).

**요청 예시**

```http
GET /api/modules/sirsoft-ecommerce/storefront?page=1&per_page=12&sort=latest&popular_limit=8&new_limit=8&recent_ids=12%2C34 HTTP/1.1
Host: api.example.com
Accept: application/json
Authorization: Bearer {YOUR_TOKEN}   (optional.sanctum: 비회원은 헤더 생략 가능)
```

**응답 필드** (`data` 내부)

| 필드 | 타입 | 용도/설명 |
| --- | --- | --- |
| category | object\|null | 지금 보고 있는 분류의 상세. `GET /categories/{slug}` 의 `data` 와 동일한 형태. `category_slug` 를 주지 않으면 `null` |
| categories | array | 공개 카테고리 트리. `GET /categories` 의 `data` 와 동일한 형태 |
| products | object | 상품 목록. `GET /products` 의 `data` 와 동일한 형태 (`data[]` + `pagination`) |
| recent_products | array | 최근 본 상품. `GET /products/recent` 의 `data` 와 동일한 형태 (`recent_ids` 가 비면 `[]`) |
| popular_products | array | 인기 상품. `GET /products/popular` 의 `data` 와 동일한 형태 (`popular_limit=0` 이면 `[]`) |
| new_products | array | 신상품. `GET /products/new` 의 `data` 와 동일한 형태 (`new_limit=0` 이면 `[]`) |

`products.pagination` 은 표준 페이지네이션 메타에 총 건수 정확도가 더해진 형태입니다.

| 필드 | 타입 | 용도/설명 |
| --- | --- | --- |
| current_page | integer | 현재 페이지 번호 |
| per_page | integer | 페이지당 항목 수 |
| from / to | integer\|null | 현재 페이지의 첫/마지막 행 번호 |
| has_more_pages | boolean | 다음 페이지 존재 여부 (총 건수를 몰라도 정확) |
| total | integer | 총 건수 (상한 초과 시 상한값 = 하한 보증) |
| last_page | integer\|null | 마지막 페이지 번호. **총 건수가 부정확하면 `null`** |
| total_relation | string | `exact` 또는 `at_least` |
| total_is_exact | boolean | 총 건수가 정확한지 여부 |
| result_cap | integer\|null | 총 건수 집계에 적용된 상한 (무제한이면 `null`) |

> 총 건수 상한과 페이지 이동의 관계는 [pagination.md](../../../../../docs/backend/pagination.md) 참조.

**응답 예시**

```http
HTTP/1.1 200
```

```json
{
  "success": true,
  "message": "상품 목록을 조회했습니다.",
  "data": {
    "category": null,
    "categories": [],
    "products": {
      "data": [],
      "pagination": {
        "current_page": 1,
        "per_page": 12,
        "from": null,
        "to": null,
        "has_more_pages": false,
        "total": 0,
        "last_page": 1,
        "total_relation": "exact",
        "total_is_exact": true,
        "result_cap": 10000
      }
    },
    "recent_products": [],
    "popular_products": [],
    "new_products": []
  }
}
```

**오류 응답**

| 상태 | 상황 |
| --- | --- |
| 404 | `category_slug` 가 가리키는 분류가 없거나 비활성 (200 + 빈 목록으로 내보내면 검색엔진이 없는 주소를 정상 페이지로 수집합니다) |
| 401 | 비회원에게 `sirsoft-ecommerce.user-products.read` 권한이 없음 |
| 403 | 인증 사용자에게 해당 권한이 없음 |
| 422 | 파라미터 검증 실패 (범위를 벗어난 `popular_limit` 등) |
| 500 | 조회 중 예외 — 응답 형태는 개별 상품 엔드포인트와 동일 |
