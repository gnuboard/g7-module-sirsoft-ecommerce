# Products API 레퍼런스

> **소유**: module `sirsoft-ecommerce` · **생성**: `php artisan api:docgen` (실측 기반). @generated 블록은 재생성 시 갱신되며, 사람이 작성한 설명은 보존됩니다.

---

## TL;DR (5초 요약)

```text
1. 이 문서는 실제 API 호출로 실측한 Products 엔드포인트 레퍼런스입니다
2. 각 엔드포인트: 메서드/URI/권한 + 요청 파라미터 표 + 실측 응답 필드 표
3. 응답 필드의 예시값은 실제 호출 응답에서 관측된 값입니다
4. 갱신: 코드 변경 후 php artisan api:docgen 재실행
5. 설명(TODO) 칸은 사람이 채웁니다
```

---


### GET /api/modules/sirsoft-ecommerce/admin/products
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.index -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.index`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@index`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| search_field | query | string | 아니오 | `all`, `name`, `product_code`, `sku`, `barcode` | 검색 대상 필드명 (검색어를 적용할 컬럼) |
| search_keyword | query | string | 아니오 | max 200 | 검색 키워드 (부분 일치) |
| category_id | query | integer | 아니오 | — | category 식별자 |
| no_category | query | boolean | 아니오 | — | 카테고리 미지정 상품만 필터 (true 시 어떤 카테고리에도 속하지 않은 상품 조회) |
| date_type | query | string | 아니오 | — | 기간 필터 기준 날짜 컬럼 (created_at 등록일 / updated_at 수정일) |
| start_date | query | date | 아니오 | — | 조회 기간 시작일 (이 날짜 이후 데이터) |
| end_date | query | date | 아니오 | — | 조회 기간 종료일 (이 날짜 이전 데이터) |
| sales_status | query | array | 아니오 | — | 판매상태 다중 필터 (on_sale/suspended/sold_out/coming_soon 값 배열, 해당 상태만 조회) |
| display_status | query | string | 아니오 | — | 전시상태 필터 (visible 전시 / hidden 숨김) |
| brand_id | query | integer | 아니오 | — | brand 식별자 |
| no_brand | query | boolean | 아니오 | — | 브랜드 미지정 상품만 필터 (true 시 브랜드가 없는 상품 조회) |
| tax_status | query | string | 아니오 | — | 과세여부 필터 (taxable 과세 / tax_free 면세) |
| price_type | query | string | 아니오 | — | 가격 범위 필터의 기준 가격 종류 (selling_price 판매가 / supply_price 공급가 / list_price 정가) |
| min_price | query | integer | 아니오 | min 0 | 가격 범위 필터 하한 (price_type 기준 이 값 이상) |
| max_price | query | integer | 아니오 | min 0 | 가격 범위 필터 상한 (price_type 기준 이 값 이하) |
| min_stock | query | integer | 아니오 | — | 재고 범위 필터 하한 (재고 수량이 이 값 이상) |
| max_stock | query | integer | 아니오 | — | 재고 범위 필터 상한 (재고 수량이 이 값 이하) |
| shipping_policy_id | query | integer | 아니오 | — | shipping policy 식별자 |
| sort_by | query | string | 아니오 | `created_at`, `updated_at`, `selling_price`, `stock_quantity`, `name` | 정렬 기준 필드명 |
| sort_order | query | string | 아니오 | `asc`, `desc` | 정렬 방향 (asc 오름차순 / desc 내림차순) |
| per_page | query | integer | 아니오 | min 10, max 100 | 페이지당 항목 수 |
| page | query | integer | 아니오 | min 1 | 조회할 페이지 번호 (1부터 시작) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.list_validation_rules`, `sirsoft-ecommerce.product.list_validation_messages`).

**응답 필드** (`data` 내부)

_목록 응답: `data.data[]` 배열 항목의 필드 + `data.pagination`._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| number | integer | `115` | 목록에서의 순번 (페이지네이션 반영 행 번호 — HasRowNumber 파생) |
| id | integer | `322` | 기본 키 (내부 식별자) |
| name | object | `{"ko":"eum et quia","en":"tenetur id quae"}` | 대상의 이름/명칭 (다국어 필드는 로케일별 값 객체) |
| name_localized | string | `eum et quia` | `name` 의 현재 로케일 해석 값 (다국어 필드를 표시용 문자열로 해석) |
| product_code | string | `PROD-GJUX-1484` | 상품코드 (상품 고유 관리 식별자) |
| sku | string | `SKU-MRAD-9306` | 재고관리코드(SKU) |
| thumbnail_url | string | `/api/modules/sirsoft-ecommerce/produc…` | thumbnail URL |
| list_price | integer | `112594` | 정가 (기본통화 자릿수로 정규화된 값) |
| list_price_formatted | string | `112,594원` | `list_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| selling_price | integer | `88949` | 판매가 (기본통화 자릿수로 정규화된 값) |
| selling_price_formatted | string | `88,949원` | `selling_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| discount_rate | integer | `21` | 할인율(%) (정가 대비 판매가 할인 비율, (1 - 판매가/정가) × 100) |
| multi_currency_list_price | object | `{"KRW":{"price":112594,"formatted":"112,594원","is_default…` | 통화별 정가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 정가) |
| multi_currency_selling_price | object | `{"KRW":{"price":88949,"formatted":"88,949원","is_default":…` | 통화별 판매가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 판매가) |
| stock_quantity | integer | `22` | 재고 수량 (옵션 사용 시 옵션 재고 합계) |
| safe_stock_quantity | integer | `12` | 안전재고 수량 (이 값 미만이면 재고 부족으로 표시) |
| is_below_safe_stock | boolean | `false` | below safe stock 여부 |
| option_stock_sum | integer | `51` | 활성 옵션의 재고 합계 (is_active 옵션들의 stock_quantity 총합) |
| sales_status | string | `on_sale` | 판매상태 값 (on_sale 판매중 / suspended 판매중지 / sold_out 품절 / coming_soon 출시예정) |
| sales_status_label | string | `판매중` | `sales_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| sales_status_variant | string | `success` | `sales_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| display_status | string | `visible` | 전시상태 값 (visible 전시 / hidden 숨김) |
| display_status_label | string | `전시` | `display_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| display_status_variant | string | `success` | `display_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| categories | array | `[]` | 소속 카테고리 목록 (각 항목: id·현지화 이름·대표 여부. categories 관계 eager load 시에만 채워짐) |
| primary_category | string | `바지` | 대표 카테고리명 (is_primary 카테고리의 현지화 이름) |
| categories_with_path | array | `[]` | 소속 카테고리 목록 + 경로 (각 항목: id·breadcrumb path·대표 여부) |
| brand_name | string | `ASUS` | 브랜드명 (연관 브랜드의 현지화 이름) |
| shipping_policy_id | integer | `31` | shipping policy 식별자 (연관 리소스 참조) |
| shipping_policy_name | string | `국내 무료배송` | 배송정책명 (연관 배송정책의 현지화 이름) |
| min_purchase_qty | integer | `1` | 최소 구매 수량 (1회 주문 시 이 수량 이상 구매) |
| max_purchase_qty | integer | `0` | 최대 구매 수량 (0=무제한) |
| has_options | boolean | `false` | options 여부 |
| options_count | integer | `1` | options 개수 (집계) |
| options | array | `[{"id":1597,"option_code":"OPT-JLNF-2511","option_values"…` | 활성 옵션(SKU) 목록 (각 옵션의 코드·옵션값·가격·재고 등, ProductOptionResource) |
| review_count | integer | `0` | review 개수 (집계) |
| rating_avg | integer | `0` | 평균 별점 (공개 리뷰 별점 평균, 소수 1자리 반올림) |
| created_at | string | `2026-07-07 14:47:31` | 생성 일시 |
| updated_at | string | `2026-07-07 14:47:31` | 최종 수정 일시 |
| is_owner | boolean | `false` | 현재 인증 사용자가 이 리소스의 소유자인지 여부 (BaseApiResource 표준 메타) |
| abilities | object | `{"can_update":true,"can_delete":true}` | 현재 사용자가 이 리소스에 수행 가능한 작업 불리언 맵 (can_update, can_delete 등 — 권한 맵 기반) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.read`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 관리자 상품 목록을 페이지네이션으로 조회합니다. `auth:sanctum` + `sirsoft-ecommerce.products.read` 권한이 필요하며, `ProductService::getList()`가 검색어/카테고리/판매·전시상태/가격·재고 범위 등 다양한 필터를 적용해 목록을 반환하고 `getStatistics()`로 집계 통계를 함께 제공합니다. 응답은 `ProductCollection`으로 감싸져 `withStatistics()`로 통계가 병합됩니다. 확장은 `sirsoft-ecommerce.product.list_validation_rules` 훅으로 추가 필터 파라미터를 주입할 수 있습니다.


### POST /api/modules/sirsoft-ecommerce/admin/products
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.store -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.store`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@store`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.create`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| name | body | array | 예 | — | 대상의 이름/명칭 |
| product_code | body | string | 예 | max 50 | 상품코드 (상품 고유 관리 식별자, 상품 간 중복 불가) |
| sales_product_code | body | string | 아니오 | max 50 | 판매자 상품코드 (판매자가 직접 입력하는 관리용 코드) |
| sku | body | string | 아니오 | max 100 | 재고관리코드(SKU) |
| category_ids | body | array | 예 | min 1, max 5 | category 식별자 배열 |
| primary_category_id | body | integer | 아니오 | — | primary category 식별자 |
| brand_id | body | integer | 아니오 | — | brand 식별자 |
| list_price | body | integer | 예 | min 0.01 | 정가 (기본통화 기준, 소수 통화는 소수 입력 허용) |
| selling_price | body | integer | 예 | min 0.01 | 판매가 (기본통화 기준, 정가 이하여야 함) |
| stock_quantity | body | integer | 예 | min 0 | 재고 수량 (옵션 사용 시 옵션 재고 합계로 관리) |
| safe_stock_quantity | body | integer | 아니오 | min 0 | 안전재고 수량 (이 값 미만이면 재고 부족 표시) |
| sales_status | body | string | 예 | — | 판매상태 (on_sale 판매중 / suspended 판매중지 / sold_out 품절 / coming_soon 출시예정) |
| display_status | body | string | 예 | — | 전시상태 (visible 전시 / hidden 숨김) |
| tax_status | body | string | 예 | — | 과세여부 (taxable 과세 / tax_free 면세) |
| tax_rate | body | number | 아니오 | min 0, max 100 | 세율(%) (과세 상품의 부가세 계산 비율) |
| shipping_policy_id | body | integer | 아니오 | — | shipping policy 식별자 |
| common_info_id | body | integer | 아니오 | — | common info 식별자 |
| description | body | array | 아니오 | — | 설명 |
| description_mode | body | string | 아니오 | `text`, `html` | 상세 설명 편집 모드 (text 일반 텍스트 / html HTML 에디터) |
| thumbnail_hash | body | string | 아니오 | max 64 | 대표 이미지로 지정할 이미지 해시 (업로드된 이미지 중 썸네일 선택) |
| image_temp_key | body | string | 아니오 | max 64 | 임시 업로드 세션 키 (사전 업로드한 이미지를 이 상품에 연결) |
| images | body | array | 아니오 | max 20 | 상품 이미지 목록 (각 항목: id/hash/url/alt_text/is_thumbnail/sort_order) |
| meta_title | body | array | 아니오 | — | SEO 메타 제목 (검색엔진/소셜 공유 표시 제목) |
| meta_description | body | array | 아니오 | — | SEO 메타 설명 (검색엔진/소셜 공유 표시 요약) |
| meta_keywords | body | array | 아니오 | — | SEO 메타 키워드 배열 (검색엔진 색인용 키워드 목록) |
| seo_sync_title | body | boolean | 아니오 | — | SEO 제목 자동 동기화 여부 (true 시 상품명으로 메타 제목 자동 채움) |
| seo_sync_description | body | boolean | 아니오 | — | SEO 설명 자동 동기화 여부 (true 시 상품 설명으로 메타 설명 자동 채움) |
| use_main_image_for_og | body | boolean | 아니오 | — | 대표 이미지를 OG(소셜 공유) 이미지로 사용할지 여부 |
| has_options | body | boolean | 아니오 | — | options 여부 |
| option_groups | body | array | 아니오 | — | 옵션 그룹 정의 (예: 색상/사이즈 등 옵션 축과 각 축의 선택값 목록) |
| options | body | array | 예 | min 1 | 옵션(SKU) 목록 (각 항목: 옵션코드·옵션명·옵션값·정가·판매가·재고 등, 최소 1건 필수) |
| additional_options | body | array | 아니오 | max 5 | 추가옵션 그룹 배열 (각 그룹당 선택지 1~20개, 필수 여부·추가금·직접입력 허용 등 설정) |
| notice_items | body | array | 아니오 | max 50 | 상품정보제공고시 항목 배열 (각 항목: 항목명·내용 다국어) |
| label_assignments | body | array | 아니오 | — | 라벨 할당 배열 (label_id + 노출 시작/종료일로 상품에 라벨 부착) |
| min_purchase_qty | body | integer | 아니오 | min 1 | 최소 구매 수량 (1회 주문 시 이 수량 이상 구매) |
| max_purchase_qty | body | integer | 아니오 | min 0 | 최대 구매 수량 (0=무제한) |
| purchase_restriction | body | string | 아니오 | `none`, `restricted` | 구매 대상 제한 (none 제한 없음 / restricted 특정 역할만 구매 허용) |
| allowed_roles | body | array | 아니오 | — | 구매 허용 역할 ID 배열 (purchase_restriction=restricted 시 필수) |
| barcode | body | string | 아니오 | max 50 | 바코드 |
| hs_code | body | string | 아니오 | max 20 | HS 코드 (수출입 관세 분류 코드) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.store_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.create`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 새 상품을 생성합니다. `auth:sanctum` + `sirsoft-ecommerce.products.create` 권한이 필요하며, `StoreProductRequest`로 검증된 데이터를 `ProductService::create()`에 넘겨 상품·옵션·카테고리·이미지·SEO 메타를 함께 저장하고 성공 시 201과 `ProductResource`를 반환합니다. 이미지는 사전에 `POST .../products/images`로 임시 업로드한 뒤 `image_temp_key`(또는 `images`/`thumbnail_hash`)로 연결하며, `options`는 최소 1건 필수입니다. 검증 실패는 422, 그 외 오류는 500으로 응답합니다.


### PATCH /api/modules/sirsoft-ecommerce/admin/products/bulk-price
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.bulk-price -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.bulk-price`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@bulkUpdatePrice`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| ids | body | array | 예 | min 1 | 대상 리소스 식별자 배열 (대량 작업 대상) |
| method | body | string | 예 | `increase`, `decrease`, `set` | 변경 방식 (increase 증가 / decrease 감소 / set 지정값으로 설정) |
| value | body | number | 예 | min 0 | 값 |
| unit | body | string | 예 | `won`, `percent` | 변경 단위 (won 금액 단위 / percent 판매가 대비 비율) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.bulk_price_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 선택한 여러 상품의 판매가를 일괄 변경합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `ProductService::bulkUpdatePrice()`가 `ids` 목록에 대해 `method`(increase/decrease/set)와 `unit`(won/percent) 조합으로 가격을 재계산해 저장합니다. 응답의 `updated_count`가 실제 반영 건수로 메타에 담기며, 대량 변경은 상품별 개별 활동 로그로 기록됩니다. 확장은 `sirsoft-ecommerce.product.bulk_price_validation_rules` 훅으로 검증 규칙을 확장할 수 있습니다.


### PATCH /api/modules/sirsoft-ecommerce/admin/products/bulk-status
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.bulk-status -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.bulk-status`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@bulkUpdateStatus`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| ids | body | array | 예 | min 1 | 대상 리소스 식별자 배열 (대량 작업 대상) |
| field | body | string | 예 | `sales_status`, `display_status` | 일괄 변경할 상태 필드 (sales_status 판매상태 / display_status 전시상태) |
| value | body | string | 예 | — | 값 |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.bulk_status_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 선택한 여러 상품의 상태를 일괄 변경합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `ProductService::bulkUpdateStatus()`가 `field`(sales_status 또는 display_status)를 지정한 `value`로 `ids` 대상에 일괄 적용합니다. 예를 들어 판매중지된 상품을 한 번에 판매중으로 전환하거나 노출/숨김을 일괄 조정할 때 사용하며, 반영 건수는 `updated_count`로 반환됩니다. 확장은 `sirsoft-ecommerce.product.bulk_status_validation_rules` 훅으로 검증 규칙을 확장할 수 있습니다.


### PATCH /api/modules/sirsoft-ecommerce/admin/products/bulk-stock
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.bulk-stock -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.bulk-stock`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@bulkUpdateStock`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| ids | body | array | 예 | min 1 | 대상 리소스 식별자 배열 (대량 작업 대상) |
| method | body | string | 예 | `increase`, `decrease`, `set` | 변경 방식 (increase 증가 / decrease 감소 / set 지정값으로 설정) |
| value | body | integer | 예 | min 0 | 값 |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.bulk_stock_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 선택한 여러 상품의 재고를 일괄 변경합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `ProductService::bulkUpdateStock()`가 `ids` 목록에 대해 `method`(increase/decrease/set)와 정수 `value`를 적용해 재고 수량을 조정합니다. 입고/재고 실사 반영 등 다건 재고 보정에 사용하며, 반영 건수는 `updated_count`로 반환됩니다. 확장은 `sirsoft-ecommerce.product.bulk_stock_validation_rules` 훅으로 검증 규칙을 확장할 수 있습니다.


### PATCH /api/modules/sirsoft-ecommerce/admin/products/bulk-update
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.bulk-update -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.bulk-update`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@bulkUpdate`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| ids | body | array | 예 | min 1 | 대상 리소스 식별자 배열 (대량 작업 대상) |
| bulk_changes | body | array | 아니오 | — | 상품 조건 기반 일괄 변경값 (지정 시 ids 전체에 sales_status/display_status 일괄 적용) |
| items | body | array | 아니오 | — | 처리 대상 항목 배열 |
| option_bulk_changes | body | array | 아니오 | — | 옵션 조건 기반 일괄 변경값 (price_adjustment/stock_quantity 를 method+value 로 일괄 조정) |
| option_items | body | array | 아니오 | — | 옵션 개별 인라인 수정 배열 (각 항목: product_id·option_id + 수정할 옵션 필드) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.bulk_update_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 상품과 옵션을 통합 일괄 수정합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `ProductService::bulkUpdate()`가 조건 기반 일괄 변경(`bulk_changes`/`option_bulk_changes`)과 행별 인라인 수정(`items`/`option_items`)을 함께 처리합니다. 일괄 변경 조건이 지정된 필드는 우선 적용되고 나머지는 개별 수정값이 반영되며, 응답 메타의 `count`는 상품 반영 건수와 옵션 반영 건수를 합산한 값입니다. 관리자 목록 화면의 인라인 편집·일괄 편집을 한 요청으로 저장하는 데 사용됩니다.


### GET /api/modules/sirsoft-ecommerce/admin/products/by-code/{code}
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.show-by-code -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.show-by-code`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@showByCode`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| code | path | string | 예 | — | 대상 리소스의 코드 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품코드로 단일 상품의 상세 정보를 조회합니다. `auth:sanctum` + `sirsoft-ecommerce.products.read` 권한이 필요하며, `ProductService::findByCode()`로 `code`에 해당하는 상품을 찾아 `ProductResource`로 반환합니다. ID가 아닌 판매/관리용 상품코드로 상세를 열람할 때 사용하며, 일치하는 상품이 없으면 404를 반환합니다.


### PUT /api/modules/sirsoft-ecommerce/admin/products/by-code/{code}
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.update-by-code -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.update-by-code`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@updateByCode`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| code | path | string | 예 | — | 대상 리소스의 코드 |
| name | body | array | 아니오 | — | 대상의 이름/명칭 |
| product_code | body | string | 예 | max 50 | 상품코드 (상품 고유 관리 식별자, 상품 간 중복 불가) |
| sales_product_code | body | string | 아니오 | max 50 | 판매자 상품코드 (판매자가 직접 입력하는 관리용 코드) |
| sku | body | string | 아니오 | max 100 | 재고관리코드(SKU) |
| category_ids | body | array | 아니오 | min 1, max 5 | category 식별자 배열 |
| primary_category_id | body | integer | 아니오 | — | primary category 식별자 |
| brand_id | body | integer | 아니오 | — | brand 식별자 |
| list_price | body | integer | 아니오 | min 0.01 | 정가 (기본통화 기준, 소수 통화는 소수 입력 허용) |
| selling_price | body | integer | 아니오 | min 0.01 | 판매가 (기본통화 기준, 정가 이하여야 함) |
| stock_quantity | body | integer | 아니오 | min 0 | 재고 수량 (옵션 사용 시 옵션 재고 합계로 관리) |
| safe_stock_quantity | body | integer | 아니오 | min 0 | 안전재고 수량 (이 값 미만이면 재고 부족 표시) |
| sales_status | body | string | 아니오 | — | 판매상태 (on_sale 판매중 / suspended 판매중지 / sold_out 품절 / coming_soon 출시예정) |
| display_status | body | string | 아니오 | — | 전시상태 (visible 전시 / hidden 숨김) |
| tax_status | body | string | 아니오 | — | 과세여부 (taxable 과세 / tax_free 면세) |
| tax_rate | body | number | 아니오 | min 0, max 100 | 세율(%) (과세 상품의 부가세 계산 비율) |
| shipping_policy_id | body | integer | 아니오 | — | shipping policy 식별자 |
| common_info_id | body | integer | 아니오 | — | common info 식별자 |
| description | body | array | 아니오 | — | 설명 |
| description_mode | body | string | 아니오 | `text`, `html` | 상세 설명 편집 모드 (text 일반 텍스트 / html HTML 에디터) |
| thumbnail_hash | body | string | 아니오 | max 64 | 대표 이미지로 지정할 이미지 해시 (업로드된 이미지 중 썸네일 선택) |
| image_temp_key | body | string | 아니오 | max 64 | 임시 업로드 세션 키 (사전 업로드한 이미지를 이 상품에 연결) |
| images | body | array | 아니오 | max 20 | 상품 이미지 목록 (각 항목: id/hash/url/alt_text/is_thumbnail/sort_order) |
| meta_title | body | array | 아니오 | — | SEO 메타 제목 (검색엔진/소셜 공유 표시 제목) |
| meta_description | body | array | 아니오 | — | SEO 메타 설명 (검색엔진/소셜 공유 표시 요약) |
| meta_keywords | body | array | 아니오 | — | SEO 메타 키워드 배열 (검색엔진 색인용 키워드 목록) |
| seo_sync_title | body | boolean | 아니오 | — | SEO 제목 자동 동기화 여부 (true 시 상품명으로 메타 제목 자동 채움) |
| seo_sync_description | body | boolean | 아니오 | — | SEO 설명 자동 동기화 여부 (true 시 상품 설명으로 메타 설명 자동 채움) |
| use_main_image_for_og | body | boolean | 아니오 | — | 대표 이미지를 OG(소셜 공유) 이미지로 사용할지 여부 |
| has_options | body | boolean | 아니오 | — | options 여부 |
| option_groups | body | array | 아니오 | — | 옵션 그룹 정의 (예: 색상/사이즈 등 옵션 축과 각 축의 선택값 목록) |
| options | body | array | 아니오 | min 1 | 옵션(SKU) 목록 (각 항목: 옵션코드·옵션명·옵션값·정가·판매가·재고 등) |
| additional_options | body | array | 아니오 | max 5 | 추가옵션 그룹 배열 (각 그룹당 선택지 1~20개, 필수 여부·추가금·직접입력 허용 등 설정) |
| notice_items | body | array | 아니오 | max 50 | 상품정보제공고시 항목 배열 (각 항목: 항목명·내용 다국어) |
| label_assignments | body | array | 아니오 | — | 라벨 할당 배열 (label_id + 노출 시작/종료일로 상품에 라벨 부착) |
| min_purchase_qty | body | integer | 아니오 | min 1 | 최소 구매 수량 (1회 주문 시 이 수량 이상 구매) |
| max_purchase_qty | body | integer | 아니오 | min 0 | 최대 구매 수량 (0=무제한) |
| purchase_restriction | body | string | 아니오 | `none`, `restricted` | 구매 대상 제한 (none 제한 없음 / restricted 특정 역할만 구매 허용) |
| allowed_roles | body | array | 아니오 | — | 구매 허용 역할 ID 배열 (purchase_restriction=restricted 시 필수) |
| barcode | body | string | 아니오 | max 50 | 바코드 |
| hs_code | body | string | 아니오 | max 20 | HS 코드 (수출입 관세 분류 코드) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.update_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품코드로 기존 상품을 수정합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `ProductService::findByCode()`로 `code` 대상 상품을 찾은 뒤 `UpdateProductRequest`로 검증된 값을 `ProductService::update()`에 넘겨 상품·옵션·이미지·SEO 등을 갱신하고 `ProductResource`를 반환합니다. `{product}` ID 경로 대신 상품코드 기반으로 수정할 때 사용하며, 대상 상품이 없으면 404, 검증 실패는 422로 응답합니다.


### POST /api/modules/sirsoft-ecommerce/admin/products/generate-code
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.generate-code -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.generate-code`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@generateCode`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.create`

**요청 파라미터**

_요청 파라미터 없음._

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.create`)이 없는 경우 |

<!-- @generated:end -->

**설명** 중복되지 않는 신규 상품코드를 생성해 반환합니다. `auth:sanctum` + `sirsoft-ecommerce.products.create` 권한이 필요하며, `ProductService::generateUniqueCode()`가 기존 상품과 충돌하지 않는 코드를 발급해 `product_code` 필드로 응답합니다. 상품 등록 폼에서 코드 자동 채움 버튼을 눌렀을 때 사용하며, 요청 본문은 없습니다.


### POST /api/modules/sirsoft-ecommerce/admin/products/images
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.images.upload-temp -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.images.upload-temp`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@uploadImage`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| file | body | file | 예 | max 10240 | 업로드 파일 |
| temp_key | body | string | 아니오 | max 64 | 임시 업로드 세션 키 (같은 상품의 여러 이미지를 한 세션으로 묶음, 생략 시 서버가 UUID 자동 발급) |
| collection | body | string | 아니오 | — | 첨부 컬렉션 그룹명 (첨부를 용도별로 묶는 키, 미지정 시 default) |
| alt_text | body | array | 아니오 | — | 이미지 대체 텍스트 (접근성/이미지 미표시 시 대체 문구) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product-image.filter_upload_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 상품 등록 전 이미지를 임시로 업로드합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `productId` 경로가 없어 `ProductImageService::upload()`가 상품에 귀속되지 않은 임시 이미지로 저장합니다. `temp_key`를 넘기면 같은 업로드 세션으로 묶이고 생략 시 서버가 UUID를 자동 발급해 응답에 포함하므로, 이후 상품 생성/수정 요청의 `image_temp_key`로 전달해 실제 상품에 연결합니다. 컬렉션 내 첫 이미지는 자동으로 대표 이미지(`is_thumbnail`)로 지정되며, 개수 상한 초과 시 422를 반환합니다.


### PATCH /api/modules/sirsoft-ecommerce/admin/products/images/reorder
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.images.reorder -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.images.reorder`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@reorderImages`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| order | body | array | 예 | min 1 | 이미지 순서 배열 (각 항목: id + 부여할 order 값, 이미지별 노출 순서 갱신) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product-image.filter_reorder_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 상품 이미지의 노출 순서를 일괄 변경합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, 컨트롤러가 `order` 배열을 `id => order` 맵으로 변환해 `ProductImageService::reorder()`에 넘겨 각 이미지의 `sort_order`를 갱신합니다. 이미지 갤러리에서 드래그로 순서를 재배치한 결과를 저장할 때 사용합니다. 확장은 `sirsoft-ecommerce.product-image.filter_reorder_validation_rules` 훅으로 검증 규칙을 확장할 수 있습니다.


### DELETE /api/modules/sirsoft-ecommerce/admin/products/images/{id}
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.images.delete -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.images.delete`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@deleteImage`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품 이미지 1건을 삭제합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `ProductImageService::delete()`가 `id`에 해당하는 이미지 레코드와 저장 파일을 제거합니다. 임시 업로드 이미지와 상품에 귀속된 이미지 모두 삭제할 수 있으며, 해당 이미지가 없으면 404를 반환합니다.


### GET /api/modules/sirsoft-ecommerce/admin/products/{identifier}
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.show -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.show`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@show`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| identifier | path | string | 예 | — | 대상 리소스의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 관리자 화면용 단일 상품 상세를 조회합니다. `auth:sanctum` + `sirsoft-ecommerce.products.read` 권한이 필요하며, `ProductService::findByIdOrCode()`로 숫자 ID 우선, 없으면 상품코드로 상품을 찾은 뒤 `getDetail($id, includeInactive: true)`로 비활성(숨김/판매중지) 상품까지 포함해 상세를 로드하고 `ProductResource`로 반환합니다. 공개 상세와 달리 전시상태에 관계없이 조회되므로 관리자 편집/열람에 사용하며, 대상이 없으면 404를 반환합니다.


### POST /api/modules/sirsoft-ecommerce/admin/products/{productId}/images
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.images.upload -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.images.upload`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@uploadImage`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| productId | path | string | 예 | — | 대상 product의 식별자 |
| file | body | file | 예 | max 10240 | 업로드 파일 |
| temp_key | body | string | 아니오 | max 64 | 임시 업로드 세션 키 (같은 상품의 여러 이미지를 한 세션으로 묶음, 생략 시 서버가 UUID 자동 발급) |
| collection | body | string | 아니오 | — | 첨부 컬렉션 그룹명 (첨부를 용도별로 묶는 키, 미지정 시 default) |
| alt_text | body | array | 아니오 | — | 이미지 대체 텍스트 (접근성/이미지 미표시 시 대체 문구) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product-image.filter_upload_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 기존 상품에 이미지 1건을 업로드해 즉시 귀속시킵니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, 경로의 `productId`가 있어 `ProductImageService::upload()`가 임시가 아닌 해당 상품 소유 이미지로 저장하고 `collection`별 마지막 순서에 추가합니다. 상품 편집 화면에서 이미지를 추가할 때 사용하며, 컬렉션의 첫 이미지는 자동으로 대표 이미지로 지정됩니다. 개수 상한 초과 시 422, 상품이 없으면 404를 반환합니다.


### PATCH /api/modules/sirsoft-ecommerce/admin/products/{productId}/images/{imageId}/thumbnail
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.images.set-thumbnail -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.images.set-thumbnail`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@setThumbnail`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| productId | path | string | 예 | — | 대상 product의 식별자 |
| imageId | path | string | 예 | — | 대상 image의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품의 대표(썸네일) 이미지를 지정합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, `ProductImageService::setThumbnail()`가 같은 상품의 기존 대표 이미지의 `is_thumbnail`을 해제하고 `imageId` 이미지에 대표 플래그를 부여합니다. 목록·상세에서 노출될 기본 이미지를 교체할 때 사용하며, 지정 대상 상품/이미지가 없으면 404를 반환합니다.


### DELETE /api/modules/sirsoft-ecommerce/admin/products/{product}
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.destroy -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.destroy`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@destroy`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.delete`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| product | path | string | 예 | — | 대상 product의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.delete`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품 1건을 삭제합니다. `auth:sanctum` + `sirsoft-ecommerce.products.delete` 권한이 필요하며, 먼저 `ProductService::checkCanDelete()`로 주문 이력을 선행 검사해 이력이 있으면 관련 주문 수(`count`)와 함께 409 Conflict로 차단하고, 통과 시 `ProductService::delete()`가 상품과 하위 데이터(옵션/이미지 등)를 명시적으로 제거합니다. 서비스 계층의 도메인 가드가 경합/우회 상황에서도 `ProductHasOrderHistoryException`으로 재차 409를 반환하며, 대상이 없으면 404를 반환합니다.


### PUT /api/modules/sirsoft-ecommerce/admin/products/{product}
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.update -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.update`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@update`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.update`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| product | path | string | 예 | — | 대상 product의 식별자 |
| name | body | array | 아니오 | — | 대상의 이름/명칭 |
| product_code | body | string | 예 | max 50 | 상품코드 (상품 고유 관리 식별자, 상품 간 중복 불가) |
| sales_product_code | body | string | 아니오 | max 50 | 판매자 상품코드 (판매자가 직접 입력하는 관리용 코드) |
| sku | body | string | 아니오 | max 100 | 재고관리코드(SKU) |
| category_ids | body | array | 아니오 | min 1, max 5 | category 식별자 배열 |
| primary_category_id | body | integer | 아니오 | — | primary category 식별자 |
| brand_id | body | integer | 아니오 | — | brand 식별자 |
| list_price | body | integer | 아니오 | min 0.01 | 정가 (기본통화 기준, 소수 통화는 소수 입력 허용) |
| selling_price | body | integer | 아니오 | min 0.01 | 판매가 (기본통화 기준, 정가 이하여야 함) |
| stock_quantity | body | integer | 아니오 | min 0 | 재고 수량 (옵션 사용 시 옵션 재고 합계로 관리) |
| safe_stock_quantity | body | integer | 아니오 | min 0 | 안전재고 수량 (이 값 미만이면 재고 부족 표시) |
| sales_status | body | string | 아니오 | — | 판매상태 (on_sale 판매중 / suspended 판매중지 / sold_out 품절 / coming_soon 출시예정) |
| display_status | body | string | 아니오 | — | 전시상태 (visible 전시 / hidden 숨김) |
| tax_status | body | string | 아니오 | — | 과세여부 (taxable 과세 / tax_free 면세) |
| tax_rate | body | number | 아니오 | min 0, max 100 | 세율(%) (과세 상품의 부가세 계산 비율) |
| shipping_policy_id | body | integer | 아니오 | — | shipping policy 식별자 |
| common_info_id | body | integer | 아니오 | — | common info 식별자 |
| description | body | array | 아니오 | — | 설명 |
| description_mode | body | string | 아니오 | `text`, `html` | 상세 설명 편집 모드 (text 일반 텍스트 / html HTML 에디터) |
| thumbnail_hash | body | string | 아니오 | max 64 | 대표 이미지로 지정할 이미지 해시 (업로드된 이미지 중 썸네일 선택) |
| image_temp_key | body | string | 아니오 | max 64 | 임시 업로드 세션 키 (사전 업로드한 이미지를 이 상품에 연결) |
| images | body | array | 아니오 | max 20 | 상품 이미지 목록 (각 항목: id/hash/url/alt_text/is_thumbnail/sort_order) |
| meta_title | body | array | 아니오 | — | SEO 메타 제목 (검색엔진/소셜 공유 표시 제목) |
| meta_description | body | array | 아니오 | — | SEO 메타 설명 (검색엔진/소셜 공유 표시 요약) |
| meta_keywords | body | array | 아니오 | — | SEO 메타 키워드 배열 (검색엔진 색인용 키워드 목록) |
| seo_sync_title | body | boolean | 아니오 | — | SEO 제목 자동 동기화 여부 (true 시 상품명으로 메타 제목 자동 채움) |
| seo_sync_description | body | boolean | 아니오 | — | SEO 설명 자동 동기화 여부 (true 시 상품 설명으로 메타 설명 자동 채움) |
| use_main_image_for_og | body | boolean | 아니오 | — | 대표 이미지를 OG(소셜 공유) 이미지로 사용할지 여부 |
| has_options | body | boolean | 아니오 | — | options 여부 |
| option_groups | body | array | 아니오 | — | 옵션 그룹 정의 (예: 색상/사이즈 등 옵션 축과 각 축의 선택값 목록) |
| options | body | array | 아니오 | min 1 | 옵션(SKU) 목록 (각 항목: 옵션코드·옵션명·옵션값·정가·판매가·재고 등) |
| additional_options | body | array | 아니오 | max 5 | 추가옵션 그룹 배열 (각 그룹당 선택지 1~20개, 필수 여부·추가금·직접입력 허용 등 설정) |
| notice_items | body | array | 아니오 | max 50 | 상품정보제공고시 항목 배열 (각 항목: 항목명·내용 다국어) |
| label_assignments | body | array | 아니오 | — | 라벨 할당 배열 (label_id + 노출 시작/종료일로 상품에 라벨 부착) |
| min_purchase_qty | body | integer | 아니오 | min 1 | 최소 구매 수량 (1회 주문 시 이 수량 이상 구매) |
| max_purchase_qty | body | integer | 아니오 | min 0 | 최대 구매 수량 (0=무제한) |
| purchase_restriction | body | string | 아니오 | `none`, `restricted` | 구매 대상 제한 (none 제한 없음 / restricted 특정 역할만 구매 허용) |
| allowed_roles | body | array | 아니오 | — | 구매 허용 역할 ID 배열 (purchase_restriction=restricted 시 필수) |
| barcode | body | string | 아니오 | max 50 | 바코드 |
| hs_code | body | string | 아니오 | max 20 | HS 코드 (수출입 관세 분류 코드) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.update_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.update`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** ID 경로로 지정한 상품을 수정합니다. `auth:sanctum` + `sirsoft-ecommerce.products.update` 권한이 필요하며, 라우트 모델 바인딩된 `Product`에 `UpdateProductRequest`로 검증된 값을 `ProductService::update()`로 반영해 상품 기본정보·옵션·이미지·SEO 메타를 갱신하고 `ProductResource`를 반환합니다. `by-code` 변형과 동일 서비스 메서드를 쓰지만 상품코드 조회 단계 없이 바로 대상 모델을 받습니다. 검증 실패는 422, 대상이 없으면 404로 응답합니다.


### GET /api/modules/sirsoft-ecommerce/admin/products/{product}/can-delete
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.can-delete -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.can-delete`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@canDelete`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.delete`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| product | path | string | 예 | — | 대상 product의 식별자 |

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| canDelete | boolean | `false` | 삭제 가능 여부 (true 삭제 가능 / false 주문 이력 등으로 삭제 불가) |
| reason | string | `이 상품은 5건의 주문 이력이 있어 삭제할 수 없습니다.` | 삭제 불가 사유 (canDelete=false 일 때 안내 문구) |
| relatedData | object | `{"orders":5,"images":4,"options":3,"additionalOptions":0,…` | 연관 데이터 건수 (orders/images/options 등 상품에 연결된 하위 데이터 수) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.delete`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품 삭제 가능 여부를 사전 확인합니다. `auth:sanctum` + `sirsoft-ecommerce.products.delete` 권한이 필요하며, `ProductService::checkCanDelete()`가 주문 이력 등 연관 데이터를 검사해 `canDelete` 불리언과 차단 `reason`, 그리고 `relatedData`(orders/images/options 등 연관 건수)를 반환합니다. 삭제 버튼을 누르기 전 확인 다이얼로그에서 삭제 가능 여부와 연관 데이터를 안내하는 데 사용하며, 실제 삭제는 DELETE 엔드포인트가 수행합니다.


### GET /api/modules/sirsoft-ecommerce/admin/products/{product}/copy
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.show-for-copy -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.show-for-copy`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@showForCopy`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| product | path | string | 예 | — | 대상 product의 식별자 |

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| name | object | `{"ko":"면 손수건 3매입 #1","en":"Cotton Handkerchief 3pcs #1"}` | 상품명 (다국어 JSON: {ko: "...", en: "..."}) |
| product_code | string | `BP747N3QSSSNEPII` | 상품코드 |
| sales_product_code | null | `null` | 판매자 상품코드 (사용자 입력용) |
| sku | string | `HK-0001` | SKU |
| brand_id | integer | `91` | 브랜드 ID |
| category_ids | array | `[114,116]` | category 식별자 배열 (연관 리소스 참조) |
| primary_category_id | integer | `116` | primary category 식별자 (연관 리소스 참조) |
| list_price | string | `4000.00` | 정가 (기본통화 기준) |
| selling_price | string | `2000.00` | 판매가 (기본통화 기준) |
| stock_quantity | integer | `59` | 재고 수량 (옵션 있으면 옵션 합계) |
| safe_stock_quantity | integer | `15` | 안전재고 수량 |
| tax_status | string | `taxable` | 과세여부: taxable(과세), tax_free(면세) |
| sales_status | string | `on_sale` | 판매상태: on_sale(판매중), suspended(판매중지), sold_out(품절), coming_soon(출시예정) |
| display_status | string | `visible` | 전시상태: visible(전시), hidden(숨김) |
| options | array | `[{"option_code":"AS2HM7CEDFHEGS43-001","option_name":{"ko…` | 복사 대상 옵션(SKU) 목록 (신규 등록 폼에 채울 옵션 정의, 다중통화 가격 포함) |
| additional_options | array | `[]` | 복사 대상 추가옵션 그룹 목록 (그룹명·선택지·추가금 등) |
| images | array | `[{"hash":"7858be3cf217","url":null,"original_filename":"p…` | 복사 대상 이미지 목록 (각 항목: hash·원본파일명 등, copy_images 선택 시 포함) |
| thumbnail_hash | string | `7858be3cf217` | 대표 이미지 해시 (썸네일로 지정된 이미지의 hash) |
| description | object | `{"ko":"<p>부드러운 면 100% 손수건 3매 세트입니다.<\/p>","en":"<p>A set …` | 상세 설명 (다국어 JSON, HTML 포함) |
| description_mode | string | `text` | 설명 모드: text(텍스트), html(HTML) |
| notice_items | array | `[{"name":{"ko":"제품 소재 (충전재 포함)","en":"Material (Including…` | 상품정보제공고시 항목 목록 (각 항목: 항목명·내용 다국어) |
| shipping_policy_id | integer | `31` | 배송정책 ID |
| shipping_policy | object | `{"id":31,"name":{"ko":"국내 무료배송","en":"Domestic Free Shipp…` | 현재 부여된 배송정책 객체 (비활성 포함 — 수정폼 활성 목록에 없을 때 union 표시용) |
| common_info_id | integer | `207` | 공통정보 템플릿 ID |
| label_assignments | array | `[{"label_id":26,"start_date":null,"end_date":null}]` | 라벨 할당 목록 (각 항목: label_id + 노출 시작/종료일) |
| min_purchase_qty | integer | `1` | 최소 구매 수량 |
| max_purchase_qty | integer | `0` | 최대 구매 수량 (0=무제한) |
| purchase_restriction | string | `none` | 구매 제한: none(없음), restricted(제한) |
| allowed_roles | array | `[]` | 구매 허용 역할 ID 배열 |
| meta_title | null | `null` | SEO 제목 (다국어 JSON) |
| meta_description | null | `null` | SEO 설명 (다국어 JSON) |
| seo_tags | array | `[]` | SEO 태그 목록 (메타 키워드 등 검색엔진 노출용 태그) |
| seo_sync_title | boolean | `true` | SEO 제목 동기화 여부 (1: 상품명으로 자동 채움, 0: 직접 입력 보존) |
| seo_sync_description | boolean | `true` | SEO 설명 동기화 여부 (1: 상품 설명으로 자동 채움, 0: 직접 입력 보존) |
| barcode | null | `null` | 바코드 |
| hs_code | null | `null` | HS 코드 (관세 분류) |
| thumbnail_url | string | `/api/modules/sirsoft-ecommerce/produc…` | thumbnail URL |
| categories | array | `[{"id":114,"name":{"ko":"스포츠","en":"Sports"},"name_locali…` | 소속 카테고리 목록 (breadcrumb 포함 — 복사 폼의 카테고리 표시용) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품 복사(복제) 등록 폼을 채우기 위한 원본 데이터를 조회합니다. `auth:sanctum` + `sirsoft-ecommerce.products.read` 권한이 필요하며, `copy_images`/`copy_options`/`copy_categories`/`copy_seo` 등 쿼리 불리언으로 복사 항목을 선택하면 `ProductService::getDetailForCopy()`가 해당 항목만 담아 반환합니다. 컨트롤러가 대표 이미지의 `thumbnail_url`, 카테고리 breadcrumb, 옵션별 다중통화 가격(`ProductOptionResource`)을 추가로 보강하며, SEO는 기본적으로 복사 제외(false)입니다. 대상 상품이 없으면 404를 반환합니다.


### GET /api/modules/sirsoft-ecommerce/admin/products/{product}/form
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.show-for-form -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.show-for-form`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@showForForm`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| product | path | string | 예 | — | 대상 product의 식별자 |

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| id | integer | `201` | 기본 키 (내부 식별자) |
| name | object | `{"ko":"면 손수건 3매입 #1","en":"Cotton Handkerchief 3pcs #1"}` | 상품명 (다국어 JSON: {ko: "...", en: "..."}) |
| product_code | string | `AS2HM7CEDFHEGS43` | 상품코드 |
| sales_product_code | null | `null` | 판매자 상품코드 (사용자 입력용) |
| sku | string | `HK-0001` | SKU |
| brand_id | integer | `91` | 브랜드 ID |
| category_ids | array | `[114,116]` | category 식별자 배열 (연관 리소스 참조) |
| primary_category_id | integer | `116` | primary category 식별자 (연관 리소스 참조) |
| created_at | string | `2026-06-15 02:24:15` | 생성 일시 |
| updated_at | string | `2026-06-15 02:24:15` | 최종 수정 일시 |
| list_price | string | `4000.00` | 정가 (기본통화 기준) |
| selling_price | string | `2000.00` | 판매가 (기본통화 기준) |
| stock_quantity | integer | `59` | 재고 수량 (옵션 있으면 옵션 합계) |
| safe_stock_quantity | integer | `15` | 안전재고 수량 |
| tax_status | string | `taxable` | 과세여부: taxable(과세), tax_free(면세) |
| sales_status | string | `on_sale` | 판매상태: on_sale(판매중), suspended(판매중지), sold_out(품절), coming_soon(출시예정) |
| display_status | string | `visible` | 전시상태: visible(전시), hidden(숨김) |
| options | array | `[{"id":1086,"option_code":"AS2HM7CEDFHEGS43-001","option_…` | 옵션(SKU) 목록 (수정 폼 바인딩용, 각 옵션의 id·코드·옵션값·가격·재고 등) |
| additional_options | array | `[]` | 추가옵션 그룹 목록 (수정 폼 바인딩용, 그룹명·선택지·추가금 등) |
| images | array | `[{"id":801,"hash":"7858be3cf217","url":null,"original_fil…` | 이미지 목록 (각 항목: id·hash·원본파일명 등) |
| thumbnail_hash | string | `7858be3cf217` | 대표 이미지 해시 (썸네일로 지정된 이미지의 hash) |
| description | object | `{"ko":"<p>부드러운 면 100% 손수건 3매 세트입니다.<\/p>","en":"<p>A set …` | 상세 설명 (다국어 JSON, HTML 포함) |
| description_mode | string | `text` | 설명 모드: text(텍스트), html(HTML) |
| notice_items | array | `[{"name":{"ko":"제품 소재 (충전재 포함)","en":"Material (Including…` | 상품정보제공고시 항목 목록 (각 항목: 항목명·내용 다국어) |
| shipping_policy_id | integer | `31` | 배송정책 ID |
| shipping_policy | object | `{"id":31,"name":{"ko":"국내 무료배송","en":"Domestic Free Shipp…` | 현재 부여된 배송정책 객체 (비활성 포함 — 수정폼 활성 목록에 없을 때 union 표시용) |
| common_info_id | integer | `207` | 공통정보 템플릿 ID |
| label_assignments | array | `[{"label_id":26,"start_date":null,"end_date":null}]` | 라벨 할당 목록 (각 항목: label_id + 노출 시작/종료일) |
| min_purchase_qty | integer | `1` | 최소 구매 수량 |
| max_purchase_qty | integer | `0` | 최대 구매 수량 (0=무제한) |
| purchase_restriction | string | `none` | 구매 제한: none(없음), restricted(제한) |
| allowed_roles | array | `[]` | 구매 허용 역할 ID 배열 |
| meta_title | null | `null` | SEO 제목 (다국어 JSON) |
| meta_description | null | `null` | SEO 설명 (다국어 JSON) |
| seo_tags | array | `[]` | SEO 태그 목록 (메타 키워드 등 검색엔진 노출용 태그) |
| seo_sync_title | boolean | `true` | SEO 제목 동기화 여부 (1: 상품명으로 자동 채움, 0: 직접 입력 보존) |
| seo_sync_description | boolean | `true` | SEO 설명 동기화 여부 (1: 상품 설명으로 자동 채움, 0: 직접 입력 보존) |
| barcode | null | `null` | 바코드 |
| hs_code | null | `null` | HS 코드 (관세 분류) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품 수정 폼을 채우기 위한 상세 데이터를 조회합니다. `auth:sanctum` + `sirsoft-ecommerce.products.read` 권한이 필요하며, `ProductService::getDetailForForm()`가 폼 입력 필드에 맞춘 형태(카테고리 ID 배열, 옵션/추가옵션, 이미지, SEO 태그 등)로 데이터를 반환합니다. 관리자 상품 편집 화면 진입 시 폼 초기값을 로드하는 데 사용하며, 대상 상품이 없으면 404를 반환합니다.


### GET /api/modules/sirsoft-ecommerce/admin/products/{product}/logs
<!-- @generated:start:api.modules.sirsoft-ecommerce.admin.products.logs -->
- **라우트명**: `api.modules.sirsoft-ecommerce.admin.products.logs`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Admin\ProductController@logs`
- **인증/권한**: `auth:sanctum` + `permission:sirsoft-ecommerce.products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| product | path | string | 예 | — | 대상 product의 식별자 |

**응답 필드** (`data` 내부)

_목록 응답: `data.data[]` 배열 항목의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| id | integer | `151066` | 기본 키 (내부 식별자) |
| log_type | string | `admin` | 로그 구분 값 (admin 관리자 작업 / user 사용자 작업 등 활동 로그 채널) |
| log_type_label | string | `관리자` | `log_type` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| loggable_type | string | `Modules\Sirsoft\Ecommerce\Models\Product` | 로그 대상 모델의 전체 클래스명 (상품 또는 상품옵션 모델) |
| loggable_type_display | string | `Product` | 로그 대상 모델의 표시용 짧은 이름 (클래스 basename) |
| loggable_id | integer | `201` | loggable 식별자 (연관 리소스 참조) |
| action | string | `product.create` | 활동 액션 키 (product.create/update 등 수행된 작업 식별자) |
| action_label | string | `생성` | `action` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| localized_description | string | `상품 생성 (면 손수건 3매입 #1)` | `description` 의 현재 로케일 해석 값 (다국어 필드를 표시용 문자열로 해석) |
| description_key | string | `sirsoft-ecommerce::activity_log.descr…` | 설명 번역 키 (localized_description 을 생성하는 다국어 키) |
| properties | null | `null` | 로그 부가 속성 (액션에 첨부된 임의 메타데이터, 없으면 null) |
| changes | array | `[{"field":"sku","label_key":"sirsoft-ecommerce::activity_…` | 단일 수정 변경 내역 (각 항목: field·label·old·new, 일괄 수정 로그면 null) |
| bulk_changes | null | `null` | 일괄 수정 변경 내역 (각 항목: model_id·changes 배열, 단일 수정 로그면 null) |
| has_changes | boolean | `false` | changes 여부 |
| actor_name | string | `관리자` | 행위를 수행한 주체(사용자/시스템)의 이름 |
| user | object | `{"uuid":"a1e0a91a-fba6-491c-a53e-7285a5686857","name":"관리…` | 행위 수행 사용자 정보 (uuid·name·email, 시스템 작업이면 name 만 '시스템') |
| ip_address | string | `192.168.1.10` | 요청/행위가 발생한 IP 주소 |
| created_at | string | `2026-06-14 08:28:44` | 생성 일시 |
| is_owner | boolean | `true` | 현재 인증 사용자가 이 리소스의 소유자인지 여부 (BaseApiResource 표준 메타) |
| abilities | object | `{"can_read":true,"can_delete":true}` | 현재 사용자가 이 리소스에 수행 가능한 작업 불리언 맵 (can_update, can_delete 등 — 권한 맵 기반) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품의 처리로그(활동 로그) 목록을 조회합니다. `auth:sanctum` + `sirsoft-ecommerce.products.read` 권한이 필요하며, 컨트롤러가 해당 상품과 그 하위 옵션(`ProductOption`)의 `ActivityLog` 레코드를 `loggable_type`/`loggable_id` 기준으로 합쳐 `created_at` 정렬(기본 desc)로 페이지네이션합니다. `per_page`/`sort_order` 쿼리로 조회 범위를 조정하며, 상품 상세의 처리 이력 탭에서 생성/수정/재고 변경 등 감사 로그를 표시하는 데 사용합니다. 대상 상품이 없으면 404를 반환합니다.


### GET /api/modules/sirsoft-ecommerce/products
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.index -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.index`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductController@index`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| category_id | query | integer | 아니오 | — | category 식별자 |
| category_slug | query | string | 아니오 | max 100 | 카테고리 slug 필터 (URL 친화 식별자로 카테고리 지정, category_id 대체 가능) |
| brand_id | query | integer | 아니오 | — | brand 식별자 |
| search | query | string | 아니오 | max 200 | 검색어 (지정한 검색 대상 필드에서 부분 일치) |
| sort | query | string | 아니오 | `latest`, `sales`, `price_asc`, `price_desc` | 정렬 기준 (필드명, `-` 접두 시 내림차순) |
| min_price | query | integer | 아니오 | min 0 | 판매가 범위 필터 하한 (판매가가 이 값 이상인 상품) |
| max_price | query | integer | 아니오 | min 0 | 판매가 범위 필터 상한 (판매가가 이 값 이하인 상품) |
| per_page | query | integer | 아니오 | min 1, max 100 | 페이지당 항목 수 |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.public_list_validation_rules`, `sirsoft-ecommerce.product.public_list_validation_messages`).

**응답 필드** (`data` 내부)

_목록 응답: `data.data[]` 배열 항목의 필드 + `data.pagination`._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| number | integer | `109` | 목록에서의 순번 (페이지네이션 반영 행 번호 — HasRowNumber 파생) |
| id | integer | `322` | 기본 키 (내부 식별자) |
| name | object | `{"ko":"eum et quia","en":"tenetur id quae"}` | 대상의 이름/명칭 (다국어 필드는 로케일별 값 객체) |
| name_localized | string | `eum et quia` | `name` 의 현재 로케일 해석 값 (다국어 필드를 표시용 문자열로 해석) |
| product_code | string | `PROD-GJUX-1484` | 상품코드 (상품 고유 관리 식별자) |
| sku | string | `SKU-MRAD-9306` | 재고관리코드(SKU) |
| thumbnail_url | string | `/api/modules/sirsoft-ecommerce/produc…` | thumbnail URL |
| list_price | integer | `112594` | 정가 (기본통화 자릿수로 정규화된 값) |
| list_price_formatted | string | `112,594원` | `list_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| selling_price | integer | `88949` | 판매가 (기본통화 자릿수로 정규화된 값) |
| selling_price_formatted | string | `88,949원` | `selling_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| discount_rate | integer | `21` | 할인율(%) (정가 대비 판매가 할인 비율, (1 - 판매가/정가) × 100) |
| multi_currency_list_price | object | `{"KRW":{"price":112594,"formatted":"112,594원","is_default…` | 통화별 정가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 정가) |
| multi_currency_selling_price | object | `{"KRW":{"price":88949,"formatted":"88,949원","is_default":…` | 통화별 판매가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 판매가) |
| stock_quantity | integer | `22` | 재고 수량 (옵션 사용 시 옵션 재고 합계) |
| safe_stock_quantity | integer | `12` | 안전재고 수량 (이 값 미만이면 재고 부족으로 표시) |
| is_below_safe_stock | boolean | `false` | below safe stock 여부 |
| sales_status | string | `on_sale` | 판매상태 값 (on_sale 판매중 / suspended 판매중지 / sold_out 품절 / coming_soon 출시예정) |
| sales_status_label | string | `판매중` | `sales_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| sales_status_variant | string | `success` | `sales_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| display_status | string | `visible` | 전시상태 값 (visible 전시 / hidden 숨김) |
| display_status_label | string | `전시` | `display_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| display_status_variant | string | `success` | `display_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| categories | array | `[]` | 소속 카테고리 목록 (각 항목: id·현지화 이름·대표 여부. categories 관계 eager load 시에만 채워짐) |
| primary_category | string | `스마트폰` | 대표 카테고리명 (is_primary 카테고리의 현지화 이름) |
| categories_with_path | array | `[]` | 소속 카테고리 목록 + 경로 (각 항목: id·breadcrumb path·대표 여부) |
| brand_name | string | `CJ제일제당` | 브랜드명 (연관 브랜드의 현지화 이름) |
| shipping_policy_id | integer | `31` | shipping policy 식별자 (연관 리소스 참조) |
| min_purchase_qty | integer | `1` | 최소 구매 수량 (1회 주문 시 이 수량 이상 구매) |
| max_purchase_qty | integer | `0` | 최대 구매 수량 (0=무제한) |
| has_options | boolean | `false` | options 여부 |
| labels | array | `[]` | 노출 중인 상품 라벨 목록 (각 항목: 라벨명·색상, 활성 라벨을 sort_order 순으로 정렬) |
| review_count | integer | `0` | review 개수 (집계) |
| rating_avg | integer | `0` | 평균 별점 (공개 리뷰 별점 평균, 소수 1자리 반올림) |
| created_at | string | `2026-07-07 14:47:31` | 생성 일시 |
| updated_at | string | `2026-07-07 14:47:31` | 최종 수정 일시 |
| is_owner | boolean | `false` | 현재 인증 사용자가 이 리소스의 소유자인지 여부 (BaseApiResource 표준 메타) |
| abilities | object | `{"can_update":true,"can_delete":true}` | 현재 사용자가 이 리소스에 수행 가능한 작업 불리언 맵 (can_update, can_delete 등 — 권한 맵 기반) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 쇼핑몰 프런트용 공개 상품 목록을 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, `ProductService::getPublicList()`가 전시상태 visible이고 판매상태가 on_sale 또는 coming_soon인 상품만 반환합니다. 카테고리/브랜드/검색어/가격 범위 필터와 `sort`(latest/sales/price_asc/price_desc) 정렬을 지원하고 결과는 `ProductCollection`으로 페이지네이션됩니다. 확장은 `sirsoft-ecommerce.product.public_list_validation_rules` 훅으로 필터를 추가할 수 있습니다.


### GET /api/modules/sirsoft-ecommerce/products/new
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.new -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.new`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductController@new`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| limit | query | integer | 아니오 | min 1, max 50 | 반환할 최대 항목 수 |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.public_new_validation_rules`).

**응답 필드** (`data` 내부)

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| id | integer | `322` | 기본 키 (내부 식별자) |
| name | object | `{"ko":"eum et quia","en":"tenetur id quae"}` | 대상의 이름/명칭 (다국어 필드는 로케일별 값 객체) |
| name_localized | string | `eum et quia` | `name` 의 현재 로케일 해석 값 (다국어 필드를 표시용 문자열로 해석) |
| product_code | string | `PROD-GJUX-1484` | 상품코드 (상품 고유 관리 식별자) |
| sku | string | `SKU-MRAD-9306` | 재고관리코드(SKU) |
| thumbnail_url | string | `/api/modules/sirsoft-ecommerce/produc…` | thumbnail URL |
| list_price | integer | `112594` | 정가 (기본통화 자릿수로 정규화된 값) |
| list_price_formatted | string | `112,594원` | `list_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| selling_price | integer | `88949` | 판매가 (기본통화 자릿수로 정규화된 값) |
| selling_price_formatted | string | `88,949원` | `selling_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| discount_rate | integer | `21` | 할인율(%) (정가 대비 판매가 할인 비율, (1 - 판매가/정가) × 100) |
| multi_currency_list_price | object | `{"KRW":{"price":112594,"formatted":"112,594원","is_default…` | 통화별 정가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 정가) |
| multi_currency_selling_price | object | `{"KRW":{"price":88949,"formatted":"88,949원","is_default":…` | 통화별 판매가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 판매가) |
| stock_quantity | integer | `22` | 재고 수량 (옵션 사용 시 옵션 재고 합계) |
| safe_stock_quantity | integer | `12` | 안전재고 수량 (이 값 미만이면 재고 부족으로 표시) |
| is_below_safe_stock | boolean | `false` | below safe stock 여부 |
| sales_status | string | `on_sale` | 판매상태 값 (on_sale 판매중 / suspended 판매중지 / sold_out 품절 / coming_soon 출시예정) |
| sales_status_label | string | `판매중` | `sales_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| sales_status_variant | string | `success` | `sales_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| display_status | string | `visible` | 전시상태 값 (visible 전시 / hidden 숨김) |
| display_status_label | string | `전시` | `display_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| display_status_variant | string | `success` | `display_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| categories | array | `[]` | 소속 카테고리 목록 (각 항목: id·현지화 이름·대표 여부. categories 관계 eager load 시에만 채워짐) |
| primary_category | string | `스마트폰` | 대표 카테고리명 (is_primary 카테고리의 현지화 이름) |
| categories_with_path | array | `[]` | 소속 카테고리 목록 + 경로 (각 항목: id·breadcrumb path·대표 여부) |
| shipping_policy_id | integer | `31` | shipping policy 식별자 (연관 리소스 참조) |
| min_purchase_qty | integer | `1` | 최소 구매 수량 (1회 주문 시 이 수량 이상 구매) |
| max_purchase_qty | integer | `0` | 최대 구매 수량 (0=무제한) |
| has_options | boolean | `false` | options 여부 |
| labels | array | `[]` | 노출 중인 상품 라벨 목록 (각 항목: 라벨명·색상, 활성 라벨을 sort_order 순으로 정렬) |
| review_count | integer | `0` | review 개수 (집계) |
| rating_avg | integer | `0` | 평균 별점 (공개 리뷰 별점 평균, 소수 1자리 반올림) |
| created_at | string | `2026-07-07 14:47:31` | 생성 일시 |
| updated_at | string | `2026-07-07 14:47:31` | 최종 수정 일시 |
| is_owner | boolean | `false` | 현재 인증 사용자가 이 리소스의 소유자인지 여부 (BaseApiResource 표준 메타) |
| abilities | object | `{"can_update":true,"can_delete":true}` | 현재 사용자가 이 리소스에 수행 가능한 작업 불리언 맵 (can_update, can_delete 등 — 권한 맵 기반) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 공개 신상품 목록을 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, `ProductService::getNewProducts()`가 최신 등록순으로 상품을 정렬해 `ProductListResource` 컬렉션으로 반환합니다. `limit`(기본 10, 최대 50) 쿼리로 개수를 제한하며, 메인 페이지의 신상품 섹션 등에 사용됩니다. 확장은 `sirsoft-ecommerce.product.public_new_validation_rules` 훅으로 검증 규칙을 확장할 수 있습니다.


### GET /api/modules/sirsoft-ecommerce/products/popular
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.popular -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.popular`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductController@popular`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| limit | query | integer | 아니오 | min 1, max 50 | 반환할 최대 항목 수 |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.public_popular_validation_rules`).

**응답 필드** (`data` 내부)

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| id | integer | `204` | 기본 키 (내부 식별자) |
| name | object | `{"ko":"베이직 라운드 티셔츠 #4","en":"Basic Round T-Shirt #4"}` | 대상의 이름/명칭 (다국어 필드는 로케일별 값 객체) |
| name_localized | string | `베이직 라운드 티셔츠 #4` | `name` 의 현재 로케일 해석 값 (다국어 필드를 표시용 문자열로 해석) |
| product_code | string | `2R9AKHR0GH2DR3NG` | 상품코드 (상품 고유 관리 식별자) |
| sku | string | `TS-0004` | 재고관리코드(SKU) |
| thumbnail_url | string | `/api/modules/sirsoft-ecommerce/produc…` | thumbnail URL |
| list_price | integer | `29000` | 정가 (기본통화 자릿수로 정규화된 값) |
| list_price_formatted | string | `29,000원` | `list_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| selling_price | integer | `22000` | 판매가 (기본통화 자릿수로 정규화된 값) |
| selling_price_formatted | string | `22,000원` | `selling_price` 값의 표시용 포맷 문자열 (통화/용량/일시 등 로케일·단위 포맷) |
| discount_rate | number | `24.1` | 할인율(%) (정가 대비 판매가 할인 비율, (1 - 판매가/정가) × 100) |
| multi_currency_list_price | object | `{"KRW":{"price":29000,"formatted":"29,000원","is_default":…` | 통화별 정가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 정가) |
| multi_currency_selling_price | object | `{"KRW":{"price":22000,"formatted":"22,000원","is_default":…` | 통화별 판매가 맵 (통화코드 → {price, formatted, is_default, editable}, 설정된 모든 통화의 환산 판매가) |
| stock_quantity | integer | `264` | 재고 수량 (옵션 사용 시 옵션 재고 합계) |
| safe_stock_quantity | integer | `15` | 안전재고 수량 (이 값 미만이면 재고 부족으로 표시) |
| is_below_safe_stock | boolean | `false` | below safe stock 여부 |
| sales_status | string | `on_sale` | 판매상태 값 (on_sale 판매중 / suspended 판매중지 / sold_out 품절 / coming_soon 출시예정) |
| sales_status_label | string | `판매중` | `sales_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| sales_status_variant | string | `success` | `sales_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| display_status | string | `visible` | 전시상태 값 (visible 전시 / hidden 숨김) |
| display_status_label | string | `전시` | `display_status` 값의 사람이 읽는 라벨 (현지화/Enum 파생) |
| display_status_variant | string | `success` | `display_status` 값의 표시 변형 키 (UI 배지 색상/스타일) |
| categories | array | `[{"id":109,"name":"식품","is_primary":0},{"id":113,"name":"…` | 소속 카테고리 목록 (각 항목: id·현지화 이름·대표 여부) |
| primary_category | string | `해산물` | 대표 카테고리명 (is_primary 카테고리의 현지화 이름) |
| categories_with_path | array | `[{"id":109,"path":[{"id":109,"name":"식품","slug":"food"}],…` | 소속 카테고리 목록 + 경로 (각 항목: id·breadcrumb path·대표 여부) |
| shipping_policy_id | integer | `31` | shipping policy 식별자 (연관 리소스 참조) |
| min_purchase_qty | integer | `1` | 최소 구매 수량 (1회 주문 시 이 수량 이상 구매) |
| max_purchase_qty | integer | `0` | 최대 구매 수량 (0=무제한) |
| has_options | boolean | `true` | options 여부 |
| labels | array | `[]` | 노출 중인 상품 라벨 목록 (각 항목: 라벨명·색상, 활성 라벨을 sort_order 순으로 정렬) |
| review_count | integer | `0` | review 개수 (집계) |
| rating_avg | integer | `0` | 평균 별점 (공개 리뷰 별점 평균, 소수 1자리 반올림) |
| created_at | string | `2026-06-15 11:24:15` | 생성 일시 |
| updated_at | string | `2026-06-15 11:24:15` | 최종 수정 일시 |
| is_owner | boolean | `false` | 현재 인증 사용자가 이 리소스의 소유자인지 여부 (BaseApiResource 표준 메타) |
| abilities | object | `{"can_update":true,"can_delete":true}` | 현재 사용자가 이 리소스에 수행 가능한 작업 불리언 맵 (can_update, can_delete 등 — 권한 맵 기반) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 공개 인기 상품 목록을 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, `ProductService::getPopularProducts()`가 최근 30일 판매량 기준으로 정렬한 상품을 `ProductListResource` 컬렉션으로 반환합니다. `limit`(기본 10, 최대 50) 쿼리로 개수를 제한하며, 베스트/인기 상품 위젯에 사용됩니다. 확장은 `sirsoft-ecommerce.product.public_popular_validation_rules` 훅으로 검증 규칙을 확장할 수 있습니다.


### GET /api/modules/sirsoft-ecommerce/products/recent
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.recent -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.recent`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductController@recent`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| ids | query | string | 아니오 | max 500 | 대상 리소스 식별자 배열 (대량 작업 대상) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.product.public_recent_validation_rules`).

**응답 필드** (`data` 내부)



<!-- 실측 응답에 필드 없음(빈 목록 등) — 데이터가 있는 상태로 재실측하거나 사람이 작성. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 최근 본 상품 목록을 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, 클라이언트가 로컬에 보관한 조회 이력 상품 ID들을 쉼표 구분 문자열(`ids`)로 전달하면 컨트롤러가 정수 배열로 파싱해 `ProductService::getProductsByIds()`로 조회한 뒤 `ProductListResource` 컬렉션을 반환합니다. `ids`가 비어 있으면 빈 배열을 반환하며, 확장은 `sirsoft-ecommerce.product.public_recent_validation_rules` 훅으로 검증 규칙을 확장할 수 있습니다.


### GET /api/modules/sirsoft-ecommerce/products/{id}
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.show -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.show`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductController@show`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 쇼핑몰 프런트용 공개 상품 상세를 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, `ProductService::getDetail()`로 상품을 로드하되 전시상태가 visible이 아니면 404를 반환합니다. 상세 페이지에 필요한 배송정책·상품고시·공통정보·브랜드·라벨·추가옵션·현재 사용자 위시리스트 관계를 추가 로드하고 `PublicProductResource`로 반환하며, 응답에는 다중통화 가격·배송비 안내(`shipping_fee_formatted`)·`is_wishlisted` 등 프런트 표시용 파생 필드가 포함됩니다.


### GET /api/modules/sirsoft-ecommerce/products/{productId}/downloadable-coupons
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.downloadable-coupons -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.downloadable-coupons`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\PublicCouponController@downloadableCoupons`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| productId | path | string | 예 | — | 대상 product의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 특정 상품에서 다운로드 가능한 쿠폰 목록을 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, `UserCouponService::getProductDownloadableCoupons()`가 해당 상품에 적용 가능한 발급 대기 쿠폰을 반환합니다. 로그인 상태면 사용자 ID를 함께 넘겨 각 쿠폰의 `is_downloaded`(이미 받았는지) 여부를 채워주고, 다중통화 혜택·최소주문금액(`multi_currency_benefit_formatted` 등)이 포함됩니다. 상품 상세의 쿠폰 받기 영역에 사용됩니다.


### GET /api/modules/sirsoft-ecommerce/products/{productId}/inquiries
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.inquiries.index -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.inquiries.index`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductInquiryController@index`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| productId | path | string | 예 | — | 대상 product의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품의 1:1 문의 목록을 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, `ProductInquiryService::getProductInquiries()`가 게시판 모듈과 연동된 문의 글을 페이지네이션해 `items`와 `board_settings`(비밀글 모드·카테고리 등) 메타를 반환합니다. `per_page`/`page`/`exclude_secret` 쿼리로 조회 범위를 조정하며, 비밀 문의는 설정과 열람 권한에 따라 마스킹됩니다. 상품 상세의 문의 탭에 사용됩니다.


### POST /api/modules/sirsoft-ecommerce/products/{productId}/inquiries
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.inquiries.store -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.inquiries.store`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductInquiryController@store`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| productId | path | string | 예 | — | 대상 product의 식별자 |
| title | body | string | 아니오 | — | 제목 |
| category | body | string | 아니오 | — | 문의 분류 (게시판 설정에 정의된 카테고리, 미지정 시 기본값) |
| content | body | string | 예 | — | 본문 내용 |
| is_secret | body | boolean | 아니오 | — | secret 여부 |
| temp_key | body | string | 아니오 | — | 첨부파일 임시 업로드 키 (사전 업로드한 첨부를 이 문의에 연결) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.inquiry.store_validation_rules`, `sirsoft-ecommerce.inquiry.store_validation_messages`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품에 1:1 문의를 작성합니다. `optional.sanctum` + `sirsoft-ecommerce.user-products.read` 권한이 적용되며(선택적 인증 표면이지만 실제 작성은 인증 사용자를 전제), `ProductInquiryService::createInquiry()`가 게시판 모듈과 연동해 문의 글을 생성하고 성공 시 201과 생성된 `id`를 반환합니다. `content`는 필수, `title`/`category`/`is_secret`은 선택이며, 첨부는 사전 업로드한 `temp_key`로 연결됩니다. 도메인 규칙 위반(비밀글 비허용 등)은 `RuntimeException`으로 422를 반환합니다.


### GET /api/modules/sirsoft-ecommerce/products/{productId}/reviews
<!-- @generated:start:api.modules.sirsoft-ecommerce.products.reviews.index -->
- **라우트명**: `api.modules.sirsoft-ecommerce.products.reviews.index`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\Public\ProductReviewController@index`
- **인증/권한**: `optional.sanctum` (선택적 인증: 회원/비회원 모두 접근) + `permission:sirsoft-ecommerce.user-products.read`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| productId | path | string | 예 | — | 대상 product의 식별자 |
| sort | query | string | 아니오 | `created_at_desc`, `created_at_asc`, `rating_desc`, `rating_asc` | 정렬 기준 (필드명, `-` 접두 시 내림차순) |
| photo_only | query | string | 아니오 | `0`, `1`, `true`, `false` | 포토리뷰만 필터 (true 시 사진이 첨부된 리뷰만 조회) |
| page | query | integer | 아니오 | min 1 | 조회할 페이지 번호 (1부터 시작) |
| per_page | query | integer | 아니오 | min 1, max 50 | 페이지당 항목 수 |
| rating | query | integer | 아니오 | `1`, `2`, `3`, `4`, `5` | 별점 필터 (지정한 평점의 리뷰만 조회) |
| option_filters | query | string | 아니오 | — | 옵션 조건 필터 (JSON 문자열로 전달, 서버에서 배열로 파싱해 특정 옵션 구매 리뷰만 조회) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.review.public_list_validation_rules`, `sirsoft-ecommerce.review.public_list_validation_messages`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 403 | Forbidden | 요구 권한(`sirsoft-ecommerce.user-products.read`)이 없는 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 상품의 공개 리뷰 목록과 별점 통계를 조회합니다. `optional.sanctum`(회원/비회원 모두 접근) + `sirsoft-ecommerce.user-products.read` 권한이 적용되며, `ProductReviewService::getProductReviews()`가 정렬(`sort`)·포토리뷰만(`photo_only`)·별점(`rating`)·옵션(`option_filters`) 필터를 적용해 리뷰를 페이지네이션하고 별점 분포(`rating_stats`)와 선택 가능한 옵션 필터, 총 개수를 함께 반환합니다. `option_filters`는 JSON 문자열로 전달되면 서버에서 배열로 파싱되며, 상품 상세의 리뷰 탭에 사용됩니다. 확장은 `sirsoft-ecommerce.review.public_list_validation_rules` 훅으로 필터를 추가할 수 있습니다.


