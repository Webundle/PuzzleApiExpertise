<?php

namespace Puzzle\Api\ExpertiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Puzzle\OAuthServerBundle\Traits\Taggable;
use Doctrine\Common\Collections\Collection;
use Puzzle\OAuthServerBundle\Traits\Pictureable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

/**
 * Expertise Staff
 *
 * @ORM\Table(name="expertise_staff")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("expertise_staff")
 * @Hateoas\Relation(
 * 		name = "self",
 * 		href = @Hateoas\Route(
 * 			"get_expertise_staff",
 * 			parameters = {"id" = "expr(object.getId())"},
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
class Staff
{
    use PrimaryKeyable,
        Pictureable,
        Timestampable,
        Blameable;
    
    /**
     * @var string
     * @ORM\Column(name="full_name", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $fullName;
    
    /**
     * @var string
     * @ORM\Column(name="biography", type="text", nullable=true)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $biography;
    
    /**
     * @var string
     * @ORM\Column(name="position", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $position;
    
    /**
     * @var array
     * @ORM\Column(name="contacts", type="array", nullable=true)
     * @JMS\Expose
     * @JMS\Type("array")
     */
    private $contacts;
    
    /**
     * @var integer
     * @ORM\Column(name="ranking", type="integer", nullable=true)
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $ranking;
    
    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="staffs", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private $service;
    
    public function setFullName($fullName) : self {
        $this->fullName = $fullName;
        return $this;
    }

    public function getFullName() :? string {
        return $this->fullName;
    }
    
    public function setBiography($biography) : self {
        $this->biography = $biography;
        return $this;
    }
    
    public function getBiography() :? string {
        return $this->biography;
    }
    
    public function setPosition($position) : self {
        $this->position = $position;
        return $this;
    }

    public function getPosition() :? string {
        return $this->position;
    }

    public function setContacts($contacts = null) : self {
        $contacts = $contacts && is_string($contacts) ? explode(',', $contacts) : $contacts;
        
        if ($this->contacts) {
            foreach ($contacts as $contact) {
                $this->addContact($contact);
            }
        }else {
            $this->contacts = $contacts;
        }
        
        return $this;
    }
    
    public function addContact($contact) {
        $this->contacts = array_unique(array_merge($this->contacts, [$contact]));
        return $this;
    }
    
    public function removeContact($contact) : self {
        $this->contacts = array_diff($this->contacts, [$contact]);
        return $this;
    }
    
    public function getContacts() :? array {
        return $this->contacts;
    }
    
    public function setRanking($ranking) : self {
        $this->ranking = $ranking;
        return $this;
    }
    
    public function getRanking():? int {
        return $this->ranking;
    }
    
    public function setService(Service $service = null) : self {
        $this->service = $service;
        return $this;
    }
    
    public function getService() :? Service{
        return $this->service;
    }
}
