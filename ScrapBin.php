<?php
/* Made By Thunder33345 */
set_time_limit(0);

Class ScrapeBin
{
  const RET_OK = 1;
  const RET_I_LIMIT = -1;
  const RET_UNKNOWN_ERROR = -2;
  const RET_D_E_GET = -3;
  const RET_D_E_WRITE = -4;
  const RET_SCRAPE_LIMIT = -5;

  public $timer_time = 3, $timer_cd_l = 60, $timer_cd_h = 180;

  private $t_tillReset, $t_remain; //timer
  private $file, $url;
  private $regex;
  private $pastes = [];
  private $totalDownloads = 0;

  public function __construct($file = 'Pastes')
  {
    $this->file = $file;
    if (!file_exists($file)) {
      mkdir($file);
    }
    $this->regex = base64_decode('
		L1xzKjx0ZD48aW1nIHNyYz0iLioiXHMqY2xhc3M9ImlfcDAiLipcLz48YSBocmVmPSJcLyguKikiPiguKik8XC9hPjxcL3RkPlxzKjx0ZFxzY2xhc3M9InRkX3NtYWxsZXIuKiI+Lio8XC90ZD5ccyo8dGQgY2xhc3M9InRkX3NtYWxsZXIuKiI+KC4qKSg/OjxcL2E+KT88XC90ZD4v
		');
    $this->url = 'http://pastebin.com/archive';
    //todo, forcescrap option to scrape sidebar
    //todo, make it more flexible like a real lib
  }

  public function scrape()
  {
    echo "Scraping...\n";
    $raw = file_get_contents($this->url);
    if (!$this->timer(1)) {
      echo "Error Internal Rate Limit Hit...\n";
      return self::RET_I_LIMIT;
    }
    $pattern = '/<div id="error" style=".*">You are scraping our site way too fast! We\'ve blocked you from the archive page until you slow down.*<\/div>/';
    if (preg_match($pattern, $raw)) {
      echo "[Critical] Page Access Have Been Limited";
      return self::RET_SCRAPE_LIMIT;
    }
    preg_match_all($this->regex, $raw, $data);
    unset($data[0]);
    $arrays = [];

    for ($i = 0; $i < count($data[1]); $i++) {

      if ($data[3][$i] !== '-') {
        preg_match_all('/(.*)<\/a>/', $data[3][$i], $cap);
        $data[3][$i] = $cap[1][0];
      }

      $pl = [
        $data[1][$i],
        $data[2][$i],
        $data[3][$i]
      ];
      array_push($arrays, $pl);
    }
    $this->pastes = array_merge($this->pastes, $arrays);
    echo "Done Scraping.\n";
    $this->sort();
    return self::RET_OK;
  }

  private function sort()
  {
    echo "Sorting...\n";
    foreach ($this->pastes as $key => $paste) {
      if ($paste[1] == 'Untitled' AND $paste[2] == '-') {
        unset($this->pastes[$key]);
      }
    }
    $this->pastes = array_values($this->pastes);
    echo "Done Sorting.\n";
    echo "Found " . count($this->pastes) . " pastes.\n";
  }

  public function intDownload(int $secl = 7, int $sech = 10)
  {
    echo "Initializing Downloads...\n";
    echo "Queuing " . count($this->pastes) . " Pastes To Download...\n";
    foreach ($this->pastes as $key => $paste) {
      $p1 = $paste[1];
      $p2 = $paste[2];

      if ($paste[1] == 'Untitled') $p1 = null;
      if ($paste[2] == '-') $p2 = null;

      $ret = $this->download($paste[0], $p1, $p2);

      if ($ret == self::RET_OK) {
        unset($this->pastes[$key]);
      }
      sleep(mt_rand($secl, $sech));
    }
    echo "Downloading Done...\n";
  }

  public function download($pasteID, $title = null, $lang = null)
  {
    $msg = "Downloading $pasteID";
    $this->totalDownloads++;
    if ($title != null) $msg .= " ($title)";
    if ($lang != null) $msg .= " [$lang]";
    echo $msg . "\n";

    if (!$this->timer(1)) {
      echo "Error Rate Limit Hit..\n";
      return self::RET_I_LIMIT;

    }

    $content = file_get_contents('http://pastebin.com/raw/' . $pasteID);
    if ($content === false) return self::RET_D_E_GET;

    $name = $pasteID;
    if ($title != null) $name .= " ($title)";
    if ($lang != null) $name .= " [$lang]";

    if (file_exists($name)) {
      $file = file_get_contents($name);
      if ($content === $file) {
        echo "ID: $pasteID already exist, not saving\n";
        return self::RET_OK;
      } else {
        $name .= " - " . date('m-D h:i:s');
      }
    }

    if (preg_match('/#EXTINF:/i', $content)) {
      $name .= '[IPTV]';
    }

    $match = ['<', '>', ':', '"', '/', '\\', '|', '?', '*',
      'CON', 'PRN', 'AUX', 'CLOCK$', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9'];
    $name = str_ireplace($match, '_', $name);

    $put = @file_put_contents($this->file . '/' . $name, $content);
    if ($content === false OR $put === false) $ret = false;

    $msg = "Completed $pasteID";
    if ($title != null) $msg .= " ($title)";
    if ($lang != null) $msg .= " [$lang]";
    if (isset($ret) and $ret === false) $msg .= " Unsuccessful(!!!)"; else $msg .= " Successful";
    echo $msg . "\n";

    if ($content === false) $ret = self::RET_D_E_WRITE; else $ret = self::RET_OK;
    return $ret;

  }

  private function timer(int $fetch = 1)
  {
    if (empty($this->t_tillReset) OR time() > $this->t_tillReset) {
      $this->t_tillReset = time() + (60 * $this->timer_time);
      $this->t_remain = 35;
    }
    if ($fetch == 1) {
      if ($this->t_remain > 1) {
        $this->t_remain--;
        return true;
      } else {
        echo "Rate Limit Over...";
        echo "Throttling...\n";
        sleep(mt_rand($this->timer_cd_l, $this->timer_cd_h));
        return false;
      }
    } else return $this->t_remain;
  }

  public function countPaste()
  {
    return count($this->pastes);
  }

  public function getDownloadCount()
  {
    return $this->totalDownloads;
  }
}