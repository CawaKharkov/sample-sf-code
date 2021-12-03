<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserSecretQuestionAnswerRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id", "question_id"})})
 * @UniqueEntity(
 *   fields={"user", "question"},
 *   errorPath="question",
 *   message="This user already has answer for this question"
 * )
 *
 * @JMS\ExclusionPolicy("all")
 */
class UserSecretQuestionAnswer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userSecretQuestionAnswers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="SecretQuestion", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $question;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     *
     */
    private $answer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getQuestion(): ?SecretQuestion
    {
        return $this->question;
    }

    public function setQuestion(?SecretQuestion $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }
}
