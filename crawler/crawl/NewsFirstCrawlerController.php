<?php
require_once dirname(__FILE__) . '/CrawlerControllerBase.php';
require_once dirname(__FILE__) . '/NewsFirstCrawler.php';

/**
 * NewsFirstCrawlerController is used to control NewsFirstCrawler
 *
 * @author damith
 */
class NewsFirstCrawlerController extends CrawlerControllerBase {

	/**
	 *
	 * @return NewsFirstCrawler
	 */
	protected function getCrawler() {
		return new NewsFirstCrawler();
	}
	
	/*
	 * Crawl on monthly interval
	 */
	public function execute($args) {
		ini_set('xdebug.max_nesting_level', 200); // this is done in order to prevent issues occur when html elements are nested
		if (($idx = array_search('--resume', $args)) !== false) {
			$resume = true;
			unset($args[$idx]);
			$args = array_values($args);
		} else {
			$resume = false;
		}
		@list($fromDate, $toDate) = $args;
		$fromDateObj = DateTime::createFromFormat('Y-m-d', $fromDate);
		if (empty($fromDateObj) || $fromDateObj->format('Y-m-d') != $fromDate) { // validating from date
			exit("Invalid start date. date should be given in Y-m-d format.\n");
		}
		if (isset($toDate)) {
			$toDateObj = DateTime::createFromFormat('Y-m-d', $toDate);
			if (empty($toDateObj) && $toDateObj->format('Y-m-d') != $toDate) { // validating to date
				exit("Invalid end date. date should be given in Y-m-d format.\n");
			}
			if ($toDateObj > $fromDateObj) {
				$crawler = $this->getCrawler();
				$oneMonthInterval = new DateInterval('P1M');
				$crawler->execute($fromDateObj, $resume); // resume option will apply to first date
				$fromDateObj->add($oneMonthInterval);
				while ($toDateObj >= $fromDateObj) {
					$crawler->execute($fromDateObj);
					$fromDateObj->add($oneMonthInterval);
				}
			} else {
				exit("start date should be less than end date.\n");
			}
		} else {
			$this->getCrawler()->execute($fromDate, $resume);
		}
	}

}

$controller = new NewsFirstCrawlerController();
$controller->execute(array_slice($argv, 1));