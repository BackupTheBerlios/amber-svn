<?php

require_once '../XMLLoader.php';

if (isset($_POST['doUpdate'])) {
  updateLocalconf($_POST);
}
$conf = readLocalconf();
$conf = $conf['config'];

?>

<html>
  <head>
    <title>Amber</title>
  </head>
  <style><!--
    body {
      background: #f4b675;
      color: #ffffff;
      font-family: arial;
    }

    a {
      color: #ffffff;
    }

    h1 {
      color: #000000;
    }
  -->
  </style>

<body>

<h1 align="center">Amber Install Tool</h1>

<p>&nbsp;

<p align="center">
  <table align="center">
    <tr>
      <td width="150" align="center">
        <a href="../../index.html" target="_top" style="font-weight: bold;">Amber</a>
      </td>
      <td width="150" align="center">
        <a href="../tests/index.html" target="_top" style="font-weight: bold;">Amber - Tests</a>
      </td>
      <td width="150" align="center">
        <a href="index.php" target="_top" style="font-weight: bold;">Amber Install Tool</a>
      </td>
    </tr>
  </table>
</p>

<p />

<form method="post" action="index.php">


  <table align="center" style="width: 450px; border: #b08454 1px dashed;">
    <tr>
      <td colspan="2"><p><em><strong>Database configuration</strong></em></p></td>
    </tr>
    <tr>
      <td>Host:</td><td>
      <input name="host" type="text" value="<?php echo htmlspecialchars($conf['host']) ?>"></td>
    </tr>
    <tr>
      <td>Username:</td><td>
      <input name="username" type="text" value="<?php echo htmlspecialchars($conf['username']) ?>"></td>
    </tr>
    <tr>
      <td>Password:</td>
      <td><input name="password" type="text" value="<?php echo htmlspecialchars($conf['password']) ?>"></td>
    <tr>
    </tr>
      <td>Database:</td>
      <td><input name="database" type="text" value="<?php echo htmlspecialchars($conf['database']) ?>"></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="doUpdate" value="Update localconf.xml"></td>
    </tr>
  </table>

  <input name="driver" type="hidden" value="mysql"></td>

</form>


</body>
</html>

<?php

function readLocalconf()
{
  $filename = '../conf/localconf.xml';

  if (file_exists($filename)) {
    $loader = new XMLLoader(false);
    $conf = $loader->getArray($filename);

    return $conf;
  }

  return array();
}

function updateLocalconf($p)
{
  $properties = array('username', 'password', 'host', 'driver', 'database');

  $fp = fopen('../conf/localconf.xml', 'w');
  fwrite($fp, '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");
  fwrite($fp, "<config>\n");
  foreach ($properties as $prop) {
    $value = htmlentities(stripslashes($_POST[$prop]));
    fwrite($fp, "  <$prop>" . $value . "</$prop>\n");
  }
  fwrite($fp, "</config>\n");
  fclose($fp);
}

?>