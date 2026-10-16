<?php

use App\Models\Ticket;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();

    $this->owner = User::factory()->create(['role' => 'customer']);
    $this->other = User::factory()->create(['role' => 'customer']);

    $this->ownTicket = Ticket::create([
        'customer_id' => $this->owner->id,
        'title' => 'Own ticket title',
        'description' => 'Own ticket description',
        'reference_number' => 'AVL 1/2/OWN',
        'status' => 'backlog',
    ]);

    $this->otherTicket = Ticket::create([
        'customer_id' => $this->other->id,
        'title' => 'Other customer ticket',
        'description' => 'Other ticket description',
        'reference_number' => 'AVL 9/9/OTHER',
        'status' => 'in_progress',
    ]);
});

test('customers can view and edit their own tickets', function () {
    $this->actingAs($this->owner);

    $this->get(route('customer.tickets.show', $this->ownTicket))->assertOk();
    $this->get(route('customer.tickets.edit', $this->ownTicket))->assertOk();
});

test('customers cannot view or edit tickets of other customers', function () {
    $this->actingAs($this->owner);

    $this->get(route('customer.tickets.show', $this->otherTicket))->assertForbidden();
    $this->get(route('customer.tickets.edit', $this->otherTicket))->assertForbidden();
});

test('ticket list and dashboard only show the customer own tickets', function () {
    $this->actingAs($this->owner);

    $this->get(route('customer.tickets.index'))
        ->assertOk()
        ->assertSee('Own ticket title')
        ->assertDontSee('Other customer ticket');

    $this->get(route('customer.dashboard'))
        ->assertOk()
        ->assertSee('Own ticket title')
        ->assertDontSee('Other customer ticket');
});
