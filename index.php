<?php
  require_once 'Amber/install/header.inc';
?>

<h1 align="center">Amber</h1>

<p>&nbsp;

<p align="center">
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
</p>

<iframe id="content" width="100%" height="100%"><!-- CONTENT --></div>

<?php
  require_once 'Amber/install/footer.inc';
?>