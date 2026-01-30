<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\RouterCommentHelper;
use App\Models\Customer;
use App\Models\NetworkUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouterCommentHelperTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_ispbills_format_comment_for_network_user(): void
    {
        $user = new NetworkUser();
        $user->id = 123;
        $user->username = 'testuser';
        $user->name = 'Test User';
        $user->mobile = '01712345678';
        $user->zone_id = 5;
        $user->package_id = 10;
        $user->expiry_date = now()->addDays(30);
        $user->status = 'active';
        $user->exists = true; // Mark as existing to get ID

        $comment = RouterCommentHelper::getComment($user);

        // Should contain all key parts
        $this->assertStringContainsString('uid--123', $comment);
        $this->assertStringContainsString('name--Test User', $comment);
        $this->assertStringContainsString('mobile--01712345678', $comment);
        $this->assertStringContainsString('zone--5', $comment);
        $this->assertStringContainsString('pkg--10', $comment);
        $this->assertStringContainsString('status--active', $comment);
    }

    /** @test */
    public function it_parses_ispbills_format_comments(): void
    {
        $comment = 'uid--123,name--Test User,mobile--01712345678,zone--5,pkg--10,exp--2026-12-31,status--active';

        $parsed = RouterCommentHelper::parseComment($comment);

        $this->assertEquals('123', $parsed['uid']);
        $this->assertEquals('Test User', $parsed['name']);
        $this->assertEquals('01712345678', $parsed['mobile']);
        $this->assertEquals('5', $parsed['zone']);
        $this->assertEquals('10', $parsed['pkg']);
        $this->assertEquals('2026-12-31', $parsed['exp']);
        $this->assertEquals('active', $parsed['status']);
    }

    /** @test */
    public function it_extracts_user_id_from_comment(): void
    {
        $comment = 'uid--123,name--Test User,mobile--01712345678';

        $userId = RouterCommentHelper::extractUserId($comment);

        $this->assertEquals(123, $userId);
    }
}
