<?php

declare(strict_types=1);

namespace App\User\Repositories;

use App\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @param  array{name: string, email: string|null, password: string}  $attributes
     */
    public function create(array $attributes): User;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User;

    public function resetEmailVerification(User $user): User;

    public function delete(User $user): void;

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $criteria
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateWithCriteria(array $criteria = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @param  string|array<int, string>  $roles
     */
    public function assignRole(User $user, string|array $roles): User;

    /**
     * @param  string|array<int, string>  $roles
     */
    public function syncRoles(User $user, string|array $roles): User;

    public function resetPassword(User $user, string $hashedPassword): User;

    public function markEmailAsVerified(User $user): User;

    public function createToken(User $user, string $name): string;
}
