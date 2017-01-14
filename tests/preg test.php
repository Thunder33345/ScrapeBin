<?php
/* Made By Thunder33345 */
$paste =[
	'1',
	'CSS</a>',
	'3'
];
//print_r($paste);
$s = 'CSS</a>';
//CSS2r3rd</a>';
preg_match_all('/(.*)<\/a>/',$paste[1],$cap);
print_r($cap);
$paste[1] = $cap[1][0];
print_r($paste);