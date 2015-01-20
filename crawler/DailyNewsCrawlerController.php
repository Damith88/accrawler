<?php

require_once dirname(__FILE__) . '/CrawlerControllerBase.php';
require_once dirname(__FILE__) . '/DailyNewsCrawler.php';

class DailyNewsCrawlerController extends CrawlerControllerBase {

    /**
     * 
     * @return DailyNewsCrawler
     */
    protected function getCrawler() {
        return new DailyNewsCrawler();
    }

}

$controller = new DailyNewsCrawlerController();
$controller->execute(array_slice($argv, 1));
