<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkTime  $workTime
     * @return \Illuminate\Http\Response
     */
    public function show(WorkTime $workTime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkTime  $workTime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WorkTime $workTime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WorkTime  $workTime
     * @return \Illuminate\Http\Response
     */
    public function destroy(WorkTime $workTime)
    {
        //
    }
    public function days(){
        $days = Carbon::getDays();
        $this->getJsonResponse($days,'days');
    }
}
