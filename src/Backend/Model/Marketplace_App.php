<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Marketplace_App implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $version;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var string|null
     */
    protected $screenshot;
    /**
     * @var string|null
     */
    protected $website;
    /**
     * @var string|null
     */
    protected $downloadUrl;
    /**
     * @var string|null
     */
    protected $sha1Hash;
    /**
     * @param string|null $version
     */
    public function setVersion(?string $version) : void
    {
        $this->version = $version;
    }
    /**
     * @return string|null
     */
    public function getVersion() : ?string
    {
        return $this->version;
    }
    /**
     * @param string|null $description
     */
    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }
    /**
     * @return string|null
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }
    /**
     * @param string|null $screenshot
     */
    public function setScreenshot(?string $screenshot) : void
    {
        $this->screenshot = $screenshot;
    }
    /**
     * @return string|null
     */
    public function getScreenshot() : ?string
    {
        return $this->screenshot;
    }
    /**
     * @param string|null $website
     */
    public function setWebsite(?string $website) : void
    {
        $this->website = $website;
    }
    /**
     * @return string|null
     */
    public function getWebsite() : ?string
    {
        return $this->website;
    }
    /**
     * @param string|null $downloadUrl
     */
    public function setDownloadUrl(?string $downloadUrl) : void
    {
        $this->downloadUrl = $downloadUrl;
    }
    /**
     * @return string|null
     */
    public function getDownloadUrl() : ?string
    {
        return $this->downloadUrl;
    }
    /**
     * @param string|null $sha1Hash
     */
    public function setSha1Hash(?string $sha1Hash) : void
    {
        $this->sha1Hash = $sha1Hash;
    }
    /**
     * @return string|null
     */
    public function getSha1Hash() : ?string
    {
        return $this->sha1Hash;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('version' => $this->version, 'description' => $this->description, 'screenshot' => $this->screenshot, 'website' => $this->website, 'downloadUrl' => $this->downloadUrl, 'sha1Hash' => $this->sha1Hash), static function ($value) : bool {
            return $value !== null;
        });
    }
}
