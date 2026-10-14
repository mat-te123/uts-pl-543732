<?php

declare(strict_types=1);

/**
 * VacantTest.php
 *
 * PHPUnit 12 integration tests for the Vacant API (calls a RUNNING server).
 *
 *   php artisan serve            # in another terminal
 *   ./vendor/bin/phpunit tests/Feature/VacantTest.php
 *
 * Optional: BASE_URL=http://host:port/api/ ./vendor/bin/phpunit ...
 */

namespace Tests\Feature;

use GuzzleHttp\Client;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class VacantTest extends TestCase
{
    // ── Shared state (persists across tests in this class) ──────────────────

    private static ?string $token = null;
    private static ?int $createdVacantId = null;
    private static ?int $userId = null;

    /** Unique per run so "email already taken" (422) can't happen. */
    private static string $email = '';
    private static string $password = 'Tono12345!';

    public static function setUpBeforeClass(): void
    {
        self::$email = 'tono+' . uniqid() . '@mail.com';
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getClient(): Client
    {
        $baseUrl = rtrim(getenv('BASE_URL') ?: 'http://127.0.0.1:8000/api/', '/') . '/';

        return new Client([
            'base_uri'    => $baseUrl,
            'http_errors' => false, // let assertions inspect 4xx/5xx responses
            'headers'     => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function decodeJson(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true) ?? [];
    }

    /** @return array<string, string> */
    private function authHeader(): array
    {
        return ['Authorization' => 'Bearer ' . self::$token];
    }

    /** Failure message that includes the real status code and body. */
    private function msg(string $label, ResponseInterface $response): string
    {
        return sprintf('%s -> HTTP %d: %s', $label, $response->getStatusCode(), (string) $response->getBody());
    }

    /** Reads the first numeric value found at any of the given key paths. */
    private function findInt(array $body, array $paths): ?int
    {
        foreach ($paths as $path) {
            $value = $body;
            foreach ($path as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) {
                    $value = null;
                    break;
                }
                $value = $value[$key];
            }
            if (is_numeric($value)) {
                return (int) $value;
            }
        }
        return null;
    }

    /** Vacant id from the usual response shapes. */
    private function extractId(array $body): ?int
    {
        return $this->findInt($body, [['data', 'id'], ['vacant', 'id'], ['vacants', 'id'], ['id']]);
    }

    /** User id from the usual response shapes. */
    private function extractUserId(array $body): ?int
    {
        return $this->findInt($body, [
            ['user', 'id'],
            ['data', 'user', 'id'],
            ['data', 'id'],
            ['user_id'],
            ['id'],
        ]);
    }

    /** Asserts the body has a status/message style key (case-insensitive, accepts "Massage"). */
    private function assertHasStatusKey(string $label, ResponseInterface $response): void
    {
        $keys = array_map('strtolower', array_keys($this->decodeJson($response)));

        $this->assertNotEmpty(
            array_intersect($keys, ['success', 'status', 'message', 'massage']),
            $this->msg("{$label}: unexpected body", $response)
        );
    }

    // ── Tests ────────────────────────────────────────────────────────────────

    /** POST /user/register */
    public function testRegister(): void
    {
        $response = $this->getClient()->post('user/register', [
            'json' => [
                'name'     => 'TONO',
                'email'    => self::$email,
                'password' => self::$password,
            ],
        ]);

        $this->assertContains($response->getStatusCode(), [200, 201], $this->msg('Register', $response));

        $body = $this->decodeJson($response);
        $this->assertTrue(
            isset($body['message']) || isset($body['success']) || isset($body['data']) || isset($body['user']),
            $this->msg('Register: body needs message/success/data/user', $response)
        );

        // Remember the user id (if the API returns it) so the user can be deleted at the end
        self::$userId = $this->extractUserId($body);
    }

    /** POST /user/login */
    #[Depends('testRegister')]
    public function testLogin(): void
    {
        $response = $this->getClient()->post('user/login', [
            'json' => [
                'email'    => self::$email,
                'password' => self::$password,
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode(), $this->msg('Login', $response));

        $body  = $this->decodeJson($response);
        $token = $body['access_token'] ?? $body['token'] ?? $body['data']['token'] ?? null;

        $this->assertNotEmpty($token, $this->msg('Login: no token in response', $response));

        self::$token = (string) $token;

        // Fallback: take the user id from the login response if register didn't give one
        self::$userId ??= $this->extractUserId($body);
    }

    /** GET /vacants (sent with token, works whether the route is public or protected) */
    #[Depends('testLogin')]
    public function testGetVacants(): void
    {
        $response = $this->getClient()->get('vacants', ['headers' => $this->authHeader()]);

        $this->assertSame(200, $response->getStatusCode(), $this->msg('GetVacants', $response));

        $body = $this->decodeJson($response);
        $list = $body['vacants'] ?? $body['data'] ?? $body;

        $this->assertIsArray($list, $this->msg('GetVacants: expected a list', $response));
    }

    /** POST /vacant/post */
    #[Depends('testLogin')]
    public function testAddVacant(): void
    {
        $client   = $this->getClient();
        $response = $client->post('vacant/post', [
            'headers' => $this->authHeader(),
            'json'    => [
                'vacant_name' => 'AI ENG',
                'vacant_desc' => 'NVIDIA',
                'vacant_link' => 'https://www.youtube.com/watch?v=p7Igl9dwOWE',
            ],
        ]);

        $this->assertContains($response->getStatusCode(), [200, 201], $this->msg('AddVacant', $response));

        $id = $this->extractId($this->decodeJson($response));

        // Fallback: the API didn't return the id, so find it in the list
        if ($id === null) {
            $list = $this->decodeJson($client->get('vacants', ['headers' => $this->authHeader()]));
            $list = $list['vacants'] ?? $list['data'] ?? $list;

            foreach ($list as $item) {
                if (($item['vacant_name'] ?? null) === 'AI ENG') {
                    $id = max($id ?? 0, (int) $item['id']);
                }
            }
        }

        $this->assertNotNull($id, $this->msg('AddVacant: could not determine the new vacant id', $response));

        self::$createdVacantId = $id;
    }

    /** PUT /vacant/{id} */
    #[Depends('testAddVacant')]
    public function testUpdateVacant(): void
    {
        $id = self::$createdVacantId;
        $this->assertNotNull($id, 'UpdateVacant: no vacant id from testAddVacant');

        $response = $this->getClient()->put("vacant/{$id}", [
            'headers' => $this->authHeader(),
            'json'    => [
                'vacant_name' => 'App developer',
                'vacant_desc' => 'APPLE',
                'vacant_link' => 'https://www.youtube.com/watch?v=yye7rSsiV6k',
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode(), $this->msg("UpdateVacant (id {$id})", $response));

        $body = $this->decodeJson($response);
        $this->assertTrue(
            isset($body['success']) || isset($body['data']) || isset($body['message']) || isset($body['vacant']),
            $this->msg('UpdateVacant: unexpected body', $response)
        );
    }

    /** DELETE /vacant/{id} */
    #[Depends('testUpdateVacant')]
    public function testDeleteVacant(): void
    {
        $id = self::$createdVacantId;
        $this->assertNotNull($id, 'DeleteVacant: no vacant id from testAddVacant');

        $response = $this->getClient()->delete("vacant/{$id}", ['headers' => $this->authHeader()]);

        $this->assertSame(200, $response->getStatusCode(), $this->msg("DeleteVacant (id {$id})", $response));
        $this->assertHasStatusKey('DeleteVacant', $response);
    }

    /** DELETE /user/{id}: cleans up the user created by this run (must stay last) */
    #[Depends('testLogin')]
    public function testDeleteUser(): void
    {
        $id = self::$userId;
        $this->assertNotNull(
            $id,
            'DeleteUser: no user id found. Make /user/register (or /user/login) return the user id, e.g. {"user":{"id":1,...}}'
        );

        $response = $this->getClient()->delete("user/{$id}", ['headers' => $this->authHeader()]);

        $this->assertSame(200, $response->getStatusCode(), $this->msg("DeleteUser (id {$id})", $response));
        $this->assertHasStatusKey('DeleteUser', $response);
    }
}