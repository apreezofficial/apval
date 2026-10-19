<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Resources\Agent\AgentProfileResource;
use Illuminate\Http\Request;
 
class AgentProfileController extends Controller
{
    public function show(Request $request)
    {
        $agent = $request->user()->agentProfile()->with('manager')->firstOrFail();

        return new AgentProfileResource($agent);
    }
}