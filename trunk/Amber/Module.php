<?php

class Module
{
  var $name;
  var $code;
  

  function initialize(&$obj)
  {
    $this->name = $obj->name;
    $this->code = $obj->code;
  }

  function run()
  {
    eval(' ?>' . $this->code . '<?php ');
  }
}

?>