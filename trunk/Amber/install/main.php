<?php
  require_once 'header.inc';
?>

<style>

fieldset
{
  border: none;
  align: center;
}

fieldset br {
	display: none;
}

label {
	margin: 0;
	display: block;
  font-weight: bold;
  font-color: #fff;
  font-size: 0.8em;
  margin-top: 10px;
}

.button
{
  margin-top: 2em;
}

</style>



<table align="center" style="width: 450px; border: #b08454 1px dashed;">
  <tr>
    <td>
      <form action="examples/index.php" method="get" target="_blank">
        <fieldset>
          <div>
            
            <?php
              require_once 'Amber/Amber.php';
              
              $cfgFileName = 'Amber/conf/localconf.xml';
              if (!file_exists($cfgFileName)) {
                Amber::showError('Error: localconf.xml does not exist', 'Amber needs to be configured before you can use it. <br>Use the <b>Install Tool</b> to set up the database connection.');
              } else {              
                $cfg = new AmberConfig;
                $cfg->fromXML($cfgFileName);
                
                setlocale (LC_CTYPE, 'de_DE', 'de_DE@euro');
                setlocale (LC_TIME, 'de_DE', 'de_DE@euro'); // needed for date, time
                setlocale (LC_MONETARY, 'de_DE', 'de_DE@euro'); // needed for numbers
                //setlocale (LC_ALL, 'de_DE', 'de_DE@euro');
                
                $amber =& Amber::getInstance($cfg);
                $mgr =& $amber->getObjectManager();
                $list = $mgr->getList('report');
                
                echo '<label for="repName">Report:</label> <select name="rep" id="repName">';
                foreach ($list as $idx => $entry) {
                  echo '<option value="' . $entry . '">' . $entry . '</option>';
                }
                echo '</select>';
              }
            ?>
            
          </div>
          
          <?php if ($amber): ?>
          <br />
          
          <div>
            <label for="exportFormat">Format:</label>
            <select name="export" id="exportFormat">
              <option value="html">HTML</option>
              <option value="pdf">PDF</option>
            </select>
          </div>
          
          <br />
          
          <div>
            <label for="mode">Mode:</label>
            <select name="mode" id="mode">
              <option value="normal">Normal</option>
              <option value="design">Design</option>
            </select>
          </div>
          
          <br />
          
          <div class="button">
            <input type="submit" value="Show report"></input>
          </div>
          <?php endif; ?>
        </fieldset>
      </form>
    </td>
  </tr>
</table>

<br />

<table align="center" style="width: 450px; border: #b08454 1px dashed;">
  <tr>
    <td>
      <a href="doc/index.html" target="_blank">Class documentation</a><br>
      <a href="Amber/install/mmcache.php" target="_blank">MMCache</a><br>
    </td>
  </tr>
</table>

<?php
  require_once 'footer.inc';
?>
