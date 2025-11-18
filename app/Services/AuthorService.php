<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Author;
use Illuminate\Support\Facades\DB;

class AuthorService
{
  public function getAllAuthorsPaginate(array $filters = [])
  {
    $query = Author::query();

    // apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('bio', 'like', "%{$search}%")
          ->orWhere('nationality', 'like', "%{$search}%");
      });
    }

    // apply sorting filter
    $orderBy = $filters['order_by'] ?? 'name';
    $orderDirection = $filters['order_direction'] ?? 'asc';
    $query->orderBy($orderBy, $orderDirection);


    // load books relationship if requested
    if (
      isset($filters['with_books']) &&
      filter_var($filters['with_books'], FILTER_VALIDATE_BOOLEAN)
    ) {
      $query->with('books', 'books.categories');
    }

    $perPage =  $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getAll()
  {
    return Author::orderBy('name', 'asc')->get();
  }

  public function getAuthor(int $id)
  {
    $author = Author::find($id);
    throw_if(!$author, NotFoundException::class, 'Author not found');

    return $author->load('books', 'books.categories');
  }


  public function search(string $search)
  {
    return Author::where('name', 'like', "%{$search}%")
      ->orWhere('bio', 'like', "%{$search}%")
      ->orWhere('nationality', 'like', "%{$search}%")
      ->orderBy('name', 'asc')
      ->get();
  }


  public function updateAuthor(int $id, array $data)
  {
    $author = Author::find($id);
    throw_if(!$author, NotFoundException::class, 'Author not found');

    return DB::transaction(function () use ($author, $data) {

      $author->update([
        'bio' => $data['bio'] ?? $author->name,
        'nationality' => $data['nationality'] ?? $author->description
      ]);

      $author->user->update([
        'first_name' => $data['first_name'] ?? $author->user->first_name,
        'last_name' => $data['last_name'] ?? $author->user->last_name,
        'phone' => $data['phone'] ?? $author->user->phone,
        'address' => $data['address'] ?? $author->user->address,
        'birth_date' => $data['birth_date'] ?? $author->user->birth_date,
      ]);

      return $author->load('books');
    });
  }


  public function deleteAuthor(int $id)
  {

    $author = Author::find($id);
    throw_if(!$author, NotFoundException::class, 'Author not found');

    throw_if(
      $author->books()->exists(),
      ConflictException::class,
      'Cannot delete author with associated books. Please remove books first or reassign them to another author.'
    );

    $author->delete();
  }


  public function attachBooks(int $id, array $data)
  {

    $author = Author::find($id);
    throw_if(!$author, NotFoundException::class, 'Author not found');

    $author->books()->syncWithoutDetaching($data['book_ids']);

    return $author->load('books', 'books.categories');
  }

  public function detachBooks(int $id, array $data)
  {
    $author = Author::find($id);
    throw_if(!$author, NotFoundException::class, 'Author not found');

    $author->books()->detach($data['book_ids']);

    return $author->load('books', 'books.categories');
  }
}
