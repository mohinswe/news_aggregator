<?php

namespace App\SourceType;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\SourceType\AbstractSourceType;


class TheNews extends AbstractSourceType
{

    public function aggregateArticles($source)
    {
        // Set source
        $this->source = $source;

        // Fetch articles
        $articles = $this->fetchArticles($source->url, $source->api_key);

        // Set articles
        $this->articles = $articles->json('data');
    
        // Store articles
        $this->storeArticles();

        // Update Categories
        $this->updateCategories();

        // Update Author 
        $this->updateAuthor();
    }

    private function fetchArticles($url, $api_key)
    {
        try{

            $lastDay = date('Y-m-d', strtotime('-1 day'));
            $api_endpoint = $url.'/news/all?api_token='.$api_key.'&language=en&published_after='.$lastDay;
            return Http::get($api_endpoint);

        } catch (\Exception $e) {
            Log::error("Error fetching news from The News API: " . $e->getMessage());
            return [];
        }
    }

    private function storeArticles(): void
    {
        foreach ($this->articles as $article) {

            Article::updateOrCreate([
                'article_id' => $article['uuid'],
                'title' => $article['title'],
                'url' => $article['url']
            ],[
                'source_id' => $this->source->id,
                'article_id' => $article['uuid'],
                'title' => $article['title'],
                'description' => $article['description'],
                'keywords' => $article['keywords'],
                'snippet' => $article['snippet'],
                'language' => $article['language'],
                'published_at' => $article['published_at'],
                'author_or_source' => $article['source'],
                'categories' => $article['categories'],
                'url' => $article['url'],
                'image_url' => $article['image_url']
            ]);
        }

    }
}