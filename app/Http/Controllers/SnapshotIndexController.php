<?php

namespace App\Http\Controllers;

use App\Models\SnapshotIndex;
use App\View\Components\ListComponent;
use App\View\Components\PageComponent;
use App\View\Components\TableComponent\Index;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Response;

class SnapshotIndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $list = SnapshotIndex::all()
            ->map(function (SnapshotIndex $snapshotIndex) {
                return [
                    'href'          => route('browse.market-snapshot.by-snapshot', ['snapshotIndex' => $snapshotIndex]),
                    'display_name'  => "{$snapshotIndex->snapshot_time->format('Y-m-d H:i')} [x{$snapshotIndex->snapshots->count()} données collectées]",
                ];
            });

        return Response::make(
            Blade::renderComponent(new PageComponent(
                title: "Liste des index",
                childComponent: Blade::renderComponent(new ListComponent(
                    $list->toArray()
                ))
            ))
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
    public function show(SnapshotIndex $snapshotIndex)
    {
        // dd($snapshotIndex);
        $tableHeaders = [
            'id',
            // 'marketShare.volume',
            // 'marketShare.last_value',
            // 'marketShare.open_value',
            // 'marketShare.close_value',
            // 'marketShare.high_value',
            // 'marketShare.low_value'
        ];
        // $snapshotIndex = $snapshotIndex->toArray();

        // dd($tableRows);
        // die;

        $data = $snapshotIndex->snapshots()->limit(10)->with('marketShare')->get()->toArray();
        // $headers = array_keys($data[0]);

        dd($data);

        return Response::make(
            Blade::renderComponent(
                new PageComponent(
                    childComponent: Blade::renderComponent(new Index(
                        headers: array_keys($snapshotIndex->marketShares->toArray()[0]),
                        rows: $snapshotIndex->marketShares->toArray()
                    ))
                )
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SnapshotIndex $snapshotIndex)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SnapshotIndex $snapshotIndex)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SnapshotIndex $snapshotIndex)
    {
        //
    }
}
