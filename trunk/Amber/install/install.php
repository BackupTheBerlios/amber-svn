<?php

/**
 * @ignore
 */
require_once 'header.inc';
require_once 'Amber/Amber.php';
require_once 'Amber/AmberConfig.php';
require_once 'Amber/XMLLoader.php';

$filename = __AMBER_BASE__ . '/conf/localconf.xml';

/**
 * @ignore
 */
function formatMessage($msg)
{
  return '<div align="center"><div style="text-align: left; color: #000000; width: 450; font-size: 10pt; border: #ee0000 2pt solid; background-color: #ffffff; padding: 5px;"><strong>' . $msg . '</strong></div></div>';
}

/**
 * @ignore
 */
function doImport(&$config)
{
  $installerPath = __AMBER_BASE__ . '/install/';
  $amber =& Amber::getInstance($config);

  // Create tx_amber_sys_objects
  $sysDb =& Amber::sysDb();
  $sql = file_get_contents($installerPath . '/tx_amber_sys_objects.sql');
  if ($sql == false) {
    echo formatMessage('Unable to open tx_amber_sys_objects.sql');
    return false;
  }
  $sysDb->Execute($sql);
  if ($sysDb->ErrorNo() != 0) {
    echo formatMessage('Importing tx_amber_sys_objects.sql failed:<p />' . $sysDb->ErrorMsg());
  }

  // Create table which hold sample data
  $db =& Amber::currentDb();
  $sql = @file_get_contents($installerPath . '/sample_data.sql');
  if ($sql == false) {
    echo formatMessage('Unable to open sample_data.sql');
    return false;
  }
  $db->Execute($sql);
  if ($db->ErrorNo() != 0) {
    echo formatMessage('Importing sample_data.sql failed:<p />' . $db->ErrorMsg());
  }
}

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
      echo formatMessage('Unable to update localconf.xml<p />' . htmlentities($filename) . ' needs to be writeable.');
    } else {
      echo formatMessage('Configuraton successfully written to:<p />' . htmlentities($filename));
    }
}
if (isset($_POST['doImport'])) {
  if (!file_exists($filename)) {
    echo formatMessage('You need to create a localconf.xml before attempting to import the sample database.');
  } else {
    $cfg->fromXML($filename);
    doImport($cfg);
  }
}



// Re-read for display
$cfg = new AmberConfig();
if (file_exists($filename)) {
  $cfg->fromXML($filename);
}
$dbcfg = $cfg->get('db');
$syscfg = $cfg->get('sys');

?>

<p />

<form method="post" action="<?php echo $__SELF__; ?>">


  <table align="center" style="width: 450px; border: #b08454 1px dashed;">
    <tr>
      <td class="install_header" colspan="2"><p><em><strong>Database configuration</strong></em></p></td>
    </tr>
    <tr>
      <td>Host:</td><td>
      <input name="host" type="text" value="<?php echo htmlspecialchars($dbcfg['host']) ?>"></td>
    </tr>
    <tr>
      <td>Username:</td><td>
      <input name="username" type="text" value="<?php echo htmlspecialchars($dbcfg['username']) ?>"></td>
    </tr>
    <tr>
      <td>Password:</td>
      <td><input name="password" type="text" value="<?php echo htmlspecialchars($dbcfg['password']) ?>"></td>
    </tr>
    <tr>
      <td>Database:</td>
      <td><input name="dbname" type="text" value="<?php echo htmlspecialchars($dbcfg['dbname']) ?>"></td>
    </tr>
    <tr>
      <td class="install_header" colspan="2"><p><em><strong>Sys_objects</strong></em></p></td>
    </tr>
    </tr>
      <td>Medium:</td>
      <td><select name="medium"><option value="db" <?php if ($syscfg['medium'] == 'db') echo 'selected'; else echo ''; ?>>Database</option><option value="file" <?php if ($syscfg['medium'] == 'file') echo 'selected'; else echo ''; ?>>File</option></select></td>
    </tr>

    <?php if ($syscfg['medium'] == 'file') { ?>

    <tr>
      <td>BasePath:</td>
      <td><input name="basepath" type="text" value="<?php echo htmlspecialchars($syscfg['basepath']) ?>"></td>
    </tr>

    <?php } ?>
    <?php
      if ($syscfg['medium'] == 'db') {
        $syscfg = $cfg->get('sys/database');
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
      <td class="install_header" colspan="2"><p><em><strong>Write configuration</strong></em></p></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td><input type="submit" name="doUpdate" value="Update localconf.xml" /></td>
    </tr>

    <tr>
      <td class="install_header" colspan="2"><p><em><strong>Import</strong></em></p></td>
    </tr>
    <tr>
      <td></td>
      <td><input type="submit" name="doImport" value="Import Sample database"/></td>
    </tr>

  </table>

  <input name="driver" type="hidden" value="mysql"></td>
  <input name="sysdriver" type="hidden" value="mysql">

</form>

<?php
  require_once 'footer.inc';
?>
