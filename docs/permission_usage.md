# using middleware in routes:

```php
// routes/api.php
Route::post('/cities', [CityController::class, 'store'])
    ->middleware('permission:' . \App\Permissions\ToperaAction::CREATE . ',' . \App\Permissions\ToperaResource::CITIES);
```

# Using in Controller Methods:

```php
<?php
// app/Http/Controllers/BookController.php

namespace App\Http\Controllers;

use App\Authorization\AuthorizationAction;
use App\Authorization\AuthorizationResource;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct()
    {
        // Admin and Librarian can manage books
        $this->middleware('permission:' . AuthorizationAction::CREATE . ',' . AuthorizationResource::BOOKS)
            ->only(['create', 'store']);

        $this->middleware('permission:' . AuthorizationAction::UPDATE . ',' . AuthorizationResource::BOOKS)
            ->only(['edit', 'update']);

        $this->middleware('permission:' . AuthorizationAction::DELETE . ',' . AuthorizationResource::BOOKS)
            ->only(['destroy']);

        // All roles can view and search books
        $this->middleware('permission:' . AuthorizationAction::VIEW . ',' . AuthorizationResource::BOOKS)
            ->only(['index', 'show']);

        $this->middleware('permission:' . AuthorizationAction::SEARCH . ',' . AuthorizationResource::BOOKS)
            ->only(['search']);
    }

    // Controller methods...
}
```
