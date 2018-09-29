<?php

namespace Puzzle\Api\ExpertiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Sluggable\Sluggable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Puzzle\OAuthServerBundle\Traits\Nameable;
use Puzzle\OAuthServerBundle\Traits\Describable;

/**
 * Expertise Project
 *
 * @ORM\Table(name="expertise_project")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("expertise_project")
 * @Hateoas\Relation(
 * 		name = "self",
 * 		href = @Hateoas\Route(
 * 			"get_expertise_project",
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 * @Hateoas\Relation(
 *     name = "gallery",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getGallery() === null)"),
 *     href = @Hateoas\Route(
 * 			"get_media_folder", 
 * 			parameters = {"id" = "expr(object.getGallery().getId())"},
 * 			absolute = true,
 * ))
 * @Hateoas\Relation(
 *     name = "service",
 *     embedded = "expr(object.getService())",
 *     exclusion = @Hateoas\Exclusion(excludeIf = "expr(object.getService() === null)"),
 *     href = @Hateoas\Route(
 * 			"get_expertise_service", 
 * 			parameters = {"id" = "expr(object.getService().getId())"},
 * 			absolute = true,
 * ))
 */
class Project
{
    use PrimaryKeyable,
        Sluggable,
        Timestampable,
        Blameable,
        Nameable,
        Describable
    ;

    /**
     * @ORM\Column(name="slug", type="string", length=255)
     * @var string
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $slug;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $picture;
    
    /**
     * @var string
     * @ORM\Column(name="client", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $client;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="started_at", type="datetime", nullable=true)
     * @JMS\Expose
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    private $startedAt;
    
    /**
     * @var \DateTime
     * @ORM\Column(name="ended_at", type="datetime", nullable=true)
     * @JMS\Expose
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    private $endedAt;

    /**
     * @var string
     * @ORM\Column(name="location", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $location;
    
    /**
     * @var string
     * @ORM\Column(name="gallery", type="string", length=255, nullable=true)
     * @JMS\Expose
     * @JMS\Type("gallery")
     */
    private $gallery;
    
    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="projects", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private $service;
    
    public function getSluggableFields() {
        return [ 'name' ];
    }
    
    public function setClient($client) : self {
        $this->client = $client;
        return $this;
    }

    public function getClient() :? string {
        return $this->client;
    }
    
    public function setStartedAt(\DateTime $startedAt) : self {
        $this->startedAt = $startedAt;
        return $this;
    }
    
    public function getStartedAt() :? \DateTime {
        return $this->startedAt;
    }
    
    public function setEndedAt(\DateTime $endedAt) : self {
        $this->endedAt = $endedAt;
        return $this;
    }
    
    public function getEndedAt() :? \DateTime {
        return $this->endedAt;
    }

    public function setLocation($location) : self {
        $this->location = $location;
        return $this;
    }

    public function getLocation() :? string {
        return $this->location;
    }
    
    public function setPicture($picture) : self {
        $this->picture = $picture;
        return $this;
    }
    
    public function getPicture() :? string {
        return $this->picture;
    }
    
    public function setGallery($gallery) : self {
        $this->gallery = $gallery;
        return $this;
    }
    
    public function getGallery() :? string {
        return $this->gallery;
    }

    public function setService(Service $service = null) : self {
        $this->service = $service;
        return $this;
    }

    public function getService() :? Service {
        return $this->service;
    }
}
