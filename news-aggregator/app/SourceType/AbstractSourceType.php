<?php

namespace App\SourceType;

use App\Models\Author;
use App\Models\Article;
use App\Models\Category;
use App\Contracts\SourceType;
use Illuminate\Support\Facades\Log;
use Illuminate\Container\Attributes\Auth;


abstract class AbstractSourceType implements SourceType
{
    protected $source;
    protected $articles = [];

    // Update categories in the database
    protected function updateCategories(): void
    {
        try {
            $articleCategories = Article::where('source_id', $this->source->id)->pluck('categories')->toArray();
            $articleCategories = array_unique(array_merge(...$articleCategories));

            $categories = Category::all()->pluck('name')->toArray();
            $missingCategories = array_diff($articleCategories, $categories);

            foreach ($missingCategories as $category) {
                Category::create([
                    'name' => $category,
                    'slug' => str_replace(' ', '-', strtolower($category)),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Error updating categories: " . $e->getMessage());
        }
    }

    // Update Author in the database
    protected function updateAuthor(): void
    {
        try {
            $articleAuthors = Article::where('source_id', $this->source->id)->pluck('author_source')->toArray();
            $articleAuthors = array_unique($articleAuthors);

            $authors = Author::all()->pluck('name')->toArray();
            $missingAuthors = array_diff($articleAuthors, $authors);

            foreach ($missingAuthors as $author) {
                Author::create([
                    'name' => $author,
                    'slug' => str_replace(' ', '-', strtolower($author)),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating authors: " . $e->getMessage());
        }
    }
}
