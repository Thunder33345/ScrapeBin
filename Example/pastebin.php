<?php

class pastebin {
	private $folder = './pastebins';
	public $pastes = array();
	function __construct( $folder = false ) {
		if( $folder ) {
			$this->folder = $folder;
		}
		$this->folder = rtrim( $this->folder, '/' );
		if( !file_exists( $this->folder ) ) {
			mkdir( $this->folder );
		}
	}
	function downloader() {
		echo "Downloading...";
		while( count( $this->pastes ) > 0 ) {
			$paste = array_shift( $this->pastes );
			$fn = sprintf( '%s/%s-%s.txt', $this->folder, $paste, date( 'Y-m-d' ) );
			$content = file_get_contents( 'http://pastebin.com/raw.php?i='.$paste );
			if( strpos( $content, 'requesting a little bit too much' ) !== false ) {
				printf( "Throttling... requeuing $s\n", $paste );
				$this->pastes[] = $paste;
				sleep(1);
			}
			else {
				file_put_contents( $fn, $content );
			}
			$delay = rand( 3, 5 );
			printf( "Downloaded %s, waiting %d sec\n", $paste, $delay );
			sleep( $delay );
		}
	}
	function scraper() {
		echo "Scraping...";
		$doc = new DOMDocument();
		$doc->recover = true;
		@$doc->loadHTMLFile( 'http://www.pastebin.com' );
		$xpath = new DOMXPath( $doc );
		$elements = $xpath->query( '//ul[@class="right_menu"]/li/a' );
		if( $elements !== null ) {
			foreach( $elements as $e ) {
				$href = $e->getAttribute( 'href' );
				if( in_array( $href, $this->pastes ) ) {
					printf( "%s already seen\n", $href );
				}
				else {
					$this->pastes[] = substr( $href, 1 );
				}
			}
		}
	}
}
echo "Starting...";
$p = new pastebin();

while( true ) {
	echo "ReInvoking...";
	$p->scraper();
	$p->downloader();
	sleep( rand( 5, 9 ) );
}

?>