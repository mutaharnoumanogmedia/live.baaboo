<?php

namespace Tests\Feature\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

trait AssertsDirectControllerResponses
{
  /**
   * @param  array<string, mixed>  $expectedSubset
   */
    protected function assertJsonResponse(Response $response, int $status, array $expectedSubset = []): array
    {
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($status, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertIsArray($data);

        foreach ($expectedSubset as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertSame($value, $data[$key]);
        }

        return $data;
    }

    protected function assertRedirectResponse(Response $response, string $expectedUrl): void
    {
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertTrue($response->isRedirect());
        $this->assertSame($expectedUrl, $response->getTargetUrl());
    }

    protected function assertViewResponse(View|Response $response, string $expectedViewName): View
    {
        $this->assertInstanceOf(View::class, $response);
        $this->assertSame($expectedViewName, $response->name());

        return $response;
    }
}
