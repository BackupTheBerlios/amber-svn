<?php
/*
  Adaption of Martin Jansen's Quicksort implementation in PHP
  to the needs of Amber
*/  



/*
  Copyright (C) 2002  Martin Jansen <mail@martin-jansen.de>

  This library is free software; you can redistribute it and/or
  modify it under the terms of the GNU Lesser General Public
  License as published by the Free Software Foundation; either
  version 2.1 of the License, or (at your option) any later version.

  This library is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  Lesser General Public License for more details.

  You should have received a copy of the GNU Lesser General Public
  License along with this library; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
*/

/**
 * Sorting arrays with the Quicksort algorithm.
 *
 * @author Martin Jansen <mail@martin-jansen.de>
 */
class quicksort {

    var $array;      // array to sort
    var $keys;       // array [0..n-1] of keys for $array
    var $cmpClass;   // Classname which contains the cmp function
     
     
    function sort()
    {
      if (!is_array($this->array)) {
        return false;
      }
  
      $firstElement = 0;
      $lastElement = count($this->array) - 1;
      $res = $this->cmpClass->Report_Sort($this->array[$this->keys[$firstElement]], $this->array[$this->keys[$firstElement]]); 
      if ($res !== 0) {
        return false; // no compare function given
      } else {  
        return $this->doSort($firstElement, $lastElement);
      }  
    }   
       
             
  function doSort($first, $last)
  {            
    if ($first < $last) {
      $middleElement = $this->array[$this->keys[floor(($first + $last) / 2)]];
  
      $fromLeft = $first;
      $fromRight = $last;
  
      while ($fromLeft <= $fromRight) {
  
        while ($this->cmpClass->Report_Sort($this->array[$this->keys[$fromLeft]], $middleElement) < 0) {
          $fromLeft++;
        }
              
        while ($this->cmpClass->Report_Sort($this->array[$this->keys[$fromRight]], $middleElement) > 0) {
          $fromRight--;
        }
  
        if ($fromLeft <= $fromRight) {
          $this->changeElements($fromLeft, $fromRight);
          $fromLeft++;
          $fromRight--;
        }
      }
      $this->doSort($first, $fromRight);
      $this->doSort($fromLeft, $last);        
    }
  
    return true;
  }

  function changeElements($a, $b)
  {
    #if (isset($this->keys[$a]) && isset($this->keys[$b])) {
      $memory = $this->keys[$a];
      $this->keys[$a] =& $this->keys[$b];
      $this->keys[$b] =& $memory;
    #}        
  }
} 


?>
