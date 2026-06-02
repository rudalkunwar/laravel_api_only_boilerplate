<?php

declare(strict_types=1);

use App\User\Models\User;
use App\User\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->repository = app(UserRepositoryInterface::class);
});

it('resolves the eloquent implementation from the container', function (): void {
    expect($this->repository)->toBeInstanceOf(App\User\Repositories\EloquentUserRepository::class);
});

it('creates a user and hashes the password', function (): void {
    $user = $this->repository->create([
        'name' => 'Ada',
        'email' => 'ada@example.com',
        'password' => 'plain-password',
    ]);

    $this->assertDatabaseHas('users', ['email' => 'ada@example.com']);
    expect(Hash::check('plain-password', $user->password))->toBeTrue();
});

it('finds users by id and email', function (): void {
    $user = User::factory()->create(['email' => 'find@example.com']);

    expect($this->repository->findById($user->id)?->id)->toBe($user->id)
        ->and($this->repository->findByEmail('find@example.com')?->id)->toBe($user->id)
        ->and($this->repository->findById(999_999))->toBeNull()
        ->and($this->repository->findByEmail('missing@example.com'))->toBeNull();
});

it('updates a user and returns the fresh model', function (): void {
    $user = User::factory()->create(['name' => 'Before']);

    $updated = $this->repository->update($user, ['name' => 'After']);

    expect($updated->name)->toBe('After');
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'After']);
});

it('deletes a user', function (): void {
    $user = User::factory()->create();

    $this->repository->delete($user);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('paginates users', function (): void {
    User::factory()->count(3)->create();

    $paginator = $this->repository->paginate(2);

    expect($paginator)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($paginator->perPage())->toBe(2)
        ->and($paginator->total())->toBe(3);
});
