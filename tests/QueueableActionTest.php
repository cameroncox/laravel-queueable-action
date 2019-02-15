<?php

namespace Spatie\QueueableAction\Tests;

use Illuminate\Support\Facades\Queue;
use Spatie\QueueableAction\ActionJob;
use Spatie\QueueableAction\Tests\Extra\ComplexAction;
use Spatie\QueueableAction\Tests\Extra\DataObject;
use Spatie\QueueableAction\Tests\Extra\SimpleAction;

class QueueableActionTest extends TestCase
{
    /** @test */
    public function an_action_can_be_queued()
    {
        Queue::fake();

        $action = new SimpleAction();

        $action->onQueue()->execute();

        Queue::assertPushed(ActionJob::class);
    }

    /** @test */
    public function an_action_with_dependencies_and_input_can_be_executed_on_the_queue()
    {
        /** @var \Spatie\QueueableAction\Tests\Extra\ComplexAction $action */
        $action = app(ComplexAction::class);

        $action->onQueue()->execute(new DataObject('foo'));

        $this->assertLogHas('foo bar');
    }

    /** @test */
    public function an_action_can_be_executed_on_a_queue()
    {
        Queue::fake();

        /** @var \Spatie\QueueableAction\Tests\Extra\ComplexAction $action */
        $action = app(ComplexAction::class);

        $action->queue = 'other';

        $action->onQueue()->execute(new DataObject('foo'));

        Queue::assertPushedOn('other', ActionJob::class);
    }

    /** @test */
    public function an_action_is_executed_immediately_if_not_queued()
    {
        Queue::fake();

        /** @var \Spatie\QueueableAction\Tests\Extra\ComplexAction $action */
        $action = app(ComplexAction::class);

        $action->queue = 'other';

        $action->execute(new DataObject('foo'));

        Queue::assertPushedTimes(ActionJob::class, 0);

        $this->assertLogHas('foo bar');
    }
}
