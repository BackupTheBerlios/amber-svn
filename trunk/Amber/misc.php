<?php

/**
 *
 * @package Amber
 * @subpackage HelperFunctions
 *
 */
function microtime_diff($a, $b)
{
  list($a_dec, $a_sec) = explode(' ' , $a);
  list($b_dec, $b_sec) = explode(' ', $b);
  return $b_sec - $a_sec + $b_dec - $a_dec;
}

function MScolor($MSColor)
{
  if (!is_numeric($MSColor)) {
    return NULL;
  } else {
    return ($MSColor % 256) * 256 * 256 + (floor($MSColor / 256) % 256) * 256 + floor($MSColor / 256 / 256);
  }
}

function MSPageOrientation($type)
{
  $orientList = array(1 => 'portrait', 2 => 'landscape');
  $orient = $orientList[$type];
  if (!$orient) {
    $orient = 'portrait';
  }
  return $orient;
}

function MSPageSize($type, &$name, &$width, &$height)
{
  $mm = 1440 / 25.4;
  $in = 1440;

  $pageList = array(
    1 => array('Letter', 8.5*$in, 11*$in),
    2 => array('LetterSmall', 8.5*$in, 11*$in),
    3 => array('Tabloid', 11*$in, 17*$in),
    4 => array('Ledger', 17*$in, 11*$in),
    5 => array('Legal', 8.5*$in, 14*$in),
    6 => array('Statement', 5.5*$in, 8.5*$in),
    7 => array('Executive', 7.25*$in, 10.5*$in),
    8 => array('A3', 297*$mm, 420*$mm),
    9 => array('A4', 210*$mm, 297*$mm),
   10 => array('A4small', 210*$mm, 297*$mm),
   11 => array('A5', 148*$mm, 210*$mm),
   12 => array('B4', 250*$mm, 354*$mm),
   13 => array('B5', 182*$mm, 257*$mm),
   14 => array('Folio', 8.5*$in, 13*$in),
   15 => array('Quatro', 215,275),
   16 => array('10x14', 10*$in, 14*$in),
   17 => array('11x17', 11*$in, 17*$in),
   18 => array('Note', 8.5*$in, 11*$in),
   19 => array('Env9', 5*$in, 8.875*$in),
   20 => array('Env10', 5*$in, 9.5*$in),
   21 => array('Env11', 5*$in, 10.375*$in),
   22 => array('Env12', 5*$in, 11*$in),
   23 => array('Env14', 5*$in, 11.5*$in),
   24 => array('CSheet', 17*$in, 22*$in),
   25 => array('DSheet', 22*$in, 34*$in),
   26 => array('ESheet', 34*$in, 44*$in),
   27 => array('EnvDL', 110*$mm, 220*$mm),
   28 => array('EnvC5', 162*$mm, 229*$mm),
   29 => array('EnvC3', 324*$mm, 458*$mm),
   30 => array('EnvC4', 229*$mm, 324*$mm),
   31 => array('EnvC6', 114*$mm, 162*$mm),
   32 => array('EnvC65', 114*$mm, 229*$mm),
   33 => array('EnvB4', 250*$mm, 353*$mm),
   34 => array('EnvB5', 176*$mm, 250*$mm),
   35 => array('EnvB6', 176*$mm, 125*$mm),
   36 => array('EnvItaly', 110*$mm, 230*$mm),
   37 => array('EnvMonarch', 3.875*$in, 7.5*$in),
   38 => array('EnvPersonal', 3.625*$in, 6.5*$in),
   39 => array('FanfoldUS', 14.875*$in, 11*$in),
   40 => array('FanfoldStdGerman', 8.5*$in, 12*$in),
   41 => array('FanfoldLglGerman', 8.5*$in, 13*$in),
  );
  $name   = $pageList[$type][0];
  $width  = $pageList[$type][1];
  $height = $pageList[$type][2];
  #Amber::dump(array('type'=>$type, 'name'=>$name, 'width'=>$width/1440, 'height'=>$height/1440));
}

?>
