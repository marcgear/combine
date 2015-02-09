<?php
namespace Combine;

class Entry
{
    /**
     * @var Question
     */
    protected $question;

    /**
     * @var String
     */
    protected $response;

    /**
     * @var User
     */
    protected $user;

    public function __construct(Question $question, $response, User $user)
    {
        $this->question = $question;
        $this->response = $response;
        $this->user     = $user;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getUser()
    {
        return $this->user;
    }
}