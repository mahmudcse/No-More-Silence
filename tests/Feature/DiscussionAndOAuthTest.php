<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\Report;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('anyone can view comments for a report', function (): void {
    $report = Report::factory()->create();
    $user = User::factory()->create(['name' => 'Ayesha']);

    Comment::create([
        'report_id' => $report->id,
        'user_id' => $user->id,
        'content' => 'This is a test public comment.',
        'likes_count' => 0,
        'is_hidden' => false,
    ]);

    $response = getJson(route('comments.index', ['reportUuid' => $report->uuid]));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'content',
                    'likes_count',
                    'is_liked',
                    'created_at',
                    'user' => ['id', 'name'],
                    'replies',
                ],
            ],
            'total',
        ])
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.content', 'This is a test public comment.');
});

test('unauthenticated users cannot post comments', function (): void {
    $report = Report::factory()->create();

    $response = postJson(route('comments.store', ['reportUuid' => $report->uuid]), [
        'content' => 'Attempting comment without auth',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can post a comment on a report', function (): void {
    $report = Report::factory()->create();
    $user = User::factory()->create(['name' => 'Tanvir']);

    $response = actingAs($user)->postJson(route('comments.store', ['reportUuid' => $report->uuid]), [
        'content' => 'I stand in solidarity with this report.',
    ]);

    $response->assertCreated()
        ->assertJsonPath('content', 'I stand in solidarity with this report.')
        ->assertJsonPath('user.name', 'Tanvir');

    assertDatabaseHas('comments', [
        'report_id' => $report->id,
        'user_id' => $user->id,
        'content' => 'I stand in solidarity with this report.',
    ]);
});

test('authenticated user can reply to a comment', function (): void {
    $report = Report::factory()->create();
    $author = User::factory()->create(['name' => 'Author']);
    $replier = User::factory()->create(['name' => 'Replier']);

    $comment = Comment::create([
        'report_id' => $report->id,
        'user_id' => $author->id,
        'content' => 'Top level comment',
        'likes_count' => 0,
        'is_hidden' => false,
    ]);

    $response = actingAs($replier)->postJson(route('comments.store', ['reportUuid' => $report->uuid]), [
        'content' => 'This is a reply to the top level comment.',
        'parent_id' => $comment->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('parent_id', $comment->id)
        ->assertJsonPath('content', 'This is a reply to the top level comment.');

    assertDatabaseHas('comments', [
        'parent_id' => $comment->id,
        'user_id' => $replier->id,
    ]);
});

test('authenticated user can toggle like on a comment', function (): void {
    $report = Report::factory()->create();
    $author = User::factory()->create();
    $liker = User::factory()->create();

    $comment = Comment::create([
        'report_id' => $report->id,
        'user_id' => $author->id,
        'content' => 'Comment to like',
        'likes_count' => 0,
        'is_hidden' => false,
    ]);

    // Like
    $res1 = actingAs($liker)->postJson(route('comments.like', ['comment' => $comment->id]));
    $res1->assertOk()
        ->assertJson(['liked' => true, 'likes_count' => 1]);

    assertDatabaseHas('comment_likes', [
        'comment_id' => $comment->id,
        'user_id' => $liker->id,
    ]);

    // Unlike
    $res2 = actingAs($liker)->postJson(route('comments.like', ['comment' => $comment->id]));
    $res2->assertOk()
        ->assertJson(['liked' => false, 'likes_count' => 0]);

    assertDatabaseMissing('comment_likes', [
        'comment_id' => $comment->id,
        'user_id' => $liker->id,
    ]);
});

test('author can delete their own comment', function (): void {
    $report = Report::factory()->create();
    $author = User::factory()->create();

    $comment = Comment::create([
        'report_id' => $report->id,
        'user_id' => $author->id,
        'content' => 'Comment to be deleted',
    ]);

    $response = actingAs($author)->deleteJson(route('comments.destroy', ['comment' => $comment->id]));
    $response->assertOk()
        ->assertJson(['success' => true]);

    assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('non-author cannot delete another user comment', function (): void {
    $report = Report::factory()->create();
    $author = User::factory()->create();
    $otherUser = User::factory()->create();

    $comment = Comment::create([
        'report_id' => $report->id,
        'user_id' => $author->id,
        'content' => 'Protected comment',
    ]);

    $response = actingAs($otherUser)->deleteJson(route('comments.destroy', ['comment' => $comment->id]));
    $response->assertForbidden();

    assertDatabaseHas('comments', ['id' => $comment->id]);
});

test('facebook auth redirect works in local demo mode or initiates oauth', function (): void {
    $response = get(route('auth.facebook'));

    // In local environment without live keys, it logs in as demo user or redirects to home
    $response->assertRedirect();
});

test('facebook auth redirect in local mode uses provided name', function (): void {
    $response = get(route('auth.facebook', ['name' => 'Sara Ahmed']));

    $response->assertRedirect();
    assertDatabaseHas('users', [
        'name' => 'Sara Ahmed',
        'email' => 'facebook.demo@nomoresilence.test',
    ]);
});
