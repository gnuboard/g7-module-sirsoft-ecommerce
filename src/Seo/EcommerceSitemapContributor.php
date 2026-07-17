<?php

namespace Modules\Sirsoft\Ecommerce\Seo;

use App\Seo\Contracts\SitemapContributorInterface;
use Illuminate\Support\Facades\Log;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Sirsoft\Ecommerce\Repositories\Contracts\ProductRepositoryInterface;

/**
 * Ecommerce 모듈 Sitemap 기여자
 *
 * 상품 및 카테고리 URL을 sitemap에 제공합니다.
 * 데이터 접근은 Repository 인터페이스에 위임합니다.
 */
class EcommerceSitemapContributor implements SitemapContributorInterface
{
    /**
     * EcommerceSitemapContributor 생성자
     *
     * @param  CategoryRepositoryInterface  $categoryRepository  카테고리 Repository
     * @param  ProductRepositoryInterface  $productRepository  상품 Repository
     */
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * 확장 식별자를 반환합니다.
     *
     * @return string 확장 식별자
     */
    public function getIdentifier(): string
    {
        return 'sirsoft-ecommerce';
    }

    /**
     * Sitemap URL 항목 배열을 반환합니다.
     *
     * 상품 목록, 카테고리별 페이지, 개별 상품 페이지의 URL을 생성합니다.
     *
     * @return array<int, array{url: string, lastmod?: string, changefreq?: string, priority?: float}>
     */
    public function getUrls(): array
    {
        $urls = [];
        $routePath = g7_module_settings('sirsoft-ecommerce', 'basic_info.route_path') ?? 'shop';

        // 기여자당 URL 안전 상한 (0 = 무제한). 상한은 이 기여자가 내보내는 모든 URL 유형에
        // 적용된다 — 특정 루프에만 걸면 다른 유형이 상한을 넘겨버린다.
        $maxUrls = (int) g7_core_settings('seo.sitemap_max_urls_per_contributor', 0);
        $truncated = false;

        // 상품 목록 페이지 — 'SEO 제공 페이지' 토글 OFF 시 제외
        if ((bool) g7_module_settings('sirsoft-ecommerce', 'seo.seo_shop_index', true)) {
            $truncated = ! $this->appendUrl($urls, [
                'url' => "/{$routePath}/products",
                'changefreq' => 'daily',
                'priority' => 0.7,
            ], $maxUrls);
        }

        // 카테고리별 페이지 — 토글 OFF 시 제외
        if (! $truncated && (bool) g7_module_settings('sirsoft-ecommerce', 'seo.seo_category', true)) {
            foreach ($this->categoryRepository->streamActiveForSitemap() as $category) {
                if (! $this->appendUrl($urls, [
                    'url' => "/{$routePath}/category/{$category->slug}",
                    'lastmod' => $category->updated_at?->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => 0.6,
                ], $maxUrls)) {
                    $truncated = true;
                    break;
                }
            }
        }

        // 개별 상품 페이지 (전시 상태가 '전시'인 상품만) — 토글 OFF 시 제외
        if (! $truncated && (bool) g7_module_settings('sirsoft-ecommerce', 'seo.seo_product_detail', true)) {
            foreach ($this->productRepository->streamVisibleForSitemap() as $product) {
                if (! $this->appendUrl($urls, [
                    'url' => "/{$routePath}/products/{$product->id}",
                    'lastmod' => $product->updated_at?->toW3cString(),
                    'changefreq' => 'weekly',
                    'priority' => 0.8,
                ], $maxUrls)) {
                    $truncated = true;
                    break;
                }
            }
        }

        if ($truncated) {
            Log::warning('[SEO] Sitemap 기여자 URL 상한 초과로 잘렸습니다.', [
                'contributor' => $this->getIdentifier(),
                'max_urls' => $maxUrls,
            ]);
        }

        return $urls;
    }

    /**
     * 안전 상한을 지키며 URL 항목을 추가합니다.
     *
     * @param  array<int, array<string, mixed>>  $urls  누적 URL 배열 (참조)
     * @param  array<string, mixed>  $entry  추가할 URL 항목
     * @param  int  $maxUrls  기여자당 URL 상한 (0 = 무제한)
     * @return bool 추가되면 true, 상한 도달로 추가하지 못하면 false
     */
    private function appendUrl(array &$urls, array $entry, int $maxUrls): bool
    {
        if ($maxUrls > 0 && count($urls) >= $maxUrls) {
            return false;
        }

        $urls[] = $entry;

        return true;
    }
}
