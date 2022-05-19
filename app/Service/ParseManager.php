<?php

namespace App\Service;

class ParseManager
{
    protected string $userChannelFile;
    protected string $customerChannelFile;
    protected $conversation;

    public function __construct(string $userChannelFile, string $customerChannelFile)
    {
        $this->userChannelFile = $userChannelFile;
        $this->customerChannelFile = $customerChannelFile;
    }

    public function getResult()
    {
        return json_encode([
            "longest_user_monologue" => $this->conversation->getLongestUserMonologue(),
            "longest_customer_monologue" => $this->conversation->getLongestCustomerMonologue(),
            "user_talk_percentage" => $this->conversation->getUserTalkPercentage(),
            "user" => $this->conversation->getUserChannel()->getTimes(),
            "customer" => $this->conversation->getCustomerChannel()->getTimes(),
        ]);
    }

    public function initConversation()
    {
        $this->conversation = new ConversationManager(
            new ChannelManager($this->userChannelFile),
            new ChannelManager($this->customerChannelFile)
        );
    }
}
