<?php
/* Made By Thunder33345 */
$url = 'http://pastebin.com/archive';
$data = file_get_contents($url);
//echo "$data";
//REGEX /\s*<td><img src="\/i\/t.gif"\s*class="i_p0".*\/><a href="\/(.*)">(.*)<\/a><\/td>\s*<td\sclass="td_smaller.*">(.*)<\/td>\s*<td class="td_smaller.*">(.*)<\/td>/
$regex = base64_decode('
L1xzKjx0ZD48aW1nIHNyYz0iLioiXHMqY2xhc3M9ImlfcDAiLipcLz48YSBocmVmPSJcLyguKikiPiguKik8XC9hPjxcL3RkPlxzKjx0ZFxzY2xhc3M9InRkX3NtYWxsZXIuKiI+Lio8XC90ZD5ccyo8dGQgY2xhc3M9InRkX3NtYWxsZXIuKiI+KC4qKSg/OjxcL2E+KT88XC90ZD4v
');
//echo $regex;
$array = [];
preg_match_all($regex,$data,$array);
print_r($array);