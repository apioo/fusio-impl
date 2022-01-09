<?php

namespace Fusio\Impl\Table\Generated;

class PageRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setTitle(?string $title) : void
    {
        $this->setProperty('title', $title);
    }
    public function getTitle() : ?string
    {
        return $this->getProperty('title');
    }
    public function setSlug(?string $slug) : void
    {
        $this->setProperty('slug', $slug);
    }
    public function getSlug() : ?string
    {
        return $this->getProperty('slug');
    }
    public function setContent(?string $content) : void
    {
        $this->setProperty('content', $content);
    }
    public function getContent() : ?string
    {
        return $this->getProperty('content');
    }
    public function setDate(?\DateTime $date) : void
    {
        $this->setProperty('date', $date);
    }
    public function getDate() : ?\DateTime
    {
        return $this->getProperty('date');
    }
}