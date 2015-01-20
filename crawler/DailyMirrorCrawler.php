<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/WebCrawlerBase.php';

/**
 * Description of DailyMirrorCrawler
 *
 * @author damith
 */
class DailyMirrorCrawler extends WebCrawlerBase {

    private $mainUrl = 'http://old.dailymirror.lk';
    private $outputDir = '/var/www/html/crawler/DailyMirror/';
    
    private function checkDate($date) {
        if (is_string($date)) {
            $ret = explode('-', $date);
        } else if ($date instanceof DateTime) {
            $y = $date->format('Y');
            $m = $date->format('m');
            $d = $date->format('d');
            $ret = array($y, $m, $d);
        } else {
            throw new Exception("invalid date");
        }
        return $ret;
    }
    
    protected function findArticlesForDate($date) {
        list($y, $m, $d) = $this->checkDate($date);
        $url = "http://old.dailymirror.lk/archive.html?year=$y&month=$m&day=$d&modid=1032";
        $content = html_entity_decode($this->retrieveSinglePage($url));
        $html = str_get_html($content);
        $titles = $html->find('a.contentpagetitle');
        $articles = array();
        foreach ($titles as $title) {
            $url = $title->href;
            $heading = trim($title->plaintext);
            if ($heading != 'Cartoon of the day') {
                $articles[] = array($heading, $this->mainUrl . $url);
            }
        }
        $articleCount = count($articles);
        $this->logMessage("$articleCount articles found.\n");
        return $articles;
    }

    public function execute($date, $resume = false) {
        $articles = $this->findArticlesForDate($date);
        if ($date instanceof DateTime) {
            $date = $date->format('Y-m-d');
        }

        $currDir = getCwd();
        chdir($this->outputDir);
        if (!$resume || !is_dir($date)) {
            mkdir($date);
        }
        chdir($date);

        $articleId = 1;
        $articleArray = array();
        foreach ($articles as $article) {
            list($heading, $url) = $article;
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
                'heading' => $heading,
                'url' => $url,
                'content' => $this->extractArticleContent($rawContent),
                'date' => $date
            );
            $articleId++;
        }

        $this->saveIndexFile($articles);

        // setting the current dir back to original dir
        chdir($currDir);

        return $this->saveArticles($articleArray);
    }
    
    protected function saveIndexFile($articles) {
        $fp = fopen('index.txt', 'w');
        foreach ($articles as $article) {
            list($heading, $url) = $article;
            fwrite($fp, "$heading\t$url\n");
        }
        fclose($fp);
    }
    
    /**
     * Given the raw html string returns extracted article content
     * @param string $rawHtmlContent
     * @return string
     */
    protected function extractArticleContent($rawHtmlContent) {
        $content = preg_replace('/<img[^>]+>/', '', $rawHtmlContent);
        $html = str_get_html($content);
        return trim(html_entity_decode($html->find('div.article-content', 0)->plaintext, ENT_QUOTES | ENT_HTML401));
    }

}
