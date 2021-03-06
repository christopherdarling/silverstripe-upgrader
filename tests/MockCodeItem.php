<?php

namespace SilverStripe\Upgrader\Tests;

use SilverStripe\Upgrader\CodeCollection\ItemInterface;

/**
 * Simple mock upgrade rule to be used in test of other system components
 */
class MockCodeItem implements ItemInterface
{
    /**
     * @var MockCodeCollection
     */
    protected $parent;

    /**
     * @var string
     */
    protected $path;

    public function __construct(MockCodeCollection $parent, $path)
    {
        $this->parent = $parent;
        $this->path = $path;
    }
    public function getPath()
    {
        return $this->path;
    }

    public function getFullPath()
    {
        return '/' . $this->getPath();
    }

    /**
     * Read the contents of this file
     * @return string
     */
    public function getContents()
    {
        return $this->parent->getItemContent($this->path);
    }

    /**
     * Update the contents of this file
     * @param string $contents
     */
    public function setContents($contents)
    {
        $this->parent->setItemContent($this->path, $contents);
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return basename($this->getPath());
    }
}
