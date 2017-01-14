<?php
echo "starting\n";
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
		while( count( $this->pastes ) > 0 ) {
			echo"downloading\n";
			$paste = array_shift( $this->pastes );
			$fn = sprintf( '%s/%s-%s.txt', $this->folder, $paste, date( 'Y-m-d' ) );
			$content = file_get_contents( 'http://pastebin.com/raw.php?i='.$paste );
			print_r($content);
			if( strpos( $content, 'requesting a little bit too much' ) !== false ) {
				printf( "Throttling... requeuing %s\n", $paste );
				$this->pastes[] = $paste;
				sleep(1);
			}
			else {
				file_put_contents( $fn, $content );
			}
			$delay = rand( 1, 3 );
			printf( "Downloaded %s, waiting %d sec\n", $paste, $delay );
			sleep( $delay );
		}
	}
	function scraper() {
		echo "Scrapping\n";
		$doc = new DOMDocument();
		$doc->recover = true;
		@$doc->loadHTMLFile( 'http://www.pastebin.com' );
		$xpath = new DOMXPath( $doc );
		$elements = $xpath->query( '//ul[@class="right_menu"]/li/a' );
		print_r($elements);
		if( $elements !== null ) {
			foreach( $elements as $e ) {
				echo 'in loop\n';
				$href = $e->getAttribute( 'href' );
				if( in_array( $href, $this->pastes ) ) {
					printf( "%s already seen\n", $href );
				}
				else {
					echo "adding $href\n";
					$this->pastes[] = substr( $href, 1 );
				}
			}
		} else {
			echo "elements are null \n";
		}
	}
}

$p = new pastebin();

echo "invoking\n";
while( true ) {
	echo "re invoked\n";
	$p->scraper();
	$p->downloader();
	sleep( 3 );
}
