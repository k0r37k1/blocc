<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Stringable;
use Symfony\Component\HttpFoundation\Response;

class ApplyPublicCacheHeaders
{
    /**
     * Route-name => cache-control option string (same syntax as cache.headers).
     *
     * @var array<string, string>
     */
    private array $routes = [
        'blog.show' => 'public;max_age=120;stale_while_revalidate=600;etag',
        'category.show' => 'public;max_age=300;stale_while_revalidate=3600;etag',
        'tag.show' => 'public;max_age=300;stale_while_revalidate=3600;etag',
        'page.show' => 'public;max_age=300;stale_while_revalidate=3600;etag',
        'sitemap' => 'public;max_age=300;stale_while_revalidate=3600;etag',
        'feed' => 'public;max_age=300;stale_while_revalidate=3600;etag',
    ];

    private ?Request $request = null;

    private ?Response $response = null;

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->request = $request;
        $this->response = $next($request);

        return $this->response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->apply($request, $response);
    }

    private function apply(Request $request, Response $response): void
    {
        if ($request->user() !== null) {
            return;
        }

        $routeName = $request->route()?->getName();

        if ($routeName === null || ! isset($this->routes[$routeName])) {
            return;
        }

        if (! $request->isMethodCacheable() || ! $response->isSuccessful()) {
            return;
        }

        $content = $response->getContent();

        if ($content === false || $content === '') {
            return;
        }

        $options = $this->parseOptions($this->routes[$routeName]);

        if (isset($options['etag']) && $options['etag'] === true) {
            $options['etag'] = hash('xxh128', $content);
        }

        if (isset($options['last_modified'])) {
            $options['last_modified'] = is_numeric($options['last_modified'])
                ? Carbon::createFromTimestamp((int) $options['last_modified'], date_default_timezone_get())
                : Carbon::parse($options['last_modified']);
        }

        $response->setCache($options);
    }

    /**
     * @return array<string, bool|string>
     */
    private function parseOptions(string $options): array
    {
        return (new Stringable(rtrim($options, ';')))->explode(';')->mapWithKeys(function (string $option): array {
            $data = explode('=', $option, 2);

            return [$data[0] => $data[1] ?? true];
        })->all();
    }
}
