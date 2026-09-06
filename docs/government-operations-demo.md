# Government operations demo

The Filament panel at `/admin` contains a local, hardcoded demonstration of the nine government operations requirements. Its records, contacts, coordinates, recommendations and evidence images are fictional.

## Prepare the local app

Run these commands from `swm-backend`:

```powershell
php artisan migrate
php artisan db:seed --class=OperationsDemoSeeder
php artisan storage:link
php artisan filament:assets
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000/admin`. Existing local officer data is preserved: the migration is additive and the demo seeder only creates missing records with `OPS-DEMO-` references.

## Suggested walkthrough

1. On **Government operations**, scan the summary, priority queue and officer-specific notification inbox.
2. Select **Simulate new report**. A fictional high-risk case is created and every officer receives an unread in-app notice.
3. Open the new case and select **Start review**.
4. Review its evidence, reporter details, area owner, rules-based crew estimate and interactive depot-to-disposal map.
5. Select **Assign team**. The form preselects an available compatible driver whose demo depot is nearest to the report.
6. Select **Start clearance**.
7. Select **Record resolution**, upload a JPEG, PNG or WebP clearance photograph, and save. The case closes and its assignment is completed.
8. For the alternate closure path, open another case and select **More actions**, then **Mark false report**. The investigation reason is mandatory.

The supplied `public/demo/reports/lane-cleared.png` can be used as a fictional clearance upload during a walkthrough. Uploaded proof is stored on the local `public` disk under the case ID.

## Demo logic

- Queue priority: risk score descending, then oldest submission first.
- Crew suggestions: fixed thresholds for people, lorries and response hours; construction waste adds two people.
- Deadlines: anchored to the report submission time and retained when recommendations are refreshed.
- Drivers: available, compatible with the waste category, matched to the assigned contractor and not already active on another case.
- Disposal centres: compatible with the waste category and ranked by haversine distance from the report coordinates.
- Overview map: open reports are coloured by risk, disposal centres use a separate marker, and report popups link to investigation.
- Case map: plots the selected or suggested driver depot, report and recommended disposal centre, then requests a driving route from the public OSRM demo service.

Maps use OpenStreetMap tiles with visible attribution and need internet access for the base layer. Driver and disposal-centre selection remains deterministic and uses haversine distance. When OSRM is available, the case map follows roads and reports total distance and estimated duration; if routing fails, the dashed direct lines remain visible. The public OSRM endpoint is suitable for this local demonstration and should be replaced with a supported or self-hosted router for production. Live traffic, tracking and cost are outside this POC.

## Verification

```powershell
php artisan test --compact
php vendor/bin/pint --test
npm run build
```

The automated suite covers officer access, notification isolation, risk ordering, recommendations, assignment conflicts, both closure paths, proof ownership and the full Filament workflow.
