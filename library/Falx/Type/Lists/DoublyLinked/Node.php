<?php

/*
 * This file is part of the Falx PHP library.
 *
 * (c) Dan Homorodean <dan.homorodean@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Falx\Type\Lists\DoublyLinked;

/**
 * Doubly linked list node
 * @author Dan Homorodean <dan.homorodean@gmail.com>
 */
class Node
{

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var Node
     */
    protected $next;

    /**
     * @var Node
     */
    protected $prior;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function getPrior()
    {
        return $this->prior;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setNext(Node $next = null)
    {
        $this->next = $next;
        return $this;
    }

    public function setPrior(Node $prior = null)
    {
        $this->prior = $prior;
        return $this;
    }

}
