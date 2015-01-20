<?php

require_once dirname(__FILE__) . '/CrawlerControllerBase.php';
require_once dirname(__FILE__) . '/DailyMirrorCrawler.php';

class DailyMirrorCrawlerController extends CrawlerControllerBase {

    /**
     * 
     * @return DailyMirrorCrawler
     */
    protected function getCrawler() {
        return new DailyMirrorCrawler();
    }

}

$controller = new DailyMirrorCrawlerController();
$controller->execute(array_slice($argv, 1));
