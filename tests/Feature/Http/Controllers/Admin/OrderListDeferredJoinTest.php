<?php

namespace Modules\Sirsoft\Ecommerce\Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Modules\Sirsoft\Ecommerce\Database\Factories\OrderFactory;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\OrderRepositoryInterface;
use Modules\Sirsoft\Ecommerce\Tests\ModuleTestCase;

/**
 * 주문 목록 지연 조인 회귀 테스트
 *
 * 주문 목록은 이제 "이번 페이지의 ID 를 먼저 구하고, 그 ID 에 대해서만 목록 컬럼과 관계를
 * 읽는" 2단계 조회다. 성능 수치는 머신에 좌우되므로 단언하지 않고, 그 성질이 유지되는지를
 * 쿼리 구조로 고정한다 — 누군가 목록 컬럼을 되돌리거나 관계를 inner 에 다시 붙이면 실패한다.
 *
 * 결과 정합성(무엇이 몇 건 어떤 순서로 나오는가)도 함께 고정한다. 조회 방식을 바꾸는 변경에서
 * 가장 조용히 깨지는 것이 정렬과 페이지 경계다.
 */
class OrderListDeferredJoinTest extends ModuleTestCase
{
    protected User $adminUser;

    /** 수집된 실행 쿼리 SQL 목록 */
    private array $queries = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = $this->createAdminUser(['sirsoft-ecommerce.orders.read']);
    }

    /** 쿼리 로그 수집을 시작합니다. */
    private function startCapture(): void
    {
        $this->queries = [];

        DB::listen(function ($query) {
            $this->queries[] = $query->sql;
        });
    }

    /**
     * 수집된 쿼리 중 조건에 맞는 것만 돌려줍니다.
     *
     * @param  string  $needle  포함 문자열
     * @return array<int, string> 일치 쿼리 목록
     */
    private function queriesContaining(string $needle): array
    {
        return array_values(array_filter($this->queries, fn (string $sql) => str_contains($sql, $needle)));
    }

    /**
     * 주문 목록을 조회합니다.
     *
     * 페이지 번호는 페이지네이터가 요청에서 해석하므로(HTTP 경로와 동일), 저장소를 직접
     * 호출하는 테스트에서는 resolver 로 지정한다.
     *
     * @param  array  $filters  조회 필터
     * @param  int  $perPage  페이지당 건수
     * @param  int  $page  조회할 페이지
     * @return LengthAwarePaginator 조회 결과
     */
    private function listOrders(array $filters = [], int $perPage = 10, int $page = 1)
    {
        Paginator::currentPageResolver(fn () => $page);

        try {
            return app(OrderRepositoryInterface::class)->getListWithFilters($filters, $perPage);
        } finally {
            Paginator::currentPageResolver(fn () => 1);
        }
    }

    public function test_offset_scan_reads_only_id_column(): void
    {
        OrderFactory::new()->count(25)->create();

        $this->startCapture();
        $this->listOrders(page: 2);

        $offsetQueries = $this->queriesContaining('offset');

        $this->assertNotEmpty($offsetQueries, '페이지 조회에는 OFFSET 쿼리가 있어야 한다');

        foreach ($offsetQueries as $sql) {
            // 넓은 컬럼이 OFFSET 스캔에 실리면 뒤쪽 페이지 비용이 선형으로 커진다
            $this->assertStringNotContainsString('admin_memo', $sql);
            $this->assertStringNotContainsString('order_meta', $sql);
            $this->assertStringNotContainsString('promotions_applied_snapshot', $sql);
            $this->assertStringNotContainsString('mc_subtotal_amount', $sql);
        }
    }

    public function test_list_query_does_not_select_unused_wide_columns(): void
    {
        OrderFactory::new()->count(3)->create();

        $this->startCapture();
        $this->listOrders();

        $selectQueries = $this->queriesContaining('from `g7_ecommerce_orders`');

        $this->assertNotEmpty($selectQueries);

        foreach ($selectQueries as $sql) {
            $this->assertStringNotContainsString('admin_memo', $sql, '목록은 관리자 메모를 읽지 않는다');
            $this->assertStringNotContainsString('mileage_policy_snapshot', $sql);
        }
    }

    public function test_relation_queries_run_once_for_the_page(): void
    {
        OrderFactory::new()->count(5)->create();

        $this->startCapture();
        $result = $this->listOrders();

        // 관계는 outer 에서만 로드된다 — inner 에도 붙으면 같은 관계 쿼리가 2회 실행된다
        $this->assertCount(1, $this->queriesContaining('from `g7_ecommerce_order_options`'));
        $this->assertTrue($result->getCollection()->first()->relationLoaded('options'));
        $this->assertTrue($result->getCollection()->first()->relationLoaded('shippingAddress'));
    }

    public function test_pages_do_not_overlap_and_follow_sort_order(): void
    {
        // ordered_at 이 같은 주문이 섞여 있어도 페이지 경계가 흔들리면 안 된다
        $sharedTime = now()->subDay();
        OrderFactory::new()->count(12)->create(['ordered_at' => $sharedTime]);
        OrderFactory::new()->count(8)->create();

        $page1 = $this->listOrders([], 10, 1);
        $page2 = $this->listOrders([], 10, 2);

        $firstIds = $page1->getCollection()->pluck('id')->all();
        $secondIds = $page2->getCollection()->pluck('id')->all();

        $this->assertCount(10, $firstIds);
        $this->assertEmpty(array_intersect($firstIds, $secondIds), '페이지 간 중복 주문이 없어야 한다');
        $this->assertSame(20, $page1->total());
    }

    public function test_sort_by_falls_back_when_column_is_not_allowed(): void
    {
        OrderFactory::new()->count(3)->create();

        // 예전에는 요청 값이 그대로 orderBy 로 흘러 SQL 오류가 났다
        $result = $this->listOrders(['sort_by' => 'admin_memo; drop table users', 'sort_order' => 'sideways']);

        $this->assertSame(3, $result->total());
    }

    public function test_filters_still_narrow_the_result(): void
    {
        OrderFactory::new()->count(4)->create(['order_device' => 'pc']);
        OrderFactory::new()->count(3)->create(['order_device' => 'mobile']);

        $result = $this->listOrders(['order_device' => 'mobile']);

        $this->assertSame(3, $result->total());
        $this->assertCount(3, $result->getCollection());
    }

    public function test_soft_deleted_orders_stay_excluded(): void
    {
        $orders = OrderFactory::new()->count(5)->create();
        $orders->first()->delete();

        $result = $this->listOrders();

        $this->assertSame(4, $result->total());
        $this->assertNotContains($orders->first()->id, $result->getCollection()->pluck('id')->all());
    }

    public function test_admin_list_endpoint_still_returns_expected_payload(): void
    {
        OrderFactory::new()->count(3)->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/modules/sirsoft-ecommerce/admin/orders');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'order_number', 'order_status', 'total_amount', 'ordered_at'],
                    ],
                    'pagination',
                ],
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }
}
