<?php

declare(strict_types = 1);

namespace Fusio\Impl\System\Model;


class About implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $apiVersion;
    /**
     * @var string|null
     */
    protected $title;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var string|null
     */
    protected $termsOfService;
    /**
     * @var string|null
     */
    protected $contactName;
    /**
     * @var string|null
     */
    protected $contactUrl;
    /**
     * @var string|null
     */
    protected $contactEmail;
    /**
     * @var string|null
     */
    protected $licenseName;
    /**
     * @var string|null
     */
    protected $licenseUrl;
    /**
     * @var array<About_Link>|null
     */
    protected $links;
    /**
     * @param string|null $apiVersion
     */
    public function setApiVersion(?string $apiVersion) : void
    {
        $this->apiVersion = $apiVersion;
    }
    /**
     * @return string|null
     */
    public function getApiVersion() : ?string
    {
        return $this->apiVersion;
    }
    /**
     * @param string|null $title
     */
    public function setTitle(?string $title) : void
    {
        $this->title = $title;
    }
    /**
     * @return string|null
     */
    public function getTitle() : ?string
    {
        return $this->title;
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
     * @param string|null $termsOfService
     */
    public function setTermsOfService(?string $termsOfService) : void
    {
        $this->termsOfService = $termsOfService;
    }
    /**
     * @return string|null
     */
    public function getTermsOfService() : ?string
    {
        return $this->termsOfService;
    }
    /**
     * @param string|null $contactName
     */
    public function setContactName(?string $contactName) : void
    {
        $this->contactName = $contactName;
    }
    /**
     * @return string|null
     */
    public function getContactName() : ?string
    {
        return $this->contactName;
    }
    /**
     * @param string|null $contactUrl
     */
    public function setContactUrl(?string $contactUrl) : void
    {
        $this->contactUrl = $contactUrl;
    }
    /**
     * @return string|null
     */
    public function getContactUrl() : ?string
    {
        return $this->contactUrl;
    }
    /**
     * @param string|null $contactEmail
     */
    public function setContactEmail(?string $contactEmail) : void
    {
        $this->contactEmail = $contactEmail;
    }
    /**
     * @return string|null
     */
    public function getContactEmail() : ?string
    {
        return $this->contactEmail;
    }
    /**
     * @param string|null $licenseName
     */
    public function setLicenseName(?string $licenseName) : void
    {
        $this->licenseName = $licenseName;
    }
    /**
     * @return string|null
     */
    public function getLicenseName() : ?string
    {
        return $this->licenseName;
    }
    /**
     * @param string|null $licenseUrl
     */
    public function setLicenseUrl(?string $licenseUrl) : void
    {
        $this->licenseUrl = $licenseUrl;
    }
    /**
     * @return string|null
     */
    public function getLicenseUrl() : ?string
    {
        return $this->licenseUrl;
    }
    /**
     * @param array<About_Link>|null $links
     */
    public function setLinks(?array $links) : void
    {
        $this->links = $links;
    }
    /**
     * @return array<About_Link>|null
     */
    public function getLinks() : ?array
    {
        return $this->links;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('apiVersion' => $this->apiVersion, 'title' => $this->title, 'description' => $this->description, 'termsOfService' => $this->termsOfService, 'contactName' => $this->contactName, 'contactUrl' => $this->contactUrl, 'contactEmail' => $this->contactEmail, 'licenseName' => $this->licenseName, 'licenseUrl' => $this->licenseUrl, 'links' => $this->links), static function ($value) : bool {
            return $value !== null;
        });
    }
}
