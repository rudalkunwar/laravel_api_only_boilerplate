# Architecture Guide

> Strict rules for AI and developers extending this boilerplate. All new code **must** follow these conventions.

---

## 1. Feature-Based Directory Structure

Each domain/feature is a self-contained module under `app/`. Every file for a feature lives in its folder — no splitting across layers.

```
app/
├── Auth/        # Shared auth: login, register, password, email verification, roles/permissions
│   ├── Actions/
│   ├── Controllers/
│   ├── Data/
│   ├── Enums/
│   └── Requests/
├── User/        # User-facing features
│   ├── Actions/
│   ├── Controllers/
│   ├── Data/
│   ├── Models/
│   ├── Policies/
│   ├── Repositories/
│   ├── Requests/
│   └── Resources/
├── Admin/       # Admin-only features
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── YourFeature/ # <-- add new features here, same pattern
├── Http/        # Shared infrastructure only
│   ├── Controllers/  # Base Controller.php
│   └── Middleware/
├── Providers/
└── Support/
    └── Http/ApiResponse.php
```

**Rule:** Adding a new feature? Create a new top-level folder under `app/`. Never create files outside it (no mixing into `Http/`, `Domain/`, etc.).

**Rule:** Tests mirror the same structure under `tests/Feature/YourFeature/` and `tests/Unit/YourFeature/`.

---

## 2. Layers (inside each feature)

| Layer | Location | Purpose |
|-------|----------|---------|
| **Controllers** | `{Feature}/Controllers/` | Handle HTTP request/response. Thin — delegate to Actions |
| **Actions** | `{Feature}/Actions/` | Single-responsibility business logic. One class, one `execute()` method. `final readonly class` |
| **Data (DTOs)** | `{Feature}/Data/` | Immutable data transfer objects. `final readonly class` with typed properties and `fromArray()` factory |
| **Enums** | `{Feature}/Enums/` | Backed string enums for roles, permissions, statuses |
| **Models** | `{Feature}/Models/` | Eloquent models (only if the feature owns the model) |
| **Repositories** | `{Feature}/Repositories/` | Data access abstraction. Interface + Eloquent implementation |
| **Policies** | `{Feature}/Policies/` | Authorization logic |
| **Requests** | `{Feature}/Requests/` | Form request classes with validation rules |
| **Resources** | `{Feature}/Resources/` | API resource transformers |

**Rule:** Controllers must not contain business logic. Always delegate to Actions.

**Rule:** Actions must not call `request()`, `auth()`, or any HTTP facade. They receive data via DTOs.

**Rule:** Repositories are the only layer that calls Eloquent models directly. Controllers and Actions must not use `Model::query()`, `save()`, `delete()`, etc.

---

## 3. Patterns

### Actions
```php
final readonly class CreateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
    ) {}

    public function execute(CreateProductData $data): Product
    {
        return $this->products->create($data->toArray());
    }
}
```

### Controllers
```php
final class ProductController extends Controller
{
    public function __construct(
        private readonly CreateProductAction $createProduct,
    ) {}

    public function store(CreateProductRequest $request): JsonResponse
    {
        $product = $this->createProduct->execute(
            CreateProductData::fromArray($request->validated()),
        );

        return ApiResponse::success(ProductResource::make($product), 'Created.', 201);
    }
}
```

### DTOs
```php
final readonly class CreateProductData
{
    public function __construct(
        public string $name,
        public float $price,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            price: (float) $data['price'],
        );
    }
}
```

### Repositories
```php
interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function create(array $attributes): Product;
    public function update(Product $product, array $attributes): Product;
    public function delete(Product $product): void;
}

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return Product::query()->find($id);
    }

    public function create(array $attributes): Product
    {
        return Product::query()->create($attributes);
    }

    public function update(Product $product, array $attributes): Product
    {
        $product->update($attributes);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
```

### Binding (Service Provider)
```php
final class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ProductRepositoryInterface::class => EloquentProductRepository::class,
    ];
}
```

---

## 4. API Responses

Always use `App\Support\Http\ApiResponse`:

```php
// Success with data
ApiResponse::success($data, 'Optional message', 200);

// Success with paginated collection
// The resource collection handles data/meta/links automatically
ApiResponse::success(ProductResource::collection($paginator));

// Bare message
ApiResponse::message('Done.', 202);

// Error
ApiResponse::error('Validation failed.', 422, ['field' => ['Error.']]);
```

---

## 5. Routes

All API routes are under `routes/api.php`, prefixed with `api/v1`. Name all routes with the `api.v1.` prefix.

```
Route::prefix('v1')->name('api.v1.')->group(function (): void {
    // Public
    Route::post('products', [ProductController::class, 'index'])->name('products.index');

    // Authenticated
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
    });

    // Admin-only
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
        Route::delete('products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');
    });
});
```

---

## 6. PHP Standards

- `declare(strict_types=1);` in every file
- All classes are `final` unless there's a specific reason not to be
- Use constructor property promotion: `public function __construct(public Type $prop) {}`
- Explicit return types on every method
- Use PHPDoc for `@param` and `@return` only when needed for array shapes or generics
- No inline comments for obvious code (method names should be self-documenting)
- Use `__` for translations, never hardcode user-facing strings

---

## 7. Testing Standards

- Mirror the app structure: `tests/Feature/YourFeature/`, `tests/Unit/YourFeature/`
- Every controller endpoint must have a feature test
- Every Action must have a unit or feature test
- Use Pest syntax: `it('does something', fn (): void => ...)`
- Use factories, never create models manually in tests
- Run `composer test` before committing

---

## 8. Security & Authorization

- Admin routes use `role:admin` middleware (Spatie)
- Define permissions in `App\Auth\Enums\Permission`, roles in `App\Auth\Enums\Role`
- Use Laravel Policies for model-level authorization
- Always validate with Form Requests, never inline in controllers

---

## 9. Adding a New Feature — Checklist

1. Create `app/YourFeature/{Actions,Controllers,Data,Models,Repositories,Requests,Resources}` as needed
2. Create the Migration in `database/migrations/`
3. Create the Model (if new entity) with Factory
4. Create the Repository Interface + Implementation
5. Bind in `RepositoryServiceProvider`
6. Create DTOs for data transport
7. Create Actions for business logic
8. Create Form Requests for validation
9. Create Controller (thin, delegates to Actions)
10. Create API Resource for response transformation
11. Register routes in `routes/api.php`
12. Create tests in `tests/Feature/YourFeature/`
13. Run `composer test`
