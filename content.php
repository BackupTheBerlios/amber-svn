<?php

$id = 0;
if (isset($_GET['id']))
{
  $id = $_GET['id'];
}
echo getContent($id);

function getContent($id)
{
  $files = array('main.php', 'tests.php', 'install.php');

  ob_start();
  include_once 'Amber/install/header.inc';
  include_once 'Amber/install/' . $files[$id];
  include_once 'Amber/install/footer.inc';
  $contents = ob_get_contents();
  ob_end_clean();

  return $contents;
}

?>