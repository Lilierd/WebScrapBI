<?php

namespace App\Http\Controllers;

use App\Models\MarketShare;
use App\Models\MarketShareSnapshot;
use App\Models\SnapshotIndex;
use App\View\Components\ListComponent;
use App\View\Components\PageComponent;
use App\View\Components\TableComponent\Index;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;

class MarketShareSnapshotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SnapshotIndex $snapshotIndex, MarketShare $marketShare)
    {

        $queryBuilder = MarketShareSnapshot::with('marketShare', 'snapshotIndex');

        if ($snapshotIndex->exists) {
            $queryBuilder->whereSnapshotIndexId($snapshotIndex->getKey());
        }

        if ($marketShare->exists) {
            $queryBuilder->whereMarketShareId($marketShare->getKey());
        }

        $list = $queryBuilder
            ->get()
            ->map(function (MarketShareSnapshot $marketShareSnapshot) {
                return [
                    'href' => route('market-share-snapshot.view', $marketShareSnapshot),
                    'display_name' => "{$marketShareSnapshot->marketShare->name}",
                ];
            })
            ->toArray();

        return Response::make(
            Blade::renderComponent(
                new PageComponent(
                    title: "Snapshots du {$snapshotIndex->snapshot_time->format('Y-m-d H:i')}:",
                    childComponent: Blade::renderComponent(new ListComponent(
                        $list
                    )),
                )
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MarketShareSnapshot $marketShareSnapshot)
    {
        $attributes = $marketShareSnapshot->only([
            'volume',
            'last_value',
            'open_value',
            'close_value',
            'high_value',
            'low_value'
        ]);
        return Response::make(
            Blade::renderComponent(
                new PageComponent(
                    title: "Snapshots du {$marketShareSnapshot->snapshotIndex->snapshot_time->format('Y-m-d H:i')} concernant {$marketShareSnapshot->marketShare->code} :",
                    childComponent: Blade::renderComponent(new Index(
                        headers: array_keys($attributes),
                        rows: [$attributes],
                    )),
                )
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MarketShareSnapshot $marketShareSnapshot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MarketShareSnapshot $marketShareSnapshot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MarketShareSnapshot $marketShareSnapshot)
    {
        //
    }
}
