<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Agent\LeadResource;
use App\Models\Agent\Lead;
use Illuminate\Http\Request;

class LeadAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('agent')->latest();

        if ($request->filled('classification')) {
            $query->where('classification', $request->query('classification'));
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->query('agent_id'));
        }

        return LeadResource::collection($query->paginate(30));
    }
}