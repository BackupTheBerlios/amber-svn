<?php
if (function_exists("mmcache")) {
  mmcache();
} else {
  echo "<html><head><title>Turck MMCache</title></head><body><h1 align=\"center\">Turck MMCache is not installed</h1></body></html>";
}
?>