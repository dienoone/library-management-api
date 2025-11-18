<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Category;

class CategoryService
{
  public function getAllCategoriesPaginate(array $filters = [])
  {
    $query = Category::query();

    // apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    // Apply sorting filter
    $orderBy = $filters['order_by'] ?? 'name';
    $orderDirection = $filters['order_direction'] ?? 'asc';
    $query->orderBy($orderBy, $orderDirection);

    // include book counts if requested
    if (
      isset($filters['with_books_count']) &&
      filter_var($filters['with_books_count'], FILTER_VALIDATE_BOOLEAN)
    ) {
      $query->withCount('books');
    }

    // load books relationship if requested
    if (
      isset($filters['with_books']) &&
      filter_var($filters['with_books'], FILTER_VALIDATE_BOOLEAN)
    ) {
      $query->with('books');
    }

    $perPage =  $filters['per_page'] ?? 15;
    return $query->paginate($perPage);
  }

  public function getAll()
  {
    return Category::orderBy('name', 'asc')->get();
  }

  public function getCategory(int $id)
  {
    $category = Category::find($id);
    throw_if(!$category, NotFoundException::class, 'Category not found');

    return $category->load('books', 'books.authors');
  }

  public function createCategory(array $data)
  {
    return Category::create([
      'name' => $data['name'],
      'description' => $data['description']
    ]);
  }

  public function updateCategory(int $id, array $data)
  {
    $category = Category::find($id);
    throw_if(!$category, NotFoundException::class, 'Category not found');

    $category->update([
      'name' => $data['name'] ?? $category->name,
      'description' => $data['description'] ?? $category->description
    ]);

    return $category->load('books', 'books.authors');
  }

  public function deleteCategory(int $id)
  {

    $category = Category::find($id);
    throw_if(!$category, NotFoundException::class, 'Category not found');

    throw_if(
      $category->books->exists(),
      ConflictException::class,
      'Cannot delete category with associated books. Please remove books first or reassign them to another category.'
    );

    $category->delete();
  }

  public function getWithBooksCount()
  {
    return Category::withCount('books')
      ->orderBy('name', 'asc')
      ->get();
  }

  public function search(string $search)
  {
    return Category::where('name', 'like', "%{$search}%")
      ->orWhere('description', 'like', "%{$search}%")
      ->orderBy('name', 'asc')
      ->get();
  }

  public function attachBooks(int $id, array $data)
  {

    $category = Category::find($id);
    throw_if(!$category, NotFoundException::class, 'Category not found');

    $category->books()->syncWithoutDetaching($data['book_ids']);

    return $category->load('books', 'books.authors');
  }

  public function detachBooks(int $id, array $data)
  {
    $category = Category::find($id);
    throw_if(!$category, NotFoundException::class, 'Category not found');

    $category->books()->detach($data['book_ids']);

    return $category->load('books', 'books.authors');
  }
}
