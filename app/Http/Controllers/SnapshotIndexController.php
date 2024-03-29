<?php

namespace App\Http\Controllers;

use App\Models\SnapshotIndex;
use App\View\Components\ListComponent;
use App\View\Components\PageComponent;
use Illuminate\Http\Request;
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
                    'display_name'  => "{$snapshotIndex->snapshot_time->format('Y-m-d H:i')} [x{$snapshotIndex->marketShare->count()} données collectées]",
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
        //
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
