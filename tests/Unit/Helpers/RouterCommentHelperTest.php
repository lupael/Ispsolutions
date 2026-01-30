<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\RouterCommentHelper;
use App\Models\NetworkUser;
use App\Models\User;
use Mockery;
use Tests\TestCase;

class RouterCommentHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_generates_ispbills_format_comment_for_network_user_with_customer(): void
    {
        // Mock customer (User model) - allow attribute setting
        $customer = Mockery::mock(User::class)->makePartial();
        $customer->shouldAllowMockingProtectedMethods();
        $customer->id = 100;
        $customer->shouldReceive('getAttribute')->with('id')->andReturn(100);
        $customer->shouldReceive('getAttribute')->with('name')->andReturn('Test User');
        $customer->shouldReceive('getAttribute')->with('mobile')->andReturn('01712345678');
        $customer->shouldReceive('getAttribute')->with('zone_id')->andReturn(5);
        $customer->shouldReceive('getAttribute')->with('phone')->andReturn(null);

        // Mock network user with related customer
        $user = Mockery::mock(NetworkUser::class)->makePartial();
        $user->shouldAllowMockingProtectedMethods();
        $user->id = 456;
        $user->user_id = 100;
        $user->username = 'testuser';
        $user->package_id = 10;
        $user->expiry_date = now()->addDays(30);
        $user->status = 'active';
        $user->shouldReceive('relationLoaded')->with('user')->andReturn(true);
        $user->shouldReceive('getAttribute')->with('user')->andReturn($customer);

        $comment = RouterCommentHelper::getComment($user);

        // Should contain customer info
        $this->assertStringContainsString('uid--100', $comment);
        $this->assertStringContainsString('nid--456', $comment);
        $this->assertStringContainsString('name--Test User', $comment);
        $this->assertStringContainsString('mobile--01712345678', $comment);
        $this->assertStringContainsString('zone--5', $comment);
        $this->assertStringContainsString('pkg--10', $comment);
        $this->assertStringContainsString('status--active', $comment);
    }

    /** @test */
    public function it_generates_comment_for_network_user_without_customer(): void
    {
        // Mock network user without customer relationship
        $user = Mockery::mock(NetworkUser::class)->makePartial();
        $user->id = 789;
        $user->username = 'standalone_user';
        $user->user_id = null;
        $user->package_id = 5;
        $user->zone_id = 3;
        $user->status = 'active';
        $user->expiry_date = null;
        $user->shouldReceive('relationLoaded')->with('user')->andReturn(false);

        $comment = RouterCommentHelper::getComment($user);

        // Should fall back to network user data
        $this->assertStringContainsString('uid--789', $comment);
        $this->assertStringContainsString('nid--789', $comment);
        $this->assertStringContainsString('name--standalone_user', $comment);
        $this->assertStringContainsString('mobile--N/A', $comment);
        $this->assertStringContainsString('zone--3', $comment);
    }

    /** @test */
    public function it_parses_ispbills_format_comments(): void
    {
        $comment = 'uid--123,nid--456,name--Test User,mobile--01712345678,zone--5,pkg--10,exp--2026-12-31,status--active';

        $parsed = RouterCommentHelper::parseComment($comment);

        $this->assertEquals('123', $parsed['uid']);
        $this->assertEquals('456', $parsed['nid']);
        $this->assertEquals('Test User', $parsed['name']);
        $this->assertEquals('01712345678', $parsed['mobile']);
        $this->assertEquals('5', $parsed['zone']);
        $this->assertEquals('10', $parsed['pkg']);
        $this->assertEquals('2026-12-31', $parsed['exp']);
        $this->assertEquals('active', $parsed['status']);
    }

    /** @test */
    public function it_parses_legacy_pipe_format_comments(): void
    {
        $comment = 'testuser|456|10|2026-12-31|pppoe';

        $parsed = RouterCommentHelper::parseComment($comment);

        // Legacy format parsing
        $this->assertEquals('testuser', $parsed['username']);
        $this->assertEquals('456', $parsed['user_id']);
        $this->assertEquals('10', $parsed['package_id']);
        $this->assertEquals('2026-12-31', $parsed['expiry_date']);
        $this->assertEquals('pppoe', $parsed['service_type']);
    }

    /** @test */
    public function it_extracts_user_id_from_ispbills_comment(): void
    {
        $comment = 'uid--123,nid--456,name--Test User,mobile--01712345678';

        $userId = RouterCommentHelper::extractUserId($comment);

        $this->assertEquals(123, $userId);
    }

    /** @test */
    public function it_extracts_user_id_from_legacy_comment(): void
    {
        $comment = 'testuser|456|10|2026-12-31|pppoe';

        $userId = RouterCommentHelper::extractUserId($comment);

        $this->assertEquals(456, $userId);
    }

    /** @test */
    public function it_extracts_mobile_from_comment(): void
    {
        $comment = 'uid--123,name--Test User,mobile--01712345678,status--active';

        $mobile = RouterCommentHelper::extractMobile($comment);

        $this->assertEquals('01712345678', $mobile);
    }

    /** @test */
    public function it_detects_expired_users_from_comment(): void
    {
        $expiredComment = 'uid--123,name--Test User,exp--2020-01-01,status--active';
        $activeComment = 'uid--456,name--Active User,exp--2030-12-31,status--active';

        $this->assertTrue(RouterCommentHelper::isExpired($expiredComment));
        $this->assertFalse(RouterCommentHelper::isExpired($activeComment));
    }

    /** @test */
    public function it_handles_comments_without_expiry_date(): void
    {
        $comment = 'uid--123,name--Test User,mobile--01712345678,status--active';

        // Should not throw and should return false
        $this->assertFalse(RouterCommentHelper::isExpired($comment));
    }

    /** @test */
    public function it_generates_legacy_format_with_build_user_comment(): void
    {
        $user = Mockery::mock(NetworkUser::class)->makePartial();
        $user->username = 'testuser';
        $user->user_id = 456;
        $user->package_id = 10;
        $user->expiry_date = now()->addDays(30);
        $user->service_type = 'pppoe';

        $comment = RouterCommentHelper::buildUserComment($user);

        // Should be pipe-separated
        $parts = explode('|', $comment);
        $this->assertCount(5, $parts);
        $this->assertEquals('testuser', $parts[0]);
        $this->assertEquals('456', $parts[1]);
        $this->assertEquals('10', $parts[2]);
    }

    /** @test */
    public function it_sanitizes_special_characters_in_names(): void
    {
        // Mock customer with special characters
        $customer = Mockery::mock(User::class)->makePartial();
        $customer->shouldAllowMockingProtectedMethods();
        $customer->id = 100;
        $customer->shouldReceive('getAttribute')->with('id')->andReturn(100);
        $customer->shouldReceive('getAttribute')->with('name')->andReturn('Test,User--With;Special');
        $customer->shouldReceive('getAttribute')->with('mobile')->andReturn('01712345678');
        $customer->shouldReceive('getAttribute')->with('phone')->andReturn(null);
        $customer->shouldReceive('getAttribute')->with('zone_id')->andReturn(null);

        // Mock network user
        $user = Mockery::mock(NetworkUser::class)->makePartial();
        $user->shouldAllowMockingProtectedMethods();
        $user->id = 456;
        $user->user_id = 100;
        $user->username = 'testuser';
        $user->shouldReceive('relationLoaded')->with('user')->andReturn(true);
        $user->shouldReceive('getAttribute')->with('user')->andReturn($customer);

        $comment = RouterCommentHelper::getComment($user);

        // Should not contain raw special characters
        $this->assertStringNotContainsString(',,', $comment);
        $this->assertStringNotContainsString('---', $comment);
        $this->assertStringNotContainsString(';;', $comment);
    }
}
