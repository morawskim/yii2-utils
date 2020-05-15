<?php

namespace mmo\yii2\tests\iterators;

use mmo\yii2\iterators\ChunkedIterator;

class ChunkedIteratorTest extends \mmo\yii2\tests\TestCase
{
    public function testChunksIterator(): void
    {
        $chunked = new ChunkedIterator(new \ArrayIterator(range(0, 100)), 10);
        $chunks = iterator_to_array($chunked, false);
        $this->assertCount(11, $chunks);
        foreach ($chunks as $j => $chunk) {
            $this->assertEquals(range($j * 10, min(100, $j * 10 + 9)), $chunk);
        }
    }

    public function testChunksIteratorWithOddValues(): void
    {
        $chunked = new ChunkedIterator(new \ArrayIterator(array(1, 2, 3, 4, 5)), 2);
        $chunks = iterator_to_array($chunked, false);
        $this->assertCount(3, $chunks);
        $this->assertEquals(array(1, 2), $chunks[0]);
        $this->assertEquals(array(3, 4), $chunks[1]);
        $this->assertEquals(array(5), $chunks[2]);
    }

    /**
     * @requires extension simplexml
     */
    public function testMustNotTerminateWithTraversable(): void
    {
        $traversable = simplexml_load_string('<root><foo/><foo/><foo/></root>')->foo;
        $chunked = new ChunkedIterator($traversable, 2);
        $actual = iterator_to_array($chunked, false);
        $this->assertCount(2, $actual);
    }

    public function testSizeOfZeroMakesIteratorInvalid(): void
    {
        $chunked = new ChunkedIterator(new \ArrayIterator(range(1, 5)), 0);
        $chunked->rewind();
        $this->assertFalse($chunked->valid());
    }

    public function testSizeLowerZeroThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ChunkedIterator(new \ArrayIterator(range(1, 5)), -1);
    }
}
