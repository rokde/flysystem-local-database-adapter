<?php

namespace spec\Rokde\Flysystem\Adapter\Model;

use Illuminate\Database\Eloquent\Model;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileModelSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Rokde\Flysystem\Adapter\Model\FileModel');
    }

    function it_is_an_eloquent_model()
    {
        $this->beAnInstanceOf(Model::class);
    }
}
