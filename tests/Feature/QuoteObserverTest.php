<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class QuoteObserverTest extends TestCase
{
    use DatabaseTransactions;

    public function test_project_quote_sent_at_updated_when_quote_sent()
    {
        // Manual creation to avoid factory issues
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $project = Project::create([
            'name' => 'Test Project Observer',
            'service_code' => 'TEST-' . rand(1000, 9999),
            // Add other likely required fields just in case, though nullable usually defaults to null
        ]);

        $quote = Quote::create([
            'project_id' => $project->id,
            'request_number' => 'COT-TEST-' . rand(1000, 9999),
            'status' => 'Pendiente',
            // 'service_name' removed
        ]);

        $this->assertNull($project->fresh()->quote_sent_at);

        // Update status to Enviado
        $quote->update(['status' => 'Enviado']);

        $this->assertNotNull($project->fresh()->quote_sent_at);
    }

    public function test_project_quote_approved_at_updated_when_quote_approved()
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $project = Project::create([
            'name' => 'Test Project Observer 2',
            'service_code' => 'TEST-' . rand(1000, 9999),
        ]);

        $quote = Quote::create([
            'project_id' => $project->id,
            'request_number' => 'COT-TEST-' . rand(1000, 9999),
            'status' => 'Pendiente'
        ]);

        $this->assertNull($project->fresh()->quote_approved_at);

        // Update status to Aprobado
        $quote->update(['status' => 'Aprobado']);

        $this->assertNotNull($project->fresh()->quote_approved_at);
    }

    public function test_other_approved_quotes_annulled_when_new_quote_approved()
    {
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);

        $project = Project::create([
            'name' => 'Test Project Observer 3',
            'service_code' => 'TEST-' . rand(1000, 9999),
        ]);

        $quote1 = Quote::create([
            'project_id' => $project->id,
            'request_number' => 'COT-TEST-' . rand(1000, 9999),
            'status' => 'Aprobado'
        ]);

        $quote2 = Quote::create([
            'project_id' => $project->id,
            'request_number' => 'COT-TEST-' . rand(1000, 9999),
            'status' => 'Pendiente'
        ]);

        // Verify initial state
        $this->assertEquals('Aprobado', $quote1->fresh()->status);

        // Approve second quote
        $quote2->update(['status' => 'Aprobado']);

        // Verify quote2 is approved and quote1 is annulled
        $this->assertEquals('Aprobado', $quote2->fresh()->status);
        $this->assertEquals('Anulado', $quote1->fresh()->status);
    }
}
