<?php
  require_once 'Amber/install/header.inc';
?>


<table height="100%" width="100%">
  <tr height="50">
    <td>
      <h1 align="center">Amber</h1>
    </td>
  </tr>
  <tr height="10">
    <td>
      <table align="center">
        <tr>
          <td width="150" align="center">
            <div id="0" onclick="showContent(this.id);">Amber</a></div>
          </td>
          <td width="150" align="center">
            <div id="1" onclick="showContent(this.id);">Amber - Tests</div>
          </td>
          <td width="150" align="center">
            <div id="2" onclick="showContent(this.id);">Amber Install Tool</div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td><iframe id="content" src="content.php?id=0" width="100%" height="100%" frameborder="0"><!-- CONTENT --></div></td>
  </tr>
</table>

<?php
  require_once 'Amber/install/footer.inc';
?>
