<?php
/* Made By Thunder33345 */
require 'ScrapBin.php';
$sb = new ScrapeBin();

$i = 0;
while (1) {
	echo "Starting Scraping Cycle: $i\n";
	echo "Total Paste Downloaded Is: " . $sb->getDownloadCount() . "\n";
	echo "Total Paste Waiting Is: " . $sb->countPaste() . "\n";
	switch ($sb->scrape()){
		case $sb::RET_SCRAPE_LIMIT:
			echo "Limit Hit Queuing Downloads...";
			$sb->intDownload();
			echo "Limit Hit Exiting...";
			exit();
			break;
	}
	sleep(mt_rand(5,7));
	$sb->intDownload();
	if ($i > 25) {
		echo "Stopping...\n";
		echo "Total Loop: $i\n";
		echo "Total Pastes Downloaded: " . $sb->getDownloadCount() . "\n";
		break;
	}
	echo "Done Cycle Sleeping Before Continuing...\n";
	sleep(mt_rand(10,15));
	$i++;
}

