<?php
/* Made By Thunder33345 */
function br(){
	echo PHP_EOL;
}
$file = 'Pastes';
if(!file_exists($file)) {
	mkdir('Pastes');
}
$content = file_get_contents( 'http://pastebin.com/raw/0eKaXLTd');
print_r($content);
br();
$fn = sprintf( '%s/%s(%s).txt', $file , '0eKaXLTd', date( 'Y-m-d' ) );
echo $fn;
file_put_contents( $fn, $content );