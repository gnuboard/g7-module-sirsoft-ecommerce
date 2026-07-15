<?php

declare(strict_types=1);

namespace Modules\Sirsoft\Ecommerce\Tests\Unit\Providers;

use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Modules\Sirsoft\Ecommerce\Http\Middleware\DetectDevice;
use Modules\Sirsoft\Ecommerce\Tests\ModuleTestCase;

/**
 * DetectDevice 미들웨어가 실제 요청 파이프라인의 web 그룹에 등록되는지 검증.
 *
 * 회귀: EcommerceServiceProvider 가 Router::pushMiddlewareToGroup('web', ...) 로 등록하면
 * HTTP Kernel 이 참조하는 web 그룹에 반영되지 않아(Router 자체 레지스트리만 갱신) SSR 셸
 * 요청에서 미들웨어가 실행되지 않는다. 그 결과 appConfig.isIos 가 항상 fail-safe false 가 되어
 * 애플페이 iOS 게이팅 서버층이 동작하지 않는다. Kernel::appendMiddlewareToGroup 을 써야 한다.
 */
class DetectDeviceMiddlewareRegistrationTest extends ModuleTestCase
{
    /**
     * @scenario mark_form=badge, requires_ios=true, device=non_ios
     *
     * @effects server_ua_sets_is_ios
     */
    public function test_detect_device_is_registered_in_kernel_web_group(): void
    {
        $kernel = $this->app->make(HttpKernelContract::class);
        $this->assertInstanceOf(HttpKernel::class, $kernel);

        // HTTP Kernel 이 실제 요청 처리 시 참조하는 web 그룹을 조회한다.
        $webGroup = $kernel->getMiddlewareGroups()['web'] ?? [];

        $this->assertContains(
            DetectDevice::class,
            $webGroup,
            'DetectDevice 미들웨어가 HTTP Kernel 의 web 그룹에 등록되어야 SSR 셸 요청에서 실행된다.'
        );
    }
}
