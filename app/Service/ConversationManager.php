<?php

namespace App\Service;

class ConversationManager
{
    protected $userChannel;
    protected $customerChannel;
    protected $longestUserMonologue = 0.0;
    protected $longestCustomerMonologue = 0.0;
    protected $userTalkPercentage = 0.0;

    public function __construct(ChannelManager $userChannel, ChannelManager $customerChannel)
    {
        $this->userChannel = $userChannel;
        $this->customerChannel = $customerChannel;
        $this->processConversation();
    }

    public function getUserChannel()
    {
        return $this->userChannel;
    }

    public function getCustomerChannel()
    {
        return $this->customerChannel;
    }

    public function getLongestUserMonologue()
    {
        return $this->longestUserMonologue;
    }

    public function getLongestCustomerMonologue()
    {
        return $this->longestCustomerMonologue;
    }

    public function getUserTalkPercentage()
    {
        return $this->userTalkPercentage;
    }

    private function updateLongestMonologue($newTime, $currentTime)
    {
        return max($currentTime, $newTime);
    }

    private function calculateUserPercentage()
    {
        $customerTimes = $this->customerChannel->getTimes();
        $userTimes = $this->userChannel->getTimes();
        $userTotal = 0.0;
        $customerTotal = 0.0;

        foreach ($userTimes as $key => $userTime) {
            if ($userTime[1] == 0) {
                break;
            }
            $userTotal += ($userTime[1] - $userTime[0]);

        }
        foreach ($customerTimes as $customerTime) {
            if ($customerTime[1] == 0) {
                break;
            }
            $customerTotal += ($customerTime[1] - $customerTime[0]);
        }
        $this->userTalkPercentage = $userTotal / ($userTimes[count($userTimes)-1][0]);
    }

    private function calculateLongestMonologue($interruptorTimes, $talkerTimes)
    {
        $notInterruptedTalk = 0.0;
        $result = 0.0;

        foreach ($talkerTimes as $key => $talkerTime) {
            foreach ($interruptorTimes as $key => $interruptorTime) {
                // start is before customer start and end is before customer start
                if (
                    ($talkerTime[0] < $interruptorTime[0]) &&
                    ($talkerTime[1] <= $interruptorTime[0])
                ) {
                    $notInterruptedTalk += $talkerTime[1] - $talkerTime[0];

                    // start is before customer start and end is between customer start/end
                } elseif (
                    ($talkerTime[0] < $interruptorTime[0]) &&
                    ($talkerTime[1] >= $interruptorTime[0]) &&
                    ($talkerTime[1] <= $interruptorTime[1])
                ) {
                    $notInterruptedTalk += $interruptorTime[0] - $talkerTime[0];
                    $result = $this->updateLongestMonologue($notInterruptedTalk, $result);
                    $notInterruptedTalk = 0.0;

                    // start is before customer start and end is after customer end
                } elseif (
                    ($talkerTime[0] < $interruptorTime[0]) &&
                    ($talkerTime[1] > $interruptorTime[1])
                ) {
                    $notInterruptedTalk += $interruptorTime[0] - $talkerTime[0];
                    $result = $this->updateLongestMonologue($notInterruptedTalk, $result);
                    $notInterruptedTalk = $talkerTime[1] - $interruptorTime[1];

                    // start between customer start/end and end is after customer end
                } elseif (
                    ($talkerTime[0] >= $interruptorTime[0]) &&
                    ($talkerTime[0] <= $interruptorTime[1]) &&
                    ($talkerTime[1] > $interruptorTime[1])
                ) {
                    $notInterruptedTalk = $talkerTime[1] - $interruptorTime[1];
                    $result = $this->updateLongestMonologue($notInterruptedTalk, $result);
                    // start after customer end
                } elseif ($talkerTime[0] >= $interruptorTime[1]) {
                    $notInterruptedTalk += $talkerTime[1] - $talkerTime[0];
                }
            }
        }

        return $result;
    }

    private function calculateLongestMonologues()
    {
        $customerTimes = $this->customerChannel->getTimes();
        $userTimes = $this->userChannel->getTimes();
        $this->longestUserMonologue = $this->calculateLongestMonologue($customerTimes, $userTimes);
        $this->longestCustomerMonologue = $this->calculateLongestMonologue($userTimes, $customerTimes);
    }

    private function processConversation()
    {
        $this->calculateUserPercentage();
        $this->calculateLongestMonologues();
    }
}
