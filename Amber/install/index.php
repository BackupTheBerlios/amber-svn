<?php

require_once '../XMLLoader.php';

$filename = '../conf/localconf.xml';

if (isset($_POST['doUpdate'])) {
  updateLocalconf($filename, $_POST);
}
$conf = readLocalconf($filename);
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
      <input name="host" type="text" value="<?php echo htmlspecialchars($conf['database']['host']) ?>"></td>
    </tr>
    <tr>
      <td>Username:</td><td>
      <input name="username" type="text" value="<?php echo htmlspecialchars($conf['database']['username']) ?>"></td>
    </tr>
    <tr>
      <td>Password:</td>
      <td><input name="password" type="text" value="<?php echo htmlspecialchars($conf['database']['password']) ?>"></td>
    <tr>
    </tr>
      <td>Database:</td>
      <td><input name="dbname" type="text" value="<?php echo htmlspecialchars($conf['database']['dbname']) ?>"></td>
    </tr>
    <tr>
      <td colspan="2"><p><em><strong>Sys_objects</strong></em></p></td>
    </tr>
    </tr>
      <td>Medium:</td>
      <td><select name="medium"><option value="db" <?php if ($conf['sys_objects']['medium'] == 'db') echo 'selected'; else echo ''; ?>>Database</option><option value="file" <?php if ($conf['sys_objects']['medium'] == 'file') echo 'selected'; else echo ''; ?>>File</option></select></td>
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

function readLocalconf($fileName)
{
  if (file_exists($fileName)) {
    $loader = new XMLLoader(false);
    $conf = $loader->getArray($fileName);

    return $conf;
  }

  return array();
}

function updateLocalconf($fileName, $p)
{
  $properties = array(
    'database' => array('username', 'password', 'host', 'driver', 'dbname'),
    'sys_objects' => array('medium')
  );

  $fp = fopen($fileName, 'w');
  fwrite($fp, '<?xml version="1.0" encoding="iso-8859-1"?>' . "\n");
  fwrite($fp, "<config>\n");
  writeArray($fp, $properties);
  fwrite($fp, "</config>\n");
  fclose($fp);
}

function writeArray($filehandle, $confArray)
{
  static $indent = '';

  $indent .= '  ';
  foreach ($confArray as $key => $prop) {
    if (is_array($prop)) {
      fwrite($filehandle, $indent . "<$key>\n");
      writeArray($filehandle, $prop);
      fwrite($filehandle, $indent . "</$key>\n");
    } else {
      $value = htmlentities(stripslashes($_POST[$prop]));
      fwrite($filehandle, $indent. "<$prop>" . $value . "</$prop>\n");
    }
  }
  $indent = substr($indent, 0, count($indent) - 3);
}

?>
