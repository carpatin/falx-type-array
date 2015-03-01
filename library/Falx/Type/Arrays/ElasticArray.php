<?php

/*
 * This file is part of the Falx PHP library.
 *
 * (c) Dan Homorodean <dan.homorodean@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Falx\Type\Arrays;

use Falx\Exception\IllegalArgumentException;

/**
 * Elastic array : an array that allocates fixed arrays to provide needed ranges of indexes as elements 
 * are set in.
 * @author Dan Homorodean <dan.homorodean@gmail.com>
 */
class ElasticArray implements \ArrayAccess, \Countable, \Iterator
{

    /**
     * @var int 
     */
    protected $innerLength;

    /**
     * @var array 
     */
    protected $fixedArrays;

    /**
     * Actual elements count.
     * @var int 
     */
    protected $count;

    /**
     * Index used in iteration , points to current fixed array.
     * @var int 
     */
    protected $fixedPointer;

    /**
     * Index used in iteration, points to current element position in the current fixed array.
     * @var int 
     */
    protected $innerPointer;

    /**
     * Constructor
     * @param int $innerLength Default is 10, the inner fixed arrays length.
     */
    public function __construct($innerLength = 10)
    {
        $this->fixedArrays = [];
        $this->innerLength = $innerLength;
        $this->count = 0;
        $this->rewind();
    }

    /**
     * Computes index of fixed array in the fixed arrays container.
     * @param int $offset
     * @return int
     */
    protected function computeFixedIndex($offset)
    {
        return (int) floor($offset / $this->innerLength);
    }

    /**
     * Computes index of element in its fixed array.
     * @param int $offset
     * @return int
     */
    protected function computeInnerIndex($offset)
    {
        return $offset % $this->innerLength;
    }

    /**
     * Offset exists implementation
     * (Implements ArrayAccess interface)
     * @param int $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        $fixedIndex = $this->computeFixedIndex($offset);

        if (!isset($this->fixedArrays[$fixedIndex])) {
            return false;
        }

        $fixedArray = $this->fixedArrays[$fixedIndex];
        $innerIndex = $this->computeInnerIndex($offset);

        if (!isset($fixedArray[$innerIndex])) {
            return false;
        }

        return true;
    }

    /**
     * Offset get implementation
     * (Implements ArrayAccess interface)
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->checkIntegerOffset($offset);

        if (!$this->offsetExists($offset)) {
            return null;
        }
        $fixedIndex = $this->computeFixedIndex($offset);
        $innerIndex = $this->computeInnerIndex($offset);
        return $this->fixedArrays[$fixedIndex]->offsetGet($innerIndex);
    }

    /**
     * Offset set implementation
     * (Implements ArrayAccess interface)
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->checkIntegerOffset($offset);

        $fixedIndex = $this->computeFixedIndex($offset);

        if (!isset($this->fixedArrays[$fixedIndex])) {
            // Create a new fixed array for given range of indexes (expand array)
            $this->fixedArrays[$fixedIndex] = new \SplFixedArray($this->innerLength);
        }

        $innerIndex = $this->computeInnerIndex($offset);
        $fixedArray = $this->fixedArrays[$fixedIndex];

        // Increment the actual count if setting a previously unset index
        if ($fixedArray->offsetGet($innerIndex) === null && $value !== null) {
            $this->count++;
        }

        $fixedArray->offsetSet($innerIndex, $value);
    }

    /**
     * Offset unset implementation
     * (Implements ArrayAccess interface)
     * @param int $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->checkIntegerOffset($offset);

        if (!$this->offsetExists($offset)) {
            return;
        }
        $fixedIndex = $this->computeFixedIndex($offset);
        $innerIndex = $this->computeInnerIndex($offset);
        $fixedArray = $this->fixedArrays[$fixedIndex];

        // Decrement elements actual count  if unsetting an offset thet had a non-NULL value
        if ($fixedArray->offsetGet($innerIndex) !== null) {
            $this->count--;
        }

        // Do inner fixed array element unset
        $fixedArray->offsetUnset($innerIndex);

        // Test if all elements in fixxed array became NULL
        $allNull = true;
        foreach ($fixedArray as $el) {
            if ($el !== null) {
                $allNull = false;
                break;
            }
        }
        // If all elements in the fixed array unset it from the fixede arrays map
        if ($allNull) {
            unset($this->fixedArrays[$fixedIndex]);
        }
    }

    /**
     * Returns the current element of iteration of the elastic array.
     * Returns FALSE if no current element available.
     * @return mixed|boolean
     */
    public function current()
    {
        if (!$this->valid()) {
            return false;
        }

        return $this->fixedArrays[$this->fixedPointer]->offsetGet($this->innerPointer);
    }

    /**
     * Returns the current key in the iteration of the elastic array.
     * Returns NULL if no current key is available.
     * @return int|null
     */
    public function key()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->innerLength * $this->fixedPointer + $this->innerPointer;
    }

    /**
     * Advances interation to the next non-null element in the elastic array.
     * Returns next element or FALSE if reached end of array.
     * @return mixed|boolean
     */
    public function next()
    {
        if (!$this->valid()) {
            return false;
        }

        // Handle case when the current fixed array's end is reached
        if ($this->innerPointer == $this->innerLength - 1) {
            $keys = array_keys($this->fixedArrays);
            sort($keys);
            $pos = array_search($this->fixedPointer, $keys);
            if ($pos < count($keys) - 1) {
                // There is at least one fixed array left for iteration
                $this->fixedPointer = $keys[$pos + 1];
                // Advance to next non-null in the fixed array
                return $this->firstNonNullElement();
            } else {
                // Last fixed array's end reached
                $this->fixedPointer = null;
                $this->innerPointer = null;
                return false;
            }
        } else {
            // Handle case when advancing in the same fixed array
            // Advance to next non-null in the fixed array
            $element = $this->nextNonNullElement();

            if ($element === false) {
                // No more non-null elements found in current fixed
                $this->innerPointer = $this->innerLength - 1;
                // Simulate reach of end of fixed array and recursively call next to advance
                return $this->next();
            }
        }
    }

    /**
     * Rewinds the interation to the first non-null element in the elastic array. 
     * @return void
     */
    public function rewind()
    {
        // Handle case when the array is empty
        if (count($this->fixedArrays) == 0) {
            $this->fixedPointer = null;
            $this->innerPointer = null;
            return;
        }

        // Handle map of fixed not empty case
        $keys = array_keys($this->fixedArrays);
        sort($keys);
        $this->fixedPointer = reset($keys);

        // Advance to next non-null in the fixed array
        $this->firstNonNullElement();
    }

    /**
     * Advances the inner pointer to the next non-null element and return it.
     * If there is no non-null element left in the current fixed array returns FALSE
     * @return mixed|boolean
     */
    private function nextNonNullElement()
    {
        $fixedArray = $this->fixedArrays[$this->fixedPointer];
        $nextPointer = $this->innerPointer + 1;
        $this->innerPointer = null;

        // Start from the following index and search for the next one with a non-null value
        for ($i = $nextPointer; $i < $this->innerLength; $i++) {
            if ($fixedArray[$i] !== null) {
                $this->innerPointer = $i;
                break;
            }
        }

        // This check is just in case, it should not reach the case to return false in its current usage
        if ($this->innerPointer !== null) {
            return $fixedArray[$this->innerPointer];
        } else {
            return false;
        }
    }

    /**
     * Advances the inner pointer to the first non-null element in the currently iterated fixed array.
     * @return boolean
     */
    private function firstNonNullElement()
    {
        $this->innerPointer = null;
        foreach ($this->fixedArrays[$this->fixedPointer] as $index => $element) {
            if ($element !== null) {
                $this->innerPointer = $index;
                break;
            }
        }

        // Check if found at least one index with a non-null value
        if ($this->innerPointer !== null) {
            return $this->fixedArrays[$this->fixedPointer]->offsetGet($this->innerPointer);
        } else {
            return false;
        }
    }

    /**
     * Returns whether the current interator position is valid.
     * @return boolean
     */
    public function valid()
    {
        if ($this->fixedPointer === null || $this->innerPointer === null) {
            return false;
        }
        return true;
    }

    /**
     * Implementation of Countable.
     * Returns the actual count of elements (non-NULL elements)
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Returns total size (number of element positions) alolocated for elements.
     * @return int
     */
    public function size()
    {
        return count($this->fixedArrays) * $this->innerLength;
    }

    /**
     * Throws exception in case of non-integer offset parameter.
     * @param int $offset
     * @throws IllegalArgumentException
     */
    protected function checkIntegerOffset($offset)
    {
        if (!is_int($offset)) {
            throw new IllegalArgumentException('Expected integer offset');
        }
    }

}
