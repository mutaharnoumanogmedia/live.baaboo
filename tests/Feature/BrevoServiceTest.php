<?php

namespace Tests\Feature;

use App\Services\BrevoService;
use Database\Seeders\TestLiveShowSeeder;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

/**
 * Exercises {@see BrevoService} directly against a fake PSR-18 HTTP client.
 *
 * The Brevo PHP SDK talks to https://api.brevo.com/v3 through a PSR-18 client,
 * not Laravel's Http facade, so instead of Http::fake() we inject our own
 * capturing client via the SDK's `options['client']` hook. This mirrors the
 * "mock the API response, assert the request" style of the reference test
 * while staying faithful to how this app actually sends mail.
 */
class BrevoServiceTest extends TestCase
{
    /**
     * Run on the isolated in-memory SQLite database and seed the shared
     * winner-email fixture so this test uses the same environment as the rest
     * of the winner-email suite. BrevoService itself is DB-agnostic, so the
     * seeded data is simply the common baseline.
     */
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestLiveShowSeeder::class);
    }

    /**
     * Build a BrevoService whose underlying SDK uses the given fake client.
     */
    private function serviceWith(ClientInterface $fakeClient): BrevoService
    {
        return new BrevoService('test-api-key', ['client' => $fakeClient]);
    }

    /**
     * A PSR-18 client that records the last request and returns a canned response.
     */
    private function fakeClient(ResponseInterface $response): ClientInterface
    {
        return new class($response) implements ClientInterface
        {
            public ?RequestInterface $lastRequest = null;

            public int $callCount = 0;

            public function __construct(private ResponseInterface $response) {}

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;
                $this->callCount++;

                return $this->response;
            }
        };
    }

    /** @test */
    public function it_sends_an_email_successfully_and_returns_a_message_id(): void
    {
        // 1. ARRANGE: fake a successful Brevo API response.
        $client = $this->fakeClient(new Response(
            201,
            ['Content-Type' => 'application/json'],
            json_encode(['messageId' => 'abc123xyz789_mocked'])
        ));

        // 2. ACT: send through the real service using the "winners" sender preset.
        $result = $this->serviceWith($client)->send(
            to: 'test@example.com',
            subject: 'Welcome to our platform!',
            htmlContent: '<p>Hello winner!</p>',
            sender: 'winners',
        );

        // 3. ASSERT: the service reports success and surfaces the message id.
        $this->assertTrue($result['success']);
        $this->assertSame(201, $result['status_code']);
        $this->assertSame('abc123xyz789_mocked', $result['message_id']);
        $this->assertNull($result['error']);

        // Double check the outgoing request hit the right endpoint with the right data.
        $this->assertSame(1, $client->callCount);
        $this->assertSame('POST', $client->lastRequest->getMethod());
        $this->assertSame(
            'https://api.brevo.com/v3/smtp/email',
            (string) $client->lastRequest->getUri()
        );
        $this->assertSame('test-api-key', $client->lastRequest->getHeaderLine('api-key'));

        $body = json_decode((string) $client->lastRequest->getBody(), true);
        $this->assertSame('test@example.com', $body['to'][0]['email']);
        $this->assertSame('Welcome to our platform!', $body['subject']);
        // The "winners" preset from config/brevo.php must be resolved as the sender.
        $this->assertSame('winners@badabing.show', $body['sender']['email']);
    }

    /** @test */
    public function it_handles_brevo_api_errors_gracefully(): void
    {
        // 1. ARRANGE: fake a Brevo API validation failure.
        $client = $this->fakeClient(new Response(
            400,
            ['Content-Type' => 'application/json'],
            json_encode([
                'code' => 'invalid_parameter',
                'message' => 'The email address is invalid',
            ])
        ));

        // 2. ACT: send an email that Brevo rejects.
        $result = $this->serviceWith($client)->send(
            to: 'bad-email-format',
            subject: 'Welcome to our platform!',
            htmlContent: '<p>Hello!</p>',
            sender: 'winners',
        );

        // 3. ASSERT: the failure is swallowed and reported, never thrown.
        $this->assertFalse($result['success']);
        $this->assertSame(400, $result['status_code']);
        $this->assertNull($result['message_id']);
        $this->assertStringContainsString('The email address is invalid', $result['error']);
    }

    /** @test */
    public function a_scheduled_email_is_reported_with_the_202_accepted_status(): void
    {
        // Brevo returns the send/schedule payload the same way; the service
        // distinguishes a scheduled send (scheduledAt set) with a 202 status.
        $client = $this->fakeClient(new Response(
            201,
            ['Content-Type' => 'application/json'],
            json_encode(['messageId' => 'scheduled_msg_001'])
        ));

        $result = $this->serviceWith($client)->send(
            to: 'winner@example.com',
            subject: 'You won!',
            htmlContent: '<p>Congrats!</p>',
            sender: 'winners',
            scheduledAt: new \DateTime('+30 minutes'),
        );

        $this->assertTrue($result['success']);
        $this->assertSame(202, $result['status_code']);
        $this->assertSame('scheduled_msg_001', $result['message_id']);

        // The scheduledAt must be forwarded to Brevo in the request body.
        $body = json_decode((string) $client->lastRequest->getBody(), true);
        $this->assertArrayHasKey('scheduledAt', $body);
    }
}
