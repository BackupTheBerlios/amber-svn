<?php
  require_once 'Amber/install/header.inc';
?>

<h1 align="center">Amber</h1>

<hr />

<ul id="menu">
  <li id="0" onclick="showContent(this.id);">Amber - Main</li>
  <li id="1" onclick="showContent(this.id);">Amber - Tests</li>
  <li id="2" onclick="showContent(this.id);">Amber - Install Tool</li>
</ul>

<hr />

<iframe id="content2" src="content.php?id=0" width="100%" height="100%" frameborder="0"><!-- CONTENT --></div>

<?php
  require_once 'Amber/install/footer.inc';
?>