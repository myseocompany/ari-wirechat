<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Job;

class JobController extends Controller
{
	public function index(){

		$model = Job::all();

		return view('jobs.index', compact('model'));
	}

}
