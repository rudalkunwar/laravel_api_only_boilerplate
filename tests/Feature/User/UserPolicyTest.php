<?php

declare(strict_types=1);

use App\Auth\Enums\Role;
use App\User\Models\User;
use App\User\Policies\UserPolicy;

beforeEach(function (): void {
    $this->policy = new UserPolicy;

    $this->admin = User::factory()->create();
    $this->admin->assignRole(Role::Admin->value);

    $this->member = User::factory()->create();
    $this->member->assignRole(Role::User->value);
});

it('allows only privileged users to list users', function (): void {
    expect($this->policy->viewAny($this->admin))->toBeTrue()
        ->and($this->policy->viewAny($this->member))->toBeFalse();
});

it('lets members view themselves but not others', function (): void {
    expect($this->policy->view($this->member, $this->member))->toBeTrue()
        ->and($this->policy->view($this->member, $this->admin))->toBeFalse()
        ->and($this->policy->view($this->admin, $this->member))->toBeTrue();
});

it('lets members update themselves but not others', function (): void {
    expect($this->policy->update($this->member, $this->member))->toBeTrue()
        ->and($this->policy->update($this->member, $this->admin))->toBeFalse()
        ->and($this->policy->update($this->admin, $this->member))->toBeTrue();
});

it('lets privileged users delete others but never themselves', function (): void {
    expect($this->policy->delete($this->admin, $this->member))->toBeTrue()
        ->and($this->policy->delete($this->admin, $this->admin))->toBeFalse()
        ->and($this->policy->delete($this->member, $this->admin))->toBeFalse();
});
