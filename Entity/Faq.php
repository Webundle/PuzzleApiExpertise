<?php

namespace Puzzle\Api\ExpertiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Hateoas\Configuration\Annotation as Hateoas;
use Puzzle\OAuthServerBundle\Traits\PrimaryKeyable;
use Puzzle\OAuthServerBundle\Traits\Nameable;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

/**
 * Expertise Faq
 *
 * @ORM\Table(name="expertise_faq")
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 * @JMS\XmlRoot("expertise_faq")
 * @Hateoas\Relation(
 * 		name = "self", 
 * 		href = @Hateoas\Route(
 * 			"get_expertise_faq", 
 * 			parameters = {"id" = "expr(object.getId())"},
 * 			absolute = true,
 * ))
 */
class Faq
{
    use PrimaryKeyable, 
        Timestampable,
        Blameable;
    
    /**
     * @var string
     * @ORM\Column(name="question", type="string", length=255)
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $question;

    /**
     * @var string
     * @ORM\Column(name="answer", type="text")
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $answer;

    public function setQuestion($question) : self {
        $this->question = $question;
        return $this;
    }

    public function getQuestion() :? string {
        return $this->question;
    }

    public function setAnswer($answer) : self {
        $this->answer = $answer;
        return $this;
    }

    public function getAnswer() :? string {
        return $this->answer;
    }
}
