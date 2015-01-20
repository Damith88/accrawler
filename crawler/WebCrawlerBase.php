<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once dirname(__FILE__) . '/../utility/DbHandler.php';
require_once dirname(__FILE__) . '/../lib/vendor/simplehtmldom/simple_html_dom.php';

/**
 * Description of WebCrawlerBase
 *
 * @author damith
 */
abstract class WebCrawlerBase {

    abstract public function execute($date);

    abstract protected function findArticlesForDate($date);

    abstract protected function extractArticleContent($rawHtmlContent);

    /**
     * 
     * @param array $articles
     * @return boolean
     */
    public function saveArticles($articles) {
        try {
            $this->doSaveArticles($articles);
        } catch (Exception $e) {
            if ($e instanceof PDOException) {
                print_r($e->errorInfo);
            }
            $message = "An error occured while trying to save articles";
            $this->logMessage($message, 'error');
            exit(1);
        }
    }
    
    protected function doSaveArticles($articles) {
        $conn = DbHandler::getConnection();
        $conn->beginTransaction(); // starts a new transaction
        $query = 'INSERT INTO article2 (heading, url, content, date)
                    VALUES (?, ?, ?, ?);';
        $sth = $conn->prepare($query);
        foreach ($articles as $article) {
            $params = array(
                $article['heading'],
                $article['url'],
                $article['content'],
                $article['date']
            );
            $sth->execute($params);
        }
        return $conn->commit(); // commit changes
    }

    protected function logMessage($message, $level = 'info') {
        echo $message;
        return true;
    }

    protected function retrieveSinglePage($url, $log = true) {
        if ($log) {
            $this->logMessage("visiting $url\n");
        }
        return $this->curl_get($url);
    }

    protected function curl_get($url, array $options = array()) {
        $defaults = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => TRUE
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if (!$result = curl_exec($ch)) {
            $message = "An Error Occured while fetching web page.\n";
            $message .= "Error No: " . curl_errno($ch) . "\n";
            $message .= "Error Message: " . curl_error($ch) . "\n";
            $this->logMessage($message, 'error');
            exit(1);
        }
        curl_close($ch);
        return $result;
    }

}
