<?php

namespace spec\Rokde\Flysystem\Adapter;

use League\Flysystem\AdapterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Rokde\Flysystem\Adapter\Model\FileModel;

class LocalDatabaseAdapterSpec extends ObjectBehavior
{
    function let(FileModel $model)
    {
        $this->beConstructedWith($model);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Rokde\Flysystem\Adapter\LocalDatabaseAdapter');
    }

    function it_implements_the_flysystem_adapter_interface()
    {
        $this->shouldImplement(AdapterInterface::class);
    }

}
