<?php

/*
 * This file is part of the Falx PHP library.
 *
 * (c) Dan Homorodean <dan.homorodean@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Falx\Type\Lists;

/**
 * Doubly linked list implemented using object nodes referencing other object nodes.
 * @author Dan Homorodean <dan.homorodean@gmail.com>
 */
class DoublyLinked implements \Iterator, \Countable
{

    /**
     * Contains the first element in the list
     * @var DoublyLinked\Node 
     */
    protected $head;

    /**
     * Contains the last element in the list
     * @var DoublyLinked\Node 
     */
    protected $tail;

    /**
     * Contains the current element (during an iteration)
     * @var DoublyLinked\Node 
     */
    protected $currentNode;

    /**
     * Stores current element integer index (during an iteration)
     * @var int
     */
    protected $currentIndex;

    /**
     * Stores the count of elements in the list
     * @var int 
     */
    protected $count;

    /**
     * Constructor - does initialization
     */
    public function __construct()
    {
        $this->count = 0;
    }

    /**
     * Returns whether the list is empty.
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->head === null && $this->tail === null;
    }

    /**
     * Returns top list element
     * @return mixed
     * @throws \RuntimeException If the list is empty
     */
    public function top()
    {
        if ($this->head === null) {
            throw new \RuntimeException('List is empty');
        }

        return $this->head->getData();
    }

    /**
     * Returns bottom list element
     * @return mixed
     * @throws \RuntimeException If the list is empty
     */
    public function bottom()
    {
        if ($this->tail === null) {
            throw new \RuntimeException('List is empty');
        }

        return $this->tail->getData();
    }

    /**
     * Adds the element at the end of the list
     * @param mixed $data
     * @return void
     */
    public function push($data)
    {
        $this->count++;
        $new = new DoublyLinked\Node($data);

        //Handle empty list
        if ($this->isEmpty()) {
            $this->head = $new;
            $this->tail = $new;
            return;
        }

        //Normal case
        $this->tail->setNext($new);
        $new->setPrior($this->tail);
        $this->tail = $new;
    }

    /**
     * Extracts element from the end of the list and returns.
     * @return mixed
     * @throws \RuntimeException If the list is empty
     */
    public function pop()
    {
        if ($this->tail === null) {
            throw new \RuntimeException('List is empty');
        }

        $this->count--;

        $data = $this->tail->getData();
        $prior = $this->tail->getPrior();

        // Handle case when list becomes empty
        if ($prior === null) {
            unset($this->head);
            unset($this->tail);
        } else {
            unset($this->tail);
            $prior->setNext(null);
            $this->tail = $prior;
        }

        return $data;
    }

    /**
     * Adds the element at the beginning of the list.
     * @param mixed $data
     * @return void
     */
    public function unshift($data)
    {
        $this->count++;

        $new = new DoublyLinked\Node($data);

        //Handle empty list
        if ($this->isEmpty()) {
            $this->head = $new;
            $this->tail = $new;
            return;
        }

        //Normal case
        $this->head->setPrior($new);
        $new->setNext($this->head);
        $this->head = $new;
    }

    /**
     * Extracts element from the beginning of the list and returns.
     * @return mixed
     * @throws \RuntimeException If the list is empty
     */
    public function shift()
    {
        if ($this->head === null) {
            throw new \RuntimeException('List is empty');
        }

        $this->count--;
        $data = $this->head->getData();
        $next = $this->head->getNext();

        // Handle case when list becomes empty
        if ($next === null) {
            unset($this->head);
            unset($this->tail);
        } else {
            unset($this->head);
            $next->setPrior(null);
            $this->head = $next;
        }
        return $data;
    }

    /*
     * Iterator interface implementation
     */

    /**
     * Returns current element
     * @return mixed
     */
    public function current()
    {
        return $this->currentNode->getData();
    }

    /**
     * Returns current index/key
     * @return int
     */
    public function key()
    {
        return $this->currentIndex;
    }

    /**
     * Advances to the next element in iteration
     */
    public function next()
    {
        $this->currentNode = $this->currentNode->getNext();
        $this->currentIndex++;
        if ($this->currentNode === null) {
            $this->currentIndex = null;
        }
    }

    /**
     * Rewinds the iteration
     */
    public function rewind()
    {
        $this->currentNode = $this->head;
        $this->currentIndex = 0;
        if ($this->currentNode === null) {
            $this->currentIndex = null;
        }
    }

    /**
     * Returns whether the current iterator state is valid
     * @return boolean
     */
    public function valid()
    {
        return $this->currentNode !== null;
    }

    /*
     * Countable interface implementation
     */

    /**
     * Returns count of elements in list
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

}
