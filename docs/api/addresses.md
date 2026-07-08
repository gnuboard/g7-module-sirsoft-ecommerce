# Addresses API 레퍼런스

> **소유**: module `sirsoft-ecommerce` · **생성**: `php artisan api:docgen` (실측 기반). @generated 블록은 재생성 시 갱신되며, 사람이 작성한 설명은 보존됩니다.

---

## TL;DR (5초 요약)

```text
1. 이 문서는 실제 API 호출로 실측한 Addresses 엔드포인트 레퍼런스입니다
2. 각 엔드포인트: 메서드/URI/권한 + 요청 파라미터 표 + 실측 응답 필드 표
3. 응답 필드의 예시값은 실제 호출 응답에서 관측된 값입니다
4. 갱신: 코드 변경 후 php artisan api:docgen 재실행
5. 설명(TODO) 칸은 사람이 채웁니다
```

---


### GET /api/modules/sirsoft-ecommerce/user/addresses
<!-- @generated:start:api.modules.sirsoft-ecommerce.user.addresses.index -->
- **라우트명**: `api.modules.sirsoft-ecommerce.user.addresses.index`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\User\UserAddressController@index`
- **인증/권한**: `auth:sanctum`

**요청 파라미터**

_요청 파라미터 없음._

**응답 필드** (`data` 내부)

_단건 응답: `data` 객체의 필드._

| 필드 | 타입 | 실측 예시값 | 용도/설명 |
| --- | --- | --- | --- |
| addresses | object | `{"data":[{"id":263,"user_id":"a1e0a91a-fba6-491c-a53e-728…` | 회원 본인 소유 배송지 컬렉션 (`data[]` 배송지 항목 배열 + `abilities.can_create` — UserAddressCollection 파생) |

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |

<!-- @generated:end -->

**설명** 로그인한 회원 본인의 배송지 목록을 조회합니다. `auth:sanctum` 인증이 필요하며, `UserAddressService::getUserAddresses()`가 현재 사용자(`Auth::id()`) 소유의 배송지를 조회해 `UserAddressCollection`으로 반환합니다. 마이페이지 배송지 관리 화면이나 주문 시 배송지 선택 목록을 채우는 용도이며, 다른 회원의 배송지는 노출되지 않습니다.


### POST /api/modules/sirsoft-ecommerce/user/addresses
<!-- @generated:start:api.modules.sirsoft-ecommerce.user.addresses.store -->
- **라우트명**: `api.modules.sirsoft-ecommerce.user.addresses.store`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\User\UserAddressController@store`
- **인증/권한**: `auth:sanctum`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| name | body | string | 예 | max 100 | 대상의 이름/명칭 |
| recipient_name | body | string | 예 | max 50 | 수령인 이름 |
| recipient_phone | body | string | 예 | max 20 | 수령인 연락처 |
| country_code | body | string | 아니오 | — | 국가 코드 (ISO 3166-1 alpha-2) |
| zipcode | body | string | 아니오 | max 10 | 우편번호 |
| province_code | body | string | 아니오 | max 10 | 광역 시·도 코드 (국내 주소 지역 구분) |
| city | body | string | 아니오 | max 100 | 시·군·구 등 도시명 |
| address | body | string | 아니오 | max 255 | 기본 주소 |
| address_detail | body | string | 아니오 | max 255 | 상세 주소 |
| address_type_code | body | string | 아니오 | `R`, `J` | 국내 주소 표기 방식 (`R` 도로명 / `J` 지번) |
| address_line_1 | body | string | 아니오 | max 255 | 주소 1행 (기본 주소) |
| address_line_2 | body | string | 아니오 | max 255 | 주소 2행 (상세 주소) |
| intl_city | body | string | 아니오 | max 100 | 도시 (국제 주소) |
| intl_state | body | string | 아니오 | max 100 | 주/도 (국제 주소) |
| intl_postal_code | body | string | 아니오 | max 20 | 우편번호 (국제 주소) |
| is_default | body | boolean | 아니오 | — | 기본값 지정 여부 |
| force_overwrite | body | boolean | 아니오 | — | 동일 배송지명 존재 시 기존 항목 덮어쓰기 허용 (미지정 시 중복이면 409) |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.user_address.store_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |

<!-- @generated:end -->

**설명** 로그인한 회원 본인의 새 배송지를 등록합니다. `auth:sanctum` 인증이 필요하며, `UserAddressService::createAddress()`가 검증된 요청에 현재 사용자 ID를 결합해 배송지를 생성하고 성공 시 `201`로 반환합니다. 국내(우편번호/도로명·지번)·해외(intl_* 필드) 주소를 모두 지원하고 `is_default`로 기본 배송지 지정이 가능합니다. 같은 이름의 배송지가 있으면 `409`(중복 ID 포함)를, `force_overwrite`로 덮어쓰기를 허용할 수 있으며, 최대 배송지 개수를 초과하면 `422`를 반환합니다.


### DELETE /api/modules/sirsoft-ecommerce/user/addresses/{id}
<!-- @generated:start:api.modules.sirsoft-ecommerce.user.addresses.destroy -->
- **라우트명**: `api.modules.sirsoft-ecommerce.user.addresses.destroy`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\User\UserAddressController@destroy`
- **인증/권한**: `auth:sanctum`

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
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 로그인한 회원 본인의 배송지 1건을 삭제합니다. `auth:sanctum` 인증이 필요하며, `UserAddressService::deleteAddress()`가 현재 사용자 소유 여부를 확인한 뒤 path의 `{id}` 배송지를 삭제합니다. 본인 소유가 아니거나 존재하지 않는 배송지이면 `404`를 반환합니다. 마이페이지 배송지 관리에서 더 이상 사용하지 않는 배송지를 제거하는 용도입니다.


### GET /api/modules/sirsoft-ecommerce/user/addresses/{id}
<!-- @generated:start:api.modules.sirsoft-ecommerce.user.addresses.show -->
- **라우트명**: `api.modules.sirsoft-ecommerce.user.addresses.show`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\User\UserAddressController@show`
- **인증/권한**: `auth:sanctum`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |

**응답 필드** (`data` 내부)

<!-- 실측 제외: unresolved-path-param — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 로그인한 회원 본인의 배송지 1건 상세를 조회합니다. `auth:sanctum` 인증이 필요하며, `UserAddressService::getAddress()`가 현재 사용자 소유의 path `{id}` 배송지를 조회해 `UserAddressResource`로 반환합니다. 본인 소유가 아니거나 존재하지 않는 배송지이면 `404`를 반환합니다. 배송지 수정 화면 진입 시 기존 값을 불러오는 용도입니다.


### PUT /api/modules/sirsoft-ecommerce/user/addresses/{id}
<!-- @generated:start:api.modules.sirsoft-ecommerce.user.addresses.update -->
- **라우트명**: `api.modules.sirsoft-ecommerce.user.addresses.update`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\User\UserAddressController@update`
- **인증/권한**: `auth:sanctum`

**요청 파라미터**

| 이름 | 위치 | 타입 | 필수 | 허용값 | 용도 |
| --- | --- | --- | --- | --- | --- |
| id | path | string | 예 | — | 대상 리소스의 식별자 |
| name | body | string | 아니오 | max 100 | 대상의 이름/명칭 |
| recipient_name | body | string | 아니오 | max 50 | 수령인 이름 |
| recipient_phone | body | string | 아니오 | max 20 | 수령인 연락처 |
| country_code | body | string | 아니오 | — | 국가 코드 (ISO 3166-1 alpha-2) |
| zipcode | body | string | 아니오 | max 10 | 우편번호 |
| province_code | body | string | 아니오 | max 10 | 광역 시·도 코드 (국내 주소 지역 구분) |
| city | body | string | 아니오 | max 100 | 시·군·구 등 도시명 |
| address | body | string | 아니오 | max 255 | 기본 주소 |
| address_detail | body | string | 아니오 | max 255 | 상세 주소 |
| address_type_code | body | string | 아니오 | `R`, `J` | 국내 주소 표기 방식 (`R` 도로명 / `J` 지번) |
| address_line_1 | body | string | 아니오 | max 255 | 주소 1행 (기본 주소) |
| address_line_2 | body | string | 아니오 | max 255 | 주소 2행 (상세 주소) |
| intl_city | body | string | 아니오 | max 100 | 도시 (국제 주소) |
| intl_state | body | string | 아니오 | max 100 | 주/도 (국제 주소) |
| intl_postal_code | body | string | 아니오 | max 20 | 우편번호 (국제 주소) |
| is_default | body | boolean | 아니오 | — | 기본값 지정 여부 |

> 이 엔드포인트는 확장이 파라미터를 추가할 수 있습니다 (`sirsoft-ecommerce.user_address.update_validation_rules`).

**응답 필드** (`data` 내부)

<!-- 실측 제외: write-method — 응답 필드는 사람이 작성하세요. -->

**에러 응답**

| 상태코드 | 의미 | 발생 조건 |
| --- | --- | --- |
| 401 | Unauthenticated | 유효한 Bearer 토큰이 없거나 만료된 경우 |
| 422 | Unprocessable Entity | 요청 파라미터가 검증 규칙을 위반한 경우 (`error.errors` 에 필드별 메시지) |
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 로그인한 회원 본인의 배송지 1건을 수정합니다. `auth:sanctum` 인증이 필요하며, `UserAddressService::updateAddress()`가 현재 사용자 소유의 path `{id}` 배송지를 검증된 값으로 갱신하고 `UserAddressResource`로 반환합니다. 모든 본문 필드는 선택이며 전달된 필드만 갱신되고, 국내·해외 주소 필드와 `is_default`(기본 배송지 지정)를 모두 지원합니다. 본인 소유가 아니거나 존재하지 않는 배송지이면 `404`를 반환합니다.


### PATCH /api/modules/sirsoft-ecommerce/user/addresses/{id}/default
<!-- @generated:start:api.modules.sirsoft-ecommerce.user.addresses.set-default -->
- **라우트명**: `api.modules.sirsoft-ecommerce.user.addresses.set-default`
- **컨트롤러**: `Modules\Sirsoft\Ecommerce\Http\Controllers\User\UserAddressController@setDefault`
- **인증/권한**: `auth:sanctum`

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
| 404 | Not Found | path 파라미터에 해당하는 리소스가 없는 경우 |

<!-- @generated:end -->

**설명** 로그인한 회원 본인의 배송지 1건을 기본 배송지로 지정합니다. `auth:sanctum` 인증이 필요하며, `UserAddressService::setDefaultAddress()`가 현재 사용자 소유의 path `{id}` 배송지를 기본으로 설정하고 기존 기본 배송지는 자동 해제됩니다. 본인 소유가 아니거나 존재하지 않는 배송지이면 `404`를 반환합니다. 마이페이지 배송지 목록에서 기본 배송지를 전환하는 용도입니다.


