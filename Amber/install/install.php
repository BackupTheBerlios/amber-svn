<?php

require_once 'header.inc';
require_once 'Amber/Amber.php';
require_once 'Amber/AmberConfig.php';
require_once 'Amber/XMLLoader.php';

$filename = __AMBER_BASE__ . '/conf/localconf.xml';

$cfg = new AmberConfig();
if (isset($_POST['doUpdate'])) {
    $props = array(
      'Username', 'Password', 'Host', 'Driver', 'DbName', 'Medium', 'BasePath',
      'SysUsername', 'SysPassword', 'SysHost', 'SysDriver', 'SysDbName'
    );
    if (file_exists($filename)) {
      $cfg->fromXML($filename);
    }

    foreach ($props as $p) {
      $methodName = 'set' . $p;
      if (isset($_POST[strtolower($p)])) {
        $postVal = $_POST[strtolower($p)];
        $cfg->$methodName($postVal);
      }
    }

    if (!$cfg->toXML($filename)) {
      $msg = "Unable to update localconf.xml<p />" . htmlentities($filename) . " needs to be writeable";
    } else {
      $msg = "Configuraton successfully written to:<p />" . htmlentities($filename);
    }
    echo '<div align="center"><div style="text-align: left; color: #000000; width: 450; font-size: 10pt; border: #ee0000 2pt solid; background-color: #ffffff; padding: 5px;"><strong>' . $msg . '</strong></div></div>';
}

// Re-read for display
$cfg = new AmberConfig();
if (file_exists($filename)) {
  $cfg->fromXML($filename);
}

?>

<p />

<form method="post" action="<?php echo $__SELF__; ?>">


  <table align="center" style="width: 450px; border: #b08454 1px dashed;">
    <tr>
      <td colspan="2"><p><em><strong>Database configuration</strong></em></p></td>
    </tr>
    <tr>
      <td>Host:</td><td>
      <input name="host" type="text" value="<?php echo htmlspecialchars($cfg->getHost()) ?>"></td>
    </tr>
    <tr>
      <td>Username:</td><td>
      <input name="username" type="text" value="<?php echo htmlspecialchars($cfg->getUsername()) ?>"></td>
    </tr>
    <tr>
      <td>Password:</td>
      <td><input name="password" type="text" value="<?php echo htmlspecialchars($cfg->getPassword()) ?>"></td>
    </tr>
    <tr>
      <td>Database:</td>
      <td><input name="dbname" type="text" value="<?php echo htmlspecialchars($cfg->getDbName()) ?>"></td>
    </tr>
    <tr>
      <td colspan="2"><p><em><strong>Sys_objects</strong></em></p></td>
    </tr>
    </tr>
      <td>Medium:</td>
      <td><select name="medium"><option value="db" <?php if ($cfg->getMedium() == 'db') echo 'selected'; else echo ''; ?>>Database</option><option value="file" <?php if ($cfg->getMedium() == 'file') echo 'selected'; else echo ''; ?>>File</option></select></td>
    </tr>

    <?php if ($cfg->getMedium() == 'file') { ?>

    <tr>
      <td>BasePath:</td>
      <td><input name="basepath" type="text" value="<?php echo htmlspecialchars($cfg->getBasePath()) ?>"></td>
    </tr>

    <?php } ?>
    <?php
      if ($cfg->getMedium() == 'db') {
        $syscfg = $cfg->getSysDbConfig();
    ?>

    <tr>
      <td>Host:</td><td>
      <input name="syshost" type="text" value="<?php echo htmlspecialchars($syscfg['host']) ?>"></td>
    </tr>
    <tr>
      <td>Username:</td><td>
      <input name="sysusername" type="text" value="<?php echo htmlspecialchars($syscfg['username']) ?>"></td>
    </tr>
    <tr>
      <td>Password:</td>
      <td><input name="syspassword" type="text" value="<?php echo htmlspecialchars($syscfg['password']) ?>"></td>
    </tr>
    <tr>
      <td>Database:</td>
      <td><input name="sysdbname" type="text" value="<?php echo htmlspecialchars($syscfg['dbname']) ?>"></td>
    </tr>


    <?php } ?>


    <tr>
      <td colspan="2" align="center"><input type="submit" name="doUpdate" value="Update localconf.xml"></td>
    </tr>
  </table>

  <input name="driver" type="hidden" value="mysql"></td>
  <input name="sysdriver" type="hidden" value="mysql">

</form>

<?php
  require_once 'footer.inc';
?>
