<?php

namespace App\Http\Controllers;

use App\Service\ParseManager;

class ProcessController extends Controller
{
    public function parse()
    {
        $userChannelFile = 'user-channel.txt';
        $customerChannelFile = 'customer-channel.txt';

        $parser = new ParseManager($userChannelFile, $customerChannelFile);
        $parser->initConversation();

        return $parser->getResult();
    }
}
