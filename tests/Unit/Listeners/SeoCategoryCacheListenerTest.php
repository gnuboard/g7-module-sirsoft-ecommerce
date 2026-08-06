<?php

namespace Modules\Sirsoft\Ecommerce\Tests\Unit\Listeners;

use App\Jobs\GenerateSitemapJob;
use App\Seo\Contracts\SeoCacheManagerInterface;
use App\Seo\SitemapIndexer;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Sirsoft\Ecommerce\Listeners\SeoCategoryCacheListener;
use Modules\Sirsoft\Ecommerce\Tests\ModuleTestCase;

/**
 * SeoCategoryCacheListener 테스트
 *
 * 카테고리 변경 시 SEO 캐시 무효화 리스너의 동작을 검증합니다.
 */
class SeoCategoryCacheListenerTest extends ModuleTestCase
{
    protected SeoCategoryCacheListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        // 사이트맵 색인 경로는 이 테스트 범위 밖 — spy 로 대체하고 잡을 fake 하여 DB/큐 부작용을 차단
        $this->app->instance(SitemapIndexer::class, Mockery::spy(SitemapIndexer::class));
        Bus::fake([GenerateSitemapJob::class]);

        $this->listener = new SeoCategoryCacheListener;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ========================================
    // getSubscribedHooks() 테스트
    // ========================================

    /**
     * 리스너가 올바른 훅-메서드 매핑을 반환하는지 확인 (3개 훅 모두 onCategoryChange)
     */
    public function test_get_subscribed_hooks_returns_correct_mapping(): void
    {
        $hooks = SeoCategoryCacheListener::getSubscribedHooks();

        // create/update 는 onCategoryChange, delete 는 onCategoryDelete (모델 대신 ID 를 받으므로 분리)
        $changeHooks = [
            'sirsoft-ecommerce.category.after_create',
            'sirsoft-ecommerce.category.after_update',
        ];

        foreach ($changeHooks as $hookName) {
            $this->assertArrayHasKey($hookName, $hooks);
            $this->assertEquals('onCategoryChange', $hooks[$hookName]['method']);
            $this->assertEquals(20, $hooks[$hookName]['priority']);
        }

        $this->assertArrayHasKey('sirsoft-ecommerce.category.after_delete', $hooks);
        $this->assertEquals('onCategoryDelete', $hooks['sirsoft-ecommerce.category.after_delete']['method']);
        $this->assertEquals(20, $hooks['sirsoft-ecommerce.category.after_delete']['priority']);

        $this->assertCount(3, $hooks);
    }

    // ========================================
    // onCategoryChange() 테스트
    // ========================================

    /**
     * 카테고리 변경 시 shop/category, shop/index, home, search/index 레이아웃 캐시가 무효화되는지 확인
     */
    public function test_on_category_change_invalidates_shop_home_and_search_layouts(): void
    {
        // Given: 카테고리 객체 Mock
        $category = new \stdClass;
        $category->id = 10;

        // SeoCacheManagerInterface Mock
        $invokedLayouts = [];
        $mockCache = $this->createMock(SeoCacheManagerInterface::class);
        $mockCache->expects($this->exactly(4))
            ->method('invalidateByLayout')
            ->willReturnCallback(function (string $layout) use (&$invokedLayouts) {
                $invokedLayouts[] = $layout;

                return 1;
            });

        $this->app->instance(SeoCacheManagerInterface::class, $mockCache);

        Log::shouldReceive('debug')->once();

        // When
        $this->listener->onCategoryChange($category);

        // Then: 4개 레이아웃이 모두 무효화되어야 함
        $this->assertContains('shop/category', $invokedLayouts);
        $this->assertContains('shop/index', $invokedLayouts);
        $this->assertContains('home', $invokedLayouts);
        $this->assertContains('search/index', $invokedLayouts);
    }

    /**
     * 카테고리가 객체인 경우 $category->id를 사용하는지 확인
     */
    public function test_on_category_change_handles_object_category(): void
    {
        // Given: 카테고리 객체
        $category = new \stdClass;
        $category->id = 25;

        $mockCache = $this->createMock(SeoCacheManagerInterface::class);
        $mockCache->method('invalidateByLayout')->willReturn(1);

        $this->app->instance(SeoCacheManagerInterface::class, $mockCache);

        // Log::debug에서 category_id가 올바르게 전달되는지 확인
        Log::shouldReceive('debug')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, '[SEO]')
                    && $context['category_id'] === 25;
            });

        // When
        $this->listener->onCategoryChange($category);

        // 검증은 Log::shouldReceive 기대치(Mockery::close)로 수행됨
        $this->addToAssertionCount(1);
    }

    /**
     * 카테고리가 스칼라 ID인 경우 그대로 사용하는지 확인
     */
    public function test_on_category_change_handles_scalar_category_id(): void
    {
        // Given: 스칼라 카테고리 ID
        $categoryId = 77;

        $mockCache = $this->createMock(SeoCacheManagerInterface::class);
        $mockCache->method('invalidateByLayout')->willReturn(1);

        $this->app->instance(SeoCacheManagerInterface::class, $mockCache);

        // Log::debug에서 category_id가 스칼라 값으로 전달되는지 확인
        Log::shouldReceive('debug')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, '[SEO]')
                    && $context['category_id'] === 77;
            });

        // When
        $this->listener->onCategoryChange($categoryId);

        // 검증은 Log::shouldReceive 기대치(Mockery::close)로 수행됨
        $this->addToAssertionCount(1);
    }

    /**
     * 카테고리가 null인 경우에도 graceful하게 처리하는지 확인
     */
    public function test_on_category_change_handles_null_category_gracefully(): void
    {
        // Given: SeoCacheManagerInterface Mock (정상 동작)
        $mockCache = $this->createMock(SeoCacheManagerInterface::class);
        $mockCache->expects($this->exactly(4))
            ->method('invalidateByLayout')
            ->willReturn(1);

        $this->app->instance(SeoCacheManagerInterface::class, $mockCache);

        // category_id가 null로 전달되어야 함
        Log::shouldReceive('debug')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, '[SEO]')
                    && $context['category_id'] === null;
            });

        // When & Then: 예외 없이 동작
        $this->listener->onCategoryChange(null);
    }

    /**
     * 캐시 무효화 중 예외 발생 시 graceful하게 처리하는지 확인
     */
    public function test_on_category_change_handles_exceptions_gracefully(): void
    {
        // Given: 카테고리 객체
        $category = new \stdClass;
        $category->id = 33;

        // 예외를 발생시키는 Mock
        $mockCache = $this->createMock(SeoCacheManagerInterface::class);
        $mockCache->method('invalidateByLayout')
            ->willThrowException(new \RuntimeException('Redis connection refused'));

        $this->app->instance(SeoCacheManagerInterface::class, $mockCache);

        // Log::warning 호출 확인
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, '[SEO]')
                    && $context['error'] === 'Redis connection refused';
            });

        // When & Then: 예외가 전파되지 않음
        $this->listener->onCategoryChange($category);

        // 검증은 Log::shouldReceive 기대치(Mockery::close)로 수행됨
        $this->addToAssertionCount(1);
    }

    // ========================================
    // handle() 메서드 테스트
    // ========================================

    /**
     * handle 메서드가 존재하는지 확인 (HookListenerInterface 준수)
     */
    public function test_handle_method_exists_for_interface_compliance(): void
    {
        $this->assertTrue(
            method_exists($this->listener, 'handle'),
            'handle() 메서드가 HookListenerInterface 준수를 위해 존재해야 합니다'
        );

        // 호출해도 예외 없이 동작하는지 확인
        $this->listener->handle();
        $this->assertTrue(true);
    }
}
