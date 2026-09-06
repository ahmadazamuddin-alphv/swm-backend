<?php

namespace Tests\Feature;

use App\Enums\AssignmentStatus;
use App\Enums\ReportStatus;
use App\Filament\Pages\OperationsDashboard;
use App\Filament\Resources\AdminNotifications\AdminNotificationResource;
use App\Filament\Resources\Reports\Pages\EditReport;
use App\Filament\Resources\Reports\Pages\ViewReport;
use App\Filament\Resources\Reports\ReportResource;
use App\Filament\Widgets\OperationsMap;
use App\Filament\Widgets\PriorityQueue;
use App\Filament\Widgets\ReportInbox;
use App\Models\AdminNotification;
use App\Models\DisposalCentre;
use App\Models\Report;
use App\Models\ResponsibleParty;
use App\Models\User;
use App\Models\WasteCategory;
use App\Services\OperationsDemo;
use App\Services\ReportRecommendationService;
use App\Services\ReportWorkflow;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperationsTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    private ReportWorkflow $workflow;

    private ReportRecommendationService $recommendations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 6)->setTime(4, 0));
        $this->officer = User::factory()->create();
        $this->actingAs($this->officer);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();
        app(OperationsDemo::class)->seed();
        $this->workflow = app(ReportWorkflow::class);
        $this->recommendations = app(ReportRecommendationService::class);
    }

    private function case(int $number = 1): Report
    {
        return Report::where('reference', sprintf('OPS-DEMO-%03d', $number))->firstOrFail();
    }

    private function assignment(Report $report): array
    {
        $driver = $this->recommendations->availableDrivers($report)->first()['driver'];

        return [
            'responsible_party_id' => ResponsibleParty::where('contractor_id', $driver->contractor_id)->value('id'),
            'driver_id' => $driver->id, 'manpower' => 8, 'lorries' => 3,
            'deadline' => now()->addHours(4)->timezone('Asia/Kuala_Lumpur')->toDateTimeString(),
        ];
    }

    private function proof(Report $report): string
    {
        Storage::fake('public');

        return UploadedFile::fake()->image('cleared.jpg')->store("reports/{$report->id}/proofs", 'public');
    }

    public function test_dashboard_and_investigation_render_for_each_demo_status(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Government operations')
            ->assertSee('Simulate new report')
            ->assertSee('Operations map')
            ->assertSee('operations-map.js', escape: false)
            ->assertSee('leaflet.css', escape: false);
        Livewire::test(OperationsDashboard::class)->assertSuccessful();
        Livewire::test(OperationsMap::class)
            ->assertSuccessful()
            ->assertSee('Operations map')
            ->assertSee('OPS-DEMO-001')
            ->assertSee('OpenStreetMap contributors');
        foreach (Report::all() as $case) {
            Livewire::test(ViewReport::class, ['record' => $case->id])
                ->assertSuccessful()->assertSee('Report evidence')->assertSee('Disposal plan')
                ->assertSee('Interactive road route')->assertSee('Calculating road route')
                ->assertSee('router.project-osrm.org')->assertSee('Case history');
        }
    }

    public function test_guest_cannot_enter_operations(): void
    {
        auth()->logout();
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/reports/'.$this->case()->id)->assertRedirect('/admin/login');
    }

    public function test_simulation_creates_one_report_and_notifies_each_officer(): void
    {
        $second = User::factory()->create();
        $before = Report::count();
        Livewire::test(OperationsDashboard::class)->callAction('simulateReport')->assertHasNoActionErrors();
        $case = Report::latest('id')->first();
        $this->assertEquals($before + 1, Report::count());
        $this->assertEquals(ReportStatus::New, $case->status);
        $this->assertNotNull($case->suggested_deadline);
        $this->assertDatabaseHas('admin_notifications', ['report_id' => $case->id, 'user_id' => $this->officer->id, 'read_at' => null]);
        $this->assertDatabaseHas('admin_notifications', ['report_id' => $case->id, 'user_id' => $second->id, 'read_at' => null]);
    }

    public function test_inbox_reads_are_scoped_to_the_current_officer(): void
    {
        $other = User::factory()->create();
        $case = app(OperationsDemo::class)->incoming();
        $foreign = AdminNotification::where('user_id', $other->id)->where('report_id', $case->id)->first();
        try {
            Livewire::test(ReportInbox::class)->call('openNotification', $foreign->id);
            $this->fail('A foreign notification was accessible.');
        } catch (ModelNotFoundException) {
            $this->assertNull($foreign->fresh()->read_at);
        }
        Livewire::test(ReportInbox::class)->call('markAllRead')->assertSuccessful();
        $this->assertNull($foreign->fresh()->read_at);
        $this->assertSame(0, AdminNotificationResource::getEloquentQuery()->whereNull('read_at')->count());
    }

    public function test_opening_case_marks_only_its_own_notification_read(): void
    {
        $case = $this->case();
        Livewire::test(ViewReport::class, ['record' => $case->id])->assertSuccessful();
        $this->assertNotNull($case->notifications()->where('user_id', $this->officer->id)->first()->read_at);
        $this->assertNull($this->case(7)->notifications()->where('user_id', $this->officer->id)->first()->read_at);
    }

    public function test_queue_excludes_closed_cases_and_sorts_by_risk(): void
    {
        $ordered = Report::query()->open()->orderByDesc('risk_score')->orderBy('submitted_at')->get();
        Livewire::test(PriorityQueue::class)
            ->assertCanSeeTableRecords($ordered->take(5), inOrder: true)
            ->assertCanNotSeeTableRecords([$this->case(5), $this->case(6)]);
    }

    public function test_queue_investigate_action_links_to_full_report_page(): void
    {
        $case = $this->case();

        Livewire::test(PriorityQueue::class)
            ->assertTableActionExists(
                'investigate',
                fn (Action $action): bool => $action->getUrl() === ReportResource::getUrl('view', ['record' => $case]),
                record: $case,
            );
    }

    public function test_recommendation_deadline_does_not_slide_and_construction_adds_people(): void
    {
        $case = $this->case();
        $original = $this->recommendations->recommend($case);
        $this->travel(3)->hours();
        $repeat = $this->recommendations->recommend($case);
        $this->assertTrue($original['suggested_deadline']->equalTo($repeat['suggested_deadline']));
        $this->assertSame(8, $repeat['suggested_manpower']);
        $case->wasteCategory()->associate(WasteCategory::where('slug', 'construction-waste')->first());
        $this->assertSame(10, $this->recommendations->recommend($case)['suggested_manpower']);
    }

    public function test_destination_requires_valid_coordinates_and_compatible_category(): void
    {
        $category = $this->case()->waste_category_id;
        DisposalCentre::query()->delete();
        $unsuitable = DisposalCentre::create(['name' => 'Unsuitable nearest', 'latitude' => 3.074, 'longitude' => 101.518, 'accepted_category_ids' => [999]]);
        $suitable = DisposalCentre::create(['name' => 'Suitable destination', 'latitude' => 3.14, 'longitude' => 101.45, 'accepted_category_ids' => [(string) $category]]);
        $this->assertSame($suitable->id, $this->recommendations->nearestDisposalCentreId(3.0738, 101.5183, $category));
        $suitable->delete();
        $this->assertNull($this->recommendations->nearestDisposalCentreId(3.0738, 101.5183, $category));
        $this->assertNull($this->recommendations->nearestDisposalCentreId(null, 101.5183, $category));
        $this->assertNull($this->recommendations->nearestDisposalCentreId(93, 101.5183, $category));
        $this->assertNull($this->recommendations->nearestDisposalCentreId(3.0738, 101.5183, null));
        $this->assertEqualsWithDelta(0, $this->recommendations->haversineKm(3, 101, 3, 101), .0001);
    }

    public function test_entire_case_workflow_records_proof_history_and_completes_assignment(): void
    {
        $case = $this->case();
        $this->workflow->review($case, $this->officer);
        $this->workflow->assign($case, $this->officer, $this->assignment($case));
        $this->workflow->start($case, $this->officer);
        $path = $this->proof($case);
        $this->workflow->solve($case, $this->officer, $path, 'Lane cleared and inspected.');

        $this->assertEquals(ReportStatus::Solved, $case->status);
        $this->assertEquals(AssignmentStatus::Completed, $case->assignment->status);
        $this->assertNotNull($case->resolved_at);
        $this->assertEquals($path, $case->resolutionProofs()->first()->path);
        $this->assertEquals($this->officer->id, $case->resolutionProofs()->first()->uploaded_by);
        $this->assertSame(4, $case->activities()->count());
        Storage::disk('public')->assertExists($path);
    }

    public function test_filament_actions_apply_real_workflow_and_validate_fields(): void
    {
        $case = $this->case();
        $page = Livewire::test(ViewReport::class, ['record' => $case->id]);
        $page->callAction('review')->assertHasNoActionErrors();
        $page->callAction('assign', data: $this->assignment($case))->assertHasNoActionErrors();
        $page->callAction('start')->assertHasNoActionErrors();
        $page->callAction('markSolved', data: ['path' => null])->assertHasActionErrors(['path' => 'required']);
        $page->unmountAction();
        $page->callAction('markFalse', data: ['false_report_reason' => ''])->assertHasActionErrors(['false_report_reason' => 'required']);
        $page->unmountAction();
        $page->callAction('markFalse', data: ['false_report_reason' => 'Duplicate case verified.'])->assertHasNoActionErrors();
        $this->assertEquals(ReportStatus::FalseReport, $case->fresh()->status);
        $this->assertEquals(AssignmentStatus::Cancelled, $case->fresh()->assignment->status);
    }

    public function test_filament_resolution_upload_closes_case(): void
    {
        Storage::fake('public');
        $case = $this->case();
        Livewire::test(ViewReport::class, ['record' => $case->id])
            ->callAction('markSolved', data: ['path' => UploadedFile::fake()->image('proof.jpg'), 'notes' => 'Cleared.'])
            ->assertHasNoActionErrors();
        $this->assertEquals(ReportStatus::Solved, $case->fresh()->status);
        $proof = $case->resolutionProofs()->first();
        $this->assertNotNull($proof);
        Storage::disk('public')->assertExists($proof->path);
    }

    public function test_reassignment_cancels_previous_assignment(): void
    {
        $case = $this->case();
        $data = $this->assignment($case);
        $this->workflow->assign($case, $this->officer, $data);
        $firstId = $case->assignment->id;
        $this->workflow->assign($case, $this->officer, [...$data, 'manpower' => 6]);
        $this->assertDatabaseHas('assignments', ['id' => $firstId, 'status' => 'cancelled']);
        $this->assertSame(1, $case->assignments()->where('status', 'active')->count());
        $this->assertSame(6, $case->assignment->manpower);
    }

    public function test_busy_driver_cannot_be_double_booked(): void
    {
        $first = $this->case();
        $data = $this->assignment($first);
        $this->workflow->assign($first, $this->officer, $data);
        $this->expectException(ValidationException::class);
        $this->workflow->assign($this->case(2), $this->officer, $data);
    }

    public function test_assignment_rejects_driver_from_another_contractor(): void
    {
        $case = $this->case();
        $data = $this->assignment($case);
        $wrongParty = ResponsibleParty::whereNotNull('contractor_id')->where('id', '!=', $data['responsible_party_id'])->first();
        $this->expectException(ValidationException::class);
        $this->workflow->assign($case, $this->officer, [...$data, 'responsible_party_id' => $wrongParty->id]);
    }

    public function test_assignment_rejects_area_owner_as_clearance_team(): void
    {
        $case = $this->case();
        $data = $this->assignment($case);
        $areaOwner = $case->zone->responsibleParty;

        $this->expectException(ValidationException::class);
        $this->workflow->assign($case, $this->officer, [...$data, 'responsible_party_id' => $areaOwner->id]);
    }

    public function test_false_report_reason_cannot_be_whitespace(): void
    {
        $this->expectException(ValidationException::class);
        $this->workflow->markFalse($this->case(), $this->officer, '      ');
    }

    public function test_missing_proof_does_not_close_case(): void
    {
        $case = $this->case();
        try {
            $this->workflow->solve($case, $this->officer, "reports/{$case->id}/proofs/missing.jpg", '');
            $this->fail('Missing proof was accepted.');
        } catch (ValidationException) {
            $this->assertEquals(ReportStatus::New, $case->fresh()->status);
            $this->assertSame(0, $case->resolutionProofs()->count());
        }
    }

    public function test_proof_from_another_case_is_rejected(): void
    {
        $path = $this->proof($this->case(2));
        $this->expectException(ValidationException::class);
        $this->workflow->solve($this->case(), $this->officer, $path, '');
    }

    public function test_closed_case_rejects_stale_action(): void
    {
        $case = $this->case();
        $stale = $case->fresh();
        $this->workflow->markFalse($case, $this->officer, 'Duplicate case verified.');
        $this->expectException(ValidationException::class);
        $this->workflow->review($stale, $this->officer);
    }

    public function test_starting_clearance_requires_an_assignment(): void
    {
        $this->expectException(ValidationException::class);
        $this->workflow->start($this->case(), $this->officer);
    }

    public function test_edit_form_cannot_bypass_closure_rules(): void
    {
        $case = $this->case();
        Livewire::test(EditReport::class, ['record' => $case->id])
            ->fillForm(['notes' => 'Reviewed the site.', 'status' => 'solved', 'resolved_at' => now()->toDateTimeString()])
            ->call('save')->assertHasNoFormErrors();
        $this->assertEquals(ReportStatus::New, $case->fresh()->status);
        $this->assertNull($case->fresh()->resolved_at);
    }

    public function test_demo_seeding_preserves_existing_officer_work(): void
    {
        $case = $this->case();
        $this->workflow->markFalse($case, $this->officer, 'Officer verified a duplicate.');
        $count = Report::count();
        $notices = AdminNotification::count();
        app(OperationsDemo::class)->seed();
        $this->assertSame($count, Report::count());
        $this->assertSame($notices, AdminNotification::count());
        $this->assertEquals(ReportStatus::FalseReport, $case->fresh()->status);
        $this->assertSame('Officer verified a duplicate.', $case->fresh()->false_report_reason);
    }
}
