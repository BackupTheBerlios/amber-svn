<?php

$id = 0;
if (isset($_GET['id']))
{
  $id = $_GET['id'];
}
echo getContent($id);

function getContent($id)
{
  $files = array('main.php', 'install.php', 'tests.php');

  include_once 'Amber/install/' . $files[$id];
}

?>