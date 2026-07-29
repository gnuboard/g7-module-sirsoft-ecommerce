<?php

namespace Modules\Sirsoft\Ecommerce\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 상품 복사용 데이터 조회 요청 (관리자)
 *
 * 권한은 라우트 미들웨어(permission:admin,sirsoft-ecommerce.products.create)에서 처리하며,
 * 여기서는 어떤 항목을 함께 복사할지 고르는 플래그만 검증합니다.
 */
class ShowProductForCopyRequest extends FormRequest
{
    /**
     * 복사 대상 항목과 미지정 시 기본값
     *
     * SEO 정보만 기본 제외 — 메타 제목·설명은 상품마다 달라야 검색 결과에서 구분된다.
     */
    private const COPY_TARGETS = [
        'images' => true,
        'options' => true,
        'categories' => true,
        'sales_info' => true,
        'description' => true,
        'notice' => true,
        'common_info' => true,
        'other_info' => true,
        'shipping' => true,
        'seo' => false,
        'identification' => true,
    ];

    /**
     * 사용자가 이 요청을 수행할 권한이 있는지 확인 (권한은 미들웨어에서 처리)
     *
     * @return bool 항상 true
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 요청에 적용할 검증 규칙
     *
     * @return array<string, array<int, mixed>> 검증 규칙 배열
     */
    public function rules(): array
    {
        $rules = [];

        foreach (array_keys(self::COPY_TARGETS) as $target) {
            $rules["copy_{$target}"] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    /**
     * 복사 옵션을 반환합니다.
     *
     * @return array<string, bool> 항목별 복사 여부
     */
    public function getCopyOptions(): array
    {
        $options = [];

        foreach (self::COPY_TARGETS as $target => $default) {
            $options[$target] = $this->boolean("copy_{$target}", $default);
        }

        return $options;
    }
}
