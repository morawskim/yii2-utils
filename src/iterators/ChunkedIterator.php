<?php

namespace mmo\yii2\iterators;

/**
 * Class ChunkedIterator
 * @link https://raw.githubusercontent.com/guzzle/guzzle3/master/src/Guzzle/Iterator/ChunkedIterator.php
 */
class ChunkedIterator extends \IteratorIterator
{
    /** @var int Size of each chunk */
    protected $chunkSize;

    /** @var array Current chunk */
    protected $chunk;

    /**
     * @param \Traversable $iterator Traversable iterator
     * @param int $chunkSize Size to make each chunk
     * @throws \InvalidArgumentException
     */
    public function __construct(\Traversable $iterator, int $chunkSize)
    {
        if ($chunkSize < 0) {
            throw new \InvalidArgumentException("The chunk size must be equal or greater than zero; $chunkSize given");
        }

        parent::__construct($iterator);
        $this->chunkSize = $chunkSize;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        parent::rewind();
        $this->next();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->chunk = [];
        for ($i = 0; $i < $this->chunkSize && parent::valid(); $i++) {
            $this->chunk[] = parent::current();
            parent::next();
        }
    }

    /**
     * @return array|mixed
     */
    public function current()
    {
        return $this->chunk;
    }

    public function valid(): bool
    {
        return (bool)$this->chunk;
    }
}
