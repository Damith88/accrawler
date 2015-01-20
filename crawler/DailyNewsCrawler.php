<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/WebCrawlerBase.php';

/**
 * Description of DailyNewsCrawler
 *
 * @author damith
 */
class DailyNewsCrawler extends WebCrawlerBase {

    private $mainUrl = 'http://www.dailynews.lk';
    private $archiveUrl = 'http://www.dailynews.lk/?q=archives/';
    private $outputDir = '/var/www/html/crawler/DailyNews/';

    public function execute($date, $resume = false) {
        if ($date instanceof DateTime) {
            $date = $date->format('Y-m-d');
        }
        if (!is_string($date)) {
            throw new Exception("invalid date");
        }
        
        $categoryArticles = $this->findArticlesForDate($date);

        $currDir = getCwd();
        chdir($this->outputDir);
        if (!$resume || !is_dir($date)) {
            mkdir($date);
        }
        chdir($date);

        $articleId = 1;
        $articleArray = array();
        foreach ($categoryArticles as $category => $articles) {
            if (!$resume || !is_dir($category)) {
                mkdir($category);
            }
            chdir($category);
            foreach ($articles as $article) {
                list($heading, $url) = $article;
                $url = $this->mainUrl . $url;
                // waiting half a second before sending each request
                // this is done to avoid denial of service. (politness)
                //usleep(500000);
                $filename = "$articleId.html";
                if (!$resume || !is_file($filename)) {
                    $rawContent = $this->retrieveSinglePage($url);
                    file_put_contents($filename, $rawContent);
                } else {
                    $rawContent = file_get_contents($filename);
                }
                $articleArray[] = array(
                    'heading' => trim($heading),
                    'url' => $url,
                    'content' => $this->extractArticleContent($rawContent),
                    'date' => $date,
                    'category' => $category
                );
                $articleId++;
            }
            chdir('..');
        }

        $this->saveIndexFile($categoryArticles);

        // setting the current dir back to original dir
        chdir($currDir);

        return $this->saveArticles($articleArray);
    }
    
    protected function saveIndexFile($categoryArticles) {
        $fp = fopen('index.txt', 'w');
        foreach ($categoryArticles as $articles) {
            foreach ($articles as $article) {
                list($heading, $url) = $article;
                $url = $this->mainUrl . $url;
                fwrite($fp, "$heading\t$url\n");
            }
        }
        fclose($fp);
    }

    /**
     * 
     * @param array $articles
     * @return boolean
     */
    public function saveArticles($articles) {
        $conn = DbHandler::getConnection();
        $conn->beginTransaction(); // starts a new transaction
        $query = 'INSERT INTO article (heading, url, content, date, category)
                    VALUES (?, ?, ?, ?, ?);';
        $sth = $conn->prepare($query);
        foreach ($articles as $article) {
            $params = array(
                $article['heading'],
                $article['url'],
                $article['content'],
                $article['date'],
                $article['category']
            );
            $sth->execute($params);
        }
        return $conn->commit(); // commit changes
    }

    public function extractArticleContent($rawContent) {
        $html = str_get_html($rawContent);
        return trim(html_entity_decode($html->find('div.field-name-body', 0)->plaintext, ENT_QUOTES | ENT_HTML401));
    }

    public function findArticlesForDate($date) {
        $currentPage = 0;
        $categoryArticles = array();

        $content = html_entity_decode($this->retrieveSinglePage($this->archiveUrl . $date), ENT_QUOTES | ENT_HTML401);
        $html = str_get_html($content);
        $categoryDivElements = $html->find('div.region-content div.view-content div.item-list');
        $articleCount = 0;
        foreach ($categoryDivElements as $element) {
            $category = $element->find('h3', 0)->plaintext;
            $articles = $element->find('span.field-content a');
            foreach ($articles as $article) {
                $categoryArticles[$category][] = array(
                    $article->plaintext,
                    $article->href
                );
            }
            $articleCount += count($articles);
        }
        $this->logMessage("$articleCount articles found.\n");

        $numberOfPages = count($html->find('li.pager-item')) + 1;

        $currentPage++;

        while ($currentPage < $numberOfPages) {
            $content = html_entity_decode($this->retrieveSinglePage($this->archiveUrl . $date . '&page=' . $currentPage), ENT_QUOTES | ENT_HTML401);
            $html = str_get_html($content);
            $articleCount = 0;
            $categoryDivElements = $html->find('div.region-content div.view-content div.item-list');
            foreach ($categoryDivElements as $element) {
                $category = $element->find('h3', 0)->plaintext;
                $articles = $element->find('span.field-content a');
                foreach ($articles as $article) {
                    $categoryArticles[$category][] = array(
                        $article->plaintext,
                        $article->href
                    );
                }
                $articleCount += count($articles);
            }
            $this->logMessage("$articleCount articles found.\n");
            $currentPage++;
        }
        return $categoryArticles;
    }

}
